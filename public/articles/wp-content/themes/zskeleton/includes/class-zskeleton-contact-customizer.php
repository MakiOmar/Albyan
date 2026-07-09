<?php
/**
 * Global contact / social in Customizer for landing pages and footers.
 *
 * Social profile URLs use the same wp_options as ZSkeleton → General settings.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Map logical social keys to wp_options (shared with ZSkeleton → General).
 *
 * @return array<string, string> Key => option name.
 */
function zskeleton_get_social_profile_options_map() {
	$map = array(
		'facebook'  => 'zskeleton_facebook_url',
		'twitter'   => 'zskeleton_twitter_url',
		'linkedin'  => 'zskeleton_linkedin_url',
		'youtube'   => 'zskeleton_youtube_url',
		'instagram' => 'zskeleton_instagram_url',
		'github'    => 'zskeleton_github_url',
		'snapchat'  => 'zskeleton_snapchat_url',
		'tiktok'    => 'zskeleton_tiktok_url',
	);

	/**
	 * Filter social keys and option names used by the Customizer and zskeleton_get_contact().
	 *
	 * @param array<string, string> $map Key => option name.
	 */
	return apply_filters( 'zskeleton_social_profile_options_map', $map );
}

/**
 * Sanitize floating WhatsApp on/off option for wp_options.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function zskeleton_sanitize_option_whatsapp_float_enabled( $value ) {
	return '1' === (string) $value ? '1' : '0';
}

/**
 * Sanitize floating WhatsApp destination URL for wp_options.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function zskeleton_sanitize_option_whatsapp_float_url( $value ) {
	$raw = trim( (string) $value );
	if ( '' === $raw ) {
		return '';
	}
	$sanitized = esc_url_raw( $raw );
	if ( '' === $sanitized ) {
		return '';
	}
	$lower = strtolower( $sanitized );
	if ( str_starts_with( $lower, 'https://' ) || str_starts_with( $lower, 'http://' ) || str_starts_with( $lower, 'tel:' ) ) {
		return $sanitized;
	}
	return '';
}

/**
 * Register Customizer section and settings.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function zskeleton_contact_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'zskeleton_contact',
		array(
			'title'       => __( 'Contact & social', 'zskeleton' ),
			'description' => __( 'Primary/secondary phone and social profile URLs match ZSkeleton → General (Contact & social); you can edit them here or on that screen.', 'zskeleton' ),
			'priority'    => 45,
		)
	);

	$contact_fields = array(
		'whatsapp' => array(
			'label'   => __( 'WhatsApp (digits or full wa.me link)', 'zskeleton' ),
			'default' => '',
		),
		'email'    => array(
			'label'   => __( 'Email', 'zskeleton' ),
			'default' => '',
		),
		'address'  => array(
			'label'   => __( 'Address', 'zskeleton' ),
			'default' => '',
		),
	);

	foreach (
		array(
			'zskeleton_contact_phone'            => array(
				'label'   => __( 'Primary phone', 'zskeleton' ),
				'default' => '',
			),
			'zskeleton_contact_phone_secondary' => array(
				'label'   => __( 'Secondary phone', 'zskeleton' ),
				'default' => '',
			),
		) as $option_name => $phone_conf
	) {
		$wp_customize->add_setting(
			$option_name,
			array(
				'type'              => 'option',
				'capability'        => 'manage_options',
				'default'           => $phone_conf['default'],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			'zskeleton_customize_' . $option_name,
			array(
				'label'    => $phone_conf['label'],
				'section'  => 'zskeleton_contact',
				'type'     => 'text',
				'settings' => $option_name,
			)
		);
	}

	foreach ( $contact_fields as $id => $conf ) {
		$setting_id = 'zskeleton_contact_' . $id;
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $conf['default'],
				'sanitize_callback' => 'zskeleton_sanitize_contact_field',
			)
		);
		$wp_customize->add_control(
			$setting_id,
			array(
				'label'    => $conf['label'],
				'section'  => 'zskeleton_contact',
				'type'     => 'text',
				'settings' => $setting_id,
			)
		);
	}

	$social_labels = array(
		'facebook'  => __( 'Facebook URL', 'zskeleton' ),
		'twitter'   => __( 'X / Twitter URL', 'zskeleton' ),
		'linkedin'  => __( 'LinkedIn URL', 'zskeleton' ),
		'youtube'   => __( 'YouTube URL', 'zskeleton' ),
		'instagram' => __( 'Instagram URL', 'zskeleton' ),
		'github'    => __( 'GitHub URL', 'zskeleton' ),
		'snapchat'  => __( 'Snapchat URL', 'zskeleton' ),
		'tiktok'    => __( 'TikTok URL', 'zskeleton' ),
	);

	foreach ( zskeleton_get_social_profile_options_map() as $key => $option_name ) {
		$label = isset( $social_labels[ $key ] ) ? $social_labels[ $key ] : $option_name;
		$wp_customize->add_setting(
			$option_name,
			array(
				'type'              => 'option',
				'capability'        => 'manage_options',
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			'zskeleton_customize_social_' . $key,
			array(
				'label'    => $label,
				'section'  => 'zskeleton_contact',
				'type'     => 'url',
				'settings' => $option_name,
			)
		);
	}

	// Floating WhatsApp (same wp_options as ZSkeleton Settings → Contact & social).
	$wp_customize->add_setting(
		'zskeleton_whatsapp_float_enabled',
		array(
			'type'              => 'option',
			'default'           => '0',
			'sanitize_callback' => 'zskeleton_sanitize_option_whatsapp_float_enabled',
		)
	);
	$wp_customize->add_control(
		'zskeleton_whatsapp_float_enabled',
		array(
			'label'       => __( 'Show floating WhatsApp button', 'zskeleton' ),
			'description' => __( 'Fixed on the bottom inline-start (left in LTR, right in RTL), opposite the back to top button.', 'zskeleton' ),
			'section'     => 'zskeleton_contact',
			'type'        => 'checkbox',
			'settings'    => 'zskeleton_whatsapp_float_enabled',
		)
	);

	$wp_customize->add_setting(
		'zskeleton_whatsapp_float_url',
		array(
			'type'              => 'option',
			'default'           => '',
			'sanitize_callback' => 'zskeleton_sanitize_option_whatsapp_float_url',
		)
	);
	$wp_customize->add_control(
		'zskeleton_whatsapp_float_url',
		array(
			'label'       => __( 'WhatsApp link URL', 'zskeleton' ),
			'description' => __( 'Example: https://wa.me/15551234567 — required for the button to appear.', 'zskeleton' ),
			'section'     => 'zskeleton_contact',
			'type'        => 'url',
			'settings'    => 'zskeleton_whatsapp_float_url',
		)
	);
}
add_action( 'customize_register', 'zskeleton_contact_customize_register' );

/**
 * @param string $value Raw value.
 * @return string
 */
