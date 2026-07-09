<?php
/**
 * Dynamic block: Section title (configurable band).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register editor script for the Section title block.
 *
 * @return void
 */
function zskeleton_section_title_register_editor_script(): void {
	$path = trailingslashit( get_template_directory() ) . 'blocks/section-title/editor.js';
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_register_script(
		'zskeleton-section-title-block-editor',
		trailingslashit( get_template_directory_uri() ) . 'blocks/section-title/editor.js',
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
add_action( 'init', 'zskeleton_section_title_register_editor_script', 8 );

/**
 * Register the dynamic block type.
 *
 * @return void
 */
function zskeleton_register_section_title_block(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$path = trailingslashit( get_template_directory() ) . 'blocks/section-title';
	$json = $path . '/block.json';
	if ( ! is_readable( $json ) ) {
		return;
	}

	register_block_type(
		$path,
		array(
			'category' => 'layout',
		)
	);
}
add_action( 'init', 'zskeleton_register_section_title_block', 9 );

/**
 * Load Dashicons when the section title uses a title-row icon.
 *
 * @param string $block_content Block HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function zskeleton_section_title_enqueue_dashicons_on_render( $block_content, $block ) {
	if ( empty( $block['blockName'] ) || 'zskeleton/section-title' !== $block['blockName'] ) {
		return $block_content;
	}
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$icon  = isset( $attrs['titleDashicon'] ) ? sanitize_key( (string) $attrs['titleDashicon'] ) : '';
	if ( '' !== $icon ) {
		wp_enqueue_style( 'dashicons' );
	}
	return $block_content;
}
add_filter( 'render_block', 'zskeleton_section_title_enqueue_dashicons_on_render', 9, 2 );
