<?php
/**
 * The file that defines the Admin Links
 *
 * @link       www.faboba.com
 * @since      1.0
 *
 * @package    Falang
 */

namespace Falang\Core;


class Admin_Links extends Links {

	/**
	 * Get the link to create a new post translation
	 *
	 * @since 1.0
	 *
	 * @param int    $post_id  the source post id
	 * @param object $language the language of the new translation
	 * @return string
	 */
	public function get_post_translation_link( $post_id, $language ) {

		$post_type = get_post_type( $post_id );
		$post_type_object = get_post_type_object( get_post_type( $post_id ) );
		if ( ! current_user_can( $post_type_object->cap->create_posts ) ) {
			return '';
		}

		// Special case for the privacy policy page which is associated to a specific capability
		if ( 'page' === $post_type_object->name && ! current_user_can( 'manage_privacy_options' ) ) {
			$privacy_page = get_option( 'wp_page_for_privacy_policy' );
//			if ( $privacy_page && in_array( $post_id, $this->model->post->get_translations( $privacy_page ) ) ) {
//				return '';
//			}
		}

		if ( 'attachment' == $post_type ) {
			$args = array(
				'page' => 'falang-translation',
				'post_id' => $post_id,
				'language'  => $language->locale,
				'action' => 'edit'
			);

			// Add nonce for media as we will directly publish a new attachment from a click on this link
			$link = wp_nonce_url( add_query_arg( $args, admin_url( 'admin.php' ) ), 'edit' );
		} else {
			$args = array(
				'page' => 'falang-translation',
				'post_id' => $post_id,
				'language'  => $language->locale,
				'action' => 'edit'
			);

			$link = wp_nonce_url( add_query_arg( $args, admin_url( 'admin.php' ) ), 'edit' );
		}

		/**
		 * Filter the new post translation link
		 *
		 * @since 1.0
		 *
		 * @param string $link     the new post translation link
		 * @param object $language the language of the new translation
		 * @param int    $post_id  the source post id
		 */
		return apply_filters( 'falang_get_post_translation_link', $link, $language, $post_id );
	}


	/**
	 * Returns html markup for a new post translation link
	 *
	 * @since 1.0
     * @from 1.3.34 add link for tab and popup
     *              remove target parqms
     *              add attr param
     * @from 1.3.26 add $display_name parameter
	 *
	 * @param int    $post_id
	 * @param object $language
	 * @return string
	 */
	public function display_post_translation_link_row( $post_id, $hide_default = true,$display_tab=true,$display_popup=true,$display_name = true) {
        $is_free = Falang()->is_free();
        $languages = Falang()->get_model()->get_languages_list(array('hide_default' => $hide_default));
        $outpout = '';
        foreach ($languages as $language){
            $link_tab = $this->get_post_translation_link( $post_id, $language );
            $language_name = '<span class="post_translation_link_flag">'.$language->get_flag().'</span>';
            $language_name .= $display_name ? esc_html( $language->name ):'';
            $link_tab_html = '<a href="'.$link_tab.'" class="falang_language_link" title="'.__( 'Display in tab', 'falang' ).'" target="_blank"><i class="icon-pop-up"></i></a>';

            $link_popup = wp_nonce_url(admin_url('admin-ajax.php?action=falang_post_translation&post_id='.$post_id.'&language='.$language->locale));
            $link_popup_html = '<a href="'.$link_popup.'" class="falang-thickbox falang_language_link" title="'.__( 'Display in popup', 'falang' ).'"><i class="icon-pop-up"></i></a>';
            if ($is_free){
                $link_popup = admin_url('admin-ajax.php?action=falang_display_static&page=popup_free');
                $link_popup_html = '<a href="'.$link_popup.'" class="thickbox falang_language_link" title="'.__( 'Display in popup', 'falang' ).'"><i class="icon-pop-up"></i></a>';
            }

            if (!$display_tab){
                $link_tab_html='';
            }

            if (!$display_popup){
                $link_popup_html='';
            }

            if ($link_tab || $link_popup ){
                $outpout.=  '<span class="post_translation_link_block">'.$language_name . $link_tab_html . $link_popup_html.'</span>';
            }

        }
        return $outpout;
	}

