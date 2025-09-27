<?php
if (!defined('ABSPATH')) exit;
$copyright_text = get_theme_mod('footer_copyright', 'All right reserved by <a href="https://9mod.cc" rel="nofollow">9MOD.CC</a>');
$menu_locations = get_nav_menu_locations();
$footer_menu = isset($menu_locations['footer_menu']) ? wp_get_nav_menu_items($menu_locations['footer_menu']) : array();

?>


<footer class="footer">
    <div class="wrp">
        <div class="copyright smf">
			<?php if(has_site_icon()) : ?>
            <a href="<?php echo get_site_url(); ?>" aria-label="<?php echo get_bloginfo('name'); ?>">
            	<img src="<?php echo get_site_icon_url(); ?>" alt="favicon" width="20" height="20">
            </a>
			<?php endif; if($copyright_text) : ?>
            <span class="copyright_i1 fbold"><?php echo $copyright_text; ?></span>
			<?php endif; ?>
        </div>
    </div>
    <div class="foot_line xsmf">
        <div class="wrp">
			<?php if($footer_menu) : ?>
            <ul class="foot_menu">
				<?php
				foreach($footer_menu as $menu_item) :
					$menu_title = $menu_item->title;
					$menu_url = $menu_item->url;
				?>
				<li>
					<a href="<?php echo $menu_url; ?>"><?php echo $menu_title; ?></a>
				</li>
				<?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</footer>

