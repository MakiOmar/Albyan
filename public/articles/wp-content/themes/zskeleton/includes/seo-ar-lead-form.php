<?php
/**
 * Lead form column for Arabic SEO homepage (Gravity Forms, shortcode, or mail fallback).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Output the right column: custom HTML, shortcode, Gravity Forms, or fallback POST form.
 *
 * @return void
 */
function zskeleton_seo_ar_render_lead_form_column() {
	$custom = apply_filters( 'zskeleton_landing_lead_form_html', '' );
	if ( is_string( $custom ) && '' !== $custom ) {
		echo $custom; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filter for full form markup.
		return;
	}

	$custom = apply_filters( 'zskeleton_seo_ar_lead_form_html', '' );
	if ( is_string( $custom ) && '' !== $custom ) {
		echo $custom; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filter for full form markup.
		return;
	}

	$shortcode = (string) get_theme_mod( 'zskeleton_seo_ar_lead_form_shortcode', '' );
	if ( '' !== $shortcode ) {
		echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode output.
		return;
	}

	$gf_id = (int) apply_filters( 'zskeleton_seo_ar_lead_gravity_form_id', 0 );
	if ( $gf_id > 0 && function_exists( 'gravity_form' ) ) {
		gravity_form( $gf_id, false, false, false, null, true, 12 );
		return;
	}

	zskeleton_seo_ar_lead_form_fallback();
}

/**
 * Minimal styled fallback form (posts to admin-post, emails admin).
 *
 * @return void
 */
function zskeleton_seo_ar_lead_form_fallback() {
	$action = admin_url( 'admin-post.php' );
	?>
	<form class="seo-ar-lead-form-fallback" method="post" action="<?php echo esc_url( $action ); ?>" novalidate>
		<?php wp_nonce_field( 'zskeleton_seo_ar_lead', 'zskeleton_seo_ar_lead_nonce' ); ?>
		<input type="hidden" name="action" value="zskeleton_seo_ar_lead" />
		<p class="seo-ar-lead-form-fallback__field">
			<label for="seo-ar-lead-name"><?php echo esc_html( 'الاسم' ); ?> <span class="seo-ar-req" aria-hidden="true">*</span></label>
			<input type="text" id="seo-ar-lead-name" name="lead_name" required autocomplete="name" placeholder="<?php echo esc_attr( 'الاسم*' ); ?>" />
		</p>
		<p class="seo-ar-lead-form-fallback__field">
			<label for="seo-ar-lead-email"><?php echo esc_html( 'البريد الإلكتروني' ); ?> <span class="seo-ar-req" aria-hidden="true">*</span></label>
			<input type="email" id="seo-ar-lead-email" name="lead_email" required autocomplete="email" placeholder="<?php echo esc_attr( 'البريد الإلكتروني*' ); ?>" />
		</p>
		<p class="seo-ar-lead-form-fallback__field seo-ar-lead-form-fallback__field--phone">
			<span class="seo-ar-lead-form-fallback__phone-label"><?php echo esc_html( 'الهاتف' ); ?> <span class="seo-ar-req" aria-hidden="true">*</span></span>
			<span class="seo-ar-lead-form-fallback__phone-row">
				<label class="screen-reader-text" for="seo-ar-lead-cc"><?php echo esc_html( 'رمز الدولة' ); ?></label>
				<select id="seo-ar-lead-cc" name="lead_country_code" aria-label="<?php echo esc_attr( 'رمز الاتصال الدولي' ); ?>">
					<?php
					$codes = array(
						'+20'  => 'مصر (+20)',
						'+966' => 'السعودية (+966)',
						'+971' => 'الإمارات (+971)',
						'+1'   => 'الولايات المتحدة (+1)',
						'+44'  => 'المملكة المتحدة (+44)',
					);
					foreach ( $codes as $code => $label ) {
						echo '<option value="' . esc_attr( $code ) . '"' . selected( $code, '+20', false ) . '>' . esc_html( $label ) . '</option>';
					}
					?>
				</select>
				<input type="tel" id="seo-ar-lead-phone" name="lead_phone" required autocomplete="tel" placeholder="<?php echo esc_attr( 'رقم الهاتف' ); ?>" />
			</span>
		</p>
		<p class="seo-ar-lead-form-fallback__field">
			<label for="seo-ar-lead-msg"><?php echo esc_html( 'الرسالة' ); ?> <span class="seo-ar-req" aria-hidden="true">*</span></label>
			<textarea id="seo-ar-lead-msg" name="lead_message" required rows="5" cols="40" placeholder="<?php echo esc_attr( 'الرسالة*' ); ?>"></textarea>
		</p>

		<?php
		// Bot protection: Turnstile / Google reCAPTCHA v3 (theme settings).
		if ( class_exists( 'ZSkeleton_ReCAPTCHA' ) && function_exists( 'zskeleton_recaptcha' ) ) {
			$captcha = zskeleton_recaptcha();
			if ( $captcha && $captcha->is_enabled() ) {
				$captcha->enqueue_scripts();
				echo '<div class="seo-ar-lead-form-fallback__captcha">';
				$captcha->render_field( 'seo_ar_lead' );
				echo '</div>';
			}
		}
		?>

		<p class="seo-ar-lead-form-fallback__field seo-ar-lead-form-fallback__field--hp" aria-hidden="true">
			<label for="seo-ar-lead-hp"><?php echo esc_html( 'موقع الشركة' ); ?></label>
			<input type="text" id="seo-ar-lead-hp" name="lead_honeypot" value="" tabindex="-1" autocomplete="off" />
		</p>
		<p class="seo-ar-lead-form-fallback__field seo-ar-lead-form-fallback__field--consent">
			<label class="seo-ar-lead-form-fallback__check">
				<input type="checkbox" name="lead_consent" value="1" />
				<span><?php echo esc_html( sprintf( 'أرغب في تلقي نصائح نمو رقمي من الخبراء من %s.', get_bloginfo( 'name' ) ) ); ?></span>
			</label>
		</p>
		<p class="seo-ar-lead-form-fallback__submit-wrap">
			<button type="submit" class="seo-ar-lead-form-fallback__submit"><?php echo esc_html( 'إرسال' ); ?></button>
		</p>
	</form>
	<?php
}