    public function display_menu_translation_link_row( $post_id, $hide_default = true,$display_tab=true,$display_popup=true) {
        $is_free = Falang()->is_free();
        $languages = Falang()->get_model()->get_languages_list(array('hide_default' => $hide_default));
        $outpout = '';
        foreach ($languages as $language){
            $link_tab = $this->get_post_translation_link( $post_id, $language );
            $language_name = '<span class="menu_translation_link_flag">'.$language->get_flag().'</span>';

            $link_tab_html = '<a href="'.$link_tab.'" class="falang_language_link" title="'.__( 'Display in tab', 'falang' ).'" target="_blank"><i class="icon-pop-up"></i></a>';

            $link_popup = wp_nonce_url(admin_url('admin-ajax.php?action=falang_menu_translation&post_id='.$post_id.'&language='.$language->locale));
            $link_popup_html = '<a href="'.$link_popup.'" class="falang-thickbox falang_language_link" title="'.__( 'Display in popup', 'falang' ).'"><i class="icon-pop-up"></i></a>';
            if ($is_free){
                $link_popup = admin_url('admin-ajax.php?action=falang_display_static&page=popup_free');
                $link_popup_html = '<a href="'.$link_popup.'" class="thickbox falang_language_link" title="'.__( 'Display in popup', 'falang' ).'"><i class="icon-pop-up"></i></a>';
            }

            if (!$display_tab){
                $link_tab_html='';
            }

            if (!$display_popup){
                $link_popup_html='';
            }

            if ($link_tab || $link_popup ){
                $outpout.=  '<span class="menu_translation_link_block">'.$language_name . $link_tab_html . $link_popup_html.'</span>';
            }

        }
        return $outpout;
    }


	/*
	 * Display a table with tab and popup link
	 * Free version display the free popup windows
	 * use fontello
	 * fas fa-external-link-alt => icon-new-tab
	 * fas fa-external-link-square-alt => icon-pop-up
	 *
	 * Since 1.3.34
	 * */
	public function display_post_translation_link_table($post_id,$hide_default = true){
	    $is_free = Falang()->is_free();
	    $languages = Falang()->get_model()->get_languages_list(array('hide_default' => $hide_default));
	    $outpout = '<table>';
	    foreach ($languages as $language){
            $link_tab = $this->get_post_translation_link( $post_id, $language );
            $link_tab_html = '<a href="'.$link_tab.'" class="falang_language_link" title="'.__( 'Display in tab', 'falang' ).'" target="_blank"><i class="icon-new-tab"></i></a>';
            $link_popup = wp_nonce_url(admin_url('admin-ajax.php?action=falang_post_translation&post_id='.$post_id.'&language='.$language->locale));
            $link_popup_html = '<a href="'.$link_popup.'" class="falang-thickbox falang_language_link" title="'.__( 'Display in popup', 'falang' ).'"><i class="icon-pop-up"></i></a>';
            if ($is_free){
                $link_popup = admin_url('admin-ajax.php?action=falang_display_static&page=popup_free');
                $link_popup_html = '<a href="'.$link_popup.'" class="thickbox falang_language_link" title="'.__( 'Display in popup', 'falang' ).'"><i class="icon-pop-up"></i></a>';
            }

            $outpout .= '<tr>';
            $outpout .= '<th>'.$language->get_flag().'</th>';
            $outpout .= '<td>'.esc_html( $language->name ).'</td>';
            $outpout .= '<td>'.$link_tab_html.'</td>';
            $outpout .= '<td>'.$link_popup_html.'</td>';
            $outpout .= '</tr>';
        }
        $outpout .= '</table>';
	    return $outpout;
    }

    /**
     * Returns association page link (not yet post/cpt)
     *
     * @since 1.3.24
     *
     * @param int    $post_id
     * @param object $language
     * @return string
     */
    public function display_post_association_list( $post_id, $language )
    {
        $name = '_falang_assoc_'.$language->locale;

        //get the post
		$selected_post = get_post_meta($post_id,$name,true);

        $dropdown_args = array(
            //'post_type'        => 'page',//not necessary actually only page association
            'selected'         => $selected_post,
            'meta_key'         => '_locale',
            'meta_value'       => $language->locale,
            'name'             => $name,
            'hierarchical'     => false,//to have the post/poge and not only the same level
            'show_option_none' => __( '(no association)' ),
            'sort_column'      => 'post_title',
            'class'			   => 'falang-association',
            'echo'             => 0,
        );

        $output         = wp_dropdown_pages( $dropdown_args );

        if ( empty( $output )) {
        	$output = "<select id=\"".$name."\" name=\"".$name."\" class=\"falang-association\">";
        	$output .= "<option value=\"\">".__( '(no association)' )."</option>";
        	$output .= "</select>";
		}

		if ( ! empty( $output ) ) {
			return $output;
		}

    }




}