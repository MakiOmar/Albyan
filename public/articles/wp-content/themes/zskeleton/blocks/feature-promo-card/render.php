<?php
/**
 * Dynamic render: Feature promo card.
 *
 * @package ZSkeleton_Theme
 * @var array         $attributes Block attributes.
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

// Dynamic block render files may load more than once per request; guard helpers.
if ( ! function_exists( 'zskeleton_feature_promo_card_hex' ) ) {
	/**
	 * Normalize hex color to #rrggbb or empty.
	 *
	 * @param mixed  $raw Raw attribute.
	 * @param string $fallback Fallback when empty/invalid.
	 * @return string
	 */
	function zskeleton_feature_promo_card_hex( $raw, $fallback ) {
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

if ( ! function_exists( 'zskeleton_feature_promo_card_font_stack' ) ) {
	/**
	 * Sanitize a font-family stack fragment for inline CSS (no url/expressions).
	 *
	 * @param mixed $raw Raw attribute.
	 * @return string Empty = inherit theme fonts.
	 */
	function zskeleton_feature_promo_card_font_stack( $raw ) {
		if ( ! is_string( $raw ) ) {
			return '';
		}
		$v = trim( $raw );
		if ( '' === $v || strlen( $v ) > 400 ) {
			return '';
		}
		if ( preg_match( '/[<>\'\\\\`{}]|expression|javascript:|@import|url\\s*\\(/i', $v ) ) {
			return '';
		}
		if ( ! preg_match( '/^[-_0-9a-zA-Z\\s,."\'()+\\/]+$/u', $v ) ) {
			return '';
		}
		return $v;
	}
}

if ( ! function_exists( 'zskeleton_feature_promo_card_int' ) ) {
	/**
	 * Clamp integer block attribute.
	 *
	 * @param mixed $raw Raw.
	 * @param int   $min Min.
	 * @param int   $max Max.
	 * @param int   $default Default.
	 * @return int
	 */
	function zskeleton_feature_promo_card_int( $raw, $min, $max, $default ) {
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

$title_html       = isset( $a['title'] ) ? (string) $a['title'] : '';
$body_html        = isset( $a['bodyHtml'] ) ? (string) $a['bodyHtml'] : '';
$list_items       = isset( $a['listItems'] ) && is_array( $a['listItems'] ) ? $a['listItems'] : array();
$button_label     = isset( $a['buttonLabel'] ) ? sanitize_text_field( (string) $a['buttonLabel'] ) : '';
$button_url       = isset( $a['buttonUrl'] ) ? esc_url_raw( (string) $a['buttonUrl'] ) : '';
$button_target    = isset( $a['buttonTarget'] ) && '_blank' === (string) $a['buttonTarget'] ? '_blank' : '_self';
$btn_nf           = ! empty( $a['buttonRelNoFollow'] );
$btn_sp           = ! empty( $a['buttonRelSponsored'] );
$btn_aria         = isset( $a['buttonAriaLabel'] ) ? sanitize_text_field( (string) $a['buttonAriaLabel'] ) : '';

$icon_mode = isset( $a['iconMode'] ) ? sanitize_key( (string) $a['iconMode'] ) : 'dashicon';
if ( ! in_array( $icon_mode, array( 'none', 'dashicon', 'image' ), true ) ) {
	$icon_mode = 'dashicon';
}

$icon_id   = isset( $a['iconImageId'] ) ? (int) $a['iconImageId'] : 0;
$icon_slug = isset( $a['iconDashicon'] ) ? sanitize_key( (string) $a['iconDashicon'] ) : '';
if ( '' === $icon_slug ) {
	$icon_slug = 'groups';
}

// Text direction follows the WordPress locale / theme (same as the rest of the page).
$page_rtl = function_exists( 'is_rtl' ) && is_rtl();

$card_bg    = zskeleton_feature_promo_card_hex( isset( $a['cardBackground'] ) ? $a['cardBackground'] : '', '#ffffff' );
$header_bg  = zskeleton_feature_promo_card_hex( isset( $a['headerBackground'] ) ? $a['headerBackground'] : '', '#f0f4f7' );
$title_c    = zskeleton_feature_promo_card_hex( isset( $a['titleColor'] ) ? $a['titleColor'] : '', '#1e293b' );
$body_c     = zskeleton_feature_promo_card_hex( isset( $a['bodyColor'] ) ? $a['bodyColor'] : '', '#475569' );
$list_c     = zskeleton_feature_promo_card_hex( isset( $a['listColor'] ) ? $a['listColor'] : '', '#475569' );
$bullet_c   = zskeleton_feature_promo_card_hex( isset( $a['listBulletColor'] ) ? $a['listBulletColor'] : '', '#64748b' );
$icon_c     = zskeleton_feature_promo_card_hex( isset( $a['iconColor'] ) ? $a['iconColor'] : '', '#334e68' );
$btn_bg     = zskeleton_feature_promo_card_hex( isset( $a['buttonBackground'] ) ? $a['buttonBackground'] : '', '#5086b3' );
$btn_fg     = zskeleton_feature_promo_card_hex( isset( $a['buttonTextColor'] ) ? $a['buttonTextColor'] : '', '#ffffff' );

$title_ff = zskeleton_feature_promo_card_font_stack( isset( $a['titleFontFamily'] ) ? $a['titleFontFamily'] : '' );
$body_ff  = zskeleton_feature_promo_card_font_stack( isset( $a['bodyFontFamily'] ) ? $a['bodyFontFamily'] : '' );

$radius     = zskeleton_feature_promo_card_int( isset( $a['cardBorderRadiusPx'] ) ? $a['cardBorderRadiusPx'] : null, 0, 80, 24 );
$pad        = zskeleton_feature_promo_card_int( isset( $a['cardPaddingPx'] ) ? $a['cardPaddingPx'] : null, 8, 80, 32 );
$header_pt  = zskeleton_feature_promo_card_int( isset( $a['headerPaddingTopPx'] ) ? $a['headerPaddingTopPx'] : null, 0, 120, 28 );
$wave       = zskeleton_feature_promo_card_int( isset( $a['headerWaveDepthPx'] ) ? $a['headerWaveDepthPx'] : null, 8, 80, 26 );

$shadow = isset( $a['shadowStrength'] ) ? sanitize_key( (string) $a['shadowStrength'] ) : 'medium';
if ( ! in_array( $shadow, array( 'none', 'soft', 'medium', 'strong' ), true ) ) {
	$shadow = 'medium';
}

$style_vars = sprintf(
	'--fpc-card-bg:%1$s;--fpc-header-bg:%2$s;--fpc-title:%3$s;--fpc-body:%4$s;--fpc-list:%5$s;--fpc-bullet:%6$s;--fpc-icon:%7$s;--fpc-btn-bg:%8$s;--fpc-btn-fg:%9$s;--fpc-radius:%10$dpx;--fpc-pad:%11$dpx;--fpc-header-pt:%12$dpx;--fpc-wave:%13$dpx;',
	$card_bg,
	$header_bg,
	$title_c,
	$body_c,
	$list_c,
	$bullet_c,
	$icon_c,
	$btn_bg,
	$btn_fg,
	$radius,
	$pad,
	$header_pt,
	$wave
);
if ( '' !== $title_ff ) {
	$style_vars .= '--fpc-title-ff:' . esc_attr( $title_ff ) . ';';
}
if ( '' !== $body_ff ) {
	$style_vars .= '--fpc-body-ff:' . esc_attr( $body_ff ) . ';';
}

$wrapper_classes = array(
	'zskeleton-feature-promo-card',
	'zskeleton-feature-promo-card--shadow-' . $shadow,
);

$inner = '';

// Icon area.
$inner .= '<div class="zskeleton-feature-promo-card__header"><div class="zskeleton-feature-promo-card__icon-wrap">';
if ( 'image' === $icon_mode && $icon_id > 0 && wp_attachment_is_image( $icon_id ) ) {
	$inner .= wp_get_attachment_image(
		$icon_id,
		'medium',
		false,
		array(
			'class'    => 'zskeleton-feature-promo-card__icon-img',
			'loading'  => 'lazy',
			'decoding' => 'async',
			'alt'      => isset( $a['iconImageAlt'] ) ? sanitize_text_field( (string) $a['iconImageAlt'] ) : '',
		)
	);
} elseif ( 'dashicon' === $icon_mode && '' !== $icon_slug ) {
	wp_enqueue_style( 'dashicons' );
	$inner .= sprintf(
		'<span class="zskeleton-feature-promo-card__icon-dashicon dashicons dashicons-%s" aria-hidden="true"></span>',
		esc_attr( $icon_slug )
	);
}
$inner .= '</div></div>';

// Title + body + list.
if ( '' !== trim( wp_strip_all_tags( $title_html ) ) ) {
	$inner .= '<div class="zskeleton-feature-promo-card__body">';
	$title_row = '';
	if ( function_exists( 'zskeleton_render_block_heading_title_row' ) ) {
		$title_row = zskeleton_render_block_heading_title_row(
			array(
				'title_inner_html' => wp_kses_post( $title_html ),
				'heading_tag'      => 'h2',
				'attributes'       => $a,
				'title_class'      => 'zskeleton-feature-promo-card__title',
				'align'            => 'center',
				'heading_id'       => '',
			)
		);
	}
	if ( '' !== $title_row ) {
		$inner .= $title_row;
	} else {
		$inner .= '<h2 class="zskeleton-feature-promo-card__title">' . wp_kses_post( $title_html ) . '</h2>';
	}
} else {
	$inner .= '<div class="zskeleton-feature-promo-card__body">';
}

if ( '' !== trim( wp_strip_all_tags( $body_html ) ) ) {
	$inner .= '<div class="zskeleton-feature-promo-card__body-copy">' . wp_kses_post( $body_html ) . '</div>';
}

$list_clean = array();
foreach ( $list_items as $row ) {
	$t = sanitize_text_field( is_string( $row ) ? $row : '' );
	if ( '' !== $t ) {
		$list_clean[] = $t;
	}
}
if ( ! empty( $list_clean ) ) {
	$inner .= '<ul class="zskeleton-feature-promo-card__list">';
	foreach ( $list_clean as $item ) {
		$inner .= '<li>' . esc_html( $item ) . '</li>';
	}
	$inner .= '</ul>';
}

if ( '' !== $button_label && '' !== $button_url ) {
	$rel = array();
	if ( $btn_nf ) {
		$rel[] = 'nofollow';
	}
	if ( $btn_sp ) {
		$rel[] = 'sponsored';
	}
	if ( '_blank' === $button_target ) {
		$rel[] = 'noopener';
		$rel[] = 'noreferrer';
	}
	$rel_s = implode( ' ', array_unique( array_filter( $rel ) ) );
	$inner .= sprintf(
		'<a class="zskeleton-feature-promo-card__cta" href="%1$s"%2$s%3$s%4$s>%5$s</a>',
		esc_url( $button_url ),
		'_blank' === $button_target ? ' target="_blank"' : '',
		'' !== $rel_s ? ' rel="' . esc_attr( $rel_s ) . '"' : '',
		'' !== $btn_aria ? ' aria-label="' . esc_attr( $btn_aria ) . '"' : '',
		esc_html( $button_label )
	);
}

$inner .= '</div>';

$dir_attr = $page_rtl ? 'rtl' : 'ltr';

$wrapper_args = array(
	'class' => implode( ' ', $wrapper_classes ),
	'style' => $style_vars,
	'dir'   => $dir_attr,
);

// WordPress wraps this file in ob_start()/ob_get_clean(); a bare `return $html` is discarded.
// Output must be echoed so the render callback buffer receives markup.
$wrapper = get_block_wrapper_attributes( $wrapper_args );

echo sprintf( '<div %s>%s</div>', $wrapper, $inner ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Wrapper from get_block_wrapper_attributes(); $inner built with escaping above.
