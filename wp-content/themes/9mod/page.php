<?php if (!defined('ABSPATH')) exit; ?>
<?php get_header(); ?>
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
                  <h1 class="title xxxlgf"><?php the_title(); ?></h1>
               </header>
               <div class="text"><?php the_content(); ?></div>
            </div>
         </article>
      </div>
   </div>
</div>
<?php get_template_part('template/footer'); get_footer(); ?>