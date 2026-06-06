<?php
/**
 * XML sitemap rules: exclude auth pages, control post types / taxonomies (Yoast + core).
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page slugs that must not appear in sitemaps (auth flows).
 *
 * @return string[]
 */
function zskeleton_get_sitemap_excluded_page_slugs() {
	$slugs = array(
		'login',
		'register',
		'forgot-password',
		'lost-password',
		'reset-password',
	);

	/**
	 * Filter page slugs excluded from Yoast / WordPress XML sitemaps.
	 *
	 * @param string[] $slugs Post name slugs.
	 */
	return apply_filters( 'zskeleton_sitemap_excluded_page_slugs', $slugs );
}

/**
 * Published page IDs to omit from sitemaps (resolved from slugs + auth options).
 *
 * @return int[]
 */
function zskeleton_get_sitemap_excluded_page_ids() {
	$ids = array();

	foreach ( zskeleton_get_sitemap_excluded_page_slugs() as $slug ) {
		if ( ! function_exists( 'zskeleton_find_page_by_slug' ) ) {
			continue;
		}
		$page = zskeleton_find_page_by_slug( $slug );
		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			$ids[] = (int) $page->ID;
		}
	}

	foreach ( array(
		'zskeleton_auth_login_page_id',
		'zskeleton_auth_register_page_id',
		'zskeleton_auth_lost_password_page_id',
		'zskeleton_auth_reset_password_page_id',
	) as $option ) {
		$id = absint( get_option( $option, 0 ) );
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}

	$ids = array_values( array_unique( array_filter( $ids ) ) );

	/**
	 * Filter page IDs excluded from XML sitemaps.
	 *
	 * @param int[] $ids Page post IDs.
	 */
	return apply_filters( 'zskeleton_sitemap_excluded_page_ids', $ids );
}

/**
 * Whether a page should be omitted from sitemaps.
 *
 * @param int|WP_Post $post Post ID or object.
 * @return bool
 */
function zskeleton_is_sitemap_excluded_page( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'page' !== $post->post_type ) {
		return false;
	}

	return in_array( (int) $post->ID, zskeleton_get_sitemap_excluded_page_ids(), true );
}

/**
 * Sanitize a list of taxonomy or post type slugs for storage.
 *
 * @param mixed $value Raw option value.
 * @return string[]
 */
