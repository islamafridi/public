<?php 
if (!defined('ABSPATH')) exit;
$app_version = get_post_meta(get_the_ID(), 'wp_version_GP', true); 
$app_name = get_post_meta(get_the_ID(), 'wp_title_GP', true);
?>

<div class="bg-white rounded-2xl shadow-sm overflow-hidden m-2 pt-4 pb-8">
    <nav class="p-4 border-b overflow-y-auto " aria-label="Breadcrumb">
        <?php
        get_template_part('components/single/single-breadcrumb');
        ?>
    </nav>
    
    <div class="flex flex-col gap-x-6 gap-y-6 items-center px-4 my-8 md:px-8 md:flex-row md:items-start">
        <div id="post-thumbnail" class="shadow-[0_1.5rem_2rem_-1rem_#00000029]">
            <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php echo $app_name ?>" width="192" height="192" class="w-48 h-48 object-cover rounded-lg blur-up lazyload" fetchpriority="high">
        </div>
        <div class="my-2 md:flex-1 md:my-0 text-center md:text-left">
            <div id="post-title" class="flex items-center mb-3">
                <h1 class="title xxlgf text-2xl lg:text-3xl font-bold text-gray-800 " itemprop="headline"><?php the_title(); ?></h1>
            </div>
            <div class="flex flex-col md:flex-row items-center justify-center md:justify-start space-x-4 mb-3">
                <span class="bg-gray-100 px-3 py-1 rounded-full text-sm font-medium"><?php echo $app_version; ?></span>
                <div class="flex items-center  mt-2 md:mt-0">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <?php echo get_the_modified_date('F j, Y'); ?>
                </div>
            </div>
            <div id="rating" class="flex items-center justify-center md:justify-start gap-2 mb-4">
                <div class="rating flex items-center">
                    
                        <?php get_template_part('components/single/single-star-rating'); ?>
                    
                </div>
                
            </div>
            <a href="<?php echo esc_url(rtrim(get_permalink(), '/') . '/downloads/'); ?>" class="btn btn-lg btn-green w-full mt-4 text-white font-bold py-3 px-6 rounded-lg flex items-center justify-center gap-2 transition-all duration-300 ease-in-out shadow-sm hover:shadow-xl">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2a.75.75 0 01.75.75v12.19l3.72-3.72a.75.75 0 111.06 1.06l-5 5a.75.75 0 01-1.06 0l-5-5a.75.75 0 111.06-1.06l3.72 3.72V2.75A.75.75 0 0112 2zm-8.25 18a.75.75 0 000 1.5h16.5a.75.75 0 000-1.5H3.75z" clip-rule="evenodd"></path></svg>
                <?php theme_translate('Download?', 'download_button'); ?>
            </a>
           
        </div>
    </div>
    <?php if(function_exists('single_top_ad') ) single_top_ad(); ?>
</div>
