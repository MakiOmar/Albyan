<?php
/**
 * Dynamic block: Theme slider selector.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Slider picker data for the block editor (Theme: Slider uses the same handle as blog hub blocks).
 *
 * @return void
 */
function zskeleton_slider_block_inline_data_on_blog_hub_editor_script(): void {
	if ( ! wp_script_is( 'zskeleton-blog-hub-blocks-editor', 'registered' ) ) {
		return;
	}

	$sliders = get_posts(
		array(
			'post_type'      => 'zskeleton_slider',
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);

	$options = array();
	foreach ( $sliders as $slider_id ) {
		$slider_id = (int) $slider_id;
		if ( $slider_id < 1 ) {
			continue;
		}
		$title = trim( (string) get_the_title( $slider_id ) );
		if ( '' === $title ) {
			$title = sprintf(
				/* translators: %d: slider post ID */
				__( 'Slider #%d', 'zskeleton' ),
				$slider_id
			);
		}
		$options[] = array(
			'value' => (string) $slider_id,
			'label' => $title,
		);
	}

	wp_add_inline_script(
		'zskeleton-blog-hub-blocks-editor',
		'window.zskeletonSliderBlockData = ' . wp_json_encode(
			array(
				'sliders' => $options,
			)
		) . ';',
		'before'
	);
}
add_action( 'init', 'zskeleton_slider_block_inline_data_on_blog_hub_editor_script', 10 );

/**
 * Load slider CSS into the block editor canvas (editor-styles-wrapper).
 * Complements block.json `editorStyle` and {@see ZSkeleton_Slider_Frontend::enqueue_assets()} for scripts.
 *
 * @return void
 */
function zskeleton_slider_block_add_editor_style(): void {
	if ( ! current_theme_supports( 'editor-styles' ) ) {
		return;
	}
	$base    = trailingslashit( get_template_directory() );
	$use_min = (bool) get_option( 'zskeleton_use_minified_assets', true );
	$rel     = ( $use_min && is_readable( $base . 'assets/css/slider.min.css' ) )
		? 'assets/css/slider.min.css'
		: 'assets/css/slider.css';
	if ( ! is_readable( $base . $rel ) ) {
		$rel = 'assets/css/slider.css';
		if ( ! is_readable( $base . $rel ) ) {
			return;
		}
	}
	add_editor_style( $rel );
}
add_action( 'after_setup_theme', 'zskeleton_slider_block_add_editor_style', 25 );

/**
 * Load slider CSS/JS in the block editor so ServerSideRender previews and saved blocks work.
 * (Front output also calls {@see ZSkeleton_Slider_Frontend::enqueue_assets()} during render.)
 *
 * @return void
 */
function zskeleton_slider_block_enqueue_editor_assets(): void {
	if ( ! class_exists( 'ZSkeleton_Slider_Frontend' ) ) {
		return;
	}
	ZSkeleton_Slider_Frontend::enqueue_assets();
}
add_action( 'enqueue_block_editor_assets', 'zskeleton_slider_block_enqueue_editor_assets', 12 );

/**
 * Register the dynamic theme slider block.
 *
 * @return void
 */
function zskeleton_register_slider_block(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$path = trailingslashit( get_template_directory() ) . 'blocks/slider-block';
	$json = $path . '/block.json';
	if ( ! is_readable( $json ) ) {
		return;
	}

	register_block_type(
		$path,
		array(
			'category' => 'theme',
		)
	);
}
add_action( 'init', 'zskeleton_register_slider_block', 9 );
