<?php
/* Google Drive Auth Actions by URL */
function verify_gdrive_access()
{
    $code = isset($_GET['code']) ? $_GET['code'] : null;
    $at_upload = isset($_GET['at_upload']) ? $_GET['at_upload'] : null;

    if ($code && $at_upload == 'gdrive') {

        if (!current_user_can('administrator')) {
            return;
        }

        $gdrive = new APKT_GoogleDrive();
        if ($gdrive->get_client()) {
            $token = $gdrive->get_client()->fetchAccessTokenWithAuthCode($code);

            $gdrive->get_client()->setAccessToken($token);

            update_option('at_gdrive_token', json_encode($token));
            header("Location: " . admin_url('admin.php?page=at-panel#storage'));
            exit;
        }
    }
}
add_action('init', 'verify_gdrive_access');

function admin_init_storage_url_action()
{
    $action = isset($_GET['action']) ? $_GET['action'] : null;

    if ($action == "connect_gdrive") {
        $gdrive = new APKT_GoogleDrive();
        if ($gdrive->get_client()) {
            if (!get_option('at_gdrive_token')) {
                header("Location: " . $gdrive->get_client()->createAuthUrl());
                exit;
            }
        }
    }

    if ($action == "disconnect_gdrive") {
        delete_option('at_gdrive_token');
        header("Location: " . admin_url('admin.php?page=at-panel#storage'));
        exit;
    }

    if ($action == "test_ftp") {
        $ftp_server = at_options('ftp_server_ip', true);
        $ftp_port = at_options('ftp_port', true) ? at_options('ftp_port', true) : 21;
        $ftp_username = at_options('ftp_username', true);
        $ftp_password = at_options('ftp_password', true);
        $ftp_directory = at_options('ftp_directory', true) ? trailingslashit(at_options('ftp_directory', true)) : '/';
        $ftp_access_url = untrailingslashit(at_options('ftp_url', true));

        $conn_id = @ftp_connect($ftp_server, $ftp_port, 30) or die(sprintf(__('Could not connect to "%s". Check again', 'apktemplates'), $ftp_server));

        if (!$ftp_access_url) {
            die(__('Enter valid URL in URL field!', 'apktemplates'));
        }

        if (@ftp_login($conn_id, $ftp_username, $ftp_password)) {
            ftp_pasv($conn_id, true) or die(__('Cannot switch to passive mode', 'apktemplates'));

            $filename = 'sample-file.txt';
            $contents = 'Thank you be a part of apktemplates. FTP successfully working :)';
            $tmp_file = tmpfile();
            fwrite($tmp_file, $contents);
            rewind($tmp_file);
            $tmp_data = stream_get_meta_data($tmp_file);

            if (@ftp_put($conn_id, $ftp_directory . $filename, $tmp_data['uri'], FTP_ASCII)) {
                echo '<p><b>' . __('sample file has been created to your server.', 'apktemplates') . '</b></p>';

                if (!$ftp_access_url) {
                    echo '<p>' . __('Access URL is must needed for generate the direct link of uploaded files. So fill the access URL and test again!', 'apktemplates') . '</p>';
                } else {
                    echo '<p>' . sprintf(__('Access the file through this %s', 'apktemplates'), '<a href="' . $ftp_access_url . $ftp_directory . $filename . '" target="_blank">' . __('link', 'apktemplates') . '</a>') . '. ' . __('If you cannot access the link you must enter the URL field correctly.', 'apktemplates') . '</p>';
                    echo '<p>' . __('If you accessed the link and the text "Thank you be a part of apktemplates. FTP successfully working :)" appeared then the connection was successful.', 'apktemplates') . '</p>';
                }
            } else {
                echo __('The test file could not be generated. The directory you have placed may not exist.', 'apktemplates') . ' - ' . error_get_last()['message'];
            }
            fclose($tmp_file);
        } else {
            echo __('Incorrect FTP server data. Check again please!', 'apktemplates');
        }
        ftp_close($conn_id);

        exit;
    }

}
add_action('admin_init', 'admin_init_storage_url_action');

