<?php
/**
 * Blog listing hub: featured area, trending, category term listing, view counts, helpers.
 *
 * Used by the “Blog listing” page template and optional view tracking on single posts.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/** Post meta key for simple read counts (used when trending mode is “views”). */
const ZSKELETON_BLOG_POST_VIEWS_META = '_zskeleton_post_views';

/** Post meta: mark a post for the blog hub “Featured” strip (highest priority). */
const ZSKELETON_BLOG_HUB_POST_FEATURED_META = '_zskeleton_blog_hub_featured';

/**
 * @param string $option Option name.
 * @param mixed  $default Default when unset.
 * @return mixed
 */
function zskeleton_blog_hub_get_option( $option, $default = '' ) {
	return get_option( $option, $default );
}

/**
 * @return bool
 */
function zskeleton_blog_hub_show_on_first_page_only() {
	return true;
}

/**
 * Visitor fingerprint for one view count per cooldown (lightweight, not cryptographically strong).
 *
 * @return string
 */
function zskeleton_blog_hub_visitor_fingerprint() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	return substr( md5( $ip . '|' . $ua ), 0, 32 );
}

/**
 * Increment post view meta once per visitor/post within cooldown.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function zskeleton_blog_hub_maybe_increment_views( $post_id ) {
	$post_id = absint( $post_id );
	if ( $post_id < 1 ) {
		return;
	}
	if ( '1' !== (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_track_post_views', '1' ) ) {
		return;
	}
	$lock_key = 'zskeleton_pv_' . $post_id . '_' . zskeleton_blog_hub_visitor_fingerprint();
	if ( get_transient( $lock_key ) ) {
		return;
	}
	set_transient( $lock_key, 1, 30 * MINUTE_IN_SECONDS );
	$current = (int) get_post_meta( $post_id, ZSKELETON_BLOG_POST_VIEWS_META, true );
	update_post_meta( $post_id, ZSKELETON_BLOG_POST_VIEWS_META, $current + 1 );
}

/**
 * @return void
 */
function zskeleton_blog_hub_template_redirect_track_views() {
	if ( ! is_singular( 'post' ) || is_preview() ) {
		return;
	}
	if ( current_user_can( 'edit_posts' ) ) {
		return;
	}
	zskeleton_blog_hub_maybe_increment_views( get_queried_object_id() );
}
add_action( 'template_redirect', 'zskeleton_blog_hub_template_redirect_track_views', 20 );

/**
 * @param int $user_id User ID.
 * @param int $post_id Post ID.
 * @return bool
 */
function zskeleton_blog_hub_user_has_post_access( $user_id, $post_id ) {
	if ( class_exists( 'ZSkeleton_Access_Control' ) ) {
		$access_control = new ZSkeleton_Access_Control();
		return (bool) $access_control->user_has_content_access( (int) $user_id, (int) $post_id );
	}
	return true;
}

/**
 * Ordered list of post IDs for the featured strip:
 * posts marked “Blog featured” (post meta), then sticky posts only (no fallback to newest posts).
 *
 * @param int $count Desired count (clamped 1–12).
 * @return int[]
 */
