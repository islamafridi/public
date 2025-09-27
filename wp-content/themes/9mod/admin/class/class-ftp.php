<?php
class FTP
{
    private $ftp_server;
    private $ftp_port;
    private $ftp_user;
    private $ftp_pass;
    private $ftp_directory;
    private $ftp_access_url;
    private $conn_id;

    public function __construct()
    {
        $this->ftp_server = at_options('ftp_server_ip', true);
        $this->ftp_port = at_options('ftp_port', true) ? at_options('ftp_port', true) : 21;
        $this->ftp_user = at_options('ftp_username', true);
        $this->ftp_pass = at_options('ftp_password', true);
        $this->ftp_directory = at_options('ftp_directory', true) ? trailingslashit(at_options('ftp_directory', true)) : '/';
        $this->ftp_access_url = untrailingslashit(at_options('ftp_url', true));
    }

    public function upload_file_to_ftp($file_path, $file_full_name)
    {

        $this->conn_id = @ftp_connect($this->ftp_server, $this->ftp_port, 30);

        if (!$this->conn_id) {
            return ['error' => sprintf(__('Could not connect to "%s". Check again!', 'apktemplates'), $this->ftp_server)];
        }

        if (!@ftp_login($this->conn_id, $this->ftp_user, $this->ftp_pass)) {
            return ['error' => __('Incorrect server data. Check again', 'apktemplates')];
        }

        if (!ftp_pasv($this->conn_id, true)) {
            return ['error' => __('Cannot switch to passive mode in FTP', 'apktemplates')];
        }

        $local_size = filesize($file_path);

        update_option('at_wp_uploading_file_size', $local_size);

        $bytes_uploaded = 0;

        $handle = fopen($file_path, 'rb');

        if (!$handle) {
            return ['error' => __('Failed to open local file for reading', 'apktemplates')];
        }

        $ret = ftp_nb_put($this->conn_id, $this->ftp_directory . $file_full_name, $file_path, FTP_BINARY);

        while ($ret == FTP_MOREDATA) {
            clearstatcache();
            $bytes_uploaded = ftell($handle);
            update_option('at_ftp_uploaded_file_size', $bytes_uploaded);
            usleep(500000);
            $ret = ftp_nb_continue($this->conn_id);
        }

        fclose($handle);

        if ($ret != FTP_FINISHED) {
            $result = ['error' => __('Upload failed!', 'apktemplates') . ' - ' . error_get_last()['message']];
        } else {
            $link_download = $this->ftp_access_url . '/' . $file_full_name;
            $result = ['url' => $link_download];
        }

        ftp_close($this->conn_id);
        return $result;
    }

}