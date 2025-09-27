<?php
class AT_Upload_APK
{
    private $upload_dir;

    private $post_id;
    private $file_url;
    private $file_version;
    private $file_size;

    private $file_base_name;
    private $file_name;
    private $file_extension;
    private $file_full_name;
    private $file_path;
    private $file_path_url;
    private $cache_path;
    private $cache_url;
    private $file_attachment_id;
    private $file_storage;

    //For Gdrive Upload
    private $is_file_import;

    private $file_download_url;
    private $download_info = [];
    private $response = [];

    public function __construct($post_id, $file_url, $file_version, $file_size)
    {
        $this->upload_dir = wp_upload_dir();

        $this->post_id = $post_id;
        $this->file_url = $file_url;
        $this->file_version = $file_version;
        $this->file_size = $file_size;

        $this->file_base_name = basename($this->file_url);

        $pattern = '/^(.*?)-\d+/';

        preg_match($pattern, $this->file_base_name, $matches);

        if (!empty($matches[1])) {
            $this->file_name = $matches[1] . '-' . str_replace('.', '-', $this->file_version);
        } else {
            $this->file_name = pathinfo($this->file_base_name, PATHINFO_FILENAME);
        }

        $this->file_extension = pathinfo($this->file_url, PATHINFO_EXTENSION);
        $this->file_full_name = $this->file_name . '.' . $this->file_extension;
        $this->file_path = $this->upload_dir['apk_path'] . '/';
        $this->file_path_url = $this->upload_dir['apk_path_url'] . '/';
        $this->cache_path = $this->upload_dir['apkt_cache_dir'] . '/';
        $this->cache_url = $this->upload_dir['apkt_cache_url'] . '/';
        $this->file_storage = at_options('upload_storage', 'my-server');
    }

    public function calculate_progress()
    {
        $storage = $this->file_storage;
        $is_file_import = get_option('at_is_file_import_to_wp', false);
    
        switch ($storage) {
            case 'my-server':
                $existing_file_id = attachment_url_to_postid($this->file_path_url . $this->file_full_name);

                if ($existing_file_id) {
                    $total_size = filesize($this->file_path . $this->file_full_name);
                    $current_size = filesize($this->file_path . $this->file_full_name);
                } else {
                    $total_size = get_option('at_wp_uploading_file_size', 0);
                    $current_size = filesize($this->file_path . $this->file_full_name);
                }
                break;
    
            case 'gdrive':
                $total_size = get_option('at_wp_uploading_file_size', 0);
                $current_size = $is_file_import ? filesize($this->cache_path . $this->file_full_name) : get_option('at_gp_uploaded_file_size', 0);
                break;
    
            case 'ftp':
                $total_size = get_option('at_wp_uploading_file_size', 0);
                $current_size = $is_file_import ? filesize($this->cache_path . $this->file_full_name) : get_option('at_ftp_uploaded_file_size', 0);
                break;
    
            default:
                $total_size = 0;
                $current_size = 0;
                break;
        }
    
        $progress = ($total_size > 0) ? (100 * $current_size) / $total_size : 0;
        $progress = floor($progress);
    
        if ($is_file_import) {
            return ['progress' => $progress, 'downloaded_size' => apkt_format_bytes($current_size), 'download_total_size' => apkt_format_bytes($total_size), 'uploaded_size' => 'N/A', 'total_size' => 'N/A', 'is_file_import' => $is_file_import];
        } else {
            return ['progress' => $progress, 'uploaded_size' => apkt_format_bytes($current_size), 'total_size' => apkt_format_bytes($total_size), 'downloaded_size' => 'N/A', 'download_total_size' => 'N/A', 'is_file_import' => $is_file_import];
        }
    }

    private function upload_storage()
    {
        switch ($this->file_storage) {
            case 'my-server':
                $upload = $this->upload_to_wp();
                break;
            case 'gdrive':
                $upload = $this->upload_to_gd();
                break;
            case 'ftp':
                $upload = $this->upload_to_ftp();
                break;
        }

        return $upload;
    }

