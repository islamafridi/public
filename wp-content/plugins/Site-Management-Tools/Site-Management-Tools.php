<?php
/*
Plugin Name: Site Management Tools
Description: Admin Post Views, Hide Game/Apps on Desktop and Hide Admin Bar features
Version: 1.4
Author: Muhammad Islam
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin activation hook
register_activation_hook(__FILE__, 'smt_activate_plugin');
register_deactivation_hook(__FILE__, 'smt_deactivate_plugin');
register_uninstall_hook(__FILE__, 'smt_uninstall_plugin');

function smt_activate_plugin() {
    // Set default options
    add_option('smt_enable_post_views', '1');
    add_option('smt_enable_desktop_redirect', '1');
    add_option('smt_hide_admin_bar', '1');
}

function smt_deactivate_plugin() {
    // Clean up any temporary data
    if (session_id()) {
        session_destroy();
    }
}

function smt_uninstall_plugin() {
    // Remove all plugin options
    delete_option('smt_enable_post_views');
    delete_option('smt_enable_desktop_redirect');
    delete_option('smt_hide_admin_bar');

    // Remove all post meta
    global $wpdb;
    $wpdb->delete($wpdb->postmeta, array('meta_key' => 'smt_post_views_count'));
    $wpdb->delete($wpdb->postmeta, array('meta_key' => 'redirect_desktop'));
}

/* === Admin Settings Page === */
add_action('admin_menu', 'smt_add_admin_menu');

function smt_add_admin_menu() {
    // Create main Site Management menu if it doesn't exist
    if (!menu_page_url('site-management', false)) {
        add_menu_page(
            'Site Management',
            'Site Management',
            'manage_options',
            'site-management',
            'smt_main_menu_page',
            'dashicons-admin-tools',
            30
        );
    }

    // Add this plugin as a submenu
    add_submenu_page(
        'site-management',
        'Site Management Tools',
        'Management Tools',
        'manage_options',
        'site-management-tools',
        'smt_admin_page'
    );
}

