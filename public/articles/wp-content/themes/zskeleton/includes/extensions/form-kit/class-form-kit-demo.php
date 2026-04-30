<?php
/**
 * Admin demo page + sample form registrations (reference for kits).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers demo forms and an Appearance submenu (manage_options only).
 */
class ZSkeleton_Form_Kit_Demo {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_filter( 'zskeleton_form_kit_forms', array( $this, 'register_forms' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( $this, 'demo_notice' ) );
	}

	/**
	 * Submenu under Appearance.
	 */
	public function register_menu() {
		add_theme_page(
			__( 'Form Kit Demo', 'zskeleton' ),
			__( 'Form Kit Demo', 'zskeleton' ),
			'manage_options',
			'zskeleton-form-kit-demo',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Enqueue kit assets on the demo screen (early enough for wp_editor).
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( 'appearance_page_zskeleton-form-kit-demo' !== $hook_suffix ) {
			return;
		}
		ZSkeleton_Form_Assets::request_enqueue( 'admin' );
		ZSkeleton_Form_Assets::enqueue_admin_screen( $hook_suffix );
	}

	/**
	 * Show last demo submission.
	 */
	public function demo_notice() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'appearance_page_zskeleton-form-kit-demo' !== $screen->id ) {
			return;
		}
		$last = get_transient( 'zskeleton_form_kit_demo_last' );
		if ( ! is_array( $last ) || empty( $last ) ) {
			return;
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( __( 'Demo form saved. Sanitized payload:', 'zskeleton' ) ) . '</p><pre style="overflow:auto;max-height:200px;">' . esc_html( wp_json_encode( $last, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ) . '</pre></div>';
		delete_transient( 'zskeleton_form_kit_demo_last' );
	}

	/**
	 * @param array $forms Forms.
	 * @return array
	 */
	public function register_forms( $forms ) {
		if ( ! is_array( $forms ) ) {
			$forms = array();
		}

		$forms['zskeleton_demo_simple'] = array(
			'context'                 => 'admin',
			'capability'              => 'manage_options',
			'allow_public_submission' => false,
			'use_ajax'                => true,
			'nonce_action'            => 'zskeleton_form_demo_simple',
			'fields'                  => array(
				array(
					'name'        => 'demo_name',
					'type'        => 'text',
					'label'       => __( 'Name', 'zskeleton' ),
					'required'    => true,
					'placeholder' => __( 'Your name', 'zskeleton' ),
				),
				array(
					'name'     => 'demo_email',
					'type'     => 'email',
					'label'    => __( 'Email', 'zskeleton' ),
					'required' => true,
				),
				array(
					'name'     => 'demo_plan',
					'type'     => 'select',
					'label'    => __( 'Plan', 'zskeleton' ),
					'required' => true,
					'choices'  => array(
						'starter' => __( 'Starter', 'zskeleton' ),
						'pro'     => __( 'Pro', 'zskeleton' ),
					),
				),
				array(
					'name'  => 'demo_notes',
					'type'  => 'textarea',
					'label' => __( 'Notes', 'zskeleton' ),
					'rows'  => 3,
				),
				array(
					'name'  => 'demo_toggle',
					'type'  => 'toggle',
					'label' => __( 'Enable notifications', 'zskeleton' ),
				),
			),
			'on_submit'               => array( $this, 'handle_demo_simple_submit' ),
		);

		$forms['zskeleton_demo_wizard'] = array(
			'context'                 => 'admin',
			'capability'              => 'manage_options',
			'allow_public_submission' => false,
			'use_ajax'                => true,
			'nonce_action'            => 'zskeleton_form_demo_wizard',
			'steps'                   => array(
				array(
					'id'    => 'identity',
					'title' => __( 'Identity', 'zskeleton' ),
					'fields' => array(
						array(
							'name'     => 'wiz_org',
							'type'     => 'text',
							'label'    => __( 'Organization', 'zskeleton' ),
							'required' => true,
						),
						array(
							'name'  => 'wiz_site',
							'type'  => 'url',
							'label' => __( 'Website', 'zskeleton' ),
						),
					),
				),
				array(
					'id'    => 'preferences',
					'title' => __( 'Preferences', 'zskeleton' ),
					'fields' => array(
						array(
							'name'    => 'wiz_volume',
							'type'    => 'range',
							'label'   => __( 'Volume', 'zskeleton' ),
							'rules'   => array(
								'min' => 0,
								'max' => 100,
							),
							'default' => 40,
						),
						array(
							'name'    => 'wiz_color',
							'type'    => 'color',
							'label'   => __( 'Accent', 'zskeleton' ),
							'default' => '#2271b1',
						),
					),
				),
			),
			'on_submit'               => array( $this, 'handle_demo_wizard_submit' ),
		);

		return $forms;
	}

	/**
	 * @param array                       $sanitized Sanitized data.
	 * @param ZSkeleton_Form_Definition $def       Definition.
	 * @return true|WP_Error
	 */
	public function handle_demo_simple_submit( $sanitized, $def ) {
		set_transient( 'zskeleton_form_kit_demo_last', $sanitized, 2 * MINUTE_IN_SECONDS );
		return true;
	}

	/**
	 * @param array                       $sanitized Sanitized data.
	 * @param ZSkeleton_Form_Definition $def       Definition.
	 * @return true|WP_Error
	 */
	public function handle_demo_wizard_submit( $sanitized, $def ) {
		set_transient( 'zskeleton_form_kit_demo_last', $sanitized, 2 * MINUTE_IN_SECONDS );
		return true;
	}

	/**
	 * Render demo page.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		echo '<div class="wrap zs-form-kit-demo-wrap">';
		echo '<h1>' . esc_html( __( 'ZSkeleton Form Kit Demo', 'zskeleton' ) ) . '</h1>';
		echo '<p>' . esc_html( __( 'Reference implementations: single-page and multi-step (wizard) forms with AJAX.', 'zskeleton' ) ) . '</p>';

		echo '<h2>' . esc_html( __( 'Single-page form', 'zskeleton' ) ) . '</h2>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- renderer escapes.
		echo ZSkeleton_Form_Renderer::render( 'zskeleton_demo_simple', array() );

		echo '<hr style="margin:2em 0;" />';
		echo '<h2>' . esc_html( __( 'Wizard form', 'zskeleton' ) ) . '</h2>';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ZSkeleton_Form_Renderer::render( 'zskeleton_demo_wizard', array() );

		echo '</div>';
	}
}
