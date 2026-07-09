<?php
/**
 * Optional single-file bundle for global front-end theme CSS (Performance settings), including configurable extra theme files.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the combined global CSS handle was enqueued on this request (set in {@see zskeleton_enqueue_assets()}).
 *
 * @return bool
 */
function zskeleton_is_combined_front_css_enqueued() {
	return true === ( $GLOBALS['zskeleton_combined_front_css_enqueued'] ?? false );
}

/**
 * Whether “Combine global CSS” is enabled in ZSkeleton Settings → Performance.
 *
 * @return bool
 */
function zskeleton_combine_theme_css_enabled() {
	return '1' === (string) get_option( 'zskeleton_combine_theme_css', '0' );
}

/**
 * Style handle that replaces `zskeleton-style` when global CSS is combined (for dependents that only needed main).
 *
 * @return string
 */
function zskeleton_theme_css_handle_for_style_dependency() {
	return zskeleton_is_combined_front_css_enqueued() ? 'zskeleton-combined-front' : 'zskeleton-style';
}

/**
 * Style handle that replaces `zskeleton-components` when global CSS is combined.
 *
 * @return string
 */
function zskeleton_theme_css_handle_for_components_dependency() {
	return zskeleton_is_combined_front_css_enqueued() ? 'zskeleton-combined-front' : 'zskeleton-components';
}

/**
 * Ordered absolute paths for the always-loaded theme CSS stack (matches enqueue order).
 *
 * @param bool $use_minified Prefer .min.css when present and readable.
 * @return string[]
 */
