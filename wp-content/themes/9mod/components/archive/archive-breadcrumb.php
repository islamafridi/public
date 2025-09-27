<?php
if (!defined('ABSPATH')) exit;
get_header();

$current_category = get_queried_object();
?>
<div class="wrp">
<div class="toolbar">
    <div class="breadcrumbs xsmf">
        <?php breadcrumbsX(); ?>
    </div>
</div>
</div>