<?php

namespace Falang\Filter\Site;

use RankMath\Paper\Paper;
use RankMath\Post;

class RankMath{

    /**
     * Constructor
     *
     * @since 1.3.8
     * @since 1.3.24 add sitemap entry
     * @since 1.3.24 add xml_post_usl
     *
     */
    public function __construct( ) {

        add_filter( 'rank_math/frontend/title', array( $this, 'rank_math_title' ), 10);
        
        add_filter( 'rank_math/frontend/description', array( $this, 'rank_math_description' ), 10);

        add_filter( 'rank_math/sitemap/entry', array( $this, 'rank_math_entry' ) ,10, 3 );

        add_filter( 'rank_math/sitemap/xml_post_url',array ($this, 'rank_math_xml_post_url'), 10, 2 );

    }

    /**
     * translate rankmath meta description
     *
     * @since 1.3.8
     *
     */
    public function rank_math_description($description){
        if (Falang()->is_default() ) return $description;
        global $post;


        // Handle homepage (static posts page)
        if (is_home() && !Post::is_simple_page()) {
            $posts_page_id = get_option('page_for_posts');
            if ($posts_page_id) {
                // Get the meta description from the posts page
                $posts_page_description = get_post_meta($posts_page_id, 'rank_math_description', true);
                if ($posts_page_description) {
                    $posts_page = get_post($posts_page_id);
                    $falang_post = new \Falang\Core\Post($posts_page_id);
                    return $falang_post->translate_post_meta($posts_page, 'rank_math_description', true, Falang()->get_current_language(), $posts_page_description);
                }

                // Fallback: Try to translate posts page content/excerpt as description
                $posts_page = get_post($posts_page_id);
                if ($posts_page) {
                    $falang_post = new \Falang\Core\Post($posts_page_id);

                    // Try post excerpt first
                    if (!empty($posts_page->post_excerpt)) {
                        $translated_excerpt = $falang_post->translate_post_field($posts_page, 'post_excerpt', Falang()->get_current_language(), $posts_page->post_excerpt);
                        if ($translated_excerpt !== $posts_page->post_excerpt) {
                            return $translated_excerpt;
                        }
                    }

                    // Try post content
                    if (!empty($posts_page->post_content)) {
                        $translated_content = $falang_post->translate_post_field($posts_page, 'post_content', Falang()->get_current_language(), $posts_page->post_content);
                        $content_excerpt = wp_trim_words(strip_tags($translated_content), 25, '...');
                        if (!empty($content_excerpt) && $content_excerpt !== wp_trim_words(strip_tags($posts_page->post_content), 25, '...')) {
                            return $content_excerpt;
                        }
                    }
                }
            }

            // Final fallback for home page: try to translate the description using falang__ function
            if (function_exists('falang__') && !empty($description)) {
                $fallback_translation = falang__($description);
                if ($fallback_translation !== $description) {
                    return $fallback_translation;
                }
            }

            return $description;
        }

        // Handle front page (static front page)
        if (is_front_page() && is_page()) {
            $front_page_id = get_option('page_on_front');
            if ($front_page_id && isset($post) && $post->ID == $front_page_id) {
                $front_page_description = get_post_meta($front_page_id, 'rank_math_description', true);
                if ($front_page_description) {
                    $front_page = get_post($front_page_id);
                    $falang_post = new \Falang\Core\Post($front_page_id);
                    return $falang_post->translate_post_meta($front_page, 'rank_math_description', true, Falang()->get_current_language(), $front_page_description);
                }
            }
        }

        // Handle archive pages
        if (is_archive()) {
            return $description;
        }

        // Handle shop page
        if (Post::is_shop_page()) {
            $id = Post::get_shop_page_id();
            if ($id) {
                $shop_description = get_post_meta($id, 'rank_math_description', true);
                if ($shop_description) {
                    $shop_page = get_post($id);
                    $falang_post = new \Falang\Core\Post($id);
                    return $falang_post->translate_post_meta($shop_page, 'rank_math_description', true, Falang()->get_current_language(), $shop_description);
                }
            }
        }

        // Handle regular posts/pages
        if (isset($post) && !empty($post->ID)) {
            $post_description = get_post_meta($post->ID, 'rank_math_description', true);


            if ($post_description) {
                $falang_post = new \Falang\Core\Post($post->ID);
                $translated_description = $falang_post->translate_post_meta($post, 'rank_math_description', true, Falang()->get_current_language(), $post_description);
                return $translated_description;
            }
        }

        // Fallback: Try to translate the description using falang__ function if available
        if (function_exists('falang__') && !empty($description)) {
            $fallback_translation = falang__($description);
            if ($fallback_translation !== $description) {
                return $fallback_translation;
            }
        }

        // Additional fallback: Try to get translated post content/excerpt as description
        if (isset($post) && !empty($post->ID)) {
            $falang_post = new \Falang\Core\Post($post->ID);

            // Try translating post excerpt as meta description
            if (!empty($post->post_excerpt)) {
                $translated_excerpt = $falang_post->translate_post_field($post, 'post_excerpt', Falang()->get_current_language(), $post->post_excerpt);
                if ($translated_excerpt !== $post->post_excerpt) {
                    return $translated_excerpt;
                }
            }

            // Try translating part of post content as meta description if no excerpt
            if (empty($description) || $description === $fallback_translation) {
                $post_content = get_post_field('post_content', $post->ID);
                if (!empty($post_content)) {
                    $translated_content = $falang_post->translate_post_field($post, 'post_content', Falang()->get_current_language(), $post_content);
                    // Extract first 160 characters as meta description
                    $content_excerpt = wp_trim_words(strip_tags($translated_content), 25, '...');
                    if (!empty($content_excerpt) && $content_excerpt !== wp_trim_words(strip_tags($post_content), 25, '...')) {
                        return $content_excerpt;
                    }
                }
            }
        }

        return $description;
    }

