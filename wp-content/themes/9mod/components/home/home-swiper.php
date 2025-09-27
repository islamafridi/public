<?php
// Query for 8 most viewed posts
$post_args = [
	'orderby' => 'meta_value_num',
	'meta_key' => 'smt_post_views_count',
	'order' => 'DESC',
	'posts_per_page' => 6,
];
$most_viewed_posts = new WP_Query($post_args);

if ($most_viewed_posts->have_posts()) :
?>

	<div class="swiper-container">
		<div class="swiper mySwiper">
			<div class="swiper-wrapper">
				<?php
				while ($most_viewed_posts->have_posts()) : $most_viewed_posts->the_post();
					get_template_part('components/post-card/card-slider');
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<!-- Navigation -->
			<div class="swiper-button-next"></div>
			<div class="swiper-button-prev"></div>
			<!-- Pagination -->
			<div class="swiper-pagination"></div>
		</div>
	</div>

<?php endif; ?>
