<?php
if (!defined('ABSPATH')) exit;
function breadcrumbsX() {
	$url = $_SERVER['REQUEST_URI'];
	$post_id = get_the_ID();
	$app_name = get_post_meta($post_id, 'wp_title_GP', true);
	$app_name = $app_name ?: get_the_title();
	$text = [
		'home' => theme_get_translate('Home', 'breadcrumb_home'),
		'page' => theme_get_translate('Page %s', 'breadcrumb_page'),
		'search' => theme_get_translate('You searched for %s', 'breadcrumb_search'),
		'404' => theme_get_translate('Error 404', 'breadcrumb_404')
	];
	$home_url = home_url('/');
	$is_current_page = true;

	if ((is_singular('post') && strpos($url, '/download/') === false) || (apkt_is_custom_post_type() && !is_post_type_archive())) {
		echo '<ul class="catbar uppercase" itemscope="" itemtype="https://schema.org/BreadcrumbList">';

		echo '<li itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem"><meta itemprop="position" content="1"><a href="' . esc_url($home_url) . '" itemprop="item"><span itemprop="name">' . esc_html($text['home']) . '</span></a></li>';

		$parent_cat_id = null;
		$post_categories = get_the_category();
		if (!empty($post_categories)) {
			$position = 2;
			foreach ($post_categories as $index => $cat) {
				if ($cat->parent == 0) {
					$parent_cat_id = $cat->term_id;
					$cat_url = get_category_link($cat->term_id);
					echo '<li itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem"><meta itemprop="position" content="' . esc_attr($position) . '"><a href="' . esc_url($cat_url) . '" itemprop="item"><span itemprop="name">' . esc_html($cat->name) . '</span></a></li>';
					$position++;
				}
			}

			if(isset($parent_cat_id)) {
				$child_categories = get_categories( array( 'parent' => $parent_cat_id ) );
			
				if(!empty($child_categories)) {
					foreach($child_categories as $cat) {
						if(in_category($cat->term_id)) {
							$cat_url = get_category_link($cat->term_id);
							echo '<li itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem"><meta itemprop="position" content="' . esc_attr($position) . '"><a href="' . esc_url($cat_url) . '" itemprop="item"><span itemprop="name">' . esc_html($cat->name) . '</span></a></li>'; 
						}
					}
				}
			}
		}

if (apkt_is_custom_post_type() && !is_post_type_archive()) {
    $post_type = get_post_type();
    $cpt = get_post_type_object($post_type);

    if ($cpt && isset($cpt->labels->name)) {
        $cpt_archive_url = get_post_type_archive_link($post_type);
        $position = 2;
        echo '<li itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">';
        echo '<meta itemprop="position" content="' . esc_attr($position) . '">';
        echo '<a href="' . esc_url($cpt_archive_url) . '" itemprop="item">';
        echo '<span itemprop="name">' . esc_html($cpt->labels->name) . '</span>';
        echo '</a></li>';
    }
}


		echo '</ul>';

	} else {
		echo '<span id="dle-speedbar"><span itemscope itemtype="https://schema.org/BreadcrumbList">';

		echo '<span itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem"><meta itemprop="position" content="1"><a href="' . esc_url($home_url) . '" itemprop="item"><span itemprop="name">' . esc_html($text['home']) . '</span></a></span>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;';

		if (is_archive() && !is_post_type_archive()) {
			$paged = get_query_var('paged');
			$current_term = get_queried_object();
			$archive_ancestors = get_ancestors($current_term->term_id, $current_term->taxonomy);
			$archive_ancestors = array_reverse($archive_ancestors);
			$position = 2;
			foreach ($archive_ancestors as $index => $ancestor_id) {
				$ancestor = get_term($ancestor_id, $current_term->taxonomy);
				echo '<span itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem"><meta itemprop="position" content="' . esc_attr($position) . '"><a href="' . esc_url(get_term_link($ancestor)) . '" itemprop="item"><span itemprop="name">' . esc_html($ancestor->name) . '</span></a></span>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;';
				$position++;
			}

			if($paged) {
				$term = get_term($current_term->term_id, $current_term->taxonomy);
				echo '<span itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem"><meta itemprop="position" content="' . esc_attr($position) . '"><a href="' . esc_url(get_term_link($term)) . '" itemprop="item"><span itemprop="name">' . esc_html($term->name) . '</span></a></span>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;';
				if($is_current_page) echo ' ' . sprintf($text['page'], $paged);
				
			} else {
				if($is_current_page) echo ' ' . esc_html($current_term->name);;
			}
		} elseif (is_post_type_archive()) {
			$paged = get_query_var('paged');
			$post_type = get_post_type();
			$cpt = get_post_type_object($post_type);
			$cpt_archive_url = get_post_type_archive_link($post_type);
			$position = 2;

if ( isset($cpt) && isset($cpt->labels->name) ) {
	if ($paged) {
		echo '<span itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">';
		echo '<meta itemprop="position" content="' . esc_attr($position) . '">';
		echo '<a href="' . esc_url($cpt_archive_url) . '" itemprop="item">';
		echo '<span itemprop="name">' . esc_html($cpt->labels->name) . '</span>';
		echo '</a></span>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;';
		if ($is_current_page) echo ' ' . sprintf($text['page'], $paged);
	} else {
		if ($is_current_page) echo ' ' . esc_html($cpt->labels->name);
	}
}

		} elseif (is_search()) {
			$paged = get_query_var('paged');
			$search_query = get_search_query();
			$position = 2;
			
			if($paged) {
				echo '<span itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem"><meta itemprop="position" content="' . esc_attr($position) . '"><a href="' . esc_url($home_url . '?s=' . $search_query) . '" itemprop="item"><span itemprop="name">' . sprintf( $text['search'], $search_query ) . '</span></a></span>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;';
				if($is_current_page) echo ' ' . sprintf($text['page'], $paged);
			} else {
				if($is_current_page) echo ' ' . sprintf($text['search'], $search_query);
			}
		} elseif( is_page() ) {
			$page_parent_id = wp_get_post_parent_id();
			
			if(!empty($page_parent_id)) {
				$page_ancestors = get_post_ancestors($post_id);
				$page_ancestors = array_reverse($page_ancestors);
				$position = 2;
				foreach ($page_ancestors as $ancestor_id) {
					$ancestor_title = get_the_title($ancestor_id);
					$ancestor_link = get_permalink($ancestor_id);
					echo '<span itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem"><meta itemprop="position" content="' . esc_attr($position) . '"><a href="' . esc_url($ancestor_link) . '" itemprop="item"><span itemprop="name">' . esc_html($ancestor_title) . '</span></a></span>&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;';
					$position++;
				}
				
				if($is_current_page) echo ' ' . get_the_title();
			} else {
				if($is_current_page) echo ' ' . get_the_title();
			}

		} elseif( is_404() ) {
			if($is_current_page) echo ' ' . esc_html($text['404']);
		}

		echo '</span></span>';
	}
}