<?php
/**
 * Shared markup for modern contact pages (theme + SEO kit).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Contact page meta from Customizer (filterable).
 *
 * @return array{phone:string,phone_secondary:string,address:string,hours:string,map_url:string,subtitle:string}
 */
function zskeleton_get_contact_page_meta() {
	$page_id = (int) get_queried_object_id();

	$defaults = array(
		'phone'             => '',
		'phone_secondary'   => '',
		'address'           => '',
		'hours'             => '',
		'map_url'           => '',
		'subtitle'          => __( 'We typically reply within one business day.', 'zskeleton' ),
	);
	$phone_primary   = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'phone' ) : (string) get_theme_mod( 'zskeleton_contact_phone', '' );
	$phone_secondary = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'phone_secondary' ) : '';
	$meta            = array(
		'phone'             => (string) $phone_primary,
		'phone_secondary'   => (string) $phone_secondary,
		'address'           => (string) get_theme_mod( 'zskeleton_contact_address', '' ),
		'hours'             => (string) get_theme_mod( 'zskeleton_contact_hours', '' ),
		'map_url'           => (string) get_theme_mod( 'zskeleton_contact_map_url', '' ),
		'subtitle'          => (string) get_theme_mod( 'zskeleton_contact_subtitle', $defaults['subtitle'] ),
	);

	// Page meta overrides for the contact template fields.
	if ( $page_id > 0 && 'page-contact.php' === (string) get_page_template_slug( $page_id ) ) {
		$overrides = array(
			'phone'              => (string) get_post_meta( $page_id, '_zskeleton_contact_phone', true ),
			'phone_secondary'    => (string) get_post_meta( $page_id, '_zskeleton_contact_phone_secondary', true ),
			'address'            => (string) get_post_meta( $page_id, '_zskeleton_contact_address', true ),
			'hours'              => (string) get_post_meta( $page_id, '_zskeleton_contact_hours', true ),
			'map_url'            => (string) get_post_meta( $page_id, '_zskeleton_contact_map_url', true ),
			'subtitle'           => (string) get_post_meta( $page_id, '_zskeleton_contact_subtitle', true ),
		);

		foreach ( array( 'phone', 'phone_secondary', 'address', 'hours', 'map_url', 'subtitle' ) as $key ) {
			if ( isset( $overrides[ $key ] ) && '' !== trim( $overrides[ $key ] ) ) {
				$meta[ $key ] = $overrides[ $key ];
			}
		}
	}

	/**
	 * Filter contact page meta (Customizer + overrides).
	 *
	 * @param array $meta Keys: phone, phone_secondary, address, hours, map_url, subtitle.
	 */
	return apply_filters( 'zskeleton_contact_page_meta', $meta );
}

/**
 * Add Contact page template meta box.
 *
 * Fields show only when the page uses `page-contact.php`.
 */
