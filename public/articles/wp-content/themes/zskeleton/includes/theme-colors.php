<?php
/**
 * Theme color palette: defaults, resolution from options, and CSS output.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default “fancy” dusty palette (mobile-first, soft contrast).
 *
 * @return array<string, string> slug => hex
 */
function zskeleton_get_theme_color_defaults() {
	return array(
		'primary'             => '#647FBC',
		'secondary'           => '#91ADC8',
		'accent'              => '#AED6CF',
		'background'          => '#FAFDD6',
		'button_background'   => '#647FBC',
		'button_text'         => '#000000',
		'counter_text'        => '#647FBC',
	);
}

/**
 * Convert #RGB or #RRGGBB to RGB triplet or null.
 *
 * @param string $hex Hex color.
 * @return int[]|null
 */
function zskeleton_hex_to_rgb( $hex ) {
	$hex = is_string( $hex ) ? strtoupper( trim( $hex ) ) : '';
	$hex = ltrim( $hex, '#' );
	if ( preg_match( '/^([0-9A-F]{3})$/', $hex, $m ) ) {
		$h = $m[1];
		$hex = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
	}
	if ( ! preg_match( '/^([0-9A-F]{6})$/', $hex ) ) {
		return null;
	}
	return array(
		hexdec( substr( $hex, 0, 2 ) ),
		hexdec( substr( $hex, 2, 2 ) ),
		hexdec( substr( $hex, 4, 2 ) ),
	);
}

/**
 * RGB triplet to #RRGGBB.
 *
 * @param int $r Red.
 * @param int $g Green.
 * @param int $b Blue.
 * @return string
 */
function zskeleton_rgb_to_hex( $r, $g, $b ) {
	$r = (int) max( 0, min( 255, round( $r ) ) );
	$g = (int) max( 0, min( 255, round( $g ) ) );
	$b = (int) max( 0, min( 255, round( $b ) ) );
	return sprintf( '#%02X%02X%02X', $r, $g, $b );
}

/**
 * Linear mix between two hex colors (0 = all first, 1 = all second).
 *
 * @param string $hex_a First color.
 * @param string $hex_b Second color.
 * @param float  $weight_b Weight for second color.
 * @return string
 */
function zskeleton_mix_hex_colors( $hex_a, $hex_b, $weight_b = 0.5 ) {
	$a = zskeleton_hex_to_rgb( $hex_a );
	$b = zskeleton_hex_to_rgb( $hex_b );
	if ( ! $a || ! $b ) {
		return $hex_a;
	}
	$w = max( 0.0, min( 1.0, (float) $weight_b ) );
	$r = $a[0] * ( 1 - $w ) + $b[0] * $w;
	$g = $a[1] * ( 1 - $w ) + $b[1] * $w;
	$bl = $a[2] * ( 1 - $w ) + $b[2] * $w;
	return zskeleton_rgb_to_hex( $r, $g, $bl );
}

/**
 * Sanitize a hex color; return default when invalid.
 *
 * WordPress Iris / wpColorPicker often keeps the input as rgb() or rgba(); sanitize_hex_color()
 * only accepts #RGB/#RRGGBB, so those values would otherwise always fall back to the default.
 *
 * @param mixed  $value   Raw value.
 * @param string $default Default hex.
 * @return string
 */
function zskeleton_sanitize_hex_color_with_default( $value, $default ) {
	if ( is_string( $value ) ) {
		$value = trim( $value );
	} elseif ( is_scalar( $value ) ) {
		$value = trim( (string) $value );
	} else {
		$value = '';
	}

	$clean = sanitize_hex_color( $value );
	if ( ! empty( $clean ) ) {
		return $clean;
	}

	// Iris and some browsers submit rgb()/rgba() instead of #hex.
	if ( preg_match( '/rgba?\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})/i', $value, $m ) ) {
		$hex   = zskeleton_rgb_to_hex( (int) $m[1], (int) $m[2], (int) $m[3] );
		$clean = sanitize_hex_color( $hex );
		if ( ! empty( $clean ) ) {
			return $clean;
		}
	}

	return $default;
}

/**
 * Sanitize callbacks for theme color options (Settings API).
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function zskeleton_sanitize_option_primary_color( $value ) {
	return zskeleton_sanitize_hex_color_with_default( $value, zskeleton_get_theme_color_defaults()['primary'] );
}

/**
 * @param mixed $value Submitted value.
 * @return string
 */
function zskeleton_sanitize_option_secondary_color( $value ) {
	return zskeleton_sanitize_hex_color_with_default( $value, zskeleton_get_theme_color_defaults()['secondary'] );
}

/**
 * @param mixed $value Submitted value.
 * @return string
 */
function zskeleton_sanitize_option_accent_color( $value ) {
	return zskeleton_sanitize_hex_color_with_default( $value, zskeleton_get_theme_color_defaults()['accent'] );
}

/**
 * @param mixed $value Submitted value.
 * @return string
 */
function zskeleton_sanitize_option_background_color( $value ) {
	return zskeleton_sanitize_hex_color_with_default( $value, zskeleton_get_theme_color_defaults()['background'] );
}

/**
 * @param mixed $value Submitted value.
 * @return string
 */
function zskeleton_sanitize_option_button_background_color( $value ) {
	return zskeleton_sanitize_hex_color_with_default( $value, zskeleton_get_theme_color_defaults()['button_background'] );
}

