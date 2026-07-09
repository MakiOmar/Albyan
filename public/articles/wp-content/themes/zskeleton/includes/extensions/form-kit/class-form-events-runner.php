<?php
/**
 * Declarative post-submit actions for UI-built forms.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Runs configured form events after validation.
 */
class ZSkeleton_Form_Events_Runner {

	/**
	 * Redirect URL collected from the last enabled redirect action in the run.
	 *
	 * @var string
	 */
	private static $redirect_url = '';

	/**
	 * @return string
	 */
	public static function get_redirect_url() {
		return self::$redirect_url;
	}

	/**
	 * Last enabled static redirect URL from form events (no field tokens).
	 *
	 * @param array<int,array<string,mixed>> $events UI events.
	 * @return string
	 */
	public static function get_configured_redirect_from_events( array $events ) {
		$url = '';
		foreach ( $events as $event ) {
			if ( ! is_array( $event ) || empty( $event['type'] ) || 'redirect' !== sanitize_key( (string) $event['type'] ) ) {
				continue;
			}
			if ( empty( $event['enabled'] ) ) {
				continue;
			}
			$candidate = isset( $event['url'] ) ? trim( (string) $event['url'] ) : '';
			if ( '' === $candidate || false !== strpos( $candidate, '{' ) ) {
				continue;
			}
			$resolved = self::resolve_redirect_url( $candidate );
			if ( '' !== $resolved ) {
				$url = $resolved;
			}
		}
		return $url;
	}

	/**
	 * Normalize a redirect destination to an absolute URL.
	 *
	 * @param string $url Raw redirect from form settings.
	 * @return string
	 */
	public static function resolve_redirect_url( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return '';
		}