function zskeleton_get_frontend_base_css_stack_paths( $use_minified ) {
	$paths = array();

	$main_file = $use_minified ? 'style.min.css' : 'style.css';
	$main_path = $use_minified ? ZSkeleton_THEME_DIR . '/assets/css/' . $main_file : get_stylesheet_directory() . '/style.css';
	if ( is_readable( $main_path ) ) {
		$paths[] = $main_path;
	}

	$components_file = $use_minified ? 'components.min.css' : 'components.css';
	$components_path = ZSkeleton_THEME_DIR . '/assets/css/' . $components_file;
	if ( is_readable( $components_path ) ) {
		$paths[] = $components_path;
	}

	$widgets_basename = ( $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/widgets-zskeleton.min.css' ) )
		? 'widgets-zskeleton.min.css'
		: 'widgets-zskeleton.css';
	$widgets_path = ZSkeleton_THEME_DIR . '/assets/css/' . $widgets_basename;
	if ( is_readable( $widgets_path ) ) {
		$paths[] = $widgets_path;
	}

	$page_title_bar_file = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/page-title-bar.min.css' )
		? 'page-title-bar.min.css'
		: 'page-title-bar.css';
	$page_title_bar_path = ZSkeleton_THEME_DIR . '/assets/css/' . $page_title_bar_file;
	if ( is_readable( $page_title_bar_path ) ) {
		$paths[] = $page_title_bar_path;
	}

	return $paths;
}

/**
 * Sanitize the Performance “extra CSS files for bundle” textarea (newline-separated paths).
 *
 * @param mixed $value Raw option value.
 * @return string Stored list (relative paths, one per line).
 */
function zskeleton_sanitize_combine_theme_css_extra_list_option( $value ) {
	$lines = zskeleton_parse_combine_theme_css_extra_relative_paths( $value, false );
	return implode( "\n", $lines );
}

/**
 * Parse and validate theme-relative CSS paths for the combined bundle.
 *
 * Paths must stay under the active theme directory, use forward slashes, end in `.css`, and must not
 * contain `..`. When $require_readable is true, non-existent or unreadable files are omitted
 * (used when building the bundle). When false, valid-looking paths are kept so files can appear later
 * (used when saving settings).
 *
 * @param mixed $raw              Multiline textarea or array of lines.
 * @param bool  $require_readable Whether each file must exist and be readable.
 * @return string[] Unique relative paths (forward slashes), declaration order preserved.
 */
function zskeleton_parse_combine_theme_css_extra_relative_paths( $raw, $require_readable ) {
	if ( is_array( $raw ) ) {
		$raw = implode( "\n", $raw );
	}
	if ( ! is_string( $raw ) ) {
		return array();
	}

	$theme_root = wp_normalize_path( trailingslashit( ZSkeleton_THEME_DIR ) );
	$lines      = preg_split( '/\r\n|\r|\n/', $raw );
	$out        = array();
	$seen       = array();

	foreach ( $lines as $line ) {
		$line = trim( $line );
		if ( '' === $line || 0 === strpos( $line, '#' ) ) {
			continue;
		}
		$line = str_replace( '\\', '/', $line );
		$line = ltrim( $line, "./ \t" );
		if ( '' === $line || false !== strpos( $line, '..' ) ) {
			continue;
		}
		if ( '/' === $line[0] ) {
			continue;
		}
		if ( ! preg_match( '/\.css$/i', $line ) ) {
			continue;
		}
		if ( ! preg_match( '#^[a-zA-Z0-9_./-]+$#', $line ) ) {
			continue;
		}

		$full = wp_normalize_path( $theme_root . $line );
		if ( 0 !== strpos( $full, $theme_root ) ) {
			continue;
		}
		if ( $require_readable && ! is_readable( $full ) ) {
			continue;
		}

		if ( isset( $seen[ $line ] ) ) {
			continue;
		}
		$seen[ $line ] = true;
		$out[]         = $line;
	}

	return $out;
}

/**
 * Absolute paths for extra theme CSS files to append to the combined bundle (after the core stack).
 *
 * @return string[]
 */
function zskeleton_get_combine_css_extra_absolute_paths() {
	$raw  = (string) get_option( 'zskeleton_combine_theme_css_extra_list', '' );
	$rels = zskeleton_parse_combine_theme_css_extra_relative_paths( $raw, true );
	$paths = array();
	foreach ( $rels as $rel ) {
		$p = wp_normalize_path( trailingslashit( ZSkeleton_THEME_DIR ) . $rel );
		if ( is_readable( $p ) ) {
			$paths[] = $p;
		}
	}
	return $paths;
}

/**
 * Core global stack plus optional extra theme CSS files (deduplicated by normalized path).
 *
 * @param bool $use_minified Prefer .min.css for the built-in stack.
 * @return string[]
 */
function zskeleton_get_all_combined_theme_css_paths( $use_minified ) {
	$base = zskeleton_get_frontend_base_css_stack_paths( $use_minified );
	$seen = array();
	foreach ( $base as $p ) {
		$seen[ wp_normalize_path( $p ) ] = true;
	}
	$out = $base;
	foreach ( zskeleton_get_combine_css_extra_absolute_paths() as $p ) {
		$n = wp_normalize_path( $p );
		if ( isset( $seen[ $n ] ) ) {
			continue;
		}
		$seen[ $n ] = true;
		$out[]      = $p;
	}
	return $out;
}

/**
 * Resolve a registered stylesheet `src` to an absolute path under the parent theme directory.
 *
 * Handles full template URI, protocol-relative, and root-relative `/wp-content/...` URLs.
 *
 * @param string $src Style src from WP_Styles.
 * @return string|false Normalized readable path, or false.
 */
function zskeleton_theme_stylesheet_src_to_theme_path( $src ) {
	$src = preg_replace( '#\?[^#]*$#', '', (string) $src );
	if ( '' === $src ) {
		return false;
	}

	if ( 0 === strpos( $src, '//' ) ) {
		$src = wp_parse_url( home_url(), PHP_URL_SCHEME ) . ':' . $src;
	}

	$dir = wp_normalize_path( trailingslashit( get_template_directory() ) );
	$uri = trailingslashit( get_template_directory_uri() );

	if ( 0 === strpos( $src, $uri ) ) {
		$rel  = substr( $src, strlen( $uri ) );
		$path = wp_normalize_path( $dir . ltrim( str_replace( '\\', '/', $rel ), '/' ) );
	} elseif ( isset( $src[0] ) && '/' === $src[0] ) {
		$path = wp_normalize_path( ABSPATH . ltrim( $src, '/' ) );
	} else {
		return false;
	}

	if ( 0 !== strpos( $path, $dir ) ) {
		return false;
	}

	return is_readable( $path ) ? $path : false;
}

/**
 * After all enqueues, drop separate requests for CSS files already concatenated into the combined bundle extras.
 *
 * @return void
 */
function zskeleton_dequeue_theme_styles_included_in_combined_extra_bundle() {
	if ( is_admin() || ! zskeleton_is_combined_front_css_enqueued() || ! zskeleton_combine_theme_css_enabled() ) {
		return;
	}

	$extras = zskeleton_get_combine_css_extra_absolute_paths();
	if ( array() === $extras ) {
		return;
	}

	$extra_set = array();
	foreach ( $extras as $p ) {
		$extra_set[ wp_normalize_path( $p ) ] = true;
	}

	global $wp_styles;
	if ( ! ( $wp_styles instanceof WP_Styles ) ) {
		return;
	}

	$queue_copy = is_array( $wp_styles->queue ) ? array_values( $wp_styles->queue ) : array();
	foreach ( $queue_copy as $handle ) {
		if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
			continue;
		}
		$obj = $wp_styles->registered[ $handle ];
		if ( empty( $obj->src ) ) {
			continue;
		}
		$path = zskeleton_theme_stylesheet_src_to_theme_path( $obj->src );
		if ( ! $path ) {
			continue;
		}
		if ( isset( $extra_set[ wp_normalize_path( $path ) ] ) ) {
			wp_dequeue_style( $handle );
		}
	}

	// `single-post` styles depend on `page-single-shared`; if the shared file is only in the bundle, drop the child handle too.
	foreach ( $extras as $p ) {
		$base = basename( (string) $p );
		if ( preg_match( '/^page-single-shared(\.min)?\.css$/i', $base ) ) {
			wp_dequeue_style( 'zskeleton-single-post' );
			break;
		}
	}
}