    /**
     * translate rankmath title
     *
     * @update 1.3.67 fix archive page , still but with attribute page and other taxonomy page
     *
     */
    public function rank_math_title($title){
        if (Falang()->is_default() ) return $title;
        global $post;

        // Handle homepage (static posts page)
        if (is_home() && !Post::is_simple_page()) {
            // Get the posts page ID
            $posts_page_id = get_option('page_for_posts');
            if ($posts_page_id) {
                // Translate the posts page title
                $translated_title = Falang()->translate_post_title($title, $posts_page_id);
                return $translated_title;
            }
            // If no posts page set, try to translate using falang__ function
            if (function_exists('falang__')) {
                return falang__($title);
            }
            return $title;
        }

        // Handle front page (static front page)
        if (is_front_page() && is_page()) {
            $front_page_id = get_option('page_on_front');
            if ($front_page_id && isset($post) && $post->ID == $front_page_id) {
                $translated_title = Falang()->translate_post_title($title, $front_page_id);
                $post->post_title = $translated_title;
                if (isset($post->post_type)) {
                    $title = Paper::get_from_options("pt_{$post->post_type}_title", $post, '%title% %sep% %sitename%');
                }
                return $title;
            }
        }

        //fix archive page , still but with attribute page and other taxonomy page
        if (is_archive() ){
            return $title;
        }

        //manage title for all products pages
        //on all product pages the global post is the first product
        if (Post::is_shop_page()){
            $id = Post::get_shop_page_id();
            $shop_page = get_post($id);
            $post = $shop_page;
        }

        if (isset($post) && !empty($post->ID) ){
            $title = Falang()->translate_post_title($title,$post->ID);
            //set the title to the translate post
            $post->post_title = $title;
            if (isset($post->post_type)){
                $title =  Paper::get_from_options( "pt_{$post->post_type}_title", $post, '%title% %sep% %sitename%' );
            }

        }

        return $title;
    }

    /**
     * Filter entry from sitemap for CPT with specific language
     *
     * @since 1.3.24
     *
     */
    public function rank_math_entry($url, $type, $object ){
        //don't filter by default here

        if ( 'post' == $type && 'page' == $object->post_type ){
           $locale = get_post_meta($object->ID, '_locale', true);
           if (!empty($locale) && 'all' != $locale ){
               $current_language = Falang()->get_current_language();
               if($current_language->locale != $locale){
                   return null;
               }
           }
        }
        return $url;
    }

    /**
     * fix permalink for post/page
     *
     * @since 1.3.24
     *
     */
    public function rank_math_xml_post_url($url, $post){
        if (Falang()->is_default() ) return $url;
        $falang_post = new \Falang\Core\Post($post->ID);

        if ($falang_post->is_post_type_translatable($post->post_type)){
            return get_permalink($post->ID);
        }

        return $url;
    }
}