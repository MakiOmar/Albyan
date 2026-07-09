<?php
/**
 * Dynamic block: Contact form (Form Kit — zskeleton_contact).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register editor script for the Contact form block.
 *
 * @return void
 */
function zskeleton_contact_form_block_register_editor_script(): void {
	$path = trailingslashit( get_template_directory() ) . 'blocks/contact-form-block/editor.js';
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_register_script(
		'zskeleton-contact-form-block-editor',
		trailingslashit( get_template_directory_uri() ) . 'blocks/contact-form-block/editor.js',
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
add_action( 'init', 'zskeleton_contact_form_block_register_editor_script', 8 );

/**
 * Register the Contact form block type.
 *
 * @return void
 */
function zskeleton_register_contact_form_block(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$path = trailingslashit( get_template_directory() ) . 'blocks/contact-form-block';
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
add_action( 'init', 'zskeleton_register_contact_form_block', 9 );

/**
 * Fallback hydrate when a render pass yields empty contact block HTML.
 *
 * @param string $block_content Rendered content from current pass.
 * @param array  $block Parsed block data.
 * @return string
 */
function zskeleton_contact_form_hydrate_empty_render( $block_content, $block ) {
	$name = ( ! empty( $block['blockName'] ) && is_string( $block['blockName'] ) ) ? $block['blockName'] : '';
	if ( 'zskeleton/contact-form' !== $name || '' !== trim( (string) $block_content ) ) {
		return $block_content;
	}

	$attrs        = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$show_heading = array_key_exists( 'showHeading', $attrs ) ? (bool) $attrs['showHeading'] : true;
	$heading_raw  = isset( $attrs['heading'] ) ? (string) $attrs['heading'] : '';
	$lead_raw     = isset( $attrs['lead'] ) ? (string) $attrs['lead'] : '';

	$heading = '' !== trim( $heading_raw ) ? sanitize_text_field( $heading_raw ) : __( 'Send a message', 'zskeleton' );
	$lead    = '' !== trim( $lead_raw ) ? sanitize_text_field( $lead_raw ) : __( 'Share a few details and we’ll route your request to the right person.', 'zskeleton' );

	$inner = '<div class="zs-contact-form-block__wrap formal-card elevated">';
	if ( $show_heading ) {
		$inner .= '<div class="zs-contact-form-block__header">';
		$inner .= '<h2 class="zs-contact-form-block__title">' . esc_html( $heading ) . '</h2>';
		if ( '' !== trim( $lead ) ) {
			$inner .= '<p class="zs-contact-form-block__lead">' . esc_html( $lead ) . '</p>';
		}
		$inner .= '</div>';
	}
	$inner .= '<div class="zs-contact-form-block__inner">';
	if ( function_exists( 'zskeleton_render_form' ) ) {
		ob_start();
		zskeleton_render_form( 'zskeleton_contact' );
		$inner .= (string) ob_get_clean();
	} else {
		$inner .= '<p class="zs-contact-form-block__fallback">' . esc_html__( 'Contact form is unavailable.', 'zskeleton' ) . '</p>';
	}
	$inner .= '</div></div>';

	// render_block filter runs after block wrapper context may be cleared.
	// Build a safe fallback wrapper attribute string without block-support APIs.
	$wrapper_class = 'zs-contact-form-block';
	return sprintf(
		'<div class="%1$s">%2$s</div>',
		esc_attr( $wrapper_class ),
		$inner
	);
}
add_filter( 'render_block', 'zskeleton_contact_form_hydrate_empty_render', 2, 2 );
