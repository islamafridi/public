<?php
/**
 * Legacy compatibility helper functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Legacy mask_link function replacement
 * This replaces your existing mask_link function to use the plugin
 */
if (!function_exists('mask_link')) {
    function mask_link($string, $action = 'e') {
        // If decoding is requested, return the string as-is for download processing
        if ($action == 'd') {
            return $string;
        }
        
        // For encoding, use the plugin's proxy system
        if (function_exists('dfr_get_proxy_download_url')) {
            return dfr_get_proxy_download_url($string);
        }
        
        // Fallback to original behavior if plugin not available
        $secret_key = defined('THEMES_NAMES') ? THEMES_NAMES : 'default_key';
        $secret_iv = defined('EXTHEMES_AUTHOR') ? EXTHEMES_AUTHOR : 'default_iv';
        $encrypt_method = "AES-256-CBC";
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        
        if ($action == 'e') {
            return base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
        }
        
        return $string;
    }
}

/**
 * Helper function to get renamed filename for templates
 */
function dfr_get_legacy_filename($url, $original_name = '') {
    if (function_exists('dfr_get_renamed_filename')) {
        return dfr_get_renamed_filename($url);
    }
    return $original_name ?: basename($url);
}

/**
 * Helper function to process download URLs in templates
 */
function dfr_process_download_url($url) {
    if (empty($url)) {
        return $url;
    }
    
    // Check if plugin is active and enabled
    $settings = get_option('download-file-renamer_settings');
    if (empty($settings['enable_proxy'])) {
        return $url;
    }
    
    // Use plugin to generate proxy URL
    if (function_exists('dfr_get_proxy_download_url')) {
        return dfr_get_proxy_download_url($url);
    }
    
    return $url;
}

/**
 * Template function to replace download link processing
 * Use this in your download template instead of mask_link
 */
function dfr_get_download_link($file_path, $use_proxy = true) {
    if (!$use_proxy) {
        return $file_path;
    }
    
    return dfr_process_download_url($file_path);
}

/**
 * Hook into template processing for automatic link replacement
 */
add_action('init', function() {
    // Only run on download pages
    if (strpos($_SERVER['REQUEST_URI'], '/file/') !== false || !empty($_GET['urls'])) {
        // Process legacy URL parameters
        if (!empty($_GET['urls']) && function_exists('dfr_get_proxy_download_url')) {
            $original_url = $_GET['urls'];
            
            // Check if this is already a proxy URL
            $settings = get_option('download-file-renamer_settings');
            $proxy_keyword = isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy';
            
            if (!isset($_GET[$proxy_keyword]) && !empty($settings['enable_proxy'])) {
                // Redirect to proxy URL
                $proxy_url = dfr_get_proxy_download_url($original_url);
                
                // Preserve other parameters
                $params = $_GET;
                unset($params['urls']);
                
                if (!empty($params)) {
                    $proxy_url .= '&' . http_build_query($params);
                }
                
                wp_redirect($proxy_url);
                exit;
            }
        }
    }
});