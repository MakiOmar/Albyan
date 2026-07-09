<?php
/**
 * Landing page (canvas) template helpers.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return bool
 */
function zskeleton_is_landing_page_template() {
	return is_page_template( 'page-landing.php' );
}

/**
 * Enqueue canvas layout styles on the landing template only.
 */
function zskeleton_enqueue_landing_page_assets() {
	if ( ! zskeleton_is_landing_page_template() ) {
		return;
	}

	$path = ZSkeleton_THEME_DIR . '/assets/css/landing-page.css';
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : ZSkeleton_VERSION;

	wp_enqueue_style(
		'zskeleton-landing-page',
		ZSkeleton_THEME_URL . '/assets/css/landing-page.css',
		array(),
		$ver
	);
}
add_action( 'wp_enqueue_scripts', 'zskeleton_enqueue_landing_page_assets', 20 );

/**
 * @param string[] $classes Body classes.
 * @return string[]
 */
function zskeleton_landing_page_body_class( array $classes ) {
	if ( zskeleton_is_landing_page_template() ) {
		$classes[] = 'zs-landing-page--canvas';
	}
	return $classes;
}
add_filter( 'body_class', 'zskeleton_landing_page_body_class' );
