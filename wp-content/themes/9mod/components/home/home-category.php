<?php
if (!defined('ABSPATH')) exit;

$terms = get_theme_mod('home_terms', []);
$counter = 1;

if (isset($terms) && !empty($terms)) :
    foreach ($terms as $term) :
        $button_id = 'sec-' . $counter;
        $term_title = $term['term_title'];
        $term_title_sanitized = str_replace(' ', '-', $term_title);
        $term_limit = $term['term_limit'];
        $term_id = $term['term_id'];
        $is_bottom_ad = $term['term_btm_ad'];
        $term_obj = get_term_by("id", $term_id, "category");
        if (!$term_obj) {
            $term_obj = get_term_by("id", $term_id, "post_tag");
        }

        $post_args = [
            "orderby" => "modified",
            "order" => "DESC",
            "posts_per_page" => $term_limit,
            "tax_query" => [
                "relation" => "AND",
            ],
        ];

        if ($term_obj) {
            $tax_query_item = [
                "taxonomy" => $term_obj->taxonomy,
                "field" => "id",
                "terms" => $term_obj->term_id,
            ];
            $post_args["tax_query"][] = $tax_query_item;
        }

        $term_posts = new WP_Query($post_args);

        $child_cats = get_categories(array('parent' => $term_id, 'hide_empty' => false));

        if ($term_posts->have_posts()) :
            // Create a unique translation key for each term
            $translation_key = 'term_title_' . $term_id . '_' . $term_title_sanitized;
?>
            <div class="section section-sep">
                <div class="wrp">
                    <div class="section-title fbold"><?php echo theme_translate($term_title, $translation_key); ?></div>
                    <div class="card-grid">
                        <?php
                        while ($term_posts->have_posts()) : $term_posts->the_post();
                            get_template_part('components/post-card/box-2');
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                </div>
            </div>
<?php 
        endif;
        $counter++;
    endforeach;
endif;
?>