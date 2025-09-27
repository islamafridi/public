<?php
function apkt_panel()
{
    $categories = get_categories(array( 'hide_empty' => 0 ));
    $tags = get_tags(array( 'hide_empty' => 0 ));

    $theme_color = get_theme_mod('theme_color', '#68cb5b');
    $logo_img = get_theme_mod('logo_img', get_template_directory_uri() . '/assets/img/logo.svg');
    
    $head_code = get_theme_mod('head_code');

    $terms = get_theme_mod('home_terms', []);

    $home_articles_swt = get_theme_mod('home_articles_swt', false);
    $home_articles_title = get_theme_mod('home_articles_title', 'Articles');
    $home_articles_limit = get_theme_mod('home_articles_limit', '4');
    $ha_btm_ad_swt = get_theme_mod('ha_btm_ad_swt', false);

    $home_news_swt = get_theme_mod('home_news_swt', false);
    $home_news_title = get_theme_mod('home_news_title', 'News');
    $home_news_limit = get_theme_mod('home_news_limit', '4');
    $hn_btm_ad_swt = get_theme_mod('hn_btm_ad_swt', false);

    $home_seo_title = get_theme_mod('home_seo_title', 'Download free games for Android');
    $home_seo_description = get_theme_mod('home_seo_description', 'In this website, you can download latest games and apps for free without virus free. Why waiting, just search and download your favorite games and apps for free.');

    $sp_hide_content_swt = get_theme_mod('sp_hide_content_swt', false);
    $sp_screenshot_swt = get_theme_mod('sp_screenshot_swt', false);
    $screenshots_limit = get_theme_mod('screenshots_limit', '5');
    $ld_promo_button = get_theme_mod('ld_promo_button', false);
    $sp_faq_swt = get_theme_mod('sp_faq_swt', false);
    $single_faqs = get_theme_mod('single_faqs', [
        [
            'question'	=>	'How do I update a game or program without losing progress/save?',
            'answer'	=>	'After releasing a new version of the MOD on our website, download the new APK and install it over the previous version without uninstalling it, it will only update to the new version, and your progress will be saved!'
        ],
        [
            'question'	=>	'9mod.cc WordPress Themes are safe to use?',
            'answer'	=>	'9mod.cc is 100% accurate theme create platform not hacker. Also our products are check with Antivirus softwares before upload.'
        ],
        [
            'question'	=>	'Themes are SEO optimized?',
            'answer'	=>	'We proud to say, we are the No 1 website to focus on SEO and Google search rich results. If you not believe, just try our theme then tell.'
        ]
    ]);
    $related_post_swt = get_theme_mod('related_post_swt', false);
    $related_post_limit = get_theme_mod('related_post_limit', '5');
    $security_title =  get_theme_mod('security_title', 'Good speed and no viruses!');
    $security_description =  get_theme_mod('security_description', 'On our site you can easily download latest version [title]! All without registration and send SMS!');

    $custom_cat = get_theme_mod('custom_cat', '');
    $custom_cat_title = get_theme_mod('custom_cat_title', '<span class="d-lg-block">Download and</span> PLAY');
    $custom_cat_posts = get_theme_mod('custom_cat_posts', [
        [
            'post' => '0',
            'bg_image' => get_template_directory_uri() . '/assets/img/rayman.png',
            'bg_color' => '#FFC541'
        ],
        [
            'post' => '0',
            'bg_image' => get_template_directory_uri() . '/assets/img/pvz.png',
            'bg_color' => '#6b40bc'
        ],
        [
            'post' => '0',
            'bg_image' => get_template_directory_uri() . '/assets/img/angry.png',
            'bg_color' => '#c00029'
        ],
        [
            'post' => '0',
            'bg_image' => get_template_directory_uri() . '/assets/img/subway.png',
            'bg_color' => '#1592e6'
        ],
    ]);

    $archive_sort = get_theme_mod('archive_sort', 'latest');
    $archive_posts_limit = get_theme_mod('archive_posts_limit', '12');

    $download_timer = get_theme_mod('download_timer', '7');
    $download_recommend_swt = get_theme_mod('download_recommend_swt', false);
    $download_recommend_post_limit = get_theme_mod('download_recommend_post_limit', '6');
    $download_random_posts_swt = get_theme_mod('download_random_posts_swt', false);
    $download_random_posts_limit = get_theme_mod('download_random_posts_limit', '8');
    $telegram_text = get_theme_mod('telegram_text', 'Join Telegram');

    $faqs = get_theme_mod('faqs', [
        [
            'question'	=>	'How to buy 9mod theme?',
            'answer'	=>	'Go visit website 9mod.cc to buy this fantastic theme for low cost.'
        ],
        [
            'question'	=>	'How to Install MODS APK?',
            'answer'	=>	'To download and install any of the Mod versions, you need to follow the simple procedure given below: First, search the app at 9mod.cc Scroll to the download link. Tap on the download button. Wait and then tap on the download link that appeared on the screen. Please wait for it to download. Tap on the install App. Go to Setting> Privacy> Install from an unknown source. Allow installation from an unknown source. Wait for the installation process. Now, enjoy the application or game. '
        ],
        [
            'question'	=>	'What is a APK Installer?',
            'answer'	=>	'Another way of Games installation. APK Installer made with attached OBB file and it’s a simple and fast way of installation of OBB Games.'
        ],
        [
            'question'	=>	'Download is not Working?',
            'answer'	=>	'As we are providing a fast cloud storage link for downloading files, sometimes due to an error, the file will be unavailable for download. Please comment about this in our article below and we will fix it soon.'
        ],
        [
            'question'	=>	'APK not Installing on your device?',
            'answer'	=>	'This is a common error happening when you have the same Game or APP installed on your device from Other Source.
Please Uninstall the other same Game or APP from your device. Try again, you will definitely be able to install it.'
        ],
    ]);

    $direct_link_ad_swt = get_theme_mod('direct_link_ad_swt', false);
    $direct_link_ad     = get_theme_mod('direct_link_ad');

    $home_top_ads_swt = get_theme_mod('home_top_ads_swt', false);
    $home_top_ads = get_theme_mod('home_top_ads');
    $home_botm_ads_swt = get_theme_mod('home_botm_ads_swt', false);
    $home_botm_ads = get_theme_mod('home_botm_ads');
    $single_top_ads_swt = get_theme_mod('single_top_ads_swt', false);
    $single_top_ads = get_theme_mod('single_top_ads');
    $single_botm_ads_swt = get_theme_mod('single_botm_ads_swt', false);
    $single_botm_ads = get_theme_mod('single_botm_ads');
    $archive_top_ads_swt = get_theme_mod('archive_top_ads_swt', false);
    $archive_top_ads = get_theme_mod('archive_top_ads');
    $archive_botm_ads_swt = get_theme_mod('archive_botm_ads_swt', false);
    $archive_botm_ads = get_theme_mod('archive_botm_ads');
    $download_top_ads_swt = get_theme_mod('download_top_ads_swt', false);
    $download_top_ads = get_theme_mod('download_top_ads');
    $download_botm_ads_swt = get_theme_mod('download_botm_ads_swt', false);
    $download_botm_ads = get_theme_mod('download_botm_ads');

    $facebook_url = get_theme_mod('facebook_url');
    $twitter_url = get_theme_mod('twitter_url');
    $youtube_url = get_theme_mod('youtube_url');
    $telegram_url = get_theme_mod('telegram_url');
    $tiktok_url = get_theme_mod('tiktok_url');
    $pinterest_url = get_theme_mod('pinterest_url');
    $whatsapp_url = get_theme_mod('whatsapp_url');
    $instagram_url = get_theme_mod('instagram_url');
    $github_url = get_theme_mod('github_url');
    $linkedin_url = get_theme_mod('linkedin_url');
    $skype_url = get_theme_mod('skype_url');
    $tumblr_url = get_theme_mod('tumblr_url');
    $twitch_url = get_theme_mod('twitch_url');
    $vk_url = get_theme_mod('vk_url');
    $reddit_url = get_theme_mod('reddit_url');

    $footer_copyright = get_theme_mod('footer_copyright', 'Copyright © 2024 APKTEMPLATES.');
    $footer_code = get_theme_mod('footer_code');

	$import_ss_limit = at_options('import_ss_limit', '7');
    $is_mod_title = at_options('is_mod_title');
    $is_mod_feature_title = at_options('is_mod_feature_title');
    $is_title_version = at_options('is_title_version');

    $upload_storage = at_options('upload_storage', 'my-server');

    $gdrive_client_id = at_options('gdrive_client_id');
    $gdrive_client_secret = at_options('gdrive_client_secret');
    $gdrive_folder_name = at_options('gdrive_folder_name');
    $gdrive_token = at_options('gdrive_token');

    $ftp_server_ip = at_options('ftp_server_ip');
    $ftp_port = at_options('ftp_port');
    $ftp_username = at_options('ftp_username');
    $ftp_password = at_options('ftp_password');
    $ftp_directory = at_options('ftp_directory');
    $ftp_url = at_options('ftp_url');

    $allow_url_fopen = ini_get('allow_url_fopen');
    $max_execution_time = ini_get('max_execution_time');
    $max_input_time = ini_get('max_input_time');
    $memory_limit = ini_get('memory_limit');
    $post_max_size = ini_get('post_max_size');
    $upload_max_filesize = ini_get('upload_max_filesize');

    $upload_dir = wp_upload_dir();
    $cache_dir = $upload_dir['apkt_cache_dir'];
    $cache_size = apkt_get_folder_size($cache_dir);
    $cache_class = $cache_size == 0 ? 'at-badge-success' : 'at-badge-danger';


   // Define delete folder URL before output
    $delete_folder_url = add_query_arg( [ 'action' => 'delete_cache' ], admin_url('admin-post.php') );


    $lic = new AT_License();
    $key = $lic->get_license_key(true);
    $mail = $lic->get_license_email();
    $is_active_lic = $lic->is_license_active();
    $get_info = $lic->get_info();

    $is_valid = $get_info['is_valid'];
    $license_name = $get_info['license_name'];
    $license_end_date = $get_info['license_end_date'];
    $license_renewal_url = $get_info['license_renewal_url'];
    $support_end_date = $get_info['support_end_date'];
    $support_renewal_url = $get_info['support_renewal_url'];
    $message = $get_info['message'];
    ?>
<div id="at-panel">
    <form method="POST" id="at-panel-form">
        <div class="at-panel-container">
            <div class="at-panel-left">
                <div class="at-panel-header">
                    <h2>Panel</h2>
                </div>
                <div class="at-panel-tabs">
                    <ul>
                        <li class="at-panel-tab active">
                            <a href="#general">
                                <i class="fa fa-cog"></i> General
                            </a>
                        </li>
                        <li class="at-panel-tab">
                            <a href="#home">
                                <i class="fa fa-home"></i> Home
                            </a>
                        </li>
                        <li class="at-panel-tab">
                            <a href="#single">
                                <i class="fa fa-file-text"></i> Single
                            </a>
                        </li>
                        <li class="at-panel-tab">
                            <a href="#archives">
                                <i class="fa fa-list-ul"></i> Archives
                            </a>
                        </li>
                        <li class="at-panel-tab">
                            <a href="#download">
                                <i class="fa fa-download"></i> Download
                            </a>
                        </li>
                        <li class="at-panel-tab">
                            <a href="#faq-page">
                                <i class="fa fa-question-circle"></i> FAQ Page
                            </a>
                        </li>
                        <li class="at-panel-tab">
                            <a href="#advertisement">
                                <i class="fa fa-usd"></i> Advertisement
                            </a>
                        </li>
                        <li class="at-panel-tab">
                            <a href="#social-links">
                                <i class="fa fa-link"></i> Social Links
                            </a>
                        </li>
                        <li class="at-panel-tab">
                            <a href="#footer">
                                <i class="fa fa-code"></i> Footer
                            </a>
                        </li>
                        <li class="at-panel-tab">
                            <a href="#apk-importer">
                                <i class="fa-brands fa-google-play"></i> APK Importer
                            </a>
                        </li>
                        <li class="at-panel-tab">
                            <a href="#storage">
                                <i class="fa-solid fa-server"></i> Storage
                            </a>
                        </li>
                        <li class="at-panel-tab">
                            <a href="#settings">
                                <i class="fa fa-sliders"></i> Settings
                            </a>
                        </li>
                        <li class="at-panel-tab">
                            <a href="#info">
                                <i class="fa fa-info-circle"></i> Info
                            </a>
                        </li>
                        <li>
                            <div class="save-changes">
                                <input type="submit" name="save-at-panel" class="button-primary" value="Save Changes" />
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="at-panel-right">
                <div class="at-panel-fields-container">
                    <div class="at-field-section active" data-section="general">
                        <div class="at-form-header">
                            <h2>General</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Theme Color', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Set your favorite color to theme.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <input type="text" name="theme_color"
                                                value="<?php echo esc_attr($theme_color); ?>" class="color-picker" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Logo', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Here, you can add a header logo for your website.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <div class="at-img-upload">
                                                <input type="text" name="logo_img" id="logo_img" class="at-text-ipt"
                                                    value="<?php echo esc_html($logo_img); ?>" />
                                                <input type="button" class="at-upload-img-ipt" value="&#xf093;"
                                                    data-title="Header Logo (Light Mode)" data-target="#logo_img" />
                                            </div>
                                        </div>
                                        <p class="at-field-hint at-mb-2">
                                            <?php esc_html_e('Required image size: width: 128px and height: 64px.', 'apktemplates');?>
                                        </p>
                                    </td>
                                </tr>
                                   <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Head Code', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Add the head code for your entire website. For instance, you can include Adsense, Google Analytics, Webmaster, Bing or any tracking codes you want. This code will appear above </head> tag.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <textarea name="head_code" class="at-textarea" spellcheck="false"
                                                rows="7"><?php if (!empty($head_code)) { echo stripslashes($head_code); } ?></textarea>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="at-field-section" data-section="home">
                        <div class="at-form-header">
                            <h2>Home</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>

                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Posts', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Customize home posts.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div id="at-term-field" class="at-mb-2 at-term-field" <?php if (isset($terms) && !empty($terms)) { echo 'style="display: block"'; } ?>>
                                            <?php
                                            if (isset($terms) && !empty($terms)) {
                                                $term_count = 0;
                                                foreach ($terms as $index => $term) : ?>
                                            <div class="at-coll-container">
                                                <div class="at-coll-header">
                                                    <div class="at-coll-title">Term
                                                        <?php echo $term_count + 1; ?>
                                                    </div>
                                                    <div class="at-coll-action">
                                                        <span class="at-action-move">
                                                            <i class="fa fa-bars"></i>
                                                        </span>
                                                        <a href="javascript:void(0);"
                                                            class="remove-at-coll delete">Remove</a>
                                                        <a href="javascript:void(0);" class="edit-at-coll edit">Edit</a>
                                                    </div>
                                                </div>
                                                <div class="at-coll-body" style="display: none">
                                                    <div class="at-mb-2 at-pb-2">
                                                        <p class="at-mini-title">Section Title</p>
                                                        <p>Add text for title.</p>
                                                        <input type="text"
                                                            name="home_terms[<?php echo $term_count; ?>][term_title]"
                                                            class="at-text-ipt"
                                                            value="<?php echo esc_attr($term['term_title']); ?>">
                                                    </div>
                                                    <div class="at-mb-2 at-pb-2">
                                                        <p class="at-mini-title">Posts Limit</p>
                                                        <p>Set the number of posts shown.</p> <input type="number"
                                                            name="home_terms[<?php echo $term_count; ?>][term_limit]"
                                                            class="at-number-ipt" min="1" max="50"
                                                            value="<?php echo esc_attr($term['term_limit']); ?>"> Posts
                                                    </div>
                                                    <div class="at-mb-2 at-pb-2">
                                                        <p class="at-mini-title">Term</p>
                                                        <p>Select a category or tag term for the posts that will be
                                                            shown.</p> <input type="search"
                                                            class="at-search-ipt coll-term-search" min="3"
                                                            placeholder="Enter atleast 3 letters...">
                                                        <div class="coll-term-results" style="display: none"> </div>
                                                        <div class="coll-term-selected">
                                                            <ul data-term-search-id="<?php echo $term_count; ?>">
                                                                <?php if (!empty($term['term_id']) && apkt_is_category_or_tag($term['term_id'])): ?>
                                                                <li
                                                                    data-term-id="<?php echo esc_attr($term['term_id']); ?>">
                                                                    <?php echo esc_html(get_term_name_by_id(($term['term_id']))); ?>
                                                                    <span class="delete"><i
                                                                            class="fa fa-trash-alt"></i></span>
                                                                    <input type="hidden"
                                                                        name="home_terms[<?php echo $term_count; ?>][term_id]"
                                                                        value="<?php echo esc_attr($term['term_id']); ?>" />
                                                                </li>
                                                                <?php else: ?>
                                                                <input type="hidden"
                                                                    name="home_terms[<?php echo $term_count; ?>][term_id]"
                                                                    value="" />
                                                                <?php endif;?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <p class="at-mini-title">Term Bottom Ad</p>
                                                        <p>Show or hide the advertisement shown at the bottom of this
                                                            term.</p> <label class="at-switch-btn"> <input
                                                                type="checkbox"
                                                                name="home_terms[<?php echo $term_count; ?>][term_btm_ad]"
                                                                <?php checked($term['term_btm_ad'], 1);?>>
                                                            <span class="at-switch"></span> </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php 
                                            $term_count++; endforeach; 
                                        } ?>
                                        </div>
                                        <button id="add-new-term" type="button" class="add-button">Add Term</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Articles', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Customize home articles.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="home_articles_swt"
                                                    <?php checked($home_articles_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div class="at-mb-2">
                                            <p class="at-mini-title">
                                                <?php esc_html_e('Section Title', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="home_articles_title" class="at-text-ipt"
                                                value="<?php echo esc_html($home_articles_title); ?>" />
                                        </div>
                                        <div class="at-mb-2">
                                            <p class="at-mini-title">
                                                <?php esc_html_e('Articles Limit', 'apktemplates');?>
                                            </p>
                                            <input type="number" name="home_articles_limit" class="at-number-ipt"
                                                min="1" max="50"
                                                value="<?php echo esc_html($home_articles_limit); ?>" />
                                        </div>
                                        <div>
                                            <p class="at-mini-title">
                                                <?php esc_html_e('Advertisement (Bottom)', 'apktemplates');?>
                                            </p>
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="ha_btm_ad_swt"
                                                    <?php checked($ha_btm_ad_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('News', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Customize home news.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-2">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="home_news_swt"
                                                    <?php checked($home_news_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div class="at-mb-2">
                                            <p class="at-mini-title">
                                                <?php esc_html_e('Section Title', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="home_news_title" class="at-text-ipt"
                                                value="<?php echo esc_html($home_news_title); ?>" />
                                        </div>
                                        <div class="at-mb-2">
                                            <p class="at-mini-title">
                                                <?php esc_html_e('News Limit', 'apktemplates');?>
                                            </p>
                                            <input type="number" name="home_news_limit" class="at-number-ipt" min="1"
                                                max="50" value="<?php echo esc_html($home_news_limit); ?>" />
                                        </div>
                                        <div>
                                            <p class="at-mini-title">
                                                <?php esc_html_e('Advertisement (Bottom)', 'apktemplates');?>
                                            </p>
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="hn_btm_ad_swt"
                                                    <?php checked($hn_btm_ad_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3><?php esc_html_e('Home SEO', 'apktemplates'); ?></h3>
                                        <div class="at-field-descr"><?php esc_html_e('Here you can add website information title and describe the website secription about the your site. Note: It will beneficial for ranking.', 'apktemplates'); ?></div>
                                    </td>
                                    <td>
                                        <div class="at-mb-2">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Home SEO title', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="home_seo_title" class="at-text-ipt" value="<?php echo stripcslashes($home_seo_title); ?>" />
                                        </div>
                                        <div>
                                            <p class="at-mini-title">
                                                <?php esc_html_e('Home SEO Description', 'apktemplates');?>
                                            </p>
                                            <textarea name="home_seo_description" class="at-textarea" spellcheck="false" rows="7"><?php echo stripcslashes($home_seo_description); ?></textarea>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="at-field-section" data-section="single">
                        <div class="at-form-header">
                            <h2>Single</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Hide Content');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Show/Hide content on all posts. By default post content are fully visible. If you hide the content, limited words only visible. Note: Its not affect your SEO.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-2">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="sp_hide_content_swt"
                                                    <?php checked($sp_hide_content_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Screenshots');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Show/Hide post screenshots on all posts. Also set limit of screenshots visible on all posts', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-2">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="sp_screenshot_swt"
                                                    <?php checked($sp_screenshot_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Screenshots Limit', 'apktemplates');?>
                                            </p>
                                            <input type="number" name="screenshots_limit" class="at-number-ipt" min="1"
                                                max="50" value="<?php echo esc_html($screenshots_limit); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Single FAQ\'s', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Here, you can set show/hide FAQ\'s and add multiple FAQs on the all posts. Note: These faq\'s only visible on posts like games and apps.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                    <div class="at-mb-2">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="sp_faq_swt"
                                                    <?php checked($sp_faq_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div id="at-single-faq-field" class="at-mb-2" style="<?php if (isset($single_faqs) && !empty($single_faqs)) { echo 'display: block;'; } ?>">
                                            <?php 
                                            if (isset($single_faqs) && !empty($single_faqs)) {
                                                $faq_count = 0;
                                                foreach ($single_faqs as $index => $faq) {
                                            ?>
                                            <div class="at-coll-container">
                                                <div class="at-coll-header">
                                                    <div class="at-coll-title">
                                                        FAQ <?php echo $faq_count + 1; ?>
                                                    </div>
                                                    <div class="at-coll-action"> <span class="at-action-move"> <i
                                                                class="fa fa-bars"></i> </span> <a
                                                            href="javascript:void(0);"
                                                            class="remove-at-coll delete">Remove</a> <a
                                                            href="javascript:void(0);"
                                                            class="edit-at-coll edit">Edit</a> </div>
                                                </div>
                                                <div class="at-coll-body" style="display: none">
                                                    <div class="at-mb-2 at-pb-2">
                                                        <p class="at-mini-title">Question</p>
                                                        <p>Write question here.</p> <input type="text"
                                                            name="single_faqs[<?php echo $faq_count; ?>][question]"
                                                            class="at-text-ipt"
                                                            value="<?php echo isset($faq['question']) ? $faq['question'] : ''; ?>">
                                                    </div>
                                                    <div>
                                                        <p class="at-mini-title">Answer</p>
                                                        <p>Write answer here.</p> <textarea class="at-textarea"
                                                            name="single_faqs[<?php echo $faq_count; ?>][answer]"
                                                            rows="5"><?php echo isset($faq['answer']) ? $faq['answer'] : ''; ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php 
                                            $faq_count++; 
                                            }
                                        }
                                        ?>
                                        </div>
                                        <button id="add-new-sp-faq" type="button" class="add-button">Add FAQ</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Related Posts');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Show/Hide related posts section and set the limit of related posts visible on post.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-2">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="related_post_swt"
                                                    <?php checked($related_post_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Related Posts Limit', 'apktemplates');?>
                                            </p>
                                            <input type="number" name="related_post_limit" class="at-number-ipt" min="1"
                                                max="50" value="<?php echo esc_html($related_post_limit); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3><?php esc_html_e('Security Info', 'apktemplates'); ?></h3>
                                        <div class="at-field-descr"><?php esc_html_e('Here you can add security information title and describe the security description about the game/application. Note: It will display on all posts.', 'apktemplates'); ?></div>
                                    </td>
                                    <td>
                                        <div class="at-mb-2">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Security title', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="security_title" class="at-text-ipt" value="<?php echo stripcslashes($security_title); ?>" />
                                        </div>
                                        <div>
                                            <p class="at-mini-title">
                                                <?php esc_html_e('Security description', 'apktemplates');?>
                                            </p>
                                            <textarea name="security_description" class="at-textarea" spellcheck="false" rows="7"><?php echo stripcslashes($security_description); ?></textarea>
                                            <p class="at-field-hint at-mt-2">
                                            <?php esc_html_e('Note: [title] automatically replace the text into post title.', 'apktemplates');?>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="at-field-section" data-section="archives">
                        <div class="at-form-header">
                            <h2>Archives</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Archive Posts Order');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Set order of archive posts.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title">
                                                <?php esc_html_e('Posts sort by order', 'apktemplates');?>
                                            </p>
                                        </div>
                                        <select name="archive_sort" class="at-select at-mb-2">
                                            <option value="latest" <?php selected($archive_sort, 'latest'); ?>>
                                                <?php esc_html_e('Latest', 'apktemplates'); ?></option>
                                            <option value="modified" <?php selected($archive_sort, 'modified'); ?>>
                                                <?php esc_html_e('Modified', 'apktemplates'); ?></option>
                                            <option value="popular" <?php selected($archive_sort, 'popular'); ?>>
                                                <?php esc_html_e('Popular', 'apktemplates'); ?></option>
                                            <option value="a_to_z" <?php selected($archive_sort, 'a_to_z'); ?>>
                                                <?php esc_html_e('A to Z ↓', 'apktemplates'); ?></option>
                                            <option value="z_to_a" <?php selected($archive_sort, 'z_to_a'); ?>>
                                                <?php esc_html_e('A to Z ↑', 'apktemplates'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Archive Posts Limit');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Set number of posts appears in archive page.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Posts Limit', 'apktemplates');?>
                                            </p>
                                            <input type="number" name="archive_posts_limit" class="at-number-ipt"
                                                min="1" max="50"
                                                value="<?php echo esc_html($archive_posts_limit); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Custom Category Page', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Select the category which you want to show special posts/page to that category.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-2">
                                            <p class="at-mini-title">Select Category:</p>
                                            <div id="at-custom-cat-search">
                                                <input type="search" class="at-search-ipt term-search" min="3"
                                                    placeholder="Enter atleast 3 letters..." />
                                                <div class="term-results" style="display: none"></div>
                                                <div class="term-selected">
                                                    <ul>
                                                        <?php
                                                        if (!empty($custom_cat)) :
                                                        ?>
                                                        <li data-term-id="<?php echo $custom_cat; ?>">
                                                            <?php echo esc_html(get_term_name_by_id($custom_cat)); ?>
                                                            <span class="delete"><i class="fa fa-trash-alt"></i></span>
                                                            <input type="hidden" name="custom_cat" value="<?php echo $custom_cat; ?>" />
                                                        </li>
                                                        <?php else: ?>
                                                        <input type="hidden" name="custom_cat" value="" />
                                                        <?php endif;?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Custom Category Posts', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Customize custom category page. Here you can able to add 4 show case posts with title, background image, and backgroud color. .', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div id="at-custom-archive-fp" class="at-mb-2 at-post-field" <?php if (isset($custom_cat_posts) && !empty($custom_cat_posts)) { echo 'style="display: block"'; } ?>>
                                            <?php 
                                            if (isset($custom_cat_posts) && !empty($custom_cat_posts)) {
                                                $post_count = 0;
                                                foreach ($custom_cat_posts as $index => $post) : ?>
                                            <div class="at-coll-container">
                                                <div class="at-coll-header">
                                                    <div class="at-coll-title">Post
                                                        <?php echo $post_count + 1; ?>
                                                    </div>
                                                    <div class="at-coll-action">
                                                        <span class="at-action-move">
                                                            <i class="fa fa-bars"></i>
                                                        </span>
                                                        <a href="javascript:void(0);"
                                                            class="remove-at-coll delete">Remove</a>
                                                        <a href="javascript:void(0);" class="edit-at-coll edit">Edit</a>
                                                    </div>
                                                </div>
                                                <div class="at-coll-body" style="display: none">
                                                    <div class="at-mb-2 at-pb-2">
                                                        <p class="at-mini-title">Backgroud Image</p>
                                                        <p>Add background image.</p>
                                                        <div class="at-img-upload">
                                                            <input type="text" name="custom_cat_posts[<?php echo $post_count; ?>][bg_image]" id="custom_cat_post_bg_<?php echo $post_count; ?>"
                                                            class="at-text-ipt"
                                                            value="<?php echo esc_html($post['bg_image']); ?>" />
                                                            <input type="button" class="at-upload-img-ipt" value="&#xf093;"
                                                            data-title="Custom Archive Page Posts Background Image" data-target="#custom_cat_post_bg_<?php echo $post_count; ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="at-mb-2 at-pb-2">
                                                        <p class="at-mini-title">Background Color</p>
                                                        <p>Set background color which suitable for background image.</p>
                                                        <input type="text" name="custom_cat_posts[<?php echo $post_count; ?>][bg_color]"
                                                        value="<?php echo esc_attr($post['bg_color']); ?>" class="color-picker" />
                                                    </div>
                                                    <div>
                                                        <p class="at-mini-title">Post</p>
                                                        <p>Select a post that will be shown.</p>
                                                        <div>
                                                            <input type="search"
                                                                class="at-search-ipt coll-post-search" min="3"
                                                                placeholder="Enter atleast 3 letters...">
                                                            <div class="coll-post-results" style="display: none"> </div>
                                                            <div class="coll-post-selected">
                                                                <ul data-post-search-id="<?php echo $post_count; ?>">
                                                                    <?php if (!empty($post['post'])) : ?>
                                                                    <li data-post-id="<?php echo esc_attr($post['post']); ?>">
                                                                        <?php echo esc_html(get_post_meta($post['post'], 'wp_title_GP', true) ?: get_the_title()); ?>
                                                                        <span class="delete"><i class="fa fa-trash-alt"></i></span>
                                                                        <input type="hidden" name="custom_cat_posts[<?php echo $post_count; ?>][post]" value="<?php echo esc_attr($post['post']); ?>" />
                                                                    </li>
                                                                    <?php else: ?>
                                                                    <input type="hidden"
                                                                        name="custom_cat_posts[<?php echo $post_count; ?>][post]"
                                                                        value="" />
                                                                    <?php endif;?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php $post_count++; endforeach; } ?>
                                        </div>
                                        <button id="add-new-custom-archive-fp" type="button" class="add-button">Add Post</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="at-field-section" data-section="download">
                        <div class="at-form-header">
                            <h2>Download</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Download Timer', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Set download timer.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Timer', 'apktemplates');?>
                                            </p>
                                            <input type="number" name="download_timer" class="at-number-ipt" min="1"
                                                max="50" value="<?php echo esc_html($download_timer); ?>" />
                                        </div>
                                    </td>
                                </tr>
								<tr>
                                    <td>
                                        <h3><?php esc_html_e('Telegram Button Text', 'apktemplates'); ?></h3>
                                    </td>
                                    <td>
                                        <div class="at-mb-2">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Button Name', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="telegram_text" class="at-text-ipt" value="<?php echo $telegram_text; ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Related Posts', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Show/Hide and set the limit of related posts on the download page.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-2">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="download_recommend_swt"
                                                    <?php checked($download_recommend_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Related Posts Limit', 'apktemplates');?>
                                            </p>
                                            <input type="number" name="download_recommend_post_limit"
                                                class="at-number-ipt" min="1" max="50"
                                                value="<?php echo esc_html($download_recommend_post_limit); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Random Posts', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Show/Hide and set the limit of random posts on the download page.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-2">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="download_random_posts_swt"
                                                    <?php checked($download_random_posts_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Random Posts Limit', 'apktemplates');?>
                                            </p>
                                            <input type="number" name="download_random_posts_limit"
                                                class="at-number-ipt" min="1" max="50"
                                                value="<?php echo esc_html($download_random_posts_limit); ?>" />
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="at-field-section" data-section="faq-page">
                        <div class="at-form-header">
                            <h2>FAQ Page</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('FAQ\'s', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Here, you can add multiple FAQs on the FAQ page. Note: These faq\'s only visible on published page with FAQ template.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div id="at-faq-field" class="at-mb-2" style="<?php if (isset($faqs) && !empty($faqs)) { echo 'display: block;'; } ?>">
                                            <?php 
                                            if (isset($faqs) && !empty($faqs)) {
                                                $faq_count = 0;
                                                foreach ($faqs as $index => $faq) {
                                            ?>
                                            <div class="at-coll-container">
                                                <div class="at-coll-header">
                                                    <div class="at-coll-title">
                                                        FAQ <?php echo $faq_count + 1; ?>
                                                    </div>
                                                    <div class="at-coll-action"> <span class="at-action-move"> <i
                                                                class="fa fa-bars"></i> </span> <a
                                                            href="javascript:void(0);"
                                                            class="remove-at-coll delete">Remove</a> <a
                                                            href="javascript:void(0);"
                                                            class="edit-at-coll edit">Edit</a> </div>
                                                </div>
                                                <div class="at-coll-body" style="display: none">
                                                    <div class="at-mb-2 at-pb-2">
                                                        <p class="at-mini-title">Question</p>
                                                        <p>Write question here.</p> <input type="text"
                                                            name="faqs[<?php echo $faq_count; ?>][question]"
                                                            class="at-text-ipt"
                                                            value="<?php echo isset($faq['question']) ? $faq['question'] : ''; ?>">
                                                    </div>
                                                    <div>
                                                        <p class="at-mini-title">Answer</p>
                                                        <p>Write answer here.</p> <textarea class="at-textarea"
                                                            name="faqs[<?php echo $faq_count; ?>][answer]"
                                                            rows="5"><?php echo isset($faq['answer']) ? $faq['answer'] : ''; ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php 
                                            $faq_count++; 
                                            }
                                        }
                                        ?>
                                        </div>
                                        <button id="add-new-faq" type="button" class="add-button">Add FAQ</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="at-field-section" data-section="advertisement">
                        <div class="at-form-header">
                            <h2>Advertisement</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Direct Link Ad', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Add an ad code that will appear in the direct download link list page.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="direct_link_ad_swt"
                                                    <?php checked($direct_link_ad_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <textarea name="direct_link_ad" class="at-textarea" spellcheck="false"
                                                rows="7"><?php echo stripslashes($direct_link_ad); ?></textarea>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Home Top Ad', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Add an ad code below the first section in Home.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="home_top_ads_swt"
                                                    <?php checked($home_top_ads_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <textarea name="home_top_ads" class="at-textarea" spellcheck="false"
                                                rows="7"><?php echo stripslashes($home_top_ads); ?></textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Home Bottom Ad', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Add an ad code below the category/tag posts and articles/news in Home.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="home_botm_ads_swt"
                                                    <?php checked($home_botm_ads_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <textarea name="home_botm_ads" class="at-textarea" spellcheck="false"
                                                rows="7"><?php echo stripslashes($home_botm_ads); ?></textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Single Top Ad', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Add an ad code above the content in post.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="single_top_ads_swt"
                                                    <?php checked($single_top_ads_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <textarea name="single_top_ads" class="at-textarea" spellcheck="false"
                                                rows="7"><?php echo stripslashes($single_top_ads); ?></textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Single Bottom Ad', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Add an ad code below the content in post.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="single_botm_ads_swt"
                                                    <?php checked($single_botm_ads_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <textarea name="single_botm_ads" class="at-textarea" spellcheck="false"
                                                rows="7"><?php echo stripslashes($single_botm_ads); ?></textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Archive Top Ad [Categories, Tags, Articles and News]', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Add an ad code above the posts in archive pages.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="archive_top_ads_swt"
                                                    <?php checked($archive_top_ads_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <textarea name="archive_top_ads" class="at-textarea" spellcheck="false"
                                                rows="7"><?php echo stripslashes($archive_top_ads); ?></textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Archive Bottom Ad [Categories, Tags, Articles and News]', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Add an ad code below the posts in archive pages.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="archive_botm_ads_swt"
                                                    <?php checked($archive_botm_ads_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <textarea name="archive_botm_ads" class="at-textarea" spellcheck="false"
                                                rows="7"><?php echo stripslashes($archive_botm_ads); ?></textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Download Top Ad', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Add an ad code above the download link in download pages.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="download_top_ads_swt"
                                                    <?php checked($download_top_ads_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <textarea name="download_top_ads" class="at-textarea" spellcheck="false"
                                                rows="7"><?php echo stripslashes($download_top_ads); ?></textarea>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Download Bottom Ad', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Add an ad code below the download link in download pages.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="download_botm_ads_swt"
                                                    <?php checked($download_botm_ads_swt, 1);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                        <div>
                                            <textarea name="download_botm_ads" class="at-textarea" spellcheck="false"
                                                rows="7"><?php echo stripslashes($download_botm_ads); ?></textarea>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="at-field-section" data-section="social-links">
                        <div class="at-form-header">
                            <h2>Social Links</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Social Platforms', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Add URL of your social media platform profiles.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Facebook', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="facebook_url" class="at-text-ipt"
                                                value="<?php echo esc_url($facebook_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Twitter', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="twitter_url" class="at-text-ipt"
                                                value="<?php echo esc_url($twitter_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('YouTube', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="youtube_url" class="at-text-ipt"
                                                value="<?php echo esc_url($youtube_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Telegram', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="telegram_url" class="at-text-ipt"
                                                value="<?php echo esc_url($telegram_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('TikTok', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="tiktok_url" class="at-text-ipt"
                                                value="<?php echo esc_url($tiktok_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Pinterest', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="pinterest_url" class="at-text-ipt"
                                                value="<?php echo esc_url($pinterest_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('WhatsApp', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="whatsapp_url" class="at-text-ipt"
                                                value="<?php echo esc_url($whatsapp_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Instagram', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="instagram_url" class="at-text-ipt"
                                                value="<?php echo esc_url($instagram_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Github', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="github_url" class="at-text-ipt"
                                                value="<?php echo esc_url($github_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('LinkedIn', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="linkedin_url" class="at-text-ipt"
                                                value="<?php echo esc_url($linkedin_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Skype', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="skype_url" class="at-text-ipt"
                                                value="<?php echo esc_url($skype_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Tumblr', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="tumblr_url" class="at-text-ipt"
                                                value="<?php echo esc_url($tumblr_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Twitch', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="twitch_url" class="at-text-ipt"
                                                value="<?php echo esc_url($twitch_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('VK', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="vk_url" class="at-text-ipt"
                                                value="<?php echo esc_url($vk_url); ?>" />
                                        </div>
                                        <div class="at-mb-1">
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Reddit', 'apktemplates');?>
                                            </p>
                                            <input type="text" name="reddit_url" class="at-text-ipt"
                                                value="<?php echo esc_url($reddit_url); ?>" />
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="at-field-section" data-section="footer">
                        <div class="at-form-header">
                            <h2>Footer</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Copyright Text', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('This text only appears in footer.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <input type="text" name="footer_copyright" class="at-text-ipt"
                                                value="<?php echo stripcslashes($footer_copyright); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Footer Code', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Add the footer code for your entire website. For instance, you can include Adsense, Google Analytics, Webmaster, Bing or any tracking codes you want. This code will appear above </body> tag.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <textarea name="footer_code" class="at-textarea" spellcheck="false"
                                                rows="7"><?php echo stripcslashes($footer_code); ?></textarea>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="at-field-section" data-section="apk-importer">
                        <div class="at-form-header">
                            <h2>APK Importer</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <h3>
                                            <?php esc_html_e('License', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php echo esc_html('Activate the license to use the theme without any restrictions. After activating the theme with the license key, you will be able to see the license status and information in this panel.', 'apktemplates'); ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php if ($is_active_lic): ?>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <div style="display: flex; column-gap: 1rem">
                                            <input type="text" id="apkt_license_key" name="apkt_license_key"
                                                class="at-text-ipt" placeholder="License key"
                                                value="<?php echo $key; ?>"
                                                <?php echo $is_active_lic ? 'disabled="disabled"' : ''; ?> />
                                            <input type="email" id="apkt_license_email" name="apkt_license_email"
                                                class="at-text-ipt" placeholder="Email address"
                                                value="<?php echo $mail; ?>"
                                                <?php echo $is_active_lic ? 'disabled="disabled"' : ''; ?> />
                                            <button type="button" id="apkt-license-btn" class="at-btn at-btn-danger"
                                                data-action="apkt_deactivate_license">Deactivate</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('License Status', 'apktemplates');?>
                                        </h3>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <span class="at-badge at-badge-success">
                                                <?php echo $is_valid ? esc_html('Active', 'apktemplates') : esc_html('Inactive', 'apktemplates'); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('License Plan', 'apktemplates');?>
                                        </h3>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <p style="font-size: 16px">
                                                <?php echo !empty($license_name) ? $license_name : ''; ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Active Domains', 'apktemplates');?>
                                        </h3>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <a href="https://9mod.cc/account/manage-products/"
                                                style="font-size: 16px"><?php echo esc_html('Manage', 'apktemplates'); ?></a>
                                        </div>
                                    </td>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('License Expired On', 'apktemplates');?>
                                        </h3>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <p style="font-size: 16px">
                                                <?php echo !empty($license_end_date) ? $license_end_date : 'N/A'; ?>
                                                <?php if ($license_renewal_url): ?><a
                                                    href="<?php echo $license_renewal_url; ?>"><?php echo esc_html('Renew', 'apktemplates'); ?></a><?php endif;?>
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Support Expired On', 'apktemplates');?>
                                        </h3>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <p style="font-size: 16px">
                                                <?php echo !empty($support_end_date) ? $support_end_date : 'N/A'; ?>
                                                <?php if ($support_renewal_url): ?><a
                                                    href="<?php echo $support_renewal_url; ?>"><?php echo esc_html('Renew', 'apktemplates'); ?></a><?php endif;?>
                                            </p>
                                        </div>
                                    </td>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('License Key', 'apktemplates');?>
                                        </h3>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <p style="font-size: 16px"><?php echo !empty($key) ? $key : ''; ?></p>
                                        </div>
                                    </td>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Message', 'apktemplates');?>
                                        </h3>
                                    </td>
                                    <td>
                                        <div class="at-mb-1">
                                            <p style="font-size: 16px"><?php echo !empty($message) ? $message : ''; ?>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <div style="display: flex; column-gap: 1rem">
                                            <input type="text" id="apkt_license_key" name="apkt_license_key"
                                                class="at-text-ipt" placeholder="License key" value="" />
                                            <input type="email" id="apkt_license_email" name="apkt_license_email"
                                                class="at-text-ipt" placeholder="Email address"
                                                value="<?php echo $mail; ?>" />
                                            <button type="button" id="apkt-license-btn" class="at-btn at-btn-success"
                                                data-action="apkt_activate_license">Activate</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif;?>
								<tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Screnshots', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            <?php esc_html_e('Set the screeshots limit while scrapping.', 'apktemplates');?>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <p class="at-mini-title at-mb-1">
                                                <?php esc_html_e('Screenshots Limit', 'apktemplates');?>
                                            </p>
                                            <input type="number" name="import_ss_limit"
                                                class="at-number-ipt" min="1" max="50"
                                                value="<?php echo esc_html($import_ss_limit); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('MOD APK Title', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Enable to add 'MOD APK' text to post title.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="is_mod_title"
                                                    <?php checked($is_mod_title);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('MOD Feature in Title', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Enable to add MOD Feature text to post title.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="is_mod_feature_title"
                                                    <?php checked($is_mod_feature_title);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('App Version in Title', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Enable to add app version to post title.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <label class="at-switch-btn">
                                                <input type="checkbox" name="is_title_version"
                                                    <?php checked($is_title_version);?>>
                                                <span class="at-switch"></span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="at-field-section" data-section="storage">
                        <div class="at-form-header">
                            <h2>Storage</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Server', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Set APK file storage server.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <select name="upload_storage" class="at-select"
                                                value="<?php echo $upload_storage; ?>">
                                                <option value="my-server"
                                                    <?php selected($upload_storage, 'my-server');?>>My Server</option>
                                                <option value="gdrive" <?php selected($upload_storage, 'gdrive');?>>
                                                    Google Drive</option>
                                                <option value="ftp" <?php selected($upload_storage, 'ftp');?>>FTP
                                                </option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <h3>Google Drive</h3>
                                        <div class="at-field-descr" style="opacity: 1">
                                            Add Google Drive credidentials to upload the APK file driectly to your
                                            Google Drive. For instruction, read guide <a
                                                href="https://9mod.cc/apk-extractor-setup/">here</a>.
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Client ID', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Place Google Drive client ID here.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <input type="text" name="gdrive_client_id" class="at-text-ipt"
                                                value="<?php echo esc_html($gdrive_client_id); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Client Secret', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Place Google Drive client Secret code here.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="at-inputs-container">
                                                <input type="password" name="gdrive_client_secret"
                                                    class="at-password-ipt"
                                                    value="<?php echo esc_html($gdrive_client_secret); ?>" />
                                                <input type="button" class="at-password-btn" value="&#xf070;" />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Folder', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Place Google Drive file storing folder name here.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <input type="text" name="gdrive_folder_name" class="at-text-ipt"
                                                value="<?php echo esc_html($gdrive_folder_name); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <?php
                                        if ($gdrive_token && $gdrive_client_id && $gdrive_client_secret) {?>
                                        <div class="at-mb-1">
                                            <span class="at-status">
                                                <?php esc_html_e('Status:', 'apktemplates');?>
                                            </span>
                                            <span class="at-badge at-badge-success">
                                                <?php esc_html_e('Connected', 'apktemplates');?>
                                            </span>
                                        </div>
                                        <div>
                                            <a href="<?php echo admin_url('admin.php?page=at-panel&action=disconnect_gdrive'); ?>"
                                                class="at-btn at-btn-danger">
                                                <?php esc_html_e('Disconnect G-Drive');?>
                                                <span class="dashicons dashicons-external"></span>
                                            </a>
                                        </div>
                                        <?php } else { ?>
                                        <div class="at-status at-mb-1">
                                            <?php esc_html_e('Status:', 'apktemplates');?>
                                            <span class="at-badge at-badge-danger">
                                                <?php esc_html_e('Disconnected', 'apktemplates');?>
                                            </span>
                                        </div>
                                        <div class="at-mt-1">
                                            <a href="<?php echo admin_url('admin.php?page=at-panel&action=connect_gdrive'); ?>"
                                                class="at-btn at-btn-success">
                                                <?php esc_html_e('Connect G-Drive');?>
                                                <span class="dashicons dashicons-external"></span>
                                            </a>
                                        </div>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <h3>FTP</h3>
                                        <div class="at-field-descr" style="opacity: 1">
                                            Add FTP credidentials to upload the APK file driectly to your FTP. For
                                            instruction, read guide <a
                                                href="https://9mod.cc/apk-extractor-setup/">here</a>.
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Server Name or IP*', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Place FTP servername or IP address here.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <input type="text" name="ftp_server_ip" class="at-text-ipt"
                                                value="<?php echo esc_html($ftp_server_ip); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Port', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Place FTP port number here.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <input type="text" name="ftp_port" class="at-text-ipt"
                                                value="<?php echo esc_html($ftp_port); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Username', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Place FTP username here.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <input type="text" name="ftp_username" class="at-text-ipt"
                                                value="<?php echo esc_html($ftp_username); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Password', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Place FTP password here.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="at-inputs-container">
                                                <input type="password" name="ftp_password" class="at-password-ipt"
                                                    value="<?php echo esc_html($ftp_password); ?>" />
                                                <input type="button" class="at-password-btn" value="&#xf070;" />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('Directory', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Place file storing directory path here.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <input type="text" name="ftp_directory" class="at-text-ipt"
                                                value="<?php echo esc_html($ftp_directory); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <h3>Samples</h3>
                                        <div class="at-field-descr" style="opacity: 1">
                                            <ul
                                                style="font-size: 15px;line-height: 1.8;list-style: disc;margin-left: .8rem;">
                                                <li>public_html</li>
                                                <li>/site.com/</li>
                                                <li>/domain/mainsite.com/public_html/</li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>
                                            <?php esc_html_e('URL', 'apktemplates');?>
                                        </h3>
                                        <div class="at-field-descr">
                                            Place url here which is accesible to download files.
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <input type="text" name="ftp_url" class="at-text-ipt"
                                                value="<?php echo esc_html($ftp_url); ?>" />
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <div>
                                            <a href="<?php echo admin_url('admin.php?page=at-panel&action=test_ftp'); ?>"
                                                class="at-btn at-btn-success">
                                                <?php esc_html_e('Test Connection', 'apktemplates');?>
                                                <span class="dashicons dashicons-external"></span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="at-field-section" data-section="settings">
                        <div class="at-form-header">
                            <h2>Settings</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <h3>Import Demo</h3>
                                        <div class="at-field-descr" style="opacity: 1">
                                            Here you can <code><strong>Import Demo</strong></code> posts.
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <div style="display: flex; column-gap: 1rem;">
                                            <a id="import-demo-btn" href="#!" class="at-btn at-btn-primary">
                                                <span class="dashicons dashicons-download"></span>
                                                <?php esc_html_e('Import Demo', 'apktemplates');?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr id="import-demo-container" class="table-content" style="display: none">
                                    <td><label for="import-demo" class="import-settings-container">
                                            <input type="file" name="import-demo" id="import-demo" class="at-upload-file-ipt"
                                                accept=".json">
                                        </label>
                                    </td>
                                </tr>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <h3>Import/Export Theme Settings</h3>
                                        <div class="at-field-descr" style="opacity: 1">
                                            Here you can <code><strong>Import/Export</strong></code> theme panel
                                            settings. With one
                                            click to export your theme settings to save as backup.
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <div style="display: flex; column-gap: 1rem;">
                                            <a id="import-settings-btn" href="#!" class="at-btn at-btn-primary">
                                                <span class="dashicons dashicons-download"></span>
                                                <?php esc_html_e('Import', 'apktemplates');?>
                                            </a>
                                            <a id="export-settings" href="#!" class="at-btn at-btn-success">
                                                <span class="dashicons dashicons-upload"></span>
                                                <?php esc_html_e('Export', 'apktemplates');?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr id="import-settings-container" class="table-content" style="display: none">
                                    <td><label for="import-settings" class="import-settings-container">
                                            <input type="file" name="import-settings" id="import-settings"
                                                class="at-upload-file-ipt" accept=".json">
                                        </label>
                                    </td>
                                </tr>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <h3>Cache</h3>
                                        <div class="at-field-descr" style="opacity: 1">
                                            This theme all cache files are stored in
                                            <code><strong>wp-content/uploads/apktemplates/cache</strong></code> folder.
                                            So you can delete cache files from here or manually delete by following the
                                            directory path. <br>
                                            <br>
                                            <code><strong>Note: </strong> You need to clear the cache or
                                            delete the cache folder inside files when you do not use the APK file
                                            importer. During the APK import process, don't clear the cache or delete
                                            folders; otherwise, you will get an import error.</code>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>Total cache size</h3>
                                    </td>
                                    <td>
                                        <span class="at-badge <?php echo $cache_class; ?> at-mb-1">
                                            <?php echo apkt_format_bytes($cache_size); ?>
                                        </span>
                                    </td>
                                </tr>

                                <tr class="table-content">
                                    <td colspan="2">
                                        <div>
                                            <a id="delete-cache" href="<?php echo esc_url($delete_folder_url); ?>"
                                                class="at-btn at-btn-danger">
                                                <span class="dashicons dashicons-trash"></span>
                                                <?php esc_html_e('Clear Cache'); ?>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                    <div class="at-field-section" data-section="info">
                        <div class="at-form-header">
                            <h2>Info</h2>
                        </div>
                        <table class="at-field-table">
                            <tbody>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <h3>Required Info</h3>
                                        <div class="at-field-descr" style="opacity: 1">
                                            Requirements are must need for importer, if it's less than required limit,
                                            APK importer will not work properly. So follow instructions to change the
                                            value of requirements. For instruction, read <a
                                                href="https://9mod.cc/apk-extractor-setup/">here</a>.
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>allow_url_fopen</h3>
                                    </td>
                                    <td>
                                        <?php if ($allow_url_fopen == 1): ?>
                                        <span class="at-badge at-badge-success">Enabled</span>
                                        <?php else: ?>
                                        <span class="at-badge at-badge-danger">Disabled</span>
                                        <p class="at-field-hint">
                                            Recommended: Enable
                                        </p>
                                        <?php endif;?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>max_execution_time</h3>
                                    </td>
                                    <td>
                                        <?php if ($max_execution_time >= 300): ?>
                                        <span class="at-badge at-badge-success">
                                            <?php echo $max_execution_time; ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="at-badge at-badge-danger at-mb-1">
                                            <?php echo ($max_execution_time <= 0) ? 'No limit' : $max_execution_time; ?>
                                        </span>
                                        <p class="at-field-hint">
                                            <strong>Recommended:</strong> <em>300 or Greater than 300</em>
                                        </p>
                                        <?php endif;?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>max_input_time</h3>
                                    </td>
                                    <td>
                                        <?php if ($max_input_time >= 300): ?>
                                        <span class="at-badge at-badge-success">
                                            <?php echo $max_input_time; ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="at-badge at-badge-danger at-mb-1">
                                            <?php echo ($max_input_time <= 0) ? 'No limit' : $max_input_time; ?>
                                        </span>
                                        <p class="at-field-hint">
                                            <strong>Recommended:</strong> <em>300 or Greater than 300</em>
                                        </p>
                                        <?php endif;?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>memory_limit</h3>
                                    </td>
                                    <td>
                                        <?php if (is_server_setting_valid($memory_limit, 4096)): ?>
                                        <span class="at-badge at-badge-success">
                                            <?php echo $memory_limit; ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="at-badge at-badge-danger at-mb-1">
                                            <?php echo $memory_limit; ?>
                                        </span>
                                        <p class="at-field-hint">
                                            <strong>Recommended:</strong> <em>4GB or Greater than 4GB</em>
                                        </p>
                                        <?php endif;?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>post_max_size</h3>
                                    </td>
                                    <td>
                                        <?php if (is_server_setting_valid($post_max_size, 4096)): ?>
                                        <span class="at-badge at-badge-success">
                                            <?php echo $post_max_size; ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="at-badge at-badge-danger at-mb-1">
                                            <?php echo $post_max_size; ?>
                                        </span>
                                        <p class="at-field-hint">
                                            <strong>Recommended:</strong> <em>4GB or Greater than 4GB</em>
                                        </p>
                                        <?php endif;?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>upload_max_filesize</h3>
                                    </td>
                                    <td>
                                        <?php if (is_server_setting_valid($upload_max_filesize, 4096)): ?>
                                        <span class="at-badge at-badge-success">
                                            <?php echo $upload_max_filesize; ?>
                                        </span>
                                        <?php else: ?>
                                        <span class="at-badge at-badge-danger at-mb-1">
                                            <?php echo $upload_max_filesize; ?>
                                        </span>
                                        <p class="at-field-hint">
                                            <strong>Recommended:</strong> <em>4GB or Greater than 4GB</em>
                                        </p>
                                        <?php endif;?>
                                    </td>
                                </tr>
                                <tr class="table-content">
                                    <td colspan="2">
                                        <h3>Theme Information</h3>
                                        <div class="at-field-descr" style="opacity: 1">
                                            Here is the information about currently installed our theme. Thank you for
                                            choosing 9mod.cc :).
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>Name</h3>
                                    </td>
                                    <td>
                                        <span class="at-badge"><?php echo 'AN1'; ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>Version</h3>
                                    </td>
                                    <td>
                                        <span class="at-badge"><?php echo APKT_THEME_VERSION; ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>Publisher</h3>
                                    </td>
                                    <td>
                                        <span class="at-badge"><?php echo 'APKTEMPLATES'; ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>Website</h3>
                                    </td>
                                    <td>
                                        <a href="https://9mod.cc" target="_blank"
                                            rel="nofollow, noindex, noreferrer"><span
                                                class="at-badge"><?php echo 'Visit'; ?> <span
                                                    class="dashicons dashicons-admin-links"></span></span></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <h3>Description</h3>
                                    </td>
                                    <td>
                                        <p style="opacity: 1;">
                                            <?php echo '9mod.cc is customized WordPress theme for apps/games.'; ?>
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="save-changes" style="display: none">
            <button type="submit">Save Changes</button>
        </div>
    </form>
</div>
<?php
}