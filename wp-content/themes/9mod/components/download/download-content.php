<?php
if (!defined('ABSPATH')) exit;
$app_name = get_post_meta( get_the_ID(), 'wp_title_GP', true );
$app_name = $app_name ?: get_the_title();
$app_required = get_post_meta( get_the_ID(), 'wp_requires_GP', true );
$download_info = get_post_meta( get_the_ID(), 'download_info', true );
$download_info2 = get_post_meta( get_the_ID(), 'download_info2', true );
$download_info3 = get_post_meta( get_the_ID(), 'download_info3', true );
?>
<div class="box-file">
    <div class="box-file-img">
        <img itemdrop="image" src="<?php the_post_thumbnail_url(); ?>" alt="<?php echo $app_name; ?>" loading="lazy">
    </div>
    <h1 class="title fbold">
		<?php the_title(); ?>
    </h1>

<?php if (!empty($download_info) || !empty($download_info2)) : ?>
    <div class="dopinfo">
        <i class="dopinfo-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16">
                <path fill="currentColor" d="M9,10a1,1,0,0,0,0,2h1v8H9a1,1,0,0,0,0,2h6a1,1,0,0,0,0-2H14V11a1,1,0,0,0-1-1Zm3-8a3,3,0,1,0,3,3A3,3,0,0,0,12,2Z"></path>
            </svg>
        </i>
        <h2>
            <?php if (!empty($download_info)) : ?>
                <div style="color:#FF0000"><?php echo $download_info; ?></div>
            <?php endif; ?>
            <?php if (!empty($download_info2)) : ?>
                <div><?php echo $download_info2; ?></div>
            <?php endif; ?>
            <?php if (!empty($download_info3)) : ?>
                <div style="color:#FF0000"><?php echo $download_info3; ?></div>
            <?php endif; ?>
        </h2>
    </div>
<?php endif; 
	
	if($app_required) : ?>
    <ul class="spec fbold">
        <li id="a_ver">
            <i class="spec_icon muted">
                <svg class="i__duocheck" viewBox="106 51 287 50">
                    <path fill="currentColor" d="M322.041 43.983l23.491-36.26c1.51-2.287.841-5.414-1.467-6.903-2.286-1.51-5.414-.884-6.903 1.467l-24.353 37.512c-18.27-7.485-38.676-11.691-60.226-11.691-21.571 0-41.934 4.206-60.247 11.691l-24.31-37.512C166.538-.064 163.388-.69 161.08.82a4.99 4.99 0 0 0-1.467 6.903l23.512 36.26c-42.387 20.773-70.968 59.924-70.968 104.834 0 2.761.173 5.479.41 8.175H392.62c.237-2.696.388-5.414.388-8.175.001-44.91-28.602-84.061-70.967-104.834zm-134.386 64.928c-7.442 0-13.482-5.997-13.482-13.46s6.04-13.439 13.482-13.439c7.485 0 13.482 5.975 13.482 13.439s-6.04 13.46-13.482 13.46zm129.835 0c-7.442 0-13.482-5.997-13.482-13.46s6.04-13.439 13.482-13.439c7.463 0 13.46 5.975 13.46 13.439s-5.997 13.46-13.46 13.46z"></path>
                </svg>
            </i>
            <?php _e('Android:', 'apktemplates'); ?> <?php echo $app_required; ?>+
            <i id="a_su" class="asu">
                <svg viewBox="0 0 468.293 468.293">
                    <circle cx="234.146" cy="234.146" r="234.146" fill="#44c4a1"></circle>
                    <path d="M357.52 110.145L191.995 275.67l-81.222-81.219-41.239 41.233 122.461 122.464 206.764-206.77z" fill="#fff"></path>
                </svg>
            </i>
            <i id="a_nosu" class="asu">
                <svg viewBox="0 0 512 512">
                    <ellipse cx="256" cy="256" rx="256" ry="255.832" fill="#e04f5f"></ellipse>
                    <g transform="matrix(-.7071 .7071 -.7071 -.7071 77.26 32)" fill="#fff">
                        <path d="M3.98-427.615h55.992v285.672H3.98z"></path>
                        <path d="M-110.828-312.815h285.672v55.992h-285.672z"></path>
                    </g>
                </svg>
            </i>
        </li>
    </ul>
	<?php endif; 
	get_template_part('components/download/download-link');
	?>
</div>
