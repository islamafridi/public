<?php
if (!defined('ABSPATH')) exit;
get_header();

global $wp_query;
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$total_pages = $wp_query->max_num_pages;

$articles_args = array(
	'post_type' => 'articles',
	'orderby' => 'modified',
	'posts_per_page' => 4
);

$articles = new WP_Query( $articles_args );

if( $articles->have_posts() ) :
?>
<div class="card_row" style="margin-bottom: 0;">
	<?php
	while($articles->have_posts()) : $articles->the_post();
		get_template_part('components/post-card/card-3');
	endwhile;
	wp_reset_postdata();
	?>
</div>
<?php endif; ?>
<div class="wrp">
<div class="toolbar">
	<div class="breadcrumbs xsmf">
		<?php breadcrumbsX(); ?>
	</div>
</div>
</div>
<div class="page">
	<div class="wrp">
		<div class="heading">
			<h1 class="fbold title xxxlgf">
			<?php 
				$archive_title = get_the_archive_title();
				$archive_title_key = 'archive_title_' . sanitize_title(strip_tags($archive_title));
				echo theme_translate($archive_title, $archive_title_key);
			?>

			</h1>
		</div>
		<div class="colomns">
			<div class="content">
				<?php if(function_exists('archive_top_ad') ) archive_top_ad(); if( have_posts() ) : ?>
				<div class="post_list">
					<?php
					while ( have_posts() ) : the_post();
						get_template_part('components/post-card/card-2');
					endwhile; 
					wp_reset_postdata();
					
					apktemplates_pagination($paged, $total_pages); ?>
				</div>
				<?php endif; if(function_exists('archive_bottom_ad') ) archive_bottom_ad(); ?>
			</div>
			<aside class="sidebar">
				<div class="block sticky">
				</div>
			</aside>
		</div>
	</div>
</div>
<?php get_template_part('template/footer'); get_footer(); ?>