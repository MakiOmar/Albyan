<?php
/**
 * Gutenberg block: Arabic SEO homepage AI lead section.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

require_once trailingslashit( get_template_directory() ) . 'includes/blocks/seo-ar-ai-lead-defaults.php';

/**
 * Block inserter category for SEO / landing blocks.
 *
 * @param array[]                      $categories              Categories.
 * @param WP_Block_Editor_Context|null $block_editor_context Editor context.
 * @return array[]
 */
function zskeleton_seo_ar_block_categories_all( array $categories, $block_editor_context = null ): array {
	unset( $block_editor_context );
	foreach ( $categories as $cat ) {
		if ( isset( $cat['slug'] ) && 'zskeleton-seo' === $cat['slug'] ) {
			return $categories;
		}
	}
	$categories[] = array(
		'slug'  => 'zskeleton-seo',
		'title' => __( 'ZSkeleton SEO', 'zskeleton' ),
		'icon'  => null,
	);
	return $categories;
}
add_filter( 'block_categories_all', 'zskeleton_seo_ar_block_categories_all', 5, 2 );

/**
 * Register editor script for the SEO AR AI lead block.
 *
 * @return void
 */
function zskeleton_seo_ar_ai_lead_block_register_editor_script(): void {
	$path = trailingslashit( get_template_directory() ) . 'blocks/seo-ar-ai-lead/editor.js';
	if ( ! is_readable( $path ) ) {
		return;
	}

	wp_register_script(
		'zskeleton-seo-ar-ai-lead-block-editor',
		trailingslashit( get_template_directory_uri() ) . 'blocks/seo-ar-ai-lead/editor.js',
		array(
			'wp-blocks',
			'wp-element',
			'wp-i18n',
			'wp-components',
			'wp-block-editor',
			'wp-server-side-render',
		),
		(string) filemtime( $path ),
		true
	);
}
add_action( 'init', 'zskeleton_seo_ar_ai_lead_block_register_editor_script', 8 );

/**
 * Register dynamic block (block.json + render.php).
 *
 * @return void
 */
function zskeleton_register_seo_ar_ai_lead_block(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$path = trailingslashit( get_template_directory() ) . 'blocks/seo-ar-ai-lead';
	$json = $path . '/block.json';
	if ( ! is_readable( $json ) ) {
		return;
	}

	register_block_type(
		$path,
		array(
			'category' => 'zskeleton-seo',
		)
	);
}
add_action( 'init', 'zskeleton_register_seo_ar_ai_lead_block', 9 );

/**
 * Find first occurrence of the AI lead block in parsed blocks (including inner blocks).
 *
 * @param array[] $blocks Parsed blocks.
 * @return array<string, mixed>|null
 */
function zskeleton_seo_ar_ai_lead_find_block_recursive( array $blocks ): ?array {
	foreach ( $blocks as $b ) {
		if ( ! empty( $b['blockName'] ) && 'zskeleton/seo-ar-ai-lead' === $b['blockName'] ) {
			return $b;
		}
		if ( ! empty( $b['innerBlocks'] ) && is_array( $b['innerBlocks'] ) ) {
			$inner = zskeleton_seo_ar_ai_lead_find_block_recursive( $b['innerBlocks'] );
			if ( null !== $inner ) {
				return $inner;
			}
		}
	}
	return null;
}

/**
 * Whether the given post content includes the AI lead block.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function zskeleton_seo_ar_page_has_ai_lead_block( int $post_id ): bool {
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return function_exists( 'has_block' ) && has_block( 'zskeleton/seo-ar-ai-lead', $post );
}

/**
 * Render the AI lead block from page content if present; otherwise return false.
 *
 * @param int   $post_id Queried page ID.
 * @param array $args    Unused (kept for callers such as the homepage template).
 * @return bool True when the block was rendered.
 */
function zskeleton_seo_ar_try_render_ai_lead_block_from_page( int $post_id, array $args = array() ): bool {
	unset( $args );
	if ( $post_id <= 0 ) {
		return false;
	}
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || ! function_exists( 'has_block' ) || ! has_block( 'zskeleton/seo-ar-ai-lead', $post ) ) {
		return false;
	}
	$parsed = parse_blocks( (string) $post->post_content );
	$found  = zskeleton_seo_ar_ai_lead_find_block_recursive( $parsed );
	if ( null === $found ) {
		return false;
	}
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render_block returns escaped markup.
	echo render_block( $found );
	return true;
}

/**
 * Register starter pattern for the Arabic SEO AI lead block.
 *
 * @return void
 */
function zskeleton_register_seo_ar_ai_lead_pattern(): void {
	if ( ! function_exists( 'register_block_pattern' ) || ! class_exists( 'WP_Block_Patterns_Registry' ) ) {
		return;
	}
	$registry = WP_Block_Patterns_Registry::get_instance();
	if ( $registry->is_registered( 'zskeleton/seo-ar-ai-lead-default' ) ) {
		return;
	}
	register_block_pattern(
		'zskeleton/seo-ar-ai-lead-default',
		array(
			'title'       => __( 'ZSkeleton — Arabic SEO AI lead section', 'zskeleton' ),
			'description' => __( 'Title (optional icon + separator), rich body, and lead form column for the Arabic SEO homepage (default Arabic copy).', 'zskeleton' ),
			'categories'  => array( 'zskeleton-seo' ),
			'keywords'    => array( 'seo', 'arabic', 'lead', 'ai', 'zskeleton' ),
			'content'     => '<!-- wp:zskeleton/seo-ar-ai-lead /-->',
			'inserter'    => true,
		)
	);
}
add_action( 'init', 'zskeleton_register_seo_ar_ai_lead_pattern', 15 );
