<?php
if (!defined('ABSPATH')) exit;
get_header();

$app_version = get_post_meta(get_the_ID(), 'wp_version_GP', true);

$is_related_posts = get_theme_mod('related_post_swt', true);
$related_post_limit = get_theme_mod('related_post_limit', 3);
$post_categories  = get_the_terms(get_the_ID(), 'category');
$security_title   = get_theme_mod('security_title', 'Good speed and no viruses!');
$sub_cat_id       = null;

if (!empty($post_categories)) {
    foreach ($post_categories as $cat) {
        if ($cat->parent != 0) {
            $sub_cat_id = $cat->term_id;
            break;
        }
    }
}

$related_posts = null;

if ($sub_cat_id) {
    $related_posts = new WP_Query([
        'cat' => $sub_cat_id,
        'posts_per_page' => $related_post_limit,
        'post__not_in' => [get_the_ID()]
    ]);
}
?>
<div class="page">
    <div class="wrp">
        <div class="content no-toolbar">

            <?php
            get_template_part('components/single/single-apk-info');
            ?>
            <div class="app_view_wrp bg-white rounded-2xl shadow-sm overflow-hidden m-2 pt-4 pb-8">
                <?php get_template_part('components/single/apk-info'); ?>
            </div>

            <?php
            $screenshots = get_post_meta(get_the_ID(), 'ss_images', true);
            $is_screenshots = get_theme_mod('sp_screenshot_swt', true);
            if ($is_screenshots && !empty($screenshots)) : ?>
                <div class="app_view_wrp bg-white rounded-2xl shadow-sm overflow-hidden m-2 pt-4 pb-8">
                    <div class="section-title fbold"><?php theme_translate('Screenshots', 'App_Screenshots'); ?></div>
                    <?php get_template_part('components/single/single-screenshots'); ?>
                </div>
            <?php endif; ?>

            <article class="app_view ignore-select">
                <div class="app_view_wrp bg-white rounded-2xl shadow-sm overflow-hidden m-2 pt-4 pb-8">
                    <div class="app_view_pad">
                        <?php
                        get_template_part('components/single/single-description');
                        get_template_part('components/single/single-faqs');
                        ?>
                        <?php
                        get_template_part('components/single/single-share');
                        ?>
                    </div>

                </div>
                <?php

                ?>
                <?php if ($is_related_posts && $related_posts && $related_posts->have_posts()) : ?>
                    <div class="app_view_wrp bg-white rounded-2xl shadow-sm overflow-hidden m-2 pt-4 pb-8">
                        <div class="cont">
                            <div class="section-title fbold">
                                <?php theme_translate('Related Posts', 'related_posts_label'); ?>
                             </div>
                            <div class="card-grid">
                                <?php
                                while ($related_posts->have_posts()) : $related_posts->the_post();
                                    get_template_part('components/post-card/box-2');
                                endwhile;
                                wp_reset_postdata();
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </article>
        </div>
    </div>
</div>
<?php get_template_part('template/footer'); ?>

<?php get_footer(); ?>