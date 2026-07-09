<?php
/**
 * Field type registry (sanitize, validate, render).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registry for form field types.
 */
class ZSkeleton_Field_Registry {

	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * @var array<string,array<string,mixed>>
	 */
	private $types = array();

	/**
	 * @return self
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Reset instance (tests).
	 */
	public static function reset_instance() {
		self::$instance = null;
	}

	private function __construct() {
		$this->types = array();
	}

	/**
	 * @param string               $type Type key.
	 * @param array<string,mixed> $callbacks sanitize, validate, render callables.
	 */
	public function register( $type, array $callbacks ) {
		$type = sanitize_key( (string) $type );
		if ( '' === $type ) {
			return;
		}
		$this->types[ $type ] = wp_parse_args(
			$callbacks,
			array(
				'sanitize' => null,
				'validate' => null,
				'render'   => null,
			)
		);
	}

	/**
	 * @param string $type Type key.
	 * @return array<string,mixed>|null
	 */
	public function get_type( $type ) {
		$type = sanitize_key( (string) $type );
		return isset( $this->types[ $type ] ) ? $this->types[ $type ] : null;
	}

	/**
	 * @return array<string,array>
	 */
	public function get_types() {
		return $this->types;
	}

	/**
	 * Merge defaults with filter zskeleton_form_field_types.
	 */
	public function load_types() {
		$this->types = array();
		ZSkeleton_Form_Field_Types::register_phase1( $this );
		ZSkeleton_Form_Field_Types::register_phase2( $this );
		/**
		 * Add or override field types.
		 *
		 * @param array<string,array> $types Type => callbacks.
		 */
		$extra = apply_filters( 'zskeleton_form_field_types', array() );
		if ( is_array( $extra ) ) {
			foreach ( $extra as $type => $callbacks ) {
				if ( is_string( $type ) && is_array( $callbacks ) ) {
					$this->register( $type, $callbacks );
				}
			}
		}
	}
}
