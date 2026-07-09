<?php
/**
 * Conditional enqueue for form kit (front + admin).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Assets for Form Kit.
 */
class ZSkeleton_Form_Assets {

	/**
	 * @var array<string,bool>
	 */
	private static $context_needed = array();

	/**
	 * @var bool
	 */
	private static $needs_media = false;

	/**
	 * When a public Form Kit form is rendered with bot protection, script deps for form-kit.js.
	 *
	 * @var string ''|'google_recaptcha'|'cloudflare_turnstile'
	 */
	private static $public_captcha_provider = '';

	/**
	 * @var bool
	 */
	private static $needs_intl_tel = false;

	/**
	 * Register hooks.
	 */
	public static function init() {
		// Forms may render during the_content; enqueue before scripts print.
		add_action( 'wp_footer', array( __CLASS__, 'enqueue_public' ), 5 );
		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'enqueue_admin' ), 5 );
	}

	/**
	 * @param string                     $context admin|public.
	 * @param ZSkeleton_Form_Definition|null $definition Optional for heavy types.
	 */
	public static function request_enqueue( $context, ZSkeleton_Form_Definition $definition = null ) {
		$context = in_array( $context, array( 'admin', 'public' ), true ) ? $context : 'public';
		self::$context_needed[ $context ] = true;
		if ( $definition && self::definition_uses_type( $definition, array( 'media', 'image' ) ) ) {
			self::$needs_media = true;
		}
		if ( $definition && self::definition_uses_intl_tel( $definition ) ) {
			self::request_intl_tel();
		}
	}

	/**
	 * Mark intl-tel-input assets as required (tel fields with country dial codes).
	 */
	public static function request_intl_tel() {
		self::$needs_intl_tel = true;
	}

	/**
	 * @param ZSkeleton_Form_Definition $definition Definition.
	 * @return bool
	 */
	private static function definition_uses_intl_tel( ZSkeleton_Form_Definition $definition ) {
		foreach ( $definition->get_fields_by_name() as $field ) {
			$t = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : '';
			if ( 'tel' === $t && ! empty( $field['intl_tel'] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Public Form Kit output includes Turnstile/reCAPTCHA — load provider scripts before form-kit.js.
	 *
	 * @param string $provider google_recaptcha|cloudflare_turnstile.
	 */
	public static function request_public_captcha( $provider ) {
		$provider = (string) $provider;
		if ( 'cloudflare_turnstile' === $provider ) {
			self::$public_captcha_provider = 'cloudflare_turnstile';
			return;
		}
		if ( 'google_recaptcha' === $provider ) {
			self::$public_captcha_provider = 'google_recaptcha';
		}
	}

	/**
	 * @param ZSkeleton_Form_Definition $definition Definition.
	 * @param string[]                  $types Types.
	 * @return bool
	 */
	private static function definition_uses_type( ZSkeleton_Form_Definition $definition, array $types ) {
		foreach ( $definition->get_fields_by_name() as $field ) {
			$t = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : '';
			if ( in_array( $t, $types, true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Force enqueue on admin screen (demo page).
	 *
	 * @param string $hook_suffix Hook.
	 */
	public static function enqueue_admin_screen( $hook_suffix ) {
		self::$context_needed['admin'] = true;
		self::enqueue_bundle( 'admin' );
	}

	/**
	 * Front-end enqueue.
	 */
	public static function enqueue_public() {
		if ( empty( self::$context_needed['public'] ) ) {
			return;
		}
		self::enqueue_bundle( 'public' );
	}

	/**
	 * Admin footer enqueue when a form was rendered in admin.
	 */
	public static function enqueue_admin() {
		if ( empty( self::$context_needed['admin'] ) ) {
			return;
		}
		self::enqueue_bundle( 'admin' );
	}

	/**
	 * @param string $context Context.
	 */
	private static function enqueue_bundle( $context ) {
		if ( self::$needs_intl_tel ) {
			self::enqueue_intl_tel_assets();
		}

		if ( wp_script_is( 'zskeleton-form-kit', 'enqueued' ) || wp_script_is( 'zskeleton-form-kit', 'done' ) ) {
			return;
		}
		$use_min = (bool) get_option( 'zskeleton_use_minified_assets', true );
		$css     = $use_min && is_readable( ZSkeleton_THEME_DIR . '/assets/css/form-kit.min.css' ) ? 'form-kit.min.css' : 'form-kit.css';
		$js      = $use_min && is_readable( ZSkeleton_THEME_DIR . '/assets/js/form-kit.min.js' ) ? 'form-kit.min.js' : 'form-kit.js';
		$ver_css = is_readable( ZSkeleton_THEME_DIR . '/assets/css/' . $css ) ? (string) filemtime( ZSkeleton_THEME_DIR . '/assets/css/' . $css ) : ZSkeleton_FORM_KIT_VERSION;
		$ver_js  = is_readable( ZSkeleton_THEME_DIR . '/assets/js/' . $js ) ? (string) filemtime( ZSkeleton_THEME_DIR . '/assets/js/' . $js ) : ZSkeleton_FORM_KIT_VERSION;

		$fk_script_deps = self::form_kit_script_dependencies( $context );

		if ( 'admin' === $context ) {
			wp_enqueue_style( 'zskeleton-form-kit', ZSkeleton_THEME_URL . '/assets/css/' . $css, array(), $ver_css );
			wp_enqueue_script( 'zskeleton-form-kit', ZSkeleton_THEME_URL . '/assets/js/' . $js, $fk_script_deps, $ver_js, true );
		} else {
			$form_kit_style_parent = function_exists( 'zskeleton_theme_css_handle_for_style_dependency' ) ? zskeleton_theme_css_handle_for_style_dependency() : 'zskeleton-style';
			wp_enqueue_style( 'zskeleton-form-kit', ZSkeleton_THEME_URL . '/assets/css/' . $css, array( $form_kit_style_parent ), $ver_css );
			wp_enqueue_script( 'zskeleton-form-kit', ZSkeleton_THEME_URL . '/assets/js/' . $js, $fk_script_deps, $ver_js, true );
		}

		wp_localize_script(
			'zskeleton-form-kit',
			'zskeletonFormKit',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'genericError'       => __( 'Something went wrong. Please try again.', 'zskeleton' ),
					'invalid'            => __( 'Please correct the errors below.', 'zskeleton' ),
					'pleaseFillRequired' => __( 'Please fill required fields.', 'zskeleton' ),
					'errorShort'         => __( 'Error', 'zskeleton' ),
					'invalidShort'       => __( 'Invalid', 'zskeleton' ),
					'successOk'          => __( 'OK', 'zskeleton' ),
					'recaptchaFailed'    => __( 'Security verification failed. Please refresh the page and try again.', 'zskeleton' ),
					'submitting'         => __( 'Submitting…', 'zskeleton' ),
					'mediaTitle'         => __( 'Select media', 'zskeleton' ),
					'mediaButton'        => __( 'Use this file', 'zskeleton' ),
				),
			)
		);

		if ( self::$needs_media && 'admin' === $context && function_exists( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
	}

	/**
	 * Script handles form-kit.js should load after (Turnstile / Google v3 helpers).
	 *
	 * @param string $context admin|public.
	 * @return string[]
	 */
	private static function form_kit_script_dependencies( $context ) {
		$deps = array();
		if ( self::$needs_intl_tel ) {
			$deps[] = 'zskeleton-intl-tel-input';
		}
		if ( 'admin' === $context ) {
			$deps[] = 'jquery';
			if ( 'cloudflare_turnstile' === self::$public_captcha_provider ) {
				$deps[] = 'cloudflare-turnstile';
			} elseif ( 'google_recaptcha' === self::$public_captcha_provider ) {
				$deps[] = 'zskeleton-recaptcha';
			}
			return $deps;
		}
		if ( 'cloudflare_turnstile' === self::$public_captcha_provider ) {
			$deps[] = 'cloudflare-turnstile';
			return $deps;
		}
		if ( 'google_recaptcha' === self::$public_captcha_provider ) {
			$deps[] = 'jquery';
			$deps[] = 'zskeleton-recaptcha';
			return $deps;
		}
		return $deps;
	}

	/**
	 * Shared config for intl-tel-input (public forms + admin preview).
	 *
	 * @return array<string, mixed>
	 */
	public static function get_intl_tel_config() {
		$is_arabic = function_exists( 'zskeleton_is_arabic_locale' ) && zskeleton_is_arabic_locale();

		/**
		 * Filter intl-tel-input runtime options passed to zskeletonIntlTelConfig.
		 *
		 * @param array<string, mixed> $config isRtl, isArabic, autoDetect, fallbackCountry, geoUrl.
		 */
		return apply_filters(
			'zskeleton_intl_tel_config',
			array(
				'isRtl'           => is_rtl(),
				'isArabic'        => $is_arabic,
				'autoDetect'      => true,
				'fallbackCountry' => $is_arabic ? 'ae' : 'us',
				'geoUrl'          => 'https://ipapi.co/json/',
			)
		);
	}

	/**
	 * intl-tel-input styles and initializer for Form Kit tel fields.
	 */
	public static function enqueue_intl_tel_assets() {
		if ( wp_script_is( 'zskeleton-intl-tel-input', 'enqueued' ) || wp_script_is( 'zskeleton-intl-tel-input', 'done' ) ) {
			return;
		}

		$use_min  = (bool) get_option( 'zskeleton_use_minified_assets', true );
		$css_path = ZSkeleton_THEME_DIR . '/assets/vendor/intl-tel-input/css/intlTelInput.css';
		$js_file  = $use_min && is_readable( ZSkeleton_THEME_DIR . '/assets/js/form-kit-intl-tel.min.js' )
			? 'form-kit-intl-tel.min.js'
			: 'form-kit-intl-tel.js';
		$js_path  = ZSkeleton_THEME_DIR . '/assets/js/' . $js_file;

		if ( is_readable( $css_path ) ) {
			wp_enqueue_style(
				'zskeleton-intl-tel-input',
				ZSkeleton_THEME_URL . '/assets/vendor/intl-tel-input/css/intlTelInput.css',
				array(),
				(string) filemtime( $css_path )
			);
		}
		if ( is_readable( $js_path ) ) {
			wp_enqueue_script(
				'zskeleton-intl-tel-input',
				ZSkeleton_THEME_URL . '/assets/js/' . $js_file,
				array(),
				(string) filemtime( $js_path ),
				true
			);
			wp_localize_script(
				'zskeleton-intl-tel-input',
				'zskeletonIntlTelConfig',
				self::get_intl_tel_config()
			);
		}
	}
}
