<?php
/**
 * Widget: Google Map (theme defaults or custom location).
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display an embedded Google Map from Appearance → ZSkeleton Settings or widget overrides.
 */
class ZSkeleton_Widget_Google_Map extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'zskeleton_google_map',
			__( 'ZSkeleton: Google Map', 'zskeleton' ),
			array(
				'description' => __( 'Embedded map using theme location settings or a custom address/coordinates.', 'zskeleton' ),
				'classname'   => 'widget_zskeleton_google_map',
			)
		);
	}

	/**
	 * Front-end output.
	 *
	 * @param array<string, string> $args     Display arguments.
	 * @param array<string, string> $instance Saved settings.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		if ( ! function_exists( 'zskeleton_render_google_map' ) ) {
			return;
		}

		$instance = wp_parse_args(
			(array) $instance,
			$this->defaults()
		);

		$use_theme = $this->should_use_theme_map_location( $instance );

		$map_args = array();
		if ( ! $use_theme ) {
			$map_args['lat']     = $this->sanitize_latitude( $instance['custom_lat'] ?? '' );
			$map_args['lng']     = $this->sanitize_longitude( $instance['custom_lng'] ?? '' );
			$map_args['address'] = sanitize_text_field( (string) ( $instance['custom_address'] ?? '' ) );
			$cz                  = trim( (string) ( $instance['custom_zoom'] ?? '' ) );
			if ( '' !== $cz && is_numeric( $cz ) ) {
				$map_args['zoom'] = (int) $cz;
			}
		}

		$map_args['width']        = trim( (string) $instance['width'] ) ?: '100%';
		$map_args['height']       = trim( (string) $instance['height'] ) ?: '360px';
		$map_args['title']        = trim( (string) $instance['iframe_title'] ) ?: __( 'Map', 'zskeleton' );
		$wrapper_extra            = trim( (string) $instance['wrapper_class'] );
		$map_args['class']        = trim( 'zskeleton-google-map-widget ' . $wrapper_extra );
		$map_args['iframe_class'] = 'zskeleton-google-map__iframe zskeleton-google-map-widget__iframe';

		$src = '';
		if ( function_exists( 'zskeleton_get_google_map_embed_src' ) ) {
			$embed_args = $map_args;
			unset( $embed_args['width'], $embed_args['height'], $embed_args['class'], $embed_args['iframe_class'], $embed_args['title'] );
			$src = zskeleton_get_google_map_embed_src( $embed_args );
		}

		if ( '' === $src ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				if ( ! empty( $instance['title'] ) ) {
					echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				echo '<p class="zskeleton-google-map-widget__notice">';
				esc_html_e( 'No map location is set. Configure the map under Appearance → ZSkeleton Settings → General, or choose “Custom location” in this widget.', 'zskeleton' );
				echo '</p>';
				echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$title = trim( (string) $instance['title'] );
		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		zskeleton_render_google_map( $map_args );

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Settings form in admin.
	 *
	 * @param array<string, string> $instance Current settings.
	 * @return void
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		$field_id = $this->get_field_id( 'title' );
		?>
		<p>
			<label for="<?php echo esc_attr( $field_id ); ?>"><?php esc_html_e( 'Title:', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title'] ); ?>" />
		</p>
		<p>
			<label>
				<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'use_theme_location' ) ); ?>" value="1" <?php checked( $instance['use_theme_location'], '1' ); ?> />
				<?php esc_html_e( 'Use theme default location (General → Map settings)', 'zskeleton' ); ?>
			</label><br />
			<label>
				<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'use_theme_location' ) ); ?>" value="0" <?php checked( $instance['use_theme_location'], '0' ); ?> />
				<?php esc_html_e( 'Custom location', 'zskeleton' ); ?>
			</label>
		</p>
		<fieldset class="zskeleton-google-map-widget-custom" style="border:1px solid #ccd0d4;padding:10px;margin:0 0 12px;">
			<legend class="screen-reader-text"><?php esc_html_e( 'Custom map location', 'zskeleton' ); ?></legend>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'custom_lat' ) ); ?>"><?php esc_html_e( 'Latitude', 'zskeleton' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'custom_lat' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'custom_lat' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['custom_lat'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. 24.7136', 'zskeleton' ); ?>" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'custom_lng' ) ); ?>"><?php esc_html_e( 'Longitude', 'zskeleton' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'custom_lng' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'custom_lng' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['custom_lng'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. 46.6753', 'zskeleton' ); ?>" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'custom_address' ) ); ?>"><?php esc_html_e( 'Address (if lat/lng empty)', 'zskeleton' ); ?></label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'custom_address' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'custom_address' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['custom_address'] ); ?>" />
			</p>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'custom_zoom' ) ); ?>"><?php esc_html_e( 'Zoom (1–20, optional)', 'zskeleton' ); ?></label>
				<input class="small-text" id="<?php echo esc_attr( $this->get_field_id( 'custom_zoom' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'custom_zoom' ) ); ?>" type="number" min="1" max="20" step="1" value="<?php echo esc_attr( (string) $instance['custom_zoom'] ); ?>" placeholder="<?php echo esc_attr( (string) get_option( 'zskeleton_map_zoom', '14' ) ); ?>" />
			</p>
		</fieldset>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>"><?php esc_html_e( 'Map height (CSS)', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'height' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['height'] ); ?>" placeholder="360px" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>"><?php esc_html_e( 'Map width (CSS)', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'width' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['width'] ); ?>" placeholder="100%" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'iframe_title' ) ); ?>"><?php esc_html_e( 'Accessible map title', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'iframe_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'iframe_title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['iframe_title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'wrapper_class' ) ); ?>"><?php esc_html_e( 'Extra CSS class (optional)', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'wrapper_class' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'wrapper_class' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['wrapper_class'] ); ?>" />
		</p>
		<?php
	}

	/**
	 * @param array<string, string> $new_instance New values.
	 * @param array<string, string> $old_instance Previous values.
	 * @return array<string, string>
	 */
	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );

		$out = array();
		$out['title']               = sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) );
		$out['use_theme_location']  = ( isset( $new_instance['use_theme_location'] ) && '0' === (string) $new_instance['use_theme_location'] ) ? '0' : '1';

		$out['custom_lat']     = $this->sanitize_latitude( $new_instance['custom_lat'] ?? '' );
		$out['custom_lng']     = $this->sanitize_longitude( $new_instance['custom_lng'] ?? '' );
		$out['custom_address'] = sanitize_text_field( (string) ( $new_instance['custom_address'] ?? '' ) );
		$out['custom_zoom']    = $this->sanitize_zoom_field( $new_instance['custom_zoom'] ?? '' );

		$out['height']         = $this->sanitize_css_size( $new_instance['height'] ?? '360px' );
		$out['width']          = $this->sanitize_css_size( $new_instance['width'] ?? '100%', true );
		$out['iframe_title']  = sanitize_text_field( (string) ( $new_instance['iframe_title'] ?? '' ) );
		$out['wrapper_class'] = $this->sanitize_wrapper_classes( $new_instance['wrapper_class'] ?? '' );

		return $out;
	}

	/**
	 * Default instance values.
	 *
	 * @return array<string, string>
	 */
	private function defaults() {
		return array(
			'title'               => '',
			'use_theme_location'  => '1',
			'custom_lat'          => '',
			'custom_lng'          => '',
			'custom_address'      => '',
			'custom_zoom'         => '',
			'height'              => '360px',
			'width'               => '100%',
			'iframe_title'        => '',
			'wrapper_class'       => '',
		);
	}

	/**
	 * Use theme map options only when the user has not selected custom data.
	 * Block/REST flows sometimes omit `use_theme_location`, which previously defaulted
	 * to “theme” and ignored saved lat/lng.
	 *
	 * @param array<string, string> $instance Widget instance.
	 */
	private function should_use_theme_map_location( array $instance ) {
		$mode = isset( $instance['use_theme_location'] ) ? (string) $instance['use_theme_location'] : '';
		if ( '0' === $mode ) {
			return false;
		}
		if ( $this->instance_has_non_empty_custom_map_location( $instance ) ) {
			return false;
		}
		return true;
	}

	/**
	 * @param array<string, string> $instance Instance.
	 */
	private function instance_has_non_empty_custom_map_location( array $instance ) {
		$addr = trim( (string) ( $instance['custom_address'] ?? '' ) );
		if ( '' !== $addr ) {
			return true;
		}
		$lat = $this->normalize_coordinate_string( $instance['custom_lat'] ?? '' );
		$lng = $this->normalize_coordinate_string( $instance['custom_lng'] ?? '' );
		if ( '' === $lat || '' === $lng ) {
			return false;
		}
		return is_numeric( $lat ) && is_numeric( $lng );
	}

	/**
	 * Trim; if there is a single comma and no period, treat comma as the decimal separator.
	 *
	 * @param mixed $value Raw.
	 * @return string
	 */
	private function normalize_coordinate_string( $value ) {
		$v = trim( (string) $value );
		if ( '' === $v ) {
			return '';
		}
		if ( 1 === substr_count( $v, ',' ) && false === strpos( $v, '.' ) ) {
			$v = str_replace( ',', '.', $v );
		}
		return $v;
	}

	/**
	 * @param mixed $value Raw latitude.
	 * @return string Empty or normalized float string.
	 */
	private function sanitize_latitude( $value ) {
		$v = $this->normalize_coordinate_string( $value );
		if ( '' === $v || ! is_numeric( $v ) ) {
			return '';
		}
		$f = (float) $v;
		if ( $f < -90.0 || $f > 90.0 ) {
			return '';
		}
		return (string) $f;
	}

	/**
	 * @param mixed $value Raw longitude.
	 * @return string Empty or normalized float string.
	 */
	private function sanitize_longitude( $value ) {
		$v = $this->normalize_coordinate_string( $value );
		if ( '' === $v || ! is_numeric( $v ) ) {
			return '';
		}
		$f = (float) $v;
		if ( $f < -180.0 || $f > 180.0 ) {
			return '';
		}
		return (string) $f;
	}

	/**
	 * @param mixed $value Raw zoom; empty allowed.
	 * @return string
	 */
	private function sanitize_zoom_field( $value ) {
		$v = trim( (string) $value );
		if ( '' === $v ) {
			return '';
		}
		if ( ! is_numeric( $v ) ) {
			return '';
		}
		$n = (int) $v;
		if ( $n < 1 ) {
			$n = 1;
		}
		if ( $n > 20 ) {
			$n = 20;
		}
		return (string) $n;
	}

	/**
	 * Allow common CSS units for width/height.
	 *
	 * @param mixed $value   Raw value.
	 * @param bool  $percent Whether % is allowed (width).
	 * @return string
	 */
	/**
	 * @param mixed $value Raw class string (space-separated allowed).
	 * @return string
	 */
	private function sanitize_wrapper_classes( $value ) {
		$raw = trim( (string) $value );
		if ( '' === $raw ) {
			return '';
		}
		$parts = preg_split( '/\s+/', $raw );
		$clean = array();
		foreach ( $parts as $part ) {
			$c = sanitize_html_class( $part );
			if ( '' !== $c ) {
				$clean[] = $c;
			}
		}
		return implode( ' ', $clean );
	}

	private function sanitize_css_size( $value, $percent = false ) {
		$v = trim( (string) $value );
		if ( '' === $v ) {
			return $percent ? '100%' : '360px';
		}
		if ( preg_match( '/^[0-9.]+(%|px|rem|em|vh|vw)$/', $v ) ) {
			if ( ! $percent && '%' === substr( $v, -1 ) ) {
				return '360px';
			}
			return $v;
		}
		return $percent ? '100%' : '360px';
	}
}

/**
 * Register theme widgets.
 *
 * @return void
 */
function zskeleton_register_theme_widgets() {
	register_widget( 'ZSkeleton_Widget_Google_Map' );
	register_widget( 'ZSkeleton_Widget_Social_Icons' );
	register_widget( 'ZSkeleton_Widget_Heading' );
	register_widget( 'ZSkeleton_Widget_Contact_Lines' );
}
add_action( 'widgets_init', 'zskeleton_register_theme_widgets', 11 );
