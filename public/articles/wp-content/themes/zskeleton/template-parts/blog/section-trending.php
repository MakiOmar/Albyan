<?php
/**
 * Trending / most-read style list (comments or simple view meta).
 *
 * Optional: pass `zskeleton_block` (array) via get_template_part args:
 * - use_theme_count     (bool)  When false, use `post_count` (1–12) from the block.
 * - use_theme_mode      (bool)  When false, use `ranking_mode` (comments|views).
 * - post_count          (int)
 * - ranking_mode        (string) comments|views
 * - section_heading     (string) Optional title.
 * - ignore_theme_visibility (bool) When true, skip the theme “show trending” check (block-only).
 * - heading_attrs (array)   From blog blocks: title row styling ({@see zskeleton_blog_hub_heading_attrs_merge}); omit on classic PHP template.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

$zsb = isset( $zskeleton_block ) && is_array( $zskeleton_block ) ? $zskeleton_block : array();

$ignore_theme_section = ! empty( $zsb['ignore_theme_visibility'] );
if ( ! $ignore_theme_section && '1' !== (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_show_trending', '1' ) ) {
	return;
}

$use_theme_count = ! isset( $zsb['use_theme_count'] ) || (bool) $zsb['use_theme_count'];
$count           = (int) zskeleton_blog_hub_get_option( 'zskeleton_blog_trending_count', '5' );
$count           = max( 1, min( 12, $count ) );
if ( ! $use_theme_count && isset( $zsb['post_count'] ) && (int) $zsb['post_count'] > 0 ) {
	$count = max( 1, min( 12, (int) $zsb['post_count'] ) );
}

$use_theme_mode  = ! isset( $zsb['use_theme_mode'] ) || (bool) $zsb['use_theme_mode'];
$trending_mode   = zskeleton_sanitize_blog_trending_mode( zskeleton_blog_hub_get_option( 'zskeleton_blog_trending_mode', 'comments' ) );
if ( ! $use_theme_mode && ! empty( $zsb['ranking_mode'] ) ) {
	$rm = sanitize_key( (string) $zsb['ranking_mode'] );
	if ( in_array( $rm, array( 'comments', 'views' ), true ) ) {
		$trending_mode = $rm;
	}
}

$qargs = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $count,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);

if ( 'views' === $trending_mode && '1' === (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_track_post_views', '1' ) ) {
	$qargs['meta_key']   = ZSKELETON_BLOG_POST_VIEWS_META;
	$qargs['orderby']    = 'meta_value_num';
	$qargs['order']      = 'DESC';
	$qargs['meta_query'] = array(
		array(
			'key'     => ZSKELETON_BLOG_POST_VIEWS_META,
			'value'   => 0,
			'compare' => '>',
			'type'    => 'NUMERIC',
		),
	);
} else {
	$qargs['orderby'] = 'comment_count';
	$qargs['order']   = 'DESC';
}

$trending = new WP_Query( $qargs );

if ( ! $trending->have_posts() && 'views' === $trending_mode ) {
	$trending = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'orderby'             => 'comment_count',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
}

if ( ! $trending->have_posts() ) {
	return;
}

$section_title = 'views' === $trending_mode
	? apply_filters( 'zskeleton_blog_hub_trending_title_views', __( 'Most read', 'zskeleton' ) )
	: apply_filters( 'zskeleton_blog_hub_trending_title_comments', __( 'Trending', 'zskeleton' ) );
if ( isset( $zsb['section_heading'] ) && '' !== trim( (string) $zsb['section_heading'] ) ) {
	$section_title = (string) $zsb['section_heading'];
}

$heading_attrs = isset( $zsb['heading_attrs'] ) && is_array( $zsb['heading_attrs'] ) ? $zsb['heading_attrs'] : null;
$heading_id    = 'blog-hub-trending-heading';

if ( null === $heading_attrs ) {
	$section_aria = ' aria-labelledby="' . esc_attr( $heading_id ) . '"';
} elseif ( ! empty( $heading_attrs['showHeading'] ) ) {
	$section_aria = ' aria-labelledby="' . esc_attr( $heading_id ) . '"';
} else {
	$section_aria = ' aria-label="' . esc_attr( wp_strip_all_tags( (string) $section_title ) ) . '"';
}
?>

<section class="blog-hub-section blog-hub-trending"<?php echo $section_aria; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	if ( null === $heading_attrs ) {
		?>
		<!-- Classic template: plain section title -->
		<div class="blog-hub-section__head">
			<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="blog-hub-section__title"><?php echo esc_html( $section_title ); ?></h2>
		</div>
		<?php
	} elseif ( ! empty( $heading_attrs['showHeading'] ) && function_exists( 'zskeleton_render_block_heading_title_row' ) ) {
		echo zskeleton_render_block_heading_title_row( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'title_inner_html' => esc_html( $section_title ),
				'heading_tag'      => 'h2',
				'attributes'       => zskeleton_blog_hub_heading_attrs_for_title_row( $heading_attrs ),
				'title_class'      => 'blog-hub-section__title blog-latest-heading',
				'align'            => 'left',
				'heading_id'       => $heading_id,
				'listing_gap_px'   => (int) ( isset( $heading_attrs['titleListingGapPx'] ) ? $heading_attrs['titleListingGapPx'] : 20 ),
			)
		);
	} elseif ( ! empty( $heading_attrs['showHeading'] ) ) {
		?>
		<div class="blog-hub-section__head">
			<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="blog-hub-section__title"><?php echo esc_html( $section_title ); ?></h2>
		</div>
		<?php
	}
	?>
	<div class="blog-hub-trending__grid practices-grid">
		<?php
		while ( $trending->have_posts() ) {
			$trending->the_post();
			get_template_part( 'template-parts/blog/blog', 'card', array( 'post' => get_post() ) );
		}
		wp_reset_postdata();
		?>
	</div>
</section>
