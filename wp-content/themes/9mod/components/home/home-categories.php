<?php if (!defined('ABSPATH')) exit; ?>
<div class="section section-sep">
    <div class="wrp">
<?php
$parent_cats = get_categories(array(
    'parent' => 0,
    'hide_empty' => true, // ✅ Hide parent categories with no posts
));

foreach ($parent_cats as $parent_cat) {
    // Get children with posts
    $child_cats = get_categories(array(
        'parent' => $parent_cat->term_id,
        'hide_empty' => true, // ✅ Hide child categories with no posts
    ));

    // Show parent only if it has children
    if (!empty($child_cats)) {
        echo '<div class="category-box shadow-sm p-6">';
        echo '<h3>' . esc_html($parent_cat->name) . ' - Categories</h3>';
        echo '<div class="category-list">';
        foreach ($child_cats as $child_cat) {
            $child_link = get_category_link($child_cat->term_id);
            echo '<a class="category-item" href="' . esc_url($child_link) . '">' . esc_html($child_cat->name) . '</a>';
        }
        echo '</div></div>';
    }
}
?>
    </div>
</div>
