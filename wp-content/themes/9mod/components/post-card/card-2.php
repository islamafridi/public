<?php
if (!defined('ABSPATH')) exit;

/* For archive page articles and news */
?>
<article class="post">
    <div class="img"><i class="cover" style="background-image: url(<?php the_post_thumbnail_url(); ?>);"></i></div>
    <div class="cont">
        <div class="xsmf meta muted"><time class="date" datetime="<?php the_modified_time('c'); ?>"><?php the_modified_time('d-m-Y, H:i'); ?></time></div>
        <h2 class="lgf title"><a class="noline" href="<?php the_permalink(); ?>"><span><?php the_title(); ?></span></a></h2>
    </div>
</article>