/**
 * @param mixed $value Submitted value.
 * @return string
 */
function zskeleton_sanitize_option_counter_text_color( $value ) {
	return zskeleton_sanitize_hex_color_with_default( $value, zskeleton_get_theme_color_defaults()['counter_text'] );
}

/**
 * @param mixed $value Submitted value.
 * @return string
 */
function zskeleton_sanitize_option_button_text_color( $value ) {
	return zskeleton_sanitize_hex_color_with_default( $value, zskeleton_get_theme_color_defaults()['button_text'] );
}

/**
 * Resolved theme colors from options (admin + Customizer when bound to options).
 *
 * @return array<string, string>
 */
function zskeleton_get_resolved_theme_colors() {
	$defaults = zskeleton_get_theme_color_defaults();

	$primary = zskeleton_sanitize_hex_color_with_default(
		get_option( 'zskeleton_primary_color', '' ),
		$defaults['primary']
	);
	$secondary = zskeleton_sanitize_hex_color_with_default(
		get_option( 'zskeleton_secondary_color', '' ),
		$defaults['secondary']
	);
	$accent = zskeleton_sanitize_hex_color_with_default(
		get_option( 'zskeleton_accent_color', '' ),
		$defaults['accent']
	);
	$background = zskeleton_sanitize_hex_color_with_default(
		get_option( 'zskeleton_background_color', '' ),
		$defaults['background']
	);
	$button_background = zskeleton_sanitize_hex_color_with_default(
		get_option( 'zskeleton_button_background_color', '' ),
		$defaults['button_background']
	);
	$counter_text = zskeleton_sanitize_hex_color_with_default(
		get_option( 'zskeleton_counter_text_color', '' ),
		$defaults['counter_text']
	);
	$button_text = zskeleton_sanitize_hex_color_with_default(
		get_option( 'zskeleton_button_text_color', '' ),
		$defaults['button_text']
	);

	$navy        = zskeleton_mix_hex_colors( $primary, '#0F172A', 0.42 );
	$bg_soft     = zskeleton_mix_hex_colors( $background, '#FFFFFF', 0.35 );
	$border_soft = zskeleton_mix_hex_colors( $secondary, '#FFFFFF', 0.55 );
	$card        = zskeleton_mix_hex_colors( '#FFFFFF', $background, 0.12 );

	// Primary buttons use Secondary Color on hover/focus (see theme settings).
	$button_hover = $secondary;

	return array(
		'primary'             => $primary,
		'secondary'           => $secondary,
		'accent'              => $accent,
		'background'          => $background,
		'button_background'   => $button_background,
		'button_background_hover' => $button_hover,
		'button_text'         => $button_text,
		'counter_text'        => $counter_text,
		'navy'                => $navy,
		'background_soft'     => $bg_soft,
		'border_soft'         => $border_soft,
		'card_surface'        => $card,
	);
}

/**
 * Default option values for ZSkeleton → Layout (top bar / footer copyright strip).
 * Gradient stops match the live theme: Primary → academic navy (same as :root and .header-top).
 *
 * @return array<string, string> Option name => value.
 */
function zskeleton_get_layout_bars_default_option_values() {
	$c = zskeleton_get_resolved_theme_colors();
	$br = '#374151';
	return array(
		'zskeleton_top_bar_gradient_color_start'         => $c['primary'],
		'zskeleton_top_bar_gradient_color_end'            => $c['navy'],
		'zskeleton_top_bar_text_color'                 => '#ffffff',
		'zskeleton_top_bar_padding_y'                 => '8px',
		'zskeleton_top_bar_content_min_height'         => '25px',
		'zskeleton_footer_copyright_card_gradient_start'  => $c['primary'],
		'zskeleton_footer_copyright_card_gradient_end'   => $c['navy'],
		'zskeleton_footer_copyright_card_text_color'   => '#ffffff',
		'zskeleton_footer_copyright_card_border_top_hidden' => '0',
		'zskeleton_footer_copyright_card_border_top_color'  => $br,
		'zskeleton_footer_copyright_card_padding_y'   => '20px',
		'zskeleton_footer_copyright_card_min_height'  => '',
	);
}

/**
 * Inline :root CSS for design tokens (legacy + named palette).
 *
 * @return string
 */
