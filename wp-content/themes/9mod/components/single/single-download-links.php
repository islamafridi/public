<?php
if (!defined('ABSPATH')) exit;
get_header();

$app_name         = get_post_meta(get_the_ID(), 'wp_title_GP', true);
$app_name         = $app_name ?: get_the_title();
$download_links   = get_post_meta(get_the_ID(), 'repeatable_download_link', true);
$is_related_posts = get_theme_mod('related_post_swt', true);
$related_post_limit = get_theme_mod('related_post_limit', 3);
$post_categories  = get_the_terms(get_the_ID(), 'category');
$security_title   = get_theme_mod('security_title', 'Good speed and no viruses!');
$sub_cat_id       = null;

if (!empty($post_categories)) {
    foreach ($post_categories as $cat) {
        if ($cat->parent != 0) {
            $sub_cat_id = $cat->term_id;
            break;
        }
    }
}

$related_posts = null;

if ($sub_cat_id) {
    $related_posts = new WP_Query([
        'cat' => $sub_cat_id,
        'posts_per_page' => $related_post_limit,
        'post__not_in' => [get_the_ID()]
    ]);
}

// Group download links by download_group
$grouped_links = [];
$has_obb_or_zip = false;
if (!empty($download_links) && is_array($download_links)) {
    foreach ($download_links as $index => $link) {
        $group = !empty($link['download_group']) ? $link['download_group'] : 'Default';
        // For "Default" group, create a unique group for each link using its index
        $group_key = ($group === 'Default') ? 'Default_' . $index : $group;
        $grouped_links[$group_key][] = array_merge($link, ['index' => $index]);
        // Check for OBB or ZIP in download_name
        $words = array_map('trim', explode(',', $link['download_name'] ?? ''));
        if (in_array('obb', array_map('strtolower', $words)) || in_array('zip', array_map('strtolower', $words))) {
            $has_obb_or_zip = true;
        }
    }
}
?>

<div class="anchor-line"><span id="dw"></span></div>
<div class="box_download overflow-hidden">
    <?php if (function_exists('single_bottom_ad')) single_bottom_ad(); ?>

    <?php if (!empty($grouped_links)) : ?>
        <div class="download-box">
            <div class="app_moreinfo_item novirus flex">
                <i class="c-green">
                    <svg class="i__shield" width="24" height="24">
                        <use xlink:href="#i__shield"></use>
                    </svg>
                </i>
                <div class="fbold sec-text"><?php theme_translate($security_title, 'security_title'); ?>
