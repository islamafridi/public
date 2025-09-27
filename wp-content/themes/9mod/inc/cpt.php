<?php
/* Register CPT News/Articles Post Type */
if (!defined('ABSPATH')) exit;
function apkt_articles_post_type() {
    $labels = array(
        'name'                => _x( 'Articles', 'Post Type General Name', 'apktemplates' ),
        'singular_name'       => _x( 'Article', 'Post Type Singular Name', 'apktemplates' ),
        'menu_name'           => __( 'Articles', 'apktemplates' ),
        'parent_item_colon'   => __( 'Parent Article:', 'apktemplates' ),
        'all_items'           => __( 'All Articles', 'apktemplates' ),
        'view_item'           => __( 'View Info', 'apktemplates' ),
        'add_new_item'        => __( 'Add New', 'apktemplates' ),
        'add_new'             => __( 'Add New', 'apktemplates' ),
        'edit_item'           => __( 'Edit Info', 'apktemplates' ),
        'update_item'         => __( 'Update Info', 'apktemplates' ),
        'search_items'        => __( 'Search', 'apktemplates' ),
        'not_found'           => __( 'Not found', 'apktemplates' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'apktemplates' ),
    );
    $args = array(
        'label'               => __( 'Article', 'apktemplates' ),
        'description'         => __( 'Info Articles', 'apktemplates' ),
        'labels'              => $labels,
        'show_in_rest'        => true,
        'supports'            => array( 'title', 'editor', 'thumbnail', 'comments' ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 7,
        'menu_icon'           => 'dashicons-media-document',
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'rewrite' => array( 'slug' => 'articles' ),
		'taxonomies'  => array( 'articles_tag' ),
    );
    register_post_type( 'articles', $args );
}
add_action( 'init', 'apkt_articles_post_type' );

/* Register CPT Articles Category/Tags */
function apkt_articles_taxonomy() {
    $labels = array(
        'name'              => _x( 'Articles Tag', 'tags general name', 'apktemplates' ),
        'singular_name'     => _x( 'Article Tag', 'tags singular name', 'apktemplates' ),
        'search_items'      => __( 'Search Tags', 'apktemplates' ),
        'all_items'         => __( 'All Tags', 'apktemplates' ),
        'edit_item'         => __( 'Edit Tag', 'apktemplates' ),
        'update_item'       => __( 'Update Tag', 'apktemplates' ),
        'add_new_item'      => __( 'Add New Tag', 'apktemplates' ),
        'new_item_name'     => __( 'New Tag Name', 'apktemplates' ),
        'menu_name'         => __( 'Tags', 'apktemplates' ),
    );

    $args = array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'public'            => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
		'show_in_nav_menus'	=> false,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'tarticles' ),
    );

    register_taxonomy( 'articles_tag', 'articles', $args );
}

add_action( 'init', 'apkt_articles_taxonomy' );

function apkt_news_post_type() {
    $labels = array(
        'name'                => _x( 'News', 'Post Type General Name', 'apktemplates' ),
        'singular_name'       => _x( 'News', 'Post Type Singular Name', 'apktemplates' ),
        'menu_name'           => __( 'News', 'apktemplates' ),
        'parent_item_colon'   => __( 'Parent News:', 'apktemplates' ),
        'all_items'           => __( 'All News', 'apktemplates' ),
        'view_item'           => __( 'View Info', 'apktemplates' ),
        'add_new_item'        => __( 'Add New', 'apktemplates' ),
        'add_new'             => __( 'Add New', 'apktemplates' ),
        'edit_item'           => __( 'Edit Info', 'apktemplates' ),
        'update_item'         => __( 'Update Info', 'apktemplates' ),
        'search_items'        => __( 'Search', 'apktemplates' ),
        'not_found'           => __( 'Not found', 'apktemplates' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'apktemplates' ),
    );
    $args = array(
        'label'               => __( 'News', 'apktemplates' ),
        'description'         => __( 'Info News', 'apktemplates' ),
        'labels'              => $labels,
        'show_in_rest'        => true,
        'supports'            => array( 'title', 'editor', 'thumbnail', 'comments' ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 8,
        'menu_icon'           => 'dashicons-media-document',
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'rewrite'             => array( 'slug' => 'news' ),
		'taxonomies'          => array( 'news_tag' ),
    );
    register_post_type( 'news', $args );
}
add_action( 'init', 'apkt_news_post_type' );

/* Register CPT News Category/Tags */
function apkt_news_taxonomy() {
    // Register articles tags
    $labels = array(
        'name'              => _x( 'News Tags', 'tags general name', 'apktemplates' ),
        'singular_name'     => _x( 'News Tag', 'tags singular name', 'apktemplates' ),
        'search_items'      => __( 'Search Tags', 'apktemplates' ),
        'all_items'         => __( 'All Tags', 'apktemplates' ),
        'edit_item'         => __( 'Edit Tag', 'apktemplates' ),
        'update_item'       => __( 'Update Tag', 'apktemplates' ),
        'add_new_item'      => __( 'Add New Tag', 'apktemplates' ),
        'new_item_name'     => __( 'New Tag Name', 'apktemplates' ),
        'menu_name'         => __( 'Tags', 'apktemplates' ),
    );

    $args = array(
        'hierarchical'      => false,
        'labels'            => $labels,
        'public'            => true,
        'show_ui'           => true,
		'show_in_rest'      => true,
        'show_admin_column' => true,
		'show_in_nav_menus'	=> false,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'tnews' ),
    );

    register_taxonomy( 'news_tag', 'news', $args );
}

add_action( 'init', 'apkt_news_taxonomy' );