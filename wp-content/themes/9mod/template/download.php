<?php 
if (!defined('ABSPATH')) exit;
add_action('wp_head', function () {
    echo '<meta name="robots" content="noindex, nofollow">';
}, 1);

get_header();
?>
<div class="page_file">
	<div class="wrp">
		<?php get_template_part('components/download/download-header'); 
			  get_template_part('components/download/download-content'); 
		?>
	</div>
	<?php get_template_part('components/download/download-related-posts'); ?>
</div>
<?php get_template_part('components/download/download-footer'); ?>