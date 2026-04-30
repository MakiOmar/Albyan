<?php
/**
 * Dynamic render: blog featured strip.
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

$use_theme_count = ! isset( $attributes['useThemeCount'] ) || (bool) $attributes['useThemeCount'];
if ( ! $use_theme_count ) {
	$zsb['use_theme_count'] = false;
	$pc                     = isset( $attributes['postCount'] ) ? (int) $attributes['postCount'] : 3;
	$zsb['post_count']      = max( 1, min( 12, $pc ) );
}

if ( isset( $attributes['sectionHeading'] ) && '' !== trim( (string) $attributes['sectionHeading'] ) ) {
	$zsb['section_heading'] = sanitize_text_field( (string) $attributes['sectionHeading'] );
}

if ( isset( $attributes['useThemeVisibility'] ) && ! (bool) $attributes['useThemeVisibility'] ) {
	$zsb['ignore_theme_visibility'] = true;
}

ob_start();
if ( ! empty( $zsb ) ) {
	get_template_part( 'template-parts/blog/section', 'featured', array( 'zskeleton_block' => $zsb ) );
} else {
	get_template_part( 'template-parts/blog/section', 'featured' );
}
$html = trim( (string) ob_get_clean() );

if ( '' === $html ) {
	return '';
}

$out = sprintf(
	'<div %s>%s</div>',
	get_block_wrapper_attributes(
		array(
			'class' => 'zskeleton-block-blog zskeleton-block-blog-featured',
		),
		'',
		isset( $block ) ? $block : null
	),
	$html
);

if ( function_exists( 'zskeleton_blog_hub_stash_dynamic_blog_block_html' ) ) {
	zskeleton_blog_hub_stash_dynamic_blog_block_html( 'zskeleton/blog-featured', $out );
}

return $out;
