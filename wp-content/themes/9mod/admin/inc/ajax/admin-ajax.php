<?php
/* APK Import Ajax */

function at_gp_create_post()
{
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'gplay_nonce')) {
        return json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
    }

    $apk_url = filter_input(INPUT_POST, 'gplay_url', FILTER_SANITIZE_URL);
    $is_advanced_options = filter_input(INPUT_POST, 'is_advanced_options', FILTER_VALIDATE_BOOLEAN);

    update_option('at_is_advanced_options', $is_advanced_options) || add_option('at_is_advanced_options', $is_advanced_options);

    $is_advanced_options = at_options('is_advanced_options', false);

    if ($is_advanced_options) {
        $post_status = sanitize_text_field($_POST['post_status']);
        $post_title_start = sanitize_text_field($_POST['post_title_start']);
        $post_title_end = sanitize_text_field($_POST['post_title_end']);
        $mod_feature = sanitize_text_field($_POST['mod_feature']);
        $post_thumbnail_format = sanitize_text_field($_POST['post_thumbnail_format']);
        $post_thumbnail_quality = sanitize_text_field($_POST['post_thumbnail_quality']);
        $import_screenshots = filter_var($_POST['import_screenshots'], FILTER_VALIDATE_BOOLEAN);
        $post_screenshots_format = sanitize_text_field($_POST['post_screenshots_format']);
        $post_language = sanitize_text_field($_POST['post_language']);

        update_option('at_post_status', $post_status);
        update_option('at_post_title_start', $post_title_start);
        update_option('at_post_title_end', $post_title_end);
        update_option('at_mod_feature', $mod_feature);
        update_option('at_post_thumbnail_format', $post_thumbnail_format);
        update_option('at_post_thumbnail_quality', $post_thumbnail_quality);
        update_option('at_import_screenshots', $import_screenshots);
        update_option('at_post_screenshots_format', $post_screenshots_format);
        update_option('at_post_language', $post_language);
    }

    $apk_data = new AT_Google_Play();
    $response = $apk_data->extract($apk_url, 'apk');
    $response = json_encode($response);

    echo $response;
    exit;
}
add_action('wp_ajax_at_gp_create_post', 'at_gp_create_post');

function at_gp_search_posts()
{
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'gplay_nonce')) {
        return json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
    }

    $search_query = strtolower($_POST['search_query']);
    $base_url = 'https://play.google.com/store/search?c=apps&q=';
    $search_url = $base_url . urlencode($search_query);

    $search_data = new AT_Google_Play();
    $response = $search_data->extract($search_url, 'search');
    $response = json_encode($response);
    echo $response;

    exit;
}

add_action('wp_ajax_at_gp_search_posts', 'at_gp_search_posts');

function at_apt_create_post()
{
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'aptoide_nonce')) {
        return json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
    }

    $apt_url = filter_input(INPUT_POST, 'apt_url', FILTER_SANITIZE_URL);
    $is_advanced_options = filter_input(INPUT_POST, 'is_advanced_options', FILTER_VALIDATE_BOOLEAN);

    update_option('at_apt_is_advanced_options', $is_advanced_options) || add_option('at_apt_is_advanced_options', $is_advanced_options);
    update_option('at_wp_uploading_file_size', 0) || add_option('at_wp_uploading_file_size', 0);
    update_option('at_gp_uploaded_file_size', 0) || add_option('at_gp_uploaded_file_size', 0);
    update_option('at_ftp_uploaded_file_size', 0) || add_option('at_ftp_uploaded_file_size', 0);
    update_option('at_is_file_import_to_wp', false) || add_option('at_is_file_import_to_wp', false);

    $storage = at_options('upload_storage', 'my-server');

    if($storage == 'gdrive' || $storage == 'ftp') {
        update_option('at_is_file_import_to_wp', true);
    }

    $is_advanced_options = at_options('apt_is_advanced_options', false);

    if ($is_advanced_options) {
        $post_status = sanitize_text_field($_POST['post_status']);
        $post_title_start = sanitize_text_field($_POST['post_title_start']);
        $post_title_end = sanitize_text_field($_POST['post_title_end']);
        $mod_feature = sanitize_text_field($_POST['mod_feature']);
        $import_screenshots = filter_var($_POST['import_screenshots'], FILTER_VALIDATE_BOOLEAN);
        $is_get_apk = filter_var($_POST['is_get_apk'], FILTER_VALIDATE_BOOLEAN);
        $post_thumbnail_quality = sanitize_text_field($_POST['post_thumbnail_quality']);

        update_option('at_apt_post_status', $post_status);
        update_option('at_apt_post_title_start', $post_title_start);
        update_option('at_apt_post_title_end', $post_title_end);
        update_option('at_apt_mod_feature', $mod_feature);
        update_option('at_apt_post_thumbnail_quality', $post_thumbnail_quality);
        update_option('at_apt_import_screenshots', $import_screenshots);
        update_option('at_is_get_apk', $is_get_apk);
    }

    $apk_data = new AT_Aptoide();
    $response = $apk_data->extract($apt_url, 'apk');
    $response = json_encode($response);

    echo $response;
    exit;
}
add_action('wp_ajax_at_apt_create_post', 'at_apt_create_post');

