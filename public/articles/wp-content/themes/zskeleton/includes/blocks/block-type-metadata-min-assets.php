<?php
/**
 * Block.json: use minified theme CSS when the “load minified assets” option is on.
 *
 * Resolves `file:` style references next to each block's block.json and swaps to `.min.css`
 * when that file exists (theme or child path).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Normalize `supports` because core may access array offsets.
 *
 * Some blocks may end up with `supports: null` (from metadata or filters) which triggers PHP 8+
 * warnings inside `wp-includes/class-wp-block-supports.php`.
 *
 * @param array $metadata Block.json metadata. May include `file` (absolute path to block.json), set by core before this filter.
 * @return array
 */
function zskeleton_block_type_metadata_use_min_css_when_available( array $metadata ): array {
	if ( isset( $metadata['supports'] ) && null === $metadata['supports'] ) {
		$metadata['supports'] = array();
	}
	if ( ! (bool) get_option( 'zskeleton_use_minified_assets', true ) ) {
		return $metadata;
	}
	if ( empty( $metadata['name'] ) || ! is_string( $metadata['name'] ) || 0 !== strpos( $metadata['name'], 'zskeleton/' ) ) {
		return $metadata;
	}
	$path = isset( $metadata['file'] ) && is_string( $metadata['file'] ) ? $metadata['file'] : '';
	$dir  = '' !== $path ? dirname( $path ) : '';
	if ( '' === $dir || ! is_dir( $dir ) ) {
		return $metadata;
	}
	foreach ( array( 'style', 'editorStyle' ) as $key ) {
		if ( empty( $metadata[ $key ] ) ) {
			continue;
		}
		$metadata[ $key ] = zskeleton_block_type_metadata_minify_style_value( $metadata[ $key ], $dir );
	}
	return $metadata;
}
add_filter( 'block_type_metadata', 'zskeleton_block_type_metadata_use_min_css_when_available', 10, 1 );

/**
 * Normalize register args `supports` to avoid PHP warnings.
 *
 * @param array  $args       Block type args.
 * @param string $block_name Block name.
 * @return array
 */
function zskeleton_register_block_type_args_normalize_supports( array $args, $block_name ): array {
	unset( $block_name );
	if ( isset( $args['supports'] ) && null === $args['supports'] ) {
		$args['supports'] = array();
	}
	return $args;
}
add_filter( 'register_block_type_args', 'zskeleton_register_block_type_args_normalize_supports', 5, 2 );

/**
 * @param string|array $val  Style field from block.json.
 * @param string       $dir  Directory containing block.json.
 * @return string|array
 */
function zskeleton_block_type_metadata_minify_style_value( $val, $dir ) {
	if ( is_array( $val ) ) {
		$out = array();
		foreach ( $val as $item ) {
			$out[] = zskeleton_block_type_metadata_minify_style_value( $item, $dir );
		}
		return $out;
	}
	if ( ! is_string( $val ) || 0 !== strpos( $val, 'file:' ) ) {
		return $val;
	}
	$rel = (string) substr( $val, 5 );
	if ( $rel === '' || preg_match( '/\.min\.css$/i', $rel ) || ! preg_match( '/\.css$/i', $rel ) ) {
		return $val;
	}
	$min_rel = preg_replace( '/\.css$/i', '.min.css', $rel, 1 );
	$min_abs = realpath( $dir . '/' . $min_rel );
	if ( $min_abs && is_readable( $min_abs ) ) {
		return 'file:' . $min_rel;
	}
	return $val;
}
