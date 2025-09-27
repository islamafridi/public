<?php

/**
 * Fired during plugin activation
 *
 * @link       www.faboba.com
 * @since      1.0
 *
 * @package    Falang
 * @subpackage Falang/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0
 *
 * @update 1.3.56 fix message display on multisite or polylang activated
 *
 * @package    Falang
 * @subpackage Falang/includes
 * @author     Stéphane Bouey <stephane.bouey@faboba.com>
 */
class Falang_Activator {
	const POLYLANG = 'polylang/polylang.php';

	/**
	 * @since    1.0
     * @update 1.3.56 fix error message with wp_die
     *                allow installation with Polylang installed
	 */
	public static function activate() {
		global $wp_version;
		$falang_model = new \Falang\Model\Falang_Model();

		$options = get_option($falang_model->option_name);

		load_plugin_textdomain( 'falang', false, basename( FALANG_DIR ) . '/languages' ); // plugin i18n


		//no multisiste
		if ( is_multisite()) {
            wp_die(
                sprintf(
                    __('Falang cannot be installed on a multisite system, <a href="%s">Go back to installed plugins</a>', 'falang' ),
                    self_admin_url( 'plugins.php' )
                )
			);
		}

		//no polylang
		if (  is_plugin_active(self::POLYLANG)) {
            wp_die(
                sprintf(
                    __('Falang cannot be activated with Polylang activated on the same site, <a href="%s">Go back to installed plugins</a>', 'falang' ),
                    self_admin_url( 'plugins.php' )
                )
            );
		}

		//create default options for the fist activation
		if (empty($options)) {
			update_option($falang_model->option_name, self::get_default_options());
		}

        // Avoid 1 query on every pages if no wpml strings is registered
        if ( ! get_option( 'falang_wpml_strings' ) ) {
            update_option( 'falang_wpml_strings', array() );
        }

        // create the empty falang dismissed notice option
        if ( ! get_option( 'falang_dismissed_notices' ) ) {
            update_option( 'falang_dismissed_notices', array() );
        }

		//create default language (after option to update default language)
        if (empty($falang_model->get_languages_list())) {
            self::add_default_language();
        }

	}

	/**
	 * Get default Falang options
	 *
	 * @since 1.0
     * @update 1.3.54 add deepl
     * @update 1.3.58 add nav_men_item meta_keys _menu_item_url checked by default
	 *
	 * return array
	 */
	public static function get_default_options() {
		return array(
			'post_type' => array(
				'post' => array('translatable' => true),
				'page' => array('translatable' => true),
				'nav_menu_item' => array('translatable' => true,'meta_keys' => array('0' => '_menu_item_url'))
			),
			'taxonomy' => array(
				'category' => array('translatable' => true),
				'post_tag' => array('translatable' => true)
			),
			'show_slug' => false,
			'autodetect' => false,
			'need_flush' => 1,
			'enable_service' => false,
			'service_name' => '',
            'deepl_key' => '',
            'deepl_free' => true,
            'google_key' => '',
            'azure_key' => '',
			'yandex_key' => '',
            'lingvanex_key' => '',
			'debug_admin' => false,
			'delete_trans_on_uninstall' => false,
            'flag_width' => 16,
            'flag_height' => 11,
            'association' => false,
            'downloadid' => '',
            'frontend_ajax' => false,
            'no_autop' => false
        );
	}

    /*
    * Add en_US language taxonomy on installation.
    * since 1.3.6 don't install language en_US when others languages exist
    *
    * @update 1.3.55 fix error during term creation rtl is an int
    *                add default order
     * @update 1.3.56 fix when polylang is/was installed on the site
     *               add published
    * */
    public static function add_default_language(){
        $falang_model = new \Falang\Model\Falang_Model();

        $args = array(
            'code' => 'en',
            'slug' => 'en',//slug is added from the languages file
            'locale' => 'en_US',
            'name' => 'English',
            'rtl' => 0,//0 left to right
            'flag' => 'us',
            'facebook' => 'en_US',
            'order' => 1
        );

        $description = serialize(array('locale' => $args['locale'], 'rtl' => (int)$args['rtl'], 'flag_code' => empty($args['flag']) ? '' : $args['flag'],'order' => (int)$args['order']));

        $default_term_language = get_term_by('name',$args['name'],'language');

        //default case (polylang or other not installed before using taxonomy
        if (empty($default_term_language)){
            // First add the language taxonomy
            $r = wp_insert_term($args['name'], 'language', array('slug' => $args['slug'], 'description' => $description));
            if (is_wp_error($r)) {
                wp_die(
                    sprintf(
                        '<p style = "font-family: sans-serif; font-size: 12px; color: #333; margin: -5px">%s</p>',
                        esc_html__('Impossible to init default en_US language', 'falang')
                    )
                );
           }
        } else {
                //probably a polylang term language installed before
                $r = (array) $default_term_language;
                wp_update_term($r['term_id'],'language',array('slug' => $args['slug'], 'description' => $description));
        }

        // The term_language taxonomy
        // Don't want shared terms so use a different slug
        wp_insert_term($args['name'], 'term_language', array('slug' => 'falang_' . $args['slug']));

        // Init a mo_id for this language
        $post = array(
            'post_title' => 'falang_mo_' . $r['term_id'],
            'post_status' => 'private', // To avoid a conflict with WP Super Cache. See https://wordpress.org/support/topic/polylang_mo-and-404s-take-2
            'post_type' => 'falang_mo',
        );
        $mo_id = wp_insert_post($post);
        update_post_meta($mo_id, '_falang_strings_translations', '');

        //set default to en_US
		$falang_model->update_default_lang('en_US');

	}

}
