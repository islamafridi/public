<?php
/* Box for Home Page Posts */
$app_name = get_post_meta(get_the_ID(), 'wp_title_GP', true);
$app_name = $app_name ?: get_the_title();
$app_size = get_post_meta(get_the_ID(), 'wp_sizes_GP', true);
$app_mod_feature = get_post_meta(get_the_ID(), 'wp_mods', true);
$is_mod = get_post_meta(get_the_ID(), 'mod-tick-box', true);
$rating_data = apkt_get_star_rating(get_the_ID());
$app_version = get_post_meta(get_the_ID(), 'wp_version_GP', true);

?>

<div class="swiper-slide swiper-slide-active" data-swiper-slide-index="0"> 
	<a href="<?php the_permalink(); ?>" class="blocks relative h-60 group rounded-xl overflow-hidden shadow-md"> 
		<img src="<?php the_post_thumbnail_url(); ?>" alt="<?php echo $app_name; ?>" loading="lazy" decoding="async">
		<span style="position: absolute;top: 10px;right: 10px;background-color: rgba(242, 68, 55, 0.8);color: white;padding: 5px;border-radius: 10px;font-size: 11px;">v<?php echo $app_version; ?></span>
		<div class="absolute"></div>
		<div class="absolute bottom-0 left-0 right-0 p-6 text-white z-10" style="width: 100%;">
			<h3 class="text-xl truncate drop-shadow-md"><?php echo $app_name; ?></h3>
			<p class="text-sm drop-shadow-sm"><?php echo $app_mod_feature; ?></p> 
			<button class="bg-green-600"> 
				GET <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg> 
			</button>
		</div>
	</a>
</div>
