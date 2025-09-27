<?php
if (!defined('ABSPATH')) exit;
global $wp_query;
$current_category = get_queried_object();
// Get the Sub categories
$child_cats = get_categories(array('parent' => $current_category->term_id, 'hide_empty' => false));

$custom_archive_id = get_theme_mod('custom_cat');

$is_custom_archive = false;
if($current_category->term_id == $custom_archive_id) $is_custom_archive = true;

$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$total_pages = $wp_query->max_num_pages;
?>
<div class="page">
    <div class="wrp">
		<div class="heading">
			<?php if(!$is_custom_archive) : ?>
				<h1 class="fbold title xxxlgf"><?php the_archive_title(); ?></h1>
			<?php endif; ?>
		</div>
		
			<?php if (is_category() && category_description()) : ?>
				<div class="category-description">
					<?php echo category_description(); ?>
				</div>
			<?php endif; ?>
	
        <div class="content">
			<?php if(function_exists('archive_top_ad')) archive_top_ad(); ?>
			<div class="section section-sep">
             <div class="card-grid">
				<?php
				if(have_posts()) :
					while(have_posts()) : the_post();
						get_template_part('components/post-card/box-2');
					endwhile;
					wp_reset_postdata();
				endif;
				
				?>
            </div>
			</div>
			<div class="pagination">
				<?php apktemplates_pagination($paged, $total_pages); ?>
			</div>

			<?php if(function_exists('archive_bottom_ad') ) archive_bottom_ad(); ?>
        </div>
    </div>
</div>