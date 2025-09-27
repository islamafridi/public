<?php
if (!defined('ABSPATH')) exit;
function apkt_meme_types( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['apk']  = 'application/vnd.android.package-archive';
    $mimes['apks'] = 'application/vnd.android.package-archive';
    $mimes['xapk'] = 'application/vnd.android.package-archive';
    return $mimes;
}
add_filter( 'upload_mimes', 'apkt_meme_types' );

function set_post_views($post_id){
	$count_key = 'wpb_post_views_count';
	$count = get_post_meta($post_id, $count_key, true);
	if ($count == '') {
		$count = 0;
		delete_post_meta($post_id, $count_key);
		add_post_meta($post_id, $count_key, '0');
	} else {
		$count++;
		update_post_meta($post_id, $count_key, $count);
	}
}
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);

function track_post_views($post_id){
	if (!is_single())
		return;
	if (empty($post_id)) {
		global $post;
		$post_id = $post->ID;
	}
	set_post_views($post_id);
}
add_action('wp_head', 'track_post_views');

function get_post_views($post_id){
	$count_key = 'wpb_post_views_count';
	$count = get_post_meta($post_id, $count_key, true);
	if ($count == '') {
		delete_post_meta($post_id, $count_key);
		add_post_meta($post_id, $count_key, '0');
		return "0";
	}
	return $count;
}




function apkt_save_post_rating() {
	$response = array();
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $rating_count = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if ($post_id <= 0 || $rating_count <= 0) {
        echo json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Invalid input!',
            ],
        ]);
		exit;
    }

    if (!wp_verify_nonce($nonce, 'star_rating_nonce')) {
        echo json_encode([
            'status' => 'error',
            'data' => [
                'message' => 'Khatam! Tata! Good Bye!',
            ],
        ]);
		exit;
    }

    if (apkt_is_new_rating()) {
        $current_avg_rating = (float) get_post_meta($post_id, 'avg_rating', true);
        $current_votes = (int) get_post_meta($post_id, 'total_votes', true);
        if (!empty($current_avg_rating) && $current_avg_rating != 0) {
            $total_rating = round($current_avg_rating * $current_votes);
            update_post_meta($post_id, 'total_rating', $total_rating);
        }

        $current_rating = (int) get_post_meta($post_id, 'total_rating', true);

        $new_rating = $current_rating + $rating_count;
        $new_votes = $current_votes + 1;

        update_post_meta($post_id, 'total_rating', $new_rating);
        update_post_meta($post_id, 'total_votes', $new_votes);
        update_post_meta($post_id, 'avg_rating', number_format($new_rating / $new_votes, 1, '.', ''));

        $avg_rating = sprintf("%.1f", $new_rating / $new_votes);
        $avg_rating = rtrim($avg_rating, '0');
        $avg_rating = rtrim($avg_rating, '.');

        $total_votes = number_format($new_votes, 0, ',', ',');

        if (!isset($_COOKIE['new_rating'])) {
            setcookie("new_rating", $post_id, time() + (24 * 365), "/");
        } else {
            $new_rating_cookie = explode(",", $_COOKIE['new_rating']);
            $new_rating_cookie[] = $post_id;
            setcookie("new_rating", implode(",", $new_rating_cookie), time() + (24 * 365), "/");
        }

        $response = [
            'status' => 'success',
            'data' => [
                'new_rating' => $avg_rating,
                'new_votes' => $total_votes
            ],
        ];
    } else {
        $response = [
            'status' => 'error',
            'data' => [
                'message' => 'You already voted this article!'
            ],
        ];
    }
    
    if (function_exists('w3tc_flush_post')) {
        w3tc_flush_post($post_id);
    }
    if (function_exists('wp_cache_post_change')) {
        wp_cache_post_change($post_id);
    }
    if (function_exists('wpfc_clear_post_cache_by_id')) {
        wpfc_clear_post_cache_by_id($post_id);
    }
    if (defined('LSCWP_V') && function_exists('do_action')) {
        do_action('litespeed_purge_post', $post_id);
    }
    if (function_exists('rocket_clean_post')) {
        rocket_clean_post($post_id);
    }

	echo json_encode($response);
	exit;
}

add_action('wp_ajax_apkt_save_post_rating', 'apkt_save_post_rating');
add_action('wp_ajax_nopriv_apkt_save_post_rating', 'apkt_save_post_rating');

