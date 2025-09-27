<?php
if (!defined('ABSPATH')) exit;
$app_name = get_post_meta( get_the_ID(), 'wp_title_GP', true ) ?: get_the_title();
$rating_data = apkt_get_star_rating( get_the_ID() );
$app_required = get_post_meta( get_the_ID(), 'wp_requires_GP', true );
$app_version = get_post_meta( get_the_ID(), 'wp_version_GP', true );
$app_size = get_post_meta( get_the_ID(), 'wp_sizes_GP', true );
$content_rating = get_post_meta( get_the_ID(), 'wp_contentrated_GP', true );
$site_domain = parse_url( home_url(), PHP_URL_HOST );

$categories = get_the_category();
$parent_category_name = '';
foreach ( $categories as $category ) {
    if ( $category->parent != 0 ) {
        $parent = get_category( $category->parent );
        if ( ! is_wp_error( $parent ) ) {
            $parent_category_name = $parent->name;
            break;
        }
    } elseif ( empty( $parent_category_name ) ) {
        $parent_category_name = $category->name;
    }
}

$meta_description = get_post_meta( get_the_ID(), 'rank_math_description', true );
if ( empty( $meta_description ) ) {
    $meta_description = wp_trim_words( get_the_excerpt(), 25, '...' );
}

$date_published = get_the_date( 'c' ); 
$date_modified = get_the_modified_date( 'c' ); 
?>

<div id="rating-layer" data-post_id="<?php the_ID(); ?>" data-rated="false">
    <div class="rating flex">
        <ul class="unit-rating flex">
            <li class="current-rating" style="width: <?php echo $rating_data->rating_width; ?>">
                <?php echo $rating_data->rating_average; ?>
            </li>
            <li><a href="javascript:void(0);" title="Useless" class="r1-unit" onclick="doRate('1', '<?php the_ID(); ?>'); return false;">1</a></li>
            <li><a href="javascript:void(0);" title="Poor" class="r2-unit" onclick="doRate('2', '<?php the_ID(); ?>'); return false;">2</a></li>
            <li><a href="javascript:void(0);" title="Fair" class="r3-unit" onclick="doRate('3', '<?php the_ID(); ?>'); return false;">3</a></li>
            <li><a href="javascript:void(0);" title="Good" class="r4-unit" onclick="doRate('4', '<?php the_ID(); ?>'); return false;">4</a></li>
            <li><a href="javascript:void(0);" title="Excellent" class="r5-unit" onclick="doRate('5', '<?php the_ID(); ?>'); return false;">5</a>
            </li>
        </ul>
        <div class="rating-info">
            <span id="rating-info" class="text-gray-600 ml-2">
                <?php echo $rating_data->rating_average; ?>
                </span>
                (<span id="vote-num" class="count"><?php echo number_format( $rating_data->rating_votes, 0, ',', ',' ); ?></span>)
        </div>
    </div>
</div>

<?php
$schema = array(
    '@context' => 'https://schema.org',
    '@type' => 'SoftwareApplication',
    'headline' => esc_html( get_the_title() ),
    'name' => esc_html( $app_name ),
    'operatingSystem' => esc_html( sprintf( __( 'Android %s+', 'apktemplates' ), $app_required ) ),
    'applicationCategory' => esc_html( $parent_category_name ),
    'description' => esc_html( $meta_description ),
    'softwareVersion' => esc_html( $app_version ? 'v' . $app_version : '' ),
    'fileSize' => esc_html( $app_size ),
    'contentRating' => esc_html( $content_rating ),
    'datePublished' => esc_html( $date_published ),
    'dateModified' => esc_html( $date_modified ),
    'author' => array(
        '@type' => 'Person',
        'name' => esc_html( $site_domain ) . ' Team',
    ),
    'thumbnailUrl' => esc_url( get_the_post_thumbnail_url() ),
    'inLanguage' => 'en-US',
    'aggregateRating' => array(
        '@type' => 'AggregateRating',
        'bestRating' => '5',
        'ratingValue' => esc_html( $rating_data->rating_average ),
        'ratingCount' => esc_html( number_format( $rating_data->rating_votes, 0, '', '' ) ),
        'worstRating' => '1',
    ),
    'offers' => array(
        '@type' => 'Offer',
        'price' => '0',
        'priceCurrency' => 'USD',
    ),
);
?>
<script type="application/ld+json">
<?php echo wp_json_encode( $schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ); ?>
</script>