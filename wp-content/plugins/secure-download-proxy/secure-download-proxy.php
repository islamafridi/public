<?php
/**
 * Plugin Name: Secure Download Proxy
 * Plugin URI: https://5plays.org
 * Description: Advanced download proxy with file renaming, secure expiring links, custom domains, analytics, and comprehensive monitoring.
 * Version: 2.1.2
 * Author: Muhammad Islam
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DFR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DFR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DFR_PLUGIN_VERSION', '2.1.2');

// Main plugin class
class DownloadFileRenamer {
    
    private $plugin_name = 'secure-download-proxy';
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('parse_request', array($this, 'handle_download_proxy'));

        // Load includes
        $this->load_includes();

        // Add settings link on plugin page
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));

        // Schedule cleanup cron job
        add_action('wp', array($this, 'schedule_cleanup'));
        add_action('dfr_daily_cleanup', array($this, 'daily_cleanup'));
    }
    
    private function load_includes() {
        $includes = [
            'includes/settings.php',
            'includes/file-handler.php',
            'includes/token-manager.php'
        ];

        foreach ($includes as $include) {
            $file_path = DFR_PLUGIN_DIR . $include;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                error_log('DFR Plugin: Missing include file - ' . $file_path);
            }
        }
    }
    
    public function init() {
        // Set default options on activation
        $existing_settings = get_option($this->plugin_name . '_settings');

        // Define all default settings including new ones
        $default_settings = array(
            'allowed_domains' => "disk.9mod.cc\ncloud.9mod.com\ncdn.9mod.cc\nfiles.9mod.com\nfdisk.5play.app",
            'old_brands' => "9mod.cc\n9mod.com\n9Mod.cc\n9Mod.com",
            'new_brand' => '5play.io',
            'enable_proxy' => '1',
            'enable_secure_links' => '0',
            'link_expiry_minutes' => '360',
            'proxy_keyword' => 'download_proxy',
            'add_brand_if_missing' => '1',
            'enable_download_tracking' => '1',
            'log_failed_downloads' => '1',
            'excluded_post_ids' => '',
            'download_mode' => 'js_redirect',
            'enable_filename_only' => '1',
            'proxy_fallback_enabled' => '1',
            'redirect_delay' => '1'
        );

        if ($existing_settings === false) {
            // First time installation - set all defaults
            add_option($this->plugin_name . '_settings', $default_settings);
        } else {
            // Plugin exists but may be missing new settings - merge defaults
            $updated_settings = array_merge($default_settings, $existing_settings);
            update_option($this->plugin_name . '_settings', $updated_settings);
        }
    }
    
    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=' . $this->plugin_name . '">Settings</a>';
        array_push($links, $settings_link);
        return $links;
    }
    
    public function get_renamed_filename($url) {
        $settings = get_option($this->plugin_name . '_settings');
        $filename = basename(parse_url($url, PHP_URL_PATH));
        
        // URL decode the filename to handle encoded characters
        $filename = urldecode($filename);
        
        if (empty($settings['new_brand'])) {
            return $filename;
        }
        
        $new_brand = trim($settings['new_brand']);
        $old_brands = !empty($settings['old_brands']) ? array_filter(array_map('trim', explode("\n", $settings['old_brands']))) : array();
        $add_brand_if_missing = isset($settings['add_brand_if_missing']) && $settings['add_brand_if_missing'];
        
        $original_filename = $filename;
        $found_old_brand = false;
        
        // Check for old brands and create replacements (case-insensitive)
        foreach ($old_brands as $old_brand) {
            $patterns = array(
                "({$old_brand})" => "({$new_brand})",
                "-{$old_brand}" => "-{$new_brand}",
                "_{$old_brand}" => "_{$new_brand}",
                ".{$old_brand}" => ".{$new_brand}",
                " {$old_brand}" => " {$new_brand}",
                "[{$old_brand}]" => "[{$new_brand}]"
            );
            
            foreach ($patterns as $old_pattern => $new_pattern) {
                // Case-insensitive search and replace
                if (stripos($filename, $old_pattern) !== false) {
                    $found_old_brand = true;
                    // Use preg_replace for case-insensitive replacement
                    $filename = preg_replace('/' . preg_quote($old_pattern, '/') . '/i', $new_pattern, $filename);
                }
            }
        }
        
        // If no old brand found and add_brand_if_missing is enabled, add new brand before extension
        if (!$found_old_brand && $add_brand_if_missing) {
            $path_info = pathinfo($filename);
            $name = $path_info['filename'];
            $extension = isset($path_info['extension']) ? '.' . $path_info['extension'] : '';
            
            // Add new brand before extension
            $filename = $name . '-' . $new_brand . $extension;
        }
        
        return $filename;
    }

    private function is_post_excluded_from_proxy() {
        global $post;

        // Get current post ID
        $current_post_id = 0;
        if ($post && isset($post->ID)) {
            $current_post_id = $post->ID;
        } elseif (is_admin() && isset($_GET['post'])) {
            $current_post_id = intval($_GET['post']);
        } elseif (isset($_POST['post_ID'])) {
            $current_post_id = intval($_POST['post_ID']);
        }

        if (!$current_post_id) {
            return false; // If we can't determine post ID, don't exclude
        }

        $settings = get_option($this->plugin_name . '_settings');
        $excluded_post_ids = isset($settings['excluded_post_ids']) ? $settings['excluded_post_ids'] : '';

        if (empty($excluded_post_ids)) {
            return false;
        }

        // Parse excluded post IDs (supports comma-separated and newline-separated)
        $excluded_ids = array();

        // Split by both commas and newlines
        $raw_ids = preg_split('/[,\n\r]+/', $excluded_post_ids);

        foreach ($raw_ids as $id) {
            $id = trim($id);
            if (is_numeric($id) && $id > 0) {
                $excluded_ids[] = intval($id);
            }
        }

        return in_array($current_post_id, $excluded_ids);
    }

    public function generate_proxy_download_url($original_url, $custom_expiry_minutes = null) {
        $settings = get_option($this->plugin_name . '_settings');

        if (empty($settings['enable_proxy'])) {
            return $original_url; // Return original URL if proxy is disabled
        }

        // Check if current post is excluded from proxy functionality
        if ($this->is_post_excluded_from_proxy()) {
            return $original_url;
        }

        // Get download mode setting
        $download_mode = isset($settings['download_mode']) ? $settings['download_mode'] : 'redirect';
        $enable_filename_only = isset($settings['enable_filename_only']) && $settings['enable_filename_only'];

        // If filename-only mode is enabled and file already has the correct brand, use direct download
        if ($enable_filename_only) {
            $original_filename = basename(parse_url($original_url, PHP_URL_PATH));
            $renamed_filename = $this->get_renamed_filename($original_url);

            // If filenames are the same (no renaming needed), use direct download
            if ($original_filename === $renamed_filename) {
                return $original_url;
            }
        }

        // Choose download mode
        switch ($download_mode) {
            case 'direct':
                // Direct download - no proxy, just return original URL
                return $original_url;

            case 'redirect':
                // HTTP redirect mode - INSECURE (exposes real URLs)
                return $this->generate_redirect_download_url($original_url);

            case 'js_redirect':
                // JavaScript redirect - Fast + Secure (URLs hidden)
                return $this->generate_js_redirect_download_url($original_url);

            case 'meta_redirect':
                // Meta refresh redirect - Fast + Secure (URLs hidden)
                return $this->generate_meta_redirect_download_url($original_url);

            case 'optimized_proxy':
                // Optimized proxy - Secure + Faster than regular proxy
                return $this->generate_optimized_proxy_download_url($original_url);

            case 'proxy':
            default:
                // Full proxy mode (slow but secure)
                $secure_enabled = isset($settings['enable_secure_links']) && $settings['enable_secure_links'] == '1';

                if ($secure_enabled) {
                    // Check if token manager class exists
                    if (class_exists('DFR_Token_Manager')) {
                        $token_manager = new DFR_Token_Manager();
                        return $token_manager->generate_secure_url($original_url, $custom_expiry_minutes);
                    } else {
                        // Fallback to basic if token manager is not available
                        return $this->generate_basic_proxy_download_url($original_url);
                    }
                } else {
                    return $this->generate_basic_proxy_download_url($original_url);
                }
        }
    }
    
    private function generate_redirect_download_url($original_url) {
        $settings = get_option($this->plugin_name . '_settings');
        $proxy_keyword = isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy';

        $encoded_url = base64_encode($original_url);
        $renamed_filename = $this->get_renamed_filename($original_url);

        // Use 'r' parameter for HTTP redirect (cleaner URL)
        return home_url('/?' . $proxy_keyword . '=1&r=1&file=' . urlencode($encoded_url) . '&name=' . urlencode($renamed_filename));
    }

    private function generate_js_redirect_download_url($original_url) {
        $settings = get_option($this->plugin_name . '_settings');
        $proxy_keyword = isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy';

        $encoded_url = base64_encode($original_url);
        $renamed_filename = $this->get_renamed_filename($original_url);

        // Use 'j' parameter for JavaScript redirect (cleaner URL)
        return home_url('/?' . $proxy_keyword . '=1&j=1&file=' . urlencode($encoded_url) . '&name=' . urlencode($renamed_filename));
    }

    private function generate_meta_redirect_download_url($original_url) {
        $settings = get_option($this->plugin_name . '_settings');
        $proxy_keyword = isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy';

        $encoded_url = base64_encode($original_url);
        $renamed_filename = $this->get_renamed_filename($original_url);

        // Use 'm' parameter for Meta refresh redirect (cleaner URL)
        return home_url('/?' . $proxy_keyword . '=1&m=1&file=' . urlencode($encoded_url) . '&name=' . urlencode($renamed_filename));
    }

    private function generate_optimized_proxy_download_url($original_url) {
        $settings = get_option($this->plugin_name . '_settings');
        $proxy_keyword = isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy';

        $encoded_url = base64_encode($original_url);
        $renamed_filename = $this->get_renamed_filename($original_url);

        // Use 'o' parameter for Optimized proxy (cleaner URL)
        return home_url('/?' . $proxy_keyword . '=1&o=1&file=' . urlencode($encoded_url) . '&name=' . urlencode($renamed_filename));
    }

    private function generate_basic_proxy_download_url($original_url) {
        $settings = get_option($this->plugin_name . '_settings');
        $proxy_keyword = isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy';

        $encoded_url = base64_encode($original_url);
        $renamed_filename = $this->get_renamed_filename($original_url);

        return home_url('/?' . $proxy_keyword . '=1&mode=proxy&file=' . urlencode($encoded_url) . '&name=' . urlencode($renamed_filename));
    }
    
    public function handle_download_proxy() {
        $settings = get_option($this->plugin_name . '_settings');
        $proxy_keyword = isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy';

        // Add noindex meta tag for proxy pages to prevent search engine indexing
        if (isset($_GET[$proxy_keyword]) && $_GET[$proxy_keyword] == '1') {
            add_action('wp_head', function() {
                echo '<meta name="robots" content="noindex, nofollow">' . "\n";
            }, 1);

            $this->process_download();
            exit;
        }
    }
    
    private function process_download() {
        $settings = get_option($this->plugin_name . '_settings');
        $secure_enabled = isset($settings['enable_secure_links']) && $settings['enable_secure_links'];

        // Check for different download modes using short parameters
        if (isset($_GET['j']) && $_GET['j'] == '1') {
            // JavaScript redirect mode
            $this->process_js_redirect_download();
            return;
        } elseif (isset($_GET['m']) && $_GET['m'] == '1') {
            // Meta refresh redirect mode
            $this->process_meta_redirect_download();
            return;
        } elseif (isset($_GET['o']) && $_GET['o'] == '1') {
            // Optimized proxy mode
            $this->process_optimized_proxy_download();
            return;
        } elseif (isset($_GET['r']) && $_GET['r'] == '1') {
            // HTTP redirect mode
            $this->process_redirect_download();
            return;
        } elseif (isset($_GET['mode'])) {
            // Legacy mode parameter support (for backward compatibility)
            $mode = $_GET['mode'];
            switch ($mode) {
                case 'redirect':
                    $this->process_redirect_download();
                    return;
                case 'js_redirect':
                    $this->process_js_redirect_download();
                    return;
                case 'meta_redirect':
                    $this->process_meta_redirect_download();
                    return;
                case 'optimized_proxy':
                    $this->process_optimized_proxy_download();
                    return;
            }
        }

        // Default handling for secure tokens and basic proxy
        if ($secure_enabled && isset($_GET['token'])) {
            // Check if token manager class exists
            if (class_exists('DFR_Token_Manager')) {
                $token_manager = new DFR_Token_Manager();
                $token_manager->process_secure_download();
            } else {
                error_log('DFR_Token_Manager class not found for secure download processing');
                http_response_code(500);
                die('Secure download processing unavailable');
            }
        } else {
            $this->process_basic_download();
        }
    }

    private function process_redirect_download() {
        if (!isset($_GET['file']) || !isset($_GET['name'])) {
            http_response_code(400);
            die('Invalid request');
        }

        $original_url = base64_decode(urldecode($_GET['file']));
        $renamed_filename = urldecode($_GET['name']);

        if (!filter_var($original_url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            die('Invalid URL');
        }

        // Track download attempt (lightweight tracking)
        $this->track_download($original_url, $renamed_filename, 'redirect');

        // Fast redirect with download headers
        header('Location: ' . $original_url, true, 302);
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

        // Optional: Add download suggestion header (may work in some browsers)
        if (!empty($renamed_filename)) {
            header('Content-Disposition: attachment; filename="' . $renamed_filename . '"');
        }

        exit;
    }

    private function process_js_redirect_download() {
        if (!isset($_GET['file']) || !isset($_GET['name'])) {
            http_response_code(400);
            die('Invalid request');
        }

        $original_url = base64_decode(urldecode($_GET['file']));
        $renamed_filename = urldecode($_GET['name']);

        if (!filter_var($original_url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            die('Invalid URL');
        }

        // Validate file exists before showing download page
        $validation_result = $this->validate_file_exists($original_url);

        if (!$validation_result['exists']) {
            http_response_code($validation_result['http_code']);

            // Log failed download attempt
            $this->log_failed_download($validation_result['error'] . ': ' . $original_url);

            // Show error page instead of download page
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="robots" content="noindex, nofollow">
                <title>Download Error</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
                    .error-box { background: #ffe6e6; border: 1px solid #ff9999; padding: 30px; border-radius: 8px; max-width: 400px; margin: 0 auto; color: #cc0000; }
                    .error-icon { font-size: 48px; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <div class="error-box">
                    <div class="error-icon">❌</div>
                    <h2>Download Error</h2>
                    <p><strong>File:</strong> <?php echo esc_html($renamed_filename); ?></p>
                    <?php
                    if ($validation_result['http_code'] == 404) {
                        echo '<p>Error 404: The requested file was not found on the server.</p>';
                    } elseif ($validation_result['http_code'] == 403) {
                        echo '<p>Error 403: Access to the file is forbidden.</p>';
                    } elseif ($validation_result['http_code'] >= 500) {
                        echo '<p>Error ' . $validation_result['http_code'] . ': Server error occurred while accessing the file.</p>';
                    } else {
                        echo '<p>Error ' . $validation_result['http_code'] . ': Unable to access the file.</p>';
                    }
                    ?>
                </div>
            </body>
            </html>
            <?php
            exit;
        }

        // Track download attempt
        $this->track_download($original_url, $renamed_filename, 'js_redirect');

        $settings = get_option($this->plugin_name . '_settings');
        $delay = isset($settings['redirect_delay']) ? intval($settings['redirect_delay']) : 1;

        // Generate JavaScript redirect page that hides the real URL
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="robots" content="noindex, nofollow">
            <title>Download Starting...</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
                .download-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 400px; margin: 0 auto; }
                .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto; }
                @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            </style>
        </head>
        <body>
            <div class="download-box">
                <h2>Download Starting...</h2>
                <div class="spinner"></div>
                <p>Your download will start in <span id="countdown"><?php echo $delay; ?></span> seconds</p>
                <p><strong>File:</strong> <?php echo esc_html($renamed_filename); ?></p>
            </div>

            <script>
                var timeLeft = <?php echo $delay; ?>;
                var countdown = document.getElementById('countdown');

                var timer = setInterval(function() {
                    timeLeft--;
                    countdown.textContent = timeLeft;

                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        // The real URL is only revealed to JavaScript, not visible in browser
                        window.location.href = '<?php echo addslashes($original_url); ?>';
                    }
                }, 1000);

                // Prevent back button from exposing URL
                history.pushState(null, null, location.href);
                window.onpopstate = function () {
                    history.go(1);
                };
            </script>
        </body>
        </html>
        <?php
        exit;
    }

    private function process_meta_redirect_download() {
        if (!isset($_GET['file']) || !isset($_GET['name'])) {
            http_response_code(400);
            die('Invalid request');
        }

        $original_url = base64_decode(urldecode($_GET['file']));
        $renamed_filename = urldecode($_GET['name']);

        if (!filter_var($original_url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            die('Invalid URL');
        }

        // Validate file exists before showing download page
        $validation_result = $this->validate_file_exists($original_url);

        if (!$validation_result['exists']) {
            http_response_code($validation_result['http_code']);

            // Log failed download attempt
            $this->log_failed_download($validation_result['error'] . ': ' . $original_url);

            // Show error page instead of download page
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="robots" content="noindex, nofollow">
                <title>Download Error</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
                    .error-box { background: #ffe6e6; border: 1px solid #ff9999; padding: 30px; border-radius: 8px; max-width: 400px; margin: 0 auto; color: #cc0000; }
                    .error-icon { font-size: 48px; margin-bottom: 20px; }
                </style>
            </head>
            <body>
                <div class="error-box">
                    <div class="error-icon">❌</div>
                    <h2>Download Error</h2>
                    <p><strong>File:</strong> <?php echo esc_html($renamed_filename); ?></p>
                    <?php
                    if ($validation_result['http_code'] == 404) {
                        echo '<p>Error 404: The requested file was not found on the server.</p>';
                    } elseif ($validation_result['http_code'] == 403) {
                        echo '<p>Error 403: Access to the file is forbidden.</p>';
                    } elseif ($validation_result['http_code'] >= 500) {
                        echo '<p>Error ' . $validation_result['http_code'] . ': Server error occurred while accessing the file.</p>';
                    } else {
                        echo '<p>Error ' . $validation_result['http_code'] . ': Unable to access the file.</p>';
                    }
                    ?>
                </div>
            </body>
            </html>
            <?php
            exit;
        }

        // Track download attempt
        $this->track_download($original_url, $renamed_filename, 'meta_redirect');

        $settings = get_option($this->plugin_name . '_settings');
        $delay = isset($settings['redirect_delay']) ? intval($settings['redirect_delay']) : 1;

        // Generate meta refresh redirect page
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="robots" content="noindex, nofollow">
            <meta http-equiv="refresh" content="<?php echo $delay; ?>;url=<?php echo esc_attr($original_url); ?>">
            <title>Download Starting...</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
                .download-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 400px; margin: 0 auto; }
                .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto; }
                @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            </style>
        </head>
        <body>
            <div class="download-box">
                <h2>Download Starting...</h2>
                <div class="spinner"></div>
                <p>Your download will start automatically in <?php echo $delay; ?> seconds</p>
                <p><strong>File:</strong> <?php echo esc_html($renamed_filename); ?></p>
                <p><a href="<?php echo esc_attr($original_url); ?>">Click here if download doesn't start</a></p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    private function process_optimized_proxy_download() {
        if (!isset($_GET['file']) || !isset($_GET['name'])) {
            http_response_code(400);
            die('Invalid request');
        }

        $original_url = base64_decode(urldecode($_GET['file']));
        $renamed_filename = urldecode($_GET['name']);

        if (!filter_var($original_url, FILTER_VALIDATE_URL)) {
            http_response_code(400);
            die('Invalid URL');
        }

        // Use optimized streaming without double requests
        $this->stream_file_optimized($original_url, $renamed_filename);
    }

    private function stream_file_optimized($url, $filename) {
        // First, validate the file exists with a HEAD request
        $validation_result = $this->validate_file_exists($url);

        if (!$validation_result['exists']) {
            http_response_code($validation_result['http_code']);

            // Log failed download attempt
            $this->log_failed_download($validation_result['error'] . ': ' . $url);

            // Return proper error message
            if ($validation_result['http_code'] == 404) {
                die('Error 404: The requested file was not found on the server.');
            } elseif ($validation_result['http_code'] == 403) {
                die('Error 403: Access to the file is forbidden.');
            } elseif ($validation_result['http_code'] >= 500) {
                die('Error ' . $validation_result['http_code'] . ': Server error occurred while accessing the file.');
            } else {
                die('Error ' . $validation_result['http_code'] . ': Unable to access the file.');
            }
        }

        // Set download headers with proper content type and length
        $content_type = $validation_result['content_type'] ?: 'application/octet-stream';
        header('Content-Type: ' . $content_type);
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        if ($validation_result['content_length'] > 0) {
            header('Content-Length: ' . $validation_result['content_length']);
        }

        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('X-Accel-Buffering: no'); // Disable Nginx buffering for faster start

        // Track successful download start
        $this->track_download($url, $filename, 'optimized_proxy');

        // Stream file with optimized settings
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 16384); // Smaller buffer for faster start
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) {
            echo $data;
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
            return strlen($data);
        });

        $result = curl_exec($ch);
        $final_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_error($ch) || $final_http_code !== 200) {
            $error = curl_error($ch) ?: 'HTTP ' . $final_http_code;
            curl_close($ch);
            error_log('DFR Optimized streaming error: ' . $error . ' URL: ' . $url);
            // Don't send headers again if streaming already started
            if (!headers_sent()) {
                http_response_code($final_http_code ?: 500);
                die('Download failed: ' . $error);
            }
        }

        curl_close($ch);
        exit;
    }

    private function validate_file_exists($url, $retry_count = 0) {
        $max_retries = 2;

        // Quick HEAD request to validate file
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Increased timeout for validation
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 1); // Minimum bytes per second
        curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, 30); // Abort if speed below limit for 30 seconds

        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_length = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $curl_error = curl_error($ch);

        curl_close($ch);

        // Check for cURL errors
        if ($curl_error) {
            // Retry on timeout or connection errors
            if ($retry_count < $max_retries && (
                stripos($curl_error, 'timeout') !== false ||
                stripos($curl_error, 'connection') !== false ||
                stripos($curl_error, 'couldn\'t connect') !== false
            )) {
                error_log("DFR: Retrying file validation (attempt " . ($retry_count + 2) . "): " . $curl_error);
                sleep(2); // Wait 2 seconds before retry
                return $this->validate_file_exists($url, $retry_count + 1);
            }

            return [
                'exists' => false,
                'http_code' => 500,
                'error' => 'Connection failed: ' . $curl_error,
                'content_type' => null,
                'content_length' => 0
            ];
        }

        // Check HTTP status
        if ($http_code !== 200) {
            return [
                'exists' => false,
                'http_code' => $http_code,
                'error' => 'HTTP ' . $http_code,
                'content_type' => $content_type,
                'content_length' => $content_length
            ];
        }

        // Additional validation: detect if server returns HTML instead of actual file
        if (stripos($content_type, 'text/html') !== false ||
            (stripos($content_type, 'text/') !== false && $content_length > 0 && $content_length < 2000)) {
            return [
                'exists' => false,
                'http_code' => 404,
                'error' => 'File not found - server returned HTML instead of file',
                'content_type' => $content_type,
                'content_length' => $content_length
            ];
        }

        return [
            'exists' => true,
            'http_code' => $http_code,
            'error' => null,
            'content_type' => $content_type,
            'content_length' => $content_length
        ];
    }

    private function process_basic_download() {
        if (!isset($_GET['file']) || !isset($_GET['name'])) {
            $this->log_failed_download('Missing file or name parameter');
            http_response_code(400);
            die('Invalid request');
        }

        $original_url = base64_decode(urldecode($_GET['file']));
        $renamed_filename = urldecode($_GET['name']);

        if (!filter_var($original_url, FILTER_VALIDATE_URL)) {
            $this->log_failed_download('Invalid URL: ' . $original_url);
            http_response_code(400);
            die('Invalid URL');
        }

        // Track download attempt
        $this->track_download($original_url, $renamed_filename, 'basic');

        $file_handler = new DFR_File_Handler();
        $file_handler->validate_and_stream_file($original_url, $renamed_filename);
        exit; // Ensure no further processing after file streaming
    }

    public function track_download($original_url, $filename, $type = 'basic') {
        $settings = get_option($this->plugin_name . '_settings');
        if (!isset($settings['enable_download_tracking']) || !$settings['enable_download_tracking']) {
            return;
        }

        $stats = get_option('dfr_download_stats', array());

        // Initialize stats if needed
        if (!isset($stats['total_downloads'])) $stats['total_downloads'] = 0;
        if (!isset($stats['today_downloads'])) $stats['today_downloads'] = 0;
        if (!isset($stats['last_download_date'])) $stats['last_download_date'] = '';
        if (!isset($stats['failed_downloads'])) $stats['failed_downloads'] = 0;

        // Check if it's a new day
        $today = date('Y-m-d');
        if ($stats['last_download_date'] !== $today) {
            $stats['today_downloads'] = 0;
            $stats['last_download_date'] = $today;
        }

        // Update stats
        $stats['total_downloads']++;
        $stats['today_downloads']++;
        $stats['last_download'] = current_time('mysql');

        // Store detailed log entry
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'filename' => $filename,
            'original_url' => $original_url,
            'type' => $type,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        );

        $download_log = get_option('dfr_download_log', array());
        $download_log[] = $log_entry;

        // Keep only last 100 download entries to prevent database bloating
        if (count($download_log) > 100) {
            $download_log = array_slice($download_log, -100);
        }

        update_option('dfr_download_stats', $stats);
        update_option('dfr_download_log', $download_log);
    }

    public function log_failed_download($error_message) {
        $settings = get_option($this->plugin_name . '_settings');
        if (!isset($settings['log_failed_downloads']) || !$settings['log_failed_downloads']) {
            return;
        }

        // Update failed download count
        $stats = get_option('dfr_download_stats', array());
        if (!isset($stats['failed_downloads'])) $stats['failed_downloads'] = 0;
        $stats['failed_downloads']++;
        update_option('dfr_download_stats', $stats);

        // Store detailed failed download log
        $failed_log_entry = array(
            'timestamp' => current_time('mysql'),
            'error_message' => $error_message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'referer' => $_SERVER['HTTP_REFERER'] ?? 'Direct access'
        );

        $failed_downloads_log = get_option('dfr_failed_downloads_log', array());
        $failed_downloads_log[] = $failed_log_entry;

        // Keep only last 500 failed download entries to prevent database bloating
        if (count($failed_downloads_log) > 500) {
            $failed_downloads_log = array_slice($failed_downloads_log, -500);
        }

        update_option('dfr_failed_downloads_log', $failed_downloads_log);

        // Log to WordPress error log
        error_log("DFR Failed Download: $error_message. IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . ", User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'));
    }

    public function schedule_cleanup() {
        if (!wp_next_scheduled('dfr_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'dfr_daily_cleanup');
        }
    }

    public function daily_cleanup() {
        // Clean up old download logs (keep only last 100 entries)
        $download_log = get_option('dfr_download_log', array());
        if (count($download_log) > 100) {
            $download_log = array_slice($download_log, -100);
            update_option('dfr_download_log', $download_log);
        }

        // Clean up expired tokens
        if (class_exists('DFR_Token_Manager')) {
            $token_manager = new DFR_Token_Manager();
            if (method_exists($token_manager, 'cleanup_expired_tokens')) {
                $token_manager->cleanup_expired_tokens();
            }
        }

        // Reset daily download counter if it's a new day
        $stats = get_option('dfr_download_stats', array());
        $today = date('Y-m-d');
        if (isset($stats['last_download_date']) && $stats['last_download_date'] !== $today) {
            $stats['today_downloads'] = 0;
            $stats['last_download_date'] = $today;
            update_option('dfr_download_stats', $stats);
        }
    }
}

// Initialize the plugin
$GLOBALS['download_file_renamer'] = new DownloadFileRenamer();

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'dfr_activate_plugin');
register_deactivation_hook(__FILE__, 'dfr_deactivate_plugin');

// Helper functions for theme integration
function dfr_get_proxy_download_url($original_url, $custom_expiry_minutes = null) {
    // Check if plugin is active and enabled
    if (!dfr_is_plugin_active() || !dfr_is_proxy_enabled()) {
        return $original_url; // Return original URL as fallback
    }

    // Check if current post is excluded from proxy functionality
    if (dfr_is_current_post_excluded()) {
        return $original_url;
    }

    global $download_file_renamer;
    if ($download_file_renamer && method_exists($download_file_renamer, 'generate_proxy_download_url')) {
        return $download_file_renamer->generate_proxy_download_url($original_url, $custom_expiry_minutes);
    }

    return $original_url; // Fallback to original URL
}

function dfr_get_renamed_filename($url) {
    // Check if plugin is active
    if (!dfr_is_plugin_active()) {
        return basename(parse_url($url, PHP_URL_PATH)); // Return original filename
    }

    global $download_file_renamer;
    if ($download_file_renamer && method_exists($download_file_renamer, 'get_renamed_filename')) {
        return $download_file_renamer->get_renamed_filename($url);
    }

    return basename(parse_url($url, PHP_URL_PATH)); // Fallback to original filename
}

function dfr_is_plugin_active() {
    return is_plugin_active('secure-download-proxy/secure-download-proxy.php') ||
           (defined('DFR_PLUGIN_VERSION') && class_exists('DownloadFileRenamer'));
}

function dfr_is_proxy_enabled() {
    $settings = get_option('secure-download-proxy_settings');
    return isset($settings['enable_proxy']) && $settings['enable_proxy'];
}

function dfr_is_current_post_excluded() {
    global $post;

    // Get current post ID
    $current_post_id = 0;
    if ($post && isset($post->ID)) {
        $current_post_id = $post->ID;
    } elseif (is_admin() && isset($_GET['post'])) {
        $current_post_id = intval($_GET['post']);
    } elseif (isset($_POST['post_ID'])) {
        $current_post_id = intval($_POST['post_ID']);
    }

    if (!$current_post_id) {
        return false; // If we can't determine post ID, don't exclude
    }

    $settings = get_option('secure-download-proxy_settings');
    $excluded_post_ids = isset($settings['excluded_post_ids']) ? $settings['excluded_post_ids'] : '';

    if (empty($excluded_post_ids)) {
        return false;
    }

    // Parse excluded post IDs (supports comma-separated and newline-separated)
    $excluded_ids = array();

    // Split by both commas and newlines
    $raw_ids = preg_split('/[,\n\r]+/', $excluded_post_ids);

    foreach ($raw_ids as $id) {
        $id = trim($id);
        if (is_numeric($id) && $id > 0) {
            $excluded_ids[] = intval($id);
        }
    }

    return in_array($current_post_id, $excluded_ids);
}

/**
 * Plugin activation hook
 */