function zskeleton_blog_hub_get_featured_post_ids( $count ) {
	$count = max( 1, min( 12, absint( $count ) ) );
	$ids   = array();

	// 1) Curated: posts with ZSKELETON_BLOG_HUB_POST_FEATURED_META (menu order, then newest).
	$q_manual = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'orderby'             => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'fields'              => 'ids',
			'meta_query'          => array(
				array(
					'key'   => ZSKELETON_BLOG_HUB_POST_FEATURED_META,
					'value' => '1',
				),
			),
		)
	);
	if ( ! empty( $q_manual->posts ) ) {
		$ids = array_map( 'absint', $q_manual->posts );
	}
	wp_reset_postdata();

	$need = $count - count( $ids );
	if ( $need <= 0 ) {
		return array_slice( array_values( array_unique( array_filter( $ids ) ) ), 0, $count );
	}

	// 2) Sticky posts (not already included), preserve sticky_posts order.
	$sticky = get_option( 'sticky_posts' );
	if ( is_array( $sticky ) && ! empty( $sticky ) ) {
		$sticky = array_values(
			array_filter(
				array_map( 'absint', $sticky ),
				function ( $sid ) use ( $ids ) {
					return $sid > 0 && ! in_array( $sid, $ids, true );
				}
			)
		);
		if ( ! empty( $sticky ) ) {
			$q = new WP_Query(
				array(
					'post_type'           => 'post',
					'post_status'         => 'publish',
					'post__in'            => $sticky,
					'posts_per_page'      => $need,
					'orderby'             => 'post__in',
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
					'fields'              => 'ids',
				)
			);
			if ( ! empty( $q->posts ) ) {
				$ids = array_merge( $ids, array_map( 'absint', $q->posts ) );
			}
			wp_reset_postdata();
		}
	}

	return array_slice( array_values( array_unique( array_filter( $ids ) ) ), 0, $count );
}

/**
 * Default Inspector values for Blog featured / trending / latest title row styling.
 *
 * @return array<string, mixed>
 */
function zskeleton_blog_hub_default_heading_control_attrs(): array {
	return array(
		'showHeading'            => true,
		'titleDashicon'          => '',
		'titleShowSeparator'     => true,
		'titleSeparatorWidthPx'  => 72,
		'titleSeparatorHeightPx' => 4,
		'titleSeparatorRadiusPx' => 999,
		'titleSeparatorColor'    => '#b8d4eb',
		'titleListingGapPx'      => 20,
	);
}

/**
 * Merge saved block attrs with defaults for Dashicon accent bar + title/listing gap.
 *
 * @param array<string, mixed> $attributes Partial block attrs from parser.
 * @return array<string, mixed>
 */
function zskeleton_blog_hub_heading_attrs_merge( array $attributes ): array {
	$defaults = zskeleton_blog_hub_default_heading_control_attrs();
	$allowed  = array_keys( $defaults );
	$slice    = array();
	foreach ( $allowed as $key ) {
		if ( array_key_exists( $key, $attributes ) ) {
			$slice[ $key ] = $attributes[ $key ];
		}
	}
	$merged                         = wp_parse_args( $slice, $defaults );
	$merged['showHeading']          = isset( $merged['showHeading'] ) && $merged['showHeading'];
	$merged['titleShowSeparator']   = isset( $merged['titleShowSeparator'] ) && $merged['titleShowSeparator'];
	$merged['titleSeparatorWidthPx'] = min( 480, max( 4, (int) $merged['titleSeparatorWidthPx'] ) );
	$merged['titleSeparatorHeightPx'] = min( 64, max( 1, (int) $merged['titleSeparatorHeightPx'] ) );
	$merged['titleSeparatorRadiusPx'] = min( 999, max( 0, (int) $merged['titleSeparatorRadiusPx'] ) );
	$merged['titleListingGapPx']   = min( 200, max( 0, (int) $merged['titleListingGapPx'] ) );
	$merged['titleSeparatorColor'] = function_exists( 'zskeleton_block_heading_separator_hex' )
		? zskeleton_block_heading_separator_hex( $merged['titleSeparatorColor'], '#b8d4eb' )
		: '#b8d4eb';
	return $merged;
}

/**
 * Strip keys that are not passed into {@see zskeleton_render_block_heading_title_row()}.
 *
 * @param array<string, mixed> $merged Output of {@see zskeleton_blog_hub_heading_attrs_merge()}.
 * @return array<string, mixed>
 */
function zskeleton_blog_hub_heading_attrs_for_title_row( array $merged ): array {
	$out = $merged;
	unset( $out['showHeading'], $out['titleListingGapPx'] );
	return $out;
}

