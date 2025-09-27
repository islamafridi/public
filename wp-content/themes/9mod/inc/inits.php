<?php
if (!defined('ABSPATH')) exit;
// Enqueue the JavaScript file
add_action('admin_enqueue_scripts', 'enqueue_admin_assets');
function enqueue_admin_assets($hook) {
    // Load only on post edit/new screens for 'post' type
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }

    // Get the current screen to check post type
    $screen = get_current_screen();
    if ($screen->post_type !== 'post') {
        return;
    }

    // Enqueue CSS
    wp_enqueue_style(
        'inits-admin', // Handle
        get_template_directory_uri() . '/assets/css/admin/inits.admin.css', // Path to CSS
        array(), // Dependencies
        '1.0.0' // Version
    );

    // Enqueue JS
    wp_enqueue_script(
        'get-file-size', // Handle
        get_template_directory_uri() . '/assets/js/admin/get.file.size.js', // Path to JS (adjusted to assets/js/)
        array('jquery'), // Dependency
        '1.0.0', // Version
        true // In footer
    );

    // Localize data for JS
    wp_localize_script('get-file-size', 'apktemplatesData', array(
        'ajaxUrl' => admin_url('admin-ajax.php')
    ));
}
// AJAX handler for file size
add_action('wp_ajax_get_file_size', 'get_file_size_callback');
function get_file_size_callback() {
    if (isset($_GET['url'])) {
        $url = filter_var($_GET['url'], FILTER_SANITIZE_URL);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            wp_send_json(['success' => false, 'error' => 'Invalid URL']);
            wp_die();
        }
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'follow_location' => true,
                'timeout' => 10,
            ]
        ]);
        $headers = @get_headers($url, 1, $context);
        if ($headers && isset($headers['Content-Length'])) {
            $bytes = is_array($headers['Content-Length']) ? end($headers['Content-Length']) : $headers['Content-Length'];
            $size = format_download_size($bytes);
            wp_send_json(['success' => true, 'size' => $size]);
        } else {
            wp_send_json(['success' => false, 'error' => 'No Content-Length header']);
        }
    } else {
        wp_send_json(['success' => false, 'error' => 'No URL provided']);
    }
    wp_die();
}

// Helper function for formatting download sizes (MB, GB, KB)
function format_download_size($bytes) {
    if ($bytes >= 1073741824) {
        $gb = $bytes / 1073741824;
        return number_format($gb, 2, '.', '') . ' GB';
    } elseif ($bytes >= 1048576) {
        $mb = $bytes / 1048576;
        return number_format($mb, 2, '.', '') . ' MB';
    } else {
        $kb = $bytes / 1024;
        return number_format($kb, 2, '.', '') . ' KB';
    }
}

// meta box
add_action('add_meta_boxes', 'add_post_metaboxes', 1);
add_action('save_post', 'save_app_info');
add_action('save_post', 'save_download_links');
add_action('save_post', 'save_screenshots');

function add_post_metaboxes(){
    add_meta_box('download-links', __('Download Links', 'apktemplates'), 'download_links_callback', 'post', 'normal', 'high');
    add_meta_box('app-info', __('App Information', 'apktemplates'), 'app_info_callback', 'post', 'normal', 'high');
    add_meta_box('screenshots', __('Screenshots', 'apktemplates'), 'screenshots_callback', 'post', 'normal', 'high');
}