function zskeleton_get_theme_color_css_variables() {
	$c = zskeleton_get_resolved_theme_colors();

	$primary_e   = esc_attr( $c['primary'] );
	$secondary_e = esc_attr( $c['secondary'] );
	$accent_e    = esc_attr( $c['accent'] );
	$bg_e        = esc_attr( $c['background'] );
	$navy_e      = esc_attr( $c['navy'] );
	$bg_soft_e   = esc_attr( $c['background_soft'] );
	$border_e    = esc_attr( $c['border_soft'] );
	$card_e      = esc_attr( $c['card_surface'] );
	$btn_bg_e    = esc_attr( $c['button_background'] );
	$btn_hov_e   = esc_attr( $c['button_background_hover'] );
	$btn_txt_e   = esc_attr( $c['button_text'] );
	$counter_e   = esc_attr( $c['counter_text'] );

	$css  = ':root{';
	$css .= '--zskeleton-color-primary:' . $primary_e . ';';
	$css .= '--zskeleton-color-secondary:' . $secondary_e . ';';
	$css .= '--zskeleton-color-accent:' . $accent_e . ';';
	$css .= '--zskeleton-color-background:' . $bg_e . ';';
	$css .= '--zskeleton-color-button-background:' . $btn_bg_e . ';';
	$css .= '--zskeleton-color-button-background-hover:' . $btn_hov_e . ';';
	$css .= '--zskeleton-color-button-text:' . $btn_txt_e . ';';
	$css .= '--zskeleton-color-counter-text:' . $counter_e . ';';
	$css .= '--primary-blue:' . $primary_e . ';';
	$css .= '--academic-navy:' . $navy_e . ';';
	$css .= '--zskeleton-accent-gold:' . $secondary_e . ';';
	$css .= '--background-light:' . $bg_soft_e . ';';
	$css .= '--card-white:' . $card_e . ';';
	$css .= '--border-light:' . $border_e . ';';
	$nav_h = '';
	$nav_a = '';
	if ( function_exists( 'zskeleton_sanitize_option_nav_item_background' ) ) {
		$nav_h = zskeleton_sanitize_option_nav_item_background( (string) get_option( 'zskeleton_nav_item_hover_bg', '' ) );
		$nav_a = zskeleton_sanitize_option_nav_item_background( (string) get_option( 'zskeleton_nav_item_active_bg', '' ) );
	}
	$menu_btn_bar = function_exists( 'zskeleton_sanitize_option_mobile_menu_button_bar_color' )
		? zskeleton_sanitize_option_mobile_menu_button_bar_color( (string) get_option( 'zskeleton_mobile_menu_button_bar_color', '#ffffff' ) )
		: '#ffffff';
	$menu_btn_bg = function_exists( 'zskeleton_sanitize_option_mobile_menu_button_background_color' )
		? zskeleton_sanitize_option_mobile_menu_button_background_color( (string) get_option( 'zskeleton_mobile_menu_button_background_color', $primary_e ) )
		: $primary_e;
	$menu_btn_border_w = function_exists( 'zskeleton_sanitize_option_mobile_menu_button_border_width' )
		? zskeleton_sanitize_option_mobile_menu_button_border_width( (string) get_option( 'zskeleton_mobile_menu_button_border_width', '2' ) )
		: '2';
	$menu_btn_border = function_exists( 'zskeleton_sanitize_option_mobile_menu_button_border_color' )
		? zskeleton_sanitize_option_mobile_menu_button_border_color( (string) get_option( 'zskeleton_mobile_menu_button_border_color', $menu_btn_bg ) )
		: $menu_btn_bg;
	if ( '' === $menu_btn_bar ) {
		$menu_btn_bar = '#ffffff';
	}
	if ( '' === $menu_btn_bg ) {
		$menu_btn_bg = $primary_e;
	}
	if ( '' === $menu_btn_border ) {
		$menu_btn_border = $menu_btn_bg;
	}
	$nav_h_e = ( '' === $nav_h ) ? 'transparent' : esc_attr( $nav_h );
	$nav_a_e = ( '' === $nav_a ) ? '' : esc_attr( $nav_a );
	$css .= '--zskeleton-nav-item-hover-bg:' . $nav_h_e . ';';
	$css .= '--zskeleton-nav-item-active-bg:' . ( '' !== $nav_a_e ? $nav_a_e : 'var(--zskeleton-nav-item-hover-bg)' ) . ';';
	$css .= '--zskeleton-mobile-menu-button-bar-color:' . esc_attr( $menu_btn_bar ) . ';';
	$css .= '--zskeleton-mobile-menu-button-background:' . esc_attr( $menu_btn_bg ) . ';';
	$css .= '--zskeleton-mobile-menu-button-border-width:' . esc_attr( $menu_btn_border_w ) . 'px;';
	$css .= '--zskeleton-mobile-menu-button-border-color:' . esc_attr( $menu_btn_border ) . ';';
	$menu_close_bg_hex = '';
	if ( function_exists( 'zskeleton_sanitize_option_layout_optional_hex_color' ) ) {
		$menu_close_bg_hex = zskeleton_sanitize_option_layout_optional_hex_color( (string) get_option( 'zskeleton_mobile_menu_close_background', '' ) );
	}
	$menu_close_br = '50%';
	if ( function_exists( 'zskeleton_sanitize_option_mobile_menu_close_border_radius' ) ) {
		$menu_close_br = zskeleton_sanitize_option_mobile_menu_close_border_radius( (string) get_option( 'zskeleton_mobile_menu_close_border_radius', '50%' ) );
	}
	$menu_close_bg_e = ( '' === $menu_close_bg_hex ) ? 'transparent' : esc_attr( $menu_close_bg_hex );
	$menu_close_tx = '#374151';
	if ( function_exists( 'zskeleton_sanitize_option_mobile_menu_close_text_color' ) ) {
		$menu_close_tx = zskeleton_sanitize_option_mobile_menu_close_text_color( (string) get_option( 'zskeleton_mobile_menu_close_text_color', '#374151' ) );
	}
	$css .= '--zskeleton-mobile-menu-close-background:' . $menu_close_bg_e . ';';
	$css .= '--zskeleton-mobile-menu-close-border-radius:' . esc_attr( $menu_close_br ) . ';';
	$css .= '--zskeleton-mobile-menu-close-color:' . esc_attr( $menu_close_tx ) . ';';
	$drawer_mode = 'default';
	if ( function_exists( 'zskeleton_sanitize_option_mobile_menu_drawer_width' ) ) {
		$drawer_mode = zskeleton_sanitize_option_mobile_menu_drawer_width( (string) get_option( 'zskeleton_mobile_menu_drawer_width', 'default' ) );
	} else {
		$dw = strtolower( trim( (string) get_option( 'zskeleton_mobile_menu_drawer_width', 'default' ) ) );
		$drawer_mode = in_array( $dw, array( 'default', 'full' ), true ) ? $dw : 'default';
	}
	$drawer_w   = ( 'full' === $drawer_mode ) ? '100%' : '85%';
	$drawer_max = ( 'full' === $drawer_mode ) ? 'none' : '320px';
	$css .= '--zskeleton-mobile-menu-drawer-width:' . esc_attr( $drawer_w ) . ';';
	$css .= '--zskeleton-mobile-menu-drawer-max-width:' . esc_attr( $drawer_max ) . ';';
	$css .= '}';

	// Page backdrop and smooth section rhythm (mobile-first).
	$css .= 'body{background-color:var(--zskeleton-color-background);color:var(--professional-gray);-webkit-font-smoothing:antialiased;}';
	$css .= '.site,#page{background-color:transparent;}';
	$css .= '.header-top,.header-topbar-split{background:linear-gradient(135deg,var(--zskeleton-color-primary) 0%,var(--academic-navy) 100%);box-shadow:0 1px 0 rgba(15,23,42,0.06);}';
	$css .= '.formal-card,.content-card,.main-content article{border-radius:12px;transition:box-shadow .25s ease,transform .2s ease;}';
	$css .= '@media (hover:hover){.formal-card:hover,.content-card:hover{box-shadow:0 12px 40px -12px rgba(15,23,42,0.12);}}';
	$css .= '.site-footer{border-top:1px solid var(--border-light);background:linear-gradient(135deg,var(--zskeleton-color-primary) 0%,var(--academic-navy) 100%);color:#fff;padding-bottom:0;}';
	$css .= '.site-footer h3,.site-footer a{color:#fff;}';

	// Exclude .zskeleton-slider__btn (CTA links), .zskeleton-slider__nav / __dot (carousel controls): those use slider meta + per-root CSS variables on #zskeleton-slider-{id}; without :not() this chain matches them and forces theme button colors.
	$btn_sel = 'button:not(.menu-toggle):not(.menu-close):not(.zskeleton-mbn2__btn):not(.zskeleton-slider__btn):not(.zskeleton-slider__nav):not(.zskeleton-slider__dot):not(.btn-secondary):not(.btn-black):not(.btn-gold):not(.btn-outline),input[type="button"]:not(.zskeleton-slider__btn):not(.zskeleton-slider__nav):not(.zskeleton-slider__dot):not(.btn-secondary):not(.btn-black),input[type="submit"]:not(.zskeleton-slider__btn):not(.zskeleton-slider__nav):not(.zskeleton-slider__dot):not(.btn-secondary):not(.btn-black),input[type="reset"]:not(.zskeleton-slider__btn):not(.zskeleton-slider__nav):not(.zskeleton-slider__dot):not(.btn-secondary):not(.btn-black),a.button:not(.zskeleton-slider__btn):not(.btn-secondary):not(.btn-black):not(.btn-gold),a.btn:not(.zskeleton-slider__btn):not(.btn-secondary):not(.btn-black):not(.btn-gold):not(.btn-outline),.button:not(.zskeleton-slider__btn):not(.btn-secondary),.btn:not(.zskeleton-slider__btn):not(.btn-secondary):not(.btn-black):not(.btn-gold):not(.btn-outline)';
	$css .= $btn_sel . '{background:' . $btn_bg_e . ';border-color:' . $btn_bg_e . ';color:' . $btn_txt_e . ';}';
	$css .= $btn_sel . ':hover,' . $btn_sel . ':focus{background:' . $btn_hov_e . ';border-color:' . $btn_hov_e . ';color:' . $btn_txt_e . ';}';
	$css .= '.btn-primary:not(.zskeleton-slider__btn){background:' . $btn_bg_e . ';border-color:' . $btn_bg_e . ';color:' . $btn_txt_e . ';}';
	$css .= '.btn-primary:not(.zskeleton-slider__btn):hover,.btn-primary:not(.zskeleton-slider__btn):focus{background:' . $btn_hov_e . ';border-color:' . $btn_hov_e . ';color:' . $btn_txt_e . ';}';
	$css .= '.stat-number,.stat-item .stat-number,.hero-stats .stat-number{color:' . $counter_e . ';}';
	$css .= '.menu-toggle,.menu-toggle:hover,.menu-toggle:focus{background:var(--zskeleton-mobile-menu-button-background, var(--primary-blue));border:var(--zskeleton-mobile-menu-button-border-width, 2px) solid var(--zskeleton-mobile-menu-button-border-color, var(--primary-blue));color:var(--zskeleton-mobile-menu-button-bar-color, #ffffff);}';
	$css .= '.menu-toggle:not(.menu-toggle--style-2) .menu-icon,.menu-toggle:not(.menu-toggle--style-2) .menu-icon::before,.menu-toggle:not(.menu-toggle--style-2) .menu-icon::after{background:currentColor;}';
	$css .= '.menu-toggle.menu-toggle--style-2 .menu-icon{width:20px;height:18px;background:repeating-linear-gradient(to bottom,currentColor 0 2px,transparent 2px 8px);border-radius:0;}';
	$css .= '.menu-toggle.menu-toggle--style-2 .menu-icon::before,.menu-toggle.menu-toggle--style-2 .menu-icon::after{display:none;}';

	$css .= zskeleton_get_top_footer_bars_layout_css();

	return $css;
}