/**
 * Sanitize featured count (1–12).
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_blog_featured_count( $value ) {
	return (string) max( 1, min( 12, absint( $value ) ) );
}

/**
 * Sanitize blog hub category term count (how many top-level terms to list).
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_blog_category_blocks_count( $value ) {
	return (string) max( 1, min( 12, absint( $value ) ) );
}

/**
 * Term listing layout for blog hub categories: simple | icons | thumbnails.
 *
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_blog_category_terms_layout( $value ) {
	$v = is_string( $value ) ? $value : (string) $value;
	return in_array( $v, array( 'simple', 'icons', 'thumbnails' ), true ) ? $v : 'thumbnails';
}

/**
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_blog_trending_count( $value ) {
	return (string) max( 1, min( 12, absint( $value ) ) );
}

/**
 * @param mixed $value Raw.
 * @return string views|comments
 */
function zskeleton_sanitize_blog_trending_mode( $value ) {
	$v = is_string( $value ) ? $value : (string) $value;
	return in_array( $v, array( 'views', 'comments' ), true ) ? $v : 'comments';
}

/**
 * @param mixed $value Raw.
 * @return string
 */
function zskeleton_sanitize_blog_lead_button_url( $value ) {
	$url = esc_url_raw( trim( (string) $value ) );
	return $url ? $url : '';
}

/**
 * Paged value for blog listing pages (matches page-blog.php query vars).
 *
 * @return int>=1
 */
function zskeleton_blog_hub_get_listing_paged(): int {
	$paged = max( 1, (int) get_query_var( 'paged' ) );
	if ( $paged < 2 ) {
		$paged = max( 1, (int) get_query_var( 'page' ) );
	}
	return max( 1, $paged );
}

/**
 * Whether hub-only sections should render (first page of the listing).
 *
 * @return bool
 */
function zskeleton_blog_hub_is_first_listing_page(): bool {
	return zskeleton_blog_hub_get_listing_paged() < 2;
}

/**
 * Post IDs to omit from the “latest” grid when de-duplication is enabled.
 *
 * @return int[]
 */
function zskeleton_blog_hub_get_latest_exclude_featured_ids(): array {
	if ( ! zskeleton_blog_hub_is_first_listing_page() ) {
		return array();
	}
	if ( '1' !== (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_show_featured', '1' )
		|| '1' !== (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_exclude_featured_from_latest', '1' ) ) {
		return array();
	}
	$feat_n = (int) zskeleton_blog_hub_get_option( 'zskeleton_blog_featured_count', '3' );
	$feat_n = max( 1, min( 12, $feat_n ) );
	$ids    = zskeleton_blog_hub_get_featured_post_ids( $feat_n );
	return array_values( array_filter( array_map( 'absint', (array) $ids ) ) );
}

/**
 * Build query arguments for the main blog posts grid (template or block).
 *
 * @param array $overrides Optional keys: exclude_featured (bool|null), posts_per_page (int), paged (int).
 * @return array<string, mixed>
 */
function zskeleton_blog_hub_main_posts_query_args( array $overrides = array() ): array {
	$paged = isset( $overrides['paged'] ) ? max( 1, absint( $overrides['paged'] ) ) : zskeleton_blog_hub_get_listing_paged();

	$per_page = isset( $overrides['posts_per_page'] ) ? (int) $overrides['posts_per_page'] : 0;
	if ( $per_page < 1 ) {
		$per_page = (int) get_option( 'zskeleton_archive_posts_per_page', 0 );
	}
	if ( $per_page < 1 ) {
		$per_page = (int) get_option( 'posts_per_page', 10 );
	}
	$per_page = max( 1, min( 50, $per_page ) );

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $per_page,
		'paged'               => $paged,
		'ignore_sticky_posts' => false,
	);

	$exclude_featured = array_key_exists( 'exclude_featured', $overrides )
		? $overrides['exclude_featured']
		: null;

	if ( false === $exclude_featured ) {
		return apply_filters( 'zskeleton_blog_hub_main_posts_query_args', $args, $overrides );
	}

	if ( true === $exclude_featured || null === $exclude_featured ) {
		$ids = zskeleton_blog_hub_get_latest_exclude_featured_ids();
		if ( ! empty( $ids ) ) {
			$args['post__not_in'] = $ids;
		}
	}

	return apply_filters( 'zskeleton_blog_hub_main_posts_query_args', $args, $overrides );
}

/**
 * Main posts query for the blog listing template or blog posts grid block.
 *
 * @param array $overrides Passed to {@see zskeleton_blog_hub_main_posts_query_args()}.
 * @return WP_Query
 */
function zskeleton_blog_hub_main_posts_query( array $overrides = array() ): WP_Query {
	return new WP_Query( zskeleton_blog_hub_main_posts_query_args( $overrides ) );
}

/**
 * Related posts query for single post discovery blocks.
 *
 * @param int $post_id Source post ID.
 * @param int $limit   Max posts.
 * @return WP_Query
 */
function zskeleton_blog_hub_related_posts_query( int $post_id, int $limit = 3 ): WP_Query {
	$post_id = absint( $post_id );
	$limit   = max( 1, min( 6, absint( $limit ) ) );
	$terms   = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'ids' ) );
	$args    = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		$args['category__in'] = array_map( 'absint', $terms );
	}
	return new WP_Query( $args );
}