function app_info_callback($post){
    $post_id = $post->ID;
    $app_fields = [
        'wp_version_GP' => [
            __('App Version', 'apktemplates'), 
            'E.g 16.0.25'
        ],
        'wp_sizes_GP' => [
            __('App Size', 'apktemplates'),
            'E.g 290M'
        ],
        'wp_mods' => [
            __('App MOD Feature', 'apktemplates'), 
            'E.g. Unlimited Coins'
        ],
        'wp_title_GP' => [
            __('App Name', 'apktemplates'), 
            'E.g. Clash Of Clans'
        ],
        'wp_GP_ID' => [
            __('App Package', 'apktemplates'), 
            'E.g com.supercell.clashofclans'
        ],
        'wp_developers_GP' => [
            __('App Publisher', 'apktemplates'), 
            'E.g Supercell'
        ],
        'wp_contentrated_GP' => [
            __('App Rated Year\'s', 'apktemplates'),
            'E.g 13+'
        ],
        'wp_requires_GP' => [
            __('App Required OS', 'apktemplates'),
            'E.g 9.0'
        ],
        'price' => [
            __('App Price', 'apktemplates'),
            'E.g 5.99'
        ],
        'download_info' => [
            __('Info for Single Download Page', 'apktemplates'),
            'E.g Premium Added'
        ],
        'mod_info' => [
            __('MOD Info', 'apktemplates'),
            'E.g Unlocked All',
            'textarea',
        ],
    ];
    $modtickbox = get_post_meta($post_id, 'mod-tick-box', true);
    $rating_data = apkt_get_star_rating($post_id);
    wp_nonce_field('app_info_nonce', 'app_info_nonce');
    ?>

<div class="custom-fields-container">
    <!-- MOD Checkbox -->
    <div class="field-group full-width">
        <label for="is_mod">
            <input id="is_mod" type="checkbox" name="is_mod" <?php if ($modtickbox === "on") { ?>checked="checked"<?php } ?> />
            Tick "MOD" or leave
        </label>
    </div>



<?php foreach ($app_fields as $field_key => $field_data) : 
    $label = $field_data[0];
    $placeholder = $field_data[1];
    $type = isset($field_data[2]) ? $field_data[2] : 'input';
    $value = esc_textarea(get_post_meta($post_id, $field_key, true));
    $full_width_class = ($type === 'textarea') ? 'full-width' : '';
?>
    <div class="field-group <?= $full_width_class ?>">
        <label for="<?= $field_key ?>"><?= $label ?></label>

<?php if ($type === 'textarea'): ?>
    <?php
        $editor_settings = [
            'textarea_name' => $field_key,
            'textarea_rows' => 5,
            'media_buttons' => false,
            'tinymce'       => true,
            'quicktags'     => true,
        ];
        wp_editor(htmlspecialchars_decode($value), $field_key, $editor_settings);
    ?>
<?php else: ?>
    <?php if ($field_key === 'wp_sizes_GP'): ?>
        <div class="size-group">
            <input type="text" id="<?= $field_key ?>" name="<?= $field_key ?>" value="<?= $value ?>" placeholder="<?= $placeholder ?>" />
            <button type="button" id="set-largest-size" class="components-button is-secondary">Set Largest Size</button>
        </div>
    <?php else: ?>
        <input type="text" id="<?= $field_key ?>" name="<?= $field_key ?>" value="<?= $value ?>" placeholder="<?= $placeholder ?>" />
    <?php endif; ?>
<?php endif; ?>
    </div>
<?php endforeach; ?>

    <!-- Rating Range -->
    <div class="field-group">
        <label for="avg_rating">Total Rating</label>
        <input type="range" step="0.1" min="1" max="5" name="avg_rating" value="<?= $rating_data->rating_average; ?>" class="range-input"/>
            <span class="tooltip"><?= $rating_data->rating_average; ?></span>
    </div>

    <!-- Total Votes -->
    <div class="field-group">
        <label for="total_votes">Total Votes</label>
        <input type="text" id="total_votes" name="total_votes"
               value="<?= esc_attr($rating_data->rating_votes); ?>"
               placeholder="E.g. 920" />
    </div>
</div>
<?php
}

function save_app_info($post_id){
    if (!isset($_POST['app_info_nonce']) || !wp_verify_nonce($_POST['app_info_nonce'], 'app_info_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    $allowed_post_types = ['post'];

    if (!in_array(get_post_type($post_id), $allowed_post_types)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $app_meta_data = [
        'wp_title_GP',
        'wp_GP_ID',
        'wp_developers_GP',
        'wp_mods',
        'wp_version_GP',
        'wp_sizes_GP',
        'wp_contentrated_GP',
        'wp_requires_GP',
        'price',
        'download_info',
        'mod_info'
    ];

    foreach ($app_meta_data as $meta_key) {
        if (isset($_POST[$meta_key])) {
            if ($meta_key === 'wp_version_GP') {
                $version = preg_replace('/^[vV]/', '', $_POST[$meta_key]);
                update_post_meta($post_id, $meta_key, sanitize_text_field($version));
            } elseif (in_array($meta_key, ['mod_info'])) {
                update_post_meta($post_id, $meta_key, wp_kses_post($_POST[$meta_key]));
            } else {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$meta_key]));
            }
        }
    }

    if (isset($_POST['is_mod'])) {
        update_post_meta($post_id, 'mod-tick-box', 'on');
    } else {
        delete_post_meta($post_id, 'mod-tick-box');
    }
    
    $new_average = (empty($_POST['avg_rating']) ? 5 : $_POST['avg_rating'] );
    $new_votes = (empty($_POST['total_votes']) ? 2 : $_POST['total_votes'] );
    update_post_meta($post_id, 'avg_rating', $new_average);
    update_post_meta($post_id, 'total_votes', $new_votes);
    update_post_meta($post_id, 'total_rating', round($new_average * $new_votes));
}