function zskeleton_sanitize_contact_field( $value ) {
	if ( is_string( $value ) && ( strpos( $value, 'http://' ) === 0 || strpos( $value, 'https://' ) === 0 ) ) {
		return esc_url_raw( $value );
	}
	return sanitize_text_field( $value );
}

/**
 * Get a contact or social field (filterable).
 *
 * Phone (primary/secondary) and email read wp_options first (ZSkeleton → Contact & social), then theme mods.
 * WhatsApp reads theme mod zskeleton_contact_whatsapp first, then the floating WhatsApp URL option
 * (zskeleton_whatsapp_float_url) if empty, so the social widget and header work when only the float button is configured.
 * Other non-social fields use theme mods (zskeleton_contact_*). Social URLs use the same wp_options
 * as ZSkeleton → General. If an option is empty, legacy theme mod zskeleton_contact_{key} is used
 * for any network (older Customizer / imports).
 *
 * @param string $key phone|phone_secondary|whatsapp|email|membership_email|media_email|address|facebook|twitter|linkedin|youtube|instagram|github|snapchat|tiktok.
 * @return string
 */
function zskeleton_get_contact( $key ) {
	$key = sanitize_key( $key );

	$social_map  = zskeleton_get_social_profile_options_map();
	$contact_ids = array( 'phone', 'phone_secondary', 'whatsapp', 'email', 'membership_email', 'media_email', 'address' );
	$allow       = array_merge( $contact_ids, array_keys( $social_map ) );

	if ( ! in_array( $key, $allow, true ) ) {
		return '';
	}

	if ( isset( $social_map[ $key ] ) ) {
		$opt = get_option( $social_map[ $key ], '' );
		$val = is_string( $opt ) ? $opt : '';

		// Legacy: social URLs may still live in theme mods zskeleton_contact_{key} (any network).
		if ( '' === trim( $val ) ) {
			$legacy = get_theme_mod( 'zskeleton_contact_' . $key, '' );
			$val    = is_string( $legacy ) ? $legacy : '';
		}
	} else {
		$val = '';
		switch ( $key ) {
			case 'phone':
				$val = (string) get_option( 'zskeleton_contact_phone', '' );
				if ( '' === trim( $val ) ) {
					$val = (string) get_theme_mod( 'zskeleton_contact_phone', '' );
				}
				break;
			case 'phone_secondary':
				$val = (string) get_option( 'zskeleton_contact_phone_secondary', '' );
				if ( '' === trim( $val ) ) {
					$val = (string) get_theme_mod( 'zskeleton_contact_phone_secondary', '' );
				}
				break;
			case 'email':
				$val = (string) get_option( 'zskeleton_contact_email', '' );
				if ( '' === trim( $val ) ) {
					$val = (string) get_theme_mod( 'zskeleton_contact_email', '' );
				}
				break;
			case 'membership_email':
				$stored = get_option( 'zskeleton_membership_email', false );
				$val    = ( false === $stored ) ? 'membership@zskeleton.org' : (string) $stored;
				break;
			case 'media_email':
				$stored = get_option( 'zskeleton_media_email', false );
				$val    = ( false === $stored ) ? 'media@zskeleton.org' : (string) $stored;
				break;
			case 'whatsapp':
				$val = (string) get_theme_mod( 'zskeleton_contact_whatsapp', '' );
				// Many sites set only the floating button URL in ZSkeleton Settings; use it when Customizer is empty.
				if ( '' === trim( $val ) ) {
					$val = (string) get_option( 'zskeleton_whatsapp_float_url', '' );
				}
				break;
			default:
				$val = (string) get_theme_mod( 'zskeleton_contact_' . $key, '' );
				break;
		}
		$val = is_string( $val ) ? $val : '';
	}

	return apply_filters( 'zskeleton_contact_info', $val, $key );
}
