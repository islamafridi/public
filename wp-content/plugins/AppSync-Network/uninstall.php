<?php
/**
 * App Sync Plugin Uninstall
 * 
 * This file runs when the plugin is deleted via the WordPress admin.
 * It removes all plugin data, options, and database tables.
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all plugin options
$options_to_delete = array(
    'app_sync_is_master',
    'app_sync_child_sites', 
    'app_sync_api_token',
    'app_sync_version',
    'app_sync_sync_frequency',
    'app_sync_log_retention',
    'app_sync_retry_attempts',
    'app_sync_timeout'
);

foreach ($options_to_delete as $option) {
    delete_option($option);
    // Also delete from multisite if applicable
    delete_site_option($option);
}

// Remove all post meta added by the plugin
global $wpdb;

$meta_keys_to_delete = array(
    'title_template',
    '_app_sync_content_hash', 
    '_app_sync_last_sync',
    '_app_sync_stats'
);

foreach ($meta_keys_to_delete as $meta_key) {
    $wpdb->delete(
        $wpdb->postmeta,
        array('meta_key' => $meta_key),
        array('%s')
    );
}

// Drop the sync logs table
$table_name = $wpdb->prefix . 'app_sync_logs';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Clear all scheduled cron jobs
wp_clear_scheduled_hook('app_sync_cleanup_logs');
wp_clear_scheduled_hook('app_sync_retry_failed');

// Remove any cached data
wp_cache_delete('app_sync_stats');
wp_cache_delete('app_sync_performance');

// Force cleanup of any transients
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_app_sync_%'");
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_app_sync_%'");

// Log the uninstall (if WP_DEBUG is enabled)
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('App Sync Plugin: Complete uninstall completed - all data removed');
}