/**
 * Delete generated combined CSS files under uploads (idempotent).
 *
 * @return void
 */
function zskeleton_flush_combined_theme_css_cache() {
	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) || empty( $upload['basedir'] ) ) {
		return;
	}
	$dir = trailingslashit( $upload['basedir'] ) . 'zskeleton-cache';
	if ( ! is_dir( $dir ) ) {
		return;
	}
	$files = glob( $dir . '/theme-base-*.css' );
	if ( ! is_array( $files ) ) {
		return;
	}
	foreach ( $files as $file ) {
		if ( is_string( $file ) && is_file( $file ) ) {
			wp_delete_file( $file );
		}
	}
}

/**
 * Build or reuse a cached concatenation of global theme CSS; returns URL and version for wp_enqueue_style.
 *
 * @param bool $use_minified Prefer minified sources.
 * @return array{url:string,ver:string}|false
 */
function zskeleton_get_theme_combined_front_bundle( $use_minified ) {
	$paths = zskeleton_get_all_combined_theme_css_paths( $use_minified );
	if ( array() === $paths ) {
		return false;
	}

	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) || empty( $upload['basedir'] ) || empty( $upload['baseurl'] ) ) {
		return false;
	}

	$sig = array();
	foreach ( $paths as $p ) {
		$sig[ wp_normalize_path( $p ) ] = (int) filemtime( $p );
	}
	$hash = substr( md5( wp_json_encode( $sig ) . ZSkeleton_VERSION ), 0, 12 );

	$cache_dir = trailingslashit( $upload['basedir'] ) . 'zskeleton-cache';
	if ( ! wp_mkdir_p( $cache_dir ) ) {
		return false;
	}
	$index_guard = trailingslashit( $cache_dir ) . 'index.php';
	if ( ! is_readable( $index_guard ) ) {
		file_put_contents( $index_guard, "<?php\n// Silence is golden.\n", LOCK_EX );
	}

	$filename = 'theme-base-' . $hash . '.css';
	$dest     = trailingslashit( $cache_dir ) . $filename;
	$baseurl  = trailingslashit( $upload['baseurl'] ) . 'zskeleton-cache/' . $filename;

	if ( is_readable( $dest ) && filesize( $dest ) > 0 ) {
		return array(
			'url' => $baseurl,
			'ver' => (string) filemtime( $dest ),
		);
	}

	$css = '';
	foreach ( $paths as $path ) {
		$chunk = file_get_contents( $path );
		if ( false === $chunk ) {
			continue;
		}
		$css .= "\n/* zskeleton-bundle: " . basename( $path ) . " */\n" . $chunk;
	}
	$css = trim( $css );
	if ( '' === $css ) {
		return false;
	}

	foreach ( glob( $cache_dir . '/theme-base-*.css' ) ?: array() as $old ) {
		if ( $old !== $dest && is_file( $old ) ) {
			wp_delete_file( $old );
		}
	}

	$tmp = $dest . '.tmp';
	if ( false === file_put_contents( $tmp, $css, LOCK_EX ) ) {
		return false;
	}
	if ( ! @rename( $tmp, $dest ) ) {
		wp_delete_file( $tmp );
		return false;
	}

	return array(
		'url' => $baseurl,
		'ver' => (string) filemtime( $dest ),
	);
}

add_action( 'update_option_zskeleton_combine_theme_css', 'zskeleton_flush_combined_theme_css_cache' );
add_action( 'update_option_zskeleton_combine_theme_css_extra_list', 'zskeleton_flush_combined_theme_css_cache' );
add_action( 'update_option_zskeleton_use_minified_assets', 'zskeleton_flush_combined_theme_css_cache' );
add_action( 'after_switch_theme', 'zskeleton_flush_combined_theme_css_cache' );
add_action( 'wp_enqueue_scripts', 'zskeleton_dequeue_theme_styles_included_in_combined_extra_bundle', 999 );
