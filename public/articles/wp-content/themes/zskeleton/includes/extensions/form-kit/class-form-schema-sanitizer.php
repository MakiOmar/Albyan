<?php
/**
 * Sanitize UI form schema JSON from the builder.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Schema sanitizer for zskeleton_form CPT.
 */
class ZSkeleton_Form_Schema_Sanitizer {

	const MAX_FIELDS = 100;

	const PUBLIC_FIELD_TYPES = array(
		'text',
		'email',
		'url',
		'tel',
		'textarea',
		'select',
		'checkbox',
		'checkboxes',
		'radio',
		'toggle',
		'number',
		'date',
		'hidden',
	);

	/**
	 * @param array<string,mixed> $raw Raw schema.
	 * @param bool                $is_public Public form flag.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function sanitize_schema( array $raw, $is_public = true ) {
		$schema = array(
			'context'                 => ! empty( $raw['context'] ) && 'admin' === $raw['context'] ? 'admin' : 'public',
			'allow_public_submission' => ! empty( $raw['allow_public_submission'] ),
			'use_ajax'                => ! isset( $raw['use_ajax'] ) || ! empty( $raw['use_ajax'] ),
			'fallback'                => isset( $raw['fallback'] ) && 'none' === $raw['fallback'] ? 'none' : 'long_page',
			'layout'                  => self::sanitize_layout( isset( $raw['layout'] ) && is_array( $raw['layout'] ) ? $raw['layout'] : array() ),
		);

		if ( ! empty( $raw['honeypot'] ) ) {
			$schema['honeypot'] = sanitize_key( (string) $raw['honeypot'] );
		}

		if ( ! empty( $raw['capability'] ) ) {
			$schema['capability'] = sanitize_key( (string) $raw['capability'] );
		}

		if ( ! empty( $raw['success_message'] ) ) {
			$schema['success_message'] = sanitize_text_field( (string) $raw['success_message'] );
		}

		if ( ! empty( $raw['submit_button_text'] ) ) {
			$schema['submit_button_text'] = sanitize_text_field( (string) $raw['submit_button_text'] );
		}

		$manager_roles = self::sanitize_submissions_manager_roles( $raw['submissions_manager_roles'] ?? '' );
		if ( ! empty( $manager_roles ) ) {
			$schema['submissions_manager_roles'] = $manager_roles;
		}

		$manager_users = self::sanitize_submissions_manager_users( $raw['submissions_manager_users'] ?? '' );
		if ( ! empty( $manager_users ) ) {
			$schema['submissions_manager_users'] = $manager_users;
		}

		if ( ! empty( $raw['redirect_url'] ) ) {
			$schema['redirect_url'] = esc_url_raw( (string) $raw['redirect_url'] );
		}

		$field_count = 0;
		if ( ! empty( $raw['layout_tree'] ) && is_array( $raw['layout_tree'] ) ) {
			$tree = self::sanitize_layout_tree( $raw['layout_tree'], $is_public, $field_count );
			if ( is_wp_error( $tree ) ) {
				return $tree;
			}
			$schema['layout_tree'] = $tree;
		} elseif ( ! empty( $raw['fields'] ) && is_array( $raw['fields'] ) ) {
			$fields = self::sanitize_fields_list( $raw['fields'], $is_public, $field_count );
			if ( is_wp_error( $fields ) ) {
				return $fields;
			}
			$schema['fields'] = $fields;
		} else {
			return new WP_Error( 'zskeleton_form_schema_empty', __( 'Add at least one field to the form.', 'zskeleton' ) );
		}

		if ( $field_count < 1 ) {
			return new WP_Error( 'zskeleton_form_schema_empty', __( 'Add at least one field to the form.', 'zskeleton' ) );
		}

		return $schema;
	}

	/**
	 * @param array<int,array<string,mixed>> $raw Events list.
	 * @return array<int,array<string,mixed>>
	 */
	public static function sanitize_events( array $raw ) {
		$out = array();
		foreach ( $raw as $event ) {
			if ( ! is_array( $event ) || empty( $event['type'] ) ) {
				continue;
			}
			$type = sanitize_key( (string) $event['type'] );
			$item = array(
				'type'    => $type,
				'enabled' => ! empty( $event['enabled'] ),
			);
			if ( 'email_admin' === $type ) {
				$item['to']      = isset( $event['to'] ) ? sanitize_email( (string) $event['to'] ) : '';
				$item['subject'] = isset( $event['subject'] ) ? sanitize_text_field( (string) $event['subject'] ) : '';
				$item['body']    = isset( $event['body'] ) ? sanitize_textarea_field( (string) $event['body'] ) : '';
			} elseif ( 'email_user' === $type ) {
				$item['to_field'] = isset( $event['to_field'] ) ? sanitize_key( (string) $event['to_field'] ) : 'email';
				$item['subject']  = isset( $event['subject'] ) ? sanitize_text_field( (string) $event['subject'] ) : '';
				$item['body']     = isset( $event['body'] ) ? sanitize_textarea_field( (string) $event['body'] ) : '';
			} elseif ( 'mailerlite_subscribe' === $type ) {
				$item['email_field'] = isset( $event['email_field'] ) ? sanitize_key( (string) $event['email_field'] ) : 'email';
				$item['group_key']   = isset( $event['group_key'] ) ? sanitize_key( (string) $event['group_key'] ) : 'general';
			} elseif ( 'save_submission' === $type ) {
				$item['enabled'] = true;
			} elseif ( 'redirect' === $type ) {
				$item['url'] = isset( $event['url'] ) ? sanitize_text_field( (string) $event['url'] ) : '';
			}
			$out[] = $item;
		}

		if ( empty( $out ) ) {
			$out[] = array(
				'type'    => 'save_submission',
				'enabled' => true,
			);
		}

		return $out;
	}

