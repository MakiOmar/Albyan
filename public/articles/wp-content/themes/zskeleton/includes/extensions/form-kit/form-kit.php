<?php
/**
 * ZSkeleton Form Kit — declarative forms, sanitize/validate, AJAX, wizards.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

define( 'ZSkeleton_FORM_KIT_VERSION', '1.0.0' );

require_once __DIR__ . '/class-form-definition.php';
require_once __DIR__ . '/class-form-field-types.php';
require_once __DIR__ . '/class-field-registry.php';
require_once __DIR__ . '/class-form-handler.php';
require_once __DIR__ . '/class-form-assets.php';
require_once __DIR__ . '/class-form-renderer.php';
require_once __DIR__ . '/class-form-ajax.php';
require_once __DIR__ . '/class-form-kit-demo.php';

/**
 * Bootstrap Form Kit.
 */
function zskeleton_form_kit_bootstrap() {
	ZSkeleton_Field_Registry::instance()->load_types();
	ZSkeleton_Form_Assets::init();
	new ZSkeleton_Form_Ajax();
	new ZSkeleton_Form_Kit_Demo();
}
add_action( 'after_setup_theme', 'zskeleton_form_kit_bootstrap', 15 );

/**
 * Output a registered Form Kit form (escaped HTML).
 *
 * @param string              $form_id Form id (key in zskeleton_form_kit_forms).
 * @param array<string,mixed> $values  Default values keyed by field name.
 * @return void
 */
function zskeleton_render_form( $form_id, array $values = array() ) {
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ZSkeleton_Form_Renderer::render escapes output.
	echo ZSkeleton_Form_Renderer::render( $form_id, $values );
}
