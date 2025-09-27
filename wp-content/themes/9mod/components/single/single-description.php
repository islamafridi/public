<?php
if (!defined('ABSPATH')) exit;
$sp_hide_content_swt = get_theme_mod('sp_hide_content_swt', false);
?>
<div class="description ">
    <div id="spoiler" class="spoiler prose-lg <?php echo $sp_hide_content_swt ? '' : 'open'; ?>">
        <?php the_content(); ?>
    </div>
    <button id="spoiler-btn" class="btn btn-sm spoiler-btn uppercase fbold" type="button">
        <svg class="i__more" xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 24 24">
            <g>
                <circle fill="none" cx="12" cy="12" r="12"></circle>
                <circle fill="none" style="stroke:currentColor;stroke-width:2;" cx="12" cy="12" r="11"></circle>
            </g>
            <circle class="i__more_c1" fill="currentColor" cx="8" cy="12" r="1"></circle>
            <circle class="i__more_c2" fill="currentColor" cx="12" cy="12" r="1"></circle>
            <circle class="i__more_c3" fill="currentColor" cx="16" cy="12" r="1"></circle>
        </svg>
            <span class="spoiler-btn-o">
                
            <?php 
            // Add this temporarily to see what's happening

            theme_translate('View More', 'View_More'); ?></span>
            <span class="spoiler-btn-h">
                <?php theme_translate('Hide', 'Hide'); ?>
            </span> 
    </button>
</div>