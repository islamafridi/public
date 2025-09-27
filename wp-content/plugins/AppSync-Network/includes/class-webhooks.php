<?php
/**
 * Webhook handler for App Sync Plugin
 */
class App_Sync_Webhooks {
    
    private $logger;
    
    public function __construct() {
        $this->logger = new App_Sync_Logger();
        
        add_action('save_post', array($this, 'trigger_webhook'), 20, 1);
        add_action('app_sync_manual_trigger', array($this, 'manual_sync'), 10, 1);
        add_action('app_sync_retry_failed', array($this, 'retry_failed_syncs'));
        
        // Schedule retry for failed syncs
        if (!wp_next_scheduled('app_sync_retry_failed')) {
            wp_schedule_event(time(), 'hourly', 'app_sync_retry_failed');
        }
    }
    
    public function trigger_webhook($post_id) {
        // Skip if not master site, revision, autosave, or not a post
        if (get_option('app_sync_is_master', 'no') !== 'yes' || 
            wp_is_post_revision($post_id) || 
            wp_is_post_autosave($post_id) ||
            get_post_type($post_id) !== 'post') {
            return;
        }

        // Check if we have the required GP ID
        $gp_id = get_post_meta($post_id, 'wp_GP_ID', true);
        if (!$gp_id) {
            return;
        }
        
        // Check if this was triggered by a manual sync to prevent double sync
        $last_manual_sync = get_transient('app_sync_manual_' . $post_id);
        if ($last_manual_sync && (time() - $last_manual_sync) < 10) {
            // Skip auto sync if manual sync happened in last 10 seconds
            return;
        }

        $this->sync_to_child_sites($post_id, 'auto');
    }
    
    public function manual_sync($post_id) {
        // Allow manual sync from both master and child sites
        $gp_id = get_post_meta($post_id, 'wp_GP_ID', true);
        if (!$gp_id) {
            return;
        }

        if (get_option('app_sync_is_master', 'no') === 'yes') {
            // Set transient to prevent auto-sync after manual sync
            set_transient('app_sync_last_' . $post_id, time(), 10);
            
            $this->sync_to_child_sites($post_id, 'manual');
        } else {
            // For child sites, log the manual sync request
            $this->logger->log($post_id, '', 'manual_sync_request', 'info', 
                null, 'Manual sync requested from child site');
        }
    }
    
    private function sync_to_child_sites($post_id, $trigger_type = 'auto') {
        $gp_id = get_post_meta($post_id, 'wp_GP_ID', true);
        
        // Check sync frequency settings
        $sync_frequency = get_option('app_sync_sync_frequency', 'immediate');
        if ($sync_frequency === 'manual' && $trigger_type === 'auto') {
            return; // Skip auto sync if set to manual only
        }
        
        // Prepare data for sync
        $data = $this->prepare_sync_data($post_id, $gp_id);
        
        // Force update for manual syncs to bypass content hash check
        if ($trigger_type === 'manual') {
            $data['force_update'] = 'true';
        }
        
        // Get child sites and API token
        $child_sites_raw = get_option('app_sync_child_sites', '');
        $child_sites = array_filter(array_map('trim', explode("\n", $child_sites_raw)));
        $api_token = get_option('app_sync_api_token');

        if (empty($child_sites) || empty($api_token)) {
            $this->logger->log($post_id, '', 'sync_config_error', 'failed', 
                null, 'No child sites configured or API token missing');
            return;
        }

        $timeout = get_option('app_sync_timeout', 30);
        $successful_syncs = 0;
        $failed_syncs = 0;

        foreach ($child_sites as $site) {
            $site_url = rtrim($site, '/');
            $endpoint_url = $site_url . '/wp-json/apps/v1/update';
            
            $response = wp_remote_post($endpoint_url, array(
                'headers' => array(
                    'Content-Type'     => 'application/json',
                    'X-App-Sync-Token' => $api_token,
                    'User-Agent'       => 'App-Sync-Plugin/' . APP_SYNC_VERSION
                ),
                'body'        => wp_json_encode($data),
                'timeout'     => $timeout,
                'redirection' => 0,
                'blocking'    => true
            ));
            
            if (is_wp_error($response)) {
                $failed_syncs++;
                $this->logger->log($post_id, $site_url, 'sync_failed', 'failed', 
                    null, $response->get_error_message());
            } else {
                $response_code = wp_remote_retrieve_response_code($response);
                $response_body = wp_remote_retrieve_body($response);
                $response_data = json_decode($response_body, true);
                
                if ($response_code >= 200 && $response_code < 300) {
                    $successful_syncs++;
                    $message = $response_data['message'] ?? 'Sync completed successfully';
                    
                    // Log differently for skipped vs updated
                    if (isset($response_data['skipped']) && $response_data['skipped']) {
                        $this->logger->log($post_id, $site_url, 'skip_update', 'info', 
                            $response_code, $message);
                    } else {
                        $this->logger->log($post_id, $site_url, 'sync_success', 'success', 
                            $response_code, $message);
                    }
                } else {
                    $failed_syncs++;
                    $error_message = $response_data['message'] ?? wp_remote_retrieve_response_message($response);
                    $this->logger->log($post_id, $site_url, 'sync_failed', 'failed', 
                        $response_code, $error_message);
                }
            }
        }
        
        // Update sync statistics
        $this->update_sync_stats($post_id, $successful_syncs, $failed_syncs);
        
        // Schedule retry for failed syncs if needed
        if ($failed_syncs > 0) {
            wp_schedule_single_event(time() + 300, 'app_sync_retry_failed'); // Retry in 5 minutes
        }
    }
    
