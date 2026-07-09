<?php
/**
 * Access control for frontend form submission management.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Who may manage submissions for a given form via the shortcode.
 */
class ZSkeleton_Form_Submissions_Access {

	/**
	 * @param array<string,string> $atts Shortcode attributes.
	 * @return array{roles: string[], users: int[]}
	 */
	public static function parse_access_from_atts( array $atts ) {
		$role_keys = array( 'role', 'roles' );
		$user_keys = array( 'user', 'user_id', 'users', 'user_ids' );

		$roles_raw = '';
		foreach ( $role_keys as $key ) {
			if ( ! empty( $atts[ $key ] ) ) {
				$roles_raw = (string) $atts[ $key ];
				break;
			}
		}

		$users_raw = '';
		foreach ( $user_keys as $key ) {
			if ( ! empty( $atts[ $key ] ) ) {
				$users_raw = (string) $atts[ $key ];
				break;
			}
		}

		return array(
			'roles' => self::parse_csv_roles( $roles_raw ),
			'users' => self::parse_csv_user_ids( $users_raw ),
		);
	}

	/**
	 * @param string $value Comma-separated role slugs.
	 * @return string[]
	 */
	public static function parse_csv_roles( $value ) {
		$value = sanitize_text_field( (string) $value );
		if ( '' === $value ) {
			return array();
		}

		$roles = array();
		foreach ( explode( ',', $value ) as $part ) {
			$role = sanitize_key( trim( $part ) );
			if ( '' !== $role ) {
				$roles[] = $role;
			}
		}

		return array_values( array_unique( $roles ) );
	}

	/**
	 * @param string $value Comma-separated user ids.
	 * @return int[]
	 */
	public static function parse_csv_user_ids( $value ) {
		$value = sanitize_text_field( (string) $value );
		if ( '' === $value ) {
			return array();
		}

		$users = array();
		foreach ( explode( ',', $value ) as $part ) {
			$id = absint( trim( $part ) );
			if ( $id > 0 ) {
				$users[] = $id;
			}
		}

		return array_values( array_unique( $users ) );
	}

	/**
	 * Access rules stored on the UI form schema (Settings tab).
	 *
	 * @param string $form_id Form registry id.
	 * @return array{roles: string[], users: int[]}|null Null when not configured on the form.
	 */
	public static function get_form_schema_access( $form_id ) {
		$form_id = sanitize_key( (string) $form_id );
		if ( '' === $form_id || ! class_exists( 'ZSkeleton_Form_Registry_Loader' ) ) {
			return null;
		}

		$post = ZSkeleton_Form_Registry_Loader::find_ui_form_post( $form_id );
		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		$schema = ZSkeleton_Forms_CPT::get_schema( $post->ID );
		$roles  = isset( $schema['submissions_manager_roles'] ) ? $schema['submissions_manager_roles'] : array();
		$users  = isset( $schema['submissions_manager_users'] ) ? $schema['submissions_manager_users'] : array();

		if ( is_string( $roles ) ) {
			$roles = self::parse_csv_roles( $roles );
		} elseif ( is_array( $roles ) ) {
			$roles = array_values(
				array_unique(
					array_filter(
						array_map(
							static function ( $role ) {
								return sanitize_key( (string) $role );
							},
							$roles
						)
					)
				)
			);
		} else {
			$roles = array();
		}

		if ( is_string( $users ) ) {
			$users = self::parse_csv_user_ids( $users );
		} elseif ( is_array( $users ) ) {
			$users = array_values(
				array_unique(
					array_filter(
						array_map( 'absint', $users )
					)
				)
			);
		} else {
			$users = array();
		}

		if ( empty( $roles ) && empty( $users ) ) {
			return null;
		}

		return array(
			'roles' => $roles,
			'users' => $users,
		);
	}

	/**
	 * Resolve who may manage submissions: form settings override shortcode attributes.
	 *
	 * @param string              $form_id Form id.
	 * @param array<string,string> $atts    Shortcode attributes (fallback).
	 * @return array{roles: string[], users: int[]}
	 */
	public static function resolve_access( $form_id, array $atts = array() ) {
		$form_access = self::get_form_schema_access( $form_id );
		if ( null !== $form_access ) {
			return $form_access;
		}

		return self::parse_access_from_atts( $atts );
	}

	/**
	 * @param string              $form_id Form id (for future per-form rules).
	 * @param array{roles: string[], users: int[]} $access Parsed access config.
	 * @return bool
	 */
	public static function can_manage( $form_id, array $access ) {
		unset( $form_id );

		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$roles = isset( $access['roles'] ) ? (array) $access['roles'] : array();
		$users = isset( $access['users'] ) ? (array) $access['users'] : array();

		if ( empty( $roles ) && empty( $users ) ) {
			return false;
		}

		$user = wp_get_current_user();
		if ( ! $user instanceof WP_User || $user->ID < 1 ) {
			return false;
		}

		if ( ! empty( $users ) && in_array( (int) $user->ID, array_map( 'absint', $users ), true ) ) {
			return true;
		}

		if ( ! empty( $roles ) ) {
			$user_roles = (array) $user->roles;
			foreach ( $roles as $role ) {
				if ( in_array( $role, $user_roles, true ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
