<?php
if (!defined('ABSPATH')) exit;

$logo_url = get_theme_mod('logo_img', get_template_directory_uri() . '/assets/img/logo.svg');
?>
<div class="page_file-h dw-header">
	<a class="btn-back" href="<?php echo rtrim(get_permalink(), '/') . '/downloads/'; ?>">
		<svg class="i__arrowleft" viewBox="0 0 24 24" width="24" height="24">
			<path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z" fill="currentColor"></path>
		</svg>
	</a>
	<a class="logo" href="<?php echo get_site_url(); ?>" title="<?php echo get_bloginfo('name'); ?>">
		<img src="<?php echo $logo_url; ?>" width="128" height="64">
	</a>
</div>