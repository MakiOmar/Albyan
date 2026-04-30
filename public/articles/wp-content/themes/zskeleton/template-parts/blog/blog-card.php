<?php
/**
 * Compact card for blog hub sections (expects args from get_template_part).
 *
 * @package ZSkeleton_Theme
 *
 * @var array $args Arguments. Required: `post` (WP_Post).
 */

defined( 'ABSPATH' ) || exit;

$post = isset( $args['post'] ) && $args['post'] instanceof WP_Post ? $args['post'] : null;
if ( ! $post ) {
	return;
}

$user_id    = get_current_user_id();
$has_access = zskeleton_blog_hub_user_has_post_access( $user_id, $post->ID );
$category   = null;
$terms      = get_the_terms( $post, 'category' );
if ( is_array( $terms ) && ! empty( $terms ) ) {
	$category = $terms[0];
}
?>

<article id="post-<?php echo esc_attr( (string) $post->ID ); ?>" <?php post_class( 'content-card blog-hub-card', $post->ID ); ?>>
	<?php if ( ! $has_access && ! current_user_can( 'administrator' ) ) : ?>
		<div class="card-top">
			<span class="chip chip--locked"><?php esc_html_e( 'Members Only', 'zskeleton' ); ?></span>
		</div>
	<?php endif; ?>

	<div class="content-card-image">
		<a
			href="<?php echo esc_url( get_permalink( $post ) ); ?>"
			data-track="content_open"
			data-track-context="blog_card_image"
			data-post-id="<?php echo esc_attr( (string) $post->ID ); ?>"
		>
			<?php echo zskeleton_get_post_thumbnail_or_placeholder_html( $post, 'medium_large', array( 'loading' => 'lazy', 'decoding' => 'async', 'alt' => get_the_title( $post ) ) ); ?>
		</a>
	</div>

	<?php if ( $category instanceof WP_Term ) : ?>
		<?php $category_link = get_term_link( $category ); ?>
		<?php if ( ! is_wp_error( $category_link ) ) : ?>
		<p class="blog-hub-card__category">
			<a href="<?php echo esc_url( $category_link ); ?>"><?php echo esc_html( $category->name ); ?></a>
		</p>
		<?php endif; ?>
	<?php endif; ?>

	<h3>
		<a
			href="<?php echo esc_url( get_permalink( $post ) ); ?>"
			data-track="content_open"
			data-track-context="blog_card_title"
			data-post-id="<?php echo esc_attr( (string) $post->ID ); ?>"
		><?php echo esc_html( get_the_title( $post ) ); ?></a>
	</h3>

	<?php if ( $has_access ) : ?>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 22 ) ); ?></p>
	<?php else : ?>
		<p><?php esc_html_e( 'This content is available exclusively to ZSkeleton members. Join our membership to access our comprehensive library and professional resources.', 'zskeleton' ); ?></p>
	<?php endif; ?>

	<div class="card-meta">
		<span class="date"><?php echo esc_html( get_the_date( 'M j, Y', $post ) ); ?></span>
	</div>
</article>
