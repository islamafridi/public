<?php
/**
* Displays the translations fields for posts
 * @since 1.3.24 div in this file
*/

if ( ! defined( 'ABSPATH' ) ) {exit;} // Don't access directly

$admin_links = new \Falang\Core\Admin_Links();

add_thickbox();

?>
<div id="meta-post-translations" style="<?php echo empty($locale)?'':'display:none';?>">
    <p><strong><?php esc_html_e( 'Translations', 'falang' ); ?></strong></p>
    <?php
        echo $admin_links->display_post_translation_link_table($post_id);
    ?>

</div>