/**
 * Optional nav link hover/focus and current-item background: hex, rgba(), hsla(), transparent, or empty.
 *
 * @param mixed $value Raw.
 * @return string Sanitized color token or empty (empty = not set; inline CSS will use transparent or match hover).
 */
function zskeleton_sanitize_option_nav_item_background( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( '' === $value ) {
		return '';
	}
	$vlow = strtolower( $value );
	if ( in_array( $vlow, array( 'transparent', 'none', 'inherit', 'initial', 'unset' ), true ) ) {
		return 'transparent' === $vlow || 'none' === $vlow ? 'transparent' : $vlow;
	}
	$hex = sanitize_hex_color( $value );
	if ( is_string( $hex ) && '' !== $hex ) {
		return $hex;
	}
	if ( preg_match( '/^rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)\s*$/i', $value, $m ) ) {
		$r = max( 0, min( 255, (int) $m[1] ) );
		$g = max( 0, min( 255, (int) $m[2] ) );
		$b = max( 0, min( 255, (int) $m[3] ) );
		return 'rgb(' . $r . ',' . $g . ',' . $b . ')';
	}
	if ( preg_match( '/^rgba\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]*\.[0-9]+|0?\.[0-9]+|[01])\s*\)\s*$/i', $value, $m ) ) {
		$r = max( 0, min( 255, (int) $m[1] ) );
		$g = max( 0, min( 255, (int) $m[2] ) );
		$b = max( 0, min( 255, (int) $m[3] ) );
		$a = (float) trim( $m[4] );
		$a = max( 0.0, min( 1.0, $a ) );
		return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $a . ')';
	}
	if ( preg_match( '/^hsla?\(\s*[^,]+,\s*[^,]+%,\s*[^,]+%(,\s*(0?\.[0-9]+|0|1))?\s*\)\s*$/i', $value ) ) {
		return $value;
	}
	if ( strlen( $value ) > 80 ) {
		return '';
	}
	return '';
}

