<?php
/**
 * Block editor: ZSkeleton blog hub sections (dynamic blocks + starter pattern).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Short-circuit WP 6.9+ block visibility for ZSkeleton blog hub dynamic blocks.
 *
 * Core `wp_render_block_visibility_support` can return an empty string when `metadata.blockVisibility`
 * is false, after the dynamic `render_callback` already ran (REST block preview then looks “empty”).
 * `block_has_support( $type, 'visibility', true )` also treats a null `supports` object as enabled.
 *
 * This file loads after `wp-settings.php` pulled in `block-supports/block-visibility.php`, so we can
 * replace the core filter and delegate for all other blocks.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block (blockName, attrs, …).
 * @return string
 */
function zskeleton_blog_hub_render_block_visibility_compat( $block_content, $block ) {
	if ( ! empty( $block['blockName'] ) && is_string( $block['blockName'] ) && 0 === strpos( $block['blockName'], 'zskeleton/blog-' ) ) {
		return $block_content;
	}
	return wp_render_block_visibility_support( $block_content, $block );
}

/**
 * Replace core block visibility on `init` so any later `add_filter` calls cannot win over the theme.
 *
 * @return void
 */
function zskeleton_blog_hub_replace_block_visibility_filter(): void {
	if ( ! function_exists( 'wp_render_block_visibility_support' ) ) {
		return;
	}
	remove_filter( 'render_block', 'wp_render_block_visibility_support', 10 );
	remove_filter( 'render_block', 'zskeleton_blog_hub_render_block_visibility_compat', 10 );
	add_filter( 'render_block', 'zskeleton_blog_hub_render_block_visibility_compat', 10, 2 );
}
add_action( 'init', 'zskeleton_blog_hub_replace_block_visibility_filter', 1000 );

/**
 * Block inserter category for blog hub blocks (core API: block_categories_all).
 *
 * @param array[]                      $categories              Default categories.
 * @param WP_Block_Editor_Context|null $block_editor_context Editor context (unused).
 * @return array[]
 */
function zskeleton_blog_hub_block_categories_all( array $categories, $block_editor_context = null ): array {
	unset( $block_editor_context );
	foreach ( $categories as $cat ) {
		if ( isset( $cat['slug'] ) && 'zskeleton-blog' === $cat['slug'] ) {
			return $categories;
		}
	}
	$categories[] = array(
		'slug'  => 'zskeleton-blog',
		'title' => __( 'ZSkeleton blog', 'zskeleton' ),
		'icon'  => null,
	);
	return $categories;
}
add_filter( 'block_categories_all', 'zskeleton_blog_hub_block_categories_all', 5, 2 );

/**
 * Pattern library category for ZSkeleton blog layouts.
 * Priority 1 so it exists before theme patterns register on init (default 10).
 *
 * @return void
 */
function zskeleton_register_blog_hub_pattern_category(): void {
	if ( ! function_exists( 'register_block_pattern_category' ) ) {
		return;
	}
	register_block_pattern_category(
		'zskeleton-blog',
		array(
			'label'       => __( 'ZSkeleton blog', 'zskeleton' ),
			'description' => __( 'Patterns for the blog listing (block editor) template.', 'zskeleton' ),
		)
	);
}
add_action( 'init', 'zskeleton_register_blog_hub_pattern_category', 1 );

/**
 * If the theme scanner did not register the blog listing pattern (cache, path, or child setup), register it from the parent template.
 *
 * @return void
 */
function zskeleton_register_blog_listing_pattern_fallback(): void {
	if ( ! function_exists( 'register_block_pattern' ) || ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
		return;
	}
	$registry = WP_Block_Patterns_Registry::get_instance();
	if ( $registry->is_registered( 'zskeleton/blog-listing-blocks' ) ) {
		return;
	}
	$file = trailingslashit( get_template_directory() ) . 'patterns/blog-listing-blocks.php';
	if ( ! is_readable( $file ) ) {
		return;
	}
	register_block_pattern(
		'zskeleton/blog-listing-blocks',
		array(
			'title'       => __( 'ZSkeleton blog — listing (blocks)', 'zskeleton' ),
			'description' => __( 'ZSkeleton blog layout: featured strip, latest posts grid, trending, categories, and lead CTA.', 'zskeleton' ),
			'categories'  => array( 'zskeleton-blog', 'query' ),
			'keywords'    => array( 'ZSkeleton blog', 'zskeleton blog', 'zskeleton', 'blog listing', 'listing blocks', 'blog hub' ),
			'inserter'    => true,
			'filePath'    => $file,
		)
	);
}
add_action( 'init', 'zskeleton_register_blog_listing_pattern_fallback', 15 );