/**
 * Pagination markup for a secondary blog listing query on a static page.
 *
 * @param WP_Query $query    Custom query (not main query).
 * @param int      $page_id  Page permalink base.
 * @param int      $paged    Current page number.
 * @return string HTML (empty when not applicable).
 */
function zskeleton_blog_hub_pagination_html( WP_Query $query, int $page_id, int $paged ): string {
	if ( $query->max_num_pages < 2 || $page_id < 1 ) {
		return '';
	}
	$permalink = get_permalink( $page_id );
	if ( ! $permalink ) {
		return '';
	}
	if ( get_option( 'permalink_structure' ) ) {
		$pagination_base = user_trailingslashit( trailingslashit( $permalink ) . 'page/%#%/', 'paged' );
	} else {
		$pagination_base = esc_url_raw( add_query_arg( 'paged', '%#%', $permalink ) );
	}
	if ( '' === $pagination_base ) {
		return '';
	}
	$links = paginate_links(
		array(
			'base'      => $pagination_base,
			'format'    => '',
			'current'   => max( 1, $paged ),
			'total'     => (int) $query->max_num_pages,
			'mid_size'  => 2,
			'prev_text' => __( '&laquo; Previous', 'zskeleton' ),
			'next_text' => __( 'Next &raquo;', 'zskeleton' ),
		)
	);
	if ( ! $links ) {
		return '';
	}
	return '<nav class="pagination-wrapper" aria-label="' . esc_attr__( 'Posts navigation', 'zskeleton' ) . '">' . wp_kses_post( $links ) . '</nav>';
}

/**
 * Output top-level category term grid (blog hub), shared by the section template and the block editor block.
 *
 * @param array $args {
 *     Optional. @type string $layout                  simple|icons|thumbnails; uses theme option when omitted or invalid.
 *     @type int    $max_terms               Number of categories (1–12); uses theme option when <1.
 *     @type int[]  $include_term_ids        When non-empty, only these category term IDs (validated); results are alphabetical by name, capped by max_terms.
 *     @type string $heading                 Section title; default filtered “Browse by category”.
 *     @type string $heading_id              HTML id on heading (accessibility).
 *     @type bool   $only_first_listing_page When true (default), outputs nothing after page 1 of the listing.
 * }
 * @return void
 */