    public function retry_failed_syncs() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        $retry_attempts = get_option('app_sync_retry_attempts', 3);
        
        // Get failed syncs from the last 24 hours that haven't exceeded retry limit
        $failed_syncs = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT post_id, site_url FROM $table_name 
             WHERE status = 'failed' 
             AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
             AND (SELECT COUNT(*) FROM $table_name t2 
                  WHERE t2.post_id = $table_name.post_id 
                  AND t2.site_url = $table_name.site_url 
                  AND t2.action = 'retry_sync') < %d
             LIMIT 10",
            $retry_attempts
        ));
        
        foreach ($failed_syncs as $sync) {
            $this->retry_single_sync($sync->post_id, $sync->site_url);
        }
    }
    
    private function retry_single_sync($post_id, $site_url) {
        $gp_id = get_post_meta($post_id, 'wp_GP_ID', true);
        if (!$gp_id) {
            return;
        }
        
        $data = $this->prepare_sync_data($post_id, $gp_id);
        $api_token = get_option('app_sync_api_token');
        $timeout = get_option('app_sync_timeout', 30);
        
        $endpoint_url = rtrim($site_url, '/') . '/wp-json/apps/v1/update';
        
        $response = wp_remote_post($endpoint_url, array(
            'headers' => array(
                'Content-Type'     => 'application/json',
                'X-App-Sync-Token' => $api_token,
                'User-Agent'       => 'App-Sync-Plugin/' . APP_SYNC_VERSION . ' (retry)'
            ),
            'body'        => wp_json_encode($data),
            'timeout'     => $timeout,
            'redirection' => 0,
            'blocking'    => true
        ));
        
        if (is_wp_error($response)) {
            $this->logger->log($post_id, $site_url, 'retry_sync', 'failed', 
                null, 'Retry failed: ' . $response->get_error_message());
        } else {
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);
            
            if ($response_code >= 200 && $response_code < 300) {
                $this->logger->log($post_id, $site_url, 'retry_sync', 'success', 
                    $response_code, 'Retry successful: ' . ($response_data['message'] ?? 'Sync completed'));
            } else {
                $error_message = $response_data['message'] ?? wp_remote_retrieve_response_message($response);
                $this->logger->log($post_id, $site_url, 'retry_sync', 'failed', 
                    $response_code, 'Retry failed: ' . $error_message);
            }
        }
    }
    
    private function prepare_sync_data($post_id, $gp_id) {
        $data = array(
            'wp_GP_ID'              => $gp_id,
            'wp_version_GP'         => get_post_meta($post_id, 'wp_version_GP', true),
            'wp_sizes_GP'           => get_post_meta($post_id, 'wp_sizes_GP', true),
            'wp_mods'               => get_post_meta($post_id, 'wp_mods', true),
            'wp_title_GP'           => get_post_meta($post_id, 'wp_title_GP', true),
            'wp_developers_GP'      => get_post_meta($post_id, 'wp_developers_GP', true),
            'wp_contentrated_GP'    => get_post_meta($post_id, 'wp_contentrated_GP', true),
            'wp_requires_GP'        => get_post_meta($post_id, 'wp_requires_GP', true),
            'price'                 => get_post_meta($post_id, 'price', true),
            'download_info'         => get_post_meta($post_id, 'download_info', true),
            'mod_info'              => get_post_meta($post_id, 'mod_info', true),
            'mod-tick-box'          => get_post_meta($post_id, 'mod-tick-box', true),
            'repeatable_download_link' => get_post_meta($post_id, 'repeatable_download_link', true),
            'ss_images'             => get_post_meta($post_id, 'ss_images', true),
            'title_template'        => get_post_meta($post_id, 'title_template', true)
        );
        
        // Remove empty values to reduce payload size
        return array_filter($data, function($value) {
            return !empty($value) || $value === '0' || $value === 0;
        });
    }
    
    private function update_sync_stats($post_id, $successful, $failed) {
        $stats = get_post_meta($post_id, '_app_sync_stats', true);
        if (!is_array($stats)) {
            $stats = array(
                'total_syncs' => 0,
                'successful_syncs' => 0,
                'failed_syncs' => 0,
                'last_sync' => null
            );
        }
        
        $stats['total_syncs'] += ($successful + $failed);
        $stats['successful_syncs'] += $successful;
        $stats['failed_syncs'] += $failed;
        $stats['last_sync'] = current_time('mysql');
        
        update_post_meta($post_id, '_app_sync_stats', $stats);
    }
    
    /**
     * Apply title template on child sites when meta is updated
     */
    public function apply_title_template_on_meta_update($meta_id, $post_id, $meta_key, $meta_value) {
        // Skip if master site
        if (get_option('app_sync_is_master', 'no') === 'yes') {
            return;
        }
        
        // Only trigger for relevant meta fields
        $relevant_keys = array('wp_version_GP', 'wp_title_GP', 'title_template');
        if (!in_array($meta_key, $relevant_keys)) {
            return;
        }
        
        $this->apply_title_template($post_id);
    }
    
    private function apply_title_template($post_id) {
        $template = get_post_meta($post_id, 'title_template', true);
        if (!$template) return;

        $wp_version_gp = get_post_meta($post_id, 'wp_version_GP', true);
        $wp_title_gp = get_post_meta($post_id, 'wp_title_GP', true);

        $new_title = str_replace(
            array('{wp_version_GP}', '{wp_title_GP}'),
            array($wp_version_gp, $wp_title_gp),
            $template
        );

        if (!empty($new_title) && trim($new_title) !== '') {
            // Prevent infinite loop
            remove_action('save_post', array($this, 'trigger_webhook'), 20);
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => sanitize_text_field($new_title)
            ));

            // Update Falang multi-language titles if Falang is active
            $this->update_falang_titles($post_id, $new_title, $wp_version_gp);

            add_action('save_post', array($this, 'trigger_webhook'), 20, 1);
        }
    }
    
    public function ajax_retry_failed_manual() {
        check_ajax_referer('app_sync_retry_failed', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'app-sync'));
        }

        // Trigger immediate retry of failed syncs
        $this->retry_failed_syncs();

        wp_send_json_success(array(
            'message' => __('Retry process completed. Check the sync logs for results.', 'app-sync')
        ));
    }

    /**
     * Update Falang multi-language titles with version updates
     */
    private function update_falang_titles($post_id, $default_title, $new_version) {
        // Check if Falang is active
        if (!class_exists('Falang\Core\Falang_Core') && !function_exists('falang_get_model')) {
            return;
        }

        try {
            // Get Falang model to retrieve available languages
            if (class_exists('Falang\Model\Falang_Model')) {
                $falang_model = new Falang\Model\Falang_Model();
                $languages = $falang_model->get_languages_list();
            } else {
                // Fallback if model class not available
                return;
            }

            foreach ($languages as $language) {
                if ($language->locale === $falang_model->get_default_locale()) {
                    continue; // Skip default language as it's already updated
                }

                // Get the prefix for this language (e.g., "_ru_RU_")
                $prefix = Falang\Core\Falang_Core::get_prefix($language->locale);
                $meta_key = $prefix . 'post_title';

                // Get existing translated title
                $existing_title = get_post_meta($post_id, $meta_key, true);

                if (!empty($existing_title)) {
                    // Update version in the existing translated title
                    $updated_title = $this->update_version_in_title($existing_title, $new_version);

                    if ($updated_title !== $existing_title) {
                        update_post_meta($post_id, $meta_key, sanitize_text_field($updated_title));

                        // Log the multi-language title update
                        $this->logger->log($post_id, 'falang_update', 'multilang_title_updated', 'success',
                            200, sprintf('Updated %s title: %s', $language->locale, $updated_title));
                    }
                }
            }
        } catch (Exception $e) {
            // Log error but don't break the sync process
            $this->logger->log($post_id, 'falang_error', 'multilang_update_failed', 'failed',
                500, 'Falang title update failed: ' . $e->getMessage());
        }
    }

    /**
     * Update version number in a title string
     */
    private function update_version_in_title($title, $new_version) {
        if (empty($new_version)) {
            return $title;
        }

        // Pattern to match version numbers (e.g., v1.2.3, v2.1, version 1.2.3, etc.)
        $patterns = array(
            '/\bv\d+\.\d+(?:\.\d+)*\b/i',           // v1.2.3, v1.2
            '/\bversion\s+\d+\.\d+(?:\.\d+)*\b/i',  // version 1.2.3
            '/\b\d+\.\d+(?:\.\d+)*\b/'              // 1.2.3 (standalone)
        );

        // Ensure new version has 'v' prefix
        $formatted_version = (strpos($new_version, 'v') === 0) ? $new_version : 'v' . $new_version;

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $title)) {
                return preg_replace($pattern, $formatted_version, $title, 1);
            }
        }

        // If no version pattern found, try to append the version
        // Look for common patterns where version might be added
        if (preg_match('/\b(APK|MOD|Premium)\b/i', $title)) {
            $title = preg_replace('/(\b(?:APK|MOD|Premium)\b)/i', $formatted_version . ' $1', $title, 1);
        } else {
            // As a last resort, append before common file extensions or at the end
            if (preg_match('/\.(apk|mod|zip|rar)$/i', $title)) {
                $title = preg_replace('/(\.[a-z]{3,4})$/i', ' ' . $formatted_version . '$1', $title);
            } else {
                $title .= ' ' . $formatted_version;
            }
        }

        return $title;
    }
}