/**
 * Optional hex for layout gradients/borders: empty allowed; invalid returns empty.
 *
 * @param mixed $value Raw.
 * @return string '' or 3/6 digit hex.
 */
function zskeleton_sanitize_option_layout_optional_hex_color( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( '' === $value ) {
		return '';
	}
	$clean = sanitize_hex_color( $value );
	if ( ! empty( $clean ) ) {
		return $clean;
	}
	if ( preg_match( '/rgba?\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})/i', $value, $m ) ) {
		$hex   = zskeleton_rgb_to_hex( (int) $m[1], (int) $m[2], (int) $m[3] );
		$clean = sanitize_hex_color( $hex );
		if ( ! empty( $clean ) ) {
			return $clean;
		}
	}
	return '';
}

/**
 * Build 135° linear-gradient or a solid from two optional hex values (for admin color pickers).
 * Both empty: no custom background. One set, one empty: solid that color. Both set: gradient.
 *
 * @param string $start_hex Sanitized start hex or ''.
 * @param string $end_hex   Sanitized end hex or ''.
 * @return string CSS background value, or empty string to keep theme default.
 */
function zskeleton_build_layout_pair_background_css( $start_hex, $end_hex ) {
	$a = is_string( $start_hex ) ? trim( $start_hex ) : '';
	$b = is_string( $end_hex ) ? trim( $end_hex ) : '';
	if ( '' === $a && '' === $b ) {
		return '';
	}
	if ( '' !== $a && '' !== $b ) {
		return 'linear-gradient(135deg,' . $a . ' 0%,' . $b . ' 100%)';
	}
	return '' !== $a ? $a : $b;
}

/**
 * Sanitize optional custom CSS background (gradients, rgb, var — no url()).
 *
 * @param mixed $value Raw.
 * @return string Sanitized token or empty.
 */
function zskeleton_sanitize_css_background_token( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( '' === $value ) {
		return '';
	}
	if ( strlen( $value ) > 500 ) {
		$value = substr( $value, 0, 500 );
	}
	if ( strpbrk( $value, '<>{};' ) !== false ) {
		return '';
	}
	$vlow = strtolower( $value );
	if ( preg_match( '/\burl\s*\(|\bexpression\s*\(|javascript:/i', $vlow ) ) {
		return '';
	}
	if ( false !== stripos( $vlow, '@import' ) || 0 === strpos( $vlow, 'data:' ) ) {
		return '';
	}
	return $value;
}

/**
 * Sanitize a linear size (padding, min-height as length, etc.).
 *
 * @param mixed  $value    Raw.
 * @param string $fallback Fallback when empty or invalid.
 * @return string
 */
function zskeleton_sanitize_css_length_value( $value, $fallback ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( '' === $value ) {
		return $fallback;
	}
	if ( ! preg_match( '/^[0-9.]+\s*(px|rem|em|%|ch|ex|vh|vw|pt|pc|in|cm|mm)?$/i', $value ) && ! preg_match( '/^[0-9.]+\s*$/', $value ) ) {
		return $fallback;
	}
	return $value;
}

