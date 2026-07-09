<?php
/**
 * Block: testimonials image slider (dynamic).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register editor script and front assets for the testimonials image slider block.
 *
 * @return void
 */
function zskeleton_testimonials_image_slider_register_assets(): void {
	$base = trailingslashit( get_template_directory() );
	$uri  = trailingslashit( get_template_directory_uri() );

	$editor = $base . 'blocks/testimonials-image-slider/editor.js';
	if ( is_readable( $editor ) ) {
		wp_register_script(
			'zskeleton-testimonials-image-slider-editor',
			$uri . 'blocks/testimonials-image-slider/editor.js',
			array(
				'wp-blocks',
				'wp-element',
				'wp-i18n',
				'wp-components',
				'wp-block-editor',
				'wp-server-side-render',
			),
			(string) filemtime( $editor ),
			true
		);
	}

	$use_min = (bool) get_option( 'zskeleton_use_minified_assets', true );

	$css_file = ( $use_min && is_readable( $base . 'assets/css/testimonials-image-slider.min.css' ) )
		? 'testimonials-image-slider.min.css'
		: 'testimonials-image-slider.css';
	$css = $base . 'assets/css/' . $css_file;
	if ( is_readable( $css ) ) {
		wp_register_style(
			'zskeleton-testimonials-image-slider',
			$uri . 'assets/css/' . $css_file,
			array( 'zskeleton-block-heading' ),
			(string) filemtime( $css )
		);
	}

	$js_file = ( $use_min && is_readable( $base . 'assets/js/testimonials-image-slider.min.js' ) )
		? 'testimonials-image-slider.min.js'
		: 'testimonials-image-slider.js';
	$js = $base . 'assets/js/' . $js_file;
	if ( is_readable( $js ) ) {
		wp_register_script(
			'zskeleton-testimonials-image-slider-view',
			$uri . 'assets/js/' . $js_file,
			array(),
			(string) filemtime( $js ),
			true
		);
	}
}
add_action( 'init', 'zskeleton_testimonials_image_slider_register_assets', 8 );

/**
 * Register block type from block.json.
 *
 * @return void
 */
function zskeleton_register_testimonials_image_slider_block(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	$path = trailingslashit( get_template_directory() ) . 'blocks/testimonials-image-slider';
	$json = $path . '/block.json';
	if ( ! is_readable( $json ) ) {
		return;
	}
	register_block_type( $path );
}
add_action( 'init', 'zskeleton_register_testimonials_image_slider_block', 9 );

/**
 * Load Dashicons when the testimonials block uses a title icon.
 *
 * @param string $block_content Block HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function zskeleton_testimonials_image_slider_enqueue_dashicons_on_render( $block_content, $block ) {
	if ( empty( $block['blockName'] ) || 'zskeleton/testimonials-image-slider' !== $block['blockName'] ) {
		return $block_content;
	}
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$icon  = isset( $attrs['titleDashicon'] ) ? sanitize_key( (string) $attrs['titleDashicon'] ) : '';
	if ( '' !== $icon ) {
		wp_enqueue_style( 'dashicons' );
	}
	return $block_content;
}
add_filter( 'render_block', 'zskeleton_testimonials_image_slider_enqueue_dashicons_on_render', 9, 2 );