/**
 * Load blog listing + component styles in the block editor so hub blocks match the public blog listing.
 *
 * The front only enqueues `blog-page.css` on the blog listing view; the editor has no `body.zskeleton-blog-listing`
 * and needs the same rules under `.editor-styles-wrapper` (see `blog-page.css` and `blog-hub-block-editor.css`).
 *
 * @return void
 */
function zskeleton_blog_hub_register_block_editor_styles(): void {
	if ( ! current_theme_supports( 'editor-styles' ) ) {
		return;
	}
	$base = trailingslashit( get_template_directory() );
	$use_min = (bool) get_option( 'zskeleton_use_minified_assets', true );

	$comp = $use_min && is_readable( $base . 'assets/css/components.min.css' )
		? 'assets/css/components.min.css'
		: 'assets/css/components.css';
	if ( ! is_readable( $base . $comp ) ) {
		$comp = 'assets/css/components.css';
	}

	$blog = $use_min && is_readable( $base . 'assets/css/blog-page.min.css' )
		? 'assets/css/blog-page.min.css'
		: 'assets/css/blog-page.css';
	if ( ! is_readable( $base . $blog ) ) {
		$blog = 'assets/css/blog-page.css';
	}
	if ( ! is_readable( $base . $blog ) ) {
		return;
	}

	$hero = $use_min && is_readable( $base . 'assets/css/blog-listing-hero.min.css' )
		? 'assets/css/blog-listing-hero.min.css'
		: 'assets/css/blog-listing-hero.css';
	if ( ! is_readable( $base . $hero ) ) {
		$hero = '';
	}

	$editor_patch = $use_min && is_readable( $base . 'assets/css/blog-hub-block-editor.min.css' )
		? 'assets/css/blog-hub-block-editor.min.css'
		: 'assets/css/blog-hub-block-editor.css';
	if ( ! is_readable( $base . $editor_patch ) ) {
		$editor_patch = '';
	}

	$queue = array( $comp );
	if ( $hero ) {
		$queue[] = $hero;
	}
	$queue[] = $blog;
	if ( $editor_patch ) {
		$queue[] = $editor_patch;
	}
	add_editor_style( $queue );
}
add_action( 'after_setup_theme', 'zskeleton_blog_hub_register_block_editor_styles', 20 );

/**
 * Editor script for block client registration, inspector controls, and ServerSideRender (shared by blog hub blocks).
 *
 * @return void
 */
function zskeleton_blog_hub_blocks_register_editor_script(): void {
	$path = trailingslashit( get_template_directory() ) . 'blocks/blog-hub-blocks-editor.js';
	if ( ! is_readable( $path ) ) {
		return;
	}
	wp_register_script(
		'zskeleton-blog-hub-blocks-editor',
		trailingslashit( get_template_directory_uri() ) . 'blocks/blog-hub-blocks-editor.js',
		array(
			'wp-hooks',
			'wp-element',
			'wp-i18n',
			'wp-block-editor',
			'wp-components',
			'wp-blocks',
			'wp-data',
			'wp-core-data',
			'wp-api-fetch',
			'wp-server-side-render',
		),
		(string) filemtime( $path ),
		true
	);
}
add_action( 'init', 'zskeleton_blog_hub_blocks_register_editor_script', 8 );

/**
 * Register dynamic blog blocks (block.json + render.php under /blocks).
 *
 * @return void
 */
function zskeleton_register_blog_hub_blocks(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	$base  = trailingslashit( get_template_directory() ) . 'blocks/';
	$slugs = array(
		'blog-featured',
		'blog-posts-grid',
		'blog-trending',
		'blog-category-terms',
		'blog-lead-gen',
	);
	foreach ( $slugs as $slug ) {
		$path = $base . $slug;
		$json = $path . '/block.json';
		if ( ! is_readable( $json ) ) {
			continue;
		}
		// editorScript is in each block.json; force inserter category so deploy/OPcache drift cannot leave blocks on "theme".
		register_block_type(
			$path,
			array(
				'category' => 'zskeleton-blog',
			)
		);
	}
}
add_action( 'init', 'zskeleton_register_blog_hub_blocks', 9 );