	/**
	 * @param array<string,mixed> $layout Layout settings.
	 * @return array<string,mixed>
	 */
	private static function sanitize_layout( array $layout ) {
		$out = array();
		if ( isset( $layout['mobile_stack_rows'] ) ) {
			$out['mobile_stack_rows'] = (bool) $layout['mobile_stack_rows'];
		}
		return $out;
	}

	/**
	 * @param array<int,array<string,mixed>> $nodes Nodes.
	 * @param bool                             $is_public Public form.
	 * @param int                              $field_count Field counter by reference.
	 * @return array<int,array<string,mixed>>|WP_Error
	 */
	private static function sanitize_layout_tree( array $nodes, $is_public, &$field_count ) {
		$out   = array();
		$names = array();

		foreach ( $nodes as $node ) {
			if ( ! is_array( $node ) || empty( $node['type'] ) ) {
				continue;
			}
			$type = sanitize_key( (string) $node['type'] );

			if ( 'field' === $type ) {
				$field = isset( $node['field'] ) && is_array( $node['field'] ) ? $node['field'] : array();
				$clean = self::sanitize_field( $field, $is_public );
				if ( is_wp_error( $clean ) ) {
					return $clean;
				}
				if ( in_array( $clean['name'], $names, true ) ) {
					return new WP_Error( 'zskeleton_form_duplicate_field', __( 'Field names must be unique.', 'zskeleton' ) );
				}
				$names[] = $clean['name'];
				++$field_count;
				$out[]   = array(
					'type'  => 'field',
					'field' => $clean,
				);
			} elseif ( 'row' === $type ) {
				$columns = isset( $node['columns'] ) ? (int) $node['columns'] : 2;
				$columns = max( 2, min( 4, $columns ) );
				$row     = array(
					'type'         => 'row',
					'id'           => ! empty( $node['id'] ) ? sanitize_key( (string) $node['id'] ) : 'row_' . wp_generate_password( 6, false, false ),
					'columns'      => $columns,
					'mobile_stack' => ! isset( $node['mobile_stack'] ) || ! empty( $node['mobile_stack'] ),
					'children'     => array(),
				);
				$children = isset( $node['children'] ) && is_array( $node['children'] ) ? $node['children'] : array();
				for ( $c = 0; $c < $columns; $c++ ) {
					$child_node = isset( $children[ $c ] ) && is_array( $children[ $c ] ) ? $children[ $c ] : array( 'type' => 'column', 'fields' => array() );
					$fields     = isset( $child_node['fields'] ) && is_array( $child_node['fields'] ) ? $child_node['fields'] : array();
					$col_fields = array();
					foreach ( $fields as $field ) {
						if ( $field_count >= self::MAX_FIELDS ) {
							return new WP_Error( 'zskeleton_form_too_many_fields', __( 'Too many fields in this form.', 'zskeleton' ) );
						}
						$clean = self::sanitize_field( $field, $is_public );
						if ( is_wp_error( $clean ) ) {
							return $clean;
						}
						if ( in_array( $clean['name'], $names, true ) ) {
							return new WP_Error( 'zskeleton_form_duplicate_field', __( 'Field names must be unique.', 'zskeleton' ) );
						}
						$names[]      = $clean['name'];
						$col_fields[] = $clean;
						++$field_count;
					}
					$row['children'][] = array(
						'type'   => 'column',
						'fields' => $col_fields,
					);
				}
				$out[] = $row;
			}
		}

		return $out;
	}

