<?php
if (!defined('ABSPATH')) exit;
$app_name = get_post_meta(get_the_ID(), 'wp_title_GP', true);
$post_categories = get_the_category(get_the_ID());
$download_recommend_swt = get_theme_mod('download_recommend_swt', false);
$download_recommend_post_limit = get_theme_mod('download_recommend_post_limit', '6');
$download_random_posts_swt = get_theme_mod('download_random_posts_swt', false);
$download_random_posts_limit = get_theme_mod('download_random_posts_limit', '8');

$parent_cat_id = null;
$child_cat_id = null;

foreach ($post_categories as $cat) {
    if ($cat->category_parent == 0) {
        $parent_cat_id = $cat->term_id;
        break;
    }
}

$child_cats = get_categories(array('parent' => $parent_cat_id, 'hide_empty' => false));
foreach ($child_cats as $child_cat) {
	if (in_category($child_cat->term_id)) {
		$child_cat_id = $child_cat->term_id;
		break;
	}
}

$parent_args = array(
	'cat' => $parent_cat_id,
	'orderby' => 'rand',
	'order' => 'DESC',
	'posts_per_page'=> $download_recommend_post_limit,
	'post__not_in' => array( get_the_ID() )
);

$child_args = array(
	'cat' => $child_cat_id,
	'orderby' => 'modified',
	'order' => 'DESC',
	'posts_per_page'=> $download_random_posts_limit,
	'post__not_in' => array( get_the_ID() )
);

$parent_cat = new WP_Query( $parent_args );
$child_cat = new WP_Query( $child_args );
?>
<div class="file-addons">
	<?php if($download_recommend_swt && $child_cat_id && $child_cat->have_posts()) : ?>
    <h3 class="title"><?php echo get_cat_name($child_cat_id); ?> <?php _e('similar to', 'apktemplates'); ?> <?php echo $app_name; ?>:</h3>
    <div class="card-grid">
        <?php 
		while($child_cat->have_posts()) : $child_cat->the_post();
			get_template_part('components/post-card/download-box'); 
		endwhile;
		wp_reset_postdata();
		?>
    </div>
	<?php endif; if($download_random_posts_swt && $parent_cat_id && $parent_cat->have_posts()) : ?>
    <h3 class="title"><?php _e('Random', 'apktemplates'); ?> <?php echo get_cat_name($parent_cat_id); ?>:</h3>
  <div class="card-grid">
        <?php 
		while($parent_cat->have_posts()) : $parent_cat->the_post();
			get_template_part('components/post-card/download-box'); 
		endwhile;
		wp_reset_postdata();
		?>
    </div>
	<?php endif; ?>
</div>