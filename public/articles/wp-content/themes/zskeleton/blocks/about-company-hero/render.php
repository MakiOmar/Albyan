<?php
/**
 * Dynamic render: About company hero.
 *
 * @package ZSkeleton_Theme
 * @var array         $attributes Block attributes.
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'zskeleton_about_company_hero_hex' ) ) {
	/**
	 * Normalize hex colors to #rrggbb.
	 *
	 * @param mixed  $raw Raw color value.
	 * @param string $fallback Fallback.
	 * @return string
	 */
	function zskeleton_about_company_hero_hex( $raw, $fallback ) {
		$fb = is_string( $fallback ) && preg_match( '/^#[0-9A-Fa-f]{6}$/', $fallback ) ? $fallback : '#000000';
		if ( ! is_string( $raw ) ) {
			return $fb;
		}
		$raw = trim( $raw );
		if ( '' === $raw ) {
			return $fb;
		}
		if ( function_exists( 'maybe_hash_hex_color' ) ) {
			$raw = maybe_hash_hex_color( $raw );
		}
		$hex = sanitize_hex_color( $raw );
		return ( is_string( $hex ) && '' !== $hex ) ? $hex : $fb;
	}
}

if ( ! function_exists( 'zskeleton_about_company_hero_rgba' ) ) {
	/**
	 * Safe rgba() parser for overlay color.
	 *
	 * @param mixed  $raw Raw color.
	 * @param string $fallback Fallback rgba.
	 * @return string
	 */
	function zskeleton_about_company_hero_rgba( $raw, $fallback ) {
		if ( ! is_string( $raw ) ) {
			return $fallback;
		}
		$v = trim( $raw );
		if ( preg_match( '/^rgba\(\s*([01]?\d?\d|2[0-4]\d|25[0-5])\s*,\s*([01]?\d?\d|2[0-4]\d|25[0-5])\s*,\s*([01]?\d?\d|2[0-4]\d|25[0-5])\s*,\s*(0|0?\.\d+|1(\.0+)?)\s*\)$/', $v ) ) {
			return $v;
		}
		return $fallback;
	}
}

if ( ! function_exists( 'zskeleton_about_company_hero_int' ) ) {
	/**
	 * Clamp integer value.
	 *
	 * @param mixed $raw Raw.
	 * @param int   $min Min.
	 * @param int   $max Max.
	 * @param int   $default Default.
	 * @return int
	 */
	function zskeleton_about_company_hero_int( $raw, $min, $max, $default ) {
		$n = isset( $raw ) ? (int) $raw : $default;
		if ( $n < $min ) {
			return $min;
		}
		if ( $n > $max ) {
			return $max;
		}
		return $n;
	}
}

$a = is_array( $attributes ) ? $attributes : array();
$theme_colors = function_exists( 'zskeleton_get_resolved_theme_colors' ) ? zskeleton_get_resolved_theme_colors() : array();
$theme_primary = isset( $theme_colors['primary'] ) ? $theme_colors['primary'] : '#647FBC';
$theme_accent = isset( $theme_colors['accent'] ) ? $theme_colors['accent'] : '#AED6CF';
$theme_background = isset( $theme_colors['background'] ) ? $theme_colors['background'] : '#FAFDD6';
$theme_button_bg = isset( $theme_colors['button_background'] ) ? $theme_colors['button_background'] : $theme_primary;
$theme_button_text = isset( $theme_colors['button_text'] ) ? $theme_colors['button_text'] : '#000000';
$theme_navy = isset( $theme_colors['navy'] ) ? $theme_colors['navy'] : '#101520';

$title_html = isset( $a['title'] ) ? (string) $a['title'] : '';
$desc_html  = isset( $a['description'] ) ? (string) $a['description'] : '';

$bg_id   = isset( $a['backgroundImageId'] ) ? (int) $a['backgroundImageId'] : 0;
$bg_url  = isset( $a['backgroundImageUrl'] ) ? esc_url_raw( (string) $a['backgroundImageUrl'] ) : '';
$bg_size = isset( $a['backgroundSize'] ) ? sanitize_key( (string) $a['backgroundSize'] ) : 'cover';
if ( ! in_array( $bg_size, array( 'cover', 'contain', 'auto' ), true ) ) {
	$bg_size = 'cover';
}