function download_links_callback($post) {
    $download_links = get_post_meta($post->ID, 'repeatable_download_link', true);
    wp_nonce_field('download_links_nonce', 'download_links_nonce');
    ?>
    <div class="table-container">
        <table id="download-table" class="wp-list-table widefat fixed striped" style="width: 100%; margin:auto">
            <thead>
                <tr>
                    <th style="max-width: 0px;"></th>
                    <th style="max-width: 50px;"><strong><?php _e('File APK', 'apktemplates'); ?></strong></th>
                    <th style="width: 180px;"><strong><?php _e('URL Title', 'apktemplates'); ?></strong></th>
                    <th style="width: 300px;"><strong><?php _e('URL', 'apktemplates'); ?></strong></th>
                    <th style="max-width: 40px;"><strong><?php _e('Size', 'apktemplates'); ?></strong></th>
                    <th style="width: 100px;"><strong><?php _e('MOD Info', 'apktemplates'); ?></strong></th>
                    <th style="width: 100px;"><strong><?php _e('Download Note', 'apktemplates'); ?></strong></th>
                    <th style="width: 35px;"><strong><?php _e('Group', 'apktemplates'); ?></strong></th>
                    <th style="max-width: 25px;"><strong><?php _e('Actions', 'apktemplates'); ?></strong></th>
                </tr>
            </thead>
            <tbody>
                <?php $row_index = 0; ?>
                <?php if ($download_links) : ?>
                    <?php foreach ($download_links as $field) : ?>
                        <tr>
                            <td class="handle_icon"><span class="handle">☰</span></td>
                            <td><input type="text" class="widefat" name="download_name[]" value="<?= esc_attr($field['download_name'] ?? ''); ?>" /></td>
                            <td><input type="text" class="widefat" name="download_mod_info[]" value="<?= esc_attr($field['download_mod_info'] ?? ''); ?>" /></td>
                            <td><input type="text" class="widefat download-url" id="download_url_<?php echo $row_index; ?>" name="download_url[]" value="<?= esc_url($field['download_url'] ?? ''); ?>" /></td>
                            <td><input type="text" class="widefat download-size" id="download_size_<?php echo $row_index; ?>" name="download_size[]" value="<?= esc_attr($field['download_size'] ?? ''); ?>" /></td>
                            <td><input type="text" class="widefat" name="download_mod_note[]" value="<?= esc_attr($field['download_mod_note'] ?? ''); ?>" /></td>
                            <td><textarea class="widefat" name="download_note[]" rows="1"><?= esc_textarea($field['download_note'] ?? ''); ?></textarea></td>
                            <td>
                                <select name="download_group[]" class="widefat">
                                    <option value="Default" <?php selected($field['download_group'] ?? '', 'Default'); ?>>Default</option>
                                    <option value="Grouped1" <?php selected($field['download_group'] ?? '', 'Grouped1'); ?>>Group 1</option>
                                    <option value="Grouped2" <?php selected($field['download_group'] ?? '', 'Grouped2'); ?>>Group 2</option>
                                    <option value="Grouped3" <?php selected($field['download_group'] ?? '', 'Grouped3'); ?>>Group 3</option>
                                    <option value="Grouped4" <?php selected($field['download_group'] ?? '', 'Grouped4'); ?>>Group 4</option>
                                </select>
                            </td>
                            <td><button type="button" class="remove-btn components-button is-destructive"><?php _e('Remove', 'apktemplates'); ?></button></td>
                        </tr>
                        <?php $row_index++; ?>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td class="handle_icon"><span class="handle">☰</span></td>
                        <td><input type="text" class="widefat" name="download_name[]" value="" /></td>
                        <td><input type="text" class="widefat" name="download_mod_info[]" value="" /></td>
                        <td><input type="text" class="widefat download-url" id="download_url_0" name="download_url[]" value="" /></td>
                        <td><input type="text" class="widefat download-size" id="download_size_0" name="download_size[]" value="" /></td>
                        <td><input type="text" class="widefat" name="download_mod_note[]" value="" /></td>
                        <td><textarea class="widefat" name="download_note[]" rows="1"></textarea></td>
                        <td>
                            <select name="download_group[]" class="widefat">
                                <option value="Default">Default</option>
                                <option value="Grouped1">Group 1</option>
                                <option value="Grouped2">Group 2</option>
                                <option value="Grouped3">Group 3</option>
                                <option value="Grouped4">Group 4</option>
                            </select>
                        </td>
                        <td><button type="button" class="remove-btn components-button is-destructive"><?php _e('Remove', 'apktemplates'); ?></button></td>
                    </tr>
                    <?php $row_index = 1; ?>
                <?php endif; ?>
                <tr class="empty-d-row" style="display:none;">
                    <td class="handle_icon"><span class="handle">☰</span></td>
                    <td><input type="text" class="widefat" name="download_name[]" /></td>
                    <td><input type="text" class="widefat" name="download_mod_info[]" /></td>
                    <td><input type="text" class="widefat download-url" id="download_url_<?php echo $row_index; ?>" name="download_url[]" /></td>
                    <td><input type="text" class="widefat download-size" id="download_size_<?php echo $row_index; ?>" name="download_size[]" /></td>
                    <td><input type="text" class="widefat" name="download_mod_note[]" /></td>
                    <td><textarea class="widefat" name="download_note[]" rows="1"></textarea></td>
                    <td>
                        <select name="download_group[]" class="widefat">
                            <option value="Default">Default</option>
                            <option value="Grouped1">Group 1</option>
                            <option value="Grouped2">Group 2</option>
                            <option value="Grouped3">Group 3</option>
                            <option value="Grouped4">Group 4</option>
                        </select>
                    </td>
                    <td><button type="button" class="remove-btn components-button is-destructive"><?php _e('Remove', 'apktemplates'); ?></button></td>
                </tr>
            </tbody>
        </table>
    </div>
    <button type="button" id="add-row" class="add-btn components-button is-secondary" style="flex-grow: 1; justify-content: center; margin-top: 16px;"><?php _e('Add Link', 'apktemplates'); ?></button>
    <?php
}