function at_apt_search_posts()
{
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'aptoide_nonce')) {
        return json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
    }

    $search_query = strtolower($_POST['search_query']);
    $base_url = 'https://web-api-cache.aptoide.com/search?query=';
    $search_url = $base_url . urlencode($search_query) . '&mature=true';

    $search_data = new AT_Aptoide();
    $response = $search_data->extract($search_url, 'search');
    $response = json_encode($response);
    echo $response;

    exit;
}
add_action('wp_ajax_at_apt_search_posts', 'at_apt_search_posts');

function at_apt_upload_apk()
{
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'aptoide_nonce')) {
        return json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
    }

    $post_id = sanitize_text_field($_POST['post_id']);
    $apk_url = isset($_POST['apk_url']) ? $_POST['apk_url'] : null;
    $apk_version = isset($_POST['apk_version']) ? $_POST['apk_version'] : null;
    $apk_size = isset($_POST['apk_size']) ? $_POST['apk_size'] : null;

    $apk_file_data = new AT_Upload_APK($post_id, $apk_url, $apk_version, $apk_size);
    $response = $apk_file_data->upload();
    $response = json_encode($response);

    echo $response;
    exit;
}
add_action('wp_ajax_at_apt_upload_apk', 'at_apt_upload_apk');

function at_apt_get_process()
{
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'aptoide_nonce')) {
        return json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
    }

    $apk_url = isset($_POST['apk_url']) ? $_POST['apk_url'] : null;
    $apk_version = isset($_POST['apk_version']) ? $_POST['apk_version'] : null;

    $apk_file_data = new AT_Upload_APK('', $apk_url, $apk_version, '');
    $response = $apk_file_data->calculate_progress();
    $response = json_encode($response);

    echo $response;
    exit;
}
add_action('wp_ajax_at_get_process', 'at_apt_get_process');

