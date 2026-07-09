<?php
/**
 * Bot protection (reCAPTCHA v3 / Turnstile) for WordPress core wp-login.php forms.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request is the wp-login.php screen.
 *
 * @return bool
 */
function zskeleton_is_wp_login_pagenow() {
	return isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'];
}

/**
 * Whether theme captcha is available and configured.
 *
 * @return bool
 */
function zskeleton_wp_login_captcha_is_configured() {
	return class_exists( 'ZSkeleton_ReCAPTCHA' ) && function_exists( 'zskeleton_recaptcha' ) && zskeleton_recaptcha()->is_enabled();
}

/**
 * Output captcha markup for wp-login forms (wrapped for layout/a11y).
 *
 * @param string $action Google reCAPTCHA v3 action name.
 */
function zskeleton_wp_login_captcha_render( $action ) {
	if ( ! zskeleton_wp_login_captcha_is_configured() ) {
		return;
	}
	echo '<div class="zskeleton-wp-login-captcha" role="group" aria-label="' . esc_attr__( 'Security verification', 'zskeleton' ) . '">';
	zskeleton_recaptcha()->render_field( $action );
	echo '</div>';
}

/**
 * Verify captcha for wp-login POST flows; returns WP_Error on failure or true on success/skip.
 *
 * @param string $context Context slug for future filters (login|register|lostpassword|resetpassword).
 * @return bool|WP_Error
 */
function zskeleton_wp_login_captcha_verify_request( $context ) {
	if ( ! zskeleton_wp_login_captcha_is_configured() ) {
		return true;
	}

	/**
	 * Short-circuit wp-login captcha verification (e.g. staging tools).
	 *
	 * @since 1.0.0
	 *
	 * @param null|bool|WP_Error $pre     Return non-null to skip default verification.
	 * @param string             $context Context slug.
	 */
	$pre = apply_filters( 'zskeleton_pre_verify_wp_login_captcha', null, $context );
	if ( null !== $pre ) {
		return $pre;
	}

	if ( ! zskeleton_is_wp_login_pagenow() ) {
		return true;
	}

	return zskeleton_recaptcha()->verify_form_submission();
}

/**
 * Register hooks when captcha is enabled.
 */
function zskeleton_wp_login_captcha_register_hooks() {
	if ( ! zskeleton_wp_login_captcha_is_configured() ) {
		return;
	}

	add_action( 'login_form', 'zskeleton_wp_login_captcha_on_login_form' );
	add_action( 'register_form', 'zskeleton_wp_login_captcha_on_register_form' );
	add_action( 'lostpassword_form', 'zskeleton_wp_login_captcha_on_lostpassword_form' );
	add_action( 'resetpass_form', 'zskeleton_wp_login_captcha_on_resetpass_form' );

	add_filter( 'authenticate', 'zskeleton_wp_login_captcha_on_authenticate', 19, 3 );
	add_action( 'lostpassword_post', 'zskeleton_wp_login_captcha_on_lostpassword_post', 10, 2 );
	add_filter( 'registration_errors', 'zskeleton_wp_login_captcha_on_registration_errors', 10, 3 );
	add_action( 'validate_password_reset', 'zskeleton_wp_login_captcha_on_validate_password_reset', 10, 2 );

	add_filter( 'shake_error_codes', 'zskeleton_wp_login_captcha_shake_error_codes' );
	add_action( 'login_enqueue_scripts', 'zskeleton_wp_login_captcha_login_styles' );
}

/**
 * Shake the login form when captcha verification fails.
 *
 * @param string[] $codes Error codes that trigger shake.
 * @return string[]
 */
function zskeleton_wp_login_captcha_shake_error_codes( $codes ) {
	$extra = array(
		'turnstile_missing',
		'turnstile_failed',
		'turnstile_error',
		'recaptcha_missing',
		'recaptcha_failed',
		'recaptcha_error',
		'recaptcha_action_mismatch',
		'recaptcha_low_score',
	);
	return array_values( array_unique( array_merge( (array) $codes, $extra ) ) );
}

