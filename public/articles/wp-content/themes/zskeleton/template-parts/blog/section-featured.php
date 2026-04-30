<?php
/**
 * Featured posts strip (sticky-first, then recent fill).
 *
 * Optional: pass `zskeleton_block` (array) via get_template_part args:
 * - use_theme_count (bool) When false, use `post_count` (1–12) instead of the theme option.
 * - post_count (int)        Used when `use_theme_count` is false.
 * - section_heading (string) Optional section title; empty uses the filtered default.
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

$posts = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => $count,
		'no_found_rows'  => true,
	)
);

if ( empty( $posts ) ) {
	return;
}

$title = apply_filters( 'zskeleton_blog_hub_featured_title', __( 'Featured', 'zskeleton' ) );
if ( isset( $zsb['section_heading'] ) && '' !== trim( (string) $zsb['section_heading'] ) ) {
	$title = (string) $zsb['section_heading'];
}
?>

<section class="blog-hub-section blog-hub-featured" aria-labelledby="blog-hub-featured-heading">
	<div class="blog-hub-section__head">
		<h2 id="blog-hub-featured-heading" class="blog-hub-section__title"><?php echo esc_html( $title ); ?></h2>
	</div>
	<div class="blog-hub-featured__grid practices-grid">
		<?php
		foreach ( $posts as $post_obj ) {
			get_template_part( 'template-parts/blog/blog', 'card', array( 'post' => $post_obj ) );
		}
		?>
	</div>
</section>
