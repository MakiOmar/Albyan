<?php
/**
 * Featured posts strip: blog-flagged posts, then sticky posts (no automatic newest fill).
 *
 * Optional: pass `zskeleton_block` (array) via get_template_part args:
 * - use_theme_count (bool) When false, use `post_count` (1–12) instead of the theme option.
 * - post_count (int)        Used when `use_theme_count` is false.
 * - section_heading (string) Optional section title; empty uses the filtered default.
 * - heading_attrs (array)   From blog blocks: Dashicon/accent/spacing ({@see zskeleton_blog_hub_heading_attrs_merge}); omit on classic PHP template.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

$zsb = array();
if ( isset( $args ) && is_array( $args ) && isset( $args['zskeleton_block'] ) && is_array( $args['zskeleton_block'] ) ) {
	$zsb = $args['zskeleton_block'];
} elseif ( isset( $zskeleton_block ) && is_array( $zskeleton_block ) ) {
	$zsb = $zskeleton_block;
}

$ignore_theme_section = ! empty( $zsb['ignore_theme_visibility'] );
if ( ! $ignore_theme_section && '1' !== (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_show_featured', '1' ) ) {
	return;
}

$use_theme_count = ! isset( $zsb['use_theme_count'] ) || (bool) $zsb['use_theme_count'];
$count           = (int) zskeleton_blog_hub_get_option( 'zskeleton_blog_featured_count', '3' );
$count           = max( 1, min( 12, $count ) );
if ( ! $use_theme_count && isset( $zsb['post_count'] ) && (int) $zsb['post_count'] > 0 ) {
	$count = max( 1, min( 12, (int) $zsb['post_count'] ) );
}

$ids = zskeleton_blog_hub_get_featured_post_ids( $count );
if ( empty( $ids ) ) {
	return;
}

$featured_posts = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => $count,
		'no_found_rows'  => true,
	)
);

if ( empty( $featured_posts ) ) {
	return;
}

$section_title = apply_filters( 'zskeleton_blog_hub_featured_title', __( 'Featured', 'zskeleton' ) );
if ( isset( $zsb['section_heading'] ) && '' !== trim( (string) $zsb['section_heading'] ) ) {
	$section_title = (string) $zsb['section_heading'];
}

$heading_attrs = isset( $zsb['heading_attrs'] ) && is_array( $zsb['heading_attrs'] ) ? $zsb['heading_attrs'] : null;
$heading_id    = 'blog-hub-featured-heading';

if ( null === $heading_attrs ) {
	$section_aria = ' aria-labelledby="' . esc_attr( $heading_id ) . '"';
} elseif ( ! empty( $heading_attrs['showHeading'] ) ) {
	$section_aria = ' aria-labelledby="' . esc_attr( $heading_id ) . '"';
} else {
	$section_aria = ' aria-label="' . esc_attr( wp_strip_all_tags( (string) $section_title ) ) . '"';
}
?>

<section class="blog-hub-section blog-hub-featured"<?php echo $section_aria; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
	<div class="blog-hub-featured__grid practices-grid">
		<?php
		foreach ( $featured_posts as $post_obj ) {
			get_template_part( 'template-parts/blog/blog', 'card', array( 'post' => $post_obj ) );
		}
		?>
	</div>
</section>
