<?php
/**
 * Allowlist, sanitize, validate, optional submit callback.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Processes form submissions.
 */
class ZSkeleton_Form_Handler {

	/**
	 * Max number of keys accepted from client (abuse guard).
	 */
	const MAX_INPUT_KEYS = 200;

	/**
	 * Process raw input against a form definition.
	 *
	 * @param string               $form_id Form id.
	 * @param array                $raw_input Typically wp_unslash( $_POST ).
	 * @param array<string,mixed> $args {
	 *     @type string $validate_scope 'all'|'step'.
	 *     @type int    $step_index    Zero-based when validate_scope is step.
	 * }
	 * @return array{ok:bool,sanitized:array,errors:array<string,string>,message?:string}|WP_Error
	 */
	public static function process_request( $form_id, array $raw_input, array $args = array() ) {
		$scope     = isset( $args['validate_scope'] ) ? (string) $args['validate_scope'] : 'all';
		$step_idx  = isset( $args['step_index'] ) ? (int) $args['step_index'] : 0;
		$definition = ZSkeleton_Form_Definition::get( $form_id );
		if ( ! $definition ) {
			return new WP_Error( 'zskeleton_form_unknown', __( 'Invalid form.', 'zskeleton' ) );
		}

		if ( count( $raw_input ) > self::MAX_INPUT_KEYS ) {
			return self::failure_response( __( 'Too many fields submitted.', 'zskeleton' ) );
		}

		$honeypot = $definition->get_honeypot_name();
		if ( $honeypot && ! empty( $raw_input[ $honeypot ] ) ) {
			/**
			 * Fires when honeypot is filled (likely bot).
			 *
			 * @param string $form_id Form id.
			 */
			do_action( 'zskeleton_form_kit_honeypot_triggered', $form_id );
			return self::failure_response( __( 'Submission could not be processed.', 'zskeleton' ) );
		}

		$allowed_names = $definition->get_all_field_names();
		if ( 'step' === $scope ) {
			$allowed_names = $definition->get_field_names_for_step( $step_idx );
		}

		$filtered   = self::allowlist_input( $raw_input, $allowed_names, $definition );
		$registry   = ZSkeleton_Field_Registry::instance();
		$sanitized  = array();
		$errors     = array();
		$fields_map = $definition->get_fields_by_name();

		foreach ( $allowed_names as $name ) {
			if ( ! isset( $fields_map[ $name ] ) ) {
				continue;
			}
			$field   = $fields_map[ $name ];
			$type    = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';
			$type_cb = $registry->get_type( $type );
			$raw_val = array_key_exists( $name, $filtered ) ? $filtered[ $name ] : null;

			if ( null === $type_cb || ! is_callable( $type_cb['sanitize'] ) ) {
				$sanitized[ $name ] = is_string( $raw_val ) ? sanitize_text_field( $raw_val ) : $raw_val;
				continue;
			}
			$sanitized[ $name ] = call_user_func( $type_cb['sanitize'], $raw_val, $field, $registry );
		}

		foreach ( $allowed_names as $name ) {
			if ( ! isset( $fields_map[ $name ] ) ) {
				continue;
			}
			$field   = $fields_map[ $name ];
			$type    = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';
			$type_cb = $registry->get_type( $type );
			$val     = isset( $sanitized[ $name ] ) ? $sanitized[ $name ] : null;
			if ( null !== $type_cb && is_callable( $type_cb['validate'] ) ) {
				$field_errors = call_user_func( $type_cb['validate'], $val, $field, $sanitized );
				if ( is_array( $field_errors ) ) {
					foreach ( $field_errors as $msg ) {
						$errors[ $name ] = $msg;
						break;
					}
				} elseif ( is_string( $field_errors ) && '' !== $field_errors ) {
					$errors[ $name ] = $field_errors;
				}
			}
		}

		if ( ! empty( $errors ) ) {
			return array(
				'ok'        => false,
				'sanitized' => $sanitized,
				'errors'    => $errors,
			);
		}

		if ( 'step' === $scope ) {
			return array(
				'ok'        => true,
				'sanitized' => $sanitized,
				'errors'    => array(),
			);
		}

		$cb = $definition->get_submit_callback();
		if ( $cb ) {
			$result = call_user_func( $cb, $sanitized, $definition );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			if ( false === $result ) {
				return self::failure_response( __( 'Submission could not be completed.', 'zskeleton' ) );
			}
		}

		return array(
			'ok'        => true,
			'sanitized' => $sanitized,
			'errors'    => array(),
		);
	}

	/**
	 * @param array  $raw_input Raw input.
	 * @param string[] $allowed_names Allowed field names.
	 * @param ZSkeleton_Form_Definition $definition Definition.
	 * @return array
	 */
	private static function allowlist_input( array $raw_input, array $allowed_names, ZSkeleton_Form_Definition $definition ) {
		$out = array();
		foreach ( $allowed_names as $name ) {
			if ( array_key_exists( $name, $raw_input ) ) {
				$out[ $name ] = $raw_input[ $name ];
			}
		}
		return $out;
	}

	/**
	 * @param string $message Message.
	 * @return array{ok:false,sanitized:array,errors:array,message:string}
	 */
	private static function failure_response( $message ) {
		return array(
			'ok'        => false,
			'sanitized' => array(),
			'errors'    => array(),
			'message'   => $message,
		);
	}

	/**
	 * Check rate limit via filter (default allow).
	 *
	 * @param string $form_id Form id.
	 * @return bool True if allowed.
	 */
	public static function rate_limit_allow( $form_id ) {
		/**
		 * Return false to block submission (e.g. transient rate limit).
		 *
		 * @param bool   $allow   Default true.
		 * @param string $form_id Form id.
		 */
		return (bool) apply_filters( 'zskeleton_form_kit_rate_limit_allow', true, $form_id );
	}
}
