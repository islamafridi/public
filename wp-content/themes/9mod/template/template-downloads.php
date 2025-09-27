<?php
if (!defined('ABSPATH')) exit;
add_action('wp_head', function () {
    echo '<meta name="robots" content="noindex, nofollow">';
}, 1);
// get-download-template.php
get_header();
$app_name = get_post_meta(get_the_ID(), 'wp_title_GP', true);
$wp_mods = get_post_meta(get_the_ID(), 'wp_mods', true);
$app_name = $app_name ?: get_the_title();
$download_links = get_post_meta(get_the_ID(), 'repeatable_download_link', true);
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
?>
<div class="container-dw">
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <!-- <div class="hero-badge">
                    <span>Premium Unlocked</span>
                </div> -->
            <h1 class="hero-title"><?php echo $app_name; ?>
                <?php
                $title = get_the_title();
                if (preg_match('/v\s?\d+(\.\d+)+/', $title, $matches)) {
                    $version = $matches[0];
                    echo esc_html($version);
                }
                ?>
                <?php theme_translate('Download', 'Download'); ?>
            </h1>
            <?php if (!empty($wp_mods)) : ?>
                <div class="hero-badge">
                    <span><?php echo $wp_mods; ?></span>
                </div>
            <?php endif; ?>
            <p class="hero-subtitle"><?php the_title(); ?></p>

            <div class="download-section">
                <p class="download-text">
                    <?php theme_translate('Thank you for downloading', 'Thank_you_for_downloading'); ?>
                        <span class="highlight"><a href="<?php echo esc_url(get_permalink()); ?>"><?php echo $app_name; ?></a></span> 
                        <?php theme_translate('from our site. The following are available links:', 'from_our_site'); ?>
                </p>

                <div class="download-options">
                    <?php get_template_part('components/single/single-download-links'); ?>
                </div>
            </div>
            <?php if(function_exists('download_top_ad') ) download_top_ad(); ?>
        </div>
    </section>

    <!-- Important Notes -->
    <section class="important-notes">
        <div class="section-header">
            <i class="fas fa-exclamation-triangle"></i>
            <h2><?php theme_translate('Important Notes', 'Important_Notes'); ?></h2>
        </div>
        <div class="notes-grid">
            <div class="note-item">
                <i class="fab fa-google-play"></i>
                <p><?php theme_translate('Google tries to block apps that aren\'t from the Play Store. To install these apps, turn off Play Protect because it can slow down your phone.', 'Important_Notes_google_tries'); ?></p>
            </div>
            <div class="note-item">
                <i class="fas fa-shield-alt"></i>
                <p><?php theme_translate('How to install games and apps from XAPK, APKs, APK files.', 'Important_Notes_how_to_install'); ?></p>
            </div>
            <div class="note-item">
                <i class="fas fa-question-circle"></i>
                <p><?php theme_translate('Any issues related to Download. Please read FAQs on', 'Important_Notes_any_issue'); ?>
                 <span class="highlight"><a href="/faqs/">9MOD.CC</a></span></p>
            </div>
        </div>
    </section>

    <!-- Installation Guide -->
    <section class="installation-guide">
        <h2><?php theme_translate('Installation Note', 'Installation_Note'); ?></h2>
        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <p><?php theme_translate('Download the APK file from the download link above.', 'Installation_Note_download_apk'); ?></p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <p><?php theme_translate('Go to Settings > Security > Enable "Unknown Sources"', 'Installation_Note_Settings'); ?></p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                 <p><?php theme_translate('Find the downloaded APK file and tap to install.', 'Installation_Note_find_download'); ?></p>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <p><?php theme_translate('Follow the installation prompts.', 'Installation_Note_Follow_installation_prompts'); ?></p>
            </div>
            <div class="step">
                <div class="step-number">5</div>
                <p><?php theme_translate('Once installed, enjoy the game!', 'Installation_Note_enjoy'); ?></p>
            </div>
        </div>
    </section>


    <!-- FAQ Section -->
    <section class="faq-section">
        <h2><?php theme_translate('Frequently Asked Questions', 'Frequently_Asked_Questions'); ?></h2>
        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question">
                    <span><?php theme_translate('What is 5Plays.org?', 'faq_what_is_5plays_title'); ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
                    </svg>
                </div>
                <div class="faq-answer">
                    <p><?php theme_translate('5Plays.org is a platform that provides modified Android applications and games with premium features unlocked.', 'faq_what_is_5plays'); ?></p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span><?php theme_translate('How to Install OBB?', 'faq_how_install_obb_title'); ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
                    </svg>
                </div>
                <div class="faq-answer">
                     <p><?php theme_translate('Extract the OBB file to Android/obb/ folder on your device storage before installing the APK.', 'faq_how_install_obb'); ?></p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span><?php theme_translate('What is a APK Installer?', 'faq_how_what_apk_installer_title'); ?></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708" />
                    </svg>
                </div>
                <div class="faq-answer">
                    <p><?php theme_translate('An APK installer is a tool that helps you install Android application packages on your device.', 'faq_how_what_apk_installer'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <?php if ($is_related_posts && $related_posts && $related_posts->have_posts()) : ?>
        <!-- Related Apps -->
        <section class="related-apps">
            <h2> <?php theme_translate('Related Posts', 'related_posts_label'); ?></h2>
            <div class="apps-grid">
                <div class="cont">
                    <div class="card-grid">
                        <?php
                        while ($related_posts->have_posts()) : $related_posts->the_post();
                            get_template_part('components/post-card/box-2');
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>


<?php get_template_part('template/footer'); ?>
<script>
    // JavaScript for FAQ
    document.addEventListener('DOMContentLoaded', function() {
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');

            question.addEventListener('click', () => {
                const isActive = item.classList.contains('active');

                // Close all FAQ items
                faqItems.forEach(faqItem => {
                    faqItem.classList.remove('active');
                });

                // Open clicked item if it wasn't active
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        });
    });
</script>

<?php if (function_exists('direct_link_ad')) direct_link_ad(); ?>


<?php
get_footer();