<div class="modal modal_search" id="qsearch_modal">
    <div class="modal_content">
        <button class="btn-close qsearch_close" type="button">
            <svg class="i__close">
                <use xlink:href="#i__close"></use>
            </svg>
        </button>
        <div class="modal_wrap">
            <div class="qsearch_form">
                <form role="search" method="get" action="<?php echo get_site_url(); ?>">
                    <div class="search_field">
                        <input id="search-input" type="search" placeholder="Search..." name="s" minlength="3" maxlength="156" required="">
                        <button type="submit" title="Find" aria-label="Search">
                            <svg class="i__search">
                                <use xlink:href="#i__search"></use>
                            </svg>
                            <span class="vhide"><?php _e('Search', 'apktemplates'); ?></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<svg aria-hidden="true" style="position: absolute; width: 0; height: 0; overflow: hidden;" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <defs>
        <symbol id="i__search" viewBox="0 0 24 24">
            <path fill="currentColor" d="M15.5,14h-.79l-.28-.27a6.51,6.51,0,1,0-.7.7l.27.28v.79l5,4.99L20.49,19Zm-6,0A4.5,4.5,0,1,1,14,9.5,4.494,4.494,0,0,1,9.5,14Z"></path>
        </symbol>
        <symbol id="i__arrowright" viewBox="0 0 24 24">
            <path fill="currentColor" d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"></path>
        </symbol>
        <symbol id="i__arrowleft" viewBox="0 0 24 24">
            <path fill="currentColor" d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"></path>
        </symbol>
        <symbol id="i__yt" viewBox="0 0 32 32">
            <rect fill="currentColor" width="32" height="32"></rect>
            <path d="M11.4,22.8l11.4-6.6L11.4,9.6V22.8z" fill="#ffffff"></path>
        </symbol>
        <symbol id="i__fb" viewBox="0 0 32 32">
            <rect fill="currentColor" width="32" height="32"></rect>
            <path d="M18.8,17l0.4-2.4h-2.3V13c-0.1-0.7,0.4-1.3,1.1-1.3c0.1,0,0.2,0,0.3,0h1.1V9.6	c-0.6-0.1-1.3-0.2-1.9-0.2c-1.6-0.1-3.1,1.1-3.2,2.7c0,0.2,0,0.4,0,0.5v1.9h-2.1V17h2.1v5.9c0.9,0.1,1.8,0.1,2.6,0V17L18.8,17z" fill="#ffffff"></path>
        </symbol>
        <symbol id="i__vk" viewBox="0 0 32 32">
            <rect fill="currentColor" width="32" height="32"></rect>
            <path d="M23.6,11.7c0.1-0.4,0-0.7-0.6-0.7h-1.8c-0.4,0-0.7,0.2-0.8,0.5c-0.6,1.4-1.3,2.6-2.3,3.8	c-0.4,0.4-0.6,0.6-0.9,0.6c-0.1,0-0.3-0.1-0.3-0.5v-3.6c0-0.5-0.1-0.7-0.5-0.7h-2.9c-0.2,0-0.5,0.2-0.5,0.4c0,0,0,0,0,0	c0,0.4,0.7,0.6,0.7,1.8v2.7c0,0.6-0.1,0.7-0.3,0.7c-0.6,0-2.1-2.3-3-4.9c-0.2-0.5-0.4-0.7-0.8-0.7H7.8c-0.5,0-0.6,0.2-0.6,0.5	c0,0.5,0.6,2.9,2.9,6.1c1.2,2,3.3,3.2,5.6,3.4c1.2,0,1.3-0.3,1.3-0.7v-1.7c0-0.5,0.1-0.6,0.5-0.6c0.3,0,0.7,0.1,1.8,1.2	c1.2,1.2,1.5,1.8,2.2,1.8h1.8c0.5,0,0.8-0.3,0.6-0.8c-0.4-0.8-0.9-1.5-1.5-2.2c-0.4-0.5-1.1-1.1-1.3-1.3c-0.3-0.4-0.2-0.5,0-0.8	C21.1,15.9,23.4,12.8,23.6,11.7" fill="#ffffff"></path>
        </symbol>
        <symbol id="i__tg" viewBox="0 0 32 32">
            <rect fill="currentColor" width="32" height="32"></rect>
            <path fill="#ffffff" d="M7.61,15.44,22.85,9.56c.71-.25,1.33.18,1.1,1.25h0L21.35,23c-.19.87-.7,1.08-1.42.67L16,20.78l-1.91,1.84a1,1,0,0,1-.8.39l.28-4,7.33-6.62c.32-.28-.07-.44-.49-.16l-9,5.7L7.43,16.69c-.85-.27-.86-.84.18-1.25Z"></path>
        </symbol>
        <symbol id="i__cat" viewBox="0 0 30 24">
            <path fill="currentColor" d="M5 0H1a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1zm18 0H9a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V1a1 1 0 0 0-1-1zM5 9H1a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1zm18 0H9a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1zM5 18H1a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1zm18 0H9a1 1 0 0 0-1 1v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-4a1 1 0 0 0-1-1z"></path>
        </symbol>
        <symbol id="i__duocheck" viewBox="106 51 287 50">
            <path fill="currentColor" d="M322.041 43.983l23.491-36.26c1.51-2.287.841-5.414-1.467-6.903-2.286-1.51-5.414-.884-6.903 1.467l-24.353 37.512c-18.27-7.485-38.676-11.691-60.226-11.691-21.571 0-41.934 4.206-60.247 11.691l-24.31-37.512C166.538-.064 163.388-.69 161.08.82a4.99 4.99 0 0 0-1.467 6.903l23.512 36.26c-42.387 20.773-70.968 59.924-70.968 104.834 0 2.761.173 5.479.41 8.175H392.62c.237-2.696.388-5.414.388-8.175.001-44.91-28.602-84.061-70.967-104.834zm-134.386 64.928c-7.442 0-13.482-5.997-13.482-13.46s6.04-13.439 13.482-13.439c7.485 0 13.482 5.975 13.482 13.439s-6.04 13.46-13.482 13.46zm129.835 0c-7.442 0-13.482-5.997-13.482-13.46s6.04-13.439 13.482-13.439c7.463 0 13.46 5.975 13.46 13.439s-5.997 13.46-13.46 13.46z"></path>
        </symbol>
        <symbol id="i__version" viewBox="0 0 490 490">
            <path fill="currentColor" d="M162.036 162.036H0V490h327.964V327.964H490V0H162.036v162.036zM459.375 30.625v266.714H327.964V162.036H192.661V30.625h266.714z"></path>
        </symbol>
        <symbol id="i__size" viewBox="0 2 24 24">
            <path fill="currentColor" d="M12 8C5.373 8 0 6.657 0 5v4c0 1.657 5.373 3 12 3s12-1.343 12-3V5c0 1.657-5.373 3-12 3zm0 6c-6.627 0-12-1.343-12-3v4c0 1.657 5.373 3 12 3s12-1.343 12-3v-4c0 1.657-5.373 3-12 3zm0 6c-6.627 0-12-1.343-12-3v4c0 1.657 5.373 3 12 3s12-1.343 12-3v-4c0 1.657-5.373 3-12 3z"></path>
        </symbol>
        <symbol id="i__download" viewBox="0 0 24 24">
            <path fill="currentColor" d="M19,12v7H5v-7H3v7c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2v-7H19z M13,12.7l2.6-2.6l1.4,1.4l-5,5l-5-5l1.4-1.4l2.6,2.6V3h2V12.7z"></path>
        </symbol>
        <symbol id="i__reload" viewBox="0 0 24 24">
            <path fill="currentColor" d="M12,6v3l4-4l-4-4v3c-4.4,0-8,3.6-8,8c0,1.5,0.4,3,1.2,4.3l1.5-1.5C6.2,13.9,6,13,6,12C6,8.7,8.7,6,12,6z M18.8,7.7l-1.5,1.5c1.6,2.9,0.4,6.5-2.5,8.1C14,17.8,13,18,12,18v-3l-4,4l4,4v-3c4.4,0,8-3.6,8-8C20,10.5,19.6,9,18.8,7.7z"></path>
        </symbol>
        <symbol id="i__shield" viewBox="0 0 24 24">
            <path fill="currentColor" d="M12,1L3,5v6c0,5.5,3.8,10.7,9,12c5.2-1.3,9-6.5,9-12V5L12,1z M19,11c0,4.5-2.8,8.4-7,9.9c-4.2-1.5-7-5.5-7-9.9V6.3l7-3.1l7,3.1V11z M7.4,11.6L6,13l4,4l8-8l-1.4-1.4L10,14.2L7.4,11.6z"></path>
        </symbol>
        <symbol id="i__close" viewBox="0 0 24 24">
            <path fill="currentColor" d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z"></path>
        </symbol>
        <symbol id="i__edit" viewBox="0 0 24 24">
            <path fill="currentColor" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"></path>
        </symbol>
        <symbol id="i__notif" viewBox="0 0 24 24">
            <path fill="currentColor" d="M18,16v-5c0-3.1-1.6-5.6-4.5-6.3V4c0-0.8-0.7-1.5-1.5-1.5S10.5,3.2,10.5,4v0.7C7.6,5.4,6,7.9,6,11v5l-2,2v1h16v-1L18,16z M13,16h-2v-2h2V16z M13,12h-2V8h2V12z M12,22c1.1,0,2-0.9,2-2h-4C10,21.1,10.9,22,12,22z"></path>
        </symbol>
        <symbol id="i__right" viewBox="0 0 24 24">
            <path fill="currentColor" d="M8.6,16.6l4.6-4.6L8.6,7.4L10,6l6,6l-6,6L8.6,16.6z"></path>
        </symbol>
        <symbol id="i__darkmod" viewBox="0 0 24 24">
            <path fill="currentColor" d="M20 15.31L23.31 12 20 8.69V4h-4.69L12 .69 8.69 4H4v4.69L.69 12 4 15.31V20h4.69L12 23.31 15.31 20H20v-4.69zM12 18V6c3.31 0 6 2.69 6 6s-2.69 6-6 6z"></path>
        </symbol>
        <symbol id="i__share-fb" viewBox="0 0 24 24">
            <path fill="currentColor" d="M13.38,12v9.18H9.58V12H7.78V8.76h1.8V6.67a3.56,3.56,0,0,1,3.83-3.83h2.81V6h-2a.77.77,0,0,0-.8.88v1.9h2.84L15.89,12Z"></path>
        </symbol>
        <symbol id="i__share-ok" viewBox="0 0 24 24">
            <path fill="currentColor" d="M12,12.29A4.65,4.65,0,1,0,7.36,7.63h0A4.63,4.63,0,0,0,12,12.29Zm0-6.56a1.93,1.93,0,1,1-1.92,1.93h0A1.93,1.93,0,0,1,12,5.73Zm5,7.36a1.36,1.36,0,0,0-1.87-.44h0a5.86,5.86,0,0,1-6.24,0,1.29,1.29,0,0,0-1.79.29h0L7,13.08H7A1.36,1.36,0,0,0,7.42,15a9,9,0,0,0,2.7,1.12L7.55,18.63a1.28,1.28,0,0,0-.1,1.81.52.52,0,0,0,.1.11,1.34,1.34,0,0,0,1.87,0h0L12,18l2.57,2.56a1.28,1.28,0,0,0,1.81.11l.1-.11h0a1.28,1.28,0,0,0,.1-1.81l-.1-.1h0L13.9,16.09A9,9,0,0,0,16.6,15,1.37,1.37,0,0,0,17,13.09Z"></path>
        </symbol>
        <symbol id="i__share-tw" viewBox="0 0 24 24">
            <path fill="currentColor" d="M15,4.72h0c2.36-.21,3.08,2.07,5.46.26A3.78,3.78,0,0,1,19,7,10.47,10.47,0,0,0,21,6.4a7.52,7.52,0,0,1-.51.68,10.2,10.2,0,0,1-1,1C19,13.56,15.43,19.3,9.2,19.3H8.14A11.06,11.06,0,0,1,3,17.65H3a7.66,7.66,0,0,0,5.4-1.46h0A3.77,3.77,0,0,1,5,13.63a4.27,4.27,0,0,0,1.63-.06,3.81,3.81,0,0,1-3-3.68,4.37,4.37,0,0,0,1.65.45A3.77,3.77,0,0,1,4.19,5.41,10.63,10.63,0,0,0,11.8,9.27a3.77,3.77,0,0,1,2.88-4.5h0Z"></path>
        </symbol>
        <symbol id="i__share-vk" viewBox="0 0 24 24">
            <path fill="currentColor" d="M11.81,17.14h1.07a.91.91,0,0,0,.49-.22.76.76,0,0,0,.15-.48s0-1.44.65-1.65,1.51,1.39,2.41,2a1.65,1.65,0,0,0,1.19.37h2.41s1.26-.07.66-1.12A8.43,8.43,0,0,0,19.05,14c-1.51-1.4-1.3-1.13.51-3.59C20.68,8.92,21.11,8,21,7.62S20,7.35,20,7.35H17.32a.69.69,0,0,0-.35,0,.89.89,0,0,0-.24.3,15.42,15.42,0,0,1-1,2.14c-1.2,2.05-1.68,2.15-1.89,2-.45-.29-.33-1.19-.33-1.82,0-2,.29-2.82-.59-3a4.49,4.49,0,0,0-1.27-.12,5.9,5.9,0,0,0-2.25.22c-.32.16-.55.49-.41.51A1.22,1.22,0,0,1,9.75,8,2.6,2.6,0,0,1,10,9.18s.2,2.32-.27,2.62-.91-.1-2-2a17.51,17.51,0,0,1-1-2,.88.88,0,0,0-.22-.25,1.08,1.08,0,0,0-.42-.16H3.58a.92.92,0,0,0-.53.16c-.12.16,0,.47,0,.47a29.27,29.27,0,0,0,4.3,7.08,6.25,6.25,0,0,0,4.5,2"></path>
        </symbol>
        <symbol id="i__share-tg" viewBox="0 0 24 24">
            <path fill="currentColor" d="m9.417 15.181-.397 5.584c.568 0 .814-.244 1.109-.537l2.663-2.545 5.518 4.041c1.012.564 1.725.267 1.998-.931l3.622-16.972.001-.001c.321-1.496-.541-2.081-1.527-1.714l-21.29 8.151c-1.453.564-1.431 1.374-.247 1.741l5.443 1.693 12.643-7.911c.595-.394 1.136-.176.691.218z"></path>
        </symbol>
        <symbol id="i__share-wa" viewBox="0 0 24 24">
            <path fill="currentColor" d="m17.507 14.307-.009.075c-2.199-1.096-2.429-1.242-2.713-.816-.197.295-.771.964-.944 1.162-.175.195-.349.21-.646.075-.3-.15-1.263-.465-2.403-1.485-.888-.795-1.484-1.77-1.66-2.07-.293-.506.32-.578.878-1.634.1-.21.049-.375-.025-.524-.075-.15-.672-1.62-.922-2.206-.24-.584-.487-.51-.672-.51-.576-.05-.997-.042-1.368.344-1.614 1.774-1.207 3.604.174 5.55 2.714 3.552 4.16 4.206 6.804 5.114.714.227 1.365.195 1.88.121.574-.091 1.767-.721 2.016-1.426.255-.705.255-1.29.18-1.425-.074-.135-.27-.21-.57-.345z"></path>
            <path fill="currentColor" d="m20.52 3.449c-7.689-7.433-20.414-2.042-20.419 8.444 0 2.096.549 4.14 1.595 5.945l-1.696 6.162 6.335-1.652c7.905 4.27 17.661-1.4 17.665-10.449 0-3.176-1.24-6.165-3.495-8.411zm1.482 8.417c-.006 7.633-8.385 12.4-15.012 8.504l-.36-.214-3.75.975 1.005-3.645-.239-.375c-4.124-6.565.614-15.145 8.426-15.145 2.654 0 5.145 1.035 7.021 2.91 1.875 1.859 2.909 4.35 2.909 6.99z"></path>
        </symbol>
    </defs>
</svg>