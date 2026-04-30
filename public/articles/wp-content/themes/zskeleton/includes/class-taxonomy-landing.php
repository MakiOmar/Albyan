<?php
/**
 * Shared taxonomy to tag FAQs and Services for specific landing pages (e.g. SEO expert).
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers zskeleton_landing and query helpers.
 */
class ZSkeleton_Landing_Taxonomy {

	const TAXONOMY = 'zskeleton_landing';

	/**
	 * Constructor.
	 */
	public function __construct() {
		// After FAQs and Services CPTs (registered at init 10).
		add_action( 'init', array( $this, 'register_taxonomy' ), 11 );
	}

	/**
	 * Register taxonomy for FAQs and Services.
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'                       => _x( 'Landings', 'Taxonomy general name', 'zskeleton' ),
			'singular_name'              => _x( 'Landing', 'Taxonomy singular name', 'zskeleton' ),
			'menu_name'                  => __( 'Landings', 'zskeleton' ),
			'all_items'                  => __( 'All landings', 'zskeleton' ),
			'parent_item'                => __( 'Parent landing', 'zskeleton' ),
			'parent_item_colon'          => __( 'Parent landing:', 'zskeleton' ),
			'new_item_name'              => __( 'New landing name', 'zskeleton' ),
			'add_new_item'               => __( 'Add new landing', 'zskeleton' ),
			'edit_item'                  => __( 'Edit landing', 'zskeleton' ),
			'update_item'                => __( 'Update landing', 'zskeleton' ),
			'view_item'                  => __( 'View landing', 'zskeleton' ),
			'separate_items_with_commas' => __( 'Separate landings with commas', 'zskeleton' ),
			'add_or_remove_items'        => __( 'Add or remove landings', 'zskeleton' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'zskeleton' ),
			'popular_items'              => __( 'Popular landings', 'zskeleton' ),
			'search_items'               => __( 'Search landings', 'zskeleton' ),
			'not_found'                  => __( 'Not found', 'zskeleton' ),
			'no_terms'                   => __( 'No landings', 'zskeleton' ),
			'items_list'                 => __( 'Landings list', 'zskeleton' ),
			'items_list_navigation'      => __( 'Landings list navigation', 'zskeleton' ),
		);

		$args = array(
			'labels'            => $labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'         => 'landing',
				'with_front'   => false,
				'hierarchical' => false,
			),
		);

		register_taxonomy(
			self::TAXONOMY,
			array( 'zskeleton_faqs', ZSkeleton_Services::POST_TYPE ),
			$args
		);
	}

	/**
	 * FAQs for a landing term slug (post type + taxonomy only; order by menu_order/title).
	 *
	 * @param string $landing_slug Term slug (e.g. ahmad-maki).
	 * @param int    $limit        Max posts, -1 for all.
	 * @return WP_Post[]
	 */
	public static function get_faqs_by_landing( $landing_slug, $limit = -1 ) {
		$landing_slug = sanitize_title( $landing_slug );
		if ( '' === $landing_slug ) {
			return array();
		}

		$limit = (int) $limit;
		$pp    = ( $limit < 1 ) ? -1 : $limit;

		return get_posts(
			array(
				'post_type'      => 'zskeleton_faqs',
				'post_status'    => 'publish',
				'posts_per_page' => $pp,
				'tax_query'      => array(
					array(
						'taxonomy' => self::TAXONOMY,
						'field'    => 'slug',
						'terms'    => $landing_slug,
					),
				),
				'orderby'        => array(
					'menu_order' => 'ASC',
					'title'      => 'ASC',
				),
			)
		);
	}

	/**
	 * Services for a landing term slug.
	 *
	 * @param string $landing_slug Term slug.
	 * @param int    $limit        Max posts, -1 for all.
	 * @return WP_Post[]
	 */
	public static function get_services_by_landing( $landing_slug, $limit = -1 ) {
		$landing_slug = sanitize_title( $landing_slug );
		if ( '' === $landing_slug ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'      => ZSkeleton_Services::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
				'tax_query'      => array(
					array(
						'taxonomy' => self::TAXONOMY,
						'field'    => 'slug',
						'terms'    => $landing_slug,
					),
				),
			)
		);
	}
}

/**
 * Get FAQs tagged with a landing term.
 *
 * @param string $landing_slug Term slug.
 * @param int    $limit        Max posts.
 * @return WP_Post[]
 */
function zskeleton_get_faqs_by_landing( $landing_slug, $limit = -1 ) {
	return ZSkeleton_Landing_Taxonomy::get_faqs_by_landing( $landing_slug, $limit );
}

/**
 * Published FAQs for the SEO Expert template (same query strategy as page-faqs.php).
 *
 * Primary: order by `_zskeleton_faq_order` like the theme FAQs page.
 * Fallback: if none match (e.g. no order meta), newest published FAQs.
 *
 * @param int|null $limit Max posts; null uses theme option with a sensible floor.
 * @return WP_Post[]
 */
function zskeleton_get_seo_expert_faqs( $limit = null ) {
	if ( null === $limit ) {
		$opt = (int) get_option( 'zskeleton_faq_per_page', 10 );
		if ( $opt < 1 ) {
			$opt = 10;
		}
		$limit = max( $opt, 50 );
	}

	$limit = (int) apply_filters( 'zskeleton_seo_expert_faqs_limit', max( 1, $limit ) );

	$args = array(
		'post_type'              => 'zskeleton_faqs',
		'post_status'            => 'publish',
		'posts_per_page'         => $limit,
		'orderby'                => 'meta_value_num',
		'meta_key'               => '_zskeleton_faq_order',
		'order'                  => 'ASC',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	$posts = get_posts( apply_filters( 'zskeleton_seo_expert_faqs_query_args', $args ) );

	if ( ! empty( $posts ) ) {
		return $posts;
	}

	return get_posts(
		array(
			'post_type'              => 'zskeleton_faqs',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => true,
		)
	);
}

/**
 * Get services tagged with a landing term.
 *
 * @param string $landing_slug Term slug.
 * @param int    $limit        Max posts.
 * @return WP_Post[]
 */
function zskeleton_get_services_by_landing( $landing_slug, $limit = -1 ) {
	return ZSkeleton_Landing_Taxonomy::get_services_by_landing( $landing_slug, $limit );
}