function smt_main_menu_page() {
    ?>
    <div class="wrap">
        <h1>Site Management</h1>
        <p>Welcome to Site Management dashboard. Use the submenu items to access different tools and plugins.</p>

        <div class="card">
            <h2>Available Tools</h2>
            <ul>
                <li><a href="<?php echo admin_url('admin.php?page=site-management-tools'); ?>">Management Tools</a> - Post views, desktop redirects, and admin bar settings</li>
                <li><a href="<?php echo admin_url('admin.php?page=secure-download-proxy'); ?>">Download Proxy</a> - Secure download proxy with file renaming and analytics</li>
                <li><a href="<?php echo admin_url('admin.php?page=app-sync'); ?>">App Sync</a> - Sync apps and content across multiple sites</li>
            </ul>
        </div>

        <div class="card">
            <h3>Quick Stats</h3>
            <?php
            global $wpdb;
            $total_views = $wpdb->get_var("SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = 'smt_post_views_count'");
            $posts_with_desktop_redirect = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'redirect_desktop' AND meta_value = '1'");

            $download_stats = get_option('dfr_download_stats', array());
            $total_downloads = isset($download_stats['total_downloads']) ? $download_stats['total_downloads'] : 0;
            $failed_downloads = isset($download_stats['failed_downloads']) ? $download_stats['failed_downloads'] : 0;

            $sync_posts = $wpdb->get_var("SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = 'wp_GP_ID'");
            $sync_table = $wpdb->prefix . 'app_sync_logs';
            if ($wpdb->get_var("SHOW TABLES LIKE '$sync_table'") === $sync_table) {
                $sync_stats = $wpdb->get_var("SELECT COUNT(*) FROM $sync_table WHERE status = 'success' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            } else {
                $sync_stats = 0;
            }
            ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <p><strong>Total Post Views:</strong> <?php echo number_format($total_views ?: 0); ?></p>
                    <p><strong>Posts with Desktop Redirect:</strong> <?php echo $posts_with_desktop_redirect ?: 0; ?></p>
                </div>
                <div>
                    <p><strong>Total Downloads:</strong> <?php echo number_format($total_downloads); ?></p>
                    <p><strong>Failed Downloads:</strong> <?php echo number_format($failed_downloads); ?></p>
                </div>
                <div>
                    <p><strong>Synced Posts:</strong> <?php echo number_format($sync_posts ?: 0); ?></p>
                    <p><strong>Successful Syncs (24h):</strong> <?php echo number_format($sync_stats ?: 0); ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function smt_admin_page() {
    if (isset($_POST['submit'])) {
        update_option('smt_enable_post_views', isset($_POST['smt_enable_post_views']) ? '1' : '0');
        update_option('smt_enable_desktop_redirect', isset($_POST['smt_enable_desktop_redirect']) ? '1' : '0');
        update_option('smt_hide_admin_bar', isset($_POST['smt_hide_admin_bar']) ? '1' : '0');
        echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
    }

    $post_views = get_option('smt_enable_post_views', '1');
    $desktop_redirect = get_option('smt_enable_desktop_redirect', '1');
    $hide_admin_bar = get_option('smt_hide_admin_bar', '1');
    ?>
    <div class="wrap">
        <h1>Site Management Tools Settings</h1>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">Enable Post Views</th>
                    <td>
                        <input type="checkbox" name="smt_enable_post_views" value="1" <?php checked(1, $post_views); ?>>
                        <label>Show view counts in admin posts list</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Enable Desktop Redirect</th>
                    <td>
                        <input type="checkbox" name="smt_enable_desktop_redirect" value="1" <?php checked(1, $desktop_redirect); ?>>
                        <label>Allow posts to redirect desktop users to 404</label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Hide Admin Bar</th>
                    <td>
                        <input type="checkbox" name="smt_hide_admin_bar" value="1" <?php checked(1, $hide_admin_bar); ?>>
                        <label>Hide admin bar for all users</label>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/* === Admin Post Views Functionality === */
function smt_add_views_column($columns) {
    if (!get_option('smt_enable_post_views', '1')) return $columns;
    $columns['views'] = 'Views';
    return $columns;
}
add_filter('manage_posts_columns', 'smt_add_views_column');

function smt_format_views($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    } else {
        return $number;
    }
}

function smt_show_views_column($column, $post_id) {
    if ($column == 'views' && get_option('smt_enable_post_views', '1')) {
        $views = get_post_meta($post_id, 'smt_post_views_count', true);
        echo $views ? smt_format_views($views) : '0';
    }
}
add_action('manage_posts_custom_column', 'smt_show_views_column', 10, 2);

function smt_sort_views_column($columns) {
    if (!get_option('smt_enable_post_views', '1')) return $columns;
    $columns['views'] = 'smt_post_views_count';
    return $columns;
}
add_filter('manage_edit-post_sortable_columns', 'smt_sort_views_column');

function smt_sort_views($query) {
    if (!is_admin() || !$query->is_main_query() || !get_option('smt_enable_post_views', '1')) return;

    $orderby = $query->get('orderby');
    if ('smt_post_views_count' == $orderby) {
        $query->set('meta_key', 'smt_post_views_count');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'smt_sort_views');

// Add view counting functionality
function smt_count_views() {
    if (!get_option('smt_enable_post_views', '1') || !is_single() || is_admin()) {
        return;
    }

    global $post;

    // Verify post exists
    if (!isset($post->ID) || !($post instanceof WP_Post)) {
        return;
    }

    $post_id = $post->ID;
    $count_key = 'smt_post_views_count';

    // Use transient-based view tracking instead of sessions for better performance
    $ip_address = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $unique_key = md5($ip_address . $user_agent . $post_id);
    $transient_key = 'smt_view_' . $unique_key;

    // Check if this user has viewed this post in the last hour
    if (!get_transient($transient_key)) {
        $count = get_post_meta($post_id, $count_key, true);

        // Initialize count if it doesn't exist
        if ($count === '' || !is_numeric($count)) {
            $new_count = 1;
        } else {
            $new_count = intval($count) + 1;
        }

        update_post_meta($post_id, $count_key, $new_count);

        // Set transient for 1 hour to prevent duplicate counting
        set_transient($transient_key, true, HOUR_IN_SECONDS);
    }
}
add_action('wp', 'smt_count_views');

/* === Hide Game/Apps on Desktop Functionality === */
function smt_add_redirect_checkbox_meta_box() {
    if (!get_option('smt_enable_desktop_redirect', '1')) return;

    add_meta_box(
        'smt_redirect_checkbox_meta_box',
        'Redirect Desktop Users to 404',
        'smt_redirect_checkbox_meta_box_callback',
        'post',
        'side'
    );
}
add_action('add_meta_boxes', 'smt_add_redirect_checkbox_meta_box');

function smt_redirect_checkbox_meta_box_callback($post) {
    wp_nonce_field('smt_redirect_desktop_nonce', 'smt_redirect_desktop_nonce_field');
    $redirect_checked = get_post_meta($post->ID, 'redirect_desktop', true);
    echo '<label for="smt_redirect_desktop">';
    echo '<input type="checkbox" id="smt_redirect_desktop" name="smt_redirect_desktop" value="1" ' . checked(1, $redirect_checked, false) . '>';
    echo ' Redirect desktop/laptop users to 404 page</label>';
}

function smt_save_redirect_checkbox_data($post_id) {
    if (!isset($_POST['smt_redirect_desktop_nonce_field']) || 
        !wp_verify_nonce($_POST['smt_redirect_desktop_nonce_field'], 'smt_redirect_desktop_nonce') ||
        !current_user_can('edit_post', $post_id) ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }
    
    if (isset($_POST['smt_redirect_desktop'])) {
        update_post_meta($post_id, 'redirect_desktop', 1);
    } else {
        delete_post_meta($post_id, 'redirect_desktop');
    }
}
add_action('save_post', 'smt_save_redirect_checkbox_data');

function smt_redirect_desktop_to_404() {
    if (!get_option('smt_enable_desktop_redirect', '1') || wp_is_mobile() || !is_single()) {
        return;
    }

    global $post;
    if (!isset($post->ID) || !($post instanceof WP_Post)) {
        return;
    }

    $post_id = $post->ID;
    $redirect_checked = get_post_meta($post_id, 'redirect_desktop', true);

    if ($redirect_checked) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();

        $template = get_404_template();
        if ($template && file_exists($template)) {
            include($template);
        } else {
            wp_die('Page not found', '404 - Not Found', array('response' => 404));
        }
        exit();
    }
}
add_action('template_redirect', 'smt_redirect_desktop_to_404');

function smt_exclude_posts_from_queries($query) {
    if (!get_option('smt_enable_desktop_redirect', '1') || wp_is_mobile() || is_admin()) {
        return;
    }

    // Apply to main queries and front page/home queries
    if ($query->is_main_query() || is_front_page() || is_home()) {
        $existing_meta_query = $query->get('meta_query');

        $desktop_meta_query = array(
            'relation' => 'OR',
            array(
                'key'     => 'redirect_desktop',
                'value'   => '1',
                'compare' => '!='
            ),
            array(
                'key'     => 'redirect_desktop',
                'compare' => 'NOT EXISTS'
            )
        );

        if (!empty($existing_meta_query)) {
            $meta_query = array(
                'relation' => 'AND',
                $existing_meta_query,
                $desktop_meta_query
            );
        } else {
            $meta_query = $desktop_meta_query;
        }

        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'smt_exclude_posts_from_queries', 10, 1);

/* === Hide Admin Bar for All Users === */
function smt_hide_admin_bar() {
    if (!get_option('smt_hide_admin_bar', '1')) {
        return true;
    }
    return false;
}
add_filter('show_admin_bar', 'smt_hide_admin_bar');