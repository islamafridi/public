<?php
// Query for 8 most viewed posts
$post_args = [
    'orderby' => 'meta_value_num',
    'meta_key' => 'smt_post_views_count',
    'order' => 'DESC',
    'posts_per_page' => 8,
];
$most_viewed_posts = new WP_Query($post_args);

if ($most_viewed_posts->have_posts()) :
?>
    <div class="section section-sep">
        <div class="wrp">
            <div class="carousel-apps-out carousel-apps-js">
                <div id="carousel-most-viewed" class="carousel-apps flickity-enabled is-draggable" tabindex="0">
                    <?php
                    while ($most_viewed_posts->have_posts()) : $most_viewed_posts->the_post(); 
                    get_template_part('components/post-card/box-1');
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </div>
        </div>
    </div>
<script>
    jQuery(document).ready(function ($) {
        $('.carousel-apps').each(function() {
            var $carousel = $(this);
            var isMobile = window.innerWidth <= 768;

            $carousel.flickity({
                prevNextButtons: true,
                groupCells: 5,
                wrapAround: true,
                pageDots: false,
                groupCells: isMobile ? 1 : 5,
                dragThreshold: 10,
                cellAlign: isMobile ? 'center' : 'left'
            });
        });
    });
</script>
<?php endif; ?>
<style>
    @media (max-width: 768px) {
            #carousel-most-viewed .item {
                width: 100%; /* Show only one item on mobile */
                margin-right: 0;
            }

            /* Hide prev/next buttons on mobile */
            .flickity-prev-next-button {
                display: none;
            }
        }
</style>