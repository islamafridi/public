<?php 
if (!defined('ABSPATH')) exit;
if (!defined('ABSPATH')) exit;
$is_single_faq = get_theme_mod('sp_faq_swt', true);
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
if($is_single_faq && !empty($single_faqs)) : ?>
<div class="app-faq">
    <div class="app-faq-heading">
        <svg xmlns="http://www.w3.org/2000/svg" height="24" width="24" viewBox="0 0 24 24">
            <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"></path>
        </svg>
        <span><?php _e('Questions and Answers', 'apktemplates'); ?></span>
    </div>
    <ul class="app-faq-list" style="display:none;">
        <?php foreach($single_faqs as $faq) : ?>
        <li>
            <h4 class="app-faq-title"><?php echo $faq['question']; ?></h4>
            <div class="app-faq-text"><?php echo $faq['answer']; ?></div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>