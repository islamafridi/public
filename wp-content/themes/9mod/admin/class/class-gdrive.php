<?php

class APKT_GoogleDrive {

    public function get_client() {
        $client_id = at_options('gdrive_client_id', true);
        $client_secret = at_options('gdrive_client_secret', true);

        if (!$client_id || !$client_secret) {
            return false;
        }

        require_once get_template_directory() . '/admin/inc/modules/google-api-php-client-master/vendor/autoload.php';

        $redirect_uri = add_query_arg('at_upload', 'gdrive', get_site_url());

        $config = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $redirect_uri,
        ];

        $client = new Google_Client($config);

        $client->setScopes(Google_Service_Drive::DRIVE);
        $client->setPrompt('select_account consent');
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');

        if ($access_token = json_decode(get_option('at_gdrive_token'), true)) {
            $client->setAccessToken($access_token);
        }

        if ($client->isAccessTokenExpired() && $refresh_token = $client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($refresh_token);
            $new_access_token = $client->getAccessToken();

            if ($new_access_token) {
                update_option('at_gdrive_token', json_encode($new_access_token));
            }
        }

        return $client;
    }

    public function create_folder($folder_name, $parent_folder_id = null) {
        $client = $this->get_client();

        $existing_folders = $this->check_folder_exists($folder_name);

        if (empty($existing_folders)) {
            $service = new Google_Service_Drive($client);
            $folder = new Google_Service_Drive_DriveFile();

            $folder->setName($folder_name);
            $folder->setMimeType('application/vnd.google-apps.folder');

            if (!empty($parent_folder_id)) {
                $folder->setParents([$parent_folder_id]);
            }

            $result = $service->files->create($folder);

            return isset($result['id']) ? $result['id'] : false;
        }

        return $existing_folders[0]['id'];
    }

    private function check_folder_exists($folder_name) {
        $client = $this->get_client();
        $service = new Google_Service_Drive($client);

        $parameters['q'] = "mimeType='application/vnd.google-apps.folder' and name='$folder_name' and trashed=false";

        $files = $service->files->listFiles($parameters);
        $existing_folders = [];

        foreach ($files as $file) {
            $existing_folders[] = $file;
        }

        return $existing_folders;
    }

    public function upload_file_to_drive($file_path, $file_full_name, $parent_folder_id = null, $contents = '') {
        $client = $this->get_client();
        $service = new Google_Service_Drive($client);
        $file = new Google_Service_Drive_DriveFile();
        $file->setName($file_full_name);
        update_option('at_wp_uploading_file_size', remote_filesize($file_path));
    
        if (!empty($parent_folder_id)) {
            $file->setParents([$parent_folder_id]);
        }
    
        try {
            $client->setDefer(true);
        
            $request = $service->files->create(
                $file,
                [
                    'uploadType' => 'resumable',
                    'fields' => 'id',
                ]
            );
        
            $chunk_size_bytes = 1 * 1024 * 1024;
            $media = new Google_Http_MediaFileUpload(
                $client,
                $request,
                'application/octet-stream',
                null,
                true,
                $chunk_size_bytes
            );
        
            $media->setFileSize(remote_filesize($file_path));
        
            $status = false;
            $handle = fopen($file_path, "rb");
            $size_uploaded = 0;

            while (!$status && !feof($handle)) {
                $chunk = $this->read_file_chunk($handle, $chunk_size_bytes);
                $size_uploaded += strlen($chunk);
                update_option('at_gp_uploaded_file_size', $size_uploaded);

                $status = $media->nextChunk($chunk);
            }
        
            $result = $status ? $status : false;
            fclose($handle);
        
            $client->setDefer(false);
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
        
        if ($result && isset($result['id'])) {
            $file_id = $result['id'];
        
            $new_permission = new Google_Service_Drive_Permission();
            $new_permission->setType('anyone');
            $new_permission->setRole('reader');
        
            try {
                $service->permissions->create($file_id, $new_permission);
            } catch (Exception $e) {
                return ['error' => json_decode($e->getMessage(), true)['error']];
            }
        }
        
        $file_dl = 'https://drive.google.com/uc?export=download&id=' . $file_id;
        
        return ['url' => $file_dl];
    }

    private function read_file_chunk($handle, $chunk_size) {
        $read_chunk = fread($handle, $chunk_size);
        return $read_chunk;
    }
}
