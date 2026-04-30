<?php
/**
 * ZSkeleton bot protection: Google reCAPTCHA v3 or Cloudflare Turnstile.
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

const ZSKELETON_CAPTCHA_PROVIDER_GOOGLE = 'google_recaptcha';

const ZSKELETON_CAPTCHA_PROVIDER_TURNSTILE = 'cloudflare_turnstile';

class ZSkeleton_ReCAPTCHA {

	/**
	 * Active provider (google_recaptcha | cloudflare_turnstile).
	 *
	 * @var string
	 */
	private $provider;

	/**
	 * Site key (provider-specific).
	 *
	 * @var string
	 */
	private $site_key;

	/**
	 * Secret key (provider-specific).
	 *
	 * @var string
	 */
	private $secret_key;

	/**
	 * Minimum score threshold for Google v3 only (0.0 to 1.0).
	 *
	 * @var float
	 */
	private $threshold;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->provider  = $this->normalize_provider( get_option( 'zskeleton_captcha_provider', ZSKELETON_CAPTCHA_PROVIDER_GOOGLE ) );
		$this->threshold = floatval( get_option( 'zskeleton_recaptcha_threshold', 0.5 ) );

		if ( ZSKELETON_CAPTCHA_PROVIDER_TURNSTILE === $this->provider ) {
			$this->site_key   = (string) get_option( 'zskeleton_turnstile_site_key', '' );
			$this->secret_key = (string) get_option( 'zskeleton_turnstile_secret_key', '' );
		} else {
			$this->site_key   = (string) get_option( 'zskeleton_recaptcha_site_key', '' );
			$this->secret_key = (string) get_option( 'zskeleton_recaptcha_secret_key', '' );
		}

		if ( $this->is_enabled() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'login_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}
	}

	/**
	 * Normalize stored provider value.
	 *
	 * @param mixed $value Raw option.
	 * @return string
	 */
	private function normalize_provider( $value ) {
		$value = is_string( $value ) ? $value : ZSKELETON_CAPTCHA_PROVIDER_GOOGLE;
		if ( ZSKELETON_CAPTCHA_PROVIDER_TURNSTILE === $value ) {
			return ZSKELETON_CAPTCHA_PROVIDER_TURNSTILE;
		}
		return ZSKELETON_CAPTCHA_PROVIDER_GOOGLE;
	}

	/**
	 * Current captcha provider slug.
	 *
	 * @return string
	 */
	public function get_provider() {
		return $this->provider;
	}

	/**
	 * Whether the selected provider is fully configured.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return ! empty( $this->site_key ) && ! empty( $this->secret_key );
	}

	/**
	 * Whether the current request includes a captcha response for the active provider.
	 *
	 * @return bool
	 */
	public function has_response_in_request() {
		if ( ! $this->is_enabled() ) {
			return false;
		}
		if ( ZSKELETON_CAPTCHA_PROVIDER_TURNSTILE === $this->provider ) {
			return ! empty( $_POST['cf-turnstile-response'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- caller verifies nonce.
		}
		return ! empty( $_POST['recaptcha_token'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Token from POST for the active provider (for server-side verification).
	 *
	 * @return string
	 */
	public function get_request_token() {
		if ( ZSKELETON_CAPTCHA_PROVIDER_TURNSTILE === $this->provider ) {
			return isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		return isset( $_POST['recaptcha_token'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Enqueue front-end scripts for the active provider.
	 */
	public function enqueue_scripts() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( ZSKELETON_CAPTCHA_PROVIDER_TURNSTILE === $this->provider ) {
			wp_enqueue_script(
				'cloudflare-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js',
				array(),
				null,
				true
			);
			return;
		}

		wp_enqueue_script(
			'google-recaptcha',
			'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $this->site_key ),
			array(),
			null,
			true
		);

		$use_minified = (bool) get_option( 'zskeleton_use_minified_assets', true );
		$file         = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/js/recaptcha.min.js' )
			? 'recaptcha.min.js'
			: 'recaptcha.js';
		$path         = ZSkeleton_THEME_DIR . '/assets/js/' . $file;

		wp_enqueue_script(
			'zskeleton-recaptcha',
			ZSkeleton_THEME_URL . '/assets/js/' . $file,
			array( 'jquery', 'google-recaptcha' ),
			is_readable( $path ) ? (string) filemtime( $path ) : ZSkeleton_VERSION,
			true
		);

		wp_localize_script(
			'zskeleton-recaptcha',
			'zskeletonRecaptcha',
			array(
				'siteKey' => $this->site_key,
				'enabled' => true,
			)
		);
	}

	/**
	 * Site key for the active provider.
	 *
	 * @return string
	 */
	public function get_site_key() {
		return $this->site_key;
	}

	/**
	 * Status summary (safe for admin display).
	 *
	 * @return array<string, mixed>
	 */
	public function get_status() {
		return array(
			'provider'  => $this->provider,
			'enabled'   => $this->is_enabled(),
			'site_key'  => $this->is_enabled() ? substr( $this->site_key, 0, 10 ) . '...' : 'Not configured',
			'secret_key'=> $this->is_enabled() ? 'Configured' : 'Not configured',
			'threshold' => $this->threshold,
		);
	}

	/**
	 * Hidden fields / widget markup for forms.
	 *
	 * @param string $action Google reCAPTCHA v3 action name (ignored for Turnstile).
	 */
	public function render_field( $action = 'submit' ) {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( ZSKELETON_CAPTCHA_PROVIDER_TURNSTILE === $this->provider ) {
			// Managed Turnstile widget; submits `cf-turnstile-response` with the form.
			printf(
				'<div class="cf-turnstile zskeleton-turnstile" data-sitekey="%s" data-theme="light"></div>',
				esc_attr( $this->site_key )
			);
			return;
		}

		echo '<input type="hidden" name="recaptcha_action" value="' . esc_attr( $action ) . '">';
		echo '<input type="hidden" name="recaptcha_token" class="recaptcha-token" value="">';
	}

	/**
	 * Verify token with the configured provider.
	 *
	 * @param string $token  Token (Google: from recaptcha_token; Turnstile: from cf-turnstile-response — may be empty if caller did not pass it; falls back to POST).
	 * @param string $action Expected Google v3 action (unused for Turnstile).
	 * @return bool|WP_Error
	 */
	public function verify_token( $token, $action = '' ) {
		if ( ! $this->is_enabled() ) {
			return true;
		}

		if ( ZSKELETON_CAPTCHA_PROVIDER_TURNSTILE === $this->provider ) {
			if ( '' === $token ) {
				$token = $this->get_request_token();
			}
			return $this->verify_turnstile_token( $token );
		}

		if ( empty( $token ) ) {
			return new WP_Error(
				'recaptcha_missing',
				__( 'reCAPTCHA verification failed. Please try again.', 'zskeleton' )
			);
		}

		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'body' => array(
					'secret'   => $this->secret_key,
					'response' => $token,
					'remoteip' => $this->get_user_ip(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'recaptcha_error',
				__( 'reCAPTCHA verification service is temporarily unavailable.', 'zskeleton' )
			);
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $response_body['success'] ) ) {
			return new WP_Error(
				'recaptcha_failed',
				__( 'reCAPTCHA verification failed. Please try again.', 'zskeleton' )
			);
		}

		if ( ! empty( $action ) && isset( $response_body['action'] ) && $response_body['action'] !== $action ) {
			return new WP_Error(
				'recaptcha_action_mismatch',
				__( 'reCAPTCHA verification failed. Please refresh and try again.', 'zskeleton' )
			);
		}

		$score = isset( $response_body['score'] ) ? floatval( $response_body['score'] ) : 0;
		if ( $score < $this->threshold ) {
			return new WP_Error(
				'recaptcha_low_score',
				__( 'Your request appears to be automated. Please try again or contact support if you believe this is an error.', 'zskeleton' )
			);
		}

		return true;
	}

	/**
	 * Verify Cloudflare Turnstile token.
	 *
	 * @param string $token Response token.
	 * @return bool|WP_Error
	 */
	private function verify_turnstile_token( $token ) {
		if ( empty( $token ) ) {
			return new WP_Error(
				'turnstile_missing',
				__( 'Security check failed. Please complete the verification and try again.', 'zskeleton' )
			);
		}

		$response = wp_remote_post(
			'https://challenges.cloudflare.com/turnstile/v0/siteverify',
			array(
				'timeout' => 15,
				'body'    => array(
					'secret'   => $this->secret_key,
					'response' => $token,
					'remoteip' => $this->get_user_ip(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'turnstile_error',
				__( 'Security verification service is temporarily unavailable.', 'zskeleton' )
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['success'] ) ) {
			return new WP_Error(
				'turnstile_failed',
				__( 'Security check failed. Please try again.', 'zskeleton' )
			);
		}

		return true;
	}

	/**
	 * Verify POST data for the active provider.
	 *
	 * @return bool|WP_Error
	 */
	public function verify_form_submission() {
		if ( ! $this->is_enabled() ) {
			return true;
		}

		if ( ZSKELETON_CAPTCHA_PROVIDER_TURNSTILE === $this->provider ) {
			return $this->verify_turnstile_token( $this->get_request_token() );
		}

		$token  = $this->get_request_token();
		$action = isset( $_POST['recaptcha_action'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		return $this->verify_token( $token, $action );
	}

	/**
	 * Visitor IP for verification APIs.
	 *
	 * @return string
	 */
	private function get_user_ip() {
		$ip_keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) ) as $ip ) {
					$ip = trim( $ip );
					if ( filter_var( $ip, FILTER_VALIDATE_IP ) !== false ) {
						return $ip;
					}
				}
			}
		}

		return '';
	}

	/**
	 * Singleton instance.
	 *
	 * @return ZSkeleton_ReCAPTCHA
	 */
	public static function get_instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}
}

/**
 * Bot protection instance (reCAPTCHA or Turnstile per theme settings).
 *
 * @return ZSkeleton_ReCAPTCHA
 */
function zskeleton_recaptcha() {
	return ZSkeleton_ReCAPTCHA::get_instance();
}
