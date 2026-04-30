<?php
/**
 * Form schema: steps, fields, security flags.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Normalizes and exposes a form definition from the zskeleton_form_kit_forms filter.
 */
class ZSkeleton_Form_Definition {

	/**
	 * Raw normalized config.
	 *
	 * @var array
	 */
	private $config = array();

	/**
	 * Flat list of field configs keyed by name.
	 *
	 * @var array
	 */
	private $fields_by_name = array();

	/**
	 * Step index => list of field names.
	 *
	 * @var array
	 */
	private $step_field_names = array();

	/**
	 * @param array $config Form configuration.
	 */
	public function __construct( array $config ) {
		$this->normalize_config( $config );
	}

	/**
	 * Load definition by id.
	 *
	 * @param string $form_id Form id.
	 * @return self|null
	 */
	public static function get( $form_id ) {
		$form_id = sanitize_key( (string) $form_id );
		if ( '' === $form_id ) {
			return null;
		}
		$forms = apply_filters( 'zskeleton_form_kit_forms', array() );
		if ( ! is_array( $forms ) || ! isset( $forms[ $form_id ] ) || ! is_array( $forms[ $form_id ] ) ) {
			return null;
		}
		$cfg        = $forms[ $form_id ];
		$cfg['id']  = $form_id;
		return new self( $cfg );
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return isset( $this->config['id'] ) ? (string) $this->config['id'] : '';
	}

	/**
	 * @return string
	 */
	public function get_nonce_action() {
		$action = isset( $this->config['nonce_action'] ) ? (string) $this->config['nonce_action'] : '';
		if ( '' === $action ) {
			return 'zskeleton_form_' . $this->get_id();
		}
		return $action;
	}

	/**
	 * Capability required for processing (admin forms). Empty string = check logged-in only for non-public.
	 *
	 * @return string
	 */
	public function get_capability() {
		return isset( $this->config['capability'] ) ? (string) $this->config['capability'] : 'manage_options';
	}

	/**
	 * @return bool
	 */
	public function allow_public_submission() {
		return ! empty( $this->config['allow_public_submission'] );
	}

	/**
	 * admin|public — affects CSS classes and enqueue context hints.
	 *
	 * @return string
	 */
	public function get_context() {
		$c = isset( $this->config['context'] ) ? (string) $this->config['context'] : 'public';
		return in_array( $c, array( 'admin', 'public' ), true ) ? $c : 'public';
	}

	/**
	 * long_page|none — no-JS multi-step fallback.
	 *
	 * @return string
	 */
	public function get_fallback() {
		$f = isset( $this->config['fallback'] ) ? (string) $this->config['fallback'] : 'long_page';
		return in_array( $f, array( 'long_page', 'none' ), true ) ? $f : 'long_page';
	}

	/**
	 * @return bool
	 */
	public function use_ajax() {
		return ! isset( $this->config['use_ajax'] ) || ! empty( $this->config['use_ajax'] );
	}

	/**
	 * @return string|null Honeypot field name if enabled.
	 */
	public function get_honeypot_name() {
		if ( empty( $this->config['honeypot'] ) ) {
			return null;
		}
		$name = sanitize_key( (string) $this->config['honeypot'] );
		return '' !== $name ? $name : null;
	}

	/**
	 * @return bool
	 */
	public function has_wizard() {
		return count( $this->step_field_names ) > 1;
	}

	/**
	 * @return int
	 */
	public function get_step_count() {
		return count( $this->step_field_names );
	}

	/**
	 * @return array Field configs keyed by name.
	 */
	public function get_fields_by_name() {
		return $this->fields_by_name;
	}

	/**
	 * @return string[]
	 */
	public function get_all_field_names() {
		return array_keys( $this->fields_by_name );
	}

	/**
	 * @param int $step_index Zero-based.
	 * @return string[]
	 */
	public function get_field_names_for_step( $step_index ) {
		$step_index = (int) $step_index;
		return isset( $this->step_field_names[ $step_index ] )
			? $this->step_field_names[ $step_index ]
			: array();
	}

	/**
	 * @param int $step_index Zero-based.
	 * @return array<string,array>
	 */
	public function get_fields_for_step( $step_index ) {
		$names = $this->get_field_names_for_step( $step_index );
		$out   = array();
		foreach ( $names as $name ) {
			if ( isset( $this->fields_by_name[ $name ] ) ) {
				$out[ $name ] = $this->fields_by_name[ $name ];
			}
		}
		return $out;
	}

	/**
	 * Step metadata for renderer.
	 *
	 * @return array<int,array{id:string,title:string}>
	 */
	public function get_step_meta() {
		return isset( $this->config['steps_meta'] ) && is_array( $this->config['steps_meta'] )
			? $this->config['steps_meta']
			: array();
	}

	/**
	 * Optional custom process callback: ( sanitized_array, ZSkeleton_Form_Definition ): bool|WP_Error.
	 *
	 * @return callable|null
	 */
	public function get_submit_callback() {
		if ( empty( $this->config['on_submit'] ) || ! is_callable( $this->config['on_submit'] ) ) {
			return null;
		}
		return $this->config['on_submit'];
	}

	/**
	 * @return array
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * @param array $config Input config.
	 */
	private function normalize_config( array $config ) {
		$id = isset( $config['id'] ) ? sanitize_key( (string) $config['id'] ) : '';
		if ( '' === $id ) {
			$id = 'form_' . wp_generate_password( 8, false, false );
		}
		$this->config                 = $config;
		$this->config['id']           = $id;
		$this->fields_by_name         = array();
		$this->step_field_names       = array();

		if ( ! empty( $config['steps'] ) && is_array( $config['steps'] ) ) {
			$steps_meta = array();
			foreach ( $config['steps'] as $i => $step ) {
				if ( ! is_array( $step ) ) {
					continue;
				}
				$sid   = isset( $step['id'] ) ? sanitize_key( (string) $step['id'] ) : 'step_' . $i;
				$title = isset( $step['title'] ) ? (string) $step['title'] : sprintf( /* translators: %d: step number */ __( 'Step %d', 'zskeleton' ), $i + 1 );
				$steps_meta[] = array(
					'id'    => $sid,
					'title' => $title,
				);
				$fields = isset( $step['fields'] ) && is_array( $step['fields'] ) ? $step['fields'] : array();
				$this->step_field_names[ $i ] = array();
				foreach ( $fields as $field ) {
					$this->ingest_field( $field, $i );
				}
			}
			$this->config['steps_meta'] = $steps_meta;
		} else {
			$fields = isset( $config['fields'] ) && is_array( $config['fields'] ) ? $config['fields'] : array();
			$this->step_field_names[0] = array();
			foreach ( $fields as $field ) {
				$this->ingest_field( $field, 0 );
			}
		}

		if ( empty( $this->step_field_names ) ) {
			$this->step_field_names[0] = array();
		}
	}

	/**
	 * @param array $field Field config.
	 * @param int   $step_index Step index.
	 */
	private function ingest_field( array $field, $step_index ) {
		$name = isset( $field['name'] ) ? sanitize_key( (string) $field['name'] ) : '';
		if ( '' === $name ) {
			return;
		}
		if ( ! isset( $field['type'] ) ) {
			$field['type'] = 'text';
		}
		$field['name'] = $name;
		if ( isset( $this->fields_by_name[ $name ] ) ) {
			return;
		}
		$this->fields_by_name[ $name ]           = $field;
		$this->step_field_names[ $step_index ][] = $name;
	}
}