function apkt_get_star_rating($post_id){
    $array = array();
    $total_rating = ( get_post_meta( $post_id, 'total_rating', true ) ) ? get_post_meta( $post_id, 'total_rating', true ) : 0;
    $total_votes = ( get_post_meta( $post_id, 'total_votes', true ) ) ? get_post_meta( $post_id, 'total_votes', true ) : 0;
    $avg_rating = ( get_post_meta( $post_id, 'avg_rating', true ) ) ? get_post_meta( $post_id, 'avg_rating', true ) : 0;
    $star_width = ($avg_rating > 0) ? strval(($avg_rating * 20) . '%') : '0%';

    if ($avg_rating == 0 && $total_votes > 0) {
        $nar = round($total_rating / $total_votes, 1);
        update_post_meta( $post_id, 'avg_rating', $nar);
    }

    $array['rating_total'] = $total_rating;
    $array['rating_votes'] =  $total_votes;
    $array['rating_average'] =  $avg_rating;
    $array['rating_width'] = $star_width;

    return (object) $array;
}

function apkt_is_new_rating() {
	$post_id = get_the_ID();
    if( !isset($_COOKIE['new_rating']) || !isset($post_id) )  return true;
    $new_rating = explode(",", $_COOKIE['new_rating']);
    return !in_array($post_id, $new_rating);
}

function modify_search_query($query){
	if (!is_admin() && $query->is_main_query() && $query->is_search()) {
		$query->set('post_type', 'post');
	}
}
add_action('pre_get_posts', 'modify_search_query');

function apkt_get_svg_icon($icon_name){
	if($icon_name === 'home'){
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect fill="none" width="24" height="24"></rect><path fill="currentColor" class="icon_lightcolor" d="M12,2.8L2,12.2V19h20v-6.8L12,2.8 M12,0.1l12,11.2V21H0v-9.7L12,0.1z"></path><circle fill="currentColor" cx="15" cy="19" r="5"></circle></svg>';
	} elseif($icon_name === 'gamepad'){
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect fill="none" width="24" height="24"></rect><circle fill="currentColor" cx="8" cy="16" r="3"></circle><path fill="currentColor" class="icon_lightcolor" d="M16,8h-2V7c0-1.7-1.3-3-3-3H8C7.4,4,7,3.6,7,3s0.4-1,1-1h10V0H8C6.3,0,5,1.3,5,3s1.3,3,3,3h3c0.6,0,1,0.4,1,1v1H8c-4.4,0-8,3.6-8,8s3.6,8,8,8h8c4.4,0,8-3.6,8-8S20.4,8,16,8z M16,22H8c-3.3,0-6-2.7-6-6s2.7-6,6-6h8c3.3,0,6,2.7,6,6S19.3,22,16,22z"></path></svg>';
	} elseif($icon_name === 'apps'){
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect fill="none" width="24" height="24"></rect><path fill="currentColor" d="M14,0h8c1.1,0,2,0.9,2,2v8c0,1.1-0.9,2-2,2h-8c-1.1,0-2-0.9-2-2V2C12,0.9,12.9,0,14,0z"></path><path fill="currentColor" class="icon_lightcolor" d="M10,14V6H2.8C1.2,6,0,7.2,0,8.8v12.4C0,22.8,1.2,24,2.8,24h12.4c1.5,0,2.8-1.2,2.8-2.8V14H10zM2.8,8H8v6H2V8.8C2,8.3,2.3,8,2.8,8z M2.8,22C2.3,22,2,21.7,2,21.2V16h6v6H2.8z M15.2,22H10v-6h6v5.2C16,21.7,15.7,22,15.2,22z"></path></svg>';
	} elseif($icon_name === 'news'){
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect fill="none" width="24" height="24"></rect><path fill="currentColor" class="icon_lightcolor" d="M10,4.8L6.6,8.2c-1.4,1.4-2.3,3.2-2.5,5.2c-0.2,2,0.3,3.9,1.5,5.4c1.5,2,3.9,3.2,6.4,3.2c3,0,5.6-1.6,7.1-4.4c0.9-1.6,1.1-3.5,0.7-5.2C18.9,12.8,17.9,13,17,13c-3.9,0-7-3.1-7-7V4.8 M11.9,0C11.9,0,12,0,11.9,0l0,6c0,2.8,2.2,5,5,5c1.5,0,2.8-0.7,3.7-1.7c1.6,2.9,1.7,6.4,0.1,9.3C18.9,22.2,15.4,24,12,24c-3,0-6-1.3-8-4c-3-4-2.4-9.7,1.2-13.2L11.9,0C11.9,0,11.9,0,11.9,0z"></path><path fill="currentColor" d="M7.9,11.5c-1.4,2.3-0.8,5.3,1.5,6.8c2.2,1.4,5.1,0.8,6.6-1.3C12.5,16.8,9.3,14.7,7.9,11.5z"></path></svg>';
	} elseif($icon_name === 'articles'){
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="0" fill="none" width="24" height="24"></rect><path fill="currentColor" class="icon_lightcolor" d="M22,5.2V4.7c0-2-1.7-3.7-3.7-3.7H5.7C3.7,1,2,2.7,2,4.7v0.5C0.8,5.6,0,6.7,0,8v12c0,1.7,1.3,3,3,3h18c1.7,0,3-1.3,3-3V8C24,6.7,23.2,5.6,22,5.2z M5.7,3h12.6C19.2,3,20,3.8,20,4.7V5H4V4.7C4,3.8,4.8,3,5.7,3z M22,20c0,0.6-0.4,1-1,1H3c-0.6,0-1-0.4-1-1V8c0-0.6,0.4-1,1-1h18c0.6,0,1,0.4,1,1V20z"></path><path fill="currentColor" d="M6,10h12c0.6,0,1,0.4,1,1l0,0c0,0.6-0.4,1-1,1H6c-0.6,0-1-0.4-1-1l0,0C5,10.4,5.4,10,6,10z"></path><path fill="currentColor" d="M5.9,14h7.1c0.5,0,0.9,0.4,0.9,0.9v0.1c0,0.5-0.4,0.9-0.9,0.9H5.9C5.4,16,5,15.6,5,15.1v-0.1C5,14.4,5.4,14,5.9,14z"></path></svg>';
	} elseif($icon_name === 'faq'){
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect y="0" width="24" height="24" fill="none"></rect><circle fill="currentColor" cx="5" cy="9" r="1"></circle><circle fill="currentColor" cx="9" cy="9" r="1"></circle><circle fill="currentColor" cx="13" cy="9" r="1"></circle><path fill="currentColor" class="icon_lightcolor" d="M17.6,6.4C16.5,2.7,13.1,0,9,0C4,0,0,4,0,9c0,3.9,2.5,7.2,6,8.5V24h9c5,0,9-4,9-9C24,11,21.4,7.5,17.6,6.4zM16,16H9c-3.9,0-7-3.1-7-7s3.1-7,7-7s7,3.1,7,7V16z"></path></svg>';
	} else{
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect fill="none" width="24" height="24"></rect><path fill="currentColor" class="icon_lightcolor" d="M12,2.8L2,12.2V19h20v-6.8L12,2.8 M12,0.1l12,11.2V21H0v-9.7L12,0.1z"></path><circle fill="currentColor" cx="15" cy="19" r="5"></circle></svg>';
	}
}

