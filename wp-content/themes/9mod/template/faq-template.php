<?php
if (!defined('ABSPATH')) exit;
/*
* Template Name: FAQ page template
* Template Post Type: page
*/
get_header();

$faqs = get_theme_mod('faqs', [
	[
		'question' => 'Is worth to buy 9mod Theme from 9mod.cc?',
		'answer' => 'Our demo site will said the answer to you.'
	],
	[
		'question' => 'Why themes are costlier in 9mod?',
		'answer' => 'Cheap quality only available on cheaper price. So we give perfect value to our themes based on theme quality of build.'
	],
	[
		'question' => 'Why other websites sold themes for low cost?',
		'answer' => 'Just compare our themes to others, then you will realize.'
	]
]); 
?>
<div class="page">
    <div class="wrp">
        <div class="content no-toolbar">
            <article class="post_view ignore-select" style="margin-bottom: 0">
				
				<div class="post_left">
                    <a class="btn-back sticky" href="<?php echo get_site_url(); ?>">
                        <svg class="i__arrowleft">
                            <use xlink:href="#i__arrowleft"></use>
                        </svg>
                    </a>
                </div>
				
                <div class="post_mid">
                    <header>
                        <h1 class="title xxxlgf"><?php _e('Questions and Answers', 'apktemplates'); ?></h1>
                    </header>
                    <?php if(!empty($faqs)) : ?>
                    <div class="text">
                        <div class="cd-faq-items">
                            <ul id="basics" class="cd-faq-group">
                                <?php foreach ( $faqs as $faq ) :
                                    if (empty($faq['question']) || empty($faq['answer'])) continue;
                                ?>
                                <li>
                                    <h3 class="cd-faq-trigger"><?php echo esc_html($faq['question']); ?></h3>
                                    <div class="cd-faq-content">
                                        <p><?php echo wp_kses_post($faq['answer']); ?></p>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <style>.post_content ul li:before{ content: ''; display: block; width: 6px !important; height: 6px !important; position: absolute; top: 10px; left: 4px; background-color: #fff; -webkit-border-radius: 8px; -moz-border-radius: 8px; border-radius: 8px;
                            }
                            .post_content ul li a { text-decoration: none; text-decoration: none; cursor: pointer;
                            }
                            .post_content a {font-size: 20px;}
                            .cd-faq-trigger{font-size:20px}
                        </style>
                    </div>
                    <?php endif; ?>
                </div>
            </article>
        </div>
    </div>
</div>
<?php get_template_part('template/footer'); get_footer(); ?>