<?php

namespace Falang\Filter\Site;

//Shortcoder plugin
//allow shortcode filtering by language
//translation can be done with Falang Pro only

class Shortcoder
{
    /**
     * Constructor
     *
     * @since 1.3.36
     *
     */
    public function __construct( ) {
        if ( defined( 'SC_VERSION' ) ) {
            add_filter('sc_mod_shortcode',[ $this, 'sc_mod_shortcode'], 10, 3);
        }
    }

    /**
     * fitler the shortcode if not the right language
     * @since 1.3.36
     *
     */
    function sc_mod_shortcode($shortcode, $atts, $enclosed_content)   {
        $post_language = get_post_meta($shortcode['id'],'_locale',true);
        $current_language = Falang()->get_current_language();
        //nothing to do for a all language meta
        if (isset($post_language) && !empty($post_language) && $post_language != 'all'){
            if ($current_language->locale != $post_language){
                $shortcode['settings']['_sc_disable_sc'] = 'yes';
            }
        }

        return $shortcode;
    }
}