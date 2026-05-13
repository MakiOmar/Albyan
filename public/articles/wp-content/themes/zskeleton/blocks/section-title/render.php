<?php
/**
 * Dynamic render: Section title band.
 *
 * @package ZSkeleton_Theme
 * @var array         $attributes Block attributes.
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'zskeleton_section_title_sanitize_css_size' ) ) {
	/**
	 * Allow safe CSS size fragments (width, padding, radius, min-height): clamp(), min(), max(), units, no url/var.
	 *
	 * @param mixed  $raw     Raw attribute.
	 * @param int    $max_len Max length.
	 * @param string $default When empty after sanitize.
	 * @return string
	 */
	function zskeleton_section_title_sanitize_css_size( $raw, $max_len, $default ) {
		$v = is_string( $raw ) ? trim( $raw ) : '';
		if ( '' === $v ) {
			return $default;
		}
		if ( strlen( $v ) > $max_len ) {
			return $default;
		}
		if ( preg_match( '/[<>\'\\\\`]|\/\*|\*\/|\\\\$/', $v ) ) {
			return $default;
		}
		if ( preg_match( '/\b(url|expression|javascript|import|@import|var)\s*\(/i', $v ) ) {
			return $default;
		}
		if ( ! preg_match( '/^[0-9a-zA-Z%,._+\s()\/:#-]+$/', $v ) ) {
			return $default;
		}
		return $v;
	}
}

if ( ! function_exists( 'zskeleton_section_title_hex_or_default' ) ) {
	/**
	 * @param mixed  $raw Raw color.
	 * @param string $fallback #rrggbb.
	 * @return string
	 */
	function zskeleton_section_title_hex_or_default( $raw, $fallback ) {
		$fb = is_string( $fallback ) && preg_match( '/^#[0-9A-Fa-f]{6}$/', $fallback ) ? $fallback : '#000000';
		if ( ! is_string( $raw ) ) {
			return $fb;
		}
		$raw = trim( $raw );
		if ( '' === $raw ) {
			return $fb;
		}
		if ( function_exists( 'maybe_hash_hex_color' ) ) {
			$raw = maybe_hash_hex_color( $raw );
		}
		$hex = sanitize_hex_color( $raw );
		return ( is_string( $hex ) && '' !== $hex ) ? $hex : $fb;
	}
}

$a = is_array( $attributes ) ? $attributes : array();

$title_html = isset( $a['title'] ) ? (string) $a['title'] : '';

$level = isset( $a['headingLevel'] ) ? (int) $a['headingLevel'] : 2;
if ( ! in_array( $level, array( 2, 3, 4 ), true ) ) {
	$level = 2;
}

$text_pos = isset( $a['textPosition'] ) ? sanitize_key( (string) $a['textPosition'] ) : 'center';
if ( ! in_array( $text_pos, array( 'left', 'center', 'right' ), true ) ) {
	$text_pos = 'center';
}

$container_w = zskeleton_section_title_sanitize_css_size( isset( $a['containerWidth'] ) ? $a['containerWidth'] : '', 120, 'min(1200px, 100%)' );
$padding     = zskeleton_section_title_sanitize_css_size( isset( $a['padding'] ) ? $a['padding'] : '', 120, 'clamp(1rem, 3vw, 2.5rem)' );
$radius      = zskeleton_section_title_sanitize_css_size( isset( $a['borderRadius'] ) ? $a['borderRadius'] : '', 80, '12px' );

$min_h_raw = isset( $a['minHeight'] ) ? $a['minHeight'] : '';
$min_h     = zskeleton_section_title_sanitize_css_size( $min_h_raw, 80, '' );

$bg = zskeleton_section_title_hex_or_default( isset( $a['backgroundColor'] ) ? $a['backgroundColor'] : '', '#f1f5f9' );
$fg = zskeleton_section_title_hex_or_default( isset( $a['textColor'] ) ? $a['textColor'] : '', '#0f172a' );

if ( 'center' === $text_pos ) {
	$margin_box = '0 auto';
} elseif ( 'right' === $text_pos ) {
	$margin_box = '0 0 0 auto';
} else {
	$margin_box = '0 auto 0 0';
}

$inner_style = sprintf(
	'box-sizing:border-box;max-width:%1$s;width:100%%;margin:%2$s;padding:%3$s;background-color:%4$s;color:%5$s;border-radius:%6$s;text-align:%7$s;',
	esc_attr( $container_w ),
	esc_attr( $margin_box ),
	esc_attr( $padding ),
	esc_attr( $bg ),
	esc_attr( $fg ),
	esc_attr( $radius ),
	esc_attr( $text_pos )
);
if ( '' !== $min_h ) {
	$inner_style .= 'min-height:' . esc_attr( $min_h ) . ';';
}

$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'zskeleton-section-title',
	),
	'',
	isset( $block ) ? $block : null
);

$tag         = 'h' . $level;
$title_inner = '' !== trim( wp_strip_all_tags( $title_html ) ) ? wp_kses_post( $title_html ) : '';
$heading     = '';
if ( function_exists( 'zskeleton_render_block_heading_title_row' ) ) {
	$heading = zskeleton_render_block_heading_title_row(
		array(
			'title_inner_html' => $title_inner,
			'heading_tag'      => $tag,
			'attributes'       => $a,
			'title_class'      => 'zskeleton-section-title__heading',
			'align'            => $text_pos,
			'heading_id'       => '',
		)
	);
}
if ( '' === $heading ) {
	$heading = sprintf(
		'<div class="zskeleton-block-heading__title-head zskeleton-block-heading--pos-%1$s"><div class="zskeleton-block-heading__title-text-wrap"><%2$s class="zskeleton-block-heading__title zskeleton-section-title__heading zskeleton-section-title__heading--empty"></%2$s></div></div>',
		esc_attr( $text_pos ),
		tag_escape( $tag )
	);
}

printf(
	'<div %1$s><div class="zskeleton-section-title__inner" style="%2$s">%3$s</div></div>',
	$wrapper, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	esc_attr( $inner_style ),
	$heading // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
);