$bg_position = isset( $a['backgroundPosition'] ) ? sanitize_text_field( (string) $a['backgroundPosition'] ) : 'center center';
if ( ! in_array( $bg_position, array( 'center center', 'center top', 'center bottom', 'left center', 'right center' ), true ) ) {
	$bg_position = 'center center';
}

$profile_url = '';
if ( isset( $a['profileImageId'] ) && (int) $a['profileImageId'] > 0 && wp_attachment_is_image( (int) $a['profileImageId'] ) ) {
	$profile_url = wp_get_attachment_image_url( (int) $a['profileImageId'], 'medium' );
}
if ( '' === $profile_url ) {
	$profile_url = isset( $a['profileImageUrl'] ) ? esc_url_raw( (string) $a['profileImageUrl'] ) : '';
}
$profile_alt = isset( $a['profileImageAlt'] ) ? sanitize_text_field( (string) $a['profileImageAlt'] ) : '';

$title_color       = zskeleton_about_company_hero_hex( isset( $a['titleColor'] ) ? $a['titleColor'] : '', $theme_navy );
$description_color = zskeleton_about_company_hero_hex( isset( $a['descriptionColor'] ) ? $a['descriptionColor'] : '', $theme_navy );
$section_bg        = zskeleton_about_company_hero_hex( isset( $a['sectionBackgroundColor'] ) ? $a['sectionBackgroundColor'] : '', $theme_background );
$overlay_color     = zskeleton_about_company_hero_rgba( isset( $a['sectionOverlayColor'] ) ? $a['sectionOverlayColor'] : '', 'rgba(255,255,255,0.18)' );
$profile_border    = zskeleton_about_company_hero_hex( isset( $a['profileBorderColor'] ) ? $a['profileBorderColor'] : '', $theme_accent );
$separator_color   = zskeleton_about_company_hero_hex( isset( $a['titleSeparatorColor'] ) ? $a['titleSeparatorColor'] : '', $theme_accent );
$separator_show    = ! isset( $a['titleShowSeparator'] ) || (bool) $a['titleShowSeparator'];
$sep_width         = zskeleton_about_company_hero_int( isset( $a['titleSeparatorWidthPx'] ) ? $a['titleSeparatorWidthPx'] : null, 20, 400, 120 );
$sep_height        = zskeleton_about_company_hero_int( isset( $a['titleSeparatorHeightPx'] ) ? $a['titleSeparatorHeightPx'] : null, 1, 24, 4 );
$sep_radius        = zskeleton_about_company_hero_int( isset( $a['titleSeparatorRadiusPx'] ) ? $a['titleSeparatorRadiusPx'] : null, 0, 999, 999 );

$b1_label = isset( $a['buttonOneLabel'] ) ? sanitize_text_field( (string) $a['buttonOneLabel'] ) : '';
$b1_url   = isset( $a['buttonOneUrl'] ) ? esc_url_raw( (string) $a['buttonOneUrl'] ) : '';
$b1_tgt   = isset( $a['buttonOneTarget'] ) && '_blank' === (string) $a['buttonOneTarget'] ? '_blank' : '_self';

$b2_label = isset( $a['buttonTwoLabel'] ) ? sanitize_text_field( (string) $a['buttonTwoLabel'] ) : '';
$b2_url   = isset( $a['buttonTwoUrl'] ) ? esc_url_raw( (string) $a['buttonTwoUrl'] ) : '';
$b2_tgt   = isset( $a['buttonTwoTarget'] ) && '_blank' === (string) $a['buttonTwoTarget'] ? '_blank' : '_self';

