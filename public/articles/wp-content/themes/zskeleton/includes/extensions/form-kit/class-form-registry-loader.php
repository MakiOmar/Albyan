<?php
/**
 * Loads UI-built forms from zskeleton_form CPT into Form Kit registry.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bridges CPT meta to zskeleton_form_kit_forms.
 */
class ZSkeleton_Form_Registry_Loader {

	const CACHE_GROUP = 'zskeleton_form_kit';

	const CACHE_KEY = 'ui_form_definitions';

	/**
	 * Prevent recursive filter when resolving code-registered form ids.
	 *
	 * @var bool
	 */
	private static $merging = false;

	/**
	 * Bootstrap hooks.
	 */
	public static function init() {
		add_filter( 'zskeleton_form_kit_forms', array( __CLASS__, 'merge_ui_forms' ), 20, 1 );
		add_action( 'save_post_' . ZSkeleton_Forms_CPT::POST_TYPE, array( __CLASS__, 'bust_cache' ), 10, 1 );
		add_action( 'deleted_post', array( __CLASS__, 'maybe_bust_cache_on_delete' ), 10, 1 );
		add_action( 'transition_post_status', array( __CLASS__, 'maybe_bust_cache_on_status' ), 10, 3 );
	}

	/**
	 * Resolve a UI-built form config by public id (cache + direct DB fallback).
	 *
	 * @param string $form_id Shortcode / registry id.
	 * @return array<string,mixed>|null
	 */
	public static function get_form_config( $form_id ) {
		$form_id = sanitize_key( (string) $form_id );
		if ( '' === $form_id ) {
			return null;
		}

		foreach ( self::get_candidate_form_ids( $form_id ) as $candidate ) {
			$forms = apply_filters( 'zskeleton_form_kit_forms', array() );
			if (
				is_array( $forms )
				&& isset( $forms[ $candidate ] )
				&& is_array( $forms[ $candidate ] )
				&& self::config_has_fields( $forms[ $candidate ] )
			) {
				return $forms[ $candidate ];
			}
		}

		foreach ( self::get_candidate_form_ids( $form_id ) as $candidate ) {
			$config = self::load_form_config_from_database( $candidate );
			if ( is_array( $config ) && self::config_has_fields( $config ) ) {
				return $config;
			}
		}

		return null;
	}

	/**
	 * Alternate ids to try when resolving a shortcode id (ui_ prefix collisions).
	 *
	 * @param string $form_id Requested id.
	 * @return string[]
	 */
	public static function get_candidate_form_ids( $form_id ) {
		$form_id = sanitize_key( (string) $form_id );
		$ids     = array( $form_id );

		if ( str_starts_with( $form_id, 'ui_' ) ) {
			$base = substr( $form_id, 3 );
			if ( '' !== $base ) {
				$ids[] = $base;
			}
		} else {
			$ids[] = 'ui_' . $form_id;
		}

		return array_values( array_unique( array_filter( $ids ) ) );
	}

	/**
	 * Whether a form config contains renderable fields.
	 *
	 * @param array<string,mixed> $config Form config.
	 * @return bool
	 */
	public static function config_has_fields( array $config ) {
		if ( ! empty( $config['layout_tree'] ) && is_array( $config['layout_tree'] ) ) {
			return count( $config['layout_tree'] ) > 0;
		}
		if ( ! empty( $config['fields'] ) && is_array( $config['fields'] ) ) {
			return count( $config['fields'] ) > 0;
		}
		if ( ! empty( $config['steps'] ) && is_array( $config['steps'] ) ) {
			return count( $config['steps'] ) > 0;
		}
		return false;
	}

