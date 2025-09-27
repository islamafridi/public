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
<div class="item is-selected">
    <div class="item_app <?php if($is_mod) echo 'mod'; ?>">
        <div class="img"><img src="<?php the_post_thumbnail_url(); ?>" alt="<?php echo $app_name; ?>" class="blur-up lazyload" loading="lazy"></div>
        <div class="cont">
            <div class="data">
                <div class="name"><a class="truncate-slider" href="<?php the_permalink(); ?>" title="<?php echo $app_name; ?>"><?php echo $app_name; ?></a></div>
            </div>
            <div class="flex items-center truncate toolsbar">
                <div class="flex items-center">
                    <svg class="text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span class="text-xs sm:text-sm text-gray-600 truncate">v<?php echo $app_version; ?></span>
                </div>
                <div class="flex items-center">
                    <svg class="text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v-.228a4.5 4.5 0 0 0-.12-1.03l-2.268-9.64a3.375 3.375 0 0 0-3.285-2.602H7.923a3.375 3.375 0 0 0-3.285 2.602l-2.268 9.64a4.5 4.5 0 0 0-.12 1.03v.228m19.5 0a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3m19.5 0a3 3 0 0 0-3-3H5.25a3 3 0 0 0-3 3m16.5 0h.008v.008h-.008v-.008Zm-3 0h.008v.008h-.008v-.008Z"></path>
                    </svg>
                    <span class="text-xs text-gray-600 truncate"><?php echo $app_size; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
