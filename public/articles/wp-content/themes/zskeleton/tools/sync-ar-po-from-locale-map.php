<?php
/**
 * Fill ar.po msgstr values from includes/languages/locale-ar.php map (single-line msgids).
 *
 * CLI: php tools/sync-ar-po-from-locale-map.php
 *
 * @package ZSkeleton_Theme
 */

if (php_sapi_name() !== 'cli') {
	exit( 'CLI only.' );
}

$theme_dir = dirname( __DIR__ );
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', true );
}
if ( ! function_exists( 'add_filter' ) ) {
	/**
	 * Stub for CLI sync (locale-ar.php registers gettext fallback at load).
	 *
	 * @param mixed ...$args Ignored.
	 * @return void
	 */
	function add_filter( ...$args ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}
}
require_once $theme_dir . '/includes/languages/locale-ar.php';

$po_path = $theme_dir . '/languages/ar.po';
$po = file_get_contents( $po_path );
if ( false === $po ) {
	fwrite( STDERR, "Cannot read PO file.\n" );
	exit( 1 );
}

$po = str_replace( "\r\n", "\n", $po );

/**
 * Escape a string for PO "..." output.
 *
 * @param string $s Source.
 * @return string
 */
function zskeleton_po_escape( $s ) {
	return str_replace( array( "\r", "\n", "\t", '\\', '"' ), array( '', '\n', '\t', '\\\\', '\\"' ), $s );
}

$map = zskeleton_get_arabic_gettext_map();
$updated = 0;

foreach ( $map as $en => $ar ) {
	if ( ! is_string( $en ) || ! is_string( $ar ) || strpos( $en, "\n" ) !== false ) {
		continue;
	}
	$mid = zskeleton_po_escape( $en );
	$ms  = zskeleton_po_escape( $ar );
	// Match msgid then empty msgstr (next block may follow with \n\n).
	$pattern = '/msgid "' . preg_quote( $mid, '/' ) . '"\Rmsgstr ""\R/';
	$replace = 'msgid "' . $mid . "\"\nmsgstr \"" . $ms . "\"\n";
	$new_po  = preg_replace( $pattern, $replace, $po, 1, $count );
	if ( $count > 0 ) {
		$po = $new_po;
		++$updated;
	}
}

file_put_contents( $po_path, $po );
echo "Updated {$updated} entries in ar.po from locale map.\n";
