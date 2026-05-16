<?php
/**
 * Block: case studies split testimonial (dynamic).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register assets for the Case study split block.
 *
 * @return void
 */
function zskeleton_case_studies_split_register_assets(): void {
	$base = trailingslashit( get_template_directory() );
	$uri  = trailingslashit( get_template_directory_uri() );

	$editor = $base . 'blocks/case-studies-split/editor.js';
	if ( is_readable( $editor ) ) {
		wp_register_script(
			'zskeleton-case-studies-split-editor',
			$uri . 'blocks/case-studies-split/editor.js',
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

	$css_file = ( $use_min && is_readable( $base . 'assets/css/case-studies-split.min.css' ) )
		? 'case-studies-split.min.css'
		: 'case-studies-split.css';
	$css = $base . 'assets/css/' . $css_file;
	if ( is_readable( $css ) ) {
		wp_register_style(
			'zskeleton-case-studies-split',
			$uri . 'assets/css/' . $css_file,
			array( 'zskeleton-block-heading' ),
			(string) filemtime( $css )
		);
	}
}
add_action( 'init', 'zskeleton_case_studies_split_register_assets', 8 );

/**
 * Register block type from metadata.
 *
 * @return void
 */
function zskeleton_register_case_studies_split_block(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	$path = trailingslashit( get_template_directory() ) . 'blocks/case-studies-split';
	$json = $path . '/block.json';
	if ( ! is_readable( $json ) ) {
		return;
	}
	register_block_type( $path );
}
add_action( 'init', 'zskeleton_register_case_studies_split_block', 9 );

/**
 * Dashicons used by optional heading icon.
 *
 * @param string $block_content Rendered markup.
 * @param array  $block         Parsed block.
 * @return string
 */
function zskeleton_case_studies_split_maybe_enqueue_dashicons( string $block_content, array $block ): string {
	if ( empty( $block['blockName'] ) || 'zskeleton/case-studies-split' !== $block['blockName'] ) {
		return $block_content;
	}
	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	if ( '' !== ( isset( $attrs['titleDashicon'] ) ? (string) $attrs['titleDashicon'] : '' ) ) {
		wp_enqueue_style( 'dashicons' );
	}
	return $block_content;
}
add_filter( 'render_block', 'zskeleton_case_studies_split_maybe_enqueue_dashicons', 10, 2 );

/**
 * Fallback hydrate when a render pass yields empty case-studies HTML.
 *
 * Mirrors the contact-form block strategy for the same "empty after render callback" behavior.
 *
 * @param string $block_content Rendered content from current pass.
 * @param array  $block Parsed block data.
 * @return string
 */
function zskeleton_case_studies_split_hydrate_empty_render( $block_content, $block ) {
	$name = ( ! empty( $block['blockName'] ) && is_string( $block['blockName'] ) ) ? $block['blockName'] : '';
	if ( 'zskeleton/case-studies-split' !== $name || '' !== trim( (string) $block_content ) ) {
		return $block_content;
	}

	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

	$render_file = trailingslashit( get_template_directory() ) . 'blocks/case-studies-split/render.php';
	$hydrated    = '';
	if ( is_readable( $render_file ) ) {
		$attributes = $attrs;
		$block      = null;
		$maybe      = include $render_file;
		$hydrated   = is_string( $maybe ) ? $maybe : '';
	}

	if ( '' !== trim( $hydrated ) ) {
		return $hydrated;
	}

	$title = isset( $attrs['sectionTitle'] ) ? sanitize_text_field( (string) $attrs['sectionTitle'] ) : '';
	$desc  = isset( $attrs['sectionDescription'] ) ? sanitize_text_field( (string) $attrs['sectionDescription'] ) : '';

	return sprintf(
		'<section class="%1$s"><div class="zskeleton-cssplit__inner"><header class="zskeleton-cssplit__header">%2$s%3$s</header></div></section>',
		esc_attr( 'wp-block-zskeleton-case-studies-split zskeleton-cssplit' ),
		'' !== $title ? '<h2 class="zskeleton-cssplit__heading">' . esc_html( $title ) . '</h2>' : '',
		'' !== $desc ? '<p class="zskeleton-cssplit__intro">' . esc_html( $desc ) . '</p>' : ''
	);
}
add_filter( 'render_block', 'zskeleton_case_studies_split_hydrate_empty_render', 2, 2 );
