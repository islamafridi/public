<?php
/**
 * File handling and streaming for Download File Renamer
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class DFR_File_Handler {
    
    private $plugin_name = 'secure-download-proxy';
    
    public function validate_and_stream_file($original_url, $renamed_filename) {
        error_log("DFR File Handler - Starting validation for: " . $original_url);

        // Check allowed domains
        $settings = get_option($this->plugin_name . '_settings');
        if (!empty($settings['allowed_domains'])) {
            $allowed_domains = array_filter(array_map('trim', explode("\n", $settings['allowed_domains'])));
            $url_domain = parse_url($original_url, PHP_URL_HOST);

            error_log("DFR File Handler - Domain check: " . $url_domain . " against allowed domains: " . implode(', ', $allowed_domains));

            if (!in_array($url_domain, $allowed_domains)) {
                error_log("DFR File Handler - Domain NOT allowed: " . $url_domain);
                http_response_code(403);
                die('Domain not allowed: ' . $url_domain);
            }
        }

        error_log("DFR File Handler - Domain check passed, starting file stream");

        // Stream the file
        $this->stream_file($original_url, $renamed_filename);
    }
    
    public function stream_file($url, $filename) {
        error_log("DFR File Handler - Starting stream for URL: " . $url);
        error_log("DFR File Handler - Filename: " . $filename);

        // Get file headers first
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        
        $headers = curl_exec($ch);

        if (curl_error($ch)) {
            $error = curl_error($ch);
            error_log("DFR File Handler - cURL error: " . $error);
            curl_close($ch);
            http_response_code(500);
            die('Failed to fetch file: ' . $error);
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        error_log("DFR File Handler - HTTP response code: " . $http_code);
        if ($http_code !== 200) {
            curl_close($ch);

            // Log failed download attempt
            error_log("DFR Failed Download - HTTP {$http_code}: {$url}");

            // Set appropriate response code and show user-friendly error
            http_response_code($http_code);
            if ($http_code == 404) {
                die('Error 404: The requested file was not found on the server.');
            } elseif ($http_code == 403) {
                die('Error 403: Access to the file is forbidden.');
            } elseif ($http_code >= 500) {
                die('Error ' . $http_code . ': Server error occurred while accessing the file.');
            } else {
                die('Error ' . $http_code . ': Unable to access the file.');
            }
        }
        
        $content_length = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        // Additional validation: detect if server returns HTML instead of actual file
        if (stripos($content_type, 'text/html') !== false ||
            (stripos($content_type, 'text/') !== false && $content_length > 0 && $content_length < 2000)) {
            error_log("DFR File Handler - Detected HTML response instead of file: Content-Type: {$content_type}, Size: {$content_length}");

            // Log failed download attempt
            global $download_file_renamer;
            if ($download_file_renamer && method_exists($download_file_renamer, 'log_failed_download')) {
                $download_file_renamer->log_failed_download('File not found - server returned HTML instead of file: ' . $url);
            }

            // Clear any output buffers and return clean 404 error
            while (ob_get_level()) {
                ob_end_clean();
            }
            header_remove();
            http_response_code(404);
            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            die('Error 404: File not found');
        }
        
        // Set download headers
        header('Content-Type: ' . ($content_type ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        if ($content_length > 0) {
            header('Content-Length: ' . $content_length);
        }
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        // Stream the actual file
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) {
            echo $data;
            return strlen($data);
        });
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        
        $result = curl_exec($ch);
        $final_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            error_log('DFR Download streaming error: ' . $error . ' URL: ' . $url);
            http_response_code(500);
            die('Error: Failed to stream file - ' . $error);
        }

        // Check if the file actually streamed successfully
        if ($final_http_code !== 200) {
            curl_close($ch);
            error_log("DFR Streaming failed - HTTP {$final_http_code}: {$url}");
            http_response_code($final_http_code);
            if ($final_http_code == 404) {
                die('Error 404: The file was not found or has been moved.');
            } else {
                die('Error ' . $final_http_code . ': Failed to download the file.');
            }
        }

        curl_close($ch);
        exit; // Ensure no further processing after file streaming
    }
}
?>