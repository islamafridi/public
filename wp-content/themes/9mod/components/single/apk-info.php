<?php
if (!defined('ABSPATH')) exit;
$app_name = get_post_meta(get_the_ID(), 'wp_title_GP', true);
$app_name = $app_name ?: get_the_title();
$app_developer = get_post_meta(get_the_ID(), 'wp_developers_GP', true);
$app_required = get_post_meta(get_the_ID(), 'wp_requires_GP', true);
$app_version = get_post_meta(get_the_ID(), 'wp_version_GP', true);
$app_size = get_post_meta(get_the_ID(), 'wp_sizes_GP', true);
$is_mod = get_post_meta(get_the_ID(), 'mod-tick-box', true);
$mod_info = get_post_meta(get_the_ID(), 'mod_info', true); 

?>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-4 md:p-8 bg-gray-50 rounded-2xl py-6 mx-2">
    <?php if (!empty($app_name)) { ?>
        <div class="apk-info-box flex items-center p-4 bg-white rounded-lg shadow-sm transition-shadow"><svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8  mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path></svg>
        <div><div class="text-gray-600 font-semibold text-sm "><?php theme_translate('App Name', 'app_name_apkinfo'); ?></div>
        <p class="text-gray-900 font-bold"><?php echo $app_name; ?></p></div></div>
    <?php } ?>

    <?php if (!empty($app_version)) { ?>
    <div class="apk-info-box flex items-center p-4 bg-white rounded-lg shadow-sm transition-shadow"><svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8  mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
    <div><div class="text-gray-600 font-semibold text-sm "><?php theme_translate('Version', 'version_apkinfo'); ?></div><p class="text-gray-900 font-bold">v<?php echo $app_version; ?></p></div></div>
    <?php } ?>

    <div class="apk-info-box flex items-center p-4 bg-white rounded-lg shadow-sm transition-shadow"><svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8  mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
    <div><div class="text-gray-600 font-semibold text-sm "><?php theme_translate('Updated', 'updated_apkinfo'); ?></div><p class="text-gray-900 font-bold"><?php echo get_the_modified_date('F j, Y'); ?></p></div></div>

    <?php if (!empty($app_developer)) { ?>
    <div class="apk-info-box flex items-center p-4 bg-white rounded-lg shadow-sm transition-shadow"><svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8  mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
    <div><div class="text-gray-600 font-semibold text-sm "><?php theme_translate('Publisher', 'publisher_apkinfo'); ?></div><p class="text-gray-900 font-bold"><?php echo $app_developer; ?></p></div></div>

   <?php } ?>
    <?php if (!empty($app_required)) { ?>
    <div class="apk-info-box flex items-center p-4 bg-white rounded-lg shadow-sm transition-shadow"><svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8  mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.39 48.39 0 0 1-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 0 1-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 0 0-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 0 1-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 0 0 .657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 0 1-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 0 0 5.427-.63 48.05 48.05 0 0 0 .582-4.717.532.532 0 0 0-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 0 0 .658-.663 48.422 48.422 0 0 0-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 0 1-.61-.58v0Z"></path></svg>
    <div><div class="text-gray-600 font-semibold text-sm "><?php theme_translate('Requirements', 'requirements_apkinfo'); ?></div><p class="text-gray-900 font-bold"><?php _e('Android ', 'apktemplates'); echo $app_required; ?>+</p></div></div>
    <?php } ?>

<div class="apk-info-box flex items-center p-4 bg-white rounded-lg shadow-sm transition-shadow">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8  mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <div>
        <div class="text-gray-600 font-semibold text-sm "><?php theme_translate('Genre', 'genre_apkinfo'); ?></div>
        <?php
            $post_categories = get_the_category(get_the_ID());
            if (!empty($post_categories)) {
                foreach ($post_categories as $category) {
                    // Check if the category is a child (has a parent)
                    if ($category->category_parent != 0) {
                        // Use WordPress get_category_link() function to generate proper URL
                        $category_url = get_category_link($category->term_id);
                        echo '<a href="' . esc_url($category_url) . '" class=" font-bold">' . esc_html($category->name) . '</a>';
                        // Break to display only the first child category
                        break;
                    }
                }
            }
        ?>
    </div>
</div>
    <?php if (!empty($app_size)) { ?>
        <div class="apk-info-box flex items-center p-4 bg-white rounded-lg shadow-sm transition-shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8  mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21 3.582 4-8 4 s-8-1.79-8-4"></path>
            </svg>
            <div>
                <div class="text-gray-600 font-semibold text-sm "><?php theme_translate('Size', 'Size_apkinfo'); ?></div>
                <p class="text-gray-900 font-bold"><?php echo $app_size; ?></p>
            </div>
            
        </div>
    <?php } ?>

<div class="apk-info-box flex items-center p-4 bg-white rounded-lg shadow-sm transition-shadow"><svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8  mr-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path></svg>
<div><div class="text-gray-600 font-semibold text-sm "><?php theme_translate('Price', 'Price_apkinfo'); ?></div><p class="text-gray-900 font-bold">Free</p></div></div>

</div>

<?php if (!empty($mod_info)) : ?>
    <div class="accordion">
        <div class="accordion-item">
            <button class="accordion-header" onclick="toggleAccordion(this)">
                <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <h2 class="text-xl font-bold tbm-0"><?php theme_translate('MOD Info?', 'mod_info_apkinfo'); ?></h2>
                    </div>
                <span class="accordion-icon">+</span>
            </button>
            <div class="accordion-body">
                <?php echo wpautop($mod_info); ?>
            </div>
        </div>
    </div>
<script>
function toggleAccordion(button) {
    const item = button.parentElement;
    item.classList.toggle("active");

    const body = item.querySelector(".accordion-body");
    const icon = item.querySelector(".accordion-icon");

    if (body.style.display === "block") {
        body.style.display = "none";
        icon.textContent = "+";
    } else {
        body.style.display = "block";
        icon.textContent = "×";
    }
}
</script>
<?php endif; ?>
