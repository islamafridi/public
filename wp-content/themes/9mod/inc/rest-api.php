<?php
if (!defined('ABSPATH')) exit;

/**
 * 5Plays APK Store REST API Endpoints
 * Provides API endpoints for Flutter mobile app
 */

// Initialize REST API
add_action('rest_api_init', 'apkt_register_rest_api_endpoints');

function apkt_register_rest_api_endpoints() {
    // Get all apps
    register_rest_route('apk-templates/v1', '/apps', array(
        'methods' => 'GET',
        'callback' => 'apkt_get_apps_api',
        'permission_callback' => '__return_true',
        'args' => array(
            'page' => array(
                'default' => 1,
                'sanitize_callback' => 'absint',
            ),
            'per_page' => array(
                'default' => 10,
                'sanitize_callback' => 'absint',
            ),
            'search' => array(
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'category' => array(
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ),
        ),
    ));

    // Get single app
    register_rest_route('apk-templates/v1', '/apps/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'apkt_get_single_app_api',
        'permission_callback' => '__return_true',
        'args' => array(
            'id' => array(
                'validate_callback' => function($param, $request, $key) {
                    return is_numeric($param);
                }
            ),
        ),
    ));

    // Get app categories
    register_rest_route('apk-templates/v1', '/categories', array(
        'methods' => 'GET',
        'callback' => 'apkt_get_app_categories_api',
        'permission_callback' => '__return_true',
    ));

    // Get API settings
    register_rest_route('apk-templates/v1', '/settings', array(
        'methods' => 'GET',
        'callback' => 'apkt_get_api_settings',
        'permission_callback' => '__return_true',
    ));
}

function apkt_get_apps_api($request) {
    $page = $request->get_param('page');
    $per_page = min($request->get_param('per_page'), 50); // Limit to 50 per page
    $search = $request->get_param('search');
    $category = $request->get_param('category');

    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'meta_query' => array(
            array(
                'key' => 'wp_title_GP',
                'compare' => 'EXISTS'
            )
        )
    );

    if (!empty($search)) {
        $args['s'] = $search;
    }

    if (!empty($category)) {
        $args['category_name'] = $category;
    }

    $query = new WP_Query($args);
    $apps = array();

    foreach ($query->posts as $post) {
        $apps[] = apkt_format_app_for_api($post);
    }

    return new WP_REST_Response(array(
        'apps' => $apps,
        'pagination' => array(
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page,
            'per_page' => $per_page,
        ),
        'api_info' => array(
            'version' => '1.0',
            'base_url' => get_option('apkt_api_base_url', home_url()),
            'timestamp' => current_time('mysql'),
        )
    ), 200);
}

function apkt_get_single_app_api($request) {
    $post_id = $request->get_param('id');
    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'post' || $post->post_status !== 'publish') {
        return new WP_Error('app_not_found', 'App not found', array('status' => 404));
    }

    $app_data = apkt_format_app_for_api($post, true);

    return new WP_REST_Response($app_data, 200);
}

function apkt_get_app_categories_api($request) {
    $categories = get_categories(array(
        'taxonomy' => 'category',
        'hide_empty' => true,
    ));

    $formatted_categories = array();
    foreach ($categories as $category) {
        $formatted_categories[] = array(
            'id' => $category->term_id,
            'name' => $category->name,
            'slug' => $category->slug,
            'count' => $category->count,
        );
    }

    return new WP_REST_Response($formatted_categories, 200);
}

function apkt_get_api_settings($request) {
    $settings = array(
        'app_name' => get_option('apkt_app_name', '5Plays APK Store'),
        'app_description' => get_option('apkt_app_description', 'Download and discover amazing Android apps'),
        'app_version' => get_option('apkt_app_version', '1.0.0'),
        'api_version' => '1.0',
        'base_url' => get_option('apkt_api_base_url', home_url()),
        'features' => array(
            'search' => true,
            'categories' => true,
            'ratings' => true,
            'screenshots' => true,
            'download_links' => true,
            'mod_support' => true,
        ),
        'contact' => array(
            'support_email' => get_option('apkt_support_email', get_option('admin_email')),
            'website' => get_option('apkt_website_url', home_url()),
        ),
    );

    return new WP_REST_Response($settings, 200);
}

function apkt_format_app_for_api($post, $include_details = false) {
    $post_id = $post->ID;
    $rating_data = apkt_get_star_rating($post_id);

    $app_data = array(
        'id' => $post_id,
        'title' => get_the_title($post),
        'content' => $include_details ? apply_filters('the_content', $post->post_content) : wp_trim_words($post->post_content, 30),
        'excerpt' => get_the_excerpt($post),
        'featured_image' => get_the_post_thumbnail_url($post, 'full'),
        'date_published' => get_the_date('c', $post),
        'app_info' => array(
            'name' => get_post_meta($post_id, 'wp_title_GP', true),
            'version' => get_post_meta($post_id, 'wp_version_GP', true),
            'size' => get_post_meta($post_id, 'wp_sizes_GP', true),
            'mod_features' => get_post_meta($post_id, 'wp_mods', true),
            'package_id' => get_post_meta($post_id, 'wp_GP_ID', true),
            'developer' => get_post_meta($post_id, 'wp_developers_GP', true),
            'content_rating' => get_post_meta($post_id, 'wp_contentrated_GP', true),
            'required_os' => get_post_meta($post_id, 'wp_requires_GP', true),
            'price' => get_post_meta($post_id, 'price', true),
            'is_mod' => get_post_meta($post_id, 'mod-tick-box', true) === 'on',
            'mod_info' => get_post_meta($post_id, 'mod_info', true),
            'download_info' => get_post_meta($post_id, 'download_info', true),
        ),
        'rating' => array(
            'average' => floatval($rating_data->rating_average),
            'votes' => intval($rating_data->rating_votes),
            'total' => intval(get_post_meta($post_id, 'total_rating', true)),
        ),
        'categories' => wp_get_post_categories($post_id, array('fields' => 'names')),
    );

    if ($include_details) {
        // Add download links
        $download_links = get_post_meta($post_id, 'repeatable_download_link', true);
        $app_data['download_links'] = array();

        if ($download_links) {
            foreach ($download_links as $link) {
                $app_data['download_links'][] = array(
                    'name' => $link['download_name'] ?? '',
                    'url' => $link['download_url'] ?? '',
                    'size' => $link['download_size'] ?? '',
                    'mod_info' => $link['download_mod_info'] ?? '',
                    'mod_note' => $link['download_mod_note'] ?? '',
                    'note' => $link['download_note'] ?? '',
                    'group' => $link['download_group'] ?? 'Default',
                );
            }
        }

        // Add screenshots
        $screenshots = get_post_meta($post_id, 'ss_images', true);
        $app_data['screenshots'] = array();

        if ($screenshots) {
            foreach ($screenshots as $screenshot) {
                if (!empty($screenshot['ss_url'])) {
                    $app_data['screenshots'][] = $screenshot['ss_url'];
                }
            }
        }
    }

    return $app_data;
}
?>