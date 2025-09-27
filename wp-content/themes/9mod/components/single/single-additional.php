<?php
if (!defined('ABSPATH')) exit;
$app_name = get_post_meta(get_the_ID(), 'wp_title_GP', true );
$app_name = $app_name?: get_the_title();
$app_version = get_post_meta(get_the_ID(), 'wp_version_GP', true );
$app_total_installs = get_post_meta(get_the_ID(), 'wp_installs_GP', true );
$app_rated_years = get_post_meta(get_the_ID(), 'wp_contentrated_GP', true );
$security_title =  get_theme_mod('security_title', 'Good speed and no viruses!');
$security_description =  get_theme_mod('security_description', 'On our site you can easily download latest version [title]! All without registration and send SMS!');
$security_description = str_replace('[title]', $app_name, $security_description);
?>
<div class="box_grey app_moreinfo">
    <div class="grid">
        <div class="grid-2">
            <div class="app_moreinfo_item gplay">
                <i class="c-green">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24">
                        <path fill="currentColor" d="M245.4 1.5c-3.4.7-8.5 2.5-11.5 4-6.6 3.4-16.5 13.3-20.2 20.2-5.6 10.5-5.7 11.5-5.7 100.5V208h-81.7c-88.8 0-89.6.1-100.4 5.6-2.9 1.4-8.2 5.5-11.6 9C-.1 237-3.7 258 5 276.8c3.6 7.8 13.3 17.8 20.9 21.6 10.8 5.5 11.6 5.6 100.4 5.6H208v81.7c0 88.8.1 89.6 5.6 100.4 3.8 7.6 13.8 17.3 21.6 20.9 18.8 8.7 39.8 5.1 54.2-9.3 3.5-3.4 7.6-8.7 9-11.6 5.5-10.8 5.6-11.6 5.6-100.4V304h81.8c88.7 0 89.5-.1 100.3-5.6 7.6-3.8 17.3-13.8 20.9-21.6 8.7-18.8 5.1-39.8-9.3-54.2-3.4-3.5-8.7-7.6-11.6-9-10.8-5.5-11.6-5.6-100.3-5.6H304v-81.8c0-88.7-.1-89.5-5.6-100.3C295 19 285 9 278.1 5.6 272.2 2.5 261.6 0 255.5.1c-2.2 0-6.7.7-10.1 1.4z"></path>
                    </svg>
                </i>
                <div class="fbold"><?php _e('Additional Information:', 'apktemplates'); ?></div>
                <div class="smf">
                    <ul class="spec">

                        <li><span class="d-block"><b><?php _e('Updated:', 'apktemplates'); ?></b></span> <time itemprop="datePublished" datetime="<?php echo get_the_modified_time('c'); ?>"><?php echo get_the_modified_date('F j, Y'); ?></time></li>
                        <li itemprop="offers" itemscope="" itemtype="https://schema.org/Offer">
                            <span class="d-block"><b><?php _e('Price', 'apktemplates'); ?></b></span><span itemprop="price" content="0">$0</span>
                            <meta itemprop="priceCurrency" content="USD">
                        </li>
						<?php if($app_total_installs) : ?>
                        <li><span class="d-block"><b><?php _e('Installs', 'apktemplates'); ?></b></span> <?php echo $app_total_installs; ?></li>
						<?php endif; if($app_rated_years) : ?>
                        <li><span class="d-block"><b><?php _e('Rated for', 'apktemplates'); ?></b></span> <b><?php echo $app_rated_years; ?></b></li>
						<?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="grid-2">
            <div class="app_moreinfo_item novirus">
                <i class="c-green">
                    <svg class="i__shield" width="24" height="24">
                        <use xlink:href="#i__shield"></use>
                    </svg>
                </i>
                <div class="fbold"><?php echo $security_title; ?></div>
                <p class="smf"><?php echo $security_description; ?></p>
            </div>
        </div>
    </div>
</div>