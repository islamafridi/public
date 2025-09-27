<?php
$theme_data = wp_get_theme();
$theme_dir = get_template_directory();

$theme_name = $theme_data->get("Name");
$theme_version = $theme_data->get("Version");
$text_domain = $theme_data->get("TextDomain");

define("APKT_THEME_NAME", $theme_name);
define("APKT_THEME_VERSION", $theme_version);
define("APKT_TRANSLATE", $text_domain);

require_once $theme_dir . "/admin/inc/admin-functions.php";
require_once $theme_dir . "/inc/advertisements.php";
require_once $theme_dir . "/inc/breadcrumbs.php";
require_once $theme_dir . "/inc/comments.php";
require_once $theme_dir . "/inc/core.php";
require_once $theme_dir . "/inc/downloads-route.php";
require_once $theme_dir . "/inc/cpt.php";
require_once $theme_dir . "/inc/inits.php";
require_once $theme_dir . "/inc/pagination.php";
require_once $theme_dir . "/inc/snippet.php";
require_once $theme_dir . "/inc/translate.class.php";

if (!function_exists("an1_setup")) {
    add_action("after_setup_theme", "an1_setup");
    function an1_setup()
    {
        load_theme_textdomain("apktemplates");
        add_theme_support("automatic-feed-links");
        add_theme_support("post-thumbnails");
        add_theme_support("post-formats", [
            "aside",
            "image",
            "video",
            "quote",
            "link",
            "status",
        ]);
        add_theme_support("woocommerce");
        add_theme_support("title-tag");
        add_theme_support("html5", [
            "search-form",
            "comment-form",
            "comment-list",
            "gallery",
            "caption",
            "script",
            "style",
        ]);
        add_theme_support("customize-selective-refresh-widgets");
        add_theme_support("align-wide");
        add_theme_support("editor-styles");

        register_nav_menus([
            "header_menu" => __("Header Menu", "apktemplates"),
            "footer_menu" => __("Footer Menu", "apktemplates"),
        ]);
    }
}

add_filter("get_the_archive_title", function ($title) {
    if (is_category()) {
        $title = single_cat_title("", false);
    } elseif (is_tag()) {
        $title = single_tag_title("", false);
    } elseif (is_author()) {
        $title = '<span class="vcard">' . get_the_author() . "</span>";
    } elseif (is_tax()) {
        $title = sprintf(
            __('%1$s', "apktemplates"),
            single_term_title("", false)
        );
    } elseif (is_post_type_archive()) {
        $title = post_type_archive_title("", false);
    }
    return $title;
});

function apktemplates_clean($string)
{
    $string = str_replace(" ", "-", $string);
    $string = preg_replace("/[^A-Za-z0-9\-]/", "", $string);
    return preg_replace("/-+/", "-", $string);
}

function equeue_an1_style()
{
    wp_enqueue_style(
        "an1",
        get_template_directory_uri() . "/assets/css/style.min.css",
        [],
        APKT_THEME_VERSION,
        "all"
    );

    if (is_singular("post")) {
        wp_enqueue_style(
            "fancybox",
            get_template_directory_uri() . "/assets/css/fancybox.css",
            ["an1"],
            null,
            "all"
        );
    }

    $url = $_SERVER["REQUEST_URI"];
    if (strpos($url, "/download/") !== false) {
        wp_enqueue_style(
            "an1-download-style",
            get_template_directory_uri() . "/assets/css/download.min.css",
            ["an1"],
            APKT_THEME_VERSION,
            "all"
        );
    }
    if (strpos($url, "/downloads/") !== false) {
        wp_enqueue_style(
            "an1-download-style",
            get_template_directory_uri() . "/assets/css/download-links.min.css",
            ["an1"],
            APKT_THEME_VERSION,
            "all"
        );
    }
}
add_action("wp_enqueue_scripts", "equeue_an1_style");

function an1_scripts() {
    wp_enqueue_script("jquery"); // always load jQuery

    $url = $_SERVER["REQUEST_URI"];

    if (strpos($url, "/download/") === false) {
        wp_enqueue_script(
            "quicklink",
            "https://cdnjs.cloudflare.com/ajax/libs/quicklink/2.2.0/quicklink.umd.js",
            array(),
            null,
            true
        );

        if (is_singular("post")) {
            wp_enqueue_script(
                "fancybox",
                get_template_directory_uri() . "/assets/js/fancybox.js",
                array("jquery"),
                null,
                true
            );
            wp_script_add_data("fancybox", "defer", true);
        }

        wp_enqueue_script(
            "mod9-script",
            get_template_directory_uri() . "/assets/js/main.min.js",
            array("jquery"), // depends on jQuery
            APKT_THEME_VERSION,
            true
        );

        wp_enqueue_script(
            "blur-up",
            get_template_directory_uri() . "/assets/js/ls.blur-up.min.js",
            array(),
            null,
            true
        );
    }

    // Always load lazysizes on every page
    wp_enqueue_script(
        "lazysizes",
        get_template_directory_uri() . "/assets/js/lazysizes.min.js",
        array(),
        null,
        true
    );

    if (is_singular("post")) {
        wp_localize_script("mod9-script", "apktemplates_ajax_vars", [
            "ajax_url" => admin_url("admin-ajax.php"),
            "nonce"    => wp_create_nonce("star_rating_nonce"),
        ]);
    }
}
add_action("wp_enqueue_scripts", "an1_scripts");