	/**
	 * @param array<int,array<string,mixed>> $fields Fields.
	 * @param bool                           $is_public Public form.
	 * @param int                            $field_count Counter.
	 * @return array<int,array<string,mixed>>|WP_Error
	 */
	private static function sanitize_fields_list( array $fields, $is_public, &$field_count ) {
		$out   = array();
		$names = array();
		foreach ( $fields as $field ) {
			if ( $field_count >= self::MAX_FIELDS ) {
				return new WP_Error( 'zskeleton_form_too_many_fields', __( 'Too many fields in this form.', 'zskeleton' ) );
			}
			$clean = self::sanitize_field( $field, $is_public );
			if ( is_wp_error( $clean ) ) {
				return $clean;
			}
			if ( in_array( $clean['name'], $names, true ) ) {
				return new WP_Error( 'zskeleton_form_duplicate_field', __( 'Field names must be unique.', 'zskeleton' ) );
			}
			$names[] = $clean['name'];
			$out[]   = $clean;
			++$field_count;
		}
		return $out;
	}

	/**
	 * @param mixed $raw Roles from schema JSON.
	 * @return string[]
	 */
	public static function sanitize_submissions_manager_roles( $raw ) {
		if ( is_array( $raw ) ) {
			$roles = array();
			foreach ( $raw as $role ) {
				$key = sanitize_key( (string) $role );
				if ( '' !== $key ) {
					$roles[] = $key;
				}
			}
			return array_values( array_unique( $roles ) );
		}

		return ZSkeleton_Form_Submissions_Access::parse_csv_roles( (string) $raw );
	}

	/**
	 * @param mixed $raw User ids from schema JSON.
	 * @return int[]
	 */
	public static function sanitize_submissions_manager_users( $raw ) {
		if ( is_array( $raw ) ) {
			$users = array();
			foreach ( $raw as $user_id ) {
				$id = absint( $user_id );
				if ( $id > 0 ) {
					$users[] = $id;
				}
			}
			return array_values( array_unique( $users ) );
		}

		return ZSkeleton_Form_Submissions_Access::parse_csv_user_ids( (string) $raw );
	}

