<?php
/**
 * Custom block: Expert profile CTA.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register editor script for Expert Profile block.
 *
 * @return void
 */
function zskeleton_expert_profile_block_register_editor_script(): void {
	$path = trailingslashit( get_template_directory() ) . 'blocks/expert-profile/editor.js';
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_register_script(
		'zskeleton-expert-profile-block-editor',
		trailingslashit( get_template_directory_uri() ) . 'blocks/expert-profile/editor.js',
		array(
			'wp-blocks',
			'wp-element',
			'wp-i18n',
			'wp-components',
			'wp-block-editor',
		),
		(string) filemtime( $path ),
		true
	);
}
add_action( 'init', 'zskeleton_expert_profile_block_register_editor_script', 8 );

/**
 * Register Expert Profile block.
 *
 * @return void
 */
function zskeleton_register_expert_profile_block(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$path = trailingslashit( get_template_directory() ) . 'blocks/expert-profile';
	$json = $path . '/block.json';
	if ( ! is_readable( $json ) ) {
		return;
	}

	register_block_type( $path );
}
add_action( 'init', 'zskeleton_register_expert_profile_block', 9 );

/**
 * Load Dashicons when this block renders (title icon uses dashicons classes).
 *
 * @param string $block_content Block HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function zskeleton_expert_profile_enqueue_dashicons_on_render( $block_content, $block ) {
	if ( ! empty( $block['blockName'] ) && 'zskeleton/expert-profile' === $block['blockName'] ) {
		wp_enqueue_style( 'dashicons' );
	}
	return $block_content;
}
add_filter( 'render_block', 'zskeleton_expert_profile_enqueue_dashicons_on_render', 9, 2 );