function save_apktemplates_settings()
{
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'panel_nonce')) {
        return json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
    }

    $is_mod_title = $_POST['is_mod_title'];
    $is_mod_feature_title = $_POST['is_mod_feature_title'];
    $is_title_version = $_POST['is_title_version'];
    $is_blocks_content = $_POST['is_blocks_content'];
    $is_get_apk = $_POST['is_get_apk'];
    $upload_storage = sanitize_text_field($_POST['upload_storage']);
    $gdrive_client_id = sanitize_text_field($_POST['gdrive_client_id']);
    $gdrive_client_secret = sanitize_text_field($_POST['gdrive_client_secret']);
    $gdrive_folder_name = sanitize_text_field($_POST['gdrive_folder_name']);
    $ftp_server_ip = sanitize_text_field($_POST['ftp_server_ip']);
    $ftp_port = sanitize_text_field($_POST['ftp_port']);
    $ftp_username = sanitize_text_field($_POST['ftp_username']);
    $ftp_password = sanitize_text_field($_POST['ftp_password']);
    $ftp_directory = sanitize_text_field($_POST['ftp_directory']);
    $ftp_url = sanitize_text_field($_POST['ftp_url']);

    update_option('at_is_mod_title', $is_mod_title) || add_option('at_is_mod_title', $is_mod_title);
    update_option('at_is_mod_feature_title', $is_mod_feature_title) || add_option('at_is_mod_feature_title', $is_mod_feature_title);
    update_option('at_is_title_version', $is_title_version) || add_option('at_is_title_version', $is_title_version);
    update_option('at_is_blocks_content', $is_blocks_content) || add_option('at_is_blocks_content', $is_blocks_content);
    update_option('at_is_get_apk', $is_get_apk) || add_option('at_is_get_apk', $is_get_apk);
    update_option('at_upload_storage', $upload_storage) || add_option('at_upload_storage', $upload_storage);
    update_option('at_gdrive_client_id', $gdrive_client_id) || add_option('at_gdrive_client_id', $gdrive_client_id);
    update_option('at_gdrive_client_secret', $gdrive_client_secret) || add_option('at_gdrive_client_secret', $gdrive_client_secret);
    update_option('at_gdrive_folder_name', $gdrive_folder_name) || add_option('at_gdrive_folder_name', $gdrive_folder_name);
    update_option('at_ftp_server_ip', $ftp_server_ip) || add_option('at_ftp_server_ip', $ftp_server_ip);
    update_option('at_ftp_port', $ftp_port) || add_option('at_ftp_port', $ftp_port);
    update_option('at_ftp_username', $ftp_username) || add_option('at_ftp_username', $ftp_username);
    update_option('at_ftp_password', $ftp_password) || add_option('at_ftp_password', $ftp_password);
    update_option('at_ftp_directory', $ftp_directory) || add_option('at_ftp_directory', $ftp_directory);
    update_option('at_ftp_url', $ftp_url) || add_option('at_ftp_url', $ftp_url);
}

add_action('wp_ajax_save_apktemplates_settings', 'save_apktemplates_settings');
add_action('wp_ajax_nopriv_save_apktemplates_settings', 'save_apktemplates_settings');

/* Panel Ajax */
function at_search_term()
{
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'panel_nonce')) {
        return json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
    }

    $query = $_POST['query'];

    $query_term = new WP_Term_Query(
        array(
            'taxonomy' => array('category', 'post_tag'),
            'hide_empty' => false,
            'name__like' => $query,
            'posts_per_page' => 50,
        )
    );

    if ($query_term->terms) {
        echo '<ul>';
        foreach ($query_term->terms as $term) {
            echo '<li data-term-id="' . $term->term_id . '">' . $term->name . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<div class="no-result">No terms found...</div>';
    }

    wp_die();
}
add_action('wp_ajax_at_search_term', 'at_search_term');

function at_search_post()
{
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'panel_nonce')) {
        return json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
    }

    $query = $_POST['query'];

    $query_posts = new WP_Query(
        array(
            'post_status' => 'publish',
            'post_type' => 'post',
            's' => $query,
            'post_parent' => 0,
            'posts_per_page' => 50,
        )
    );
    if ($query_posts->have_posts()):
        echo '<ul>';
        while ($query_posts->have_posts()):
            $query_posts->the_post();
            echo '<li data-post-id="' . get_the_ID() . '">' . get_the_title() . '</li>';
        endwhile;
        echo '</ul>';
    else:
        echo '<div class="no-result">No posts found...</div>';
    endif;

    wp_die();
}
add_action('wp_ajax_at_search_post', 'at_search_post');

function apkt_delete_folder_action() {
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'panel_nonce')) {
        return json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
    }

    $upload_dir = wp_upload_dir();
    $cache_dir = $upload_dir['apkt_cache_dir']; 
    apkt_delete_files($cache_dir);
    wp_send_json_success('Cache cleared :)');
    exit;
}

add_action('wp_ajax_apkt_delete_folder_action', 'apkt_delete_folder_action');

new AT_License();

