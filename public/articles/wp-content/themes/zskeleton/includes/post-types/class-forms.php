<?php
/**
 * UI-built Form Kit definitions (CPT + schema/events meta).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers zskeleton_form CPT for admin-built forms.
 */
class ZSkeleton_Forms_CPT {

	const POST_TYPE = 'zskeleton_form';

	const META_SCHEMA = '_zskeleton_form_schema';

	const META_EVENTS = '_zskeleton_form_events';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'admin_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_admin_column' ), 10, 2 );
	}

	/**
	 * Register custom post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Forms', 'post type general name', 'zskeleton' ),
			'singular_name'      => _x( 'Form', 'post type singular name', 'zskeleton' ),
			'menu_name'          => __( 'Forms', 'zskeleton' ),
			'add_new'            => __( 'Add New', 'zskeleton' ),
			'add_new_item'       => __( 'Add New Form', 'zskeleton' ),
			'edit_item'          => __( 'Edit Form', 'zskeleton' ),
			'new_item'           => __( 'New Form', 'zskeleton' ),
			'view_item'          => __( 'View Form', 'zskeleton' ),
			'search_items'       => __( 'Search Forms', 'zskeleton' ),
			'not_found'          => __( 'No forms found.', 'zskeleton' ),
			'not_found_in_trash' => __( 'No forms found in Trash.', 'zskeleton' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => ZSkeleton_Theme_Features_Admin::MENU_SLUG,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'page',
			'map_meta_cap'        => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'supports'            => array( 'title' ),
			'show_in_rest'        => false,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * @param array<string,string> $columns Columns.
	 * @return array<string,string>
	 */
	public function admin_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['zs_form_shortcode'] = __( 'Shortcode', 'zskeleton' );
				$new['zs_form_id']        = __( 'Form ID', 'zskeleton' );
				$new['zs_form_entries']   = __( 'Submissions', 'zskeleton' );
			}
		}
		return $new;
	}

	/**
	 * @param string $column Column key.
	 * @param int    $post_id Post ID.
	 */
	public function render_admin_column( $column, $post_id ) {
		if ( 'zs_form_shortcode' === $column ) {
			$form_id = self::get_form_id_for_post( $post_id );
			if ( '' !== $form_id ) {
				echo '<code>[zskeleton_form id="' . esc_attr( $form_id ) . '"]</code>';
				echo '<br><code>[zskeleton_form_submissions form_id="' . esc_attr( $form_id ) . '"]</code>';
			}
			return;
		}
		if ( 'zs_form_id' === $column ) {
			$form_id = self::get_form_id_for_post( $post_id );
			if ( '' !== $form_id ) {
				echo '<code>' . esc_html( $form_id ) . '</code>';
			}
			return;
		}
		if ( 'zs_form_entries' === $column ) {
			$form_id = self::get_form_id_for_post( $post_id );
			if ( '' === $form_id ) {
				return;
			}
			$count = ZSkeleton_Form_Submissions_Repository::count_for_form( $form_id );
			$url   = add_query_arg(
				array(
					'page'    => 'zskeleton-form-submissions',
					'form_id' => $form_id,
				),
				admin_url( 'admin.php' )
			);
			echo '<a href="' . esc_url( $url ) . '">' . esc_html( (string) $count ) . '</a>';
		}
	}

	/**
	 * Resolve Form Kit id from a form post.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public static function get_form_id_for_post( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || self::POST_TYPE !== $post->post_type ) {
			return '';
		}
		$slug = sanitize_key( (string) $post->post_name );
		if ( '' === $slug ) {
			return '';
		}
		return ZSkeleton_Form_Registry_Loader::resolve_public_form_id( $slug );
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array<string,mixed>
	 */
	public static function get_schema( $post_id ) {
		$raw = get_post_meta( $post_id, self::META_SCHEMA, true );
		if ( is_string( $raw ) && '' !== $raw ) {
			$decoded = json_decode( $raw, true );
			if ( is_array( $decoded ) ) {
				return $decoded;
			}
		}
		if ( is_array( $raw ) ) {
			return $raw;
		}
		return array();
	}

	/**
	 * @param int $post_id Post ID.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_events( $post_id ) {
		$raw = get_post_meta( $post_id, self::META_EVENTS, true );
		if ( is_string( $raw ) && '' !== $raw ) {
			$decoded = json_decode( $raw, true );
			if ( is_array( $decoded ) ) {
				return $decoded;
			}
		}
		if ( is_array( $raw ) ) {
			return $raw;
		}
		return array();
	}
}

new ZSkeleton_Forms_CPT();
