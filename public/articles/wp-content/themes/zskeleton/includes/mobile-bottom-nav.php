<?php
/**
 * Mobile bottom navigation (Style 2) helpers.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile bottom navigation bar style from Appearance → ZSkeleton Settings.
 *
 * @return string style1|style2
 */
function zskeleton_get_mobile_bottom_nav_style(): string {
	$raw = (string) get_option( 'zskeleton_mobile_bottom_nav_style', 'style1' );
	if ( function_exists( 'zskeleton_sanitize_option_mobile_bottom_nav_style' ) ) {
		return zskeleton_sanitize_option_mobile_bottom_nav_style( $raw );
	}
	$v = strtolower( trim( $raw ) );
	return in_array( $v, array( 'style1', 'style2' ), true ) ? $v : 'style1';
}

/**
 * Normalize social href values stored without a scheme (matches social icons widget behavior).
 *
 * @param string $raw Raw.
 * @return string
 */
function zskeleton_normalize_social_href_for_share( $raw ): string {
	$raw = trim( (string) $raw );
	if ( '' === $raw ) {
		return '';
	}
	if ( strlen( $raw ) >= 2 && '//' === substr( $raw, 0, 2 ) ) {
		$raw = 'https:' . $raw;
	}
	if ( preg_match( '#^(https?|mailto:|tel:)#i', $raw ) ) {
		return $raw;
	}
	if ( preg_match( '/^[a-z0-9.-]+\.[a-z]{2,}[\w\-./?#&=:%+~]*$/i', $raw ) ) {
		return 'https://' . $raw;
	}
	return $raw;
}

/**
 * WhatsApp URL from Contact & social (digits or full URL).
 *
 * @return string Empty when unset.
 */
function zskeleton_get_whatsapp_url_from_contact(): string {
	if ( ! function_exists( 'zskeleton_get_contact' ) ) {
		return '';
	}
	$raw = trim( (string) zskeleton_get_contact( 'whatsapp' ) );
	if ( '' === $raw ) {
		return '';
	}
	if ( strlen( $raw ) >= 2 && '//' === substr( $raw, 0, 2 ) ) {
		$raw = 'https:' . $raw;
	}
	if ( preg_match( '#^https?://#i', $raw ) ) {
		$out = esc_url_raw( $raw );
		return is_string( $out ) && '' !== $out ? $out : '';
	}
	$digits = preg_replace( '/\D+/', '', $raw );
	return '' !== $digits ? 'https://wa.me/' . $digits : '';
}

/**
 * Resolved WhatsApp link for mobile bottom nav Style 2 (float URL wins when set).
 *
 * @return string
 */
function zskeleton_get_mobile_bottom_nav_whatsapp_url(): string {
	$float = function_exists( 'zskeleton_get_whatsapp_float_button_url' ) ? trim( (string) zskeleton_get_whatsapp_float_button_url() ) : '';
	if ( '' !== $float ) {
		$url = esc_url( $float );
		return is_string( $url ) && '' !== $url ? $url : '';
	}
	$contact = zskeleton_get_whatsapp_url_from_contact();
	if ( '' !== $contact ) {
		$url = esc_url( $contact );
		return is_string( $url ) && '' !== $url ? $url : '';
	}
	$url = esc_url( home_url( '/' ) );
	return is_string( $url ) && '' !== $url ? $url : '';
}

/**
 * Cart URL when WooCommerce is available.
 *
 * @return string
 */
function zskeleton_get_mobile_bottom_nav_cart_url(): string {
	if ( function_exists( 'wc_get_cart_url' ) ) {
		$url = (string) wc_get_cart_url();
		$url = esc_url( $url );
		return is_string( $url ) && '' !== $url ? $url : esc_url( home_url( '/' ) );
	}
	$shop = function_exists( 'zskeleton_get_page_url' ) ? zskeleton_get_page_url( 'shop', '' ) : '';
	if ( is_string( $shop ) && '' !== $shop && '#' !== $shop ) {
		$url = esc_url( $shop );
		return is_string( $url ) && '' !== $url ? $url : esc_url( home_url( '/' ) );
	}
	return esc_url( home_url( '/' ) );
}

/**
 * Bell / alerts link for Style 2 (profile when logged in, login otherwise).
 *
 * @return string
 */
function zskeleton_get_mobile_bottom_nav_bell_url(): string {
	if ( is_user_logged_in() ) {
		$profile = function_exists( 'zskeleton_get_page_url' ) ? zskeleton_get_page_url( 'profile', home_url( '/profile/' ) ) : home_url( '/profile/' );
		$url     = esc_url( is_string( $profile ) ? $profile : home_url( '/profile/' ) );
		return is_string( $url ) && '' !== $url ? $url : esc_url( home_url( '/' ) );
	}

	$redirect = function_exists( 'zskeleton_get_page_url' ) ? zskeleton_get_page_url( 'profile', home_url( '/profile/' ) ) : home_url( '/profile/' );
	$redirect = is_string( $redirect ) ? $redirect : home_url( '/profile/' );

	$login = function_exists( 'zskeleton_get_auth_page_url' )
		? zskeleton_get_auth_page_url( 'login', $redirect )
		: wp_login_url( $redirect );

	$url = esc_url( (string) $login );
	return is_string( $url ) && '' !== $url ? $url : esc_url( home_url( '/' ) );
}