function zskeleton_sanitize_sitemap_slug_list( $value ) {
	if ( is_string( $value ) ) {
		$value = explode( ',', $value );
	}
	if ( ! is_array( $value ) ) {
		return array();
	}
	$out = array();
	foreach ( $value as $slug ) {
		$slug = sanitize_key( (string) $slug );
		if ( '' !== $slug ) {
			$out[] = $slug;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Default post types included in the XML sitemap when not configured yet.
 *
 * @return string[]
 */
function zskeleton_get_default_sitemap_post_types() {
	$types = array( 'post', 'page' );
	foreach ( array( 'zskeleton_faqs', 'zskeleton_services' ) as $pt ) {
		if ( post_type_exists( $pt ) ) {
			$types[] = $pt;
		}
	}

	/**
	 * @param string[] $types Post type names.
	 */
	return apply_filters( 'zskeleton_default_sitemap_post_types', $types );
}

/**
 * Default taxonomies included in the XML sitemap when not configured yet.
 *
 * @return string[]
 */
function zskeleton_get_default_sitemap_taxonomies() {
	$taxonomies = array( 'category', 'post_tag' );
	if ( taxonomy_exists( 'zskeleton_landing' ) ) {
		$taxonomies[] = 'zskeleton_landing';
	}

	/**
	 * @param string[] $taxonomies Taxonomy names.
	 */
	return apply_filters( 'zskeleton_default_sitemap_taxonomies', $taxonomies );
}

/**
 * Post types the theme allows in XML sitemaps (Appearance → Settings → Content).
 *
 * @return string[]
 */
function zskeleton_get_sitemap_enabled_post_types() {
	$raw = get_option( 'zskeleton_sitemap_post_types', false );
	if ( false === $raw ) {
		$enabled = zskeleton_get_default_sitemap_post_types();
	} else {
		$enabled = zskeleton_sanitize_sitemap_slug_list( $raw );
	}

	/**
	 * @param string[] $enabled Post type names.
	 */
	return apply_filters( 'zskeleton_sitemap_enabled_post_types', $enabled );
}

/**
 * Taxonomies the theme allows in XML sitemaps.
 *
 * @return string[]
 */
function zskeleton_get_sitemap_enabled_taxonomies() {
	$raw = get_option( 'zskeleton_sitemap_taxonomies', false );
	if ( false === $raw ) {
		$enabled = zskeleton_get_default_sitemap_taxonomies();
	} else {
		$enabled = zskeleton_sanitize_sitemap_slug_list( $raw );
	}

	/**
	 * @param string[] $enabled Taxonomy names.
	 */
	return apply_filters( 'zskeleton_sitemap_enabled_taxonomies', $enabled );
}

/**
 * Include categories/tags with zero posts in the taxonomy sitemap (Yoast hides them by default).
 *
 * @return bool
 */
function zskeleton_sitemap_include_empty_terms() {
	$include = '1' === (string) get_option( 'zskeleton_sitemap_include_empty_terms', '1' );

	/**
	 * @param bool $include Whether to include empty terms.
	 */
	return (bool) apply_filters( 'zskeleton_sitemap_include_empty_terms', $include );
}

/**
 * Public post types offered in theme sitemap settings.
 *
 * @return array<string,string> slug => label
 */
function zskeleton_get_sitemap_selectable_post_types() {
	$out = array();
	foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $obj ) {
		if ( ! $obj instanceof WP_Post_Type || in_array( $obj->name, array( 'attachment' ), true ) ) {
			continue;
		}
		$out[ $obj->name ] = $obj->labels->singular_name ? $obj->labels->singular_name : $obj->label;
	}

	/**
	 * @param array<string,string> $out slug => label.
	 */
	return apply_filters( 'zskeleton_sitemap_selectable_post_types', $out );
}

/**
 * Public taxonomies offered in theme sitemap settings.
 *
 * @return array<string,string> slug => label
 */
function zskeleton_get_sitemap_selectable_taxonomies() {
	$skip = array( 'nav_menu', 'link_category', 'post_format', 'wp_pattern_category' );
	$out  = array();
	foreach ( get_taxonomies( array( 'public' => true ), 'objects' ) as $obj ) {
		if ( ! $obj instanceof WP_Taxonomy || in_array( $obj->name, $skip, true ) ) {
			continue;
		}
		$out[ $obj->name ] = $obj->labels->singular_name ? $obj->labels->singular_name : $obj->label;
	}

	/**
	 * @param array<string,string> $out slug => label.
	 */
	return apply_filters( 'zskeleton_sitemap_selectable_taxonomies', $out );
}

/**
 * Clear Yoast SEO sitemap transients after settings change.
 *
 * @return void
 */
function zskeleton_clear_yoast_sitemap_cache() {
	if ( class_exists( 'WPSEO_Sitemaps_Cache' ) ) {
		WPSEO_Sitemaps_Cache::clear();
	}
}

/**
 * Sanitize post-type list on save (custom theme settings form bypasses options.php sanitizers).
 *
 * @param mixed $value New value.
 * @return string[]
 */
function zskeleton_pre_update_sitemap_post_types( $value ) {
	return zskeleton_sanitize_sitemap_slug_list( $value );
}

/**
 * @param mixed $value New value.
 * @return string[]
 */
function zskeleton_pre_update_sitemap_taxonomies( $value ) {
	return zskeleton_sanitize_sitemap_slug_list( $value );
}

add_filter( 'pre_update_option_zskeleton_sitemap_post_types', 'zskeleton_pre_update_sitemap_post_types' );
add_filter( 'pre_update_option_zskeleton_sitemap_taxonomies', 'zskeleton_pre_update_sitemap_taxonomies' );

add_action(
	'update_option_zskeleton_sitemap_post_types',
	static function () {
		zskeleton_clear_yoast_sitemap_cache();
	}
);
add_action(
	'update_option_zskeleton_sitemap_taxonomies',
	static function () {
		zskeleton_clear_yoast_sitemap_cache();
	}
);
add_action(
	'update_option_zskeleton_sitemap_include_empty_terms',
	static function () {
		zskeleton_clear_yoast_sitemap_cache();
	}
);

/**
 * Register Yoast + core sitemap filters.
 */
function zskeleton_register_sitemap_exclusions() {
	// Yoast: correct filter — {@see WPSEO_Post_Type_Sitemap_Provider::get_excluded_posts()}.
	add_filter(
		'wpseo_exclude_from_sitemap_by_post_ids',
		static function ( $excluded_posts_ids ) {
			if ( ! is_array( $excluded_posts_ids ) ) {
				$excluded_posts_ids = array();
			}
			$auth_ids = zskeleton_get_sitemap_excluded_page_ids();
			if ( empty( $auth_ids ) ) {
				return $excluded_posts_ids;
			}
			return array_values( array_unique( array_merge( $excluded_posts_ids, $auth_ids ) ) );
		}
	);

	add_filter(
		'wpseo_sitemap_exclude_post_type',
		static function ( $exclude, $post_type ) {
			if ( $exclude ) {
				return true;
			}
			$enabled = zskeleton_get_sitemap_enabled_post_types();
			if ( empty( $enabled ) ) {
				return true;
			}
			return ! in_array( $post_type, $enabled, true );
		},
		10,
		2
	);

	add_filter(
		'wpseo_sitemap_exclude_taxonomy',
		static function ( $exclude, $taxonomy_name ) {
			if ( $exclude ) {
				return true;
			}
			$enabled = zskeleton_get_sitemap_enabled_taxonomies();
			if ( empty( $enabled ) ) {
				return true;
			}
			return ! in_array( $taxonomy_name, $enabled, true );
		},
		10,
		2
	);

	add_filter(
		'wpseo_sitemap_exclude_empty_terms',
		static function ( $exclude, $taxonomy_names ) {
			unset( $taxonomy_names );
			return ! zskeleton_sitemap_include_empty_terms();
		},
		10,
		2
	);

	// WordPress core sitemaps (when Yoast is off).
	add_filter(
		'wp_sitemaps_post_types',
		static function ( $post_types ) {
			$enabled = zskeleton_get_sitemap_enabled_post_types();
			if ( empty( $enabled ) ) {
				return array();
			}
			return array_intersect_key( $post_types, array_flip( $enabled ) );
		}
	);

	add_filter(
		'wp_sitemaps_taxonomies',
		static function ( $taxonomies ) {
			$enabled = zskeleton_get_sitemap_enabled_taxonomies();
			if ( empty( $enabled ) ) {
				return array();
			}
			return array_intersect_key( $taxonomies, array_flip( $enabled ) );
		}
	);

	add_filter(
		'wp_sitemaps_posts_query_args',
		static function ( $args, $post_type ) {
			if ( 'page' !== $post_type ) {
				return $args;
			}
			$omit = zskeleton_get_sitemap_excluded_page_ids();
			if ( empty( $omit ) ) {
				return $args;
			}
			$existing             = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
			$args['post__not_in'] = array_values( array_unique( array_merge( $existing, $omit ) ) );
			return $args;
		},
		10,
		2
	);

	add_filter(
		'wp_sitemaps_taxonomies_query_args',
		static function ( $args, $taxonomy ) {
			if ( zskeleton_sitemap_include_empty_terms() ) {
				$args['hide_empty'] = false;
			}
			unset( $taxonomy );
			return $args;
		},
		10,
		2
	);
}
add_action( 'init', 'zskeleton_register_sitemap_exclusions', 20 );
