<?php
if (!defined('ABSPATH')) exit;
$app_required = get_post_meta( get_the_ID(), 'wp_requires_GP', true );
$download_timer = get_theme_mod('download_timer', 7);

$menu_locations = get_nav_menu_locations();
$footer_menu = isset($menu_locations['footer_menu']) ? wp_get_nav_menu_items($menu_locations['footer_menu']) : array();

if(!empty($footer_menu)) :
?>
<div class="page_file-f">
	<?php
	foreach($footer_menu as $menu_item) :
		$menu_title = $menu_item->title;
		$menu_url = $menu_item->url;
	?>
	<a href="<?php echo $menu_url; ?>"><?php echo $menu_title; ?></a>
	<?php endforeach; ?>
</div>
<?php endif; ?>
<div class="background"><i class="bg-c1"></i><i class="bg-c2"></i><i class="bg-c3"></i></div>
<script>
/* Get Dark Mode or Not from localstorage */
const getElementById = id => document.getElementById(id),
    classes = getElementById('html').classList,
    cl = "darkmod";
if (localStorage.getItem("theme-ttl") > Date.now()) classes.toggle(cl, localStorage.getItem("theme") === 'dark');

/* Download Timer */
function countdown(sec) {
	var time = parseInt(sec);
    time--;
    if (time > 0) {
        document.getElementById("timer").innerHTML = '' + time + '';
        window.setTimeout("countdown(" + time + ")", 1500);
    } else {
        document.getElementById("timer").style.display = 'none';
        document.getElementById("pre_download").style.display = 'inline';
    }
}

countdown(<?php echo $download_timer; ?>);

/* Android version verify */
/* Function to get Android version from the user agent */
function getAndroidVersion(userAgent) {
    var match = (userAgent = (userAgent || navigator.userAgent).toLowerCase()).match(/android\s([0-9\.]*)/i);
    return match ? match[1] : undefined;
}

/* Event listener for when the DOM is fully loaded */
document.addEventListener("DOMContentLoaded", function() {
    var userAndroidVersion = parseFloat(getAndroidVersion());
    var requiredAndroidVersion = parseFloat(<?php echo $app_required; ?>);

    if (!isNaN(userAndroidVersion)) {
        var a_suElement = document.getElementById("a_su");
        var a_nosuElement = document.getElementById("a_nosu");

        if (userAndroidVersion >= requiredAndroidVersion) {
            a_nosuElement.style.display = "none";
            a_suElement.style.display = "inline";
        } else {
            a_suElement.style.display = "none";
            a_nosuElement.style.display = "inline";
        }
    }
});
</script>
<?php get_footer(); ?>