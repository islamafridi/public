<?php
if (!defined('ABSPATH')) exit;
$is_home_articles = get_theme_mod('home_articles_swt', false);
$articles_title = get_theme_mod('home_articles_title', 'Latest Articles');
$home_articles_limit = get_theme_mod('home_articles_limit', '4');
$ha_btm_ad_swt = get_theme_mod('ha_btm_ad_swt', false);

$articles_args = array(
    'post_type' => 'articles',
    'orderby' => 'modified',
    'posts_per_page' => $home_articles_limit
);
$articles = new WP_Query($articles_args);
?>
<div class="wrp">
	<?php if ($is_home_articles && $articles->have_posts()) : ?>
    <div class="section">
        <div class="section-title fbold"><?php echo $articles_title; ?></div>
        <div class="last-news">
			<?php 
			while($articles->have_posts()) : $articles->the_post();
				get_template_part('components/post-card/card-1'); 
			endwhile;
			wp_reset_postdata();
			?>
        </div>
    </div>
	<?php if ($ha_btm_ad_swt && function_exists('home_bottom_ad')) home_bottom_ad(); ?>
	<?php endif; get_template_part('components/home/home-news'); ?>
</div>