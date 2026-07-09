<?php
/**
 * Widget: heading with optional font size and configurable underline width and thickness.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display a single heading and a rule whose length is controlled in the widget.
 */
class ZSkeleton_Widget_Heading extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'zskeleton_heading',
			__( 'ZSkeleton: Heading', 'zskeleton' ),
			array(
				'description' => __( 'A heading (H2–H4) with optional font size, custom underline length and thickness, and optional text/underline colors.', 'zskeleton' ),
				'classname'   => 'widget_zskeleton_heading',
			)
		);
	}

	/**
	 * @param array<string, string> $args     Sidebar args.
	 * @param array<string, string> $instance Settings.
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );

		$title = trim( (string) $instance['title'] );
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		if ( '' === $title ) {
			return;
		}

		$tag  = $this->sanitize_heading_tag( $instance['heading_level'] );
		$uw   = $this->sanitize_underline_width( $instance['underline_width'] );
		$ut   = $this->sanitize_underline_thickness( $instance['underline_thickness'] );
		$tc   = $this->sanitize_optional_color( $instance['title_color'] );
		$uc   = $this->sanitize_optional_color( $instance['underline_color'] );
		if ( '' === $uc && '' !== $tc ) {
			$uc = $tc;
		}
		$fs   = $this->sanitize_font_size( $instance['font_size'] );

		$style = '--zskeleton-widget-underline-width: ' . esc_attr( $uw ) . '; --zskeleton-widget-underline-thickness: ' . esc_attr( $ut ) . ';';
		if ( '' !== $fs ) {
			$style .= ' --zskeleton-widget-title-font-size: ' . esc_attr( $fs ) . ';';
		}
		if ( '' !== $tc ) {
			$style .= ' --zskeleton-widget-title-color: ' . esc_attr( $tc ) . ';';
		}
		if ( '' !== $uc ) {
			$style .= ' --zskeleton-widget-underline-color: ' . esc_attr( $uc ) . ';';
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf(
			'<div class="zskeleton-widget-heading-block zskeleton-widget-heading" style="%s">',
			esc_attr( $style )
		);
		printf(
			'<%1$s class="zskeleton-widget-heading__text">%2$s</%1$s>',
			$tag, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $title )
		);
		echo '<span class="zskeleton-widget-heading__rule" aria-hidden="true"></span>';
		echo '</div>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param array<string, string> $instance Instance.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		$levels   = array(
			'h2' => 'H2',
			'h3' => 'H3',
			'h4' => 'H4',
		);
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Heading text', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'heading_level' ) ); ?>"><?php esc_html_e( 'Heading level', 'zskeleton' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'heading_level' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'heading_level' ) ); ?>">
				<?php foreach ( $levels as $val => $lab ) : ?>
					<option value="<?php echo esc_attr( $val ); ?>" <?php selected( (string) $instance['heading_level'], $val ); ?>><?php echo esc_html( $lab ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'font_size' ) ); ?>"><?php esc_html_e( 'Font size (optional)', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'font_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'font_size' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['font_size'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. 1.5rem, 20px (leave empty for theme default)', 'zskeleton' ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'underline_width' ) ); ?>"><?php esc_html_e( 'Underline length (CSS width)', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'underline_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'underline_width' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['underline_width'] ); ?>" placeholder="3rem" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'underline_thickness' ) ); ?>"><?php esc_html_e( 'Underline thickness (CSS height)', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'underline_thickness' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'underline_thickness' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['underline_thickness'] ); ?>" placeholder="2px" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title_color' ) ); ?>"><?php esc_html_e( 'Text color (optional)', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title_color' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title_color'] ); ?>" placeholder="#333333" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'underline_color' ) ); ?>"><?php esc_html_e( 'Underline color (optional)', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'underline_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'underline_color' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['underline_color'] ); ?>" placeholder="<?php esc_attr_e( 'Empty = same as text', 'zskeleton' ); ?>" />
		</p>
		<?php
	}

	/**
	 * @param array<string, string> $new_instance New.
	 * @param array<string, string> $old_instance Old.
	 * @return array<string, string>
	 */
	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );
		$out = $this->defaults();
		$out['title']            = sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) );
		$out['heading_level']       = $this->sanitize_heading_tag( $new_instance['heading_level'] ?? 'h3' );
		$out['font_size']         = $this->sanitize_font_size( $new_instance['font_size'] ?? '' );
		$out['underline_width']   = $this->sanitize_underline_width( $new_instance['underline_width'] ?? '' );
		$out['underline_thickness'] = $this->sanitize_underline_thickness( $new_instance['underline_thickness'] ?? '' );
		$out['title_color']         = $this->sanitize_optional_color( $new_instance['title_color'] ?? '' );
		$out['underline_color']   = $this->sanitize_optional_color( $new_instance['underline_color'] ?? '' );
		return $out;
	}

	/**
	 * @return array<string, string>
	 */
	private function defaults() {
		return array(
			'title'                 => '',
			'heading_level'         => 'h3',
			'font_size'            => '',
			'underline_width'       => '3rem',
			'underline_thickness'   => '2px',
			'title_color'           => '',
			'underline_color'       => '',
		);
	}

	/**
	 * @param mixed $tag Raw.
	 * @return string h2|h3|h4
	 */
	private function sanitize_heading_tag( $tag ) {
		$tag = strtolower( trim( (string) $tag ) );
		if ( in_array( $tag, array( 'h2', 'h3', 'h4' ), true ) ) {
			return $tag;
		}
		return 'h3';
	}

	/**
	 * @param mixed $v Raw CSS font-size.
	 * @return string Empty when unset, otherwise a safe value.
	 */
	private function sanitize_font_size( $v ) {
		$v = trim( (string) $v );
		if ( '' === $v ) {
			return '';
		}
		$v_lower = strtolower( $v );
		if ( in_array( $v_lower, array( 'inherit', 'initial', 'unset', 'larger', 'smaller' ), true ) ) {
			return $v_lower;
		}
		if ( strlen( $v ) > 40 ) {
			return '';
		}
		if ( preg_match( '/^[0-9.]+\s*(px|rem|em|%|ch|vw|vh|ex|pt|pc)$/i', $v ) ) {
			return $v;
		}
		return '';
	}

	/**
	 * @param mixed $v Raw.
	 * @return string
	 */
	private function sanitize_underline_width( $v ) {
		$v = trim( (string) $v );
		if ( '' === $v ) {
			return '3rem';
		}
		if ( preg_match( '/^[0-9.]+\s*(px|rem|em|%|ch|vw|vh|cm|mm|in|pt|pc)$/i', $v ) ) {
			return $v;
		}
		return '3rem';
	}

	/**
	 * @param mixed $v Raw CSS height.
	 * @return string
	 */
	private function sanitize_underline_thickness( $v ) {
		$v = trim( (string) $v );
		if ( '' === $v ) {
			return '2px';
		}
		if ( preg_match( '/^[0-9.]+\s*(px|rem|em|%|ch|ex|cm|mm|in|pt|pc)$/i', $v ) ) {
			return $v;
		}
		return '2px';
	}

	/**
	 * @param mixed $v Raw.
	 * @return string
	 */
	private function sanitize_optional_color( $v ) {
		$v = trim( (string) $v );
		if ( '' === $v ) {
			return '';
		}
		$hex = sanitize_hex_color( $v );
		return is_string( $hex ) && '' !== $hex ? $hex : '';
	}
}
