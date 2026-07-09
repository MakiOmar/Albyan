<?php
/**
 * Dynamic block: About company hero.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register editor script for About company hero block.
 *
 * @return void
 */
function zskeleton_about_company_hero_register_editor_script(): void {
	$path = trailingslashit( get_template_directory() ) . 'blocks/about-company-hero/editor.js';
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_register_script(
		'zskeleton-about-company-hero-block-editor',
		trailingslashit( get_template_directory_uri() ) . 'blocks/about-company-hero/editor.js',
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
add_action( 'init', 'zskeleton_about_company_hero_register_editor_script', 8 );

/**
 * Register About company hero block.
 *
 * @return void
 */
function zskeleton_register_about_company_hero_block(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$path = trailingslashit( get_template_directory() ) . 'blocks/about-company-hero';
	$json = $path . '/block.json';
	if ( ! is_readable( $json ) ) {
		return;
	}

	register_block_type( $path );
}
add_action( 'init', 'zskeleton_register_about_company_hero_block', 9 );