/**
 * Sanitize min-height (keyword or length).
 *
 * @param mixed  $value    Raw.
 * @param string $fallback Fallback when empty.
 * @return string
 */
function zskeleton_sanitize_css_min_height_value( $value, $fallback = '' ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( '' === $value ) {
		return $fallback;
	}
	$l = strtolower( $value );
	if ( in_array( $l, array( 'auto', '0', 'none', 'min-content', 'max-content', 'fit-content' ), true ) ) {
		return 'none' === $l ? '0' : $l;
	}
	return zskeleton_sanitize_css_length_value( $value, $fallback );
}

/**
 * Top bar text color: empty = use default white in generated CSS.
 *
 * @param mixed $value Raw.
 * @return string Empty or hex.
 */
function zskeleton_sanitize_option_top_bar_text_color( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( '' === $value ) {
		return '';
	}
	return zskeleton_sanitize_hex_color_with_default( $value, '#ffffff' );
}

/**
 * Footer copyright card border color (color picker only): empty = theme default #374151 in CSS.
 *
 * @param mixed $value Raw.
 * @return string Empty or hex.
 */
function zskeleton_sanitize_option_footer_copyright_border_color( $value ) {
	return function_exists( 'zskeleton_sanitize_option_layout_optional_hex_color' )
		? zskeleton_sanitize_option_layout_optional_hex_color( $value )
		: '';
}

/**
 * Footer copyright card text: empty = white.
 *
 * @param mixed $value Raw.
 * @return string Empty or hex.
 */
function zskeleton_sanitize_option_footer_copyright_text_color( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( '' === $value ) {
		return '';
	}
	return zskeleton_sanitize_hex_color_with_default( $value, '#ffffff' );
}

/**
 * Mobile menu button style slug.
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_option_mobile_menu_button_style( $value ) {
	$v = is_string( $value ) ? strtolower( trim( $value ) ) : 'style1';
	return in_array( $v, array( 'style1', 'style2' ), true ) ? $v : 'style1';
}

/**
 * Mobile slide-out menu panel layout slug (tabs vs single list).
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_option_mobile_menu_panel_style( $value ) {
	$v = is_string( $value ) ? strtolower( trim( $value ) ) : 'style1';
	return in_array( $v, array( 'style1', 'style2' ), true ) ? $v : 'style1';
}

/**
 * Mobile slide-out drawer width mode.
 *
 * @param mixed $value Raw.
 * @return string default|full
 */
function zskeleton_sanitize_option_mobile_menu_drawer_width( $value ) {
	$v = is_string( $value ) ? strtolower( trim( $value ) ) : 'default';
	return in_array( $v, array( 'default', 'full' ), true ) ? $v : 'default';
}

/**
 * Mobile bottom navigation bar style slug.
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_option_mobile_bottom_nav_style( $value ) {
	$v = is_string( $value ) ? strtolower( trim( $value ) ) : 'style1';
	return in_array( $v, array( 'style1', 'style2' ), true ) ? $v : 'style1';
}

/**
 * Mobile menu button bars color (hex only).
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_option_mobile_menu_button_bar_color( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( '' === $value ) {
		return '#ffffff';
	}
	return zskeleton_sanitize_hex_color_with_default( $value, '#ffffff' );
}

/**
 * Mobile menu button background color (hex only).
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_option_mobile_menu_button_background_color( $value ) {
	$defaults = function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults() : array();
	$fallback = isset( $defaults['primary'] ) ? (string) $defaults['primary'] : '#647FBC';
	$value    = is_string( $value ) ? trim( $value ) : '';
	if ( '' === $value ) {
		return $fallback;
	}
	return zskeleton_sanitize_hex_color_with_default( $value, $fallback );
}

/**
 * Mobile menu button border color (hex only).
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_option_mobile_menu_button_border_color( $value ) {
	$defaults = function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults() : array();
	$fallback = isset( $defaults['primary'] ) ? (string) $defaults['primary'] : '#647FBC';
	$value    = is_string( $value ) ? trim( $value ) : '';
	if ( '' === $value ) {
		return $fallback;
	}
	return zskeleton_sanitize_hex_color_with_default( $value, $fallback );
}

/**
 * Mobile menu button border width in px (0-10).
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_option_mobile_menu_button_border_width( $value ) {
	$n = is_numeric( $value ) ? (float) $value : 2.0;
	if ( $n < 0 ) {
		$n = 0;
	}
	if ( $n > 10 ) {
		$n = 10;
	}
	$rounded = round( $n, 2 );
	$text    = rtrim( rtrim( number_format( $rounded, 2, '.', '' ), '0' ), '.' );
	return '' === $text ? '2' : $text;
}

/**
 * Mobile slide-out menu close button border-radius (CSS length; default 50% circle).
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_option_mobile_menu_close_border_radius( $value ) {
	return function_exists( 'zskeleton_sanitize_css_length_value' )
		? zskeleton_sanitize_css_length_value( (string) $value, '50%' )
		: '50%';
}

/**
 * Mobile slide-out menu close button icon/text color (matches --professional-gray when unset).
 *
 * @param mixed $value Raw.
 * @return string Hex color.
 */
function zskeleton_sanitize_option_mobile_menu_close_text_color( $value ) {
	return zskeleton_sanitize_hex_color_with_default( (string) $value, '#374151' );
}

