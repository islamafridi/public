<?php
/**
 * Settings page handler for App Sync Plugin
 */
class App_Sync_Settings {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_app_sync_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_app_sync_clear_logs', array($this, 'ajax_clear_logs'));
        add_action('wp_ajax_app_sync_regenerate_token', array($this, 'ajax_regenerate_token'));
    }
    
    public function add_settings_menu() {
        add_options_page(
            __('App Sync Settings', 'app-sync'),
            __('App Sync', 'app-sync'),
            'manage_options',
            'app-sync',
            array($this, 'settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('app_sync_settings', 'app_sync_is_master');
        register_setting('app_sync_settings', 'app_sync_child_sites');
        register_setting('app_sync_settings', 'app_sync_api_token');
        register_setting('app_sync_settings', 'app_sync_sync_frequency');
        register_setting('app_sync_settings', 'app_sync_log_retention');
        register_setting('app_sync_settings', 'app_sync_retry_attempts');
        register_setting('app_sync_settings', 'app_sync_timeout');
    }
    
    public function settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'app-sync'));
        }
        
        $is_master = get_option('app_sync_is_master', 'no');
        $child_sites = get_option('app_sync_child_sites', '');
        $api_token = get_option('app_sync_api_token', '');
        $sync_frequency = get_option('app_sync_sync_frequency', 'immediate');
        $log_retention = get_option('app_sync_log_retention', 30);
        $retry_attempts = get_option('app_sync_retry_attempts', 3);
        $timeout = get_option('app_sync_timeout', 30);

        // Handle main settings form submission ONLY
        if (isset($_POST['submit']) && check_admin_referer('app_sync_settings_nonce')) {
            $this->handle_form_submission();
            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully.', 'app-sync') . '</p></div>';
            
            // Refresh values after save
            $is_master = get_option('app_sync_is_master', 'no');
            $child_sites = get_option('app_sync_child_sites', '');
            $api_token = get_option('app_sync_api_token', '');
            $sync_frequency = get_option('app_sync_sync_frequency', 'immediate');
            $log_retention = get_option('app_sync_log_retention', 30);
            $retry_attempts = get_option('app_sync_retry_attempts', 3);
            $timeout = get_option('app_sync_timeout', 30);
        }
        
        // Get sync statistics
        $sync_stats = $this->get_sync_statistics();
        
        ?>
        <div class="wrap">
            <h1><?php _e('App Sync Settings', 'app-sync'); ?></h1>
            
            <div class="app-sync-settings-container">
                <div class="main-settings">
                    <form method="post" id="app-sync-settings-form">
                        <?php wp_nonce_field('app_sync_settings_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Site Type', 'app-sync'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="is_master" value="yes" <?php checked($is_master, 'yes'); ?> id="is-master-checkbox">
                                        <?php _e('This is the Master Site', 'app-sync'); ?>
                                    </label>
                                    <p class="description">
                                        <?php _e('Master sites push updates to child sites. Child sites receive updates from master sites.', 'app-sync'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr id="sync-frequency-row" <?php echo $is_master === 'no' ? 'style="display:none;"' : ''; ?>>
                                <th scope="row"><label for="sync_frequency"><?php _e('Sync Frequency', 'app-sync'); ?></label></th>
                                <td>
                                    <select id="sync_frequency" name="sync_frequency">
                                        <option value="immediate" <?php selected($sync_frequency, 'immediate'); ?>><?php _e('Immediate (on save)', 'app-sync'); ?></option>
                                        <option value="manual" <?php selected($sync_frequency, 'manual'); ?>><?php _e('Manual only', 'app-sync'); ?></option>
                                    </select>
                                    <p class="description"><?php _e('Choose when to sync data to child sites.', 'app-sync'); ?></p>
                                </td>
                            </tr>
                            
                            <tr id="child-sites-row" <?php echo $is_master === 'no' ? 'style="display:none;"' : ''; ?>>
                                <th scope="row"><label for="child_sites"><?php _e('Child Site URLs', 'app-sync'); ?></label></th>
                                <td>
                                    <textarea id="child_sites" name="child_sites" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($child_sites); ?></textarea>
                                    <p class="description">
                                        <?php _e('Enter one URL per line (e.g., https://child1.com)', 'app-sync'); ?><br>
                                        <button type="button" id="test-connections" class="button button-secondary" style="margin-top: 5px;">
                                            <?php _e('Test All Connections', 'app-sync'); ?>
                                        </button>
                                    </p>
                                    <div id="connection-test-results" style="margin-top: 10px;"></div>
                                </td>
                            </tr>
                            
                            <tr id="master-token-row" <?php echo $is_master === 'no' ? 'style="display:none;"' : ''; ?>>
                                <th scope="row"><?php _e('API Token', 'app-sync'); ?></th>
                                <td>
                                    <div class="token-section">
                                        <div class="token-display-wrapper">
                                            <input type="text" 
                                                   id="api-token-input" 
                                                   value="<?php echo esc_attr($api_token ?: ''); ?>" 
                                                   readonly 
                                                   class="large-text code token-input"
                                                   placeholder="<?php esc_attr_e('No token generated yet', 'app-sync'); ?>"
                                                   style="background: #ffffff; border: 2px solid #dee2e6; font-family: 'SF Mono', Monaco, 'Cascadia Code', monospace;">
                                            <button type="button" 
                                                    id="copy-token-btn" 
                                                    class="button button-secondary copy-btn" 
                                                    data-token="<?php echo esc_attr($api_token); ?>"
                                                    <?php echo empty($api_token) ? 'disabled' : ''; ?>>>
                                                <span class="dashicons dashicons-admin-page"></span>
                                                <?php _e('Copy', 'app-sync'); ?>
                                            </button>
                                        </div>
                                        
                                        <div class="token-actions">
                                            <button type="button" 
                                                    id="generate-token-btn"
                                                    class="button button-primary token-generate-btn"
                                                    data-master="<?php echo $is_master; ?>">
                                                <?php echo empty($api_token) ? __('Generate Token', 'app-sync') : __('Generate New Token', 'app-sync'); ?>
                                            </button>
                                        </div>
                                        
                                        <div class="token-info">
                                            <p class="description">
                                                <strong><?php _e('Instructions:', 'app-sync'); ?></strong><br>
                                                1. <?php _e('Generate or copy the token above', 'app-sync'); ?><br>
                                                2. <?php _e('Go to each child site\'s App Sync settings', 'app-sync'); ?><br>
                                                3. <?php _e('Paste the token in the "API Token" field', 'app-sync'); ?><br>
                                                4. <?php _e('Save the settings to enable synchronization', 'app-sync'); ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr id="child-token-row" <?php echo $is_master === 'yes' ? 'style="display:none;"' : ''; ?>>
                                <th scope="row"><label for="api_token"><?php _e('API Token', 'app-sync'); ?></label></th>
                                <td>
                                    <input type="text" id="api_token" name="api_token" value="<?php echo esc_attr($api_token); ?>" class="regular-text" placeholder="<?php esc_attr_e('Enter token from master site', 'app-sync'); ?>">
                                    <p class="description">
                                        <?php _e('Enter the API token generated on the master site to enable sync.', 'app-sync'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><label for="timeout"><?php _e('Request Timeout', 'app-sync'); ?></label></th>
                                <td>
                                    <input type="number" id="timeout" name="timeout" value="<?php echo esc_attr($timeout); ?>" min="10" max="120" class="small-text">
                                    <?php _e('seconds', 'app-sync'); ?>
                                    <p class="description"><?php _e('Maximum time to wait for sync requests to complete.', 'app-sync'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><label for="retry_attempts"><?php _e('Retry Attempts', 'app-sync'); ?></label></th>
                                <td>
                                    <input type="number" id="retry_attempts" name="retry_attempts" value="<?php echo esc_attr($retry_attempts); ?>" min="0" max="10" class="small-text">
                                    <p class="description"><?php _e('Number of times to retry failed sync attempts.', 'app-sync'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><label for="log_retention"><?php _e('Log Retention', 'app-sync'); ?></label></th>
                                <td>
                                    <input type="number" id="log_retention" name="log_retention" value="<?php echo esc_attr($log_retention); ?>" min="1" max="365" class="small-text">
                                    <?php _e('days', 'app-sync'); ?>
                                    <p class="description"><?php _e('How long to keep sync logs before automatic cleanup.', 'app-sync'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <?php submit_button(__('Save Settings', 'app-sync')); ?>
                    </form>
                </div>
                
                <div class="sidebar-info">
                    <div class="postbox">
                        <h3 class="hndle"><span><?php _e('Sync Statistics', 'app-sync'); ?></span></h3>
                        <div class="inside">
                            <table class="widefat">
                                <tbody>
                                    <tr>
                                        <td><strong><?php _e('Total Posts with GP ID:', 'app-sync'); ?></strong></td>
                                        <td><?php echo number_format($sync_stats['total_posts']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e('Successful Syncs (24h):', 'app-sync'); ?></strong></td>
                                        <td style="color: #46b450;"><?php echo number_format($sync_stats['successful_24h']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e('Failed Syncs (24h):', 'app-sync'); ?></strong></td>
                                        <td style="color: #dc3232;"><?php echo number_format($sync_stats['failed_24h']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong><?php _e('Total Log Entries:', 'app-sync'); ?></strong></td>
                                        <td><?php echo number_format($sync_stats['total_logs']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <p style="margin-top: 15px;">
                                <button type="button" id="clear-logs" class="button button-secondary" style="width: 100%;">
                                    <?php _e('Clear All Logs', 'app-sync'); ?>
                                </button>
                            </p>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h3 class="hndle"><span><?php _e('Plugin Information', 'app-sync'); ?></span></h3>
                        <div class="inside">
                            <p><strong><?php _e('Version:', 'app-sync'); ?></strong> <?php echo APP_SYNC_VERSION; ?></p>
                            <p><strong><?php _e('Database Version:', 'app-sync'); ?></strong> <?php echo get_option('app_sync_version', '1.0'); ?></p>
                            <p><strong><?php _e('WordPress Version:', 'app-sync'); ?></strong> <?php echo get_bloginfo('version'); ?></p>
                            <p><strong><?php _e('PHP Version:', 'app-sync'); ?></strong> <?php echo PHP_VERSION; ?></p>
                        </div>
                    </div>
                    
                    <div class="postbox">
                        <h3 class="hndle"><span><?php _e('Recent Sync Activity', 'app-sync'); ?></span></h3>
                        <div class="inside">
                            <?php $this->display_recent_activity(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Toggle master/child specific options
            $('#is-master-checkbox').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#sync-frequency-row, #child-sites-row, #master-token-row').show();
                    $('#child-token-row').hide();
                } else {
                    $('#sync-frequency-row, #child-sites-row, #master-token-row').hide();
                    $('#child-token-row').show();
                }
            });
            
            // Enhanced Token Generation via AJAX
            $('#generate-token-btn').on('click', function(e) {
                e.preventDefault();
                
                var $btn = $(this);
                var isMaster = $btn.data('master');
                
                if (isMaster !== 'yes') {
                    alert('<?php _e('Only master sites can generate tokens.', 'app-sync'); ?>');
                    return;
                }
                
                if (!confirm('<?php _e('This will create a new token. You will need to update all child sites. Continue?', 'app-sync'); ?>')) {
                    return;
                }
                
                // Show loading state
                var originalText = $btn.text();
                $btn.prop('disabled', true).text('<?php _e('Generating...', 'app-sync'); ?>');
                
                $.ajax({
                    url: appSyncAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'app_sync_regenerate_token',
                        nonce: appSyncAjax.regenerate_token_nonce
                    },
                    timeout: 10000,
                    success: function(response) {
                        if (response.success && response.data.token) {
                            // Update the token input
                            $('#api-token-input').val(response.data.token);
                            
                            // Enable copy button
                            $('#copy-token-btn').prop('disabled', false).data('token', response.data.token);
                            
                            // Show success message
                            $('<div class="notice notice-success is-dismissible"><p><strong><?php _e('Success!', 'app-sync'); ?></strong> <?php _e('New API token generated. Please update all child sites with this new token.', 'app-sync'); ?></p></div>').insertAfter('.wrap h1');
                            
                            // Update button text
                            $btn.text('<?php _e('Generate New Token', 'app-sync'); ?>');
                            
                        } else {
                            alert(response.data || '<?php _e('Failed to generate token. Please try again.', 'app-sync'); ?>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Token generation failed:', error);
                        alert('<?php _e('Network error. Please check your connection and try again.', 'app-sync'); ?>');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                        if ($btn.text() === '<?php _e('Generating...', 'app-sync'); ?>') {
                            $btn.text(originalText);
                        }
                    }
                });
            });
            
            // Enhanced Copy Token Function
            $(document).on('click', '#copy-token-btn', function(e) {
                e.preventDefault();
                
                var $btn = $(this);
                var token = $('#api-token-input').val().trim();
                
                // Check if token exists
                if (!token || token === '') {
                    alert('<?php _e('No token available. Please generate a token first.', 'app-sync'); ?>');
                    return;
                }
                
                // Disable button during copy
                $btn.prop('disabled', true);
                
                // Create a temporary textarea for copying
                var tempTextarea = document.createElement('textarea');
                tempTextarea.value = token;
                tempTextarea.style.position = 'absolute';
                tempTextarea.style.left = '-9999px';
                tempTextarea.style.top = '0';
                document.body.appendChild(tempTextarea);
                
                try {
                    // Select and copy
                    tempTextarea.select();
                    tempTextarea.setSelectionRange(0, 99999); // For mobile
                    
                    var successful = false;
                    
                    // Try modern clipboard API first
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(token).then(function() {
                            showCopySuccess($btn);
                        }).catch(function() {
                            // Fallback to execCommand
                            successful = document.execCommand('copy');
                            if (successful) {
                                showCopySuccess($btn);
                            } else {
                                copyFailed($btn);
                            }
                        });
                    } else {
                        // Use execCommand for older browsers
                        successful = document.execCommand('copy');
                        if (successful) {
                            showCopySuccess($btn);
                        } else {
                            copyFailed($btn);
                        }
                    }
                } catch (err) {
                    console.error('Copy failed:', err);
                    copyFailed($btn);
                }
                
                // Clean up
                document.body.removeChild(tempTextarea);
            });
            
            // Auto-select token when clicking on input
            $('#api-token-input').on('click focus', function() {
                this.select();
            });
            
            // Test connections
            $('#test-connections').on('click', function() {
                var $btn = $(this);
                var $results = $('#connection-test-results');
                var sites = $('#child_sites').val().trim().split('\n').filter(function(site) {
                    return site.trim() !== '';
                });
                
                if (sites.length === 0) {
                    alert('<?php _e('Please enter at least one child site URL.', 'app-sync'); ?>');
                    return;
                }
                
                $btn.prop('disabled', true).text('<?php _e('Testing...', 'app-sync'); ?>');
                $results.html('<div class="notice notice-info inline"><p><?php _e('Testing connections...', 'app-sync'); ?></p></div>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'app_sync_test_connection',
                        sites: sites,
                        nonce: '<?php echo wp_create_nonce('app_sync_test_connection'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            var html = '<div class="connection-results">';
                            $.each(response.data.results, function(index, result) {
                                var status_class = result.success ? 'notice-success' : 'notice-error';
                                var status_text = result.success ? '✓' : '✗';
                                html += '<div class="notice ' + status_class + ' inline" style="margin: 5px 0; padding: 8px;"><p>' + 
                                       '<strong>' + status_text + ' ' + result.site + '</strong><br>' + 
                                       result.message + '</p></div>';
                            });
                            html += '</div>';
                            $results.html(html);
                        } else {
                            $results.html('<div class="notice notice-error inline"><p>' + response.data + '</p></div>');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('<?php _e('Test All Connections', 'app-sync'); ?>');
                    }
                });
            });
            
            // Clear logs
            $('#clear-logs').on('click', function() {
                if (!confirm('<?php _e('Are you sure you want to clear all sync logs? This action cannot be undone.', 'app-sync'); ?>')) {
                    return;
                }
                
                var $btn = $(this);
                $btn.prop('disabled', true).text('<?php _e('Clearing...', 'app-sync'); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'app_sync_clear_logs',
                        nonce: '<?php echo wp_create_nonce('app_sync_clear_logs'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('Logs cleared successfully.', 'app-sync'); ?>');
                            location.reload();
                        } else {
                            alert(response.data);
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text('<?php _e('Clear All Logs', 'app-sync'); ?>');
                    }
                });
            });
            
            // Show copy success feedback
            function showCopySuccess($btn) {
                var originalHtml = $btn.html();
                $btn.html('<span class="dashicons dashicons-yes" style="color: #ffffff;"></span> <?php _e('Copied!', 'app-sync'); ?>');
                $btn.css('background-color', '#46b450');
                
                setTimeout(function() {
                    $btn.html(originalHtml);
                    $btn.css('background-color', '');
                    $btn.prop('disabled', false);
                }, 2000);
            }
            
            // Handle copy failure
            function copyFailed($btn) {
                alert('<?php _e('Failed to copy token. Please manually select and copy the token from the field above.', 'app-sync'); ?>');
                $btn.prop('disabled', false);
                $('#api-token-input').focus().select();
            }
        });
        </script>
        <?php
    }
    
    public function ajax_regenerate_token() {
        // Check nonce
        if (!wp_verify_nonce($_POST['nonce'], 'app_sync_regenerate_token')) {
            wp_send_json_error(__('Security check failed.', 'app-sync'));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied.', 'app-sync'));
        }
        
        // Check if master site
        if (get_option('app_sync_is_master', 'no') !== 'yes') {
            wp_send_json_error(__('Only master sites can generate tokens.', 'app-sync'));
        }
        
        // Generate new token
        $new_token = bin2hex(random_bytes(32));
        update_option('app_sync_api_token', $new_token);
        
        wp_send_json_success(array(
            'token' => $new_token,
            'message' => __('Token generated successfully.', 'app-sync')
        ));
    }
    
    private function handle_form_submission() {
        $is_master = isset($_POST['is_master']) ? 'yes' : 'no';
        update_option('app_sync_is_master', $is_master);
        
        // Update sync frequency
        if (isset($_POST['sync_frequency'])) {
            update_option('app_sync_sync_frequency', sanitize_text_field($_POST['sync_frequency']));
        }
        
        // Update timeout and retry settings
        if (isset($_POST['timeout'])) {
            update_option('app_sync_timeout', max(10, min(120, intval($_POST['timeout']))));
        }
        
        if (isset($_POST['retry_attempts'])) {
            update_option('app_sync_retry_attempts', max(0, min(10, intval($_POST['retry_attempts']))));
        }
        
        if (isset($_POST['log_retention'])) {
            update_option('app_sync_log_retention', max(1, min(365, intval($_POST['log_retention']))));
        }
        
        if ($is_master === 'yes') {
            // Master site settings
            update_option('app_sync_child_sites', sanitize_textarea_field($_POST['child_sites']));
            
            // Generate token if empty
            if (empty(get_option('app_sync_api_token'))) {
                update_option('app_sync_api_token', bin2hex(random_bytes(32)));
            }
        } else {
            // Child site settings
            if (isset($_POST['api_token'])) {
                update_option('app_sync_api_token', sanitize_text_field($_POST['api_token']));
            }
        }
    }
    
    public function ajax_test_connection() {
        check_ajax_referer('app_sync_test_connection', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'app-sync'));
        }
        
        $sites = $_POST['sites'];
        $api_token = get_option('app_sync_api_token');
        
        if (empty($api_token)) {
            wp_send_json_error(__('API token not configured', 'app-sync'));
        }
        
        $results = array();
        
        foreach ($sites as $site) {
            $site = trim($site);
            if (empty($site)) continue;
            
            $url = rtrim($site, '/') . '/wp-json/apps/v1/health';
            
            $response = wp_remote_get($url, array(
                'headers' => array(
                    'X-App-Sync-Token' => $api_token,
                    'User-Agent' => 'App-Sync-Plugin/' . APP_SYNC_VERSION . ' (connection-test)'
                ),
                'timeout' => 15,
                'redirection' => 0
            ));
            
            if (is_wp_error($response)) {
                $results[] = array(
                    'site' => $site,
                    'success' => false,
                    'message' => $response->get_error_message()
                );
            } else {
                $code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
                
                if ($code === 200 && isset($data['status'])) {
                    $results[] = array(
                        'site' => $site,
                        'success' => true,
                        'message' => sprintf(
                            __('Connected successfully. Site type: %s, Plugin version: %s', 'app-sync'),
                            $data['site_type'],
                            $data['plugin_version'] ?? 'unknown'
                        )
                    );
                } else {
                    $results[] = array(
                        'site' => $site,
                        'success' => false,
                        'message' => sprintf(__('HTTP %d: %s', 'app-sync'), $code, wp_remote_retrieve_response_message($response))
                    );
                }
            }
        }
        
        wp_send_json_success(array('results' => $results));
    }
    
    public function ajax_clear_logs() {
        check_ajax_referer('app_sync_clear_logs', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permission denied', 'app-sync'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        $result = $wpdb->query("TRUNCATE TABLE $table_name");
        
        if ($result !== false) {
            wp_send_json_success(__('Logs cleared successfully', 'app-sync'));
        } else {
            wp_send_json_error(__('Failed to clear logs', 'app-sync'));
        }
    }
    
    private function get_sync_statistics() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        // Count posts with GP ID
        $total_posts = $wpdb->get_var(
            "SELECT COUNT(DISTINCT post_id) FROM $wpdb->postmeta WHERE meta_key = 'wp_GP_ID'"
        );
        
        // Count recent successful syncs - include both success statuses and updated data
        $successful_24h = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name 
             WHERE status IN ('success', 'info') 
             AND action IN ('sync_success', 'data_updated', 'sync_received')
             AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        // Count recent failed syncs
        $failed_24h = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name 
             WHERE status = 'failed' 
             AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );
        
        // Total log entries
        $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        return array(
            'total_posts' => intval($total_posts),
            'successful_24h' => intval($successful_24h),
            'failed_24h' => intval($failed_24h),
            'total_logs' => intval($total_logs)
        );
    }
    
    private function display_recent_activity() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        $recent_logs = $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 5"
        );
        
        if (empty($recent_logs)) {
            echo '<p>' . __('No recent activity', 'app-sync') . '</p>';
            return;
        }
        
        echo '<div class="recent-activity">';
        foreach ($recent_logs as $log) {
            $status_color = $log->status === 'success' ? '#46b450' : '#dc3232';
            $time_diff = human_time_diff(strtotime($log->created_at), current_time('timestamp'));
            
            // Use stored post title from log, fallback to current title
            $post_title = !empty($log->post_title) ? $log->post_title : (get_the_title($log->post_id) ?: __('(No title)', 'app-sync'));
            
            echo '<div style="margin-bottom: 10px; padding: 8px; border-left: 3px solid ' . $status_color . '; background: #f9f9f9;">';
            echo '<div style="font-size: 12px; color: #666;">' . $time_diff . ' ' . __('ago', 'app-sync') . '</div>';
            echo '<div><strong>' . esc_html(parse_url($log->site_url, PHP_URL_HOST)) . '</strong></div>';
            
            // Post title with edit link
            if (get_post_status($log->post_id)) {
                echo '<div style="margin: 4px 0;"><a href="' . get_edit_post_link($log->post_id) . '" target="_blank" style="text-decoration: none; color: #0073aa;">' . esc_html($post_title) . '</a></div>';
            } else {
                echo '<div style="margin: 4px 0; color: #666;">' . esc_html($post_title) . ' <em>(deleted)</em></div>';
            }
            
            echo '<div style="font-size: 12px;">' . esc_html(ucfirst($log->action)) . ' - ' . esc_html(ucfirst($log->status)) . '</div>';
            echo '</div>';
        }
        echo '</div>'; 
    }
}