	/**
	 * @param array<string,mixed> $field Field config.
	 * @param bool                $is_public Public form.
	 * @return array<string,mixed>|WP_Error
	 */
	private static function sanitize_field( array $field, $is_public ) {
		$name = isset( $field['name'] ) ? sanitize_key( (string) $field['name'] ) : '';
		if ( '' === $name ) {
			$name = 'field_' . wp_generate_password( 8, false, false );
		}

		$type = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';
		if ( $is_public && ! in_array( $type, self::PUBLIC_FIELD_TYPES, true ) ) {
			$type = 'text';
		}

		$label = isset( $field['label'] ) ? (string) $field['label'] : $name;
		$label = self::repair_unicode_escape_label( $label );

		$clean = array(
			'name'  => $name,
			'type'  => $type,
			'label' => sanitize_text_field( $label ),
		);

		if ( ! empty( $field['required'] ) ) {
			$clean['required'] = true;
		}
		if ( ! empty( $field['placeholder'] ) ) {
			$clean['placeholder'] = sanitize_text_field( (string) $field['placeholder'] );
		}
		if ( ! empty( $field['description'] ) ) {
			$clean['description'] = sanitize_text_field( (string) $field['description'] );
		}
		if ( ! empty( $field['choices'] ) && is_array( $field['choices'] ) ) {
			$choices = array();
			foreach ( $field['choices'] as $k => $label ) {
				$key = sanitize_key( (string) $k );
				if ( '' !== $key ) {
					$choices[ $key ] = sanitize_text_field( (string) $label );
				}
			}
			$clean['choices'] = $choices;
		}
		if ( 'tel' === $type && ! empty( $field['intl_tel'] ) ) {
			$clean['intl_tel'] = true;
			if ( ! empty( $field['initial_country'] ) ) {
				$country = strtolower( sanitize_text_field( (string) $field['initial_country'] ) );
				if ( preg_match( '/^[a-z]{2}$/', $country ) ) {
					$clean['initial_country'] = $country;
				}
			}
		}
		if ( ! empty( $field['rules'] ) && is_array( $field['rules'] ) ) {
			$rules = self::sanitize_field_rules( $field['rules'] );
			if ( ! empty( $rules ) ) {
				$clean['rules'] = $rules;
			}
		}

		return $clean;
	}

	/**
	 * Sanitize field validation rules from the builder.
	 *
	 * @param array<string,mixed> $rules Raw rules.
	 * @return array<string,mixed>
	 */
	private static function sanitize_field_rules( array $rules ) {
		$clean = array();

		if ( ! empty( $rules['pattern'] ) ) {
			$pattern = self::sanitize_pattern( (string) $rules['pattern'] );
			if ( '' !== $pattern ) {
				$clean['pattern'] = $pattern;
				if ( ! empty( $rules['pattern_message'] ) ) {
					$clean['pattern_message'] = sanitize_text_field( (string) $rules['pattern_message'] );
				}
			}
		}

		return $clean;
	}

	/**
	 * Normalize a regex pattern for PHP preg_match (with delimiters).
	 *
	 * @param string $raw User-entered pattern.
	 * @return string Empty string when invalid.
	 */
	private static function sanitize_pattern( $raw ) {
		$raw = trim( (string) $raw );
		if ( '' === $raw ) {
			return '';
		}

		if ( preg_match( '/^\/(.+)\/([imsxADSUXJu]*)$/s', $raw ) ) {
			if ( false === @preg_match( $raw, '' ) ) {
				return '';
			}
			return $raw;
		}

		$delimited = '/' . str_replace( '/', '\/', $raw ) . '/';
		if ( false === @preg_match( $delimited, '' ) ) {
			return '';
		}

		return $delimited;
	}

	/**
	 * Repair labels corrupted when JSON unicode escapes lost backslashes in HTML attributes.
	 *
	 * @param string $label Label text.
	 * @return string
	 */
	private static function repair_unicode_escape_label( $label ) {
		if ( ! is_string( $label ) || '' === $label ) {
			return $label;
		}
		if ( ! preg_match( '/^u[0-9a-f]{4}/i', $label ) ) {
			return $label;
		}
		$repaired = preg_replace_callback(
			'/u([0-9a-f]{4})/i',
			static function ( $matches ) {
				return mb_convert_encoding( pack( 'H*', $matches[1] ), 'UTF-8', 'UCS-2BE' );
			},
			$label
		);
		return is_string( $repaired ) ? $repaired : $label;
	}
}