/**
 * Handle fallback lead form POST.
 *
 * @return void
 */
function zskeleton_seo_ar_handle_lead_form() {
	if ( ! isset( $_POST['zskeleton_seo_ar_lead_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zskeleton_seo_ar_lead_nonce'] ) ), 'zskeleton_seo_ar_lead' ) ) {
		wp_die( esc_html( 'فشل التحقق الأمني.' ), '', array( 'response' => 403 ) );
	}

	$honeypot = isset( $_POST['lead_honeypot'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_honeypot'] ) ) : '';
	if ( '' !== $honeypot ) {
		$ref = wp_get_referer();
		if ( ! is_string( $ref ) || '' === $ref ) {
			$ref = home_url( '/' );
		}
		wp_safe_redirect( $ref );
		exit;
	}

	// Bot protection: enforce server-side verification when configured in theme settings.
	if ( class_exists( 'ZSkeleton_ReCAPTCHA' ) && function_exists( 'zskeleton_recaptcha' ) ) {
		$captcha = zskeleton_recaptcha();
		if ( $captcha && $captcha->is_enabled() ) {
			$verification = $captcha->verify_form_submission();
			if ( is_wp_error( $verification ) ) {
				$ref = wp_get_referer();
				if ( ! is_string( $ref ) || '' === $ref ) {
					$ref = home_url( '/' );
				}
				wp_safe_redirect( add_query_arg( 'lead', 'error', $ref ) );
				exit;
			}
		}
	}

	$name    = isset( $_POST['lead_name'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_name'] ) ) : '';
	$email   = isset( $_POST['lead_email'] ) ? sanitize_email( wp_unslash( $_POST['lead_email'] ) ) : '';
	$cc_raw  = isset( $_POST['lead_country_code'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_country_code'] ) ) : '+20';
	$cc      = preg_replace( '/[^0-9+]/', '', (string) $cc_raw );
	$phone   = isset( $_POST['lead_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['lead_phone'] ) ) : '';
	$message = isset( $_POST['lead_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['lead_message'] ) ) : '';

	if ( '' === $name || '' === $email || ! is_email( $email ) || '' === $phone || '' === $message ) {
		$ref = wp_get_referer();
		if ( ! is_string( $ref ) || '' === $ref ) {
			$ref = home_url( '/' );
		}
		wp_safe_redirect( add_query_arg( 'lead', 'error', $ref ) );
		exit;
	}

	$full_phone = trim( $cc . ' ' . $phone );
	$to         = get_option( 'admin_email' );
	$subject    = sprintf( '[%s] %s', get_bloginfo( 'name' ), 'عميل محتمل جديد من صفحة السيو الرئيسية' );
	$body       = sprintf(
		"%s: %s\n%s: %s\n%s: %s\n%s: %s\n",
		'الاسم',
		$name,
		'البريد الإلكتروني',
		$email,
		'الهاتف',
		$full_phone,
		'الرسالة',
		$message
	);

	wp_mail( $to, $subject, $body );

	$ref = wp_get_referer();
	if ( ! is_string( $ref ) || '' === $ref ) {
		$ref = home_url( '/' );
	}
	wp_safe_redirect( add_query_arg( 'lead', 'sent', $ref ) );
	exit;
}
add_action( 'admin_post_nopriv_zskeleton_seo_ar_lead', 'zskeleton_seo_ar_handle_lead_form' );
add_action( 'admin_post_zskeleton_seo_ar_lead', 'zskeleton_seo_ar_handle_lead_form' );
