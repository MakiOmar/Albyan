<?php
/**
 * Template Name: Complete Payment
 *
 * Template is provided by the ZSkeleton Membership & Payments plugin.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( defined( 'ZSKELETON_MEMBERSHIP_PATH' ) && is_readable( ZSKELETON_MEMBERSHIP_PATH . 'templates/page-complete-payment.php' ) ) {
	require ZSKELETON_MEMBERSHIP_PATH . 'templates/page-complete-payment.php';
	return;
}

get_header();
echo '<p>' . esc_html__( 'Please activate the ZSkeleton Membership & Payments plugin to use this page.', 'zskeleton' ) . '</p>';
get_footer();