/**
 * Min-height for footer bar (optional); empty = no min-height in CSS.
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_option_footer_copyright_min_height( $value ) {
	$value = is_string( $value ) ? trim( $value ) : '';
	if ( '' === $value ) {
		return '';
	}
	return zskeleton_sanitize_css_min_height_value( $value, '' );
}

/**
 * Optional padding / length fields; empty = use default constant in get_option( ..., default ).
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_option_top_bar_padding_y( $value ) {
	$v = zskeleton_sanitize_css_length_value( (string) $value, '8px' );
	return (string) ( '' !== $v ? $v : '8px' );
}

/**
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_option_top_bar_content_min_height( $value ) {
	$v = zskeleton_sanitize_css_length_value( (string) $value, '25px' );
	return (string) ( '' !== $v ? $v : '25px' );
}

/**
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_option_footer_copyright_padding_y( $value ) {
	$v = zskeleton_sanitize_css_length_value( (string) $value, '20px' );
	return (string) ( '' !== $v ? $v : '20px' );
}

/**
 * Inline styles for the header top bar and footer copyright strip (ZSkeleton → Layout).
 * Defaults match the uncustomized theme (gradient top bar, #fff text, 8px / 25px, footer #374151 top border, 20px vertical padding on copyright row).
 *
 * @return string Safe CSS fragment (no <style> tag).
 */
