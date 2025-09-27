<?php
if (!defined('ABSPATH')) exit;
$app_name = get_post_meta(get_the_ID(), 'wp_title_GP', true);
$app_name = $app_name ?: get_the_title();
$app_developer = get_post_meta(get_the_ID(), 'wp_developers_GP', true);
$app_required = get_post_meta(get_the_ID(), 'wp_requires_GP', true);
$app_version = get_post_meta(get_the_ID(), 'wp_version_GP', true);
$app_size = get_post_meta(get_the_ID(), 'wp_sizes_GP', true);
$is_mod = get_post_meta(get_the_ID(), 'mod-tick-box', true);
?>

<h1 class="title xxlgf" itemprop="headline"><?php the_title(); ?></h1>
<meta itemprop="name" content="<?php echo $app_name; ?>">

<div class="app_view-first">
    <figure class="img">
		<img itemdrop="image" src="<?php the_post_thumbnail_url(); ?>" alt="<?php echo $app_name; ?> icon" loading="lazy">
		<?php if($is_mod) : ?>
		<span class="label-offline"><span><?php _e('MOD', 'apktemplates'); ?></span></span>
		<?php endif; ?>
	</figure>
    <div class="cont inline">
        <ul class="spec smf">
            <?php if($app_required) : ?>
            <li>
                <i class="spec_icon muted">
                    <svg class="i__duocheck">
                        <use xlink:href="#i__duocheck"></use>
                    </svg>
                </i>
                <span itemprop="operatingSystem"><?php _e('Android', 'apktemplates'); echo $app_required; ?>+</span>
            </li>
            <?php endif; if($app_version) : ?>
            <li>
                <i class="spec_icon muted">
                    <svg class="i__version">
                        <use xlink:href="#i__version"></use>
                    </svg>
                </i>
                <?php _e('Version:', 'apktemplates'); ?> <span itemprop="softwareVersion">
                <?php echo $app_version; ?>
                </span>
            </li>
            <?php endif; if($app_size) : ?>
            <li>
                <i class="spec_icon muted">
                    <svg class="i__size">
                        <use xlink:href="#i__size"></use>
                    </svg>
                </i>
                <span itemprop="fileSize">
                <?php echo $app_size; ?>
                </span>
            </li>
            <?php endif; ?>
        </ul>

    </div>
</div>
<?php if($app_developer) : ?>
<div class="developer smf muted" itemprop="author" itemscope="" itemtype="http://schema.org/Organization">
	<span itemprop="name"><?php echo $app_developer; ?></span> 
</div>
<?php endif; ?>