/* Initializations */
function at_options($option, $default = false)
{
    $value = get_option('at_' . $option, false);

    if ($value !== false) {
        return $value;
    } else {
        return ($default !== false) ? $default : '';
    }
}

function at_apk_upload_dir($uploads)
{
    $apk_dir = '/apktemplates/apk';
    $cache_dir = '/apktemplates/cache';

    $apk_dir_path = $uploads['basedir'] . $apk_dir;
    $apk_dir_url = $uploads['baseurl'] . $apk_dir;

    $cache_dir_path = $uploads['basedir'] . $cache_dir;
    $cache_dir_url = $uploads['baseurl'] . $cache_dir;

    if(!file_exists($cache_dir_path)) {
        mkdir($cache_dir_path, 0755, true);
    }

    $current_year = date('Y');
    $current_month = date('m');

    $year_dir = $apk_dir_path . '/' . $current_year;
    if (!file_exists($year_dir)) {
        mkdir($year_dir, 0755, true);
    }

    $month_dir = $year_dir . '/' . $current_month;
    if (!file_exists($month_dir)) {
        mkdir($month_dir, 0755, true);
    }

    $uploads['apk_path'] = $apk_dir_path . '/' . $current_year . '/' . $current_month;
    $uploads['apk_path_url'] = $apk_dir_url . '/' . $current_year . '/' . $current_month;
    $uploads['apk_sub_dir'] = '/' . $current_year . '/' . $current_month;
    $uploads['apk_base_dir'] = $apk_dir_path;
    $uploads['apk_base_url'] = $apk_dir_url;
    $uploads['apkt_cache_dir'] = $cache_dir_path;
    $uploads['apkt_cache_url'] = $cache_dir_url;


    return $uploads;
}
add_filter('upload_dir', 'at_apk_upload_dir');

function apkt_apk_media_filter()
{
    global $pagenow, $typenow;

    if ($pagenow == 'upload.php' && $typenow == 'attachment') {
        echo '<select name="file_filter">';
        echo '<option value="">All APK Files</option>';
        echo '<option value="apk" ' . (isset($_GET['filter_by_apk']) && $_GET['filter_by_apk'] == 'apk' ? 'selected' : '') . '>APK Files</option>';
        echo '</select>';
    }
}
add_action('restrict_manage_posts', 'apkt_apk_media_filter');

function apkt_apk_media_query($query)
{
    global $pagenow, $typenow, $wpdb;

    if ($pagenow == 'upload.php' && $typenow == 'attachment' && isset($_GET['file_filter']) && $_GET['file_filter'] == 'apk') {
        $query->set('meta_query', array(
            array(
                'key' => '_wp_attached_file',
                'value' => '.apk$',
                'compare' => 'REGEXP'
            )
        ));
    }
}
add_action('pre_get_posts', 'apkt_apk_media_query');

/* APK Importer */

function get_http_response_code($url)
{
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        return false;
    }

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $ip = rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "REMOTE_ADDR: $ip",
        "HTTP_X_FORWARDED_FOR: $ip",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        "Referer: http://m.apkpure.com",
    ));

    curl_exec($ch);

    $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return $response_code === 200;
}

function validate_gplay_url($gplay_url)
{
    if (empty($gplay_url) || !strpos($gplay_url, 'play.google.com')) {
        return false;
    } else {
        return true;
    }
}

function validate_gplay_id_in_url($gplay_url)
{
    if (preg_match("/\bid=([^\&]+)/", $gplay_url, $matches)) {
        return true;
    } else {
        return false;
    }
}

function get_parsed_category($category)
{
    $parent_category = __('Apps', 'apktemplates');

    if (!empty($category) && strpos($category, 'GAME_') !== false) {
        $parent_category = __('Games', 'apktemplates');
    }

    return $parent_category;
}

function get_parsed_subcategory($category)
{
    $sub_category = '';

    if (!empty($category)) {
        $sub_category = strtolower(str_replace('GAME_', '', $category));
        $sub_category = str_replace('_', '-', $sub_category);
    }

    return $sub_category;
}

