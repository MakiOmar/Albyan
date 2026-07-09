<?php
/**
 * Shortcode embedding for Form Kit forms.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * [zskeleton_form id="my-form"]
 */
class ZSkeleton_Form_Shortcode {

	/**
	 * Register shortcode.
	 */
	public static function init() {
		add_shortcode( 'zskeleton_form', array( __CLASS__, 'render' ) );
	}

	/**
	 * @param array<string,string> $atts Attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => '',
			),
			$atts,
			'zskeleton_form'
		);

		$form_id = sanitize_key( (string) $atts['id'] );
		if ( '' === $form_id ) {
			return '';
		}

		if ( ! class_exists( 'ZSkeleton_Form_Definition' ) || ! ZSkeleton_Form_Definition::get( $form_id ) ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				$detail = self::get_admin_detail_message( $form_id );
				return '<p class="zs-form-shortcode-missing"><strong>' . esc_html__( 'Form not found.', 'zskeleton' ) . '</strong> ' . esc_html( $detail ) . '</p>';
			}
			return '';
		}

		ob_start();
		zskeleton_render_form( $form_id );
		return (string) ob_get_clean();
	}

	/**
	 * Explain why a form id failed to resolve (visible to admins only).
	 *
	 * @param string $form_id Requested id.
	 * @return string
	 */
	private static function get_admin_detail_message( $form_id ) {
		if ( ! class_exists( 'ZSkeleton_Form_Registry_Loader' ) ) {
			return __( 'Form Kit is not loaded. Confirm the zskeleton theme is active on this site.', 'zskeleton' );
		}

		$post = ZSkeleton_Form_Registry_Loader::find_ui_form_post( $form_id );
		if ( ! $post instanceof WP_Post ) {
			return sprintf(
				/* translators: %s: form id from shortcode */
				__( 'No form matches id “%s”. Copy the shortcode from the Forms list in wp-admin.', 'zskeleton' ),
				$form_id
			);
		}

		if ( 'publish' !== $post->post_status ) {
			return sprintf(
				/* translators: %s: form title */
				__( 'The form “%s” exists but is not published yet. Publish it, then try again.', 'zskeleton' ),
				$post->post_title
			);
		}

		$schema = ZSkeleton_Forms_CPT::get_schema( $post->ID );
		if (
			empty( $schema['layout_tree'] )
			&& empty( $schema['fields'] )
			&& empty( $schema['steps'] )
		) {
			return sprintf(
				/* translators: %s: form title */
				__( 'The form “%s” has no saved fields. Edit it in the builder, add fields, and click Update.', 'zskeleton' ),
				$post->post_title
			);
		}

		$resolved = ZSkeleton_Forms_CPT::get_form_id_for_post( $post->ID );
		if ( $resolved && $resolved !== $form_id ) {
			return sprintf(
				/* translators: 1: shortcode id, 2: correct id */
				__( 'Try [zskeleton_form id="%2$s"] instead of id="%1$s".', 'zskeleton' ),
				$form_id,
				$resolved
			);
		}

		return __( 'Re-save the form in wp-admin or clear the site object cache, then reload this page.', 'zskeleton' );
	}
}
