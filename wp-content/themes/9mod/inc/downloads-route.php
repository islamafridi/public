<?php
// downloads page start
function custom_add_get_download_endpoint() {
    add_rewrite_endpoint('downloads', EP_PERMALINK);
}
add_action('init', 'custom_add_get_download_endpoint');

function custom_get_download_template() {
    global $wp_query;

    if (isset($wp_query->query_vars['downloads'])) {
        $post_id = get_the_ID();

        // Optional: restrict to specific post types
         if (get_post_type($post_id) !== 'post') return;

        $template_path = get_template_directory() . '/template/template-downloads.php';

        if (file_exists($template_path)) {
            include $template_path;
            exit;
        }
    }
}
add_action('template_redirect', 'custom_get_download_template');
// Add specific rewrite rules for multilingual downloads with translated category slugs
function custom_multilingual_downloads_rules() {
    // English URLs
    add_rewrite_rule(
        '^games/([^/]+)/downloads/?$',
        'index.php?category_name=games&name=$matches[1]&downloads=1',
        'top'
    );
    add_rewrite_rule(
        '^apps/([^/]+)/downloads/?$',
        'index.php?category_name=apps&name=$matches[1]&downloads=1',
        'top'
    );

    // Russian URLs with translated category slugs
    add_rewrite_rule(
        '^ru/igry/([^/]+)/downloads/?$',
        'index.php?category_name=games&name=$matches[1]&downloads=1',
        'top'
    );
    add_rewrite_rule(
        '^ru/programmy/([^/]+)/downloads/?$',
        'index.php?category_name=apps&name=$matches[1]&downloads=1',
        'top'
    );
}
add_action('init', 'custom_multilingual_downloads_rules', 99);

// Flush rules when needed
function custom_multilingual_downloads_flush() {
    if (get_option('custom_multilingual_downloads_flushed') != '1') {
        custom_add_get_download_endpoint();
        custom_multilingual_downloads_rules();
        flush_rewrite_rules();
        update_option('custom_multilingual_downloads_flushed', '1');
    }
}
add_action('wp_loaded', 'custom_multilingual_downloads_flush');


// download page
function apkt_download_page_endpoint(){
	add_rewrite_endpoint('download', EP_PERMALINK);
}
add_action('init', 'apkt_download_page_endpoint');

function apkt_download_page_template(){
	global $wp_query;

	if (isset($wp_query->query_vars['download'])) {
		$requested_url = $_SERVER['REQUEST_URI'];

		if (preg_match('/\/download\/(\d+)/', $requested_url, $matches)) {
			$download_id = $matches[1];
			$template_path = get_template_directory() . '/template/download.php';
		} else if (strpos($requested_url, '/download') !== false) {
			$template_path = get_template_directory() . '/template/download.php';
		}

		if (isset($template_path) && file_exists($template_path)) {
			global $custom_download_id;
			$custom_download_id = isset($download_id) ? $download_id : null;
			include $template_path;
			exit;
		}
	}
}
add_action('template_redirect', 'apkt_download_page_template');

// Add specific rewrite rules for multilingual download (singular) with translated category slugs
function custom_multilingual_download_rules() {
    // English URLs for /download endpoint
    add_rewrite_rule(
        '^games/([^/]+)/download/?$',
        'index.php?category_name=games&name=$matches[1]&download=1',
        'top'
    );
    add_rewrite_rule(
        '^apps/([^/]+)/download/?$',
        'index.php?category_name=apps&name=$matches[1]&download=1',
        'top'
    );

    // Russian URLs with translated category slugs for /download endpoint
    add_rewrite_rule(
        '^ru/igry/([^/]+)/download/?$',
        'index.php?category_name=games&name=$matches[1]&download=1',
        'top'
    );
    add_rewrite_rule(
        '^ru/programmy/([^/]+)/download/?$',
        'index.php?category_name=apps&name=$matches[1]&download=1',
        'top'
    );

    // Handle download with ID for both languages
    add_rewrite_rule(
        '^games/([^/]+)/download/(\d+)/?$',
        'index.php?category_name=games&name=$matches[1]&download=1&download_id=$matches[2]',
        'top'
    );
    add_rewrite_rule(
        '^apps/([^/]+)/download/(\d+)/?$',
        'index.php?category_name=apps&name=$matches[1]&download=1&download_id=$matches[2]',
        'top'
    );
    add_rewrite_rule(
        '^ru/igry/([^/]+)/download/(\d+)/?$',
        'index.php?category_name=games&name=$matches[1]&download=1&download_id=$matches[2]',
        'top'
    );
    add_rewrite_rule(
        '^ru/programmy/([^/]+)/download/(\d+)/?$',
        'index.php?category_name=apps&name=$matches[1]&download=1&download_id=$matches[2]',
        'top'
    );
}
add_action('init', 'custom_multilingual_download_rules', 98);

// Flush rules for download endpoint
function custom_multilingual_download_flush() {
    if (get_option('custom_multilingual_download_flushed') != '1') {
        apkt_download_page_endpoint();
        custom_multilingual_download_rules();
        flush_rewrite_rules();
        update_option('custom_multilingual_download_flushed', '1');
    }
}
add_action('wp_loaded', 'custom_multilingual_download_flush');

//downloads page end