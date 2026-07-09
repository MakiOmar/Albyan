<?php
/**
 * Google Map embed helpers (theme settings + reusable renderer).
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the iframe `src` for a Google Maps embed (no JavaScript API key required).
 *
 * Uses coordinates from theme settings when arguments are omitted. If latitude and longitude
 * are empty, falls back to the optional address string for the `q` parameter.
 *
 * @param array<string, string|int|float> $args {
 *     Optional overrides.
 *
 *     @type string $lat     Latitude (-90..90). Default from `zskeleton_map_latitude`.
 *     @type string $lng     Longitude (-180..180). Default from `zskeleton_map_longitude`.
 *     @type string $address Place query when lat/lng are empty. Default from `zskeleton_map_address`.
 *     @type int    $zoom    1–20. Default from `zskeleton_map_zoom` or 14.
 * }
 * @return string Embed URL or empty string if nothing to show.
 */
function zskeleton_get_google_map_embed_src( $args = array() ) {
	$defaults = array(
		'lat'     => (string) get_option( 'zskeleton_map_latitude', '' ),
		'lng'     => (string) get_option( 'zskeleton_map_longitude', '' ),
		'address' => (string) get_option( 'zskeleton_map_address', '' ),
		'zoom'    => (int) get_option( 'zskeleton_map_zoom', 14 ),
	);
	$merged   = wp_parse_args( $args, $defaults );

	$lat     = trim( (string) $merged['lat'] );
	$lng     = trim( (string) $merged['lng'] );
	$address = trim( (string) $merged['address'] );
	$zoom    = (int) $merged['zoom'];
	if ( $zoom < 1 ) {
		$zoom = 1;
	}
	if ( $zoom > 20 ) {
		$zoom = 20;
	}

	$hl = sanitize_key( str_replace( '_', '-', get_locale() ) );
	if ( '' === $hl ) {
		$hl = 'en';
	}

	$q_value = '';
	if ( '' !== $lat && '' !== $lng ) {
		$q_value = $lat . ',' . $lng;
	} elseif ( '' !== $address ) {
		$q_value = $address;
	}

	if ( '' === $q_value ) {
		return '';
	}

	$src = add_query_arg(
		array(
			'q'      => $q_value,
			'z'      => $zoom,
			'output' => 'embed',
			'hl'     => $hl,
		),
		'https://maps.google.com/maps'
	);

	/**
	 * Filter the Google Maps embed iframe URL.
	 *
	 * @param string               $src  URL built by the theme.
	 * @param array<string, mixed> $args Arguments used to build the URL.
	 */
	return (string) apply_filters( 'zskeleton_google_map_embed_src', $src, $merged );
}

/**
 * Echo a responsive Google Map iframe using theme settings or passed arguments.
 *
 * @param array<string, string|int|float> $args {
 *     Optional. Merged with defaults for {@see zskeleton_get_google_map_embed_src()}.
 *
 *     @type string $width        CSS width for the wrapper (default 100%).
 *     @type string $height       CSS min-height for the iframe (default 360px).
 *     @type string $class        Extra class on the wrapper (default zskeleton-google-map).
 *     @type string $iframe_class Class on the iframe element.
 *     @type string $title        Accessible title for the iframe.
 * }
 * @return void
 */
function zskeleton_render_google_map( $args = array() ) {
	$map_args = $args;
	unset( $map_args['width'], $map_args['height'], $map_args['class'], $map_args['iframe_class'], $map_args['title'] );

	$src = zskeleton_get_google_map_embed_src( $map_args );
	if ( '' === $src ) {
		return;
	}

	$defaults = array(
		'width'        => '100%',
		'height'       => '360px',
		'class'        => 'zskeleton-google-map',
		'iframe_class' => 'zskeleton-google-map__iframe',
		'title'        => __( 'Map', 'zskeleton' ),
	);
	$frame    = wp_parse_args( $args, $defaults );

	$wrapper_class = trim( 'zskeleton-google-map-wrap ' . (string) $frame['class'] );
	$iframe_class  = trim( (string) $frame['iframe_class'] );

	$style = sprintf(
		'width:%s;min-height:%s;border:0;display:block;',
		esc_attr( (string) $frame['width'] ),
		esc_attr( (string) $frame['height'] )
	);

	$iframe_atts = array(
		'class'           => $iframe_class,
		'title'           => (string) $frame['title'],
		'src'             => $src,
		'style'           => $style,
		'loading'         => 'lazy',
		'referrerpolicy'  => 'no-referrer-when-downgrade',
		'allowfullscreen' => 'true',
	);

	/**
	 * Filter iframe attributes before output.
	 *
	 * @param array<string, string> $iframe_atts Attributes for the iframe (string values).
	 * @param array<string, mixed> $frame       Display arguments including width/height/title.
	 */
	$iframe_atts = apply_filters( 'zskeleton_google_map_iframe_atts', $iframe_atts, $frame );

	echo '<div class="' . esc_attr( $wrapper_class ) . '">';
	// <!-- Google Map embed -->
	echo '<iframe';
	foreach ( $iframe_atts as $attr => $val ) {
		$attr = sanitize_key( (string) $attr );
		if ( '' === $attr ) {
			continue;
		}
		echo ' ' . esc_attr( $attr ) . '="' . esc_attr( (string) $val ) . '"';
	}
	echo '></iframe>';
	echo '</div>';
}
