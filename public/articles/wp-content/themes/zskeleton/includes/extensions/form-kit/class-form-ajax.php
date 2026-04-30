<?php
/**
 * AJAX validate step + submit.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles Form Kit AJAX endpoints.
 */
class ZSkeleton_Form_Ajax {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_ajax_zskeleton_form_validate_step', array( $this, 'handle_validate_step' ) );
		add_action( 'wp_ajax_zskeleton_form_submit', array( $this, 'handle_submit' ) );
		add_action( 'wp_ajax_nopriv_zskeleton_form_validate_step', array( $this, 'handle_validate_step_nopriv' ) );
		add_action( 'wp_ajax_nopriv_zskeleton_form_submit', array( $this, 'handle_submit_nopriv' ) );
	}

	/**
	 * Logged-in validate step.
	 */
	public function handle_validate_step() {
		$this->process_validate_step( false );
	}

	/**
	 * Public validate step.
	 */
	public function handle_validate_step_nopriv() {
		$this->process_validate_step( true );
	}

	/**
	 * Logged-in submit.
	 */
	public function handle_submit() {
		$this->process_submit( false );
	}

	/**
	 * Public submit.
	 */
	public function handle_submit_nopriv() {
		$this->process_submit( true );
	}

	/**
	 * @param bool $is_nopriv Nopriv handler.
	 */
	private function process_validate_step( $is_nopriv ) {
		$form_id = isset( $_POST['zs_form_id'] ) ? sanitize_key( wp_unslash( $_POST['zs_form_id'] ) ) : '';
		$def      = ZSkeleton_Form_Definition::get( $form_id );
		if ( ! $def ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'zskeleton' ) ), 400 );
		}
		if ( $is_nopriv && ! $def->allow_public_submission() ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'zskeleton' ) ), 403 );
		}
		if ( ! isset( $_POST['zs_form_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zs_form_nonce'] ) ), $def->get_nonce_action() ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'zskeleton' ) ), 403 );
		}
		if ( ! $this->authorize( $def ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'zskeleton' ) ), 403 );
		}
		if ( ! ZSkeleton_Form_Handler::rate_limit_allow( $form_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Too many requests. Please wait.', 'zskeleton' ) ), 429 );
		}

		$step = isset( $_POST['zs_step_index'] ) ? (int) $_POST['zs_step_index'] : 0;
		$data = $this->strip_internal_keys( wp_unslash( $_POST ) );
		$res  = ZSkeleton_Form_Handler::process_request(
			$form_id,
			$data,
			array(
				'validate_scope' => 'step',
				'step_index'     => $step,
			)
		);

		if ( is_wp_error( $res ) ) {
			wp_send_json_error( array( 'message' => $res->get_error_message() ), 400 );
		}
		if ( ! empty( $res['errors'] ) ) {
			wp_send_json_success(
				array(
					'valid'  => false,
					'errors' => $res['errors'],
				)
			);
		}
		wp_send_json_success( array( 'valid' => true ) );
	}

	/**
	 * @param bool $is_nopriv Nopriv handler.
	 */
	private function process_submit( $is_nopriv ) {
		$form_id = isset( $_POST['zs_form_id'] ) ? sanitize_key( wp_unslash( $_POST['zs_form_id'] ) ) : '';
		$def      = ZSkeleton_Form_Definition::get( $form_id );
		if ( ! $def ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'zskeleton' ) ), 400 );
		}
		if ( $is_nopriv && ! $def->allow_public_submission() ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'zskeleton' ) ), 403 );
		}
		if ( ! isset( $_POST['zs_form_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zs_form_nonce'] ) ), $def->get_nonce_action() ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'zskeleton' ) ), 403 );
		}
		if ( ! $this->authorize( $def ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'zskeleton' ) ), 403 );
		}
		if ( ! ZSkeleton_Form_Handler::rate_limit_allow( $form_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Too many requests. Please wait.', 'zskeleton' ) ), 429 );
		}

		$data = $this->strip_internal_keys( wp_unslash( $_POST ) );
		$res  = ZSkeleton_Form_Handler::process_request(
			$form_id,
			$data,
			array(
				'validate_scope' => 'all',
			)
		);

		if ( is_wp_error( $res ) ) {
			wp_send_json_error( array( 'message' => $res->get_error_message() ), 400 );
		}
		if ( ! empty( $res['errors'] ) ) {
			wp_send_json_success(
				array(
					'saved'  => false,
					'errors' => $res['errors'],
				)
			);
		}
		if ( ! empty( $res['message'] ) && empty( $res['ok'] ) ) {
			wp_send_json_error( array( 'message' => $res['message'] ), 400 );
		}
		if ( ! empty( $res['ok'] ) ) {
			/**
			 * After successful form kit submit (sanitized data persisted by callback).
			 *
			 * @param string $form_id Form id.
			 * @param array  $data    Sanitized values.
			 */
			do_action( 'zskeleton_form_kit_submitted', $form_id, isset( $res['sanitized'] ) ? $res['sanitized'] : array() );
			/**
			 * Filter success message returned to the client (AJAX JSON).
			 *
			 * @param string $message Default message.
			 * @param string $form_id Form id.
			 */
			$success_msg = apply_filters( 'zskeleton_form_kit_submit_response_message', __( 'Submitted successfully.', 'zskeleton' ), $form_id );
			wp_send_json_success(
				array(
					'saved'   => true,
					'message' => $success_msg,
				)
			);
		}
		wp_send_json_error( array( 'message' => __( 'Submission could not be completed.', 'zskeleton' ) ), 400 );
	}

	/**
	 * @param ZSkeleton_Form_Definition $def Definition.
	 * @return bool
	 */
	private function authorize( ZSkeleton_Form_Definition $def ) {
		if ( $def->allow_public_submission() ) {
			return true;
		}
		$cap = $def->get_capability();
		return is_user_logged_in() && ( '' === $cap || current_user_can( $cap ) );
	}

	/**
	 * @param array $post Post data.
	 * @return array
	 */
	private function strip_internal_keys( array $post ) {
		$strip = array(
			'action',
			'zs_form_id',
			'zs_form_nonce',
			'_wp_http_referer',
			'zs_step_index',
		);
		foreach ( $strip as $k ) {
			unset( $post[ $k ] );
		}
		return $post;
	}
}
