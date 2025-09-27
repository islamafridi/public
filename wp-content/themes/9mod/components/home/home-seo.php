<?php
if (!defined('ABSPATH')) exit;
$seo_title = get_theme_mod('home_seo_title', 'Download free games for Android');
$seo_description = get_theme_mod('home_seo_description', 'In this website, you can download latest games and apps for free without virus free. Why waiting, just search and download your favorite games and apps for free.');

if(!empty($seo_title) || !empty($seo_description)) :
?>
<div class="wrp">
	<div class="section seo-section">
		<h1 class="section-title fbold"><?php // echo $seo_title; ?></h1>
		<div class="seo-text"><?php // echo $seo_description; ?></div>
		<h1 class="section-title fbold"><?php theme_translate($seo_title, 'page_seo_title'); ?></h1>
		<div class="seo-text"><?php theme_translate($seo_description, 'page_seo_description'); ?></div>
	</div>
</div>
<?php endif; ?>