	/**
	 * Find a UI form post matching a shortcode id (for admin diagnostics).
	 *
	 * @param string $form_id Requested id.
	 * @return WP_Post|null
	 */
	public static function find_ui_form_post( $form_id ) {
		$candidates = self::get_candidate_form_ids( $form_id );
		$posts      = get_posts(
			array(
				'post_type'      => ZSkeleton_Forms_CPT::POST_TYPE,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => 200,
				'no_found_rows'  => true,
			)
		);
		$code_ids   = self::get_code_registered_form_ids();

		foreach ( $posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}
			$slug     = sanitize_key( (string) $post->post_name );
			$resolved = self::resolve_public_form_id( $slug, $code_ids );
			if ( in_array( $resolved, $candidates, true ) || in_array( $slug, $candidates, true ) ) {
				return $post;
			}
		}

		return null;
	}

	/**
	 * Load one published UI form directly from the database (bypasses registry cache).
	 *
	 * @param string $form_id Resolved public id.
	 * @return array<string,mixed>|null
	 */
	private static function load_form_config_from_database( $form_id ) {
		$form_id  = sanitize_key( (string) $form_id );
		$posts    = get_posts(
			array(
				'post_type'      => ZSkeleton_Forms_CPT::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'no_found_rows'  => true,
			)
		);
		$code_ids = self::get_code_registered_form_ids();

		foreach ( $posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}
			$slug     = sanitize_key( (string) $post->post_name );
			$resolved = self::resolve_public_form_id( $slug, $code_ids );
			if ( $resolved !== $form_id ) {
				continue;
			}

			$config = self::map_post_to_form_config( $post, $form_id );
			if ( ! empty( $config ) ) {
				return $config;
			}
		}

		return null;
	}

	/**
	 * @param array<string,array<string,mixed>> $forms Registered forms.
	 * @return array<string,array<string,mixed>>
	 */
	public static function merge_ui_forms( $forms ) {
		if ( self::$merging ) {
			return is_array( $forms ) ? $forms : array();
		}
		if ( ! is_array( $forms ) ) {
			$forms = array();
		}

		self::$merging = true;
		foreach ( self::get_ui_form_configs() as $form_id => $config ) {
			if ( isset( $forms[ $form_id ] ) ) {
				continue;
			}
			$forms[ $form_id ] = $config;
		}
		self::$merging = false;

		return $forms;
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_ui_form_configs() {
		$cached = wp_cache_get( self::CACHE_KEY, self::CACHE_GROUP );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$transient_key = self::get_transient_key();
		$transient     = get_transient( $transient_key );
		if ( is_array( $transient ) ) {
			wp_cache_set( self::CACHE_KEY, $transient, self::CACHE_GROUP, HOUR_IN_SECONDS );
			return $transient;
		}

		$configs = self::load_from_database();
		set_transient( $transient_key, $configs, HOUR_IN_SECONDS );
		wp_cache_set( self::CACHE_KEY, $configs, self::CACHE_GROUP, HOUR_IN_SECONDS );

		return $configs;
	}

	/**
	 * @return array<string,array<string,mixed>>
	 */
	private static function load_from_database() {
		$configs = array();
		$posts   = get_posts(
			array(
				'post_type'      => ZSkeleton_Forms_CPT::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 200,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		$code_ids = self::get_code_registered_form_ids();

		foreach ( $posts as $post ) {
			if ( ! $post instanceof WP_Post ) {
				continue;
			}
			$slug    = sanitize_key( (string) $post->post_name );
			$form_id = self::resolve_public_form_id( $slug, $code_ids );
			if ( '' === $form_id ) {
				continue;
			}

			$config = self::map_post_to_form_config( $post, $form_id );
			if ( self::config_has_fields( $config ) ) {
				$configs[ $form_id ] = $config;
			}
		}

		return $configs;
	}

	/**
	 * @param WP_Post $post Post.
	 * @param string  $form_id Resolved form id.
	 * @return array<string,mixed>
	 */
	private static function map_post_to_form_config( WP_Post $post, $form_id ) {
		$schema = ZSkeleton_Forms_CPT::get_schema( $post->ID );
		$events = ZSkeleton_Forms_CPT::get_events( $post->ID );

		$config = array(
			'id'                      => $form_id,
			'context'                 => isset( $schema['context'] ) ? (string) $schema['context'] : 'public',
			'allow_public_submission' => ! empty( $schema['allow_public_submission'] ),
			'use_ajax'                => ! isset( $schema['use_ajax'] ) || ! empty( $schema['use_ajax'] ),
			'fallback'                => isset( $schema['fallback'] ) ? (string) $schema['fallback'] : 'long_page',
			'ui_post_id'              => (int) $post->ID,
			'ui_events'               => $events,
		);

		if ( ! empty( $schema['honeypot'] ) ) {
			$config['honeypot'] = sanitize_key( (string) $schema['honeypot'] );
		}

		if ( ! empty( $schema['capability'] ) ) {
			$config['capability'] = sanitize_key( (string) $schema['capability'] );
		}

		if ( ! empty( $schema['success_message'] ) ) {
			$config['success_message'] = (string) $schema['success_message'];
		}

		if ( ! empty( $schema['submit_button_text'] ) ) {
			$config['submit_button_text'] = (string) $schema['submit_button_text'];
		}

		if ( ! empty( $schema['layout'] ) && is_array( $schema['layout'] ) ) {
			$config['layout'] = $schema['layout'];
		}

		if ( ! empty( $schema['steps'] ) && is_array( $schema['steps'] ) ) {
			$config['steps'] = $schema['steps'];
		} elseif ( ! empty( $schema['layout_tree'] ) && is_array( $schema['layout_tree'] ) ) {
			$config['layout_tree'] = $schema['layout_tree'];
		} elseif ( ! empty( $schema['fields'] ) && is_array( $schema['fields'] ) ) {
			$config['fields'] = $schema['fields'];
		} else {
			return array();
		}

		if ( ! empty( $events ) ) {
			$config['on_submit'] = array( 'ZSkeleton_Form_Events_Runner', 'run' );
		}

		return $config;
	}

	/**
	 * @param string        $slug Post slug.
	 * @param array<string> $code_ids Optional preloaded code form ids.
	 * @return string
	 */
	public static function resolve_public_form_id( $slug, array $code_ids = array() ) {
		$slug = sanitize_key( (string) $slug );
		if ( '' === $slug ) {
			return '';
		}

		if ( empty( $code_ids ) ) {
			$code_ids = self::get_code_registered_form_ids();
		}

		if ( in_array( $slug, $code_ids, true ) ) {
			$prefixed = 'ui_' . $slug;
			if ( ! in_array( $prefixed, $code_ids, true ) ) {
				return $prefixed;
			}
			return $prefixed;
		}

		return $slug;
	}

	/**
	 * Form ids registered in PHP before UI merge.
	 *
	 * @return string[]
	 */
	public static function get_code_registered_form_ids() {
		$forms = apply_filters( 'zskeleton_form_kit_forms', array() );
		if ( ! is_array( $forms ) ) {
			return array();
		}
		return array_map( 'strval', array_keys( $forms ) );
	}

	/**
	 * @param int $post_id Post id.
	 */
	public static function bust_cache( $post_id ) {
		unset( $post_id );
		delete_transient( self::get_transient_key() );
		wp_cache_delete( self::CACHE_KEY, self::CACHE_GROUP );
	}

	/**
	 * @param int $post_id Post id.
	 */
	public static function maybe_bust_cache_on_delete( $post_id ) {
		$post = get_post( $post_id );
		if ( $post && ZSkeleton_Forms_CPT::POST_TYPE === $post->post_type ) {
			self::bust_cache( $post_id );
		}
	}

	/**
	 * @param string  $new_status New status.
	 * @param string  $old_status Old status.
	 * @param WP_Post $post       Post.
	 */
	public static function maybe_bust_cache_on_status( $new_status, $old_status, $post ) {
		unset( $new_status, $old_status );
		if ( $post instanceof WP_Post && ZSkeleton_Forms_CPT::POST_TYPE === $post->post_type ) {
			self::bust_cache( (int) $post->ID );
		}
	}

	/**
	 * @return string
	 */
	private static function get_transient_key() {
		return 'zskeleton_ui_forms_v' . ZSkeleton_FORM_KIT_VERSION;
	}
}
