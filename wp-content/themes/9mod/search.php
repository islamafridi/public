<?php 
if (!defined('ABSPATH')) exit;
get_header();

global $wp_query;
$paged = get_query_var('paged') ? get_query_var('paged') : 1;
$total_pages = $wp_query->max_num_pages;
?>
<div class="wrp">
<div class="toolbar">
    <div class="breadcrumbs xsmf">
        <?php breadcrumbsX(); ?>
    </div>
</div>
</div>
<div class="page">
    <div class="wrp">
        <div class="content">
            <div class="heading">
                <h1 class="fbold title xxxlgf">
                     <?php theme_translate('Site Search', 'h1_search_page'); ?> 
                </h1>
            </div>
            <?php if( !have_posts() ): ?>
            <div class="alert"><b><?php theme_translate('Information', 'Information_search'); ?></b><br>
              <?php theme_translate('Unfortunately, site search have no results. Try to change or shorten your request.', 'no_search'); ?>
            </div>
            <?php endif; ?>
                <div class="searchbox">
                    <div class="box_grey">
                        <form role="search" method="get" action="<?php echo get_site_url(); ?>">
                            <div class="search_field">
                                <input placeholder="<?php echo get_search_query(); ?>" id="search-input" type="search" name="s" minlength="3" maxlength="156" required>
                                <button type="submit" title="Find">
                                    <svg class="i__search">
										<use xlink:href="#i__search"></use>
                                    </svg>
                                    <span class="vhide">
                                       <?php theme_translate('Search', 'search_page'); ?>    
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="section section-sep">
                    <?php if(function_exists('archive_top_ad') ) archive_top_ad(); ?>
            <div class="card-grid" style="margin-top: 1em;">
                <?php 
				 if( have_posts() ) :
                	while( have_posts() ): the_post();
						get_template_part('components/post-card/box-2');
					endwhile;
					wp_reset_postdata();
                endif;
				
				if(function_exists('archive_bottom_ad') ) archive_bottom_ad();
				?>
            </div>
            </div>
            <div class="pagination">
				<?php apktemplates_pagination($paged, $total_pages); ?>
			</div>
        </div>
    </div>
</div>
<?php get_template_part('template/footer'); get_footer(); ?>