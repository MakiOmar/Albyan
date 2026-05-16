<?php
/**
 * Dynamic block: Feature promo card (icon strip, title, list, CTA).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register editor script for the Feature promo card block.
 *
 * @return void
 */
function zskeleton_feature_promo_card_register_editor_script(): void {
	$path = trailingslashit( get_template_directory() ) . 'blocks/feature-promo-card/editor.js';
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_register_script(
		'zskeleton-feature-promo-card-block-editor',
		trailingslashit( get_template_directory_uri() ) . 'blocks/feature-promo-card/editor.js',
		array(
			'wp-blocks',
			'wp-element',
			'wp-i18n',
			'wp-components',
			'wp-block-editor',
			'wp-server-side-render',
		),
		(string) filemtime( $path ),
		true
	);
}
add_action( 'init', 'zskeleton_feature_promo_card_register_editor_script', 8 );

/**
 * Register the dynamic block type.
 *
 * @return void
 */
function zskeleton_register_feature_promo_card_block(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$path = trailingslashit( get_template_directory() ) . 'blocks/feature-promo-card';
	$json = $path . '/block.json';
	if ( ! is_readable( $json ) ) {
		return;
	}

	register_block_type(
		$path,
		array(
			'category' => 'widgets',
		)
	);
}
add_action( 'init', 'zskeleton_register_feature_promo_card_block', 9 );

/**
 * Ensure Dashicons load when the block renders a Dashicon on the front.
 *
 * @param string $block_content Block HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function zskeleton_feature_promo_card_enqueue_dashicons_on_render( $block_content, $block ) {
	if ( empty( $block['blockName'] ) || 'zskeleton/feature-promo-card' !== $block['blockName'] ) {
		return $block_content;
	}
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$mode  = isset( $attrs['iconMode'] ) ? sanitize_key( (string) $attrs['iconMode'] ) : 'dashicon';
	$title_icon = isset( $attrs['titleDashicon'] ) ? sanitize_key( (string) $attrs['titleDashicon'] ) : '';
	if ( 'dashicon' === $mode || '' !== $title_icon ) {
		wp_enqueue_style( 'dashicons' );
	}
	return $block_content;
}
add_filter( 'render_block', 'zskeleton_feature_promo_card_enqueue_dashicons_on_render', 9, 2 );