/**
 * Network label for share popover (translation-ready).
 *
 * @param string $key Network key.
 * @return string
 */
function zskeleton_get_mobile_bottom_nav_share_network_label( $key ): string {
	$key = sanitize_key( (string) $key );
	$labels = array(
		'snapchat'  => _x( 'Snapchat', 'social share', 'zskeleton' ),
		'facebook'  => _x( 'Facebook', 'social share', 'zskeleton' ),
		'twitter'   => _x( 'X (Twitter)', 'social share', 'zskeleton' ),
		'whatsapp'  => _x( 'WhatsApp', 'social share', 'zskeleton' ),
		'instagram' => _x( 'Instagram', 'social share', 'zskeleton' ),
		'youtube'   => _x( 'YouTube', 'social share', 'zskeleton' ),
		'tiktok'    => _x( 'TikTok', 'social share', 'zskeleton' ),
		'linkedin'  => _x( 'LinkedIn', 'social share', 'zskeleton' ),
	);
	return isset( $labels[ $key ] ) ? $labels[ $key ] : ucfirst( $key );
}

/**
 * Build a native share intent URL for the current page.
 *
 * @param string $network Network key.
 * @param string $page_url Canonical page URL.
 * @param string $title   Page title.
 * @return string Empty when not supported / missing profile URL.
 */
function zskeleton_get_mobile_bottom_nav_native_share_url( $network, $page_url, $title ): string {
	$network = sanitize_key( (string) $network );
	$page_url = trim( (string) $page_url );
	$title = (string) $title;
	if ( '' === $page_url ) {
		return '';
	}

	$u = rawurlencode( $page_url );
	$t = rawurlencode( $title );
	$text = rawurlencode( trim( $title . ' ' . $page_url ) );

	switch ( $network ) {
		case 'facebook':
			return 'https://www.facebook.com/sharer/sharer.php?u=' . $u;
		case 'twitter':
			return 'https://twitter.com/intent/tweet?url=' . $u . '&text=' . $t;
		case 'linkedin':
			return 'https://www.linkedin.com/sharing/share-offsite/?url=' . $u;
		case 'whatsapp':
			return 'https://api.whatsapp.com/send?text=' . $text;
		default:
			return '';
	}
}

/**
 * SVG icon markup for share popover buttons (fill currentColor).
 *
 * @param string $key Network key.
 * @return string Unescaped SVG HTML; escape at output with wp_kses().
 */
