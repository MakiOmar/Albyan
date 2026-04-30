<?php
/**
 * Default field type implementations.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers built-in field types.
 */
class ZSkeleton_Form_Field_Types {

	/**
	 * @param ZSkeleton_Field_Registry $registry Registry.
	 */
	public static function register_phase1( ZSkeleton_Field_Registry $registry ) {
		$text_types = array( 'text', 'search', 'password', 'hidden' );
		foreach ( $text_types as $t ) {
			$registry->register(
				$t,
				array(
					'sanitize' => array( __CLASS__, 'sanitize_text_like' ),
					'validate' => array( __CLASS__, 'validate_text_like' ),
					'render'   => array( __CLASS__, 'render_input' ),
				)
			);
		}
		$registry->register(
			'email',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_email_field' ),
				'validate' => array( __CLASS__, 'validate_email_field' ),
				'render'   => array( __CLASS__, 'render_input' ),
			)
		);
		$registry->register(
			'url',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_url_field' ),
				'validate' => array( __CLASS__, 'validate_url_field' ),
				'render'   => array( __CLASS__, 'render_input' ),
			)
		);
		$registry->register(
			'tel',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_text_like' ),
				'validate' => array( __CLASS__, 'validate_text_like' ),
				'render'   => array( __CLASS__, 'render_input' ),
			)
		);
		$registry->register(
			'textarea',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_textarea' ),
				'validate' => array( __CLASS__, 'validate_textarea' ),
				'render'   => array( __CLASS__, 'render_textarea' ),
			)
		);
		$registry->register(
			'number',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_number' ),
				'validate' => array( __CLASS__, 'validate_number' ),
				'render'   => array( __CLASS__, 'render_input' ),
			)
		);
		$registry->register(
			'range',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_number' ),
				'validate' => array( __CLASS__, 'validate_number' ),
				'render'   => array( __CLASS__, 'render_range' ),
			)
		);
		$registry->register(
			'select',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_select' ),
				'validate' => array( __CLASS__, 'validate_select' ),
				'render'   => array( __CLASS__, 'render_select' ),
			)
		);
		$registry->register(
			'checkbox',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_checkbox' ),
				'validate' => array( __CLASS__, 'validate_checkbox' ),
				'render'   => array( __CLASS__, 'render_checkbox' ),
			)
		);
		$registry->register(
			'checkboxes',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_checkboxes' ),
				'validate' => array( __CLASS__, 'validate_checkboxes' ),
				'render'   => array( __CLASS__, 'render_checkboxes' ),
			)
		);
		$registry->register(
			'radio',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_select' ),
				'validate' => array( __CLASS__, 'validate_radio' ),
				'render'   => array( __CLASS__, 'render_radio' ),
			)
		);
		$registry->register(
			'toggle',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_checkbox' ),
				'validate' => array( __CLASS__, 'validate_checkbox' ),
				'render'   => array( __CLASS__, 'render_toggle' ),
			)
		);
		$registry->register(
			'color',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_color' ),
				'validate' => array( __CLASS__, 'validate_color' ),
				'render'   => array( __CLASS__, 'render_input' ),
			)
		);
		foreach ( array( 'date', 'time', 'datetime-local' ) as $dt ) {
			$registry->register(
				$dt,
				array(
					'sanitize' => array( __CLASS__, 'sanitize_datetime' ),
					'validate' => array( __CLASS__, 'validate_datetime' ),
					'render'   => array( __CLASS__, 'render_input' ),
				)
			);
		}
	}

	/**
	 * @param ZSkeleton_Field_Registry $registry Registry.
	 */
	public static function register_phase2( ZSkeleton_Field_Registry $registry ) {
		$registry->register(
			'media',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_attachment_id' ),
				'validate' => array( __CLASS__, 'validate_attachment_id' ),
				'render'   => array( __CLASS__, 'render_media' ),
			)
		);
		$registry->register(
			'image',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_attachment_id' ),
				'validate' => array( __CLASS__, 'validate_image_attachment' ),
				'render'   => array( __CLASS__, 'render_media' ),
			)
		);
		$registry->register(
			'wysiwyg',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_wysiwyg' ),
				'validate' => array( __CLASS__, 'validate_textarea' ),
				'render'   => array( __CLASS__, 'render_wysiwyg' ),
			)
		);
		$registry->register(
			'code',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_code' ),
				'validate' => array( __CLASS__, 'validate_text_like' ),
				'render'   => array( __CLASS__, 'render_textarea' ),
			)
		);
		$registry->register(
			'json',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_code' ),
				'validate' => array( __CLASS__, 'validate_json' ),
				'render'   => array( __CLASS__, 'render_textarea' ),
			)
		);
		$registry->register(
			'group',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_group' ),
				'validate' => array( __CLASS__, 'validate_group' ),
				'render'   => array( __CLASS__, 'render_group' ),
			)
		);
		$registry->register(
			'repeater',
			array(
				'sanitize' => array( __CLASS__, 'sanitize_repeater' ),
				'validate' => array( __CLASS__, 'validate_repeater' ),
				'render'   => array( __CLASS__, 'render_repeater' ),
			)
		);
	}

	/**
	 * @param mixed $raw Raw value.
	 * @param array $field Field config.
	 * @return string
	 */
	public static function sanitize_text_like( $raw, array $field ) {
		if ( is_array( $raw ) ) {
			return '';
		}
		return sanitize_text_field( (string) $raw );
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All sanitized.
	 * @return array|string|null
	 */
	public static function validate_text_like( $value, array $field, array $all ) {
		$errs = self::required_error( $value, $field );
		if ( null !== $errs ) {
			return $errs;
		}
		if ( ! is_string( $value ) ) {
			return null;
		}
		if ( isset( $field['rules']['min_length'] ) && strlen( $value ) < (int) $field['rules']['min_length'] ) {
			return array( __( 'Input is too short.', 'zskeleton' ) );
		}
		if ( isset( $field['rules']['max_length'] ) && strlen( $value ) > (int) $field['rules']['max_length'] ) {
			return array( __( 'Input is too long.', 'zskeleton' ) );
		}
		if ( ! empty( $field['rules']['pattern'] ) && is_string( $field['rules']['pattern'] ) && '' !== $value ) {
			$p = (string) $field['rules']['pattern'];
			// Pattern must include delimiters, e.g. /^[a-z]+$/i .
			if ( @preg_match( $p, $value ) !== 1 ) {
				return array( __( 'Invalid format.', 'zskeleton' ) );
			}
		}
		return null;
	}

	/**
	 * @param mixed $raw Raw.
	 * @return string
	 */
	public static function sanitize_email_field( $raw, array $field ) {
		if ( is_array( $raw ) ) {
			return '';
		}
		return sanitize_email( (string) $raw );
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_email_field( $value, array $field, array $all ) {
		$errs = self::required_error( $value, $field );
		if ( null !== $errs ) {
			return $errs;
		}
		if ( is_string( $value ) && '' !== $value && ! is_email( $value ) ) {
			return array( __( 'Please enter a valid email address.', 'zskeleton' ) );
		}
		return null;
	}

	/**
	 * @param mixed $raw Raw.
	 * @return string
	 */
	public static function sanitize_url_field( $raw, array $field ) {
		if ( is_array( $raw ) ) {
			return '';
		}
		return esc_url_raw( (string) $raw );
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_url_field( $value, array $field, array $all ) {
		$errs = self::required_error( $value, $field );
		if ( null !== $errs ) {
			return $errs;
		}
		if ( is_string( $value ) && '' !== $value ) {
			$url = esc_url_raw( $value );
			if ( '' === $url ) {
				return array( __( 'Please enter a valid URL.', 'zskeleton' ) );
			}
		}
		return null;
	}

	/**
	 * @param mixed $raw Raw.
	 * @return string
	 */
	public static function sanitize_textarea( $raw, array $field ) {
		if ( is_array( $raw ) ) {
			return '';
		}
		return sanitize_textarea_field( (string) $raw );
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_textarea( $value, array $field, array $all ) {
		$errs = self::required_error( $value, $field );
		if ( null !== $errs ) {
			return $errs;
		}
		if ( is_string( $value ) && isset( $field['rules']['max_length'] ) && strlen( $value ) > (int) $field['rules']['max_length'] ) {
			return array( __( 'Input is too long.', 'zskeleton' ) );
		}
		return null;
	}

	/**
	 * @param mixed $raw Raw.
	 * @return float|int|string
	 */
	public static function sanitize_number( $raw, array $field ) {
		if ( is_array( $raw ) ) {
			return '';
		}
		$s = (string) $raw;
		if ( '' === $s ) {
			return '';
		}
		if ( false !== strpos( $s, '.' ) ) {
			return floatval( $s );
		}
		return intval( $s, 10 );
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_number( $value, array $field, array $all ) {
		if ( ! empty( $field['required'] ) && ( '' === $value || null === $value ) ) {
			return array( __( 'This field is required.', 'zskeleton' ) );
		}
		if ( '' === $value || null === $value ) {
			return null;
		}
		if ( ! is_numeric( $value ) ) {
			return array( __( 'Please enter a valid number.', 'zskeleton' ) );
		}
		$v = floatval( $value );
		if ( isset( $field['rules']['min'] ) && $v < (float) $field['rules']['min'] ) {
			return array( __( 'Value is too small.', 'zskeleton' ) );
		}
		if ( isset( $field['rules']['max'] ) && $v > (float) $field['rules']['max'] ) {
			return array( __( 'Value is too large.', 'zskeleton' ) );
		}
		return null;
	}

	/**
	 * @param mixed $raw Raw.
	 * @return string
	 */
	public static function sanitize_select( $raw, array $field ) {
		if ( is_array( $raw ) ) {
			return '';
		}
		return sanitize_text_field( (string) $raw );
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_select( $value, array $field, array $all ) {
		$errs = self::required_error( $value, $field );
		if ( null !== $errs ) {
			return $errs;
		}
		if ( '' === $value ) {
			return null;
		}
		$allowed = self::choice_values( $field );
		if ( ! empty( $allowed ) && ! in_array( (string) $value, $allowed, true ) ) {
			return array( __( 'Invalid selection.', 'zskeleton' ) );
		}
		return null;
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_radio( $value, array $field, array $all ) {
		return self::validate_select( $value, $field, $all );
	}

	/**
	 * @param mixed $raw Raw.
	 * @return string
	 */
	public static function sanitize_checkbox( $raw, array $field ) {
		if ( '1' === $raw || 1 === $raw || true === $raw || 'on' === $raw || 'yes' === $raw ) {
			return '1';
		}
		return '';
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_checkbox( $value, array $field, array $all ) {
		if ( ! empty( $field['required'] ) && '1' !== (string) $value ) {
			return array( __( 'This field is required.', 'zskeleton' ) );
		}
		return null;
	}

	/**
	 * @param mixed $raw Raw.
	 * @return array
	 */
	public static function sanitize_checkboxes( $raw, array $field ) {
		if ( ! is_array( $raw ) ) {
			if ( '' === $raw || null === $raw ) {
				return array();
			}
			$raw = array( $raw );
		}
		$out = array();
		foreach ( $raw as $item ) {
			$out[] = sanitize_text_field( (string) $item );
		}
		return $out;
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_checkboxes( $value, array $field, array $all ) {
		if ( ! is_array( $value ) ) {
			$value = array();
		}
		if ( ! empty( $field['required'] ) && empty( $value ) ) {
			return array( __( 'Please select at least one option.', 'zskeleton' ) );
		}
		$allowed = self::choice_values( $field );
		if ( ! empty( $allowed ) ) {
			foreach ( $value as $v ) {
				if ( ! in_array( (string) $v, $allowed, true ) ) {
					return array( __( 'Invalid selection.', 'zskeleton' ) );
				}
			}
		}
		return null;
	}

	/**
	 * @param mixed $raw Raw.
	 * @return string
	 */
	public static function sanitize_color( $raw, array $field ) {
		if ( is_array( $raw ) ) {
			return '';
		}
		$c = sanitize_hex_color( (string) $raw );
		return $c ? $c : '';
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_color( $value, array $field, array $all ) {
		$errs = self::required_error( $value, $field );
		if ( null !== $errs ) {
			return $errs;
		}
		if ( '' !== $value && ! sanitize_hex_color( (string) $value ) ) {
			return array( __( 'Invalid color.', 'zskeleton' ) );
		}
		return null;
	}

	/**
	 * @param mixed $raw Raw.
	 * @return string
	 */
	public static function sanitize_datetime( $raw, array $field ) {
		if ( is_array( $raw ) ) {
			return '';
		}
		return sanitize_text_field( (string) $raw );
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_datetime( $value, array $field, array $all ) {
		return self::validate_text_like( $value, $field, $all );
	}

	/**
	 * @param mixed $raw Raw.
	 * @return int
	 */
	public static function sanitize_attachment_id( $raw, array $field ) {
		return absint( $raw );
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_attachment_id( $value, array $field, array $all ) {
		if ( ! empty( $field['required'] ) && (int) $value < 1 ) {
			return array( __( 'Please select a file.', 'zskeleton' ) );
		}
		if ( (int) $value < 1 ) {
			return null;
		}
		if ( ! current_user_can( 'edit_post', (int) $value ) ) {
			return array( __( 'You cannot use this file.', 'zskeleton' ) );
		}
		$post = get_post( (int) $value );
		if ( ! $post || 'attachment' !== $post->post_type ) {
			return array( __( 'Invalid attachment.', 'zskeleton' ) );
		}
		return null;
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_image_attachment( $value, array $field, array $all ) {
		$base = self::validate_attachment_id( $value, $field, $all );
		if ( null !== $base ) {
			return is_array( $base ) ? $base : array( $base );
		}
		if ( (int) $value < 1 ) {
			return null;
		}
		if ( ! wp_attachment_is_image( (int) $value ) ) {
			return array( __( 'Please select an image.', 'zskeleton' ) );
		}
		return null;
	}

	/**
	 * @param mixed $raw Raw.
	 * @return string
	 */
	public static function sanitize_wysiwyg( $raw, array $field ) {
		if ( is_array( $raw ) ) {
			return '';
		}
		return wp_kses_post( (string) $raw );
	}

	/**
	 * @param mixed $raw Raw.
	 * @return string
	 */
	public static function sanitize_code( $raw, array $field ) {
		if ( is_array( $raw ) ) {
			return '';
		}
		return sanitize_textarea_field( (string) $raw );
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_json( $value, array $field, array $all ) {
		$errs = self::required_error( $value, $field );
		if ( null !== $errs ) {
			return $errs;
		}
		if ( '' === $value ) {
			return null;
		}
		json_decode( (string) $value );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			return array( __( 'Invalid JSON.', 'zskeleton' ) );
		}
		return null;
	}

	/**
	 * Max nesting for group/repeater.
	 */
	const MAX_GROUP_DEPTH = 5;

	/**
	 * Max rows repeater.
	 */
	const MAX_REPEATER_ROWS = 50;

	/**
	 * @param mixed $raw Raw.
	 * @return array
	 */
	public static function sanitize_group( $raw, array $field ) {
		if ( ! is_array( $raw ) ) {
			return array();
		}
		return self::sanitize_nested_array( $raw, 0 );
	}

	/**
	 * @param array $arr Array.
	 * @param int   $depth Depth.
	 * @return array
	 */
	private static function sanitize_nested_array( array $arr, $depth ) {
		if ( $depth > self::MAX_GROUP_DEPTH ) {
			return array();
		}
		$out = array();
		foreach ( $arr as $k => $v ) {
			$key = sanitize_key( (string) $k );
			if ( '' === $key ) {
				continue;
			}
			if ( is_array( $v ) ) {
				$out[ $key ] = self::sanitize_nested_array( $v, $depth + 1 );
			} else {
				$out[ $key ] = sanitize_text_field( (string) $v );
			}
		}
		return $out;
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_group( $value, array $field, array $all ) {
		if ( ! empty( $field['required'] ) && ( ! is_array( $value ) || empty( $value ) ) ) {
			return array( __( 'This field is required.', 'zskeleton' ) );
		}
		return null;
	}

	/**
	 * @param mixed $raw Raw.
	 * @return array
	 */
	public static function sanitize_repeater( $raw, array $field ) {
		if ( is_string( $raw ) && '' !== $raw ) {
			$decoded = json_decode( $raw, true );
			if ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) ) {
				$raw = $decoded;
			} else {
				return array();
			}
		}
		if ( ! is_array( $raw ) ) {
			return array();
		}
		$raw = array_slice( $raw, 0, self::MAX_REPEATER_ROWS );
		$out = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$out[] = self::sanitize_nested_array( $row, 0 );
		}
		return $out;
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @param array $all   All.
	 * @return array|string|null
	 */
	public static function validate_repeater( $value, array $field, array $all ) {
		if ( ! is_array( $value ) ) {
			$value = array();
		}
		if ( ! empty( $field['required'] ) && empty( $value ) ) {
			return array( __( 'Add at least one row.', 'zskeleton' ) );
		}
		return null;
	}

	/**
	 * @param array  $field Field config.
	 * @param string $fid   Field id.
	 * @param mixed  $value Current value.
	 * @param array  $args  context, form_id, step_index.
	 * @return string
	 */
	public static function render_input( array $field, $value, array $args ) {
		$type     = isset( $field['type'] ) ? (string) $field['type'] : 'text';
		$name     = $field['name'];
		$id       = $args['field_id'];
		$classes  = 'form-control zs-field__control';
		$required = ! empty( $field['required'] ) ? ' required' : '';
		$attrs    = isset( $field['attributes'] ) && is_array( $field['attributes'] ) ? $field['attributes'] : array();
		$extra    = '';
		foreach ( $attrs as $ak => $av ) {
			$extra .= ' ' . esc_attr( (string) $ak ) . '="' . esc_attr( (string) $av ) . '"';
		}
		if ( 'hidden' === $type ) {
			return '<input type="hidden" class="zs-field__control" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '"' . $extra . ' />';
		}
		$ph = '';
		if ( isset( $field['placeholder'] ) && '' !== (string) $field['placeholder'] ) {
			$ph = ' placeholder="' . esc_attr( (string) $field['placeholder'] ) . '"';
		} elseif ( ! empty( $args['floating'] ) ) {
			$ph = ' placeholder=" "';
		}
		return '<input type="' . esc_attr( $type ) . '" class="' . esc_attr( $classes ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $value ) . '"' . $required . $ph . $extra . ' />';
	}

	/**
	 * @param array  $field Field.
	 * @param mixed  $value Value.
	 * @param array  $args Args.
	 * @return string
	 */
	public static function render_textarea( array $field, $value, array $args ) {
		$name     = $field['name'];
		$id       = $args['field_id'];
		$required = ! empty( $field['required'] ) ? ' required' : '';
		$rows     = isset( $field['rows'] ) ? (int) $field['rows'] : 4;
		$ph       = '';
		if ( isset( $field['placeholder'] ) && '' !== (string) $field['placeholder'] ) {
			$ph = ' placeholder="' . esc_attr( (string) $field['placeholder'] ) . '"';
		} elseif ( ! empty( $args['floating'] ) ) {
			$ph = ' placeholder=" "';
		}
		return '<textarea class="form-control zs-field__control" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="' . esc_attr( (string) $rows ) . '"' . $required . $ph . '>' . esc_textarea( (string) $value ) . '</textarea>';
	}

	/**
	 * @param array  $field Field.
	 * @param mixed  $value Value.
	 * @param array  $args Args.
	 * @return string
	 */
	public static function render_range( array $field, $value, array $args ) {
		$field['type'] = 'range';
		$min           = isset( $field['rules']['min'] ) ? (float) $field['rules']['min'] : 0;
		$max           = isset( $field['rules']['max'] ) ? (float) $field['rules']['max'] : 100;
		$field['attributes'] = isset( $field['attributes'] ) && is_array( $field['attributes'] ) ? $field['attributes'] : array();
		$field['attributes']['min'] = (string) $min;
		$field['attributes']['max'] = (string) $max;
		if ( isset( $field['rules']['step'] ) ) {
			$field['attributes']['step'] = (string) $field['rules']['step'];
		}
		return self::render_input( $field, $value, $args ) . '<span class="zs-field__range-value" data-zs-range-for="' . esc_attr( $args['field_id'] ) . '">' . esc_html( (string) $value ) . '</span>';
	}

	/**
	 * @param array  $field Field.
	 * @param mixed  $value Value.
	 * @param array  $args Args.
	 * @return string
	 */
	public static function render_select( array $field, $value, array $args ) {
		$name     = $field['name'];
		$id       = $args['field_id'];
		$required = ! empty( $field['required'] ) ? ' required' : '';
		$html     = '<select class="form-control zs-field__control" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '"' . $required . '>';
		$html    .= '<option value="">' . esc_html( __( 'Select…', 'zskeleton' ) ) . '</option>';
		$choices  = isset( $field['choices'] ) && is_array( $field['choices'] ) ? $field['choices'] : array();
		foreach ( $choices as $cv => $clabel ) {
			if ( is_int( $cv ) && is_string( $clabel ) ) {
				$cv = $clabel;
			}
			$html .= '<option value="' . esc_attr( (string) $cv ) . '"' . selected( (string) $value, (string) $cv, false ) . '>' . esc_html( (string) $clabel ) . '</option>';
		}
		$html .= '</select>';
		return $html;
	}

	/**
	 * @param array  $field Field.
	 * @param mixed  $value Value.
	 * @param array  $args Args.
	 * @return string
	 */
	public static function render_checkbox( array $field, $value, array $args ) {
		$name = $field['name'];
		$id   = $args['field_id'];
		$chk  = '1' === (string) $value ? ' checked' : '';
		return '<label class="zs-field__check"><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1"' . $chk . ' /><span>' . esc_html( isset( $field['label'] ) ? (string) $field['label'] : '' ) . '</span></label>';
	}

	/**
	 * @param array  $field Field.
	 * @param mixed  $value Value.
	 * @param array  $args Args.
	 * @return string
	 */
	public static function render_checkboxes( array $field, $value, array $args ) {
		$name   = $field['name'] . '[]';
		$vals   = is_array( $value ) ? $value : array();
		$html   = '<fieldset class="zs-field__fieldset"><legend class="zs-field__legend">' . esc_html( isset( $field['label'] ) ? (string) $field['label'] : '' ) . '</legend>';
		$choices = isset( $field['choices'] ) && is_array( $field['choices'] ) ? $field['choices'] : array();
		$i      = 0;
		foreach ( $choices as $cv => $clabel ) {
			if ( is_int( $cv ) && is_string( $clabel ) ) {
				$cv = $clabel;
			}
			$fid = $args['field_id'] . '_' . $i;
			$chk = in_array( (string) $cv, array_map( 'strval', $vals ), true ) ? ' checked' : '';
			$html .= '<label class="zs-field__check"><input type="checkbox" id="' . esc_attr( $fid ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $cv ) . '"' . $chk . ' /><span>' . esc_html( (string) $clabel ) . '</span></label>';
			++$i;
		}
		$html .= '</fieldset>';
		return $html;
	}

	/**
	 * @param array  $field Field.
	 * @param mixed  $value Value.
	 * @param array  $args Args.
	 * @return string
	 */
	public static function render_radio( array $field, $value, array $args ) {
		$fname   = $field['name'];
		$choices = isset( $field['choices'] ) && is_array( $field['choices'] ) ? $field['choices'] : array();
		$html    = '<fieldset class="zs-field__fieldset"><legend class="zs-field__legend">' . esc_html( isset( $field['label'] ) ? (string) $field['label'] : '' ) . '</legend>';
		$i       = 0;
		foreach ( $choices as $cv => $clabel ) {
			if ( is_int( $cv ) && is_string( $clabel ) ) {
				$cv = $clabel;
			}
			$fid = $args['field_id'] . '_' . $i;
			$chk = (string) $value === (string) $cv ? ' checked' : '';
			$html .= '<label class="zs-field__check"><input type="radio" id="' . esc_attr( $fid ) . '" name="' . esc_attr( $fname ) . '" value="' . esc_attr( (string) $cv ) . '"' . $chk . ' /><span>' . esc_html( (string) $clabel ) . '</span></label>';
			++$i;
		}
		$html .= '</fieldset>';
		return $html;
	}

	/**
	 * @param array  $field Field.
	 * @param mixed  $value Value.
	 * @param array  $args Args.
	 * @return string
	 */
	public static function render_toggle( array $field, $value, array $args ) {
		$name = $field['name'];
		$id   = $args['field_id'];
		$chk  = '1' === (string) $value ? ' checked' : '';
		$lbl  = isset( $field['label'] ) ? (string) $field['label'] : '';
		return '<div class="toggle-switch zs-field__toggle"><input type="checkbox" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="1"' . $chk . ' /><label class="toggle-slider" for="' . esc_attr( $id ) . '"></label><span class="zs-field__toggle-label">' . esc_html( $lbl ) . '</span></div>';
	}

	/**
	 * @param array  $field Field.
	 * @param mixed  $value Value.
	 * @param array  $args Args.
	 * @return string
	 */
	public static function render_media( array $field, $value, array $args ) {
		$id   = (int) $value;
		$name = $field['name'];
		$fid  = $args['field_id'];
		$url  = $id ? wp_get_attachment_url( $id ) : '';
		$html = '<div class="zs-field__media" data-zs-media-field>';
		$html .= '<input type="hidden" class="zs-field__media-input" id="' . esc_attr( $fid ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $id ) . '" />';
		if ( 'admin' === $args['context'] ) {
			$html .= '<button type="button" class="button zs-field__media-btn" data-zs-media-open>' . esc_html( __( 'Select media', 'zskeleton' ) ) . '</button> ';
			$html .= '<button type="button" class="button-link zs-field__media-clear" data-zs-media-clear>' . esc_html( __( 'Clear', 'zskeleton' ) ) . '</button>';
		}
		$html .= '<span class="zs-field__media-preview">' . ( $url ? '<img src="' . esc_url( $url ) . '" alt="" />' : '' ) . '</span>';
		$html .= '</div>';
		return $html;
	}

	/**
	 * @param array  $field Field.
	 * @param mixed  $value Value.
	 * @param array  $args Args.
	 * @return string
	 */
	public static function render_wysiwyg( array $field, $value, array $args ) {
		if ( 'admin' !== $args['context'] ) {
			return self::render_textarea( $field, $value, $args );
		}
		$name = $field['name'];
		$id   = $args['field_id'];
		ob_start();
		wp_editor(
			(string) $value,
			$id,
			array(
				'textarea_name' => $name,
				'media_buttons' => false,
				'textarea_rows' => isset( $field['rows'] ) ? (int) $field['rows'] : 6,
				'teeny'         => true,
			)
		);
		return '<div class="zs-field__wysiwyg">' . ob_get_clean() . '</div>';
	}

	/**
	 * @param array  $field Field.
	 * @param mixed  $value Value.
	 * @param array  $args Args.
	 * @return string
	 */
	public static function render_group( array $field, $value, array $args ) {
		return '<p class="zs-field__hint">' . esc_html( __( 'Group field: use JSON or custom render via filter.', 'zskeleton' ) ) . '</p>';
	}

	/**
	 * @param array  $field Field.
	 * @param mixed  $value Value.
	 * @param array  $args Args.
	 * @return string
	 */
	public static function render_repeater( array $field, $value, array $args ) {
		$json = is_array( $value ) ? wp_json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) : (string) $value;
		$name = $field['name'];
		$id   = $args['field_id'];
		return '<textarea class="form-control zs-field__control zs-field__repeater-json" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="6">' . esc_textarea( $json ) . '</textarea><p class="zs-field__hint">' . esc_html( __( 'Repeater JSON (advanced).', 'zskeleton' ) ) . '</p>';
	}

	/**
	 * @param array $field Field.
	 * @return string[]
	 */
	private static function choice_values( array $field ) {
		$choices = isset( $field['choices'] ) && is_array( $field['choices'] ) ? $field['choices'] : array();
		$vals    = array();
		foreach ( $choices as $cv => $clabel ) {
			if ( is_int( $cv ) && is_string( $clabel ) ) {
				$vals[] = (string) $clabel;
			} else {
				$vals[] = (string) $cv;
			}
		}
		return $vals;
	}

	/**
	 * @param mixed $value Value.
	 * @param array $field Field.
	 * @return array|null Null if ok, else array of errors.
	 */
	private static function required_error( $value, array $field ) {
		if ( empty( $field['required'] ) ) {
			return null;
		}
		if ( null === $value || '' === $value || array() === $value ) {
			return array( __( 'This field is required.', 'zskeleton' ) );
		}
		return null;
	}
}