function zskeleton_blog_hub_render_category_terms_listing( array $args = array() ): void {
	$only_first = ! isset( $args['only_first_listing_page'] ) || $args['only_first_listing_page'];
	if ( $only_first && ! zskeleton_blog_hub_is_first_listing_page() ) {
		return;
	}

	$bypass_cat_visibility = ! empty( $args['bypass_theme_category_visibility'] );
	if ( ! $bypass_cat_visibility && '1' !== (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_show_category_blocks', '1' ) ) {
		return;
	}

	$max_cats = isset( $args['max_terms'] ) ? (int) $args['max_terms'] : 0;
	if ( $max_cats < 1 ) {
		$max_cats = (int) zskeleton_blog_hub_get_option( 'zskeleton_blog_category_blocks_count', '6' );
	}
	$max_cats = max( 1, min( 12, $max_cats ) );

	$layout = isset( $args['layout'] ) ? sanitize_key( (string) $args['layout'] ) : '';
	if ( '' === $layout || ! in_array( $layout, array( 'simple', 'icons', 'thumbnails' ), true ) ) {
		$layout = (string) zskeleton_blog_hub_get_option( 'zskeleton_blog_category_terms_layout', 'thumbnails' );
	}
	if ( ! in_array( $layout, array( 'simple', 'icons', 'thumbnails' ), true ) ) {
		$layout = 'thumbnails';
	}

	$include_ids = array();
	if ( ! empty( $args['include_term_ids'] ) && is_array( $args['include_term_ids'] ) ) {
		foreach ( $args['include_term_ids'] as $tid ) {
			$tid = absint( $tid );
			if ( $tid > 0 ) {
				$include_ids[] = $tid;
			}
		}
		$include_ids = array_values( array_unique( $include_ids ) );
	}

	if ( ! empty( $include_ids ) ) {
		$include_ids = array_values( array_unique( $include_ids ) );
		$terms       = get_terms(
			array(
				'taxonomy'   => 'category',
				'include'    => $include_ids,
				'hide_empty' => true,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'number'     => $max_cats,
			)
		);
	} else {
		$default_category_id = (int) get_option( 'default_category', 1 );
		$terms               = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => true,
				'exclude'    => $default_category_id > 0 ? array( $default_category_id ) : array(),
				'number'     => $max_cats,
				'orderby'    => 'count',
				'order'      => 'DESC',
				'parent'     => 0,
			)
		);
	}

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}

	$show_section_heading = ! isset( $args['show_section_heading'] ) || $args['show_section_heading'];
	$title                = '';
	if ( $show_section_heading ) {
		$title = isset( $args['heading'] ) && '' !== trim( (string) $args['heading'] )
			? (string) $args['heading']
			: apply_filters( 'zskeleton_blog_hub_categories_title', __( 'Browse by category', 'zskeleton' ) );
	}
	$heading_id = isset( $args['heading_id'] ) && '' !== trim( (string) $args['heading_id'] )
		? sanitize_html_class( (string) $args['heading_id'] )
		: 'blog-hub-categories-heading';

	get_template_part(
		'template-parts/taxonomy/term',
		'listing',
		array(
			'terms'         => $terms,
			'taxonomy'      => 'category',
			'style'         => $layout,
			'heading'       => $title,
			'heading_id'    => $heading_id,
			'heading_tag'   => 'h2',
			'show_count'    => true,
			'section_class' => 'blog-hub-categories',
		)
	);
}

/**
 * Remember dynamic blog hub HTML before `render_block` filters (editor + frontend).
 *
 * @param string $block_name Full block name, e.g. zskeleton/blog-featured or zskeleton/seo-ar-ai-lead.
 * @param string $html       Final string returned from the render callback.
 * @return void
 */
function zskeleton_blog_hub_stash_dynamic_blog_block_html( $block_name, $html ) {
	if ( ! is_string( $block_name ) || ! is_string( $html ) || strlen( $html ) < 1 ) {
		return;
	}
	if ( 0 !== strpos( $block_name, 'zskeleton/blog-' ) && 'zskeleton/seo-ar-ai-lead' !== $block_name ) {
		return;
	}
	if ( ! isset( $GLOBALS['zskeleton_blog_hub_dynamic_block_stash'] ) || ! is_array( $GLOBALS['zskeleton_blog_hub_dynamic_block_stash'] ) ) {
		$GLOBALS['zskeleton_blog_hub_dynamic_block_stash'] = array();
	}
	$GLOBALS['zskeleton_blog_hub_dynamic_block_stash'][ $block_name ] = $html;
}

