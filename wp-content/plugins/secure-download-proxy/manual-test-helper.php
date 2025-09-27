<?php
/**
 * Manual Test Helper - Run this to simulate the download process
 * Access via: /wp-content/plugins/download-file-renamer/manual-test-helper.php
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>Download File Renamer - Manual Test Helper</h1>";

// Test URLs from the original test file
$working_url = "https://fdisk.5play.app/some-working-file.apk";
$broken_url = "https://fdisk.5play.app/non-existent-file.apk";

echo "<h2>Direct URL Testing (Without Proxy)</h2>";

// Function to test URL directly
function test_url_directly($url) {
    echo "<h3>Testing: $url</h3>";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $headers = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $content_length = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "<p><strong>HTTP Code:</strong> $http_code</p>";
    echo "<p><strong>Content Length:</strong> " . ($content_length > 0 ? $content_length . " bytes" : "Unknown") . "</p>";
    echo "<p><strong>Content Type:</strong> " . ($content_type ?: "Unknown") . "</p>";

    if ($error) {
        echo "<p><strong>cURL Error:</strong> $error</p>";
    }

    if ($http_code == 200) {
        echo "<p style='color: green;'>✅ URL is accessible</p>";
    } elseif ($http_code == 404) {
        echo "<p style='color: red;'>❌ File not found (404)</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ HTTP Error: $http_code</p>";
    }

    echo "<hr>";
}

// Test both URLs
test_url_directly($working_url);
test_url_directly($broken_url);

echo "<h2>Plugin Status</h2>";

// Check plugin function availability
if (function_exists('dfr_get_proxy_download_url')) {
    echo "<p style='color: green;'>✅ Plugin function dfr_get_proxy_download_url is available</p>";

    // Get settings
    $settings = get_option('download-file-renamer_settings');
    echo "<p><strong>Secure Links Enabled:</strong> " . (isset($settings['enable_secure_links']) && $settings['enable_secure_links'] == '1' ? 'YES' : 'NO') . "</p>";
    echo "<p><strong>Proxy Keyword:</strong> " . (isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy') . "</p>";

    // Generate proxy URLs
    echo "<h3>Generated Proxy URLs:</h3>";
    $proxy_working = dfr_get_proxy_download_url($working_url);
    $proxy_broken = dfr_get_proxy_download_url($broken_url);

    echo "<p><strong>Working URL Proxy:</strong><br>";
    echo "<a href='$proxy_working' target='_blank'>$proxy_working</a></p>";

    echo "<p><strong>Broken URL Proxy:</strong><br>";
    echo "<a href='$proxy_broken' target='_blank'>$proxy_broken</a></p>";

} else {
    echo "<p style='color: red;'>❌ Plugin function dfr_get_proxy_download_url is NOT available</p>";
}

echo "<h2>What to Test:</h2>";
echo "<ol>";
echo "<li>Click on the 'Working URL Proxy' link above - it should either download a file or show a proper error</li>";
echo "<li>Click on the 'Broken URL Proxy' link above - it should show a 404 error message, NOT download a 965-966 byte file</li>";
echo "<li>Check the file size if any download occurs - it should be the actual file size, not ~965 bytes</li>";
echo "<li>Look for any error messages in the browser or download</li>";
echo "</ol>";

echo "<p><strong>Expected Results:</strong></p>";
echo "<ul>";
echo "<li>Working URL: Should download the actual file OR show appropriate error if the file doesn't exist</li>";
echo "<li>Broken URL: Should show '404: The requested file was not found on the server' message</li>";
echo "<li>No 965-byte files should be downloaded for broken URLs</li>";
echo "</ul>";
?>