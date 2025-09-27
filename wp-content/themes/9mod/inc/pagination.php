<?php
if (!defined('ABSPATH')) exit;
function apktemplates_pagination($paged, $total_pages) {
	
	
	$prev_arrow =  'i__arrowleft';
	$next_arrow = 'i__arrowright';
	
    // Hide pagination if there is only one page
    if ($total_pages <= 1) {
        return '';
    }
	echo '<div class="navigation open" id="navcollapse">';
	echo '<div class="collapse">';
    echo '<div class="navigation_ext">';
    if ($paged > 1) {
        echo '<a href="'.get_pagenum_link($paged - 1).'"><svg class="' . $prev_arrow . '"><use xlink:href="#' . $prev_arrow . '"></use></svg><span class="vhide">Back</span></a>';
    } else {
        echo '<span><svg class="' . $prev_arrow . '"><use xlink:href="#' . $prev_arrow . '"></use></svg><span class="vhide">Back</span></span>';
    }

    echo '<div class="pages">';
    $last_displayed_page = 0;

    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == 1 || $i == $total_pages || ($i >= $paged - 1 && $i <= $paged + 1)) {
            if ($last_displayed_page != $i - 1) {
                echo '<span class="nav_ext">...</span>';
            }
            if ($i == $paged) {
                echo '<span class="current">'.$i.'</span>';
            } else {
                echo '<a href="'.get_pagenum_link($i).'">'.$i.'</a>';
            }
            $last_displayed_page = $i;
        }
    }

    echo '</div>';
    
    if ($paged < $total_pages) {
        echo '<a href="'.get_pagenum_link($paged + 1).'"><svg class="' . $next_arrow . '"><use xlink:href="#' . $next_arrow . '"></use></svg><span class="vhide">Next</span></a>';
    } else {
        echo '<span><svg class="' . $next_arrow . '"><use xlink:href="#' . $next_arrow . '"></use></svg><span class="vhide">Next</span></span>';
    }
    echo '</div>';
	echo '</div>';
	echo '<div class="navigation_in">';
	$next_page = $paged + 1;
    // Check if next page exists
    if ($next_page <= $total_pages) {
		echo '<div id="ajax-next-page" class="nav_more"><a href="' . get_pagenum_link($next_page) . '"><span class="btn btn-dark"><span class="uppercase fbold smf">More...</span></span></a></div>';
	}
	echo '<button id="navcollapse_btn" class="btn uppercase smf" type="button"><span class="fbold">Page '.$paged.'</span> of '.$total_pages.'</button>';
	echo '</div>';
	echo '</div>';
}
?>