function get_android_version_by_sdk($sdk_version)
{
    $android_versions = [
        1 => '1.0',
        2 => '1.1',
        3 => '1.5',
        4 => '1.6',
        5 => '2.0',
        6 => '2.0.1',
        7 => '2.1',
        8 => '2.2',
        9 => '2.3',
        10 => '2.3.3',
        11 => '3.0',
        12 => '3.1',
        13 => '3.2',
        14 => '4.0',
        15 => '4.0.3',
        16 => '4.1',
        17 => '4.2',
        18 => '4.3',
        19 => '4.4',
        20 => '4.4W',
        21 => '5.0',
        22 => '5.1',
        23 => '6.0',
        24 => '7.0',
        25 => '7.1',
        26 => '8.0',
        27 => '8.1',
        28 => '9.0',
        29 => '10.0',
        30 => '11.0',
        31 => '12.0',
        32 => '12.1',
        33 => '13.0',
        34 => '14.0',
    ];

    return isset($android_versions[$sdk_version]) ? $android_versions[$sdk_version] : '';
}

/* Panel */
function is_server_setting_valid($value, $limit)
{
    if ($value == -1) {
        return 999999;
    }

    if (preg_match('/^(\d+)(.)$/', $value, $matches)) {
        if ($matches[2] == 'G') {
            $value = $matches[1] * 1024 * 1024 * 1024;
        } else if ($matches[2] == 'M') {
            $value = $matches[1] * 1024 * 1024;
        } else if ($matches[2] == 'K') {
            $value = $matches[1] * 1024;
        }
    }
    return ($value >= $limit * 1024 * 1024);
}

function get_term_name_by_id($term_id)
{
    $term_info = get_term(intval($term_id));

    if (!is_wp_error($term_info) && isset($term_info->name)) {
        return esc_html($term_info->name);
    }

    return '';
}

function removeslashes_deep($string)
{
    return stripslashes(str_replace('\\', '', $string));
}

/* WordPress Inits */
function remove_at_admin_footer_wp_text()
{
    if (isset($_GET['page']) && in_array($_GET['page'], array('at-apk-importer', 'at-panel'))) {
        add_filter('admin_footer_text', function ($content) {
            return '<em>Thank your for choosing <a href="https://9mod.cc" target="_blank">9mod.cc</a></em>';
        }, 11);
    }
}
add_action('admin_init', 'remove_at_admin_footer_wp_text');

function remote_filesize($url)
{
    static $regex = '/^Content-Length: *+\K\d++$/im';
    if (!$fp = @fopen($url, 'rb')) {
        return false;
    }
    if (
        isset($http_response_header) &&
        preg_match($regex, implode("\n", $http_response_header), $matches)
    ) {
        return (int) $matches[0];
    }
    return strlen(stream_get_contents($fp));
}

function apkt_format_bytes($get_bytes)
{
    $bytes = (int) $get_bytes;
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes . ' Bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes . ' Bytes';
    } else {
        $bytes = '0 Bytes';
    }

    return $bytes;
}

function apkt_set_post_thumbnail_from_url($post_id, $url) {
    $image_id = media_sideload_image($url, $post_id, null, 'id');
    
    if (!is_wp_error($image_id)) {
        return $image_id;
    }
    
    return 0;
}

function apkt_get_folder_size($path) {
    $total_size = 0;

    $dir = opendir($path);

    while ($file = readdir($dir)) {
        if ($file != '.' && $file != '..') {
            $file_path = $path . DIRECTORY_SEPARATOR . $file;

            if (is_dir($file_path)) {
                $total_size += apkt_get_folder_size($file_path);
            } else {
                $total_size += filesize($file_path);
            }
        }
    }

    closedir($dir);

    return $total_size;
}

function apkt_delete_files($dir) {
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        if (is_dir($file)) {
            apkt_delete_files($file);
        } else {
            unlink($file);
        }
    }
    rmdir($dir);
}