function apkt_menu_icon_field( $item_id, $item ) {
    $menu_icon = get_post_meta( $item_id, 'menu_icon', true );
    ?>
    <div class="description description-wide">
        <label for="menu_icon-<?php echo $item_id ;?>"><?php _e( "Menu Icon", 'apktemplates' ); ?></label>
        <input type="hidden" class="nav-menu-id" value="<?php echo $item_id ;?>" />
        <div class="logged-input-holder">
            <select name="menu_icon[<?php echo $item_id ;?>]" id="menu_icon-<?php echo $item_id ;?>" class="widefat edit-menu-item-title" value="<?php echo $menu_icon; ?>">
                <option value="home" <?php selected( $menu_icon, 'home' ); ?>><?php _e( 'Home', 'apktemplates' ); ?></option>
                <option value="gamepad" <?php selected( $menu_icon, 'gamepad' ); ?>><?php _e( 'Gamepad', 'apktemplates' ); ?></option>
                <option value="apps" <?php selected( $menu_icon, 'apps' ); ?>><?php _e( 'Apps', 'apktemplates' ); ?></option>
				<option value="news" <?php selected( $menu_icon, 'news' ); ?>><?php _e( 'News', 'apktemplates' ); ?></option>
                <option value="articles" <?php selected( $menu_icon, 'articles' ); ?>><?php _e( 'Articles', 'apktemplates' ); ?></option>
                <option value="faq" <?php selected( $menu_icon, 'faq' ); ?>><?php _e( 'FAQ', 'apktemplates' ); ?></option>
            </select>
        </div>
    </div>
    <?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'apkt_menu_icon_field', 10, 2 );

