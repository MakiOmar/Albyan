<?php
/**
 * Dynamic render: blog lead CTA band.
 *
 * @package ZSkeleton_Theme
 * @var array         $attributes Block attributes.
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

if ( ! zskeleton_blog_hub_is_first_listing_page() ) {
	return '';
}

$zsb = array();

$use_theme = ! isset( $attributes['useThemeCopy'] ) || (bool) $attributes['useThemeCopy'];
if ( ! $use_theme ) {
	$zsb['use_theme_lead'] = false;
	if ( isset( $attributes['leadTitle'] ) ) {
		$zsb['title'] = (string) $attributes['leadTitle'];
	}
	if ( isset( $attributes['leadText'] ) ) {
		$zsb['text'] = (string) $attributes['leadText'];
	}
	if ( isset( $attributes['buttonText'] ) ) {
		$zsb['button_text'] = (string) $attributes['buttonText'];
	}
	if ( isset( $attributes['buttonUrl'] ) ) {
		$zsb['button_url'] = (string) $attributes['buttonUrl'];
	}
}

if ( isset( $attributes['useThemeVisibility'] ) && ! (bool) $attributes['useThemeVisibility'] ) {
	$zsb['ignore_theme_visibility'] = true;
}

ob_start();
if ( ! empty( $zsb ) ) {
	get_template_part( 'template-parts/blog/section', 'lead-gen', array( 'zskeleton_block' => $zsb ) );
} else {
	get_template_part( 'template-parts/blog/section', 'lead-gen' );
}
$html = trim( (string) ob_get_clean() );

if ( '' === $html ) {
	return '';
}

$out = sprintf(
	'<div %s>%s</div>',
	get_block_wrapper_attributes(
		array(
			'class' => 'zskeleton-block-blog zskeleton-block-blog-lead-gen',
		),
		'',
		isset( $block ) ? $block : null
	),
	$html
);

if ( function_exists( 'zskeleton_blog_hub_stash_dynamic_blog_block_html' ) ) {
	zskeleton_blog_hub_stash_dynamic_blog_block_html( 'zskeleton/blog-lead-gen', $out );
}

return $out;