/**
 * Restore output after `render_block_{$name}` if generic filters cleared it (WP 6.9+ visibility, etc.).
 *
 * @param string         $block_content Block output.
 * @param array          $parsed_block  Parsed block.
 * @param \WP_Block|null $instance      Block instance.
 * @return string
 */
function zskeleton_blog_hub_restore_dynamic_blog_block_stash( $block_content, $parsed_block, $instance ) {
	$name = '';
	if ( $instance instanceof WP_Block ) {
		$name = (string) $instance->name;
	} elseif ( is_array( $parsed_block ) && ! empty( $parsed_block['blockName'] ) ) {
		$name = (string) $parsed_block['blockName'];
	}
	if ( '' === $name || ( 0 !== strpos( $name, 'zskeleton/blog-' ) && 'zskeleton/seo-ar-ai-lead' !== $name ) ) {
		return $block_content;
	}
	$stash_map = isset( $GLOBALS['zskeleton_blog_hub_dynamic_block_stash'] ) && is_array( $GLOBALS['zskeleton_blog_hub_dynamic_block_stash'] )
		? $GLOBALS['zskeleton_blog_hub_dynamic_block_stash']
		: array();
	if ( '' !== trim( (string) $block_content ) ) {
		unset( $stash_map[ $name ] );
		$GLOBALS['zskeleton_blog_hub_dynamic_block_stash'] = $stash_map;
		return $block_content;
	}
	if ( ! empty( $stash_map[ $name ] ) && is_string( $stash_map[ $name ] ) && strlen( $stash_map[ $name ] ) > 0 ) {
		$out = (string) $stash_map[ $name ];
		unset( $stash_map[ $name ] );
		$GLOBALS['zskeleton_blog_hub_dynamic_block_stash'] = $stash_map;
		return $out;
	}
	return $block_content;
}

/**
 * Register per-block `render_block_{name}` restore hooks for all ZSkeleton blog hub dynamic blocks.
 *
 * @return void
 */
function zskeleton_blog_hub_register_dynamic_block_stash_restore_filters(): void {
	$blocks = array(
		'zskeleton/blog-posts-grid',
		'zskeleton/blog-featured',
		'zskeleton/blog-trending',
		'zskeleton/blog-category-terms',
		'zskeleton/blog-lead-gen',
		'zskeleton/seo-ar-ai-lead',
	);
	foreach ( $blocks as $block_name ) {
		add_filter( 'render_block_' . $block_name, 'zskeleton_blog_hub_restore_dynamic_blog_block_stash', 999998, 3 );
	}
}
add_action( 'init', 'zskeleton_blog_hub_register_dynamic_block_stash_restore_filters', 5 );

/**
 * Dynamic ZSkeleton blog hub block names stored in post content.
 *
 * @return string[]
 */
function zskeleton_blog_hub_dynamic_block_names(): array {
	return array(
		'zskeleton/blog-category-terms',
		'zskeleton/blog-featured',
		'zskeleton/blog-posts-grid',
		'zskeleton/blog-trending',
		'zskeleton/blog-lead-gen',
	);
}

/**
 * Whether post content includes any blog hub block (for front-end CSS scope).
 *
 * @param WP_Post|object|null $post Post object.
 * @return bool
 */
function zskeleton_post_content_has_blog_hub_blocks( $post ): bool {
	if ( ! $post instanceof WP_Post || '' === trim( (string) $post->post_content ) ) {
		return false;
	}
	foreach ( zskeleton_blog_hub_dynamic_block_names() as $block_name ) {
		if ( function_exists( 'has_block' ) && has_block( $block_name, $post ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Whether `blog-page.css` should load on this front request.
 *
 * @return bool
 */
function zskeleton_should_enqueue_blog_hub_page_styles(): bool {
	if ( function_exists( 'zskeleton_is_blog_listing_public_view' ) && zskeleton_is_blog_listing_public_view() ) {
		return true;
	}
	if ( is_singular() ) {
		$obj = get_queried_object();
		if ( $obj instanceof WP_Post && zskeleton_post_content_has_blog_hub_blocks( $obj ) ) {
			return true;
		}
	}
	return false;
}
