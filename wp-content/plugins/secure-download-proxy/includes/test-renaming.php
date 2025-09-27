<?php
/**
 * Test file for Download File Renamer functionality
 * Place this in wp-content/plugins/download-file-renamer/ and access via browser
 */

// Only run if WordPress is loaded
if (defined('ABSPATH')) {
    
    echo '<h1>Download File Renamer - Test Results</h1>';
    
    // Test URLs
    $test_urls = array(
        'https://disk.9mod.cc/jwKtUMmBuar8cKHdOhfiI/Soccer-Manager-2025/Soccer-Manager-2026-v3.0.12-(9mod.cc).apk',
        'https://disk.9mod.cc/jwKtUMmBuar8cKHdOhfiI/Game-v2.1-9Mod.com.apk',
        'https://disk.9mod.cc/jwKtUMmBuar8cKHdOhfiI/Truecaller-v15.30.6.xapk',
        'https://cloud.9mod.com/Block%20Blast/Block-Blast-v8.0.3-9mod.apk'
    );
    
    echo '<h2>File Renaming Tests:</h2>';
    echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
    echo '<tr><th>Original URL</th><th>Original Filename</th><th>Renamed Filename</th></tr>';
    
    foreach ($test_urls as $url) {
        $original_filename = basename(parse_url($url, PHP_URL_PATH));
        $original_decoded = urldecode($original_filename);
        $renamed_filename = dfr_get_renamed_filename($url);
        
        echo '<tr>';
        echo '<td>' . esc_html($url) . '</td>';
        echo '<td>' . esc_html($original_decoded) . '</td>';
        echo '<td><strong>' . esc_html($renamed_filename) . '</strong></td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    echo '<h2>Proxy URL Tests:</h2>';
    echo '<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">';
    echo '<tr><th>Original URL</th><th>Basic Proxy URL</th><th>Secure Proxy URL</th></tr>';
    
    foreach (array_slice($test_urls, 0, 2) as $url) {
        $basic_proxy = dfr_get_proxy_download_url($url);
        
        // Temporarily enable secure links for testing
        $settings = get_option('download-file-renamer_settings');
        $original_secure_setting = $settings['enable_secure_links'] ?? '0';
        $settings['enable_secure_links'] = '1';
        update_option('download-file-renamer_settings', $settings);
        
        $secure_proxy = dfr_get_proxy_download_url($url);
        
        // Restore original setting
        $settings['enable_secure_links'] = $original_secure_setting;
        update_option('download-file-renamer_settings', $settings);
        
        echo '<tr>';
        echo '<td>' . esc_html($url) . '</td>';
        echo '<td><small>' . esc_html($basic_proxy) . '</small></td>';
        echo '<td><small>' . esc_html($secure_proxy) . '</small></td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    // Display current settings
    $settings = get_option('download-file-renamer_settings');
    echo '<h2>Current Plugin Settings:</h2>';
    echo '<pre>';
    print_r($settings);
    echo '</pre>';
    
} else {
    echo 'This file must be accessed through WordPress.';
}
?>