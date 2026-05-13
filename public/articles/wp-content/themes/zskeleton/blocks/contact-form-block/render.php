<?php
/**
 * Dynamic render: Contact form block (Form Kit).
 *
 * @package ZSkeleton_Theme
 * @var array         $attributes Block attributes.
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$a = is_array( $attributes ) ? $attributes : array();

$show_heading = array_key_exists( 'showHeading', $a ) ? (bool) $a['showHeading'] : true;
$heading_raw  = isset( $a['heading'] ) ? (string) $a['heading'] : '';
$lead_raw     = isset( $a['lead'] ) ? (string) $a['lead'] : '';

$heading = '' !== trim( $heading_raw )
	? sanitize_text_field( $heading_raw )
	: __( 'Send a message', 'zskeleton' );

$lead = '' !== trim( $lead_raw )
	? sanitize_text_field( $lead_raw )
	: __( 'Share a few details and we’ll route your request to the right person.', 'zskeleton' );

$inner = '';

$inner .= '<div class="zs-contact-form-block__wrap formal-card elevated">';
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
	$form_html = ob_get_clean();
	$inner    .= $form_html;
} else {
	$inner .= '<p class="zs-contact-form-block__fallback">' . esc_html__( 'Contact form is unavailable.', 'zskeleton' ) . '</p>';
}
$inner .= '</div></div>';

$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'zs-contact-form-block',
	)
);

return sprintf( '<div %s>%s</div>', $wrapper, $inner );
