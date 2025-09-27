<?php
if (!defined('ABSPATH')) exit;
$app_name = get_post_meta(get_the_ID(), 'wp_title_GP', true);
$app_name = $app_name ?: get_the_title();
$screenshots = get_post_meta(get_the_ID(), 'ss_images', true);
$is_screenshots = get_theme_mod('sp_screenshot_swt', true);
$screenshots_limit = get_theme_mod('screenshots_limit', 5);

if($is_screenshots && isset($screenshots) && !empty($screenshots)) :
?>
<div class="app_screens">
    <div class="sep_line"></div>
    
    <div class="app_screens_in">
        <div class="app_screens_list">
			<?php
			foreach($screenshots as $index => $screenshot) :
				if($index + 1 > $screenshots_limit) break; 
			?>
            <a data-fancybox="gallery" href="<?php echo $screenshot['ss_url']; ?>" target="_blank">
                <meta itemprop="screenshot" content="<?php echo $screenshot['ss_url']; ?>">
                <img style="max-height: 500px;" src="<?php echo $screenshot['ss_url']; ?>" alt="<?php echo $app_name; ?> screenshot <?php echo $index + 1; ?>" loading="lazy" class="blur-up lazyload">
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>