</div>
            </div>

            <?php foreach ($grouped_links as $group_key => $links) : ?>
                <div class="accordion-dw download-item">
                    <div class="accordion-item-dw">
                        <div class="accordion-item-header-dw <?php echo $group_key === array_key_first($grouped_links) ? 'active-dw' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                            </svg>
                            <?php echo esc_html($links[0]['download_mod_info'] ?? 'Download'); ?>
                        </div>

                        <div class="accordion-item-body-dw">
                            <div class="accordion-item-body-content-dw">
                                <?php foreach ($links as $field) : ?>
                                    <?php
                                    $download_name     = $field['download_name'] ?? '';
                                    $download_version  = $field['download_version'] ?? '';
                                    $download_mod_info = $field['download_mod_info'] ?? '';
                                    $download_mod_note = $field['download_mod_note'] ?? '';
                                    $download_size     = $field['download_size'] ?? '';
                                    $download_note     = $field['download_note'] ?? '';
                                    $index             = $field['index'];
                                    ?>
                                    <?php if (!empty($download_note)) : ?>
                                        <p class="download-note"><?php echo wp_kses_post($download_note); ?></p>
                                    <?php endif; ?>

                                    <a class="btn-download" href="<?php echo esc_url(get_permalink() . 'download/' . $index . '/'); ?>" rel="nofollow">
                                        <div class="game-card">
                                            <img src="<?php the_post_thumbnail_url('thumbnail'); ?>" alt="<?php echo esc_attr($app_name); ?>" width="192" height="192" class="game-icon w-48 h-48 object-cover rounded-lg blur-up lazyload" loading="lazy" fetchpriority="high">
                                            <div class="game-details">
                                                <h3 class="game-title"><?php echo esc_html($app_name); ?></h3>
                                                <div class="meta-info">
                                                    <span class="badge size"><?php echo esc_html($download_size); ?></span>
                                                    <?php
                                                    // Split the download_name into words based on commas and trim whitespace
                                                    $words = array_map('trim', explode(',', $download_name));
                                                    // Determine button_class based on the first word in download_name
                                                    $first_word = !empty($words) ? strtolower($words[0]) : '';
                                                    $button_class = ($first_word === 'apk') ? 'green' :
                                                                    (($first_word === 'xapk') ? 'xapk' :
                                                                    (($first_word === 'apks') ? 'apks' :
                                                                    (($first_word === 'obb' || $first_word === 'zip') ? 'obb' : $first_word)));
                                                    // Render each word in a separate span
                                                    foreach ($words as $word) {
                                                        if (!empty($word)) {
                                                            echo '<span class="badge ' . esc_attr($button_class) . '">' . esc_html(strtoupper($word)) . '</span>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <?php
                                                $matches = [];
                                                $version_pattern = '/\bv\d+(\.\d+)*([a-zA-Z-]*)?\b/';
                                                $has_version = preg_match($version_pattern, $download_mod_info, $matches);
                                                $version = $has_version && !empty($matches[0]) ? $matches[0] : '';
                                                $title = $has_version ? trim(str_replace($matches[0], '', $download_mod_info)) : $download_mod_info;
                                                ?>

                                                <?php if (!empty($download_mod_note) || preg_match($version_pattern, $download_mod_info, $matches)) : ?>
                                                    <p class="mod-info">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                                                            <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 0 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0"/>
                                                            <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708"/>
                                                        </svg>
                                                        <?php
                                                        echo esc_html($matches[0] ?? $download_mod_info);
                                                        if (!empty($download_mod_note)) {
                                                            echo ' - ' . esc_html($download_mod_note);
                                                        }
                                                        ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="download-icon">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($has_obb_or_zip) : ?>
                <div class="foot cache">
                    <i class="b-yellow c-warn">
                        <svg class="i__notif" width="24" height="24">
                            <use xlink:href="#i__notif"></use>
                        </svg>
                    </i>
                    <div class="c-warn smf">
                        <span class="d-block fbold">Unzip/Extract the downloaded OBB, and put in the given path:</span>
                        Android → <b>obb</b>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const accordionItemHeaders = document.querySelectorAll('.accordion-item-header-dw');

    accordionItemHeaders.forEach((accordionItemHeader, index) => {
        // Set the first accordion to be open by default
        if (index === 0) {
            accordionItemHeader.classList.add('active-dw');
            const accordionItemBody = accordionItemHeader.nextElementSibling;
            accordionItemBody.style.maxHeight = accordionItemBody.scrollHeight + 'px';
        }

        accordionItemHeader.addEventListener('click', event => {
            // Toggle the clicked accordion
            accordionItemHeader.classList.toggle('active-dw');
            const accordionItemBody = accordionItemHeader.nextElementSibling;
            if (accordionItemHeader.classList.contains('active-dw')) {
                accordionItemBody.style.maxHeight = accordionItemBody.scrollHeight + 'px';
            } else {
                accordionItemBody.style.maxHeight = 0;
            }

            // Close other accordions
            accordionItemHeaders.forEach((otherHeader, otherIndex) => {
                if (otherIndex !== index && otherHeader.classList.contains('active-dw')) {
                    otherHeader.classList.remove('active-dw');
                    otherHeader.nextElementSibling.style.maxHeight = 0;
                }
            });
        });
    });
});
</script>

<?php
get_footer();
?>