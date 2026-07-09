<?php
/**
 * Public contact form via Form Kit (sanitized, validated, AJAX).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the main theme contact form.
 *
 * @param array<string,array> $forms Forms.
 * @return array<string,array>
 */
function zskeleton_register_contact_form_kit( $forms ) {
	if ( ! is_array( $forms ) ) {
		$forms = array();
	}

	$forms['zskeleton_contact'] = array(
		'context'                 => 'public',
		'capability'              => '',
		'allow_public_submission' => true,
		'use_ajax'                => true,
		'nonce_action'            => 'zskeleton_contact_form_kit',
		'fallback'                => 'long_page',
		'honeypot'                => 'contact_company_website',
		'fields'                  => array(
			array(
				'name'        => 'name',
				'type'        => 'text',
				'label'       => __( 'Full name', 'zskeleton' ),
				'required'    => true,
				'placeholder' => __( 'Your name', 'zskeleton' ),
			),
			array(
				'name'     => 'email',
				'type'     => 'email',
				'label'    => __( 'Email', 'zskeleton' ),
				'required' => true,
			),
			array(
				'name'        => 'organization',
				'type'        => 'text',
				'label'       => __( 'Organization', 'zskeleton' ),
				'required'    => false,
				'placeholder' => __( 'Company or team (optional)', 'zskeleton' ),
			),
			array(
				'name'        => 'phone',
				'type'        => 'tel',
				'label'       => __( 'Phone', 'zskeleton' ),
				'required'    => false,
				'placeholder' => __( 'Phone (optional)', 'zskeleton' ),
			),
			array(
				'name'     => 'subject',
				'type'     => 'select',
				'label'    => __( 'Topic', 'zskeleton' ),
				'required' => true,
				'choices'  => array(
					'general'  => __( 'General inquiry', 'zskeleton' ),
					'support'  => __( 'Support', 'zskeleton' ),
					'sales'    => __( 'Sales & partnerships', 'zskeleton' ),
					'press'    => __( 'Press & media', 'zskeleton' ),
					'feedback' => __( 'Feedback', 'zskeleton' ),
				),
			),
			array(
				'name'     => 'urgency',
				'type'     => 'select',
				'label'    => __( 'Response priority', 'zskeleton' ),
				'required' => true,
				'choices'  => array(
					'standard' => __( 'Standard (1–2 business days)', 'zskeleton' ),
					'urgent'   => __( 'Urgent (same day when possible)', 'zskeleton' ),
				),
			),
			array(
				'name'     => 'message',
				'type'     => 'textarea',
				'label'    => __( 'Message', 'zskeleton' ),
				'required' => true,
				'rows'     => 6,
			),
			array(
				'name'  => 'newsletter_signup',
				'type'  => 'toggle',
				'label' => __( 'Email me tips and product updates', 'zskeleton' ),
			),
		),
		'on_submit'               => 'zskeleton_contact_form_kit_process_submit',
	);

	return $forms;
}
add_filter( 'zskeleton_form_kit_forms', 'zskeleton_register_contact_form_kit' );

/**
 * User-facing AJAX success line for the contact form.
 *
 * @param string $message Default message.
 * @param string $form_id Form id.
 * @return string
 */
function zskeleton_contact_form_kit_success_message( $message, $form_id ) {
	if ( 'zskeleton_contact' === $form_id ) {
		return __( 'Thank you for your message. We will get back to you shortly.', 'zskeleton' );
	}
	return $message;
}
add_filter( 'zskeleton_form_kit_submit_response_message', 'zskeleton_contact_form_kit_success_message', 10, 2 );

/**
 * Send contact email after Form Kit validation.
 *
 * @param array                       $data Sanitized field values.
 * @param ZSkeleton_Form_Definition $def  Form definition.
 * @return true|WP_Error
 */
