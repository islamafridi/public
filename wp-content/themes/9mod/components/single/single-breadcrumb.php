<?php
if (!defined('ABSPATH')) exit;
// Get the post categories
$post_categories = get_the_category(get_the_ID());

// Pre define parent & sub category ID's
$parent_cat_id = null;
$child_cat_id = null;

// Get the parent category id
foreach ($post_categories as $cat) {
    if ($cat->category_parent == 0) {
		//define parent category id
        $parent_cat_id = $cat->term_id;
        break;
    }
}

// Get the Sub category id
$child_cats = get_categories(array('parent' => $parent_cat_id, 'hide_empty' => false));
foreach ($child_cats as $child_cat) {
	if (in_category($child_cat->term_id)) {
		$child_cat_id = $child_cat->term_id;
		break;
	}
}

if($parent_cat_id) : ?>
<meta content="<?php echo get_cat_name($parent_cat_id); ?>">
<?php endif; if($child_cat_id) : ?>
<meta content="<?php echo get_cat_name($child_cat_id); ?>">
<?php endif; ?>

<?php breadcrumbsX(); ?>