function zskeleton_get_mobile_bottom_nav_share_svg( $key ): string {
	$key = sanitize_key( (string) $key );
	$common = '<svg class="zskeleton-mbn2-share__icon" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="currentColor">';
	switch ( $key ) {
		case 'snapchat':
			return $common . '<path d="M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12 1.033-.301.165-.088.344-.104.464-.104.182 0 .359.029.509.09.45.149.734.479.734.838.015.449-.39.839-1.213 1.168-.089.029-.209.075-.344.119-.45.135-1.139.36-1.333.81-.09.224-.061.524.12.868l.015.015c.06.136 1.526 3.475 4.791 4.014.255.044.435.27.42.509 0 .075-.015.149-.045.225-.24.569-1.273.988-3.146 1.271-.059.091-.12.375-.164.57-.029.179-.074.36-.134.553-.076.271-.27.405-.555.405h-.03c-.135 0-.313-.031-.538-.074-.36-.075-.765-.135-1.273-.135-.3 0-.599.015-.913.074-.6.104-1.123.464-1.723.884-.853.599-1.826 1.288-3.294 1.288-.06 0-.119-.015-.18-.015h-.149c-1.468 0-2.427-.675-3.279-1.288-.599-.42-1.107-.779-1.707-.884-.314-.045-.629-.074-.928-.074-.54 0-.958.089-1.272.149-.211.043-.391.074-.54.074-.374 0-.523-.224-.583-.42-.061-.192-.09-.389-.135-.567-.046-.181-.105-.494-.166-.57-1.918-.222-2.95-.642-3.189-1.226-.031-.063-.052-.15-.055-.225-.015-.243.165-.465.42-.509 3.264-.54 4.73-3.879 4.791-4.02l.016-.029c.18-.345.224-.645.119-.869-.195-.434-.884-.658-1.332-.809-.121-.029-.24-.074-.346-.119-1.107-.435-1.257-.93-1.197-1.273.09-.479.674-.793 1.168-.793.146 0 .27.029.383.074.42.194.789.3 1.104.3.234 0 .384-.06.465-.105l-.046-.569c-.098-1.626-.225-3.651.307-4.837C7.392 1.077 10.739.807 11.727.807l.419-.015h.06z"/></svg>';
		case 'facebook':
			return $common . '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';
		case 'twitter':
			return $common . '<path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>';
		case 'whatsapp':
			return $common . '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
		case 'instagram':
			return $common . '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.35 3.608 1.325.975.975 1.263 2.242 1.325 3.608.058 1.266.069 1.646.069 4.85s-.012 3.584-.069 4.85c-.062 1.366-.35 2.633-1.325 3.608-.975.975-2.242 1.263-3.608 1.325-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.35-3.608-1.325-.975-.975-1.263-2.242-1.325-3.608-.058-1.266-.07-1.646-.07-4.85s.012-3.584.07-4.85c.062-1.366.35-2.633 1.325-3.608.975-.975 2.242-1.263 3.608-1.325 1.266-.058 1.646-.07 4.85-.07zM12 0C8.741 0 8.333.014 7.053.072 5.771.132 4.659.333 3.67.63c-.987.306-1.87.717-2.648 1.496S.936 3.672.63 4.64C.333 5.631.131 6.743.072 8.025.012 9.305 0 9.713 0 12s.012 2.695.072 3.975c.059 1.281.261 2.394.63 3.36.306.968.717 1.85 1.496 2.628.778.779 1.66 1.19 2.628 1.496.966.369 2.08.57 3.36.63 1.28.06 1.688.072 3.947.072s2.667-.012 3.947-.072c1.281-.059 2.394-.261 3.36-.63.968-.306 1.85-.717 2.628-1.496.779-.778 1.19-1.66 1.496-2.628.369-.966.57-2.079.63-3.36.06-1.28.072-1.689.072-3.947s-.012-2.667-.072-3.947c-.059-1.281-.261-2.394-.63-3.36-.306-.968-.717-1.85-1.496-2.628-.778-.779-1.66-1.19-2.628-1.496-.966-.369-2.08-.57-3.36-.63C14.667.014 14.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>';
		case 'youtube':
			return $common . '<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>';
		case 'tiktok':
			return $common . '<path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>';
		case 'linkedin':
			return $common . '<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>';
		default:
			return '';
	}
}

/**
 * Share grid items for the Style 2 popover (fixed order; items may be disabled when unset).
 *
 * @return array<int, array{key:string,url:string,label:string,enabled:bool}>
 */
function zskeleton_get_mobile_bottom_nav_share_items(): array {
	$order = apply_filters(
		'zskeleton_mobile_bottom_nav_share_order',
		array( 'snapchat', 'facebook', 'twitter', 'whatsapp', 'instagram', 'youtube', 'tiktok', 'linkedin' )
	);

	$page_url = '';
	if ( function_exists( 'wp_get_canonical_url' ) ) {
		$page_url = (string) wp_get_canonical_url();
	}
	if ( '' === $page_url ) {
		if ( is_singular() ) {
			$page_url = (string) get_permalink();
		} else {
			$page_url = home_url( '/' );
		}
	}

	$title = is_singular() ? (string) get_the_title() : (string) get_bloginfo( 'name' );

	$map = function_exists( 'zskeleton_get_social_profile_options_map' ) ? zskeleton_get_social_profile_options_map() : array();
	$out = array();

	foreach ( $order as $key ) {
		$key = sanitize_key( (string) $key );
		$url = '';
		$enabled = false;

		if ( 'whatsapp' === $key ) {
			$url = zskeleton_get_mobile_bottom_nav_native_share_url( 'whatsapp', $page_url, $title );
			$enabled = '' !== $url;
		} elseif ( isset( $map[ $key ] ) && function_exists( 'zskeleton_get_contact' ) ) {
			$raw = trim( (string) zskeleton_get_contact( $key ) );
			if ( '' !== $raw ) {
				$raw = zskeleton_normalize_social_href_for_share( $raw );
				$native = zskeleton_get_mobile_bottom_nav_native_share_url( $key, $page_url, $title );
				if ( in_array( $key, array( 'facebook', 'twitter', 'linkedin' ), true ) && '' !== $native ) {
					$url = $native;
				} else {
					$maybe = esc_url_raw( $raw );
					$url   = is_string( $maybe ) && '' !== $maybe ? $maybe : '';
				}
				$enabled = is_string( $url ) && '' !== $url;
			}
		}

		$out[] = array(
			'key'     => $key,
			'url'     => is_string( $url ) ? $url : '',
			'label'   => zskeleton_get_mobile_bottom_nav_share_network_label( $key ),
			'enabled' => (bool) $enabled,
		);
	}

	/**
	 * Filter resolved share grid items for mobile bottom nav Style 2.
	 *
	 * @param array<int, array{key:string,url:string,label:string,enabled:bool}> $out
	 */
	return apply_filters( 'zskeleton_mobile_bottom_nav_share_items', $out );
}
