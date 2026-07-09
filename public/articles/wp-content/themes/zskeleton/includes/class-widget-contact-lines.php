<?php
/**
 * Widget: primary phone, secondary phone, and email (mailto) with icons; layout and visibility are configurable.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders contact lines from ZSkeleton contact options / theme.
 */
class ZSkeleton_Widget_Contact_Lines extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'zskeleton_contact_lines',
			__( 'ZSkeleton: Contact (phone & email)', 'zskeleton' ),
			array(
				'description' => __( 'Lists primary phone, secondary phone, and/or mailto email with icons. Choose vertical or horizontal layout and which lines to show. Values come from ZSkeleton contact settings.', 'zskeleton' ),
				'classname'   => 'widget_zskeleton_contact_lines',
			)
		);
	}

	/**
	 * @param array<string, string> $args     Sidebar args.
	 * @param array<string, string> $instance Settings.
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );

		$phone1 = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'phone' ) : '';
		$phone2 = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'phone_secondary' ) : '';
		$email  = '';
		if ( function_exists( 'zskeleton_get_contact' ) ) {
			$email = zskeleton_get_contact( 'email' );
		}
		if ( '' === trim( $email ) ) {
			$email = (string) get_option( 'zskeleton_contact_email', '' );
		}
		$email = sanitize_email( $email );

		// Use gettext for the default label; do not persist the translated string in the DB
		// so the front-end string follows the current site locale. Legacy saves with the English default are treated the same.
		$email_stored = trim( (string) ( $instance['email_link_text'] ?? '' ) );
		if ( '' === $email_stored || 'Send us an email' === $email_stored ) {
			$email_label = __( 'Send us an email', 'zskeleton' );
		} else {
			$email_label = $email_stored;
		}

		$show_primary   = ( $instance['show_phone_primary'] ?? '1' ) === '1';
		$show_secondary = ( $instance['show_phone_secondary'] ?? '1' ) === '1';
		$show_email     = ( $instance['show_email'] ?? '1' ) === '1';

		$rows = array();
		if ( $show_primary && '' !== trim( (string) $phone1 ) ) {
			$rows[] = array(
				'kind'  => 'phone-primary',
				'value' => $phone1,
				'text'  => $phone1,
				'href'  => 'tel:' . preg_replace( '/\s+/', '', (string) $phone1 ),
			);
		}
		if ( $show_secondary && '' !== trim( (string) $phone2 ) ) {
			$rows[] = array(
				'kind'  => 'phone-secondary',
				'value' => $phone2,
				'text'  => $phone2,
				'href'  => 'tel:' . preg_replace( '/\s+/', '', (string) $phone2 ),
			);
		}
		if ( $show_email && '' !== $email && is_email( $email ) ) {
			$rows[] = array(
				'kind'  => 'email',
				'value' => $email,
				'text'  => $email_label,
				'href'  => 'mailto:' . $email,
			);
		}

		if ( empty( $rows ) ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$title = trim( (string) $instance['title'] );
				if ( '' !== $title ) {
					echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $title, $instance, $this->id_base ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				echo '<p class="zskeleton-contact-lines-widget__empty">';
				esc_html_e( 'Add a primary phone, secondary phone, or contact email in ZSkeleton contact settings to show items here.', 'zskeleton' );
				echo '</p>';
				echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			return;
		}

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$title = trim( (string) $instance['title'] );
		if ( '' !== $title ) {
			echo $args['before_title'] . esc_html( apply_filters( 'widget_title', $title, $instance, $this->id_base ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$is_horizontal = 'horizontal' === $this->sanitize_layout_direction( $instance['layout_direction'] ?? 'vertical' );
		$ul_classes    = array( 'zskeleton-contact-lines' );
		if ( $is_horizontal ) {
			$ul_classes[] = 'zskeleton-contact-lines--horizontal';
		}
		printf( '<ul class="%s" role="list">', esc_attr( implode( ' ', $ul_classes ) ) );

		foreach ( $rows as $row ) {
			$is_email = ( 'email' === $row['kind'] );
			$li_class = 'zskeleton-contact-lines__item zskeleton-contact-lines__item--' . sanitize_html_class( str_replace( '_', '-', (string) $row['kind'] ) );
			$text     = (string) $row['text'];
			$aria     = $is_email
				? sprintf( /* translators: %s: email address */ __( 'Send email to %s', 'zskeleton' ), $row['value'] )
				: sprintf( /* translators: %s: phone number */ __( 'Call %s', 'zskeleton' ), $row['value'] );
			printf( '<li class="%s">', esc_attr( $li_class ) );
			printf(
				'<a class="zskeleton-contact-lines__link" href="%s" aria-label="%s">',
				esc_url( $row['href'] ),
				esc_attr( $aria )
			);
			if ( $is_email ) {
				echo $this->get_svg_envelope(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			} else {
				echo $this->get_svg_phone(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			// .zskeleton-phone-digits: LTR the number only; link flex keeps natural icon + text for RTL/LTR.
			$text_class = $is_email
				? 'zskeleton-contact-lines__text'
				: 'zskeleton-contact-lines__text zskeleton-phone-digits';
			printf( '<span class="%s">%s</span>', esc_attr( $text_class ), esc_html( $text ) );
			echo '</a></li>';
		}

		echo '</ul>';

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @return string Inline SVG.
	 */
	private function get_svg_phone() {
		return '<span class="zskeleton-contact-lines__icon" aria-hidden="true"><svg class="zskeleton-contact-lines__svg" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.5 3h3l1.5 4.5L8.5 9.5c1 2.5 3.5 5 6 6l2-2.5L21 14v3a1.5 1.5 0 01-1.3 1.5C11 20 4 13 3.5 4.8A1.5 1.5 0 015 3.5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round" fill="none"/></svg></span>';
	}

	/**
	 * @return string Inline SVG.
	 */
	private function get_svg_envelope() {
		return '<span class="zskeleton-contact-lines__icon" aria-hidden="true"><svg class="zskeleton-contact-lines__svg" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6h16v12H4V6zm2 2l6 4 6-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg></span>';
	}

	/**
	 * @param array<string, string> $instance Instance.
	 */
	public function form( $instance ) {
		$instance    = wp_parse_args( (array) $instance, $this->defaults() );
		$email_value = (string) $instance['email_link_text'];
		if ( '' === trim( $email_value ) || 'Send us an email' === $email_value ) {
			$email_value = '';
		}
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title (optional):', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'layout_direction' ) ); ?>"><?php esc_html_e( 'Layout direction', 'zskeleton' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'layout_direction' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'layout_direction' ) ); ?>">
				<option value="vertical" <?php selected( (string) ( $instance['layout_direction'] ?? 'vertical' ), 'vertical' ); ?>><?php esc_html_e( 'Vertical', 'zskeleton' ); ?></option>
				<option value="horizontal" <?php selected( (string) ( $instance['layout_direction'] ?? 'vertical' ), 'horizontal' ); ?>><?php esc_html_e( 'Horizontal', 'zskeleton' ); ?></option>
			</select>
		</p>
		<fieldset class="zskeleton-widget-fieldset" style="margin:0.5em 0;padding:0.75em;border:1px solid #dcdcde;border-radius:4px">
			<legend style="padding:0 0.25em;"><?php esc_html_e( 'Show contact lines', 'zskeleton' ); ?></legend>
			<p>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_phone_primary' ) ); ?>" value="1" <?php checked( (string) ( $instance['show_phone_primary'] ?? '1' ), '1' ); ?> />
					<?php esc_html_e( 'Primary phone', 'zskeleton' ); ?>
				</label>
			</p>
			<p>
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_phone_secondary' ) ); ?>" value="1" <?php checked( (string) ( $instance['show_phone_secondary'] ?? '1' ), '1' ); ?> />
					<?php esc_html_e( 'Secondary phone', 'zskeleton' ); ?>
				</label>
			</p>
			<p style="margin-bottom:0">
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_email' ) ); ?>" value="1" <?php checked( (string) ( $instance['show_email'] ?? '1' ), '1' ); ?> />
					<?php esc_html_e( 'Email', 'zskeleton' ); ?>
				</label>
			</p>
		</fieldset>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'email_link_text' ) ); ?>"><?php esc_html_e( 'Email link text', 'zskeleton' ); ?></label>
			<input
				class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'email_link_text' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'email_link_text' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $email_value ); ?>"
				placeholder="<?php echo esc_attr( __( 'Send us an email', 'zskeleton' ) ); ?>"
			/>
		</p>
		<p class="description">
			<?php esc_html_e( 'Phone numbers and email are read from ZSkeleton contact settings. Empty lines are hidden. Leave the email text blank to use the translatable default.', 'zskeleton' ); ?>
		</p>
		<?php
	}

	/**
	 * @param array<string, string> $new_instance New.
	 * @param array<string, string> $old_instance Old.
	 * @return array<string, string>
	 */
	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );
		$out                            = $this->defaults();
		$out['title']                   = sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) );
		$out['layout_direction']        = $this->sanitize_layout_direction( $new_instance['layout_direction'] ?? 'vertical' );
		$out['show_phone_primary']     = ! empty( $new_instance['show_phone_primary'] ) ? '1' : '0';
		$out['show_phone_secondary']   = ! empty( $new_instance['show_phone_secondary'] ) ? '1' : '0';
		$out['show_email']              = ! empty( $new_instance['show_email'] ) ? '1' : '0';
		$out['email_link_text']         = sanitize_text_field( (string) ( $new_instance['email_link_text'] ?? '' ) );
		return $out;
	}

	/**
	 * @return array<string, string>
	 */
	private function defaults() {
		return array(
			'title'                 => '',
			'layout_direction'      => 'vertical',
			'show_phone_primary'    => '1',
			'show_phone_secondary'  => '1',
			'show_email'            => '1',
			'email_link_text'       => '',
		);
	}

	/**
	 * @param mixed $v Raw.
	 * @return string "vertical" or "horizontal"
	 */
	private function sanitize_layout_direction( $v ) {
		$v = (string) $v;
		return 'horizontal' === $v ? 'horizontal' : 'vertical';
	}
}
