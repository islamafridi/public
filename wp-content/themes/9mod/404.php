<?php 
if (!defined('ABSPATH')) exit;
get_header(); ?>
<style>
	.searchbox {
		width: 30%;
		margin-top: 30px;
	}
@media (max-width:575px) {
   	.searchbox {
		width: 100%;
	}
}
</style>
<div class="page-404" style="display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:60px 20px;">
    <h1 style="font-size:72px; margin-bottom:20px; color:#e74c3c;">404</h1>
    <h2 style="font-size:28px; margin-bottom:10px;"><?php _e('Oops! The page you\'re looking for doesn\'t exist.', 'apktemplates'); ?></h2>
    <p style="font-size:16px; max-width:600px; margin-bottom:30px;">
        <?php _e('Can\'t find the page? Try searching the site.', 'apktemplates'); ?>
    </p>
		<div class="searchbox">
			<div class="box_grey">
				<form role="search" method="get" action="<?php echo get_site_url(); ?>">
					<div class="search_field">
						<input placeholder="Search apps, games, and more..." id="search-input" type="search" name="s" minlength="3" maxlength="156" required>
						<button type="submit" title="Find">
							<svg class="i__search">
								<use xlink:href="#i__search"></use>
							</svg>
							<span class="vhide"><?php _e('Search', 'apktemplates'); ?></span>
						</button>
					</div>
				</form>
			</div>
		</div>
    <a href="<?php echo home_url(); ?>" style="background:#68cb5b; color:#fff; padding:12px 24px; border-radius:5px; text-decoration:none; margin-top:45px;">
        <?php _e('Go to Homepage', 'apktemplates'); ?>
    </a>
</div>
<?php get_template_part('template/footer'); get_footer(); ?>