function save_download_links($post_id){
    if (!isset($_POST['download_links_nonce']) || !wp_verify_nonce($_POST['download_links_nonce'], 'download_links_nonce')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    $allowed_post_types = ['post'];

    if (!in_array(get_post_type($post_id), $allowed_post_types)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $download_links = get_post_meta($post_id, 'repeatable_download_link', true);
    $new_data = [];

    $download_name = isset($_POST['download_name']) ? $_POST['download_name'] : [];
    $download_size = isset($_POST['download_size']) ? $_POST['download_size'] : [];
    $download_url = isset($_POST['download_url']) ? $_POST['download_url'] : [];
    $download_mod_info = isset($_POST['download_mod_info']) ? $_POST['download_mod_info'] : [];
    $download_mod_note = isset($_POST['download_mod_note']) ? $_POST['download_mod_note'] : [];
    $download_note = isset($_POST['download_note']) ? $_POST['download_note'] : [];
    $download_group = isset($_POST['download_group']) ? $_POST['download_group'] : [];

    $count = count($download_name);

    for ($i = 0; $i < $count; $i++) {
        if (!empty($download_name[$i])) {
            $new_data[$i]['download_name'] = sanitize_text_field($download_name[$i]);
            $new_data[$i]['download_size'] = !empty($download_size[$i]) ? sanitize_text_field($download_size[$i]) : '';
            $new_data[$i]['download_url'] = !empty($download_url[$i]) ? esc_url_raw($download_url[$i]) : '';
            $new_data[$i]['download_mod_info'] = !empty($download_mod_info[$i]) ? sanitize_text_field($download_mod_info[$i]) : '';
            $new_data[$i]['download_mod_note'] = !empty($download_mod_note[$i]) ? sanitize_text_field($download_mod_note[$i]) : '';
            $new_data[$i]['download_note'] = !empty($download_note[$i]) ? wp_kses_post($download_note[$i]) : '';
            $new_data[$i]['download_group'] = !empty($download_group[$i]) ? sanitize_text_field($download_group[$i]) : 'Default';
        }
    }

    if (!empty($new_data) && $new_data !== $download_links) {
        update_post_meta($post_id, 'repeatable_download_link', $new_data);
    } elseif (empty($new_data) && $download_links) {
        delete_post_meta($post_id, 'repeatable_download_link', $download_links);
    }
}

function screenshots_callback($post){
    $screenshots = get_post_meta($post->ID, 'ss_images', true);
    wp_nonce_field('screenshots_nonce', 'screenshots_nonce');
    ?>
    <table id="screenshots-table" class="form-table">
        <tbody>
            <?php
            $counter = 1;
             if ($screenshots) : 
                ?>
                <?php foreach ($screenshots as $field) : ?>
                    <tr>
                        <td>
                            <input id="screenshot-url-<?php echo $counter; ?>" class="widefat" type="text" name="ss_url[]" value="<?= esc_url($field['ss_url']); ?>" placeholder="Enter Image URL" />
                        </td>
                        <td>
                            <button type="button" id="screenshot-btn-<?php echo $counter; ?>" class="upload-screenshot-btn components-button is-secondary" data-target="screenshot-url-<?php echo $counter; ?>" style="justify-content: center;"><?php _e('Upload', 'apktemplates'); ?></button>
                            <button type="button" id="remove-screenshot-btn" class="remove-screenshot-btn remove-btn components-button is-destructive"><?php _e('Remove', 'apktemplates'); ?></button>
                        </td>
                    </tr>
                <?php
                $counter++;
                endforeach; ?>
            <?php else : ?>
                <tr>
                    <td>
                        <input id="screenshot-url-1" class="widefat" type="text" name="ss_url[]" value="" placeholder="Enter Image URL" />
                    </td>
                    <td>
                        <button type="button" id="screenshot-btn-1" class="upload-screenshot-btn components-button is-secondary" data-target="screenshot-url-1" style="justify-content: center;"><?php _e('Upload', 'apktemplates'); ?></button>
                        <button type="button" id="remove-screenshot-btn" class="remove-screenshot-btn remove-btn components-button is-destructive"><?php _e('Remove', 'apktemplates'); ?></button>
                    </td>
                </tr>
            <?php endif; ?>
            <tr class="empty-screenshot-row" style="display:none;">
                <td>
                    <input id="ss-url-<?php echo $counter; ?>" class="widefat" type="text" name="ss_url[]" value="" placeholder="Enter Image URL" />
                </td>
                <td>
                    <button type="button" id="screenshot-btn-<?php echo $counter; ?>" class="upload-screenshot-btn components-button is-secondary" data-target="screenshot-url-<?php echo $counter; ?>" style="justify-content: center;"><?php _e('Upload', 'apktemplates'); ?></button>
                    <button type="button" id="remove-screenshot-btn" class="remove-screenshot-btn remove-btn components-button is-destructive"><?php _e('Remove', 'apktemplates'); ?></button>
                </td>
            </tr>
        </tbody>
    </table>
    <button type="button" id="add-screenshot-btn" class="add-btn components-button is-secondary" style="justify-content: center; margin-top: 16px;"><?php _e('Add Screenshot', 'apktemplates'); ?></button>
    <?php
}

function save_screenshots($post_id){
    if (!isset($_POST['screenshots_nonce']) || !wp_verify_nonce($_POST['screenshots_nonce'], 'screenshots_nonce')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    $allowed_post_types = ['post'];

    if (!in_array(get_post_type($post_id), $allowed_post_types)) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $screenshots = get_post_meta($post_id, 'ss_images', true);
    $new_data = [];

    $screenshots_url = $_POST['ss_url'];
    $count = count($screenshots_url);

    for ($i = 0; $i < $count; $i++) {
        if ($screenshots_url[$i] !== '') {
            $new_data[$i]['ss_url'] = esc_url($screenshots_url[$i]);
        }
    }

    if (!empty($new_data) && $new_data !== $screenshots) {
        update_post_meta($post_id, 'ss_images', $new_data);
    } elseif (empty($new_data) && $screenshots) {
        delete_post_meta($post_id, 'ss_images', $screenshots);
    }
}

// Include REST API and Admin Settings
require_once get_template_directory() . '/inc/rest-api.php';
require_once get_template_directory() . '/inc/admin-settings.php';
?>
