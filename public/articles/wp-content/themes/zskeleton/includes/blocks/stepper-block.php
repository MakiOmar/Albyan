<?php
/**
 * Dynamic block: Stepper.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register editor script for the Stepper block.
 *
 * @return void
 */
function zskeleton_stepper_register_editor_script(): void {
	$path = trailingslashit( get_template_directory() ) . 'blocks/stepper/editor.js';
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_register_script(
		'zskeleton-stepper-block-editor',
		trailingslashit( get_template_directory_uri() ) . 'blocks/stepper/editor.js',
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
add_action( 'init', 'zskeleton_stepper_register_editor_script', 8 );

/**
 * Register Stepper block type from metadata.
 *
 * @return void
 */
function zskeleton_register_stepper_block(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$path = trailingslashit( get_template_directory() ) . 'blocks/stepper';
	$json = $path . '/block.json';
	if ( ! is_readable( $json ) ) {
		return;
	}

	register_block_type( $path );
}
add_action( 'init', 'zskeleton_register_stepper_block', 9 );