function zskeleton_add_contact_page_metabox() {
	global $post;

	if ( ! ( $post instanceof WP_Post ) ) {
		return;
	}

	// Only show fields after the page has been saved with the template selected.
	$template_slug = (string) get_page_template_slug( $post->ID );
	if ( 'page-contact.php' !== $template_slug ) {
		return;
	}

	add_meta_box(
		'zskeleton_contact_page_metabox',
		__( 'Contact page', 'zskeleton' ),
		'zskeleton_render_contact_page_metabox',
		'page',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_page', 'zskeleton_add_contact_page_metabox' );

/**
 * Render meta box fields for the contact page template.
 *
 * @param WP_Post $post Page being edited.
 */
function zskeleton_render_contact_page_metabox( $post ) {
	$template_slug = (string) get_page_template_slug( $post->ID );
	if ( 'page-contact.php' !== $template_slug ) {
		return;
	}

	wp_nonce_field( 'zskeleton_save_contact_page_meta', 'zskeleton_contact_page_meta_nonce' );

	$phone    = get_post_meta( $post->ID, '_zskeleton_contact_phone', true );
	$phone_2  = get_post_meta( $post->ID, '_zskeleton_contact_phone_secondary', true );
	$address  = get_post_meta( $post->ID, '_zskeleton_contact_address', true );
	$hours    = get_post_meta( $post->ID, '_zskeleton_contact_hours', true );
	$map_url  = get_post_meta( $post->ID, '_zskeleton_contact_map_url', true );
	$subtitle = get_post_meta( $post->ID, '_zskeleton_contact_subtitle', true );

	$defaults = array(
		'subtitle' => __( 'We typically reply within one business day.', 'zskeleton' ),
	);
	$subtitle = '' === (string) $subtitle ? (string) $defaults['subtitle'] : (string) $subtitle;

	?>
	<div class="zs-meta-fields">
		<div class="zs-meta-field">
			<label class="zs-meta-field__label" for="zskeleton_contact_subtitle_ed"><?php esc_html_e( 'Contact page subtitle (under title)', 'zskeleton' ); ?></label>
			<?php
			if ( function_exists( 'zskeleton_render_meta_wysiwyg' ) ) {
				zskeleton_render_meta_wysiwyg(
					'zskeleton_contact_subtitle_ed',
					'zskeleton_contact_subtitle',
					(string) $subtitle,
					array( 'textarea_rows' => 4 )
				);
			} else {
				?>
				<textarea
					id="zskeleton_contact_subtitle"
					name="zskeleton_contact_subtitle"
					class="widefat"
					rows="3"
				><?php echo esc_textarea( (string) $subtitle ); ?></textarea>
				<?php
			}
			?>
			<p class="zs-meta-field__hint"><?php esc_html_e( 'Shown below the page title on the modern contact template.', 'zskeleton' ); ?></p>
		</div>

		<div class="zs-meta-field">
			<label class="zs-meta-field__label" for="zskeleton_contact_phone"><?php esc_html_e( 'Phone (displayed on contact page)', 'zskeleton' ); ?></label>
			<input
				type="text"
				id="zskeleton_contact_phone"
				name="zskeleton_contact_phone"
				class="widefat"
				value="<?php echo esc_attr( (string) $phone ); ?>"
				autocomplete="tel"
			/>
		</div>

		<div class="zs-meta-field">
			<label class="zs-meta-field__label" for="zskeleton_contact_phone_secondary"><?php esc_html_e( 'Secondary phone (optional)', 'zskeleton' ); ?></label>
			<input
				type="text"
				id="zskeleton_contact_phone_secondary"
				name="zskeleton_contact_phone_secondary"
				class="widefat"
				value="<?php echo esc_attr( (string) $phone_2 ); ?>"
				autocomplete="tel"
			/>
		</div>

		<div class="zs-meta-field">
			<label class="zs-meta-field__label" for="zskeleton_contact_address_ed"><?php esc_html_e( 'Address', 'zskeleton' ); ?></label>
			<?php
			if ( function_exists( 'zskeleton_render_meta_wysiwyg' ) ) {
				zskeleton_render_meta_wysiwyg(
					'zskeleton_contact_address_ed',
					'zskeleton_contact_address',
					(string) $address,
					array( 'textarea_rows' => 5 )
				);
			} else {
				?>
				<textarea
					id="zskeleton_contact_address"
					name="zskeleton_contact_address"
					class="widefat"
					rows="4"
				><?php echo esc_textarea( (string) $address ); ?></textarea>
				<?php
			}
			?>
		</div>

		<div class="zs-meta-field">
			<label class="zs-meta-field__label" for="zskeleton_contact_hours_ed"><?php esc_html_e( 'Business hours', 'zskeleton' ); ?></label>
			<?php
			if ( function_exists( 'zskeleton_render_meta_wysiwyg' ) ) {
				zskeleton_render_meta_wysiwyg(
					'zskeleton_contact_hours_ed',
					'zskeleton_contact_hours',
					(string) $hours,
					array( 'textarea_rows' => 4 )
				);
			} else {
				?>
				<textarea
					id="zskeleton_contact_hours"
					name="zskeleton_contact_hours"
					class="widefat"
					rows="3"
				><?php echo esc_textarea( (string) $hours ); ?></textarea>
				<?php
			}
			?>
		</div>

		<div class="zs-meta-field">
			<label class="zs-meta-field__label" for="zskeleton_contact_map_url"><?php esc_html_e( 'Map link (Google Maps / OpenStreetMap URL)', 'zskeleton' ); ?></label>
			<input
				type="url"
				id="zskeleton_contact_map_url"
				name="zskeleton_contact_map_url"
				class="widefat"
				value="<?php echo esc_attr( (string) $map_url ); ?>"
				placeholder="https://"
			/>
		</div>
	</div>
	<?php
}

/**
 * Save contact page meta box fields.
 *
 * @param int $post_id Post ID.
 */
function zskeleton_save_contact_page_metabox( $post_id ) {
	// Autosaves / revisions do not need meta updates.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( ! isset( $_POST['zskeleton_contact_page_meta_nonce'] ) ) {
		return;
	}

	$nonce = (string) wp_unslash( $_POST['zskeleton_contact_page_meta_nonce'] );
	if ( ! wp_verify_nonce( $nonce, 'zskeleton_save_contact_page_meta' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$template_slug = (string) get_page_template_slug( $post_id );
	if ( 'page-contact.php' !== $template_slug ) {
		// Template isn't selected; avoid writing these values.
		return;
	}

	$phone    = isset( $_POST['zskeleton_contact_phone'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['zskeleton_contact_phone'] ) ) : '';
	$phone_2  = isset( $_POST['zskeleton_contact_phone_secondary'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['zskeleton_contact_phone_secondary'] ) ) : '';
	$subtitle = isset( $_POST['zskeleton_contact_subtitle'] ) ? wp_kses_post( wp_unslash( (string) $_POST['zskeleton_contact_subtitle'] ) ) : '';
	$address  = isset( $_POST['zskeleton_contact_address'] ) ? wp_kses_post( wp_unslash( (string) $_POST['zskeleton_contact_address'] ) ) : '';
	$hours    = isset( $_POST['zskeleton_contact_hours'] ) ? wp_kses_post( wp_unslash( (string) $_POST['zskeleton_contact_hours'] ) ) : '';
	$map_url  = isset( $_POST['zskeleton_contact_map_url'] ) ? esc_url_raw( wp_unslash( (string) $_POST['zskeleton_contact_map_url'] ) ) : '';

	update_post_meta( $post_id, '_zskeleton_contact_phone', $phone );
	update_post_meta( $post_id, '_zskeleton_contact_phone_secondary', $phone_2 );
	update_post_meta( $post_id, '_zskeleton_contact_subtitle', $subtitle );
	update_post_meta( $post_id, '_zskeleton_contact_address', $address );
	update_post_meta( $post_id, '_zskeleton_contact_hours', $hours );
	update_post_meta( $post_id, '_zskeleton_contact_map_url', $map_url );
}
add_action( 'save_post_page', 'zskeleton_save_contact_page_metabox' );

/**
 * Whether the current view is the theme contact page (template or /contact/ slug).
 *
 * @return bool
 */
function zskeleton_is_contact_page_view() {
	if ( ! is_page() ) {
		return false;
	}
	if ( is_page_template( 'page-contact.php' ) ) {
		return true;
	}
	$obj = get_queried_object();
	return $obj instanceof WP_Post && 'contact' === $obj->post_name;
}

/**
 * Register contact page stylesheet (dependency-safe).
 *
 * @return void
 */
function zskeleton_enqueue_contact_page_stylesheets() {
	$use_min = (bool) get_option( 'zskeleton_use_minified_assets', true );
	$file    = $use_min && is_readable( ZSkeleton_THEME_DIR . '/assets/css/page-contact.min.css' ) ? 'page-contact.min.css' : 'page-contact.css';
	$path    = ZSkeleton_THEME_DIR . '/assets/css/' . $file;
	$ver     = is_readable( $path ) ? (string) filemtime( $path ) : ZSkeleton_VERSION;
	wp_enqueue_style(
		'zskeleton-page-contact',
		ZSkeleton_THEME_URL . '/assets/css/' . $file,
		array( 'zskeleton-style', 'zskeleton-components' ),
		$ver
	);
}

/**
 * Enqueue contact page stylesheet when the theme contact template or /contact/ page is shown.
 */
function zskeleton_enqueue_contact_page_styles() {
	if ( ! zskeleton_is_contact_page_view() ) {
		return;
	}
	zskeleton_enqueue_contact_page_stylesheets();
}
add_action( 'wp_enqueue_scripts', 'zskeleton_enqueue_contact_page_styles', 26 );

/**
 * Enqueue contact layout CSS from plugins (e.g. SEO Agency Kit contact template).
 *
 * @return void
 */
function zskeleton_enqueue_contact_page_styles_forced() {
	zskeleton_enqueue_contact_page_stylesheets();
}

/**
 * Register Customizer fields for the contact page.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function zskeleton_contact_page_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'zskeleton_contact_page',
		array(
			'title'       => __( 'Contact page', 'zskeleton' ),
			'description' => __( 'Primary and secondary phone numbers are set under Appearance → Customize → Contact & social (or ZSkeleton Settings → General).', 'zskeleton' ),
			'priority'    => 45,
		)
	);

	$text = array( 'sanitize_callback' => 'sanitize_text_field' );
	$area = array( 'sanitize_callback' => 'sanitize_textarea_field' );
	$url  = array( 'sanitize_callback' => 'esc_url_raw' );

	$wp_customize->add_setting( 'zskeleton_contact_subtitle', array_merge( $area, array( 'default' => __( 'We typically reply within one business day.', 'zskeleton' ) ) ) );
	$wp_customize->add_control(
		'zskeleton_contact_subtitle',
		array(
			'label'   => __( 'Contact page subtitle (under title)', 'zskeleton' ),
			'section' => 'zskeleton_contact_page',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting( 'zskeleton_contact_address', $area );
	$wp_customize->add_control(
		'zskeleton_contact_address',
		array(
			'label'   => __( 'Address', 'zskeleton' ),
			'section' => 'zskeleton_contact_page',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting( 'zskeleton_contact_hours', $area );
	$wp_customize->add_control(
		'zskeleton_contact_hours',
		array(
			'label'   => __( 'Business hours', 'zskeleton' ),
			'section' => 'zskeleton_contact_page',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting( 'zskeleton_contact_map_url', $url );
	$wp_customize->add_control(
		'zskeleton_contact_map_url',
		array(
			'label'       => __( 'Map link (Google Maps / OpenStreetMap URL)', 'zskeleton' ),
			'description' => __( 'Opens in a new tab from the contact page.', 'zskeleton' ),
			'section'     => 'zskeleton_contact_page',
			'type'        => 'url',
		)
	);
}
add_action( 'customize_register', 'zskeleton_contact_page_customize_register' );

/**
 * Render the fancy contact layout + Form Kit form.
 *
 * @param array<string,mixed> $args {
 *     @type string   $intro_html     Optional HTML intro (already escaped for kses).
 *     @type string   $email_override Optional public email (else theme option).
 *     @type string   $phone          Phone line.
 *     @type string   $phone_secondary Optional second phone.
 *     @type string   $address        Multi-line address.
 *     @type string   $hours          Business hours.
 *     @type string   $map_url        Map URL.
 *     @type callable|null $form_render Callback that echoes form HTML, or null for default Form Kit.
 * }
 * @return void
 */
function zskeleton_render_contact_page_layout( array $args = array() ) {
	$defaults = zskeleton_get_contact_page_meta();
	$intro    = isset( $args['intro_html'] ) ? (string) $args['intro_html'] : '';

	$email = '';
	if ( isset( $args['email_override'] ) && '' !== $args['email_override'] ) {
		$email = sanitize_email( (string) $args['email_override'] );
	}
	if ( '' === $email ) {
		$email = sanitize_email( get_option( 'zskeleton_contact_email', get_option( 'admin_email' ) ) );
	}

	$phone   = ( isset( $args['phone'] ) && '' !== $args['phone'] ) ? (string) $args['phone'] : $defaults['phone'];
	$phone_secondary = ( isset( $args['phone_secondary'] ) && '' !== $args['phone_secondary'] ) ? (string) $args['phone_secondary'] : $defaults['phone_secondary'];
	$address = ( isset( $args['address'] ) && '' !== $args['address'] ) ? (string) $args['address'] : $defaults['address'];
	$hours   = ( isset( $args['hours'] ) && '' !== $args['hours'] ) ? (string) $args['hours'] : $defaults['hours'];
	$map_url = ( isset( $args['map_url'] ) && '' !== $args['map_url'] ) ? esc_url_raw( (string) $args['map_url'] ) : $defaults['map_url'];

	$form_cb = isset( $args['form_render'] ) ? $args['form_render'] : null;
	?>
	<div class="zs-contact-page">
		<?php if ( '' !== $intro ) : ?>
			<div class="zs-contact-page__intro formal-card bordered">
				<div class="zs-contact-page__intro-inner">
					<?php echo $intro; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- caller passes kses'd HTML. ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="zs-contact-page__grid">
			<?php if ( '' !== $email ) : ?>
				<a class="zs-contact-card formal-card" href="<?php echo esc_url( 'mailto:' . $email ); ?>">
					<span class="zs-contact-card__icon" aria-hidden="true">
						<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 6h16v12H4V6zm2 2l6 4 6-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
					</span>
					<span class="zs-contact-card__label"><?php esc_html_e( 'Email', 'zskeleton' ); ?></span>
					<span class="zs-contact-card__value"><?php echo esc_html( $email ); ?></span>
				</a>
			<?php endif; ?>

			<?php if ( '' !== $phone ) : ?>
				<a class="zs-contact-card formal-card" href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', $phone ) ); ?>">
					<span class="zs-contact-card__icon" aria-hidden="true">
						<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.5 3h3l1.5 4.5L8.5 9.5c1 2.5 3.5 5 6 6l2-2.5L21 14v3a1.5 1.5 0 01-1.3 1.5C11 20 4 13 3.5 4.8A1.5 1.5 0 015 3.5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
					</span>
					<span class="zs-contact-card__label"><?php esc_html_e( 'Phone', 'zskeleton' ); ?></span>
					<span class="zs-contact-card__value zskeleton-phone-digits"><?php echo esc_html( $phone ); ?></span>
				</a>
			<?php endif; ?>

			<?php if ( '' !== $phone_secondary ) : ?>
				<a class="zs-contact-card formal-card" href="<?php echo esc_url( 'tel:' . preg_replace( '/\s+/', '', $phone_secondary ) ); ?>">
					<span class="zs-contact-card__icon" aria-hidden="true">
						<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.5 3h3l1.5 4.5L8.5 9.5c1 2.5 3.5 5 6 6l2-2.5L21 14v3a1.5 1.5 0 01-1.3 1.5C11 20 4 13 3.5 4.8A1.5 1.5 0 015 3.5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
					</span>
					<span class="zs-contact-card__label"><?php esc_html_e( 'Secondary phone', 'zskeleton' ); ?></span>
					<span class="zs-contact-card__value zskeleton-phone-digits"><?php echo esc_html( $phone_secondary ); ?></span>
				</a>
			<?php endif; ?>

			<?php if ( '' !== $address ) : ?>
				<div class="zs-contact-card formal-card zs-contact-card--static">
					<span class="zs-contact-card__icon" aria-hidden="true">
						<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 21s7-4.6 7-11a7 7 0 10-14 0c0 6.4 7 11 7 11z" stroke="currentColor" stroke-width="1.4"/><circle cx="12" cy="10" r="2.2" stroke="currentColor" stroke-width="1.4"/></svg>
					</span>
					<span class="zs-contact-card__label"><?php esc_html_e( 'Studio / office', 'zskeleton' ); ?></span>
					<span class="zs-contact-card__value"><?php echo wp_kses_post( wpautop( $address ) ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $hours ) : ?>
				<div class="zs-contact-card formal-card zs-contact-card--static">
					<span class="zs-contact-card__icon" aria-hidden="true">
						<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.4"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
					</span>
					<span class="zs-contact-card__label"><?php esc_html_e( 'Hours', 'zskeleton' ); ?></span>
					<span class="zs-contact-card__value"><?php echo wp_kses_post( wpautop( $hours ) ); ?></span>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $map_url ) : ?>
				<a class="zs-contact-card formal-card zs-contact-card--map" href="<?php echo esc_url( $map_url ); ?>" target="_blank" rel="noopener noreferrer">
					<span class="zs-contact-card__icon" aria-hidden="true">
						<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l8 4v6c0 5-8 10-8 10S4 17 4 12V6l8-4z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/></svg>
					</span>
					<span class="zs-contact-card__label"><?php esc_html_e( 'Map & directions', 'zskeleton' ); ?></span>
					<span class="zs-contact-card__value"><?php esc_html_e( 'Open in maps', 'zskeleton' ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<div class="zs-contact-page__form-wrap formal-card elevated">
			<div class="zs-contact-page__form-header">
				<h2 class="zs-contact-page__form-title"><?php esc_html_e( 'Send a message', 'zskeleton' ); ?></h2>
				<p class="zs-contact-page__form-lead"><?php esc_html_e( 'Share a few details and we’ll route your request to the right person.', 'zskeleton' ); ?></p>
			</div>
			<div class="zs-contact-page__form-inner">
				<?php
				if ( is_callable( $form_cb ) ) {
					call_user_func( $form_cb );
				} elseif ( function_exists( 'zskeleton_render_form' ) ) {
					zskeleton_render_form( 'zskeleton_contact' );
				} else {
					echo '<p class="zs-contact-page__fallback">' . esc_html__( 'Contact form is unavailable.', 'zskeleton' ) . '</p>';
				}
				?>
			</div>
		</div>
	</div>
	<?php
}
