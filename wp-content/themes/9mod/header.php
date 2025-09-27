<?php
if (!defined('ABSPATH')) exit;
$url = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> id="html" <?php if (strpos($url, '/download/') === false && strpos($url, '/downloads/') === false) echo 'class="ap-open hfix tb_on"'; ?> dir="ltr">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="HandheldFriendly" content="true">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=5.0, width=device-width">
	<?php wp_head(); set_post_views(get_the_ID()); ?>
<?php
	get_template_part('assets/css/theme-color-css');
?>
</head>
	<body class="">
		<?php if (strpos($url, '/download/') == false) get_template_part('template/header'); ?>