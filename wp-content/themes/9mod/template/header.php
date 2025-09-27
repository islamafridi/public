<?php
if (!defined('ABSPATH')) exit;
$an1_is_rtl = get_theme_mod('an1_rtl_mode', false);
$logo_url = get_theme_mod('logo_img', get_template_directory_uri() . '/assets/img/logo.svg');
$menu_locations = get_nav_menu_locations();
$header_menu = isset($menu_locations['header_menu']) ? wp_get_nav_menu_items($menu_locations['header_menu']) : array();
$footer_menu = isset($menu_locations['footer_menu']) ? wp_get_nav_menu_items($menu_locations['footer_menu']) : array();

$current_url = home_url($_SERVER['REQUEST_URI']);
$current_category = get_queried_object();
$current_page_id = get_the_ID();

$facebook_url = get_theme_mod('facebook_url', 'https://www.facebook.com/apktemplates');
$telegram_url = get_theme_mod('telegram_url', 'https://telegram.me/apktemplates');
$youtube_url = get_theme_mod('youtube_url', 'https://www.youtube.com/@apktemplates?sub_confirmation=1');
?>

<div class="header">
    <header>

        <div class="logo">
            <?php if(!empty($logo_url)) : ?>
                <a href="<?php echo home_url('/'); ?>" title="<?php echo get_bloginfo('name'); ?>">
                <img src="<?php echo esc_url($logo_url); ?>" width="128" height="64" alt="9MOD" fetchpriority="high">
                        <span class="vhide"><?php echo get_bloginfo('name'); ?></span> 
                </a>
                <?php endif; ?>
            <div class="lang-switcher">
                <?php if (function_exists('falang__') && class_exists('Falang')) {
                        echo do_shortcode('[falangsw display_name="1" display_flags="0" positioning="h"]'); }
                ?>
           </div>
        </div>


        <button class="head_menu_btn d-block d-lg-none" type="button" aria-label="Menu">
            <span class="butterbrod"><i></i><i></i><i></i></span>
        </button> 
        <div class="hmenu">
            <button class="btn-close hmenu_close d-block d-lg-none" type="button">
                <svg class="i__close">
                    <use xlink:href="#i__close"></use>
                </svg>
            </button>
            <div class="head_menu">

                <?php if(isset($header_menu) && !empty($header_menu)) : ?>
                <ul class="head_menu_f">
                    <?php
                    foreach($header_menu as $menu_item) :
                        $menu_id = $menu_item->object_id;
                        $menu_title = $menu_item->title;
                        $menu_url = $menu_item->url;
                        $menu_icon = $menu_item->menu_icon;
                        $menu_type = $menu_item->type;
                        
                        // Create translation key for header menu items
                        $header_menu_key = 'header_menu_' . $menu_id . '_' . sanitize_title($menu_title);
                    
                        $is_active = false;
                
                        if ($menu_type === 'taxonomy' && ($current_url == $menu_url || is_category($menu_id) || in_category($menu_id)) && !(is_front_page() && is_home())) {
                            $is_active = true;
                        } elseif ($menu_type === 'post_type' && ($current_url == $menu_url || (is_page($menu_id) && !is_front_page()))) {
                            $is_active = true;
                        } elseif ($menu_type === 'custom') {
                            if ($current_url == $menu_url && (is_front_page() && is_home())) {
                                $is_active = true;
                            } elseif ($current_url == $menu_url) {
                                $is_active = true;
                            }
                        }
                    ?>
                    <li <?php if($is_active) echo 'class="active"'; ?>>
                        <a class="xsmf fbold" href="<?php echo $menu_url; ?>">
                            <i>
                                <?php echo apkt_get_svg_icon($menu_icon); ?>
                            </i>
                            <span><?php echo theme_translate($menu_title, $header_menu_key); ?></span> 
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; if($footer_menu) : ?>
                <ul class="head_menu_s d-lg-none">
                    <?php
                    $footer_menu_counter = 1;
                    foreach($footer_menu as $menu_item) :
                        $menu_title = $menu_item->title;
                        $menu_url = $menu_item->url;
                        
                        // Create translation key for footer menu items
                        // Using counter since footer menu might not have object_id
                        $footer_menu_key = 'footer_menu_' . $footer_menu_counter . '_' . sanitize_title($menu_title);
                    ?>
                    <li>
                        <a href="<?php echo $menu_url; ?>"><?php echo theme_translate($menu_title, $footer_menu_key); ?></a>
                    </li>
                    <?php 
                        $footer_menu_counter++;
                    endforeach; ?>
 
                </ul>
                <?php endif; ?>
                <div class="lang-switcher-mobile">
                    <?php if (function_exists('falang__') && class_exists('Falang')) {
                            echo do_shortcode('[falangsw display_name="1" display_flags="1" positioning="h"]'); }
                    ?>
                </div> 
            </div>
        </div>
        <div class="tools">
            <button id="darkmod_btn" class="darkmod_btn tool-btn" aria-label="<?php echo theme_translate('Dark Theme', 'aria_dark_theme'); ?>">
                <svg width="24" height="24">
                    <use xlink:href="#i__darkmod"></use>
                </svg>
            </button>
            <button id="qsearch_btn" class="qsearch tool-btn" data-target="qsearch_modal" aria-label="<?php echo theme_translate('Search', 'aria_search'); ?>">
                <svg width="24" height="24">
                    <use xlink:href="#i__search"></use>
                </svg>
            </button>
        </div>
    </header>
</div>

<script>
const getElementById = (id) => document.getElementById(id);
const htmlClasses = getElementById('html').classList;
const themeClass = 'darkmod';

const setTheme = (theme, force = false) => {
  if (force === false && localStorage.getItem('theme-ttl') > Date.now()) return;
  switch (theme) {
    case 'light':
      localStorage.setItem('theme', 'light');
      localStorage.setItem('theme-ttl', Date.now() + 86400000);
      htmlClasses.remove(themeClass);
      break;
    case 'dark':
      localStorage.setItem('theme', 'dark');
      localStorage.setItem('theme-ttl', Date.now() + 86400000);
      htmlClasses.add(themeClass);
      break;
  }
};

if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
  setTheme('dark');
} else {
  setTheme('light');
}

if (window.matchMedia) {
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
    setTheme(event.matches ? 'dark' : 'light', true);
  });
}

if (localStorage.getItem('theme-ttl') > Date.now()) {
  htmlClasses.toggle(themeClass, localStorage.getItem('theme') === 'dark');
}

getElementById('darkmod_btn').addEventListener('click', (e) => {
  e.preventDefault();
  if (htmlClasses.contains(themeClass)) {
    setTheme('light', true);
  } else {
    setTheme('dark', true);
  }
});

</script>