function zskeleton_contact_form_kit_process_submit( $data, $def ) {
	$name             = isset( $data['name'] ) ? (string) $data['name'] : '';
	$email            = isset( $data['email'] ) ? (string) $data['email'] : '';
	$organization     = isset( $data['organization'] ) ? (string) $data['organization'] : '';
	$phone            = isset( $data['phone'] ) ? (string) $data['phone'] : '';
	$subject          = isset( $data['subject'] ) ? (string) $data['subject'] : '';
	$urgency          = isset( $data['urgency'] ) ? (string) $data['urgency'] : '';
	$message          = isset( $data['message'] ) ? (string) $data['message'] : '';
	$newsletter_signup = ! empty( $data['newsletter_signup'] ) && '1' === (string) $data['newsletter_signup'];

	if ( '' === $name || '' === $email || '' === $subject || '' === $message ) {
		return new WP_Error( 'zskeleton_contact_incomplete', __( 'Please complete all required fields.', 'zskeleton' ) );
	}

	if ( ! is_email( $email ) ) {
		return new WP_Error( 'zskeleton_contact_email', __( 'Please enter a valid email address.', 'zskeleton' ) );
	}

	$admin_email   = get_option( 'admin_email' );
	$contact_email = get_option( 'zskeleton_contact_email', $admin_email );

	$email_subject = sprintf(
		/* translators: 1: topic, 2: sender name */
		__( '[%1$s] Contact: %2$s — %3$s', 'zskeleton' ),
		wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		$subject,
		$name
	);

	$email_message  = __( 'New contact form submission:', 'zskeleton' ) . "\n\n";
	$email_message .= __( 'Name', 'zskeleton' ) . ': ' . $name . "\n";
	$email_message .= __( 'Email', 'zskeleton' ) . ': ' . $email . "\n";
	$email_message .= __( 'Organization', 'zskeleton' ) . ': ' . ( $organization !== '' ? $organization : __( 'Not provided', 'zskeleton' ) ) . "\n";
	$email_message .= __( 'Phone', 'zskeleton' ) . ': ' . ( $phone !== '' ? $phone : __( 'Not provided', 'zskeleton' ) ) . "\n";
	$email_message .= __( 'Topic', 'zskeleton' ) . ': ' . $subject . "\n";
	$email_message .= __( 'Priority', 'zskeleton' ) . ': ' . $urgency . "\n";
	$email_message .= __( 'Newsletter', 'zskeleton' ) . ': ' . ( $newsletter_signup ? __( 'Yes', 'zskeleton' ) : __( 'No', 'zskeleton' ) ) . "\n\n";
	$email_message .= __( 'Message', 'zskeleton' ) . ":\n" . $message . "\n\n---\n";
	$email_message .= __( 'Submitted', 'zskeleton' ) . ': ' . gmdate( 'c' ) . "\n";
	$ip             = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( '' !== $ip ) {
		$email_message .= __( 'IP', 'zskeleton' ) . ': ' . $ip . "\n";
	}

	$headers = array(
		'Reply-To: ' . $name . ' <' . $email . '>',
		'Content-Type: text/plain; charset=UTF-8',
	);

	$sent = wp_mail( $contact_email, $email_subject, $email_message, $headers );

	if ( ! $sent ) {
		return new WP_Error( 'zskeleton_contact_mail', __( 'We could not send your message. Please try again later.', 'zskeleton' ) );
	}

	$auto_subject = __( 'We received your message', 'zskeleton' );
	$auto_body    = sprintf(
		/* translators: %s: recipient name */
		__( "Hi %s,\n\nThank you for contacting us. We have received your message and will respond as soon as we can.\n\n— %s", 'zskeleton' ),
		$name,
		wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES )
	);
	if ( 'urgent' === $urgency ) {
		$auto_body .= "\n\n" . __( 'You marked this as urgent; our team will prioritize it when possible.', 'zskeleton' );
	}
	$auto_body .= "\n\n" . __( 'This is an automated message.', 'zskeleton' );

	wp_mail( $email, $auto_subject, $auto_body );

	return true;
}