function save_apkt_customization()
{
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'panel_nonce')) {
        return json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
    }

    $data = $_POST['data'];

    $new_data = [];
    $repeater_data = [];
    $multi_select_data = [];
    $options_data = [];

    $options = [
		'import_ss_limit',
        'is_mod_title',
        'is_mod_feature_title',
        'is_title_version',
        'upload_storage',
        'gdrive_client_id',
        'gdrive_client_secret',
        'gdrive_folder_name',
        'ftp_server_ip',
        'ftp_port',
        'ftp_username',
        'ftp_password',
        'ftp_directory',
        'ftp_url',
    ];

    $single_data = array_filter($data, function ($item) {
        return strpos($item['name'], '[') === false;
    });

    $repeater_datas = array_filter($data, function ($item) {
        return strpos($item['name'], '[') !== false;
    });

    foreach ($single_data as $item) {
        if ($item['value'] === 'true' || $item['value'] === 'false') {
            $item['value'] = filter_var($item['value'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $item['value'] = removeslashes_deep($item['value']);
        }

        if (!in_array($item['name'], $options)) {
            $new_data[$item['name']] = $item['value'];
        } else {
            $options_data[$item['name']] = $item['value'];
        }
    }
    // for normal repeater with array (download faq and home terms)
    foreach ($repeater_datas as $item) {
        if ($item['value'] === 'true' || $item['value'] === 'false') {
            $item['value'] = filter_var($item['value'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $item['value'] = removeslashes_deep($item['value']);
        }

        if (!in_array($item['name'], $options)) {
            if (strpos($item['name'], '[]') !== false) {
                $main_name = str_replace('[]', '', $item['name']);
                $repeater_data[$main_name] = array();
            } else {
                preg_match('/^([^\[]+)\[([\d]+)\]\[([^\]]+)\]$/', $item['name'], $matches);
    
                if (count($matches) === 4) {
                    $main_name = $matches[1];
                    $index = $matches[2];
                    $field_name = $matches[3];
    
                    if (!isset($repeater_data[$main_name])) {
                        $repeater_data[$main_name] = array();
                    }
    
                    if (!isset($repeater_data[$main_name][$index])) {
                        $repeater_data[$main_name][$index] = array();
                    }
    
                    $repeater_data[$main_name][$index][$field_name] = $item['value'];
                }
            }
        }
    }
    // For multi selected options repeater (App Info)
    foreach ($repeater_datas as $item) {
        if ($item['value'] === 'true' || $item['value'] === 'false') {
            $item['value'] = filter_var($item['value'], FILTER_VALIDATE_BOOLEAN);
        }
        if (!in_array($item['name'], $options)) {
            preg_match('/^([^\[]+)(\[[^\]]+\])?$/', $item['name'], $matches);

            if (count($matches) === 3) {
                $main_name = $matches[1];
                $multi_select_suffix = $matches[2];
                if ($multi_select_suffix) {
                    $value_key = trim($multi_select_suffix, '[]');
                    if ($item['value'] === true) {
                        if (!isset($multi_select_data[$main_name])) {
                            $multi_select_data[$main_name] = [];
                        }
                        $multi_select_data[$main_name][] = $value_key;
                    }
                }
            }
        }
    }

    foreach ($repeater_data as $main_name => $values) {
        $new_data[$main_name] = $values;
    }

    foreach ($multi_select_data as $main_name => $values) {
        $new_data[$main_name] = $values;
    }

    foreach ($new_data as $main_name => $values) {
        set_theme_mod($main_name, $values);
    }

    foreach ($options_data as $main_name => $values) {
        update_option('at_' . $main_name, $values);
    }

    wp_send_json_success('Changes Saved!');
    wp_die();
}

add_action('wp_ajax_save_at_customization', 'save_apkt_customization');

function apkt_import_demo() {
    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
        $import_file = $_FILES['import_file']['tmp_name'];
        $import_json = file_get_contents($import_file);

        if ($import_json !== false) {
            $demo_data = json_decode($import_json, true);
            if (is_array($demo_data)) {
                if (isset($demo_data['info']) && strtolower($demo_data['info']['theme_name']) === strtolower(APKT_THEME_NAME)) {
                    
                    foreach ($demo_data['posts'] as $post_data) {
                        $title = $post_data['title'];
                        $description = wp_encode_emoji($post_data['description']);
                        $thumbnail_url = $post_data['thumbnail'];
                        //$banner_url = $post_data['apktemplates']['wp_poster_GP'];
                        
                        $parent_cat_name = $post_data['category']['name'];
                        $parent_cat_slug = $post_data['category']['slug'];
                        
                        $child_cat_name = $post_data['subcategory']['name'];
                        $child_cat_slug = $post_data['subcategory']['slug'];
                        
                        $parent_cat_id = null;
                        $child_cat_id = null;
                        
                        $parent_category = get_term_by("slug", $parent_cat_slug, "category");

                        if ($parent_category) {
                            $parent_cat_id = $parent_category->term_id;
                        } else {
                            $parent_id = wp_insert_term($parent_cat_name, "category", ['slug' => $parent_cat_slug]);
                            if (!is_wp_error($parent_id)) {
                                $parent_cat_id = $parent_id["term_id"];
                            }
                        }

                        $child_category = get_term_by("slug", $child_cat_slug, "category");

                        if ($child_category) {
                            $child_cat_id = $child_category->term_id;
                        } else {
                            $child_id = wp_insert_term($child_cat_name, "category", ["parent" => $parent_cat_id, "slug" => $child_cat_slug]);
                            if (!is_wp_error($child_id)) {
                                $child_cat_id = $child_id["term_id"];
                            }
                        }
                        
                        $post_id = wp_insert_post([
                            'post_title'   => $title,
                            'post_content' => $description,
                            'post_category' => [$parent_cat_id, $child_cat_id],
                            'post_status'  => 'publish',
                            'meta_input'   => $post_data['apktemplates']
                        ]);
                        
                        if ($post_id && !is_wp_error($post_id)) {
                            /* if (!empty($banner_url)) {
                                $image_id = media_sideload_image($banner_url, $post_id, null, 'id');
                            
                                if (!is_wp_error($image_id)) {
                                    $uploaded_image_url = wp_get_attachment_url($image_id);
                                    update_post_meta($post_id, 'wp_poster_GP', $uploaded_image_url);
                                }
                            } */
                            
                            if (!empty($thumbnail_url)) {
                                $image_id = apkt_set_post_thumbnail_from_url($post_id, $thumbnail_url);
                                set_post_thumbnail($post_id, $image_id);
                            }
                        }
                    }
					
                    foreach ($demo_data['articles'] as $post_data) {
                        $title = $post_data['title'];
                        $description = wp_encode_emoji($post_data['description']);
                        $thumbnail_url = $post_data['thumbnail'];
                        
                        $post_id = wp_insert_post([
                            'post_title'   => $title,
                            'post_content' => $description,
							'post_type' => 'articles',
                            'post_status'  => 'publish',
                        ]);
                        
                        if ($post_id && !is_wp_error($post_id)) {
                            
                            if (!empty($thumbnail_url)) {
                                $image_id = apkt_set_post_thumbnail_from_url($post_id, $thumbnail_url);
                                set_post_thumbnail($post_id, $image_id);
                            }
                        }
                    }
					
					foreach ($demo_data['news'] as $post_data) {
                        $title = $post_data['title'];
                        $description = wp_encode_emoji($post_data['description']);
                        $thumbnail_url = $post_data['thumbnail'];
                        
                        $post_id = wp_insert_post([
                            'post_title'   => $title,
                            'post_content' => $description,
							'post_type' => 'news',
                            'post_status'  => 'publish',
                        ]);
                        
                        if ($post_id && !is_wp_error($post_id)) {
                            
                            if (!empty($thumbnail_url)) {
                                $image_id = apkt_set_post_thumbnail_from_url($post_id, $thumbnail_url);
                                set_post_thumbnail($post_id, $image_id);
                            }
                        }
                    }
                    
                    wp_send_json_success('Import demo successful :)');
                    
                } else {
                    wp_send_json_error('The imported demo is not for this theme.');
                }
            } else {
                wp_send_json_error('Invalid JSON format');
            }
        } else {
            wp_send_json_error('Unable to read file');
        }
    } else {
        wp_send_json_error('No file uploaded or error occurred');
    }
    exit;
}

add_action('wp_ajax_apkt_import_demo', 'apkt_import_demo');

function apkt_import_settings() {
    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
        $import_file = $_FILES['import_file']['tmp_name'];
        
        $import_json = file_get_contents($import_file);
        
        if ($import_json !== false) {
            $backup_data = json_decode($import_json, true);
            
            if (is_array($backup_data)) {
                if (isset($backup_data['info']) && $backup_data['info']['theme_name'] === strtolower(APKT_THEME_NAME)) {
                    foreach ($backup_data as $option_name => $option_value) {
                        if ($option_name !== 'info') {
                            if ($option_value['is_option']) {
                                update_option($option_name, $option_value['value']);
                            } else {
                                set_theme_mod($option_name, $option_value['value']);
                            }
                        }
                    }

                    wp_send_json_success('Import successful :)');
                } else {
                    wp_send_json_error('The imported backup is not for this theme.');
                }
            } else {
                wp_send_json_error('Invalid JSON format');
            }
        } else {
            wp_send_json_error('Unable to read file');
        }
    } else {
        wp_send_json_error('No file uploaded or error occurred');
    }
    exit;
}
add_action('wp_ajax_apkt_import_settings', 'apkt_import_settings');

function apkt_export_settings() {
    $nonce = sanitize_text_field($_POST['nonce']);

    if (!wp_verify_nonce($nonce, 'panel_nonce')) {
        return json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
    }

    $date = date('Y-m-d_H-i-s');

    $backup_data = array();

    $backup_data['info'] = array(
        'theme_name' => strtolower(APKT_THEME_NAME),
        'theme_version' => APKT_THEME_VERSION,
        'author' => 'apktemplates',
        'created' => $date
    );

    $option_names = array(
		'at_import_ss_limit',
        'at_is_mod_title',
        'at_is_mod_feature_title',
        'at_is_title_version',
        'at_upload_storage',
        'at_gdrive_client_id',
        'at_gdrive_client_secret',
        'at_gdrive_folder_name',
        'at_ftp_server_ip',
        'at_ftp_port',
        'at_ftp_username',
        'at_ftp_password',
        'at_ftp_directory',
        'at_ftp_url'
    );

    $theme_mods_names = array(
        'theme_color',
        'logo_img',
        'an1_rtl_mod',
        'head_code',
        'home_terms',
        'home_articles_swt',
        'home_articles_title',
        'home_articles_limit',
        'ha_btm_ad_swt',
        'home_news_swt',
        'home_news_title',
        'home_news_limit',
        'hn_btm_ad_swt',
        'home_seo_title',
        'home_seo_description',
        'sp_hide_content_swt',
        'sp_screenshot_swt',
        'screenshots_limit',
        'ld_promo_button',
        'sp_faq_swt',
        'single_faqs',
        'related_post_swt',
        'related_post_limit',
        'security_title',
        'security_description',
        'custom_cat',
        'custom_cat_title',
        'custom_cat_posts',
        'archive_sort',
        'archive_posts_limit',
        'download_timer',
        'download_recommend_swt',
        'download_recommend_post_limit',
        'download_random_posts_swt',
        'download_random_posts_limit',
        'telegram_text',
        'faqs',
        'home_top_ads_swt',
        'home_top_ads',
        'home_botm_ads_swt',
        'home_botm_ads',
        'single_top_ads_swt',
        'single_top_ads',
        'single_botm_ads_swt',
        'single_botm_ads',
        'archive_top_ads_swt',
        'archive_top_ads',
        'archive_botm_ads_swt',
        'archive_botm_ads',
        'download_top_ads_swt',
        'download_top_ads',
        'download_botm_ads_swt',
        'download_botm_ads',
        'facebook_url',
        'twitter_url',
        'youtube_url',
        'telegram_url',
        'tiktok_url',
        'pinterest_url',
        'whatsapp_url',
        'instagram_url',
        'github_url',
        'linkedin_url',
        'skype_url',
        'tumblr_url',
        'twitch_url',
        'vk_url',
        'reddit_url',
        'footer_copyright',
        'footer_code'
    );    

    foreach ($option_names as $option_name) {
        $backup_data[$option_name] = array(
            'value' => get_option($option_name),
            'is_option' => true,
        );
    }

    foreach ($theme_mods_names as $theme_mod_name) {
        $backup_data[$theme_mod_name] = array(
            'value' => get_theme_mod($theme_mod_name),
            'is_option' => false,
        );
    }

    $backup_json = json_encode($backup_data);
    $upload_dir = wp_upload_dir();
    $export_json_path = $upload_dir['apkt_cache_dir'] . '/' . strtolower(APKT_THEME_NAME) . '_' . $date . '_settings' . 'json';

    file_put_contents($export_json_path, $backup_json);
    
    $export_json_url = $upload_dir['apkt_cache_url'] . '/' . strtolower(APKT_THEME_NAME) . '_' . $date . '_settings' . 'json';
    wp_send_json_success($export_json_url);
    exit;
}
add_action('wp_ajax_apkt_export_settings', 'apkt_export_settings');