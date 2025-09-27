<?php

namespace Falang\Filter\Admin;


class WooCommerce {

	/**
	 * Constructor
	 *
	 * @since 1.3.24
     * @since 1.3.34 add woocommerce_variation_options
	 *
	 */
	public function __construct( ) {

        add_filter('woocommerce_get_cart_page_permalink', [ $this, 'translate_woocommerce_get_page_page_permalink' ]);
        add_filter('woocommerce_get_checkout_page_permalink', [ $this, 'translate_woocommerce_get_page_page_permalink' ]);
        add_filter('woocommerce_variation_options', [ $this, 'translate_woocommerce_variation_options' ],10, 3);

    }

    /**
     * Translate the permalink when update cart quantity
     * necessary becasue the get_home_url don't have the language code
     * filter not translate_home_url not apply
     *
     * @since 1.3.24 manage get_{wc_page}_hook front ajax call
     */
    public function translate_woocommerce_get_page_page_permalink($permalink){
        if (!Falang()->is_default()) {
            if (get_option('permalink_structure')) {
                $home = get_home_url() . '/' . Falang()->get_current_language()->slug;
                $permalink = str_replace(home_url(), $home, $permalink);
            } else {
                $permalink = add_query_arg(array('lang' => Falang()->get_current_language()->slug), $permalink);
            }
        }
        return $permalink;
    }

    /**
     * Display variation link
     * need to have the jquery code here , variation are loaded dynamicly
     *
     * @since 1.3.34
     *
     */
    public function translate_woocommerce_variation_options($loop, $variation_data, $variation ) {
        ?>
        <script>
            //popup system
            jQuery( document ).ready(function($) {
                jQuery(".falang-thickbox").click(function(e) {
                    tb_show('', this.href, false);
                    jQuery("#TB_window").addClass("falang-modal-full");
                    return false;
                });
            });
        </script>
        <div class="falang-variation-language-wrapper">
            <h4>
                <?php esc_html_e( 'Translations', 'falang' ); ?>
            </h4>
            <?php
            $admin_links = new \Falang\Core\Admin_Links();
            echo $admin_links->display_post_translation_link_row($variation->ID,true,false);
            ?>
        </div>
        <?php
    }


}