		if ( 0 === strpos( $url, '?' ) ) {
			$url = home_url( '/' ) . $url;
		} elseif ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) {
			$url = home_url( $url );
		}

		$resolved = esc_url_raw( $url );
		if ( '' !== $resolved ) {
			return $resolved;
		}

		// Allow tokenized URLs to pass through for runtime replacement.
		if ( false !== strpos( $url, '{' ) && wp_parse_url( $url, PHP_URL_HOST ) ) {
			return $url;
		}

		return '';
	}

	/**
	 * @param array<string,mixed>     $sanitized Sanitized field values.
	 * @param ZSkeleton_Form_Definition $def Form definition.
	 * @return true|WP_Error
	 */
	public static function run( array $sanitized, ZSkeleton_Form_Definition $def ) {
		self::$redirect_url = '';

		$events = $def->get_ui_events();
		if ( empty( $events ) ) {
			return true;
		}

		$log      = array();
		$has_save = false;

		foreach ( $events as $event ) {
			if ( ! is_array( $event ) || empty( $event['type'] ) ) {
				continue;
			}
			$type = sanitize_key( (string) $event['type'] );
			if ( empty( $event['enabled'] ) && 'save_submission' !== $type ) {
				continue;
			}

			$result = self::run_action( $type, $event, $sanitized, $def );
			$log[]  = array(
				'type'    => $type,
				'success' => ! is_wp_error( $result ),
				'message' => is_wp_error( $result ) ? $result->get_error_message() : '',
			);

			if ( is_wp_error( $result ) ) {
				return $result;
			}
			if ( 'save_submission' === $type ) {
				$has_save = true;
			}
		}

		if ( ! $has_save ) {
			self::maybe_save_submission( $sanitized, $def, $log );
		}

		return true;
	}

	/**
	 * @param string                    $type Action type.
	 * @param array<string,mixed>       $event Event config.
	 * @param array<string,mixed>       $sanitized Values.
	 * @param ZSkeleton_Form_Definition $def Definition.
	 * @return true|WP_Error
	 */
	private static function run_action( $type, array $event, array $sanitized, ZSkeleton_Form_Definition $def ) {
		switch ( $type ) {
			case 'save_submission':
				return self::action_save_submission( $sanitized, $def, $event );

			case 'email_admin':
				return self::action_email_admin( $sanitized, $def, $event );

			case 'email_user':
				return self::action_email_user( $sanitized, $def, $event );

			case 'mailerlite_subscribe':
				return self::action_mailerlite( $sanitized, $event );

			case 'redirect':
				return self::action_redirect( $sanitized, $event );

			default:
				/**
				 * Custom event action.
				 *
				 * @param true|WP_Error             $result  Default true.
				 * @param string                    $type    Action type.
				 * @param array<string,mixed>       $event   Config.
				 * @param array<string,mixed>       $data    Sanitized values.
				 * @param ZSkeleton_Form_Definition $def     Definition.
				 */
				$result = apply_filters( 'zskeleton_form_kit_run_event', true, $type, $event, $sanitized, $def );
				return is_wp_error( $result ) ? $result : true;
		}
	}

	/**
	 * @param array<string,mixed>       $sanitized Values.
	 * @param ZSkeleton_Form_Definition $def Definition.
	 * @param array<string,mixed>       $event Event config.
	 * @return true|WP_Error
	 */
	private static function action_save_submission( array $sanitized, ZSkeleton_Form_Definition $def, array $event ) {
		unset( $event );
		return self::maybe_save_submission( $sanitized, $def, array() );
	}

	/**
	 * @param array<string,mixed>       $sanitized Values.
	 * @param ZSkeleton_Form_Definition $def Definition.
	 * @param array<int,array>          $log Event log.
	 * @return true|WP_Error
	 */
	private static function maybe_save_submission( array $sanitized, ZSkeleton_Form_Definition $def, array $log ) {
		$id = ZSkeleton_Form_Submissions_Repository::insert(
			$def->get_id(),
			$sanitized,
			array(
				'meta' => array( 'events' => $log ),
			)
		);
		if ( false === $id ) {
			return new WP_Error( 'zskeleton_form_save_failed', __( 'Could not save your submission.', 'zskeleton' ) );
		}
		return true;
	}

	/**
	 * @param array<string,mixed>       $sanitized Values.
	 * @param ZSkeleton_Form_Definition $def Definition.
	 * @param array<string,mixed>       $event Event config.
	 * @return true|WP_Error
	 */
	private static function action_email_admin( array $sanitized, ZSkeleton_Form_Definition $def, array $event ) {
		$to = isset( $event['to'] ) ? sanitize_email( (string) $event['to'] ) : '';
		if ( '' === $to ) {
			$to = sanitize_email( (string) get_option( 'zskeleton_contact_email', get_option( 'admin_email' ) ) );
		}
		if ( '' === $to ) {
			return new WP_Error( 'zskeleton_form_email_admin', __( 'Admin email is not configured.', 'zskeleton' ) );
		}

		$subject = isset( $event['subject'] ) ? (string) $event['subject'] : sprintf(
			/* translators: %s: form id */
			__( 'New form submission: %s', 'zskeleton' ),
			$def->get_id()
		);
		$body = isset( $event['body'] ) ? (string) $event['body'] : self::default_admin_body( $sanitized, $def );

		$subject = self::replace_tokens( $subject, $sanitized );
		$body    = self::replace_tokens( $body, $sanitized );

		$sent = wp_mail( $to, $subject, $body );
		if ( ! $sent ) {
			return new WP_Error( 'zskeleton_form_email_admin', __( 'Could not send admin notification.', 'zskeleton' ) );
		}
		return true;
	}

	/**
	 * @param array<string,mixed>       $sanitized Values.
	 * @param ZSkeleton_Form_Definition $def Definition.
	 * @param array<string,mixed>       $event Event config.
	 * @return true|WP_Error
	 */
	private static function action_email_user( array $sanitized, ZSkeleton_Form_Definition $def, array $event ) {
		unset( $def );
		$field = isset( $event['to_field'] ) ? sanitize_key( (string) $event['to_field'] ) : 'email';
		$to    = isset( $sanitized[ $field ] ) ? sanitize_email( (string) $sanitized[ $field ] ) : '';
		if ( '' === $to ) {
			return true;
		}

		$subject = isset( $event['subject'] ) ? (string) $event['subject'] : __( 'We received your message', 'zskeleton' );
		$body    = isset( $event['body'] ) ? (string) $event['body'] : __( 'Thank you for contacting us. We will get back to you soon.', 'zskeleton' );

		$subject = self::replace_tokens( $subject, $sanitized );
		$body    = self::replace_tokens( $body, $sanitized );

		wp_mail( $to, $subject, $body );
		return true;
	}

	/**
	 * @param array<string,mixed> $sanitized Values.
	 * @param array<string,mixed> $event Event config.
	 * @return true|WP_Error
	 */
	private static function action_mailerlite( array $sanitized, array $event ) {
		$field = isset( $event['email_field'] ) ? sanitize_key( (string) $event['email_field'] ) : 'email';
		$email = isset( $sanitized[ $field ] ) ? sanitize_email( (string) $sanitized[ $field ] ) : '';
		if ( '' === $email ) {
			return true;
		}

		if ( function_exists( 'zskeleton_mailerlite_subscribe' ) && function_exists( 'zskeleton_is_mailerlite_active' ) && zskeleton_is_mailerlite_active() ) {
			$group_key = isset( $event['group_key'] ) ? sanitize_key( (string) $event['group_key'] ) : 'general';
			$group_id  = function_exists( 'zskeleton_get_mailerlite_group_id' ) ? zskeleton_get_mailerlite_group_id( $group_key ) : null;
			zskeleton_mailerlite_subscribe( $email, array(), $group_id );
		}

		$subscribers = get_option( 'zskeleton_newsletter_subscribers', array() );
		if ( ! is_array( $subscribers ) ) {
			$subscribers = array();
		}
		if ( ! in_array( $email, $subscribers, true ) ) {
			$subscribers[] = $email;
			update_option( 'zskeleton_newsletter_subscribers', $subscribers );
		}

		return true;
	}

	/**
	 * @param array<string,mixed> $sanitized Values.
	 * @param array<string,mixed> $event Event config.
	 * @return true|WP_Error
	 */
	private static function action_redirect( array $sanitized, array $event ) {
		$url = isset( $event['url'] ) ? trim( (string) $event['url'] ) : '';
		if ( '' === $url ) {
			return true;
		}

		$url = self::replace_tokens( $url, $sanitized );
		$url = self::resolve_redirect_url( $url );
		if ( '' !== $url ) {
			self::$redirect_url = $url;
		}

		return true;
	}

	/**
	 * @param array<string,mixed>       $sanitized Values.
	 * @param ZSkeleton_Form_Definition $def Definition.
	 * @return string
	 */
	private static function default_admin_body( array $sanitized, ZSkeleton_Form_Definition $def ) {
		$lines = array(
			sprintf( 'Form: %s', $def->get_id() ),
			sprintf( 'Time: %s', current_time( 'mysql' ) ),
			'',
		);
		foreach ( $sanitized as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = wp_json_encode( $value );
			}
			$lines[] = $key . ': ' . (string) $value;
		}
		return implode( "\n", $lines );
	}

	/**
	 * @param string              $text Template text.
	 * @param array<string,mixed> $sanitized Values.
	 * @return string
	 */
	private static function replace_tokens( $text, array $sanitized ) {
		foreach ( $sanitized as $key => $value ) {
			if ( is_array( $value ) ) {
				$value = wp_json_encode( $value );
			}
			$text = str_replace( '{' . $key . '}', (string) $value, $text );
		}
		return $text;
	}
}
