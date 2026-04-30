<?php
/**
 * Dynamic render: paginated latest posts grid for blog listing pages.
 *
 * @package ZSkeleton_Theme
 * @var array         $attributes Block attributes.
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$page_id = (int) get_queried_object_id();
$paged   = zskeleton_blog_hub_get_listing_paged();
$hub     = zskeleton_blog_hub_is_first_listing_page();

$match_exclude = ! isset( $attributes['matchThemeExcludeFeatured'] ) || $attributes['matchThemeExcludeFeatured'];
$overrides     = array();
if ( ! $match_exclude ) {
	$overrides['exclude_featured'] = false;
}
$pp = isset( $attributes['postsPerPage'] ) ? (int) $attributes['postsPerPage'] : 0;
if ( $pp > 0 ) {
	$overrides['posts_per_page'] = min( 50, max( 1, $pp ) );
}

$blog_query = zskeleton_blog_hub_main_posts_query( $overrides );

ob_start();

if ( $blog_query->have_posts() ) {
	$show_heading = ! isset( $attributes['showHeading'] ) || $attributes['showHeading'];
	$heading_text = isset( $attributes['heading'] ) ? trim( (string) $attributes['heading'] ) : '';
	if ( $show_heading && $hub ) {
		if ( '' === $heading_text ) {
			$heading_text = (string) apply_filters( 'zskeleton_blog_hub_latest_title', __( 'Latest articles', 'zskeleton' ) );
		}
		printf(
			'<h2 class="blog-latest-heading">%s</h2>',
			esc_html( $heading_text )
		);
	}

	echo '<div class="practices-grid">';
	while ( $blog_query->have_posts() ) {
		$blog_query->the_post();
		get_template_part( 'template-parts/blog/blog', 'card', array( 'post' => get_post() ) );
	}
	echo '</div>';

	echo wp_kses_post( zskeleton_blog_hub_pagination_html( $blog_query, $page_id, $paged ) );
} else {
	?>
	<div class="no-posts formal-card">
		<h2><?php esc_html_e( 'Nothing Found', 'zskeleton' ); ?></h2>
		<p><?php esc_html_e( 'It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'zskeleton' ); ?></p>
		<?php get_search_form(); ?>
	</div>
	<?php
}

wp_reset_postdata();

$inner = trim( (string) ob_get_clean() );

$cols  = isset( $attributes['columns'] ) ? (int) $attributes['columns'] : 0;
$grid  = 'zskeleton-block-blog zskeleton-block-blog-posts-grid';
if ( $cols >= 1 && $cols <= 4 ) {
	$grid .= ' zskeleton-blog-grid--cols-' . $cols;
}

$out = sprintf(
	'<div %s>%s</div>',
	get_block_wrapper_attributes(
		array(
			'class' => $grid,
		),
		'',
		isset( $block ) ? $block : null
	),
	$inner
);

if ( function_exists( 'zskeleton_blog_hub_stash_dynamic_blog_block_html' ) ) {
	zskeleton_blog_hub_stash_dynamic_blog_block_html( 'zskeleton/blog-posts-grid', $out );
}

return $out;
