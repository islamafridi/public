<?php
/*
Plugin Name: AppSync Network
Description: AppSync Network lets you sync apps and content from one master site to multiple child sites with ease. Enhanced with Falang multi-language support.
Version: 2.2
Author: Muhammad Islam
Text Domain: app-sync
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Network: false
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('APP_SYNC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('APP_SYNC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('APP_SYNC_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('APP_SYNC_VERSION', '2.2');
define('APP_SYNC_MIN_WP_VERSION', '5.0');
define('APP_SYNC_MIN_PHP_VERSION', '7.4');

/**
 * Plugin main class
 */
class App_Sync_Plugin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Check system requirements
        if (!$this->check_requirements()) {
            return;
        }
        
        // Load text domain
        load_plugin_textdomain('app-sync', false, dirname(APP_SYNC_PLUGIN_BASENAME) . '/languages');
        
        // Include required files
        $this->include_files();
        
        // Initialize hooks
        $this->init_hooks();
    }
    
    private function check_requirements() {
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), APP_SYNC_MIN_WP_VERSION, '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . 
                     sprintf(__('App Sync Plugin requires WordPress version %s or higher.', 'app-sync'), APP_SYNC_MIN_WP_VERSION) . 
                     '</p></div>';
            });
            return false;
        }
        
        // Check PHP version
        if (version_compare(PHP_VERSION, APP_SYNC_MIN_PHP_VERSION, '<')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>' . 
                     sprintf(__('App Sync Plugin requires PHP version %s or higher.', 'app-sync'), APP_SYNC_MIN_PHP_VERSION) . 
                     '</p></div>';
            });
            return false;
        }
        
        return true;
    }
    
    private function include_files() {
        $required_files = array(
            'includes/class-settings.php',
            'includes/class-rest-api.php',
            'includes/class-webhooks.php',
            'includes/class-metabox.php',
            'includes/class-logger.php',
            'includes/class-sync-manager.php'
        );
        
        foreach ($required_files as $file) {
            $file_path = APP_SYNC_PLUGIN_DIR . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log('App Sync Plugin: Missing required file - ' . $file_path);
            }
        }
    }
    
    private function init_hooks() {
        // Initialize components
        if (class_exists('App_Sync_Settings')) {
            new App_Sync_Settings();
        }
        if (class_exists('App_Sync_REST_API')) {
            new App_Sync_REST_API();
        }
        if (class_exists('App_Sync_Webhooks')) {
            new App_Sync_Webhooks();
        }
        if (class_exists('App_Sync_Metabox')) {
            new App_Sync_Metabox();
        }
        if (class_exists('App_Sync_Manager')) {
            new App_Sync_Manager();
        }
        
        // Admin enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        
        // Add plugin action links
        add_filter('plugin_action_links_' . APP_SYNC_PLUGIN_BASENAME, array($this, 'plugin_action_links'));
    }
    
    public function admin_scripts($hook) {
        // Load on relevant pages including posts list
        if (in_array($hook, array('post.php', 'post-new.php', 'edit.php', 'settings_page_app-sync', 'tools_page_app-sync-manager'))) {
            wp_enqueue_script(
                'app-sync-admin',
                APP_SYNC_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                APP_SYNC_VERSION,
                true
            );
            
            wp_localize_script('app-sync-admin', 'appSyncAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('app_sync_nonce'),
                'manual_sync_nonce' => wp_create_nonce('app_sync_manual_sync'),
                'regenerate_token_nonce' => wp_create_nonce('app_sync_regenerate_token')
            ));
            
            wp_enqueue_style(
                'app-sync-admin',
                APP_SYNC_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                APP_SYNC_VERSION
            );
        }
    }
    
    public function plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=app-sync') . '">' . __('Settings', 'app-sync') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    public function activate() {
        // Set default options
        $defaults = array(
            'app_sync_is_master' => 'no',
            'app_sync_child_sites' => '',
            'app_sync_api_token' => '',
            'app_sync_version' => APP_SYNC_VERSION,
            'app_sync_sync_frequency' => 'immediate',
            'app_sync_log_retention' => 30,
            'app_sync_retry_attempts' => 3,
            'app_sync_timeout' => 30
        );
        
        foreach ($defaults as $option => $default) {
            if (false === get_option($option)) {
                update_option($option, $default);
            }
        }
        
        // Create database table for sync logs
        $this->create_sync_log_table();
        
        // Schedule cleanup cron job
        if (!wp_next_scheduled('app_sync_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'app_sync_cleanup_logs');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('app_sync_cleanup_logs');
        wp_clear_scheduled_hook('app_sync_retry_failed');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    private function create_sync_log_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'app_sync_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            post_title text,
            gp_id varchar(255) NOT NULL,
            site_url varchar(255) NOT NULL,
            action varchar(50) NOT NULL,
            status varchar(20) NOT NULL,
            response_code int(11) DEFAULT NULL,
            response_message text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY gp_id (gp_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public static function uninstall() {
        global $wpdb;
        
        // Remove all plugin options
        $options = array(
            'app_sync_is_master',
            'app_sync_child_sites',
            'app_sync_api_token',
            'app_sync_version',
            'app_sync_sync_frequency',
            'app_sync_log_retention',
            'app_sync_retry_attempts',
            'app_sync_timeout'
        );
        
        foreach ($options as $option) {
            delete_option($option);
        }
        
        // Drop custom table
        $table_name = $wpdb->prefix . 'app_sync_logs';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        // Clear scheduled events
        wp_clear_scheduled_hook('app_sync_cleanup_logs');
        wp_clear_scheduled_hook('app_sync_retry_failed');
    }
}

// Initialize the plugin
App_Sync_Plugin::get_instance();

// Uninstall hook
register_uninstall_hook(__FILE__, array('App_Sync_Plugin', 'uninstall'));