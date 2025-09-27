<?php
/**
 * Test file for debugging secure links
 * Access via: /wp-content/plugins/download-file-renamer/test-secure-links.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>Secure Links Test & Debug</h1>";

// Test working URL vs non-existent URL
$working_url = "https://fdisk.5play.app/some-working-file.apk";
$broken_url = "https://fdisk.5play.app/non-existent-file.apk";

echo "<h2>Testing URL Generation:</h2>";

// Test working URL
if (function_exists('dfr_get_proxy_download_url')) {
    $proxy_url_working = dfr_get_proxy_download_url($working_url);
    $proxy_url_broken = dfr_get_proxy_download_url($broken_url);

    echo "<p><strong>Working URL:</strong> $working_url</p>";
    echo "<p><strong>Generated Proxy:</strong> <a href='$proxy_url_working'>$proxy_url_working</a></p>";
    echo "<br>";
    echo "<p><strong>Broken URL:</strong> $broken_url</p>";
    echo "<p><strong>Generated Proxy:</strong> <a href='$proxy_url_broken'>$proxy_url_broken</a></p>";
} else {
    echo "<p>❌ dfr_get_proxy_download_url function not found</p>";
}

// Show current settings
$settings = get_option('download-file-renamer_settings');
echo "<h2>Current Settings:</h2>";
echo "<p><strong>Enable Secure Links:</strong> " . (isset($settings['enable_secure_links']) && $settings['enable_secure_links'] == '1' ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Proxy Keyword:</strong> " . (isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy') . "</p>";

echo "<h2>Test Instructions:</h2>";
echo "<p>1. Click the links above to test downloads</p>";
echo "<p>2. Working URL should download or show proper error</p>";
echo "<p>3. Broken URL should show 404 error (not 966-byte file)</p>";
?>