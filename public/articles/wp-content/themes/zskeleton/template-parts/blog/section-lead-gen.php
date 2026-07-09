<?php
/**
 * Lead generation / CTA band for the blog listing template.
 *
 * Optional: pass `zskeleton_block` (array) via get_template_part args:
 * - use_theme_lead  (bool)   When true (default), read copy from ZSkeleton Content options.
 * - title, text, button_text, button_url — used when `use_theme_lead` is false.
 * - ignore_theme_visibility (bool) When true, skip the theme “show lead block” check.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

$zsb = isset( $zskeleton_block ) && is_array( $zskeleton_block ) ? $zskeleton_block : array();

$use_theme = ! isset( $zsb['use_theme_lead'] ) || (bool) $zsb['use_theme_lead'];
$ignore    = ! empty( $zsb['ignore_theme_visibility'] );

if ( ! $ignore && '1' !== (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_show_lead_block', '1' ) ) {
	return;
}

if ( $use_theme ) {
	$raw_title = (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_lead_title', '' );
	$raw_text  = (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_lead_text', '' );
	$raw_btn   = (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_lead_button_text', '' );
	$url       = (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_lead_button_url', '' );
} else {
	$raw_title = isset( $zsb['title'] ) ? (string) $zsb['title'] : '';
	$raw_text  = isset( $zsb['text'] ) ? (string) $zsb['text'] : '';
	$raw_btn   = isset( $zsb['button_text'] ) ? (string) $zsb['button_text'] : '';
	$url       = isset( $zsb['button_url'] ) ? (string) $zsb['button_url'] : '';
}

if ( '' === trim( $raw_title ) && '' === trim( $raw_text ) && '' === trim( $raw_btn ) && '' === trim( $url ) ) {
	return;
}

$title = '' !== trim( $raw_title ) ? $raw_title : __( 'Stay ahead with our newsletter', 'zskeleton' );
$text  = '' !== trim( $raw_text ) ? $raw_text : __( 'Get practical guides and updates in your inbox. No spam — unsubscribe anytime.', 'zskeleton' );
$btn   = '' !== trim( $raw_btn ) ? $raw_btn : __( 'Subscribe', 'zskeleton' );
?>

<section class="blog-hub-section blog-hub-lead" aria-labelledby="blog-hub-lead-heading">
	<div class="blog-hub-lead__inner">
		<div class="blog-hub-lead__copy">
			<?php if ( '' !== trim( $title ) ) : ?>
				<h2 id="blog-hub-lead-heading" class="blog-hub-lead__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( '' !== trim( $text ) ) : ?>
				<p class="blog-hub-lead__text"><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( '' !== trim( $url ) && '' !== trim( $btn ) ) : ?>
			<div class="blog-hub-lead__cta">
				<a
					class="btn btn-primary blog-hub-lead__btn"
					href="<?php echo esc_url( $url ); ?>"
					data-track="cta_click"
					data-track-context="blog_hub_lead"
					data-track-label="<?php echo esc_attr( sanitize_title( $btn ) ); ?>"
				><?php echo esc_html( $btn ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