function apkt_save_menu_icon( $menu_id, $menu_item_db_id ) {
    if ( isset( $_POST['menu_icon'][$menu_item_db_id] ) ) {
        $sanitized_data = sanitize_text_field( $_POST['menu_icon'][$menu_item_db_id] );
        update_post_meta( $menu_item_db_id, 'menu_icon', $sanitized_data );
    } else {
        delete_post_meta( $menu_item_db_id, 'menu_icon' );
    }
}
add_action( 'wp_update_nav_menu_item', 'apkt_save_menu_icon', 10, 2 );

if( class_exists( 'WPSEO_Options' ) ){
    function wp_version_GP() {
		global $post;
		$app_version = get_post_meta(get_the_ID(), 'wp_version_GP', true);
		return $app_version;
	}
	
	function register_custom_yoast_variables() {
		wpseo_register_var_replacement( '%%wp_version_GP%%', 'wp_version_GP', 'advanced' );
	}
	
	add_action('wpseo_register_extra_replacements', 'register_custom_yoast_variables');
}

if (class_exists('RankMath')) {
	add_action( 'rank_math/vars/register_extra_replacements', function(){
		rank_math_register_var_replacement(
			'wp_version_GP',
			[
				'name'        => esc_html__( 'App Version', 'apktemplates' ),
				'description' => esc_html__( 'Display App version.', 'apktemplates' ),
				'variable'    => 'wp_version_GP',
				'example'     => version_callback(),
			],
			'version_callback'
		);
	});
    function version_callback(){
        $app_version = get_post_meta(get_the_ID(), 'wp_version_GP', true);
		if(!empty($app_version)) {
			return $app_version;
		} else {
			return '[Version]';
		}
    }
}

function apkt_archive_posts_sort($query){
	$archive_post_limit = get_theme_mod('archive_posts_limit', 12);
	$archive_sort_order = get_theme_mod('archive_sort', 'latest');

	if ( !is_admin() && ( is_archive() || is_post_type_archive() ) && $query->is_main_query() ) {
		switch ($archive_sort_order) {
			case 'latest':
				$query->set('orderby', 'date');
				$query->set('order', 'DESC');
				break;
			case 'modified':
				$query->set('orderby', 'modified');
				$query->set('order', 'DESC');
				break;
			case 'popular':
				$query->set('meta_key', 'wpb_post_views_count');
				$query->set('orderby', 'meta_value_num');
				$query->set('order', 'DESC');
				break;
			case 'a_to_z':
				$query->set('orderby', 'title');
				$query->set('order', 'ASC');
				break;
			case 'z_to_a':
				$query->set('orderby', 'title');
				$query->set('order', 'DESC');
				break;
			default:
				$query->set('orderby', 'date');
				$query->set('order', 'DESC');
				break;
		}
		$query->set('posts_per_page', $archive_post_limit);
	}

	if ($query->is_search && !is_admin()) {
		$query->set('post_type', 'post');
	}
}
add_action('pre_get_posts', 'apkt_archive_posts_sort');

function apkt_is_custom_post_type( $post = NULL ) {
    $all_custom_post_types = get_post_types( array ( '_builtin' => FALSE ) );

    if ( empty ( $all_custom_post_types ) )
        return FALSE;

    $custom_types      = array_keys( $all_custom_post_types );
    $current_post_type = get_post_type( $post );

    if ( ! $current_post_type )
        return FALSE;

    return in_array( $current_post_type, $custom_types );
}

function apkt_is_category_or_tag($id) {
    if (empty($id)) {
        return false;
    }

    $category = get_term($id, 'category');
    if ($category && !is_wp_error($category)) {
        return true;
    }

    $tag = get_term($id, 'post_tag');
    if ($tag && !is_wp_error($tag)) {
        return true;
    }

    return false;
}

// Vegam in wp
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_null' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

add_filter('wp_resource_hints', function (array $urls, string $relation): array {
    if ($relation !== 'dns-prefetch') {
        return $urls;
    }
    $urls = array_filter($urls, function (string $url): bool {
        return strpos($url, 's.w.org') === false;
    });
    return $urls;
}, 10, 2);

remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0 );
remove_filter('wp_robots', 'wp_robots_max_image_preview_large');

function apkt_head_code() {
    $head_code = get_theme_mod('head_code');
    if (!empty($head_code)) {
        echo $head_code;
    }
}
add_action('wp_head', 'apkt_head_code');

function apkt_footer_code() {
    $footer_code = get_theme_mod('footer_code');
    if (!empty($footer_code)) {
        echo $footer_code;
    }
}
add_action('wp_footer', 'apkt_footer_code');