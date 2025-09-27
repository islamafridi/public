<?php
/**
 * Metabox handler for title templates and sync status
 */
class App_Sync_Metabox {
    
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_metaboxes'));
        add_action('save_post', array($this, 'save_title_template'), 10, 2);
        add_action('updated_post_meta', array($this, 'update_title_from_meta'), 10, 4);
        add_action('added_post_meta', array($this, 'update_title_from_meta'), 10, 4);
        add_action('wp_ajax_app_sync_test_template', array($this, 'ajax_test_template'));
        add_action('wp_ajax_app_sync_manual_sync', array($this, 'ajax_manual_sync'));
    }
    
    public function add_metaboxes() {
        // Add title template metabox only on child sites
        if (get_option('app_sync_is_master', 'no') === 'no') {
            add_meta_box(
                'app_sync_title_template',
                __('Title Template', 'app-sync'),
                array($this, 'title_template_metabox'),
                'post',
                'normal',
                'high'
            );
        }
        
        // Add sync status metabox on master sites
        if (get_option('app_sync_is_master', 'no') === 'yes') {
            add_meta_box(
                'app_sync_status',
                __('Sync Status', 'app-sync'),
                array($this, 'sync_status_metabox'),
                'post',
                'side',
                'high'
            );
        }
        
        // Add sync info metabox on all sites
        add_meta_box(
            'app_sync_info',
            __('App Sync Info', 'app-sync'),
            array($this, 'sync_info_metabox'),
            'post',
            'side',
            'default'
        );
    }
    
    public function title_template_metabox($post) {
        $title_template = get_post_meta($post->ID, 'title_template', true);
        $wp_version_gp = get_post_meta($post->ID, 'wp_version_GP', true);
        $wp_title_gp = get_post_meta($post->ID, 'wp_title_GP', true);
        
        wp_nonce_field('app_sync_title_nonce', 'app_sync_title_nonce');
        ?>
        <div class="app-sync-template-container">
            <div class="field-group">
                <label for="title_template"><strong><?php _e('Title Template', 'app-sync'); ?></strong></label>
                <input type="text" id="title_template" name="title_template" 
                       value="<?php echo esc_attr($title_template); ?>" 
                       class="large-text" 
                       placeholder="<?php esc_attr_e('e.g., {wp_title_GP} v{wp_version_GP} MOD APK', 'app-sync'); ?>">
                <p class="description">
                    <?php _e('Use <code>{wp_version_GP}</code> for version number and <code>{wp_title_GP}</code> for app name.', 'app-sync'); ?><br>
                    <?php _e('Example: "Toca Boca World {wp_version_GP} APK MOD" or "{wp_title_GP} MOD APK v{wp_version_GP} (Free Craft)"', 'app-sync'); ?>
                </p>
            </div>
            
            <div class="template-actions">
                <button type="button" id="test-template" class="button button-secondary">
                    <?php _e('Test Template', 'app-sync'); ?>
                </button>
                <div id="template-preview" style="margin-top: 10px; display: none;">
                    <strong><?php _e('Preview:', 'app-sync'); ?></strong> 
                    <span id="preview-text"></span>
                </div>
            </div>
            
            <div class="current-values" style="margin-top: 15px; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa;">
                <h4><?php _e('Current Values:', 'app-sync'); ?></h4>
                <p><strong>wp_title_GP:</strong> <?php echo esc_html($wp_title_gp ?: __('Not set', 'app-sync')); ?></p>
                <p><strong>wp_version_GP:</strong> <?php echo esc_html($wp_version_gp ?: __('Not set', 'app-sync')); ?></p>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#test-template').on('click', function() {
                var template = $('#title_template').val();
                if (!template) {
                    alert('<?php _e('Please enter a template first', 'app-sync'); ?>');
                    return;
                }
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'app_sync_test_template',
                        template: template,
                        post_id: <?php echo $post->ID; ?>,
                        nonce: '<?php echo wp_create_nonce('app_sync_test_template'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#preview-text').text(response.data.preview);
                            $('#template-preview').show();
                        } else {
                            alert(response.data);
                        }
                    }
                });
            });
            
            $('#title_template').on('input', function() {
                $('#template-preview').hide();
            });
        });
        </script>
        <?php
    }
    
    public function sync_status_metabox($post) {
        $gp_id = get_post_meta($post->ID, 'wp_GP_ID', true);
        
        if (!$gp_id) {
            echo '<p>' . __('No GP ID set. Sync unavailable.', 'app-sync') . '</p>';
            return;
        }
        
        // Get recent sync logs
        $logs = $this->get_sync_logs($post->ID, 5);
        
        ?>
        <div class="sync-status-container">
            <div class="sync-actions">
                <button type="button" id="manual-sync" class="button button-primary" style="width: 100%;">
                    <?php _e('Sync Now', 'app-sync'); ?>
                </button>
            </div>
            
            <div id="sync-results" style="margin-top: 10px;"></div>
            
            <?php if (!empty($logs)): ?>
            <div class="sync-history" style="margin-top: 15px;">
                <h4><?php _e('Recent Sync History', 'app-sync'); ?></h4>
                <div class="sync-log-entries">
                    <?php foreach ($logs as $log): ?>
                    <div class="sync-entry sync-<?php echo esc_attr($log->status); ?>" style="padding: 8px; margin: 5px 0; border-left: 3px solid; background: #f9f9f9;">
                        <div class="sync-site"><?php echo esc_html(parse_url($log->site_url, PHP_URL_HOST)); ?></div>
                        <div class="sync-status-badge">
                            <span class="status-<?php echo esc_attr($log->status); ?>">
                                <?php echo esc_html(ucfirst($log->status)); ?>
                            </span>
                            <span class="sync-time"><?php echo human_time_diff(strtotime($log->created_at), current_time('timestamp')) . ' ' . __('ago', 'app-sync'); ?></span>
                        </div>
                        <?php if ($log->response_message && $log->status === 'failed'): ?>
                        <div class="sync-error" style="font-size: 12px; color: #d63638;">
                            <?php echo esc_html($log->response_message); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#manual-sync').on('click', function() {
                var $btn = $(this);
                var $results = $('#sync-results');
                
                $btn.prop('disabled', true).text('<?php _e('Syncing...', 'app-sync'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'app_sync_manual_sync',
                        post_id: <?php echo $post->ID; ?>,
                        nonce: '<?php echo wp_create_nonce('app_sync_manual_sync'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $results.html('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $results.html('<div class="notice notice-error inline"><p>' + response.data + '</p></div>');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('<?php _e('Sync Now', 'app-sync'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public function sync_info_metabox($post) {
        $gp_id = get_post_meta($post->ID, 'wp_GP_ID', true);
        $last_sync = get_post_meta($post->ID, '_app_sync_last_sync', true);
        $sync_hash = get_post_meta($post->ID, '_app_sync_content_hash', true);
        
        ?>
        <div class="sync-info">
            <p><strong><?php _e('GP ID:', 'app-sync'); ?></strong> <?php echo esc_html($gp_id ?: __('Not set', 'app-sync')); ?></p>
            <?php if ($last_sync): ?>
            <p><strong><?php _e('Last Sync:', 'app-sync'); ?></strong> 
                <?php echo human_time_diff($last_sync, current_time('timestamp')) . ' ' . __('ago', 'app-sync'); ?>
            </p>
            <?php endif; ?>
            <p><strong><?php _e('Site Type:', 'app-sync'); ?></strong> 
                <?php echo get_option('app_sync_is_master', 'no') === 'yes' ? __('Master', 'app-sync') : __('Child', 'app-sync'); ?>
            </p>
        </div>
        <?php
    }
    
    public function save_title_template($post_id, $post) {
        // Security checks
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!isset($_POST['app_sync_title_nonce']) || !wp_verify_nonce($_POST['app_sync_title_nonce'], 'app_sync_title_nonce')) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if ($post->post_type !== 'post') return;
        
        // Save the title template
        if (isset($_POST['title_template'])) {
            $title_template = sanitize_text_field($_POST['title_template']);
            $old_template = get_post_meta($post_id, 'title_template', true);
            
            if ($title_template !== $old_template) {
                update_post_meta($post_id, 'title_template', $title_template);
                
                // Apply template immediately if we have the required data
                $this->apply_title_template($post_id, $title_template);
            }
        }
    }
    
    public function update_title_from_meta($meta_id, $post_id, $meta_key, $meta_value) {
        // Skip if master site
        if (get_option('app_sync_is_master', 'no') === 'yes') {
            return;
        }
        
        // Only trigger for relevant meta fields
        $relevant_keys = array('wp_version_GP', 'wp_title_GP');
        if (!in_array($meta_key, $relevant_keys)) {
            return;
        }
        
        $title_template = get_post_meta($post_id, 'title_template', true);
        if (!empty($title_template)) {
            $this->apply_title_template($post_id, $title_template);
        }
    }
    
    private function apply_title_template($post_id, $template) {
        if (empty($template)) return;
        
        $wp_version_gp = get_post_meta($post_id, 'wp_version_GP', true);
        $wp_title_gp = get_post_meta($post_id, 'wp_title_GP', true);
        
        $new_title = str_replace(
            array('{wp_version_GP}', '{wp_title_GP}'),
            array($wp_version_gp, $wp_title_gp),
            $template
        );
        
        if (!empty($new_title) && trim($new_title) !== '') {
            // Prevent infinite loop
            remove_action('save_post', array($this, 'save_title_template'), 10);
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => sanitize_text_field($new_title)
            ));
            add_action('save_post', array($this, 'save_title_template'), 10, 2);
        }
    }
    
    public function ajax_test_template() {
        check_ajax_referer('app_sync_test_template', 'nonce');
        
        $template = sanitize_text_field($_POST['template']);
        $post_id = intval($_POST['post_id']);
        
        if (!current_user_can('edit_post', $post_id)) {
            wp_die(__('Permission denied', 'app-sync'));
        }
        
        $wp_version_gp = get_post_meta($post_id, 'wp_version_GP', true);
        $wp_title_gp = get_post_meta($post_id, 'wp_title_GP', true);
        
        $preview = str_replace(
            array('{wp_version_GP}', '{wp_title_GP}'),
            array($wp_version_gp ?: '[Version]', $wp_title_gp ?: '[App Name]'),
            $template
        );
        
        wp_send_json_success(array('preview' => $preview));
    }
    
    public function ajax_manual_sync() {
        check_ajax_referer('app_sync_manual_sync', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(__('Permission denied', 'app-sync'));
        }
        
        // Trigger sync manually
        do_action('app_sync_manual_trigger', $post_id);
        
        wp_send_json_success(array(
            'message' => __('Sync initiated successfully. Check the sync history for results.', 'app-sync')
        ));
    }
    
    private function get_sync_logs($post_id, $limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE post_id = %d ORDER BY created_at DESC LIMIT %d",
            $post_id,
            $limit
        ));
    }
}