<?php
/**
 * Dynamic render: theme slider block.
 *
 * @package ZSkeleton_Theme
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$raw_id = isset( $attributes['sliderId'] ) ? $attributes['sliderId'] : 0;
// REST/JSON may pass numeric strings; coerce safely.
$slider_id = is_numeric( $raw_id ) ? (int) $raw_id : 0;
$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'zskeleton-block-theme-slider',
	)
);

if ( $slider_id < 1 ) {
	return sprintf(
		'<div %1$s><p class="zskeleton-block-theme-slider__notice">%2$s</p></div>',
		$wrapper,
		esc_html__( 'Select a slider in block settings.', 'zskeleton' )
	);
}

$markup = zskeleton_slider_markup( $slider_id, 'zskeleton-block-theme-slider__slider' );
if ( '' === $markup ) {
	return sprintf(
		'<div %1$s><p class="zskeleton-block-theme-slider__notice">%2$s</p></div>',
		$wrapper,
		esc_html__( 'The selected slider is unavailable or has no slides.', 'zskeleton' )
	);
}

return sprintf(
	'<div %1$s>%2$s</div>',
	$wrapper,
	$markup
);