$b1_text   = zskeleton_about_company_hero_hex( isset( $a['buttonOneTextColor'] ) ? $a['buttonOneTextColor'] : '', $theme_button_text );
$b1_bg     = zskeleton_about_company_hero_hex( isset( $a['buttonOneBackgroundColor'] ) ? $a['buttonOneBackgroundColor'] : '', '#ffffff' );
$b1_border = zskeleton_about_company_hero_hex( isset( $a['buttonOneBorderColor'] ) ? $a['buttonOneBorderColor'] : '', $theme_accent );
$b2_text   = zskeleton_about_company_hero_hex( isset( $a['buttonTwoTextColor'] ) ? $a['buttonTwoTextColor'] : '', $theme_button_text );
$b2_bg     = zskeleton_about_company_hero_hex( isset( $a['buttonTwoBackgroundColor'] ) ? $a['buttonTwoBackgroundColor'] : '', $theme_button_bg );
$b2_border = zskeleton_about_company_hero_hex( isset( $a['buttonTwoBorderColor'] ) ? $a['buttonTwoBorderColor'] : '', $theme_button_bg );

$background_image = '';
if ( $bg_id > 0 && wp_attachment_is_image( $bg_id ) ) {
	$attachment_src = wp_get_attachment_image_url( $bg_id, 'full' );
	if ( is_string( $attachment_src ) && '' !== $attachment_src ) {
		$background_image = esc_url_raw( $attachment_src );
	}
}
if ( '' === $background_image ) {
	$background_image = $bg_url;
}

$css_vars = sprintf(
	'--ach-title:%1$s;--ach-desc:%2$s;--ach-bg:%3$s;--ach-overlay:%4$s;--ach-profile-border:%5$s;--ach-sep:%6$s;--ach-sep-w:%7$dpx;--ach-sep-h:%8$dpx;--ach-sep-r:%9$dpx;--ach-b1-fg:%10$s;--ach-b1-bg:%11$s;--ach-b1-bd:%12$s;--ach-b2-fg:%13$s;--ach-b2-bg:%14$s;--ach-b2-bd:%15$s;--ach-bg-size:%16$s;--ach-bg-pos:%17$s;',
	$title_color,
	$description_color,
	$section_bg,
	$overlay_color,
	$profile_border,
	$separator_color,
	$sep_width,
	$sep_height,
	$sep_radius,
	$b1_text,
	$b1_bg,
	$b1_border,
	$b2_text,
	$b2_bg,
	$b2_border,
	$bg_size,
	$bg_position
);
if ( '' !== $background_image ) {
	$css_vars .= '--ach-bg-image:url("' . esc_url( $background_image ) . '");';
}

$wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'zskeleton-about-company-hero',
		'style' => $css_vars,
	),
	'',
	isset( $block ) ? $block : null
);

echo '<section ' . $wrapper . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '<div class="zskeleton-about-company-hero__overlay"></div>';
echo '<div class="zskeleton-about-company-hero__inner">';
if ( '' !== $profile_url ) {
	echo '<div class="zskeleton-about-company-hero__profile-wrap">';
	echo '<img class="zskeleton-about-company-hero__profile" src="' . esc_url( $profile_url ) . '" alt="' . esc_attr( $profile_alt ) . '" loading="lazy" decoding="async" />';
	echo '</div>';
}
if ( '' !== trim( wp_strip_all_tags( $title_html ) ) ) {
	echo '<h2 class="zskeleton-about-company-hero__title">' . wp_kses_post( $title_html ) . '</h2>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
if ( $separator_show ) {
	echo '<span class="zskeleton-about-company-hero__separator" aria-hidden="true"></span>';
}
if ( '' !== trim( wp_strip_all_tags( $desc_html ) ) ) {
	echo '<div class="zskeleton-about-company-hero__description">' . wp_kses_post( $desc_html ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
echo '<div class="zskeleton-about-company-hero__buttons">';
if ( '' !== $b1_label && '' !== $b1_url ) {
	echo '<a class="zskeleton-about-company-hero__btn zskeleton-about-company-hero__btn--one" href="' . esc_url( $b1_url ) . '"' . ( '_blank' === $b1_tgt ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $b1_label ) . '</a>';
}
if ( '' !== $b2_label && '' !== $b2_url ) {
	echo '<a class="zskeleton-about-company-hero__btn zskeleton-about-company-hero__btn--two" href="' . esc_url( $b2_url ) . '"' . ( '_blank' === $b2_tgt ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $b2_label ) . '</a>';
}
echo '</div>';
echo '</div>';
echo '</section>';
