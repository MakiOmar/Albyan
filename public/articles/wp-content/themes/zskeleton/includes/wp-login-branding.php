<?php
/**
 * Replace the default WordPress login logo with the theme branding logo and site title.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Point the login header logo link at the site home URL.
 *
 * @param string $login_header_url Default login header URL.
 * @return string
 */
function zskeleton_login_header_url( $login_header_url ) {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'zskeleton_login_header_url' );

/**
 * Accessible name for the login logo (replaces “Powered by WordPress”).
 *
 * @param string $login_header_text Default header text.
 * @return string
 */
function zskeleton_login_header_text( $login_header_text ) {
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}
add_filter( 'login_headertext', 'zskeleton_login_header_text' );

/**
 * Output CSS so wp-login.php uses the theme settings logo as the header graphic.
 *
 * @return void
 */
function zskeleton_login_page_logo_styles() {
	if ( ! function_exists( 'zskeleton_get_logo' ) ) {
		return;
	}

	$logo = zskeleton_get_logo( 'desktop' );
	if ( ! $logo ) {
		return;
	}

	printf(
		'<style id="zskeleton-login-logo">.login h1 a{background-image:url(%s);background-size:contain;background-position:center center;width:100%%;max-width:320px;height:84px;}</style>' . "\n",
		esc_url( $logo )
	);
}
add_action( 'login_head', 'zskeleton_login_page_logo_styles', 99 );