/**
 * Spacing for captcha blocks on wp-login.php.
 */
function zskeleton_wp_login_captcha_login_styles() {
	wp_add_inline_style(
		'login',
		'.zskeleton-wp-login-captcha{margin:16px 0;max-width:100%;}'
	);
}

/**
 * Fires after the password field on the login form.
 */
function zskeleton_wp_login_captcha_on_login_form() {
	zskeleton_wp_login_captcha_render( 'wp_login' );
}

/**
 * Fires after the email field on the registration form.
 */
function zskeleton_wp_login_captcha_on_register_form() {
	zskeleton_wp_login_captcha_render( 'wp_register' );
}

/**
 * Fires inside the lost password form.
 */
function zskeleton_wp_login_captcha_on_lostpassword_form() {
	zskeleton_wp_login_captcha_render( 'wp_lostpassword' );
}

/**
 * Fires on the password reset form.
 *
 * @param WP_User $user User being reset.
 */
function zskeleton_wp_login_captcha_on_resetpass_form( $user ) {
	unset( $user );
	zskeleton_wp_login_captcha_render( 'wp_resetpassword' );
}

/**
 * Block login when captcha fails (runs before core password authentication).
 *
 * @param null|WP_User|WP_Error $user     Filter value.
 * @param string                $username Username or email.
 * @param string                $password Password.
 * @return null|WP_User|WP_Error
 */
function zskeleton_wp_login_captcha_on_authenticate( $user, $username, $password ) {
	unset( $username, $password );

	if ( ! isset( $_POST['wp-submit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return $user;
	}

	if ( ! zskeleton_is_wp_login_pagenow() ) {
		return $user;
	}

	$verification = zskeleton_wp_login_captcha_verify_request( 'login' );
	if ( is_wp_error( $verification ) ) {
		return $verification;
	}

	return $user;
}

/**
 * Block lost-password email when captcha fails.
 *
 * @param WP_Error      $errors    Error object (by reference semantics for object).
 * @param WP_User|false $user_data Matched user, if any.
 */
function zskeleton_wp_login_captcha_on_lostpassword_post( $errors, $user_data ) {
	unset( $user_data );

	if ( ! ( $errors instanceof WP_Error ) ) {
		return;
	}

	if ( ! isset( $_POST['wp-submit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	$verification = zskeleton_wp_login_captcha_verify_request( 'lostpassword' );
	if ( is_wp_error( $verification ) ) {
		$errors->add( $verification->get_error_code(), $verification->get_error_message() );
	}
}

/**
 * Block registration when captcha fails.
 *
 * @param WP_Error $errors               Registration errors.
 * @param string   $sanitized_user_login Login.
 * @param string   $user_email           Email.
 * @return WP_Error
 */
function zskeleton_wp_login_captcha_on_registration_errors( $errors, $sanitized_user_login, $user_email ) {
	unset( $sanitized_user_login, $user_email );

	if ( ! ( $errors instanceof WP_Error ) ) {
		return $errors;
	}

	if ( ! isset( $_POST['wp-submit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return $errors;
	}

	$verification = zskeleton_wp_login_captcha_verify_request( 'register' );
	if ( is_wp_error( $verification ) ) {
		$errors->add( $verification->get_error_code(), $verification->get_error_message() );
	}

	return $errors;
}

/**
 * Block password reset save when captcha fails.
 *
 * @param WP_Error         $errors Errors.
 * @param WP_User|WP_Error $user   User.
 */
function zskeleton_wp_login_captcha_on_validate_password_reset( $errors, $user ) {
	unset( $user );

	if ( ! ( $errors instanceof WP_Error ) ) {
		return;
	}

	if ( ! isset( $_POST['wp-submit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return;
	}

	$verification = zskeleton_wp_login_captcha_verify_request( 'resetpassword' );
	if ( is_wp_error( $verification ) ) {
		$errors->add( $verification->get_error_code(), $verification->get_error_message() );
	}
}

add_action( 'init', 'zskeleton_wp_login_captcha_register_hooks', 1 );
