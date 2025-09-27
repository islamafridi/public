<?php
if (!defined('ABSPATH')) exit;
/* For Archive  posts box */
$app_name = get_post_meta(get_the_ID(), 'wp_title_GP', true);
$app_name = $app_name ?: get_the_title();
$app_developer = get_post_meta(get_the_ID(), 'wp_developers_GP', true);
$is_mod = get_post_meta(get_the_ID(), 'mod-tick-box', true);
$rating_data = apkt_get_star_rating(get_the_ID());
$app_size = get_post_meta(get_the_ID(), 'wp_sizes_GP', true);
$app_version = get_post_meta(get_the_ID(), 'wp_version_GP', true);
?>
<a href="<?php the_permalink(); ?>" class="card" title="<?php echo $app_name; ?>">
  <div class="thumb">
    <img class="blur-up lazyload" src="<?php the_post_thumbnail_url('thumbnail'); ?>" alt="<?php echo $app_name; ?>" loading="lazy">
    <div class="mod-badge">
      <?php if ($is_mod) echo '<span>MOD</span>'; ?>
    </div>
  </div>

  <div class="content">
      <h3>
        <div class="truncate"><?php echo $app_name; ?></div>
        <?php
        $updated_timestamp = get_the_modified_time('U');
        $some_days_ago = strtotime('-30 days');
        if ($updated_timestamp > $some_days_ago) :
        ?>
            <span class="updated">Updated</span>
        <?php endif; ?>
      </h3>
    <div class="meta flex items-center truncate toolsbar category text-gray-400">
      <span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-[var(--post-color)] mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </span>
      <span>

        <?php
        $post_categories = get_the_category(get_the_ID());
        if (!empty($post_categories)) {
          foreach ($post_categories as $category) {
            // Check if the category is a child (has a parent)
            if ($category->category_parent != 0) {
              // Display child category name
              echo '<div class="text-gray-600 text-sm child-category">' . esc_html($category->name) . '</div>';
            }
          }
        }
        ?>
      </span>


    </div>
    <div class="version-info">
      <span class="mod-type text-sm">

      </span>
    </div>
    <div class="flex items-center truncate toolsbar">
      <div class="flex items-center">
        <svg class="text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        <span class="text-xs sm:text-sm text-gray-600 truncate">v<?php echo $app_version; ?></span>
      </div>
      <div class="flex items-center">
        <svg class="text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v-.228a4.5 4.5 0 0 0-.12-1.03l-2.268-9.64a3.375 3.375 0 0 0-3.285-2.602H7.923a3.375 3.375 0 0 0-3.285 2.602l-2.268 9.64a4.5 4.5 0 0 0-.12 1.03v.228m19.5 0a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3m19.5 0a3 3 0 0 0-3-3H5.25a3 3 0 0 0-3 3m16.5 0h.008v.008h-.008v-.008Zm-3 0h.008v.008h-.008v-.008Z"></path>
        </svg>
        <span class="text-xs text-gray-600 truncate"><?php echo $app_size; ?></span>
      </div>
    </div>

  </div>
</a>