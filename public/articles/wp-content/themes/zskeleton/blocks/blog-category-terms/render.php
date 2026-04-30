<?php
/**
 * Dynamic render: blog category term grid.
 *
 * @package ZSkeleton_Theme
 * @var array         $attributes Block attributes.
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

if ( ! zskeleton_blog_hub_is_first_listing_page() ) {
	return '';
}

$layout   = isset( $attributes['layout'] ) ? sanitize_key( (string) $attributes['layout'] ) : '';
$max      = isset( $attributes['maxTerms'] ) ? (int) $attributes['maxTerms'] : 0;
$cat_args = array();
if ( '' !== $layout && in_array( $layout, array( 'simple', 'icons', 'thumbnails' ), true ) ) {
	$cat_args['layout'] = $layout;
}
if ( $max > 0 ) {
	$cat_args['max_terms'] = $max;
}
if ( isset( $attributes['showSectionHeading'] ) && ! (bool) $attributes['showSectionHeading'] ) {
	$cat_args['show_section_heading'] = false;
} elseif ( isset( $attributes['sectionHeading'] ) && '' !== trim( (string) $attributes['sectionHeading'] ) ) {
	$cat_args['heading'] = sanitize_text_field( (string) $attributes['sectionHeading'] );
}
if ( isset( $attributes['useThemeVisibility'] ) && ! (bool) $attributes['useThemeVisibility'] ) {
	$cat_args['bypass_theme_category_visibility'] = true;
}

if ( ! empty( $attributes['categoryIds'] ) && is_array( $attributes['categoryIds'] ) ) {
	$ids = array_values(
		array_filter(
			array_map(
				static function ( $v ) {
					$n = absint( $v );
					return $n > 0 ? $n : 0;
				},
				$attributes['categoryIds']
			)
		)
	);
	if ( ! empty( $ids ) ) {
		$cat_args['include_term_ids'] = $ids;
	}
}

ob_start();
zskeleton_blog_hub_render_category_terms_listing( $cat_args );
$html = trim( (string) ob_get_clean() );

if ( '' === $html ) {
	return '';
}

$out = sprintf(
	'<div %s>%s</div>',
	get_block_wrapper_attributes(
		array(
			'class' => 'zskeleton-block-blog zskeleton-block-blog-category-terms',
		),
		'',
		isset( $block ) ? $block : null
	),
	$html
);

if ( function_exists( 'zskeleton_blog_hub_stash_dynamic_blog_block_html' ) ) {
	zskeleton_blog_hub_stash_dynamic_blog_block_html( 'zskeleton/blog-category-terms', $out );
}

return $out;
