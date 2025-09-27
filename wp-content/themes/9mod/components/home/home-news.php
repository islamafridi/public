<?php
if (!defined('ABSPATH')) exit;
$is_home_news = get_theme_mod('home_news_swt', false);
$news_title = get_theme_mod('home_news_title', 'Latest News');
$home_news_limit = get_theme_mod('home_news_limit', '4');
$hn_btm_ad_swt = get_theme_mod('hn_btm_ad_swt', false);

$news_args = array(
    'post_type' => 'news',
    'orderby' => 'modified',
    'posts_per_page' => $home_news_limit
);
$news = new WP_Query($news_args);

if ($is_home_news && $news->have_posts()) : ?>
<div class="section">
	<div class="section-title fbold"><?php echo $news_title; ?></div>
	<div class="last-news">
		<?php 
		while($news->have_posts()) : $news->the_post();
			get_template_part('components/post-card/card-1'); 
		endwhile;
		wp_reset_postdata();
		?>
	</div>
</div>
<?php endif; 

if ($hn_btm_ad_swt && function_exists('home_bottom_ad')) {
    home_bottom_ad();
}
?>