    private function download_file($file_url, $save_path)
    {
        $ip = rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $file_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-Forwarded-For: ' . $ip,
        ));

        $fp = fopen($save_path, 'w');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        $response = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $response;
    }

    private function attach_file_to_wp()
    {
        $file_mime = wp_check_filetype($this->file_full_name, null);

        $attachment = array(
            'post_title' => $this->file_name,
            'post_mime_type' => $file_mime["type"],
            'post_content' => '',
            'post_status' => 'inherit',
        );

        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = wp_insert_attachment($attachment, $this->file_path . $this->file_full_name);

        if (!is_wp_error($attachment_id)) {
            $this->file_attachment_id = $attachment_id;
        } else {
            $this->file_attachment_id = 0;
        }
    }

    private function upload_to_wp()
    {
        $existing_file_id = attachment_url_to_postid($this->file_path_url . $this->file_full_name);

        if (!$existing_file_id) {
            update_option('at_wp_uploading_file_size', remote_filesize($this->file_url));

            $file_save_path = $this->file_path . $this->file_full_name;

            $download_file = $this->download_file($this->file_url, $file_save_path);

            if ($download_file !== false) {
                sleep(2);
                $this->attach_file_to_wp();
                $this->file_download_url = $this->file_path_url . $this->file_full_name;
                $this->set_download_links();
                $this->response['status'] = 'success';
                $this->response['data']['message'] = __(strtoupper($this->file_extension) . ' File uploaded to your server.', 'apktemplates');
            } else {
                $this->response['status'] = 'error';
                $this->response['data']['message'] = __(strtoupper($this->file_extension) . ' File not uploaded to your server.', 'apktemplates');
            }

            sleep(3);
            update_option('at_wp_uploading_file_size', 0);
        } else {
            $this->response['status'] = 'warning';
            $this->response['data']['message'] = __(strtoupper($this->file_extension) . ' File already exist in your server.', 'apktemplates');
        }
    }

    private function upload_to_gd()
    {
        if (!get_option('at_gdrive_token', null)) {
            $this->response['status'] = 'warning';
            $this->response['data']['message'] = __('You have not connected to Google Drive.', 'apktemplates');
            return;
        }
    
        update_option('at_wp_uploading_file_size', remote_filesize($this->file_url));
    
        $file_save_path = $this->cache_path . $this->file_full_name;
        $download_file = $this->download_file($this->file_url, $file_save_path);
    
        if ($download_file !== false) {
            sleep(2);

            update_option('at_wp_uploading_file_size', 0);
            update_option('at_gp_uploaded_file_size', 0);
            update_option('at_is_file_import_to_wp', false);
    
            $file_download_path = $this->cache_path . $this->file_full_name;
    
            $gdrive = new APKT_GoogleDrive();
            $folder_name = get_option('at_gdrive_folder_name', null);
    
            $folder_id = ($folder_name) ? $gdrive->create_folder($folder_name) : null;
    
            if (!empty($file_download_path)) {
                $upload_result = $gdrive->upload_file_to_drive($file_download_path, $this->file_full_name, $folder_id);
    
                if (isset($upload_result['url'])) {
                    $this->file_download_url = $upload_result['url'];
                    $this->set_download_links();
                    $this->response['status'] = 'success';
                    $this->response['data']['message'] = strtoupper($this->file_extension) . ' file successfully uploaded to Google Drive.';
                } elseif (isset($upload_result['error'])) {
                    $error_message = $upload_result['error'];
                    $this->response['status'] = 'error';
                    $this->response['data']['message'] = $error_message;
                }
            }
            sleep(3);
        } else {
            $this->response['status'] = 'error';
            $this->response['data']['message'] = __(strtoupper($this->file_extension) . ' File not downloaded to your server.', 'apktemplates');
        }
        unlink($file_save_path);
    }
    
    private function upload_to_ftp()
    {
        if (!at_options('ftp_server_ip', true) || !at_options('ftp_username', true) || !at_options('ftp_password', true) || !at_options('ftp_url', true)) {
            $this->response['warning']['text'] = __('Enter FTP connection details to upload files to the FTP server.', 'apktemplates');
            return;
        }
    
        update_option('at_wp_uploading_file_size', remote_filesize($this->file_url));
    
        $file_save_path = $this->cache_path . $this->file_full_name;
        $download_file = $this->download_file($this->file_url, $file_save_path);
    
        if ($download_file !== false) {
            sleep(2);

            update_option('at_wp_uploading_file_size', 0);
            update_option('at_ftp_uploaded_file_size', 0);
            update_option('at_is_file_import_to_wp', false);
        
            if ($download_file !== false) {
                $file_download_path = $this->cache_path . $this->file_full_name;
                $ftp = new FTP();
                $upload_result = $ftp->upload_file_to_ftp($file_download_path, $this->file_full_name);
                if (isset($upload_result['url'])) {
                    $this->file_download_url = $upload_result['url'];
                    $this->set_download_links();
                    $this->response['status'] = 'success';
                    $this->response['data']['message'] = strtoupper($this->file_extension) . ' file successfully uploaded to FTP.';
                } elseif (isset($upload_result['error'])) {
                    $error_message = $upload_result['error'];
                    $this->response['status'] = 'warning';
                    $this->response['data']['message'] = __('FTP file not uploaded. Error: ', 'apktemplates') . $error_message;
                } else {
                    $this->response['status'] = 'warning';
                    $this->response['data']['message'] = __('FTP upload done with errors. Check the FTP credentials in panel.', 'apktemplates');
                }
            } else {
                $this->response['status'] = 'warning';
                $this->response['data']['message'] = __('Failed to save the file to the temporary path.', 'apktemplates');
            }
            sleep(3);
        } else {
            $this->response['status'] = 'warning';
            $this->response['data']['message'] = __('Failed to download the file.', 'apktemplates');
        }
        unlink($file_save_path);
    }
    

    private function set_download_links()
    {
        $mod_feature = '';
        $is_apt_advanced_options = at_options('apt_is_advanced_options', false);
        $apt_mod_feature = at_options('apt_mod_feature', '');

        if ($is_apt_advanced_options && !empty($apt_mod_feature)) {
            $mod_feature = $apt_mod_feature;
        }

        $download_info_data = [
            'download_name' => strtoupper($this->file_extension),
            'download_version' => $this->file_version,
            'download_mod_info' => $mod_feature,
            'download_size' => apkt_format_bytes($this->file_size),
            'download_url' => $this->file_download_url,
        ];

        array_push($this->download_info, $download_info_data);

        update_post_meta($this->post_id, "repeatable_download_link", $this->download_info);

    }

    public function upload()
    {
        if (isset($this->file_url) && $this->file_url !== null) {
            $this->upload_storage();
            return $this->response;
        } else {
            $this->response['status'] = 'error';
            $this->response['data']['message'] = __('File not uploaded due to empty URL.', 'apktemplates');
            return $this->response;
        }

    }
}
