<?php
/**
 * Shared section heading: optional Dashicon + title + accent bar (same pattern as Expert Profile CTA).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register front stylesheet for shared heading row (enqueued when a block renders the row).
 *
 * @return void
 */
function zskeleton_register_block_heading_shared_style(): void {
	$base    = trailingslashit( get_template_directory() );
	$uri     = trailingslashit( get_template_directory_uri() );
	$use_min = (bool) get_option( 'zskeleton_use_minified_assets', true );
	$file    = ( $use_min && is_readable( $base . 'assets/css/zskeleton-block-heading.min.css' ) )
		? 'zskeleton-block-heading.min.css'
		: 'zskeleton-block-heading.css';
	$path = $base . 'assets/css/' . $file;
	if ( ! is_readable( $path ) ) {
		return;
	}
	wp_register_style(
		'zskeleton-block-heading',
		$uri . 'assets/css/' . $file,
		array(),
		(string) filemtime( $path )
	);
}
add_action( 'init', 'zskeleton_register_block_heading_shared_style', 5 );

/**
 * @param mixed $slug Dashicon slug without `dashicons-` prefix.
 * @return string Safe slug or empty.
 */
function zskeleton_block_heading_sanitize_dashicon_slug( $slug ): string {
	if ( ! is_string( $slug ) ) {
		return '';
	}
	$slug = sanitize_key( trim( $slug ) );
	if ( '' === $slug || strlen( $slug ) > 64 ) {
		return '';
	}
	return $slug;
}

/**
 * @param mixed $raw Hex color.
 * @param string $fallback #rrggbb.
 * @return string
 */
function zskeleton_block_heading_separator_hex( $raw, string $fallback ): string {
	$fb = preg_match( '/^#[0-9A-Fa-f]{6}$/', $fallback ) ? $fallback : '#b8d4eb';
	if ( ! is_string( $raw ) ) {
		return $fb;
	}
	$raw = trim( $raw );
	if ( function_exists( 'maybe_hash_hex_color' ) ) {
		$raw = maybe_hash_hex_color( $raw );
	}
	$hex = sanitize_hex_color( $raw );
	return ( is_string( $hex ) && '' !== $hex ) ? $hex : $fb;
}

/**
 * Inline style attribute value for the separator span.
 *
 * @param array<string, mixed> $attributes Block attributes (titleSeparator* keys).
 * @return string Escaped for style="...".
 */
function zskeleton_block_heading_separator_style_attr( array $attributes ): string {
	$w = isset( $attributes['titleSeparatorWidthPx'] ) ? (int) $attributes['titleSeparatorWidthPx'] : 72;
	$w = min( 480, max( 4, $w ) );
	$h = isset( $attributes['titleSeparatorHeightPx'] ) ? (int) $attributes['titleSeparatorHeightPx'] : 4;
	$h = min( 64, max( 1, $h ) );
	$r = isset( $attributes['titleSeparatorRadiusPx'] ) ? (int) $attributes['titleSeparatorRadiusPx'] : 999;
	$r = min( 999, max( 0, $r ) );
	$c = zskeleton_block_heading_separator_hex(
		isset( $attributes['titleSeparatorColor'] ) ? $attributes['titleSeparatorColor'] : '',
		'#b8d4eb'
	);
	return esc_attr(
		sprintf(
			'width:%dpx;height:%dpx;border-radius:%dpx;background-color:%s;display:block;max-width:100%%;',
			$w,
			$h,
			$r,
			$c
		)
	);
}

/**
 * Markup: optional icon, heading, optional separator (Expert Profile CTA pattern).
 *
 * @param array<string, mixed> $args {
 *     Optional. Arguments for assembling the heading row.
 *     @type string               $title_inner_html Safe HTML inside the heading (e.g. wp_kses_post or esc_html).
 *     @type string               $heading_tag      Tag name: h2, h3, or h4.
 *     @type array<string, mixed> $attributes       Block attrs (titleDashicon, titleShowSeparator, titleSeparator*).
 *     @type string               $title_class      Extra class(es) on the heading element.
 *     @type string               $align            Horizontal cluster alignment: left, center, or right.
 *     @type string               $heading_id       Optional id attribute for the heading.
 *     @type int|null             $listing_gap_px   Optional margin-bottom (px) under the title row before the listing. Null uses stylesheet spacing only.
 * }
 * @return string HTML or empty when there is no title text and no icon.
 */