add_action("wp_enqueue_scripts", "an1_scripts");

// poster in posts
function custom_add_thumbnail_column($columns)
{
    $new_columns = [];
    foreach ($columns as $key => $value) {
        if ($key == "title") {
            $new_columns["thumbnail"] = __("Poster", "9mod");
        }
        $new_columns[$key] = $value;
    }
    return $new_columns;
}
add_filter("manage_posts_columns", "custom_add_thumbnail_column");

function custom_display_thumbnail_column($column_name, $post_id)
{
    if ($column_name === "thumbnail") {
        if (has_post_thumbnail($post_id)) {
            echo get_the_post_thumbnail($post_id, [100, 100]);
        } else {
            echo __("No Thumbnail", "9mod");
        }
    }
}
add_action(
    "manage_posts_custom_column",
    "custom_display_thumbnail_column",
    10,
    2
);

function custom_admin_thumbnail_styles()
{
    echo '<style>
        .column-thumbnail {
            width: 80px !important;
            text-align: center;
        }
        .column-thumbnail img {
            max-width: 80px;
            height: auto;
            display: block;
            margin: 0 auto;
			border-radius: 10px;
        }
    </style>';
}
add_action("admin_head", "custom_admin_thumbnail_styles");

// swiper js
function mytheme_enqueue_swiper_home() {
    if ( is_front_page() || is_home() ) {
        $swiper_version = '11.0.0';

        // Use get_stylesheet_directory_* if this code may run in a child theme
        $theme_uri = get_template_directory_uri();
        $theme_dir = get_template_directory();

        // Core Swiper CSS (CDN)
        wp_enqueue_style(
            'swiper-core',
            "https://cdn.jsdelivr.net/npm/swiper@{$swiper_version}/swiper-bundle.min.css",
            array(),
            $swiper_version
        );

        // Theme's custom Swiper overrides (loaded after core)
        $custom_css_file = $theme_dir . '/assets/css/swiper.min.css';
        if ( file_exists( $custom_css_file ) ) {
            wp_enqueue_style(
                'swiper-custom',
                $theme_uri . '/assets/css/swiper.min.css',
                array( 'swiper-core' ), // ensures it loads after the core file
                filemtime( $custom_css_file )
            );
        }

        // Core Swiper JS (CDN)
        wp_enqueue_script(
            'swiper-core-js',
            "https://cdn.jsdelivr.net/npm/swiper@{$swiper_version}/swiper-bundle.min.js",
            array(),
            $swiper_version,
            true
        );

        // Init script depends on swiper-core-js
        $init_js_file = $theme_dir . '/assets/js/swiper-init.js';
        if ( file_exists( $init_js_file ) ) {
            wp_enqueue_script(
                'swiper-init',
                $theme_uri . '/assets/js/swiper-init.js',
                array( 'swiper-core-js' ),
                filemtime( $init_js_file ),
                true
            );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'mytheme_enqueue_swiper_home' );
// end swiper js

// google fonts
function mytheme_google_fonts_preconnect() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action('wp_head', 'mytheme_google_fonts_preconnect', 5);

function mytheme_enqueue_google_fonts() {
    wp_enqueue_style(
        'mytheme-google-fonts',
        'https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap',
        array(),
        null
    );
}
add_action( 'wp_enqueue_scripts', 'mytheme_enqueue_google_fonts' );
// end google fonts

// Fix hreflang locale consistency for Falang
function fix_falang_hreflang_locales($hreflangs) {
    $corrected_hreflangs = array();

    foreach ($hreflangs as $lang => $url) {
        // Convert to proper ISO format: en-US, ru-RU (uppercase country codes)
        if ($lang !== 'x-default') {
            $parts = explode('-', $lang);
            if (count($parts) === 2) {
                $lang = strtolower($parts[0]) . '-' . strtoupper($parts[1]);
            }
        }
        $corrected_hreflangs[$lang] = $url;
    }

    return $corrected_hreflangs;
}
add_filter('falang_hreflang', 'fix_falang_hreflang_locales');

// delete extra translatons
// add_action('init', function() {
//     $option_name = 'falang_wpml_strings';
//     $strings = get_option($option_name, []);

//     if (!empty($strings) && is_array($strings)) {
//         // Loop through all strings and remove the ones from 'apktemplates' context
//         foreach ($strings as $key => $data) {
//             if (isset($data['name']) && $data['name'] === '5play') {
//                 unset($strings[$key]);
//             }
//         }

//         // Save back the cleaned array
//         update_option($option_name, $strings);
//     }
// });