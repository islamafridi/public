<?php
/**
 * REST API handler for App Sync Plugin
 */
class App_Sync_REST_API {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }
    
    public function register_routes() {
        // Get metadata endpoint
        register_rest_route('apps/v1', '/get/(?P<gp_id>[a-zA-Z0-9\.-]+)', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_metadata'),
            'permission_callback' => array($this, 'verify_api_token'),
            'args'                => array(
                'gp_id' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return !empty($param);
                    }
                )
            )
        ));

        // Update metadata endpoint
        register_rest_route('apps/v1', '/update', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'update_metadata'),
            'permission_callback' => array($this, 'verify_api_token')
        ));
        
        // Bulk update endpoint
        register_rest_route('apps/v1', '/bulk-update', array(
            'methods'             => 'POST',
            'callback'            => array($this, 'bulk_update_metadata'),
            'permission_callback' => array($this, 'verify_api_token')
        ));
        
        // Health check endpoint
        register_rest_route('apps/v1', '/health', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'health_check'),
            'permission_callback' => array($this, 'verify_api_token')
        ));
        
        // Sync logs endpoint
        register_rest_route('apps/v1', '/logs/(?P<post_id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array($this, 'get_sync_logs'),
            'permission_callback' => array($this, 'verify_api_token'),
            'args'                => array(
                'post_id' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                )
            )
        ));
    }
    
    public function verify_api_token(WP_REST_Request $request) {
        $token = $request->get_header('X-App-Sync-Token');
        $stored_token = get_option('app_sync_api_token');
        
        if (!$stored_token || !hash_equals($stored_token, $token)) {
            return new WP_Error(
                'rest_forbidden',
                __('Invalid or missing API token', 'app-sync'),
                array('status' => 403)
            );
        }
        
        return true;
    }
    
    public function get_metadata(WP_REST_Request $request) {
        $gp_id = sanitize_text_field($request['gp_id']);
        $post_id = $this->get_post_by_gp_id($gp_id);
        
        if (!$post_id) {
            return new WP_Error(
                'no_post',
                __('Post not found', 'app-sync'),
                array('status' => 404)
            );
        }

        $metadata = array(
            'post_id'               => $post_id,
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
            'title_template'        => get_post_meta($post_id, 'title_template', true),
            'last_modified'         => get_post_modified_time('U', false, $post_id),
            'content_hash'          => get_post_meta($post_id, '_app_sync_content_hash', true)
        );
        
        // Filter out empty values for cleaner response
        $metadata = array_filter($metadata, function($value) {
            return !empty($value) || $value === '0' || $value === 0;
        });

        return rest_ensure_response($metadata);
    }
    
    public function update_metadata(WP_REST_Request $request) {
        $params = $request->get_json_params();
        
        if (empty($params['wp_GP_ID'])) {
            return new WP_Error(
                'missing_gp_id',
                __('GP ID is required', 'app-sync'),
                array('status' => 400)
            );
        }
        
        $gp_id = sanitize_text_field($params['wp_GP_ID']);
        $post_id = $this->get_post_by_gp_id($gp_id);

        if (!$post_id) {
            return new WP_Error(
                'no_post',
                __('Post not found', 'app-sync'),
                array('status' => 404)
            );
        }

        // Generate content hash for change detection
        $content_hash = $this->generate_content_hash($params);
        $current_hash = get_post_meta($post_id, '_app_sync_content_hash', true);
        
        // Get force update parameter
        $force_update = $request->get_param('force_update') === 'true';
        
        // Always update if it's a manual sync or if content has actually changed
        $should_update = $force_update || empty($current_hash) || $content_hash !== $current_hash;
        
        if (!$should_update) {
            // Log the skip on child site
            $logger = new App_Sync_Logger();
            $logger->log($post_id, 'master_site', 'content_unchanged', 'info', 
                200, 'Content hash unchanged, update skipped');
            
            return rest_ensure_response(array(
                'success' => true,
                'message' => __('Content unchanged, skipping update', 'app-sync'),
                'post_id' => $post_id,
                'skipped' => true
            ));
        }

        // Store the current title template BEFORE updating any meta
        $current_title_template = get_post_meta($post_id, 'title_template', true);

        // Update core fields
        $core_fields = array(
            'wp_version_GP', 'wp_title_GP', 'wp_sizes_GP', 'wp_mods',
            'wp_developers_GP', 'wp_contentrated_GP', 'wp_requires_GP',
            'price', 'download_info'
        );
        
        foreach ($core_fields as $field) {
            if (isset($params[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($params[$field]));
            }
        }

        // Update HTML content fields
        $html_fields = array('mod_info');
        foreach ($html_fields as $field) {
            if (isset($params[$field])) {
                update_post_meta($post_id, $field, wp_kses_post($params[$field]));
            }
        }

        // Update checkbox fields
        if (isset($params['mod-tick-box'])) {
            update_post_meta($post_id, 'mod-tick-box', $params['mod-tick-box'] === 'on' ? 'on' : '');
        }

        // Update array fields with proper sanitization
        if (isset($params['repeatable_download_link']) && is_array($params['repeatable_download_link'])) {
            $sanitized_links = array();
            foreach ($params['repeatable_download_link'] as $link) {
                if (is_array($link)) {
                    $sanitized_links[] = array(
                        'download_name'     => sanitize_text_field($link['download_name'] ?? ''),
                        'download_size'     => sanitize_text_field($link['download_size'] ?? ''),
                        'download_url'      => esc_url_raw($link['download_url'] ?? ''),
                        'download_mod_info' => wp_kses_post($link['download_mod_info'] ?? ''),
                        'download_mod_note' => sanitize_text_field($link['download_mod_note'] ?? ''),
                        'download_note'     => sanitize_text_field($link['download_note'] ?? ''),
                        'download_group'    => sanitize_text_field($link['download_group'] ?? 'Default')
                    );
                }
            }
            update_post_meta($post_id, 'repeatable_download_link', $sanitized_links);
        }

        if (isset($params['ss_images']) && is_array($params['ss_images'])) {
            $sanitized_screenshots = array();
            foreach ($params['ss_images'] as $screenshot) {
                if (is_array($screenshot)) {
                    $sanitized_screenshots[] = array(
                        'ss_url' => esc_url_raw($screenshot['ss_url'] ?? '')
                    );
                }
            }
            update_post_meta($post_id, 'ss_images', $sanitized_screenshots);
        }

        // Handle title template: Only update if provided and child site doesn't have one
        if (isset($params['title_template']) && !empty($params['title_template'])) {
            if (empty($current_title_template)) {
                update_post_meta($post_id, 'title_template', sanitize_text_field($params['title_template']));
            }
        }

        // Apply title template using the child site's template
        $template_to_use = !empty($current_title_template) ? $current_title_template : ($params['title_template'] ?? '');

        $post_update_data = array('ID' => $post_id);

        if (!empty($template_to_use)) {
            $new_title = str_replace(
                array('{wp_version_GP}', '{wp_title_GP}'),
                array($params['wp_version_GP'] ?? '', $params['wp_title_GP'] ?? ''),
                $template_to_use
            );

            if (!empty($new_title)) {
                $post_update_data['post_title'] = sanitize_text_field($new_title);

                // Update Falang multi-language titles if Falang is active
                $this->update_falang_titles($post_id, $new_title, $params['wp_version_GP'] ?? '');
            }
        }
        
        // IMPORTANT: Always update post_modified date to show in recent posts
        $post_update_data['post_modified'] = current_time('mysql');
        $post_update_data['post_modified_gmt'] = current_time('mysql', true);
        
        // Update the post (this will also update the modified date)
        wp_update_post($post_update_data);
        
        // Update content hash and last sync time
        update_post_meta($post_id, '_app_sync_content_hash', $content_hash);
        update_post_meta($post_id, '_app_sync_last_sync', current_time('timestamp'));

        // Log the sync on child site
        $logger = new App_Sync_Logger();
        $logger->log($post_id, 'master_site', 'data_updated', 'success', 
            200, sprintf('Post updated successfully. Title: %s', get_the_title($post_id)));

        return rest_ensure_response(array(
            'success' => true,
            'message' => __('Metadata updated successfully', 'app-sync'),
            'post_id' => $post_id,
            'content_hash' => $content_hash,
            'updated' => true
        ));
    }
    
    public function bulk_update_metadata(WP_REST_Request $request) {
        $params = $request->get_json_params();
        
        if (empty($params['updates']) || !is_array($params['updates'])) {
            return new WP_Error(
                'invalid_updates',
                __('Updates array is required', 'app-sync'),
                array('status' => 400)
            );
        }
        
        $results = array();
        $successful = 0;
        $failed = 0;
        
        foreach ($params['updates'] as $update) {
            $fake_request = new WP_REST_Request('POST', '/apps/v1/update');
            $fake_request->set_body(wp_json_encode($update));
            
            $result = $this->update_metadata($fake_request);
            
            if (is_wp_error($result)) {
                $failed++;
                $results[] = array(
                    'gp_id' => $update['wp_GP_ID'] ?? 'unknown',
                    'success' => false,
                    'error' => $result->get_error_message()
                );
            } else {
                $successful++;
                $results[] = array(
                    'gp_id' => $update['wp_GP_ID'] ?? 'unknown',
                    'success' => true,
                    'post_id' => $result->data['post_id'] ?? null
                );
            }
        }
        
        return rest_ensure_response(array(
            'total' => count($params['updates']),
            'successful' => $successful,
            'failed' => $failed,
            'results' => $results
        ));
    }
    
    public function health_check(WP_REST_Request $request) {
        $health = array(
            'status' => 'healthy',
            'timestamp' => current_time('mysql'),
            'site_type' => get_option('app_sync_is_master', 'no') === 'yes' ? 'master' : 'child',
            'plugin_version' => APP_SYNC_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time')
        );
        
        // Check database connectivity
        global $wpdb;
        $db_check = $wpdb->get_var("SELECT 1");
        $health['database'] = $db_check === '1' ? 'connected' : 'error';
        
        // Check if sync logs table exists
        $table_name = $wpdb->prefix . 'app_sync_logs';
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        ));
        $health['sync_logs_table'] = $table_exists ? 'exists' : 'missing';
        
        // Check API token
        $api_token = get_option('app_sync_api_token');
        $health['api_token'] = !empty($api_token) ? 'set' : 'missing';
        
        // Check child sites (if master)
        if (get_option('app_sync_is_master', 'no') === 'yes') {
            $child_sites = array_filter(array_map('trim', explode("\n", get_option('app_sync_child_sites', ''))));
            $health['child_sites_count'] = count($child_sites);
        }
        
        // Check recent sync activity
        if ($table_exists) {
            $recent_syncs = $wpdb->get_var(
                "SELECT COUNT(*) FROM $table_name WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            );
            $health['recent_syncs_24h'] = intval($recent_syncs);
            
            $failed_syncs = $wpdb->get_var(
                "SELECT COUNT(*) FROM $table_name WHERE status = 'failed' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            );
            $health['failed_syncs_24h'] = intval($failed_syncs);
        }
        
        return rest_ensure_response($health);
    }
    
    public function get_sync_logs(WP_REST_Request $request) {
        global $wpdb;
        
        $post_id = intval($request['post_id']);
        $limit = min(intval($request->get_param('limit') ?: 50), 100);
        $offset = max(intval($request->get_param('offset') ?: 0), 0);
        $status = sanitize_text_field($request->get_param('status') ?: '');
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        // Build WHERE clause
        $where_conditions = array($wpdb->prepare('post_id = %d', $post_id));
        if (!empty($status)) {
            $where_conditions[] = $wpdb->prepare('status = %s', $status);
        }
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        
        // Get logs
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name $where_clause ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        ));
        
        // Get total count
        $total = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name $where_clause"
        );
        
        return rest_ensure_response(array(
            'logs' => $logs,
            'total' => intval($total),
            'limit' => $limit,
            'offset' => $offset
        ));
    }
    
    private function get_post_by_gp_id($gp_id) {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'wp_GP_ID' AND meta_value = %s",
                $gp_id
            )
        );
    }
    
    private function generate_content_hash($data) {
        // Remove dynamic fields that shouldn't affect the hash
        $hash_data = $data;
        unset($hash_data['title_template']);
        unset($hash_data['_app_sync_last_sync']);
        unset($hash_data['_app_sync_content_hash']);

        return md5(wp_json_encode($hash_data));
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
                        $logger = new App_Sync_Logger();
                        $logger->log($post_id, 'falang_update', 'multilang_title_updated', 'success',
                            200, sprintf('Updated %s title: %s', $language->locale, $updated_title));
                    }
                }
            }
        } catch (Exception $e) {
            // Log error but don't break the sync process
            $logger = new App_Sync_Logger();
            $logger->log($post_id, 'falang_error', 'multilang_update_failed', 'failed',
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