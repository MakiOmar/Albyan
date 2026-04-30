<?php
/**
 * Glossary entries (custom post type) — managed under Theme Features in admin.
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers `zskeleton_glossary` (max 20 chars for WordPress post type keys) and list/edit UI.
 */
class ZSkeleton_Glossary_Terms {

	/**
	 * Post type slug.
	 */
	const POST_TYPE = 'zskeleton_glossary';

	/**
	 * Meta key for manual sort order (lower = first).
	 */
	const META_ORDER = '_zskeleton_glossary_order';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'add_admin_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'show_admin_columns' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'orderby_custom_column' ) );
	}

	/**
	 * Register glossary post type.
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Glossary entries', 'Post type general name', 'zskeleton' ),
			'singular_name'         => _x( 'Glossary entry', 'Post type singular name', 'zskeleton' ),
			'menu_name'             => __( 'Glossary', 'zskeleton' ),
			'name_admin_bar'        => __( 'Glossary entry', 'zskeleton' ),
			'add_new'               => __( 'Add New', 'zskeleton' ),
			'add_new_item'          => __( 'Add glossary entry', 'zskeleton' ),
			'new_item'              => __( 'New glossary entry', 'zskeleton' ),
			'edit_item'             => __( 'Edit glossary entry', 'zskeleton' ),
			'view_item'             => __( 'View glossary entry', 'zskeleton' ),
			'all_items'             => __( 'All entries', 'zskeleton' ),
			'search_items'          => __( 'Search glossary', 'zskeleton' ),
			'parent_item_colon'     => __( 'Parent glossary entry:', 'zskeleton' ),
			'not_found'             => __( 'No glossary entries found.', 'zskeleton' ),
			'not_found_in_trash'    => __( 'No glossary entries found in Trash.', 'zskeleton' ),
			'archives'              => __( 'Glossary archives', 'zskeleton' ),
			'insert_into_item'      => __( 'Insert into glossary entry', 'zskeleton' ),
			'uploaded_to_this_item' => __( 'Uploaded to this glossary entry', 'zskeleton' ),
			'filter_items_list'     => __( 'Filter glossary list', 'zskeleton' ),
			'items_list'            => __( 'Glossary list', 'zskeleton' ),
			'items_list_navigation' => __( 'Glossary list navigation', 'zskeleton' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Term and definition pairs for glossary pages.', 'zskeleton' ),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => 'zskeleton-theme-features',
			'menu_position'       => null,
			'query_var'           => true,
			'rewrite'             => array(
				'slug'       => 'glossary',
				'with_front' => false,
			),
			'capability_type'     => 'post',
			'has_archive'         => true,
			'hierarchical'        => false,
			'supports'            => array( 'title', 'editor', 'revisions', 'page-attributes' ),
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => false,
			'can_export'          => true,
			'show_in_rest'        => true,
			'exclude_from_search' => false,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	/**
	 * Meta boxes.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'zskeleton_glossary_settings',
			__( 'Glossary settings', 'zskeleton' ),
			array( $this, 'render_settings_box' ),
			self::POST_TYPE,
			'side',
			'default'
		);
	}

	/**
	 * Side meta: display order.
	 *
	 * @param WP_Post $post Post.
	 */
	public function render_settings_box( $post ) {
		wp_nonce_field( 'zskeleton_glossary_meta_box', 'zskeleton_glossary_nonce' );
		$order = get_post_meta( $post->ID, self::META_ORDER, true );
		if ( '' === $order || null === $order ) {
			$order = (int) $post->menu_order;
		}
		?>
		<!-- Glossary entry: display order (used when listing terms). -->
		<div class="zs-meta-fields zs-meta-fields--compact zs-meta-fields--panel">
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_glossary_order"><?php esc_html_e( 'Display order', 'zskeleton' ); ?></label>
				<input type="number" class="widefat" id="zskeleton_glossary_order" name="zskeleton_glossary_order" min="0" step="1" value="<?php echo esc_attr( (string) (int) $order ); ?>" />
				<p class="zs-meta-field__hint"><?php esc_html_e( 'Lower numbers appear first when the theme lists glossary entries.', 'zskeleton' ); ?></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Persist order meta and keep menu_order in sync for optional menu usage.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_boxes( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['zskeleton_glossary_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zskeleton_glossary_nonce'] ) ), 'zskeleton_glossary_meta_box' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! $post instanceof WP_Post || self::POST_TYPE !== $post->post_type ) {
			return;
		}

		$order = isset( $_POST['zskeleton_glossary_order'] ) ? (int) $_POST['zskeleton_glossary_order'] : 0;
		if ( $order < 0 ) {
			$order = 0;
		}
		update_post_meta( $post_id, self::META_ORDER, $order );

		if ( (int) $post->menu_order !== $order ) {
			remove_action( 'save_post', array( $this, 'save_meta_boxes' ), 10 );
			wp_update_post(
				array(
					'ID'         => $post_id,
					'menu_order' => $order,
				)
			);
			add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );
		}
	}

	/**
	 * List table columns.
	 *
	 * @param array<string,string> $columns Columns.
	 * @return array<string,string>
	 */
	public function add_admin_columns( $columns ) {
		$new                   = array();
		$new['cb']             = $columns['cb'];
		$new['title']          = $columns['title'];
		$new['glossary_order'] = __( 'Order', 'zskeleton' );
		$new['date']           = $columns['date'];
		return $new;
	}

	/**
	 * @param string $column Column key.
	 * @param int    $post_id Post ID.
	 */
	public function show_admin_columns( $column, $post_id ) {
		if ( 'glossary_order' !== $column ) {
			return;
		}
		$order = get_post_meta( $post_id, self::META_ORDER, true );
		echo $order !== '' ? esc_html( (string) (int) $order ) : '0';
	}

	/**
	 * @param array<string,string> $columns Sortable map.
	 * @return array<string,string>
	 */
	public function sortable_columns( $columns ) {
		$columns['glossary_order'] = 'glossary_order';
		return $columns;
	}

	/**
	 * @param WP_Query $query Query.
	 */
	public function orderby_custom_column( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( 'glossary_order' !== $query->get( 'orderby' ) ) {
			return;
		}
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}
		$query->set( 'meta_key', self::META_ORDER );
		$query->set( 'orderby', 'meta_value_num' );
	}

	/**
	 * Default query args for published glossary entries (ordered).
	 *
	 * @param array<string,mixed> $args Overrides for get_posts().
	 * @return array<int,WP_Post>
	 */
	public static function get_entries( array $args = array() ) {
		$defaults = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_key'       => self::META_ORDER,
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		);
		return get_posts( wp_parse_args( $args, $defaults ) );
	}
}

/**
 * Published glossary entries, ordered by display order then title.
 *
 * @param array<string,mixed> $args Optional get_posts() overrides.
 * @return array<int,WP_Post>
 */
function zskeleton_get_glossary_entries( array $args = array() ) {
	return ZSkeleton_Glossary_Terms::get_entries( $args );
}
