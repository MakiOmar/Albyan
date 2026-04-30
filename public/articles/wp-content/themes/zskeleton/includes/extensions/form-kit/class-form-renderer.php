<?php
/**
 * HTML output, nonces, wizard panels, a11y.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders form markup.
 */
class ZSkeleton_Form_Renderer {

	/**
	 * @param string              $form_id Form id.
	 * @param array<string,mixed> $values  Current values keyed by field name.
	 * @return string HTML.
	 */
	public static function render( $form_id, array $values = array() ) {
		$definition = ZSkeleton_Form_Definition::get( $form_id );
		if ( ! $definition ) {
			return '';
		}

		ZSkeleton_Form_Assets::request_enqueue( $definition->get_context() );

		$registry = ZSkeleton_Field_Registry::instance();
		$ctx      = $definition->get_context();
		$classes  = 'zs-form zs-form--' . sanitize_html_class( $ctx );
		if ( $definition->has_wizard() ) {
			$classes .= ' zs-form--wizard';
		}
		if ( $definition->use_ajax() ) {
			$classes .= ' zs-form--ajax';
		}

		ob_start();
		$form_uid = 'zs-form-' . esc_attr( $definition->get_id() );
		$action   = esc_url( admin_url( 'admin-ajax.php' ) );
		?>
		<form id="<?php echo esc_attr( $form_uid ); ?>" class="<?php echo esc_attr( $classes ); ?>" method="post" action="<?php echo $action; ?>"
			data-zs-form-id="<?php echo esc_attr( $definition->get_id() ); ?>"
			data-zs-form-ajax="<?php echo $definition->use_ajax() ? '1' : '0'; ?>"
			data-zs-form-wizard="<?php echo $definition->has_wizard() ? '1' : '0'; ?>"
			data-zs-form-fallback="<?php echo esc_attr( $definition->get_fallback() ); ?>"
			novalidate>
			<?php wp_nonce_field( $definition->get_nonce_action(), 'zs_form_nonce', false, true ); ?>
			<input type="hidden" name="action" value="zskeleton_form_submit" />
			<input type="hidden" name="zs_form_id" value="<?php echo esc_attr( $definition->get_id() ); ?>" />
			<?php
			if ( $definition->has_wizard() ) {
				self::render_wizard_progress( $definition );
			}
			$step_count = $definition->get_step_count();
			for ( $s = 0; $s < $step_count; $s++ ) {
				$panel_class = 'zs-form__step';
				if ( $definition->has_wizard() ) {
					$panel_class .= 0 === $s ? ' is-active' : '';
				}
				$meta = $definition->get_step_meta();
				$sid  = isset( $meta[ $s ]['id'] ) ? $meta[ $s ]['id'] : 'step_' . $s;
				echo '<div class="' . esc_attr( $panel_class ) . '" data-zs-step="' . esc_attr( (string) $s ) . '" id="' . esc_attr( $form_uid . '-step-' . $s ) . '" role="group" aria-labelledby="' . esc_attr( $form_uid . '-step-title-' . $s ) . '"' . ( $definition->has_wizard() && $s > 0 ? ' hidden' : '' ) . '>';
				if ( $definition->has_wizard() && ! empty( $meta[ $s ]['title'] ) ) {
					echo '<h3 class="zs-form__step-title" id="' . esc_attr( $form_uid . '-step-title-' . $s ) . '">' . esc_html( $meta[ $s ]['title'] ) . '</h3>';
				}
				echo '<div class="zs-form__step-inner">';
				foreach ( $definition->get_fields_for_step( $s ) as $fname => $field ) {
					$val = isset( $values[ $fname ] ) ? $values[ $fname ] : ( isset( $field['default'] ) ? $field['default'] : '' );
					echo self::render_field_row( $field, $val, $registry, $definition, $form_uid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in method.
				}
				echo '</div></div>';
			}
			$hp = $definition->get_honeypot_name();
			if ( $hp ) {
				echo '<div class="zs-form__hp" aria-hidden="true" style="position:absolute;left:-9999px;">';
				echo '<label for="' . esc_attr( $form_uid . '-hp-' . $hp ) . '">' . esc_html( __( 'Leave empty', 'zskeleton' ) ) . '</label>';
				echo '<input type="text" id="' . esc_attr( $form_uid . '-hp-' . $hp ) . '" name="' . esc_attr( $hp ) . '" value="" tabindex="-1" autocomplete="off" />';
				echo '</div>';
			}
			self::render_footer_nav( $definition, $form_uid );
			?>
			<div class="zs-form__notices" role="alert" aria-live="polite" aria-atomic="true" tabindex="-1" hidden></div>
		</form>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @param ZSkeleton_Form_Definition $definition Definition.
	 * @param string                    $form_uid Form DOM id.
	 */
	private static function render_footer_nav( ZSkeleton_Form_Definition $definition, $form_uid ) {
		echo '<div class="zs-form__actions">';
		if ( $definition->has_wizard() ) {
			echo '<button type="button" class="button zs-form__btn zs-form__btn--back" data-zs-back hidden>' . esc_html( __( 'Back', 'zskeleton' ) ) . '</button>';
			echo '<button type="button" class="button zs-form__btn zs-form__btn--next button-primary" data-zs-next>' . esc_html( __( 'Next', 'zskeleton' ) ) . '</button>';
			echo '<button type="submit" class="button zs-form__btn zs-form__btn--submit button-primary" data-zs-submit hidden>' . esc_html( __( 'Submit', 'zskeleton' ) ) . '</button>';
		} else {
			echo '<button type="submit" class="button zs-form__btn zs-form__btn--submit button-primary">' . esc_html( __( 'Submit', 'zskeleton' ) ) . '</button>';
		}
		echo '</div>';
	}

	/**
	 * @param ZSkeleton_Form_Definition $definition Definition.
	 */
	private static function render_wizard_progress( ZSkeleton_Form_Definition $definition ) {
		$meta = $definition->get_step_meta();
		echo '<ol class="zs-form__progress" role="list">';
		foreach ( $meta as $i => $m ) {
			$cls = 0 === (int) $i ? ' is-active' : '';
			echo '<li class="zs-form__progress-item' . esc_attr( $cls ) . '" data-zs-progress-step="' . esc_attr( (string) $i ) . '">';
			echo '<span class="zs-form__progress-num">' . esc_html( (string) ( $i + 1 ) ) . '</span> ';
			echo '<span class="zs-form__progress-label">' . esc_html( isset( $m['title'] ) ? (string) $m['title'] : '' ) . '</span>';
			echo '</li>';
		}
		echo '</ol>';
	}

	/**
	 * @param array                       $field Field config.
	 * @param mixed                       $value Value.
	 * @param ZSkeleton_Field_Registry    $registry Registry.
	 * @param ZSkeleton_Form_Definition   $definition Definition.
	 * @param string                      $form_uid Form id.
	 * @return string HTML.
	 */
	public static function render_field_row( array $field, $value, ZSkeleton_Field_Registry $registry, ZSkeleton_Form_Definition $definition, $form_uid ) {
		$type = isset( $field['type'] ) ? sanitize_key( (string) $field['type'] ) : 'text';
		$name = $field['name'];
		$fid  = $form_uid . '-field-' . $name;
		$cb   = $registry->get_type( $type );
		$ctx  = $definition->get_context();

		$use_floating = ! in_array( $type, array( 'checkbox', 'checkboxes', 'radio', 'toggle', 'hidden', 'media', 'wysiwyg', 'repeater', 'group' ), true );

		$inner = '';
		if ( $cb && is_callable( $cb['render'] ) ) {
			$inner = call_user_func(
				$cb['render'],
				$field,
				$value,
				array(
					'field_id' => $fid,
					'context'  => $ctx,
					'form_id'  => $definition->get_id(),
					'floating' => $use_floating,
				)
			);
		}
		$label_text   = isset( $field['label'] ) ? (string) $field['label'] : '';
		$desc         = isset( $field['description'] ) ? (string) $field['description'] : '';

		$mod_hidden = ( 'hidden' === $type ) ? ' zs-field--hidden' : '';

		ob_start();
		echo '<div class="form-group zs-field zs-field--' . esc_attr( $type ) . esc_attr( $mod_hidden ) . '" data-zs-field="' . esc_attr( $name ) . '">';
		if ( $use_floating && '' !== $label_text ) {
			echo '<div class="zs-field__floating">';
			echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- type renderers escape.
			echo '<label class="form-label zs-field__label" for="' . esc_attr( $fid ) . '">' . esc_html( $label_text );
			if ( ! empty( $field['required'] ) ) {
				echo ' <span class="zs-field__req" aria-hidden="true">*</span>';
			}
			echo '</label></div>';
		} else {
			if ( '' !== $label_text && ! in_array( $type, array( 'checkbox', 'checkboxes', 'radio', 'toggle' ), true ) ) {
				echo '<label class="form-label zs-field__label" for="' . esc_attr( $fid ) . '">' . esc_html( $label_text );
				if ( ! empty( $field['required'] ) ) {
					echo ' <span class="zs-field__req" aria-hidden="true">*</span>';
				}
				echo '</label>';
			}
			echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		if ( '' !== $desc ) {
			echo '<p class="form-text zs-field__desc" id="' . esc_attr( $fid . '-desc' ) . '">' . esc_html( $desc ) . '</p>';
		}
		echo '<p class="form-text invalid-feedback zs-field__error" id="' . esc_attr( $fid . '-err' ) . '" hidden></p>';
		echo '</div>';
		return (string) ob_get_clean();
	}
}
