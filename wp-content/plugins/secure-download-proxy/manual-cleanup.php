<?php
/**
 * Manual cleanup script for Download File Renamer plugin
 * Run this to clean up existing proxy URLs after plugin deactivation
 */

// Prevent direct access
if (!defined('WP_USE_THEMES')) {
    define('WP_USE_THEMES', false);
    require_once(__DIR__ . '/../../../wp-config.php');
}

function dfr_manual_cleanup() {
    global $wpdb;

    $settings = get_option('download-file-renamer_settings');
    $proxy_keyword = isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'dl';

    // Get the site URL to construct the proxy URL pattern
    $site_url = home_url();

    // Patterns to search for proxy URLs (using the actual proxy keyword)
    $proxy_pattern = $site_url . '/?' . $proxy_keyword . '=1&file=';
    $secure_proxy_pattern = $site_url . '/?' . $proxy_keyword . '=1&token=';

    echo "Starting cleanup with proxy keyword: {$proxy_keyword}\n";
    echo "Site URL: {$site_url}\n";
    echo "Looking for pattern: {$proxy_pattern}\n\n";

    // Search in post content for proxy URLs
    $posts_with_proxy = $wpdb->get_results($wpdb->prepare("
        SELECT ID, post_content, post_title
        FROM {$wpdb->posts}
        WHERE post_content LIKE %s
        OR post_content LIKE %s
    ", '%' . $wpdb->esc_like($proxy_pattern) . '%', '%' . $wpdb->esc_like($secure_proxy_pattern) . '%'));

    echo "Found " . count($posts_with_proxy) . " posts with proxy URLs\n\n";

    foreach ($posts_with_proxy as $post) {
        echo "Processing post ID {$post->ID}: {$post->post_title}\n";
        $updated_content = $post->post_content;
        $changes_made = 0;

        // Find and replace basic proxy URLs
        $basic_pattern = '/(' . preg_quote($site_url, '/') . '\/\?' . preg_quote($proxy_keyword, '/') . '=1&file=([^&\s"\']+))/';
        $updated_content = preg_replace_callback($basic_pattern, function($matches) use (&$changes_made) {
            $encoded_url = urldecode($matches[2]);
            $original_url = base64_decode($encoded_url);

            // Validate that it's a proper URL before replacing
            if (filter_var($original_url, FILTER_VALIDATE_URL)) {
                $changes_made++;
                echo "  Replaced: {$matches[1]}\n";
                echo "  With: {$original_url}\n\n";
                return $original_url;
            }
            return $matches[1]; // Return original if decode fails
        }, $updated_content);

        // Find and replace secure proxy URLs
        $secure_pattern = '/(' . preg_quote($site_url, '/') . '\/\?' . preg_quote($proxy_keyword, '/') . '=1&token=([^&\s"\']+))/';
        $updated_content = preg_replace_callback($secure_pattern, function($matches) use (&$changes_made) {
            $token = $matches[2];

            // Try to get the original URL from token data if token manager exists
            if (class_exists('DFR_Token_Manager')) {
                global $wpdb;
                $token_data = $wpdb->get_var($wpdb->prepare(
                    "SELECT original_url FROM {$wpdb->prefix}dfr_secure_tokens WHERE token = %s",
                    $token
                ));

                if ($token_data && filter_var($token_data, FILTER_VALIDATE_URL)) {
                    $changes_made++;
                    echo "  Replaced secure URL: {$matches[1]}\n";
                    echo "  With: {$token_data}\n\n";
                    return $token_data;
                }
            }

            return $matches[1]; // Return original if we can't decode
        }, $updated_content);

        // Update the post if content changed
        if ($updated_content !== $post->post_content) {
            $result = $wpdb->update(
                $wpdb->posts,
                array('post_content' => $updated_content),
                array('ID' => $post->ID),
                array('%s'),
                array('%d')
            );

            if ($result !== false) {
                echo "  ✓ Updated post content ({$changes_made} URLs replaced)\n\n";
            } else {
                echo "  ✗ Failed to update post content\n\n";
            }
        } else {
            echo "  No changes needed for this post\n\n";
        }
    }

    // Also check post meta for any stored proxy URLs
    $meta_with_proxy = $wpdb->get_results($wpdb->prepare("
        SELECT meta_id, meta_value, post_id, meta_key
        FROM {$wpdb->postmeta}
        WHERE meta_value LIKE %s
        OR meta_value LIKE %s
    ", '%' . $wpdb->esc_like($proxy_pattern) . '%', '%' . $wpdb->esc_like($secure_proxy_pattern) . '%'));

    echo "Found " . count($meta_with_proxy) . " post meta entries with proxy URLs\n\n";

    foreach ($meta_with_proxy as $meta) {
        echo "Processing meta ID {$meta->meta_id} (post {$meta->post_id}, key: {$meta->meta_key})\n";
        $updated_meta = $meta->meta_value;
        $changes_made = 0;

        // Apply the same regex replacements as above
        $basic_pattern = '/(' . preg_quote($site_url, '/') . '\/\?' . preg_quote($proxy_keyword, '/') . '=1&file=([^&\s"\']+))/';
        $updated_meta = preg_replace_callback($basic_pattern, function($matches) use (&$changes_made) {
            $encoded_url = urldecode($matches[2]);
            $original_url = base64_decode($encoded_url);

            if (filter_var($original_url, FILTER_VALIDATE_URL)) {
                $changes_made++;
                echo "  Replaced: {$matches[1]}\n";
                echo "  With: {$original_url}\n\n";
                return $original_url;
            }
            return $matches[1];
        }, $updated_meta);

        $secure_pattern = '/(' . preg_quote($site_url, '/') . '\/\?' . preg_quote($proxy_keyword, '/') . '=1&token=([^&\s"\']+))/';
        $updated_meta = preg_replace_callback($secure_pattern, function($matches) use (&$changes_made) {
            $token = $matches[2];

            if (class_exists('DFR_Token_Manager')) {
                global $wpdb;
                $token_data = $wpdb->get_var($wpdb->prepare(
                    "SELECT original_url FROM {$wpdb->prefix}dfr_secure_tokens WHERE token = %s",
                    $token
                ));

                if ($token_data && filter_var($token_data, FILTER_VALIDATE_URL)) {
                    $changes_made++;
                    echo "  Replaced secure URL: {$matches[1]}\n";
                    echo "  With: {$token_data}\n\n";
                    return $token_data;
                }
            }

            return $matches[1];
        }, $updated_meta);

        // Update the meta if it changed
        if ($updated_meta !== $meta->meta_value) {
            $result = $wpdb->update(
                $wpdb->postmeta,
                array('meta_value' => $updated_meta),
                array('meta_id' => $meta->meta_id),
                array('%s'),
                array('%d')
            );

            if ($result !== false) {
                echo "  ✓ Updated meta value ({$changes_made} URLs replaced)\n\n";
            } else {
                echo "  ✗ Failed to update meta value\n\n";
            }
        } else {
            echo "  No changes needed for this meta\n\n";
        }
    }

    echo "Cleanup completed!\n";
}

// Run the cleanup
if (php_sapi_name() === 'cli' || (isset($_GET['run']) && $_GET['run'] === 'cleanup')) {
    dfr_manual_cleanup();
} else {
    echo "To run this cleanup script:\n";
    echo "1. Via CLI: php " . __FILE__ . "\n";
    echo "2. Via browser: " . get_site_url() . "/wp-content/plugins/download-file-renamer/manual-cleanup.php?run=cleanup\n";
}
?>