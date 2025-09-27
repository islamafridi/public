<?php
/**
 * Token management for secure downloads
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class DFR_Token_Manager {
    
    private $plugin_name = 'secure-download-proxy';
    
    public function generate_secure_url($original_url, $custom_expiry_minutes = null) {
        $settings = get_option($this->plugin_name . '_settings');
        
        $expiry_minutes = $custom_expiry_minutes !== null ? $custom_expiry_minutes : (isset($settings['link_expiry_minutes']) ? $settings['link_expiry_minutes'] : 360);
        
        // Ensure minimum 5 minutes
        if ($expiry_minutes < 5) {
            $expiry_minutes = 5;
        }
        
        $expiry_time = time() + ($expiry_minutes * 60);
        
        // Create token data that includes everything we need
        $token_data = array(
            'url' => $original_url,
            'expires' => $expiry_time,
            'created' => time()
        );
        
        // Create a reversible token using base64 and hash verification
        $data_string = json_encode($token_data);
        $hash = hash_hmac('sha256', $data_string, wp_salt('secure_auth'));
        $full_token = base64_encode($data_string) . '.' . substr($hash, 0, 16);
        
        // Create short hash for URL (increased to 24 characters for better security)
        $short_hash = substr(hash('sha256', $full_token . time() . wp_generate_password(8, false)), 0, 24);
        
        $expiry_datetime = date('YmdHi', $expiry_time);
        
        // Get renamed filename
        global $download_file_renamer;
        $renamed_filename = $download_file_renamer->get_renamed_filename($original_url);
        
        // Store the mapping
        $this->store_token_mapping($short_hash, $full_token);
        
        // Use standard WordPress URL format
        $short_token = $short_hash . ':' . $expiry_datetime . '/' . $renamed_filename;
        return home_url('/?' . $this->get_proxy_keyword() . '=1&token=' . urlencode($short_token));
    }
    
    private function get_proxy_keyword() {
        $settings = get_option($this->plugin_name . '_settings');
        return isset($settings['proxy_keyword']) ? $settings['proxy_keyword'] : 'download_proxy';
    }
    
    public function process_secure_download() {
        if (!isset($_GET['token'])) {
            http_response_code(400);
            die('Invalid request - token missing');
        }
        
        $token_string = urldecode($_GET['token']);

        // Debug: log the token for troubleshooting
        error_log("DFR Token Debug - Raw token: " . $_GET['token']);
        error_log("DFR Token Debug - Decoded token: " . $token_string);

        // Parse token format: short_hash:datetime/filename (now 24-char hash)
        if (!preg_match('/^([a-f0-9]{24}):(\d{12})\/(.+)$/', $token_string, $matches)) {
            error_log("DFR Token Debug - Regex failed for: " . $token_string);
            http_response_code(400);
            die('Invalid token format: ' . htmlspecialchars($token_string));
        }
        
        $short_hash = $matches[1];
        $expiry_datetime = $matches[2];
        $filename = $matches[3];
        
        // Get the full token from storage
        $full_token = $this->get_token_mapping($short_hash);
        
        if (!$full_token) {
            http_response_code(403);
            die('Token not found or expired');
        }
        
        // Parse the full token
        $token_parts = explode('.', $full_token);
        if (count($token_parts) !== 2) {
            http_response_code(400);
            die('Invalid token structure');
        }
        
        $data_string = base64_decode($token_parts[0]);
        $provided_hash = $token_parts[1];
        
        // Verify hash
        $expected_hash = substr(hash_hmac('sha256', $data_string, wp_salt('secure_auth')), 0, 16);
        if (!hash_equals($expected_hash, $provided_hash)) {
            http_response_code(403);
            die('Invalid token signature');
        }
        
        $token_data = json_decode($data_string, true);
        
        if (!$token_data || !isset($token_data['url'], $token_data['expires'])) {
            http_response_code(400);
            die('Invalid token data');
        }
        
        // Check if token has expired
        if (time() > $token_data['expires']) {
            // Log failed download attempt
            global $download_file_renamer;
            if ($download_file_renamer && method_exists($download_file_renamer, 'log_failed_download')) {
                $download_file_renamer->log_failed_download('Expired secure token accessed');
            }

            // Clean up expired token
            $this->remove_token_mapping($short_hash);
            http_response_code(410);
            die('Download link has expired');
        }
        
        $original_url = $token_data['url'];

        // Debug: log the URL being accessed
        error_log("DFR Secure Download - Attempting to download: " . $original_url);

        // Track secure download
        global $download_file_renamer;
        if ($download_file_renamer && method_exists($download_file_renamer, 'track_download')) {
            $download_file_renamer->track_download($original_url, $filename, 'secure');
        }

        // Use file handler to validate and stream
        error_log("DFR Secure Download - Creating file handler for: " . $filename);
        $file_handler = new DFR_File_Handler();
        $file_handler->validate_and_stream_file($original_url, $filename);
    }
    
    private function store_token_mapping($short_hash, $full_token) {
        $token_storage = get_option('dfr_active_tokens', array());
        
        // Clean up expired tokens first
        $current_time = time();
        foreach ($token_storage as $hash => $token) {
            $token_parts = explode('.', $token);
            if (count($token_parts) === 2) {
                $data = json_decode(base64_decode($token_parts[0]), true);
                if ($data && isset($data['expires']) && $data['expires'] <= $current_time) {
                    unset($token_storage[$hash]);
                }
            }
        }
        
        // Store new token
        $token_storage[$short_hash] = $full_token;
        
        // Limit storage size to prevent database bloating
        if (count($token_storage) > 1000) {
            $token_storage = array_slice($token_storage, -1000, null, true);
        }
        
        update_option('dfr_active_tokens', $token_storage);
    }
    
    private function get_token_mapping($short_hash) {
        $token_storage = get_option('dfr_active_tokens', array());
        return isset($token_storage[$short_hash]) ? $token_storage[$short_hash] : false;
    }
    
    private function remove_token_mapping($short_hash) {
        $token_storage = get_option('dfr_active_tokens', array());
        if (isset($token_storage[$short_hash])) {
            unset($token_storage[$short_hash]);
            update_option('dfr_active_tokens', $token_storage);
        }
    }
    
    /**
     * Clean up expired tokens (can be called via cron)
     */
    public function cleanup_expired_tokens() {
        $token_storage = get_option('dfr_active_tokens', array());
        $current_time = time();
        $cleaned = false;
        
        foreach ($token_storage as $hash => $token) {
            $token_parts = explode('.', $token);
            if (count($token_parts) === 2) {
                $data = json_decode(base64_decode($token_parts[0]), true);
                if ($data && isset($data['expires']) && $data['expires'] <= $current_time) {
                    unset($token_storage[$hash]);
                    $cleaned = true;
                }
            }
        }
        
        if ($cleaned) {
            update_option('dfr_active_tokens', $token_storage);
        }
        
        return $cleaned;
    }
}
?>