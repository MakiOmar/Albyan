<?php
/**
 * Optional WebP and SVG uploads (Appearance → ZSkeleton Settings → Content).
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return bool
 */
function zskeleton_upload_mimes_allow_webp(): bool {
	return '1' === (string) get_option( 'zskeleton_allow_upload_webp', '0' );
}

/**
 * @return bool
 */
function zskeleton_upload_mimes_allow_svg(): bool {
	return '1' === (string) get_option( 'zskeleton_allow_upload_svg', '0' );
}

/**
 * Add or remove WebP / SVG from allowed upload MIME types.
 *
 * @param array<string, string> $mimes Mime types map (extension => mime).
 * @return array<string, string>
 */
function zskeleton_filter_upload_mimes( $mimes ) {
	if ( ! is_array( $mimes ) ) {
		return $mimes;
	}

	if ( zskeleton_upload_mimes_allow_webp() ) {
		$mimes['webp'] = 'image/webp';
	} else {
		unset( $mimes['webp'] );
	}

	if ( zskeleton_upload_mimes_allow_svg() ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
	} else {
		unset( $mimes['svg'], $mimes['svgz'] );
	}

	return $mimes;
}
add_filter( 'upload_mimes', 'zskeleton_filter_upload_mimes', 99 );

/**
 * Help WordPress accept .svg when SVG uploads are enabled.
 *
 * @param array<string, mixed> $data     File data.
 * @param string               $file     Full path to file.
 * @param string               $filename File name.
 * @param string[]|string      $mimes    Mime types.
 * @return array<string, mixed>
 */
function zskeleton_fix_svg_filetype_and_ext( $data, $file, $filename, $mimes ) {
	if ( ! zskeleton_upload_mimes_allow_svg() ) {
		return $data;
	}
	if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
		return $data;
	}
	$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
	if ( 'svg' !== $ext ) {
		return $data;
	}
	$data['ext']  = 'svg';
	$data['type'] = 'image/svg+xml';
	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'zskeleton_fix_svg_filetype_and_ext', 10, 4 );

/**
 * Basic SVG sanity check (must look like XML/SVG).
 *
 * @param array<string, mixed> $file Upload array from wp_handle_upload.
 * @return array<string, mixed>
 */
function zskeleton_validate_svg_upload( $file ) {
	if ( ! zskeleton_upload_mimes_allow_svg() ) {
		return $file;
	}
	if ( ! empty( $file['error'] ) || empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
		return $file;
	}
	$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
	if ( 'svg' !== $ext ) {
		return $file;
	}
	$sample = file_get_contents( $file['tmp_name'], false, null, 0, 8192 );
	if ( false === $sample ) {
		$file['error'] = __( 'Could not read the uploaded SVG file.', 'zskeleton' );
		return $file;
	}
	$trim = ltrim( $sample );
	$trim = preg_replace( '/^\xEF\xBB\xBF/', '', $trim );
	if ( ! preg_match( '/^(<\?xml|<!DOCTYPE\s+svg|<svg[\s>])/i', $trim ) ) {
		$file['error'] = __( 'The file does not appear to be a valid SVG.', 'zskeleton' );
		return $file;
	}
	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'zskeleton_validate_svg_upload' );

/**
 * Improve media modal display for SVG attachments.
 *
 * @param array<string, mixed> $response   Attachment data.
 * @param WP_Post              $attachment Attachment post.
 * @param array<string, mixed> $meta       Meta.
 * @return array<string, mixed>
 */
function zskeleton_prepare_svg_attachment_for_js( $response, $attachment, $meta ) {
	if ( ! zskeleton_upload_mimes_allow_svg() || ! $attachment instanceof WP_Post ) {
		return $response;
	}
	if ( 'image/svg+xml' !== $attachment->post_mime_type ) {
		return $response;
	}
	$url = isset( $response['url'] ) ? $response['url'] : wp_get_attachment_url( $attachment->ID );
	if ( $url ) {
		if ( empty( $response['image'] ) ) {
			$response['image'] = $url;
		}
		if ( empty( $response['thumb'] ) ) {
			$response['thumb'] = $url;
		}
	}
	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'zskeleton_prepare_svg_attachment_for_js', 10, 3 );

/**
 * When WebP uploads are enabled, do not block in the browser if PHP cannot
 * resize WebP (core sets webp_upload_error and shows “Convert it to JPEG or PNG”).
 *
 * @param array<string, mixed> $defaults Plupload defaults from {@see wp_plupload_default_settings()}.
 * @return array<string, mixed>
 */
function zskeleton_plupload_allow_webp_when_enabled( $defaults ) {
	if ( ! zskeleton_upload_mimes_allow_webp() || ! is_array( $defaults ) ) {
		return $defaults;
	}
	$defaults['webp_upload_error'] = false;
	return $defaults;
}
add_filter( 'plupload_default_settings', 'zskeleton_plupload_allow_webp_when_enabled', 20 );

/**
 * Same for the classic uploader script ({@see wp_admin_plupload_init()}).
 *
 * @param array<string, mixed> $init Plupload init array.
 * @return array<string, mixed>
 */
function zskeleton_plupload_init_allow_webp_when_enabled( $init ) {
	if ( ! zskeleton_upload_mimes_allow_webp() || ! is_array( $init ) ) {
		return $init;
	}
	$init['webp_upload_error'] = false;
	return $init;
}
add_filter( 'plupload_init', 'zskeleton_plupload_init_allow_webp_when_enabled', 20 );

/**
 * REST / block editor upload check (WP 6.8+).
 *
 * @param bool        $prevent   Whether to block unsupported image types.
 * @param string|null $mime_type Mime type of the file when known.
 * @return bool
 */
function zskeleton_rest_allow_webp_when_enabled( $prevent, $mime_type ) {
	if ( ! $prevent || ! zskeleton_upload_mimes_allow_webp() ) {
		return $prevent;
	}
	if ( $mime_type && 'image/webp' === $mime_type ) {
		return false;
	}
	return $prevent;
}
add_filter( 'wp_prevent_unsupported_mime_type_uploads', 'zskeleton_rest_allow_webp_when_enabled', 10, 2 );
