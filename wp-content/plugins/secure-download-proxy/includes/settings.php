<?php
/**
 * Settings management for Download File Renamer
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class DFR_Settings {
    
    private $plugin_name = 'secure-download-proxy';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_init', array($this, 'handle_admin_actions'));
    }

    public function handle_admin_actions() {
        if (isset($_POST['action']) && $_POST['action'] === 'clear_logs' && wp_verify_nonce($_POST['dfr_nonce'], 'dfr_clear_logs')) {
            delete_option('dfr_download_log');
            $stats = get_option('dfr_download_stats', array());
            $stats['total_downloads'] = 0;
            $stats['today_downloads'] = 0;
            $stats['failed_downloads'] = 0;
            $stats['last_download'] = 'Never';
            update_option('dfr_download_stats', $stats);

            wp_redirect(add_query_arg('logs-cleared', '1', wp_get_referer()));
            exit;
        }

        if (isset($_POST['action']) && $_POST['action'] === 'clear_failed_logs' && wp_verify_nonce($_POST['dfr_nonce'], 'dfr_clear_failed_logs')) {
            delete_option('dfr_failed_downloads_log');
            // Also reset the failed downloads counter
            $stats = get_option('dfr_download_stats', array());
            $stats['failed_downloads'] = 0;
            update_option('dfr_download_stats', $stats);

            wp_redirect(add_query_arg('failed-logs-cleared', '1', wp_get_referer()));
            exit;
        }

        if (isset($_POST['action']) && $_POST['action'] === 'reset_settings' && wp_verify_nonce($_POST['dfr_nonce'], 'dfr_reset_settings')) {
            // Force reinitialize settings with defaults
            global $download_file_renamer;
            if ($download_file_renamer) {
                delete_option($this->plugin_name . '_settings');
                $download_file_renamer->init();
            }

            wp_redirect(add_query_arg('settings-reset', '1', wp_get_referer()));
            exit;
        }
    }
    
    public function add_admin_menu() {
        // Create main Site Management menu if it doesn't exist
        if (!menu_page_url('site-management', false)) {
            add_menu_page(
                'Site Management',
                'Site Management',
                'manage_options',
                'site-management',
                array($this, 'main_menu_page'),
                'dashicons-admin-tools',
                30
            );
        }

        // Add this plugin as a submenu under Site Management
        add_submenu_page(
            'site-management',
            'Secure Download Proxy',
            'Download Proxy',
            'manage_options',
            $this->plugin_name,
            array($this, 'admin_page')
        );
    }

    public function main_menu_page() {
        ?>
        <div class="wrap">
            <h1>Site Management</h1>
            <p>Welcome to Site Management dashboard. Use the submenu items to access different tools and plugins.</p>

            <div class="card">
                <h2>Available Tools</h2>
                <ul>
                    <li><a href="<?php echo admin_url('admin.php?page=site-management-tools'); ?>">Management Tools</a> - Post views, desktop redirects, and admin bar settings</li>
                    <li><a href="<?php echo admin_url('admin.php?page=secure-download-proxy'); ?>">Download Proxy</a> - Secure download proxy with file renaming and analytics</li>
                    <li><a href="<?php echo admin_url('admin.php?page=app-sync'); ?>">App Sync</a> - Sync apps and content across multiple sites</li>
                </ul>
            </div>

            <?php
            // Quick stats from both plugins
            global $wpdb;
            $total_views = $wpdb->get_var("SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = 'smt_post_views_count'");
            $posts_with_desktop_redirect = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'redirect_desktop' AND meta_value = '1'");

            $download_stats = get_option('dfr_download_stats', array());
            $total_downloads = isset($download_stats['total_downloads']) ? $download_stats['total_downloads'] : 0;
            $failed_downloads = isset($download_stats['failed_downloads']) ? $download_stats['failed_downloads'] : 0;
            ?>
            <div class="card">
                <h3>Quick Stats</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div>
                        <p><strong>Total Post Views:</strong> <?php echo number_format($total_views ?: 0); ?></p>
                        <p><strong>Posts with Desktop Redirect:</strong> <?php echo $posts_with_desktop_redirect ?: 0; ?></p>
                    </div>
                    <div>
                        <p><strong>Total Downloads:</strong> <?php echo number_format($total_downloads); ?></p>
                        <p><strong>Failed Downloads:</strong> <?php echo number_format($failed_downloads); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function admin_init() {
        register_setting($this->plugin_name . '_group', $this->plugin_name . '_settings', array($this, 'sanitize_settings'));
        
        add_settings_section(
            $this->plugin_name . '_basic_section',
            'Basic Settings',
            null,
            $this->plugin_name
        );
        
        add_settings_section(
            $this->plugin_name . '_security_section',
            'Security Settings',
            array($this, 'security_section_callback'),
            $this->plugin_name
        );
        
        // Basic Settings
        add_settings_field(
            'enable_proxy',
            'Enable Download Proxy',
            array($this, 'enable_proxy_callback'),
            $this->plugin_name,
            $this->plugin_name . '_basic_section'
        );
        
        add_settings_field(
            'allowed_domains',
            'Allowed Domains',
            array($this, 'allowed_domains_callback'),
            $this->plugin_name,
            $this->plugin_name . '_basic_section'
        );
        
        add_settings_field(
            'old_brands',
            'Old Brand Names',
            array($this, 'old_brands_callback'),
            $this->plugin_name,
            $this->plugin_name . '_basic_section'
        );
        
        add_settings_field(
            'new_brand',
            'New Brand Name',
            array($this, 'new_brand_callback'),
            $this->plugin_name,
            $this->plugin_name . '_basic_section'
        );
        
        add_settings_field(
            'add_brand_if_missing',
            'Add Brand if Missing',
            array($this, 'add_brand_if_missing_callback'),
            $this->plugin_name,
            $this->plugin_name . '_basic_section'
        );
        
        add_settings_field(
            'proxy_keyword',
            'Download Proxy Keyword',
            array($this, 'proxy_keyword_callback'),
            $this->plugin_name,
            $this->plugin_name . '_basic_section'
        );

        add_settings_field(
            'excluded_post_ids',
            'Excluded Post IDs',
            array($this, 'excluded_post_ids_callback'),
            $this->plugin_name,
            $this->plugin_name . '_basic_section'
        );

        add_settings_field(
            'download_mode',
            'Download Mode',
            array($this, 'download_mode_callback'),
            $this->plugin_name,
            $this->plugin_name . '_basic_section'
        );

        add_settings_field(
            'enable_filename_only',
            'Smart Filename Mode',
            array($this, 'enable_filename_only_callback'),
            $this->plugin_name,
            $this->plugin_name . '_basic_section'
        );

        add_settings_field(
            'redirect_delay',
            'Redirect Delay (seconds)',
            array($this, 'redirect_delay_callback'),
            $this->plugin_name,
            $this->plugin_name . '_basic_section'
        );
        
        // Security Settings
        add_settings_field(
            'enable_secure_links',
            'Enable Secure Links',
            array($this, 'enable_secure_links_callback'),
            $this->plugin_name,
            $this->plugin_name . '_security_section'
        );
        
        add_settings_field(
            'link_expiry_minutes',
            'Link Expiry Time (Minutes)',
            array($this, 'link_expiry_minutes_callback'),
            $this->plugin_name,
            $this->plugin_name . '_security_section'
        );


        // Analytics section
        add_settings_section(
            $this->plugin_name . '_analytics_section',
            'Analytics & Monitoring',
            array($this, 'analytics_section_callback'),
            $this->plugin_name
        );

        add_settings_field(
            'enable_download_tracking',
            'Enable Download Tracking',
            array($this, 'enable_download_tracking_callback'),
            $this->plugin_name,
            $this->plugin_name . '_analytics_section'
        );

        add_settings_field(
            'log_failed_downloads',
            'Log Failed Downloads',
            array($this, 'log_failed_downloads_callback'),
            $this->plugin_name,
            $this->plugin_name . '_analytics_section'
        );
    }
    
    public function security_section_callback() {
        echo '<p>Configure security features for download links. Secure links use encrypted tokens and can expire.</p>';
    }
    
    public function enable_proxy_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $checked = isset($settings['enable_proxy']) && $settings['enable_proxy'] ? 'checked' : '';
        echo '<input type="checkbox" name="' . $this->plugin_name . '_settings[enable_proxy]" value="1" ' . $checked . '>';
        echo '<p class="description">Enable proxy download functionality for file renaming</p>';
    }
    
    public function allowed_domains_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $value = isset($settings['allowed_domains']) ? $settings['allowed_domains'] : '';
        echo '<textarea name="' . $this->plugin_name . '_settings[allowed_domains]" rows="5" cols="50">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">Enter allowed domains (one per line). Example:<br>disk.9mod.cc<br>cloud.9mod.com</p>';
    }
    
    public function old_brands_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $value = isset($settings['old_brands']) ? $settings['old_brands'] : '';
        echo '<textarea name="' . $this->plugin_name . '_settings[old_brands]" rows="4" cols="50">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">Enter old brand names to replace (one per line). <strong>Case-insensitive matching.</strong><br>';
        echo 'Example:<br>9mod.cc<br>9mod.com<br>9Mod.cc<br>9Mod.com</p>';
    }
    
    public function new_brand_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $value = isset($settings['new_brand']) ? $settings['new_brand'] : '';
        echo '<input type="text" name="' . $this->plugin_name . '_settings[new_brand]" value="' . esc_attr($value) . '" size="30">';
        echo '<p class="description">Enter the new brand name to replace with. Example: 5play.io</p>';
    }
    
    public function add_brand_if_missing_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $checked = isset($settings['add_brand_if_missing']) && $settings['add_brand_if_missing'] ? 'checked' : '';
        echo '<input type="checkbox" name="' . $this->plugin_name . '_settings[add_brand_if_missing]" value="1" ' . $checked . '>';
        echo '<p class="description">Add new brand name to filename if no old brand is found<br>';
        echo '<strong>Example:</strong> Truecaller-v15.30.6.xapk → Truecaller-v15.30.6-5play.io.xapk</p>';
    }
    
    public function proxy_keyword_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $value = isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy';
        echo '<input type="text" name="' . $this->plugin_name . '_settings[proxy_keyword]" value="' . esc_attr($value) . '" size="30">';
        echo '<p class="description">Keyword used in download URLs. Default: download_proxy<br>';
        echo '<strong>Example:</strong> ?download_proxy=1 or ?dl=1</p>';
    }

    public function excluded_post_ids_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $value = isset($settings['excluded_post_ids']) ? $settings['excluded_post_ids'] : '';
        echo '<textarea name="' . $this->plugin_name . '_settings[excluded_post_ids]" rows="3" cols="50">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">Post IDs to exclude from proxy functionality (one per line or comma-separated)<br>';
        echo '<strong>Example:</strong> 123, 456, 789 or one ID per line</p>';
    }

    public function download_mode_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $value = isset($settings['download_mode']) ? $settings['download_mode'] : 'js_redirect';
        ?>
        <select name="<?php echo $this->plugin_name; ?>_settings[download_mode]">
            <option value="direct" <?php selected($value, 'direct'); ?>>Direct (Fastest - No proxy)</option>
            <option value="js_redirect" <?php selected($value, 'js_redirect'); ?>>JavaScript Redirect (Fast + Secure) - RECOMMENDED</option>
            <option value="meta_redirect" <?php selected($value, 'meta_redirect'); ?>>Meta Refresh (Fast + Secure)</option>
            <option value="optimized_proxy" <?php selected($value, 'optimized_proxy'); ?>>Optimized Proxy (Secure + Fast start)</option>
            <option value="redirect" <?php selected($value, 'redirect'); ?>>HTTP Redirect (Fast but INSECURE - exposes URLs)</option>
            <option value="proxy" <?php selected($value, 'proxy'); ?>>Full Proxy (Secure but slow)</option>
        </select>
        <p class="description">
            <strong>JavaScript Redirect (RECOMMENDED):</strong> Fast download start + URLs stay hidden + tracking<br>
            <strong>Meta Refresh:</strong> Similar to JS redirect but works without JavaScript<br>
            <strong>Optimized Proxy:</strong> Secure proxy with much faster start than regular proxy<br>
            <strong>HTTP Redirect:</strong> Fast but EXPOSES real URLs (not recommended)<br>
            <strong>Direct:</strong> No proxy at all (fastest but no tracking)<br>
            <strong>Full Proxy:</strong> Most secure but slowest start
        </p>
        <?php
    }

    public function enable_filename_only_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $checked = isset($settings['enable_filename_only']) && $settings['enable_filename_only'] ? 'checked' : '';
        echo '<input type="checkbox" name="' . $this->plugin_name . '_settings[enable_filename_only]" value="1" ' . $checked . '>';
        echo '<p class="description">Only use proxy when filename actually needs to be changed. If file already has correct branding, use direct download for better performance.</p>';
    }

    public function redirect_delay_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $value = isset($settings['redirect_delay']) ? intval($settings['redirect_delay']) : 1;
        echo '<input type="number" name="' . $this->plugin_name . '_settings[redirect_delay]" value="' . $value . '" min="0" max="10" step="1">';
        echo '<p class="description">Delay in seconds before redirect starts (for JavaScript and Meta redirect modes). 0 = instant redirect.</p>';
    }
    
    public function enable_secure_links_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $checked = isset($settings['enable_secure_links']) && $settings['enable_secure_links'] ? 'checked' : '';
        echo '<input type="checkbox" name="' . $this->plugin_name . '_settings[enable_secure_links]" value="1" ' . $checked . ' id="enable_secure_links">';
        echo '<p class="description">Enable secure, expiring download links with encrypted tokens</p>';
    }
    
    public function link_expiry_minutes_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $value = isset($settings['link_expiry_minutes']) ? $settings['link_expiry_minutes'] : '360';
        echo '<input type="number" name="' . $this->plugin_name . '_settings[link_expiry_minutes]" value="' . esc_attr($value) . '" min="5" max="43200" size="10">';
        echo '<p class="description">How many minutes until download links expire (5-43200 minutes). Default: 360 minutes (6 hours)</p>';
        echo '<p class="description"><strong>Common values:</strong> 5 (5 min), 30 (30 min), 60 (1 hour), 360 (6 hours), 720 (12 hours), 1440 (24 hours)</p>';
    }


    public function analytics_section_callback() {
        echo '<p>Track download statistics and monitor failed downloads for better insights.</p>';
    }

    public function enable_download_tracking_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $checked = isset($settings['enable_download_tracking']) && $settings['enable_download_tracking'] ? 'checked' : '';
        echo '<input type="checkbox" name="' . $this->plugin_name . '_settings[enable_download_tracking]" value="1" ' . $checked . '>';
        echo '<p class="description">Track download counts, user agents, and IP addresses for analytics</p>';
    }

    public function log_failed_downloads_callback() {
        $settings = get_option($this->plugin_name . '_settings');
        $checked = isset($settings['log_failed_downloads']) && $settings['log_failed_downloads'] ? 'checked' : '';
        echo '<input type="checkbox" name="' . $this->plugin_name . '_settings[log_failed_downloads]" value="1" ' . $checked . '>';
        echo '<p class="description">Log failed download attempts (404 errors, invalid tokens, etc.) to WordPress error log</p>';
    }
    
    public function admin_page() {
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'analytics';
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <?php if (isset($_GET['settings-updated'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>Settings saved successfully!</strong></p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['logs-cleared'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>Download logs cleared successfully!</strong></p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['failed-logs-cleared'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>Failed downloads log cleared successfully!</strong></p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['settings-reset'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong>Settings reset to defaults successfully!</strong></p>
                </div>
            <?php endif; ?>

            <nav class="nav-tab-wrapper">
                <a href="?page=<?php echo $this->plugin_name; ?>&tab=analytics" class="nav-tab <?php echo $active_tab == 'analytics' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-chart-line"></span> Analytics
                </a>
                <a href="?page=<?php echo $this->plugin_name; ?>&tab=basic" class="nav-tab <?php echo $active_tab == 'basic' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-admin-settings"></span> Basic Settings
                </a>
                <a href="?page=<?php echo $this->plugin_name; ?>&tab=security" class="nav-tab <?php echo $active_tab == 'security' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-shield"></span> Security
                </a>
                <a href="?page=<?php echo $this->plugin_name; ?>&tab=monitoring" class="nav-tab <?php echo $active_tab == 'monitoring' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-visibility"></span> Monitoring
                </a>
            </nav>

            <div class="tab-content">
                <?php
                switch ($active_tab) {
                    case 'analytics':
                        $this->render_analytics_tab();
                        break;
                    case 'basic':
                        $this->render_basic_settings_tab();
                        break;
                    case 'security':
                        $this->render_security_tab();
                        break;
                    case 'monitoring':
                        $this->render_monitoring_tab();
                        break;
                    default:
                        $this->render_analytics_tab();
                }
                ?>
            </div>
            
        </div>

        <style>
        .dfr-stat-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin: 10px 0;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .dfr-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .dfr-stat-number {
            font-size: 32px;
            font-weight: 600;
            color: #135e96;
        }
        .dfr-stat-label {
            color: #646970;
            font-size: 14px;
            margin-top: 5px;
        }
        .nav-tab .dashicons {
            margin-right: 5px;
        }
        </style>
        <?php
    }

    private function render_analytics_tab() {
        $download_stats = get_option('dfr_download_stats', array());
        $total_downloads = isset($download_stats['total_downloads']) ? $download_stats['total_downloads'] : 0;
        $today_downloads = isset($download_stats['today_downloads']) ? $download_stats['today_downloads'] : 0;
        $last_download = isset($download_stats['last_download']) ? $download_stats['last_download'] : 'Never';
        $failed_downloads = isset($download_stats['failed_downloads']) ? $download_stats['failed_downloads'] : 0;
        ?>
        <div class="dfr-stat-card">
            <h2 style="margin-top: 0;">📊 Download Statistics</h2>
            <div class="dfr-stat-grid">
                <div class="dfr-stat-card">
                    <div class="dfr-stat-number"><?php echo number_format($total_downloads); ?></div>
                    <div class="dfr-stat-label">Total Downloads</div>
                </div>
                <div class="dfr-stat-card">
                    <div class="dfr-stat-number"><?php echo number_format($today_downloads); ?></div>
                    <div class="dfr-stat-label">Today's Downloads</div>
                </div>
                <div class="dfr-stat-card">
                    <div class="dfr-stat-number" style="font-size: 18px;"><?php echo $last_download !== 'Never' ? date('M j, Y g:i A', strtotime($last_download)) : 'Never'; ?></div>
                    <div class="dfr-stat-label">Last Download</div>
                </div>
                <div class="dfr-stat-card">
                    <div class="dfr-stat-number" style="color: #dc3232;"><?php echo number_format($failed_downloads); ?></div>
                    <div class="dfr-stat-label">Failed Downloads</div>
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <form method="post" action="" style="display: inline-block;">
                    <?php wp_nonce_field('dfr_clear_logs', 'dfr_nonce'); ?>
                    <input type="hidden" name="action" value="clear_logs">
                    <input type="submit" name="clear_logs" class="button button-secondary" value="Clear Download Logs" onclick="return confirm('Are you sure you want to clear all download logs?');">
                </form>

                <form method="post" action="" style="display: inline-block;">
                    <?php wp_nonce_field('dfr_reset_settings', 'dfr_nonce'); ?>
                    <input type="hidden" name="action" value="reset_settings">
                    <input type="submit" name="reset_settings" class="button button-secondary" value="Reset All Settings" onclick="return confirm('Are you sure you want to reset all settings to defaults? This will fix any missing settings.');">
                </form>
            </div>
        </div>

        <?php if ($failed_downloads > 0): ?>
        <div class="dfr-stat-card" style="margin-top: 20px;">
            <h2 style="margin-top: 0;">❌ Failed Downloads Log</h2>
            <?php $this->render_failed_downloads_list(); ?>
        </div>
        <?php endif; ?>
        <?php
    }

    private function render_basic_settings_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields($this->plugin_name . '_group');
            ?>
            <div class="dfr-stat-card">
                <h2 style="margin-top: 0;">⚙️ Basic Settings</h2>
                <table class="form-table" role="presentation">
                    <?php $this->render_basic_settings_fields(); ?>
                </table>
                <?php submit_button(); ?>
            </div>
        </form>
        <?php
    }

    private function render_security_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields($this->plugin_name . '_group');
            ?>
            <div class="dfr-stat-card">
                <h2 style="margin-top: 0;">🔒 Security Settings</h2>
                <table class="form-table" role="presentation">
                    <?php $this->render_security_settings_fields(); ?>
                </table>

                <h3>Security Status</h3>
                <?php
                $settings = get_option($this->plugin_name . '_settings');
                $secure_enabled = isset($settings['enable_secure_links']) && $settings['enable_secure_links'] == '1';
                $expiry_minutes = isset($settings['link_expiry_minutes']) ? $settings['link_expiry_minutes'] : '360';
                $expiry_hours = round($expiry_minutes / 60, 1);
                $proxy_keyword = isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy';
                ?>
                <div style="padding: 20px; background: <?php echo $secure_enabled ? '#d1eddb' : '#fef7f0'; ?>; border-left: 4px solid <?php echo $secure_enabled ? '#00a32a' : '#dba617'; ?>;">
                    <h3>Current Security Mode: <?php echo $secure_enabled ? '🔒 Secure (Encrypted)' : '🔓 Basic (Simple)'; ?></h3>
                    <p><strong>Status:</strong> <?php echo $secure_enabled ? "Links expire after {$expiry_minutes} minutes ({$expiry_hours} hours)" : 'Links never expire'; ?></p>
                    <p><strong>URL Type:</strong> <?php echo $secure_enabled ? 'Short encrypted tokens (changes every time)' : 'Base64 encoded (permanent)'; ?></p>
                    <p><strong>Proxy Keyword:</strong> <code><?php echo esc_html($proxy_keyword); ?></code></p>
                    <?php if ($secure_enabled): ?>
                        <p><strong>URL Format:</strong> <code>/?<?php echo esc_html($proxy_keyword); ?>=1&token=abc123def456:202509121108/filename.apk</code></p>
                    <?php else: ?>
                        <p><strong>URL Format:</strong> <code>/?<?php echo esc_html($proxy_keyword); ?>=1&file=base64encoded&name=filename.apk</code></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-left: 4px solid #0073aa;">
                <h3>File Renaming Examples:</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Original Filename</th>
                            <th style="padding: 8px; text-align: left; border: 1px solid #ddd;">Renamed Filename</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;"><code>Soccer-Manager-2026-v3.0.12-(9mod.cc).apk</code></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><code>Soccer-Manager-2026-v3.0.12-(5play.io).apk</code></td>
                        </tr>
                        <tr style="background: #f8f9fa;">
                            <td style="padding: 8px; border: 1px solid #ddd;"><code>Game-v2.1-9Mod.com.apk</code></td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><code>Game-v2.1-5play.io.apk</code></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;"><code>Truecaller-v15.30.6.xapk</code> (no brand)</td>
                            <td style="padding: 8px; border: 1px solid #ddd;"><code>Truecaller-v15.30.6-5play.io.xapk</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 20px; padding: 20px; background: #fff3cd; border-left: 4px solid #856404;">
                <h3>How to Use:</h3>
                <p><strong>1.</strong> In your theme files, use:</p>
                <code>dfr_get_proxy_download_url($original_url)</code>
                
                <p style="margin-top: 15px;"><strong>2.</strong> Replace your download links with:</p>
                <code>&lt;a href="&lt;?php echo dfr_get_proxy_download_url($download_url); ?&gt;"&gt;Download&lt;/a&gt;</code>
                
                <p style="margin-top: 15px;"><strong>3.</strong> For custom expiry time (when secure links enabled):</p>
                <code>dfr_get_proxy_download_url($download_url, 30) // Expires in 30 minutes</code>
            </div>

            <?php submit_button(); ?>
        </form>
        
        <script>
        // Show/hide security options based on secure links setting
        document.addEventListener('DOMContentLoaded', function() {
            const secureLinksCheckbox = document.getElementById('enable_secure_links');
            const securityRows = document.querySelectorAll('tr');
            
            function toggleSecurityOptions() {
                let foundSecuritySection = false;
                securityRows.forEach(function(row) {
                    const label = row.querySelector('th');
                    if (label && label.textContent.includes('Security Settings')) {
                        foundSecuritySection = true;
                        return;
                    }
                    if (foundSecuritySection && label) {
                        const labelText = label.textContent;
                        if (labelText.includes('Link Expiry')) {
                            row.style.opacity = secureLinksCheckbox.checked ? '1' : '0.5';
                            const input = row.querySelector('input');
                            if (input) {
                                input.disabled = !secureLinksCheckbox.checked;
                            }
                        }
                    }
                });
            }
            
            if (secureLinksCheckbox) {
                toggleSecurityOptions();
                secureLinksCheckbox.addEventListener('change', toggleSecurityOptions);
            }
        });
        </script>
        <?php
    }

    private function render_monitoring_tab() {
        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields($this->plugin_name . '_group');
            ?>
            <div class="dfr-stat-card">
                <h2 style="margin-top: 0;">👁️ Monitoring Settings</h2>
                <table class="form-table" role="presentation">
                    <?php $this->render_monitoring_settings_fields(); ?>
                </table>
                <?php submit_button(); ?>
            </div>
        </form>
        <?php
    }

    private function render_basic_settings_fields() {
        ?>
        <tr>
            <th scope="row">Enable Download Proxy</th>
            <td><?php $this->enable_proxy_callback(); ?></td>
        </tr>
        <tr>
            <th scope="row">Allowed Domains</th>
            <td><?php $this->allowed_domains_callback(); ?></td>
        </tr>
        <tr>
            <th scope="row">Old Brand Names</th>
            <td><?php $this->old_brands_callback(); ?></td>
        </tr>
        <tr>
            <th scope="row">New Brand Name</th>
            <td><?php $this->new_brand_callback(); ?></td>
        </tr>
        <tr>
            <th scope="row">Add Brand if Missing</th>
            <td><?php $this->add_brand_if_missing_callback(); ?></td>
        </tr>
        <tr>
            <th scope="row">Download Proxy Keyword</th>
            <td><?php $this->proxy_keyword_callback(); ?></td>
        </tr>
        <tr>
            <th scope="row">Excluded Post IDs</th>
            <td><?php $this->excluded_post_ids_callback(); ?></td>
        </tr>
        <tr>
            <th scope="row">Download Mode</th>
            <td><?php $this->download_mode_callback(); ?></td>
        </tr>
        <tr>
            <th scope="row">Smart Filename Mode</th>
            <td><?php $this->enable_filename_only_callback(); ?></td>
        </tr>
        <tr>
            <th scope="row">Redirect Delay (seconds)</th>
            <td><?php $this->redirect_delay_callback(); ?></td>
        </tr>
        <?php
    }

    private function render_security_settings_fields() {
        ?>
        <tr>
            <th scope="row">Enable Secure Links</th>
            <td><?php $this->enable_secure_links_callback(); ?></td>
        </tr>
        <tr>
            <th scope="row">Link Expiry Time (Minutes)</th>
            <td><?php $this->link_expiry_minutes_callback(); ?></td>
        </tr>
        <?php
    }

    private function render_monitoring_settings_fields() {
        ?>
        <tr>
            <th scope="row">Enable Download Tracking</th>
            <td><?php $this->enable_download_tracking_callback(); ?></td>
        </tr>
        <tr>
            <th scope="row">Log Failed Downloads</th>
            <td><?php $this->log_failed_downloads_callback(); ?></td>
        </tr>
        <?php
    }

    public function sanitize_settings($input) {
        // Get existing settings to merge with new input
        $existing_settings = get_option($this->plugin_name . '_settings', array());
        $sanitized = $existing_settings; // Start with existing settings

        // Handle text fields - only update if present in input
        $text_fields = array('allowed_domains', 'old_brands', 'new_brand', 'proxy_keyword', 'excluded_post_ids', 'download_mode');
        foreach ($text_fields as $field) {
            if (isset($input[$field])) {
                $sanitized[$field] = sanitize_textarea_field($input[$field]);
            }
        }

        // Handle number fields - only update if present in input
        if (isset($input['link_expiry_minutes'])) {
            $sanitized['link_expiry_minutes'] = absint($input['link_expiry_minutes']);
        }
        if (isset($input['redirect_delay'])) {
            $sanitized['redirect_delay'] = absint($input['redirect_delay']);
        }

        // Handle checkboxes - WordPress doesn't submit unchecked checkboxes
        // We need to detect which form section is being submitted
        $checkbox_fields = array('enable_proxy', 'enable_secure_links', 'add_brand_if_missing', 'enable_download_tracking', 'log_failed_downloads', 'enable_filename_only');

        // Check if this is a security tab submission (contains security-related fields)
        $is_security_form = isset($input['enable_secure_links']) || isset($input['link_expiry_minutes']);
        $is_basic_form = isset($input['enable_proxy']) || isset($input['allowed_domains']) || isset($input['excluded_post_ids']) || isset($input['download_mode']) || isset($input['redirect_delay']);
        $is_monitoring_form = isset($input['enable_download_tracking']) || isset($input['log_failed_downloads']);

        foreach ($checkbox_fields as $field) {
            // For security form, handle security checkboxes
            if ($is_security_form && $field == 'enable_secure_links') {
                $sanitized[$field] = isset($input[$field]) && $input[$field] == '1' ? '1' : '0';
            }
            // For basic form, handle basic checkboxes
            elseif ($is_basic_form && in_array($field, array('enable_proxy', 'add_brand_if_missing', 'enable_filename_only'))) {
                $sanitized[$field] = isset($input[$field]) && $input[$field] == '1' ? '1' : '0';
            }
            // For monitoring form, handle monitoring checkboxes
            elseif ($is_monitoring_form && in_array($field, array('enable_download_tracking', 'log_failed_downloads'))) {
                $sanitized[$field] = isset($input[$field]) && $input[$field] == '1' ? '1' : '0';
            }
            // Otherwise, only update if explicitly set
            elseif (array_key_exists($field, $input)) {
                $sanitized[$field] = isset($input[$field]) && $input[$field] == '1' ? '1' : '0';
            }
        }

        return $sanitized;
    }

    private function render_failed_downloads_list() {
        $failed_downloads_log = get_option('dfr_failed_downloads_log', array());

        if (empty($failed_downloads_log)) {
            echo '<p>No failed downloads recorded yet.</p>';
            return;
        }

        // Pagination setup
        $per_page = 10;
        $current_page = isset($_GET['failed_page']) ? max(1, intval($_GET['failed_page'])) : 1;
        $total_items = count($failed_downloads_log);
        $total_pages = ceil($total_items / $per_page);

        // Reverse the array to show newest first, then paginate
        $failed_downloads_log = array_reverse($failed_downloads_log);
        $offset = ($current_page - 1) * $per_page;
        $current_items = array_slice($failed_downloads_log, $offset, $per_page);
        ?>

        <div style="margin-bottom: 15px;">
            <strong>Total Failed Downloads:</strong> <?php echo number_format($total_items); ?>
        </div>

        <div style="overflow-x: auto;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 150px;">Date/Time</th>
                        <th>Error Message</th>
                        <th style="width: 120px;">IP Address</th>
                        <th style="width: 100px;">User Agent</th>
                        <th style="width: 120px;">Referer</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($current_items as $log_entry): ?>
                    <tr>
                        <td>
                            <strong><?php echo date('M j, Y', strtotime($log_entry['timestamp'])); ?></strong><br>
                            <small><?php echo date('g:i A', strtotime($log_entry['timestamp'])); ?></small>
                        </td>
                        <td>
                            <span style="color: #dc3232; font-weight: 500;">
                                <?php echo esc_html($log_entry['error_message']); ?>
                            </span>
                        </td>
                        <td>
                            <code><?php echo esc_html($log_entry['ip']); ?></code>
                        </td>
                        <td>
                            <small title="<?php echo esc_attr($log_entry['user_agent']); ?>">
                                <?php
                                $user_agent = $log_entry['user_agent'];
                                if (strlen($user_agent) > 20) {
                                    echo esc_html(substr($user_agent, 0, 20)) . '...';
                                } else {
                                    echo esc_html($user_agent);
                                }
                                ?>
                            </small>
                        </td>
                        <td>
                            <small title="<?php echo esc_attr($log_entry['referer']); ?>">
                                <?php
                                $referer = $log_entry['referer'];
                                if ($referer === 'Direct access') {
                                    echo '<em>Direct</em>';
                                } else {
                                    $parsed = parse_url($referer);
                                    echo esc_html($parsed['host'] ?? 'Unknown');
                                }
                                ?>
                            </small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="tablenav">
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo number_format($total_items); ?> items</span>
                <span class="pagination-links">
                    <?php if ($current_page > 1): ?>
                        <a class="first-page button" href="<?php echo add_query_arg('failed_page', 1); ?>">«</a>
                        <a class="prev-page button" href="<?php echo add_query_arg('failed_page', $current_page - 1); ?>">‹</a>
                    <?php endif; ?>

                    <span class="paging-input">
                        <span class="tablenav-paging-text">
                            <?php echo $current_page; ?> of <span class="total-pages"><?php echo $total_pages; ?></span>
                        </span>
                    </span>

                    <?php if ($current_page < $total_pages): ?>
                        <a class="next-page button" href="<?php echo add_query_arg('failed_page', $current_page + 1); ?>">›</a>
                        <a class="last-page button" href="<?php echo add_query_arg('failed_page', $total_pages); ?>">»</a>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <?php endif; ?>

        <div style="margin-top: 15px;">
            <form method="post" action="" style="display: inline-block;">
                <?php wp_nonce_field('dfr_clear_failed_logs', 'dfr_nonce'); ?>
                <input type="hidden" name="action" value="clear_failed_logs">
                <input type="submit" name="clear_failed_logs" class="button button-secondary"
                       value="Clear Failed Downloads Log"
                       onclick="return confirm('Are you sure you want to clear the failed downloads log?');">
            </form>
        </div>
        <?php
    }
}

// Initialize settings
new DFR_Settings();
?>