function dfr_activate_plugin() {
    // Nothing special needed on activation - init() handles defaults
}

/**
 * Plugin deactivation hook - Clean up proxy URLs in database
 */
function dfr_deactivate_plugin() {
    global $wpdb;

    $settings = get_option('secure-download-proxy_settings');
    $proxy_keyword = isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy';

    // Get the site URL to construct the proxy URL pattern
    $site_url = home_url();

    // Patterns to search for proxy URLs
    $proxy_pattern = $site_url . '/?' . $proxy_keyword . '=1&file=';
    $secure_proxy_pattern = $site_url . '/?' . $proxy_keyword . '=1&token=';

    // Search in post content for proxy URLs
    $posts_with_proxy = $wpdb->get_results($wpdb->prepare("
        SELECT ID, post_content
        FROM {$wpdb->posts}
        WHERE post_content LIKE %s
        OR post_content LIKE %s
    ", '%' . $wpdb->esc_like($proxy_pattern) . '%', '%' . $wpdb->esc_like($secure_proxy_pattern) . '%'));

    foreach ($posts_with_proxy as $post) {
        $updated_content = $post->post_content;

        // Find and replace basic proxy URLs
        $basic_pattern = '/(' . preg_quote($site_url, '/') . '\/\?' . preg_quote($proxy_keyword, '/') . '=1&file=([^&\s"\']+))/';
        $updated_content = preg_replace_callback($basic_pattern, function($matches) {
            $encoded_url = urldecode($matches[2]);
            $original_url = base64_decode($encoded_url);

            // Validate that it's a proper URL before replacing
            if (filter_var($original_url, FILTER_VALIDATE_URL)) {
                return $original_url;
            }
            return $matches[1]; // Return original if decode fails
        }, $updated_content);

        // Find and replace secure proxy URLs (these are more complex)
        $secure_pattern = '/(' . preg_quote($site_url, '/') . '\/\?' . preg_quote($proxy_keyword, '/') . '=1&token=([^&\s"\']+))/';
        $updated_content = preg_replace_callback($secure_pattern, function($matches) {
            // For secure URLs, we need to look up the original URL from the token
            $token = $matches[2];

            // Try to get the original URL from token data if token manager exists
            if (class_exists('DFR_Token_Manager')) {
                global $wpdb;
                $token_data = $wpdb->get_var($wpdb->prepare(
                    "SELECT original_url FROM {$wpdb->prefix}dfr_secure_tokens WHERE token = %s",
                    $token
                ));

                if ($token_data && filter_var($token_data, FILTER_VALIDATE_URL)) {
                    return $token_data;
                }
            }

            return $matches[1]; // Return original if we can't decode
        }, $updated_content);

        // Update the post if content changed
        if ($updated_content !== $post->post_content) {
            $wpdb->update(
                $wpdb->posts,
                array('post_content' => $updated_content),
                array('ID' => $post->ID),
                array('%s'),
                array('%d')
            );
        }
    }

    // Also check post meta for any stored proxy URLs
    $meta_with_proxy = $wpdb->get_results($wpdb->prepare("
        SELECT meta_id, meta_value
        FROM {$wpdb->postmeta}
        WHERE meta_value LIKE %s
        OR meta_value LIKE %s
    ", '%' . $wpdb->esc_like($proxy_pattern) . '%', '%' . $wpdb->esc_like($secure_proxy_pattern) . '%'));

    foreach ($meta_with_proxy as $meta) {
        $updated_meta = $meta->meta_value;

        // Apply the same regex replacements as above
        $basic_pattern = '/(' . preg_quote($site_url, '/') . '\/\?' . preg_quote($proxy_keyword, '/') . '=1&file=([^&\s"\']+))/';
        $updated_meta = preg_replace_callback($basic_pattern, function($matches) {
            $encoded_url = urldecode($matches[2]);
            $original_url = base64_decode($encoded_url);

            if (filter_var($original_url, FILTER_VALIDATE_URL)) {
                return $original_url;
            }
            return $matches[1];
        }, $updated_meta);

        $secure_pattern = '/(' . preg_quote($site_url, '/') . '\/\?' . preg_quote($proxy_keyword, '/') . '=1&token=([^&\s"\']+))/';
        $updated_meta = preg_replace_callback($secure_pattern, function($matches) {
            $token = $matches[2];

            if (class_exists('DFR_Token_Manager')) {
                global $wpdb;
                $token_data = $wpdb->get_var($wpdb->prepare(
                    "SELECT original_url FROM {$wpdb->prefix}dfr_secure_tokens WHERE token = %s",
                    $token
                ));

                if ($token_data && filter_var($token_data, FILTER_VALIDATE_URL)) {
                    return $token_data;
                }
            }

            return $matches[1];
        }, $updated_meta);

        // Update the meta if it changed
        if ($updated_meta !== $meta->meta_value) {
            $wpdb->update(
                $wpdb->postmeta,
                array('meta_value' => $updated_meta),
                array('meta_id' => $meta->meta_id),
                array('%s'),
                array('%d')
            );
        }
    }

    // Clean up the cron job
    wp_clear_scheduled_hook('dfr_daily_cleanup');

    // Log the cleanup action
    error_log('DFR Plugin Deactivated: Cleaned up proxy URLs and restored original URLs in database');
}
?>