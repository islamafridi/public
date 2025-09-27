<?php
if (!defined('ABSPATH')) exit;

$user_info = get_userdata(1);

$args = array(
	'post_type' => 'articles',
	'orderby' => 'modified',
	'posts_per_page' => 6,
	'ignore_sticky_posts' => 1,
	'post__not_in' => array(get_the_ID()),
);

$related_posts = new WP_Query($args);

get_header();
?>
<div class="page">
    <div class="wrp">
        <div class="content no-toolbar">
            <article class="post_view ignore-select" itemscope="" itemtype="http://schema.org/Article">
				<?php if($related_posts) : ?>
                <div class="post_left">
                    <a class="btn-back sticky" href="<?php echo get_site_url(); ?>/articles/">
                        <svg class="i__arrowright">
                            <use xlink:href="#i__arrowright"></use>
                        </svg>
                    </a>
                </div>
				<?php else  : ?>
				<div class="post_right">
                    <a class="btn-back sticky" href="<?php echo get_site_url(); ?>/articles/">
                       <svg class="i__arrowleft">
                            <use xlink:href="#i__arrowleft"></use>
                        </svg>
                    </a>
                </div>
				<?php endif; ?>
                <div class="post_mid">
              
                <ul class="catbar d-sm-none">
                        <li> <a href="<?php echo get_site_url(); ?>/articles/"><span><?php _e('Articles', 'apktemplates'); ?></span></a> </li>
                </ul>
                 
                    <div>
                        <?php breadcrumbsX(); ?>
                        <h1 class="title xxxlgf" itemprop="headline">
                            <?php the_title(); ?>
                        </h1>
                        <time class="date" datetime="<?php echo get_the_modified_time('c'); ?>">
                        <?php echo get_the_modified_time('d-m-Y, H:i'); ?>
                        </time> - <span itemprop="author">
                        <?php echo $user_info->display_name; ?>
                        </span>
                </div>
					<?php if(function_exists('single_top_ad') ) single_top_ad(); ?>
                    <div class="text" itemprop="description">
                        <?php the_content();?>
                    </div>
					<?php if(function_exists('single_bottom_ad') ) single_bottom_ad(); ?>
                    <div style="margin-top: 15px; text-align: center;" itemprop="publisher" itemscope=""
                        itemtype="https://schema.org/Organization">
                        <div itemprop="logo" itemscope="" itemtype="https://schema.org/ImageObject">
                            <img src="<?php echo get_site_icon_url($size = 32); ?>" width="32" height="32">
                            <meta itemprop="width" content="180">
                            <meta itemprop="height" content="180">
                        </div>
                        <meta itemprop="name" content="<?php echo get_bloginfo('name'); ?>">
                    </div>
                    <?php get_template_part('components/single/single-share'); ?>
                </div>
            </article>
            <div class="sep_line"></div>
            <div class="related">
                <h4 class="title fbold xxlgf"><?php _e('Other Articles', 'apktemplates'); ?></h4>
                <div class="colomns">
					<?php if( $related_posts->have_posts() ) : ?>
                    <div class="content">
                        <div class="post_list">
                            <?php 
							while($related_posts->have_posts()): $related_posts->the_post();
								get_template_part('components/post-card/card-2');
                            endwhile;
							wp_reset_postdata();
							?>
                            <div class="navigation">
                                <div class="navigation_in">
                                    <div class="nav_more">
										<a href="<?php echo get_site_url(); ?>/articles/" class="btn btn-dark" role="button">
											<span class="uppercase fbold smf"><?php _e('More...', 'apktemplates'); ?></span>
										</a>
									</div>
                                </div>
                            </div>
                        </div>
                    </div>
					<?php endif; ?>
                    <aside class="sidebar">
                        <div class="block sticky"></div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_template_part('template/footer'); get_footer(); ?>