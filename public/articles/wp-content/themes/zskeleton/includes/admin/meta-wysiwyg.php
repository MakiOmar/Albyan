<?php
/**
 * Shared WordPress post editor (TinyMCE) helpers for theme meta boxes and admin screens.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a field config uses the visual editor for a textarea.
 *
 * @param array<string,mixed> $conf Field definition (type + optional `editor`).
 * @return bool
 */
function zskeleton_field_config_uses_wysiwyg( array $conf ) {
	if ( ! isset( $conf['type'] ) || 'textarea' !== $conf['type'] ) {
		return false;
	}
	$mode = isset( $conf['editor'] ) ? (string) $conf['editor'] : 'wysiwyg';
	return 'textarea' !== $mode;
}

/**
 * Default wp_editor() arguments for theme meta (teeny, no media).
 *
 * @param array<string,mixed> $overrides Merge into defaults.
 * @return array<string,mixed>
 */
function zskeleton_get_meta_wysiwyg_editor_args( array $overrides = array() ) {
	$defaults = array(
		'media_buttons' => false,
		'teeny'         => true,
		'quicktags'     => true,
		'textarea_rows' => 6,
	);
	return array_merge( $defaults, $overrides );
}

/**
 * Output a TinyMCE instance for post meta (or similar) screens.
 *
 * @param string               $editor_id     HTML id (use letters, numbers, underscores only).
 * @param string               $textarea_name POST input name.
 * @param string               $content       Initial HTML/text.
 * @param array<string,mixed>  $args {
 *     Optional. Merged into wp_editor settings.
 *
 *     @type array<string,mixed> $editor Merged into zskeleton_get_meta_wysiwyg_editor_args().
 * }
 */
function zskeleton_render_meta_wysiwyg( $editor_id, $textarea_name, $content, array $args = array() ) {
	$editor_id = preg_replace( '/[^a-zA-Z0-9_]/', '_', (string) $editor_id );
	$editor_in = isset( $args['editor'] ) && is_array( $args['editor'] ) ? $args['editor'] : array();
	$settings  = zskeleton_get_meta_wysiwyg_editor_args( $editor_in );
	$settings['textarea_name'] = $textarea_name;
	if ( isset( $args['textarea_rows'] ) ) {
		$settings['textarea_rows'] = (int) $args['textarea_rows'];
	}
	wp_editor( (string) $content, $editor_id, $settings );
}

/**
 * Enqueue thin admin CSS for stacked editors in meta boxes.
 *
 * @param string $hook_suffix Current screen hook.
 */
function zskeleton_meta_wysiwyg_admin_assets( $hook_suffix ) {
	$extra_hooks = array(
		'zskeleton-memberships_page_zskeleton-content-restrictions',
	);

	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) && ! in_array( $hook_suffix, $extra_hooks, true ) ) {
		return;
	}

	wp_register_style( 'zskeleton-meta-wysiwyg', false, array(), ZSkeleton_VERSION );
	wp_enqueue_style( 'zskeleton-meta-wysiwyg' );
	wp_add_inline_style(
		'zskeleton-meta-wysiwyg',
		'.zs-meta-field .wp-editor-wrap, .zskeleton-seo-field .wp-editor-wrap { margin-top: 4px; max-width: 100%; }'
	);
}
add_action( 'admin_enqueue_scripts', 'zskeleton_meta_wysiwyg_admin_assets', 5 );