function zskeleton_get_top_footer_bars_layout_css() {
	$ld       = function_exists( 'zskeleton_get_layout_bars_default_option_values' ) ? zskeleton_get_layout_bars_default_option_values() : array();
	$resolved = zskeleton_get_resolved_theme_colors();

	$top_g1   = (string) get_option( 'zskeleton_top_bar_gradient_color_start', $ld['zskeleton_top_bar_gradient_color_start'] ?? $resolved['primary'] );
	$top_g2   = (string) get_option( 'zskeleton_top_bar_gradient_color_end', $ld['zskeleton_top_bar_gradient_color_end'] ?? $resolved['navy'] );
	$top_g1_s = function_exists( 'zskeleton_sanitize_option_layout_optional_hex_color' ) ? zskeleton_sanitize_option_layout_optional_hex_color( $top_g1 ) : '';
	$top_g2_s = function_exists( 'zskeleton_sanitize_option_layout_optional_hex_color' ) ? zskeleton_sanitize_option_layout_optional_hex_color( $top_g2 ) : '';
	$top_bg   = '';
	if ( '' !== $top_g1_s || '' !== $top_g2_s ) {
		if ( $top_g1_s === $resolved['primary'] && $top_g2_s === $resolved['navy'] ) {
			$top_bg = '';
		} else {
			$top_bg = function_exists( 'zskeleton_build_layout_pair_background_css' ) ? zskeleton_build_layout_pair_background_css( $top_g1_s, $top_g2_s ) : '';
		}
	}
	$top_txt_o = (string) get_option( 'zskeleton_top_bar_text_color', $ld['zskeleton_top_bar_text_color'] ?? '#ffffff' );
	$top_txt   = ( '' !== $top_txt_o && function_exists( 'zskeleton_sanitize_option_top_bar_text_color' ) )
		? zskeleton_sanitize_option_top_bar_text_color( $top_txt_o )
		: '#ffffff';
	if ( '' === $top_txt ) {
		$top_txt = '#ffffff';
	}
	$top_pad = (string) get_option( 'zskeleton_top_bar_padding_y', $ld['zskeleton_top_bar_padding_y'] ?? '8px' );
	$top_pad = function_exists( 'zskeleton_sanitize_css_length_value' ) ? zskeleton_sanitize_css_length_value( $top_pad, '8px' ) : '8px';
	$top_mh  = (string) get_option( 'zskeleton_top_bar_content_min_height', $ld['zskeleton_top_bar_content_min_height'] ?? '25px' );
	$top_mh  = function_exists( 'zskeleton_sanitize_css_length_value' ) ? zskeleton_sanitize_css_length_value( $top_mh, '25px' ) : '25px';

	$ft_g1   = (string) get_option( 'zskeleton_footer_copyright_card_gradient_start', $ld['zskeleton_footer_copyright_card_gradient_start'] ?? $resolved['primary'] );
	$ft_g2   = (string) get_option( 'zskeleton_footer_copyright_card_gradient_end', $ld['zskeleton_footer_copyright_card_gradient_end'] ?? $resolved['navy'] );
	$ft_g1_s = function_exists( 'zskeleton_sanitize_option_layout_optional_hex_color' ) ? zskeleton_sanitize_option_layout_optional_hex_color( $ft_g1 ) : '';
	$ft_g2_s = function_exists( 'zskeleton_sanitize_option_layout_optional_hex_color' ) ? zskeleton_sanitize_option_layout_optional_hex_color( $ft_g2 ) : '';
	$ft_bg   = '';
	if ( '' !== $ft_g1_s || '' !== $ft_g2_s ) {
		if ( $ft_g1_s === $resolved['primary'] && $ft_g2_s === $resolved['navy'] ) {
			$ft_bg = '';
		} else {
			$ft_bg = function_exists( 'zskeleton_build_layout_pair_background_css' ) ? zskeleton_build_layout_pair_background_css( $ft_g1_s, $ft_g2_s ) : '';
		}
	}
	$ft_tx_o = (string) get_option( 'zskeleton_footer_copyright_card_text_color', $ld['zskeleton_footer_copyright_card_text_color'] ?? '#ffffff' );
	$ft_tx = ( '' !== $ft_tx_o && function_exists( 'zskeleton_sanitize_option_footer_copyright_text_color' ) )
		? zskeleton_sanitize_option_footer_copyright_text_color( $ft_tx_o )
		: '#ffffff';
	if ( '' === $ft_tx ) {
		$ft_tx = '#ffffff';
	}
	$ft_border_off   = (string) get_option( 'zskeleton_footer_copyright_card_border_top_hidden', $ld['zskeleton_footer_copyright_card_border_top_hidden'] ?? '0' );
	$ft_br_o         = (string) get_option( 'zskeleton_footer_copyright_card_border_top_color', $ld['zskeleton_footer_copyright_card_border_top_color'] ?? '#374151' );
	$ft_br_san       = function_exists( 'zskeleton_sanitize_option_footer_copyright_border_color' )
		? zskeleton_sanitize_option_footer_copyright_border_color( $ft_br_o )
		: '';
	$ft_border_hidden = ( '1' === $ft_border_off );
	$ft_br            = ( '' !== $ft_br_san ) ? $ft_br_san : '#374151';
	$ft_pad    = (string) get_option( 'zskeleton_footer_copyright_card_padding_y', $ld['zskeleton_footer_copyright_card_padding_y'] ?? '20px' );
	$ft_pad    = function_exists( 'zskeleton_sanitize_css_length_value' ) ? zskeleton_sanitize_css_length_value( $ft_pad, '20px' ) : '20px';
	$ft_mh_raw = (string) get_option( 'zskeleton_footer_copyright_card_min_height', $ld['zskeleton_footer_copyright_card_min_height'] ?? '' );
	$ft_mh     = function_exists( 'zskeleton_sanitize_option_footer_copyright_min_height' ) ? zskeleton_sanitize_option_footer_copyright_min_height( $ft_mh_raw ) : '';
	$ft_mh     = is_string( $ft_mh ) ? $ft_mh : '';

	$css = '';

	// Custom top bar background replaces the default gradient in color_css (same selector wins by order; we only add this block when set).
	if ( '' !== $top_bg ) {
		$css .= '.header-top,.header-topbar-split{background:' . esc_attr( $top_bg ) . ';box-shadow:0 1px 0 rgba(15,23,42,0.06);}';
	}

	$tt_e = esc_attr( $top_txt );
	$css .= '.header-top,.header-topbar-split{color:' . $tt_e . ';}';
	$css .= '.header-top .header-top-content a,.header-top .header-links a,.header-top .header-contact,.header-top .header-separator,.header-top .header-topbar-wpml__summary,.header-top .header-topbar-wpml__link{color:' . $tt_e . ';opacity:1;}';
	$css .= '.header-top .header-inline-search__input{color:' . $tt_e . ';caret-color:currentColor;}';
	$css .= '.header-top .header-inline-search__icon,.header-top .header-inline-search__icon svg{stroke:currentColor;color:inherit;}';
	$py_e = esc_attr( $top_pad );
	$css .= '.header-top,.header-topbar-split{padding-top:' . $py_e . ';padding-bottom:' . $py_e . ';}';
	$css .= '.header-top .header-top-content{min-height:' . esc_attr( $top_mh ) . ';}';

	$ft_tx_e  = esc_attr( $ft_tx );
	$ft_pd_e  = esc_attr( $ft_pad );
	$ft_bt    = $ft_border_hidden
		? 'border-top:0;'
		: 'border-top:1px solid ' . esc_attr( $ft_br ) . ';';
	$css     .= '.site-footer .footer-bottom.footer-copyright-card{' . $ft_bt . 'padding-top:' . $ft_pd_e . ';padding-bottom:' . $ft_pd_e . ';text-align:center;color:' . $ft_tx_e . ';}';
	$css     .= '.site-footer .footer-bottom.footer-copyright-card p,.site-footer .footer-bottom.footer-copyright-card a,.site-footer .footer-bottom.footer-copyright-card li,.site-footer .footer-bottom.footer-copyright-card .legal-links a{color:' . $ft_tx_e . ';}';
	if ( '' !== $ft_bg ) {
		$css .= '.site-footer .footer-bottom.footer-copyright-card{background:' . esc_attr( $ft_bg ) . ';}';
	}
	if ( '' !== $ft_mh ) {
		$css .= '.site-footer .footer-bottom.footer-copyright-card{min-height:' . esc_attr( $ft_mh ) . ';}';
	}

	return $css;
}

/**
 * One-time copy of Customizer-stored colors (theme_mod) into options.
 */
function zskeleton_migrate_legacy_theme_mod_colors_to_options() {
	if ( get_option( 'zskeleton_theme_colors_migrated_v2' ) ) {
		return;
	}
	$keys = array(
		'zskeleton_primary_color',
		'zskeleton_secondary_color',
	);
	foreach ( $keys as $key ) {
		$current = get_option( $key, false );
		if ( false !== $current && '' !== $current ) {
			continue;
		}
		$mod = get_theme_mod( $key, '' );
		if ( is_string( $mod ) && '' !== $mod ) {
			$san = sanitize_hex_color( $mod );
			if ( $san ) {
				update_option( $key, $san );
			}
		}
	}
	update_option( 'zskeleton_theme_colors_migrated_v2', '1' );
}
add_action( 'after_setup_theme', 'zskeleton_migrate_legacy_theme_mod_colors_to_options', 5 );