function zskeleton_render_block_heading_title_row( array $args ): string {
	if ( function_exists( 'wp_enqueue_style' ) ) {
		wp_enqueue_style( 'zskeleton-block-heading' );
	}

	$inner = isset( $args['title_inner_html'] ) ? (string) $args['title_inner_html'] : '';
	$tag   = isset( $args['heading_tag'] ) ? strtolower( (string) $args['heading_tag'] ) : 'h2';
	if ( ! preg_match( '/^h[2-4]$/', $tag ) ) {
		$tag = 'h2';
	}
	$attrs       = isset( $args['attributes'] ) && is_array( $args['attributes'] ) ? $args['attributes'] : array();
	$title_class = isset( $args['title_class'] ) ? trim( preg_replace( '/[^a-zA-Z0-9 _-]/', '', (string) $args['title_class'] ) ) : '';
	$title_class = trim( preg_replace( '/\s+/', ' ', $title_class ) );
	$align       = isset( $args['align'] ) ? sanitize_key( (string) $args['align'] ) : 'left';
	if ( ! in_array( $align, array( 'left', 'center', 'right' ), true ) ) {
		$align = 'left';
	}
	$heading_id = isset( $args['heading_id'] ) ? (string) $args['heading_id'] : '';
	$heading_id = preg_replace( '/[^A-Za-z0-9_-]/', '', $heading_id );
	if ( strlen( $heading_id ) > 120 ) {
		$heading_id = '';
	}

	$dash = zskeleton_block_heading_sanitize_dashicon_slug( isset( $attrs['titleDashicon'] ) ? $attrs['titleDashicon'] : '' );

	$has_title = '' !== trim( wp_strip_all_tags( $inner ) );
	if ( ! $has_title && '' === $dash ) {
		return '';
	}

	$show_sep = ! array_key_exists( 'titleShowSeparator', $attrs ) || (bool) $attrs['titleShowSeparator'];

	$gap_mb = '';
	if ( array_key_exists( 'listing_gap_px', $args ) && null !== $args['listing_gap_px'] ) {
		$gp = min( 200, max( 0, (int) $args['listing_gap_px'] ) );
		$gap_mb = sprintf( 'margin-bottom:%dpx;', $gp );
	}

	$head_classes = array(
		'zskeleton-block-heading__title-head',
		'zskeleton-block-heading--pos-' . $align,
	);
	$head_style = '' !== $gap_mb ? ' style="' . esc_attr( $gap_mb ) . '"' : '';
	$out        = '<div class="' . esc_attr( implode( ' ', $head_classes ) ) . '"' . $head_style . '>';
	if ( '' !== $dash ) {
		if ( function_exists( 'wp_enqueue_style' ) ) {
			wp_enqueue_style( 'dashicons' );
		}
		$out .= sprintf(
			'<span class="%1$s" aria-hidden="true"></span>',
			esc_attr( 'zskeleton-block-heading__title-icon dashicons dashicons-' . $dash )
		);
	}
	$out .= '<div class="zskeleton-block-heading__title-text-wrap">';
	if ( $has_title ) {
		$h_classes = trim( 'zskeleton-block-heading__title ' . $title_class );
		$id_attr   = '' !== $heading_id ? ' id="' . esc_attr( $heading_id ) . '"' : '';
		$out      .= sprintf(
			'<%1$s class="%2$s"%4$s>%3$s</%1$s>',
			tag_escape( $tag ),
			esc_attr( $h_classes ),
			$inner, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Caller supplies escaped fragment.
			$id_attr // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- id escaped above.
		);
	}
	if ( $show_sep && ( $has_title || '' !== $dash ) ) {
		$out .= '<span class="zskeleton-block-heading__separator" style="' . zskeleton_block_heading_separator_style_attr( $attrs ) . '" aria-hidden="true"></span>';
	}
	$out .= '</div></div>';

	return $out;
}
