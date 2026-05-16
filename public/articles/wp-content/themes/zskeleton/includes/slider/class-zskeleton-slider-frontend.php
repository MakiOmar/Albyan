<?php
/**
 * Slider shortcode, asset loading, and HTML renderer.
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front output + shortcode [zskeleton_slider].
 */
class ZSkeleton_Slider_Frontend {

	/**
	 * Whether assets were queued this request.
	 *
	 * @var bool
	 */
	private static $assets_enqueued = false;

	/**
	 * Bootstrap hooks.
	 */
	public static function init() {
		add_shortcode( 'zskeleton_slider', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_from_content' ), 25 );
	}

	/**
	 * Render a published slider by ID or slug (for templates).
	 *
	 * @param int|string $id_or_slug Post ID or post_name.
	 * @param string     $extra_class Optional root CSS class.
	 * @return string
	 */
	public static function render( $id_or_slug, $extra_class = '' ) {
		self::enqueue_assets();
		$post = ZSkeleton_Sliders::get_slider_post( $id_or_slug );
		if ( ! $post ) {
			return '';
		}
		return self::render_slider_html( $post, sanitize_html_class( $extra_class ) );
	}

	/**
	 * Enqueue slider CSS/JS once (safe to call multiple times).
	 */
	public static function enqueue_assets() {
		if ( self::$assets_enqueued ) {
			return;
		}
		self::$assets_enqueued = true;

		$use_min = (bool) get_option( 'zskeleton_use_minified_assets', true );
		$css     = ( $use_min && is_readable( ZSkeleton_THEME_DIR . '/assets/css/slider.min.css' ) ) ? 'slider.min.css' : 'slider.css';
		$js      = ( $use_min && is_readable( ZSkeleton_THEME_DIR . '/assets/js/slider.min.js' ) ) ? 'slider.min.js' : 'slider.js';
		$css_ver = is_readable( ZSkeleton_THEME_DIR . '/assets/css/' . $css ) ? (string) filemtime( ZSkeleton_THEME_DIR . '/assets/css/' . $css ) : ZSkeleton_VERSION;
		$js_ver  = is_readable( ZSkeleton_THEME_DIR . '/assets/js/' . $js ) ? (string) filemtime( ZSkeleton_THEME_DIR . '/assets/js/' . $js ) : ZSkeleton_VERSION;

		// Main theme handle is not registered in the block editor; avoid a missing-dep edge case there.
		$slider_style_deps = array();
		if ( wp_style_is( 'zskeleton-style', 'registered' ) ) {
			$slider_style_deps[] = 'zskeleton-style';
		}

		wp_enqueue_style(
			'zskeleton-slider',
			ZSkeleton_THEME_URL . '/assets/css/' . $css,
			$slider_style_deps,
			$css_ver
		);

		wp_enqueue_script(
			'zskeleton-slider',
			ZSkeleton_THEME_URL . '/assets/js/' . $js,
			array(),
			$js_ver,
			true
		);

		wp_localize_script(
			'zskeleton-slider',
			'zskeletonSliderStrings',
			array(
				'prev' => __( 'Previous slide', 'zskeleton' ),
				'next' => __( 'Next slide', 'zskeleton' ),
				'goto' => __( 'Go to slide', 'zskeleton' ),
			)
		);
	}

	/**
	 * Pre-enqueue when main singular content likely contains the shortcode.
	 */
	public static function maybe_enqueue_from_content() {
		if ( is_admin() || ! is_singular() ) {
			return;
		}
		global $post;
		if ( ! $post instanceof WP_Post ) {
			return;
		}
		$content = (string) $post->post_content;
		if ( has_shortcode( $content, 'zskeleton_slider' ) || has_block( 'zskeleton/theme-slider', $post ) ) {
			self::enqueue_assets();
		}
	}

	/**
	 * Shortcode handler.
	 *
	 * @param array<string,string>|string $atts Attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		self::enqueue_assets();

		$atts = shortcode_atts(
			array(
				'id'    => '',
				'slug'  => '',
				'class' => '',
			),
			$atts,
			'zskeleton_slider'
		);

		$post = null;
		if ( $atts['id'] !== '' && is_numeric( $atts['id'] ) ) {
			$post = ZSkeleton_Sliders::get_slider_post( (int) $atts['id'] );
		}
		if ( ! $post && $atts['slug'] !== '' ) {
			$post = ZSkeleton_Sliders::get_slider_post( $atts['slug'] );
		}

		if ( ! $post ) {
			return '';
		}

		$extra_class = sanitize_html_class( (string) $atts['class'] );
		return self::render_slider_html( $post, $extra_class );
	}

	/**
	 * CSS custom properties for the slider root (min height, colors, overlay tint).
	 *
	 * @param int    $post_id         Slider post ID.
	 * @param string $min_height_raw Optional min-height value from meta (CSS fragment).
	 * @return string Semicolon-separated declarations (no trailing semicolon), escaped for style="" where needed.
	 */
	private static function build_slider_root_style( $post_id, $min_height_raw ) {
		$post_id = (int) $post_id;
		$parts    = array();

		$min_height_raw = trim( (string) $min_height_raw );
		if ( '' !== $min_height_raw ) {
			$parts[] = '--zs-slider-min-height: ' . esc_attr( $min_height_raw );
		}

		$title_c = get_post_meta( $post_id, ZSkeleton_Sliders::META_COLOR_TITLE, true );
		$title_h = ZSkeleton_Sliders::sanitize_slider_hex_color( is_string( $title_c ) ? $title_c : '' );
		if ( '' !== $title_h ) {
			$parts[] = '--zs-slider-title-color: ' . $title_h;
		}

		$desc_c = get_post_meta( $post_id, ZSkeleton_Sliders::META_COLOR_DESC, true );
		$desc_h = ZSkeleton_Sliders::sanitize_slider_hex_color( is_string( $desc_c ) ? $desc_c : '' );
		if ( '' !== $desc_h ) {
			$parts[] = '--zs-slider-desc-color: ' . $desc_h;
		}

		$accent = get_post_meta( $post_id, ZSkeleton_Sliders::META_COLOR_ACCENT, true );
		$accent_h = ZSkeleton_Sliders::sanitize_slider_hex_color( is_string( $accent ) ? $accent : '' );
		if ( '' !== $accent_h ) {
			$parts[] = '--zs-slider-accent: ' . $accent_h;
		}

		$overlay_hex = '#0f172a';
		$oc          = get_post_meta( $post_id, ZSkeleton_Sliders::META_OVERLAY_COLOR, true );
		$overlay_h   = ZSkeleton_Sliders::sanitize_slider_hex_color( is_string( $oc ) ? $oc : '' );
		if ( '' !== $overlay_h ) {
			$overlay_hex = $overlay_h;
		}

		$op_pct = 45;
		$oo     = get_post_meta( $post_id, ZSkeleton_Sliders::META_OVERLAY_OPACITY, true );
		if ( is_numeric( $oo ) && '' !== (string) $oo ) {
			$op_pct = min( 100, max( 0, (int) $oo ) );
		}

		$rgb   = self::hex_to_rgb_components( $overlay_hex );
		$alpha = min( 1.0, max( 0.0, $op_pct / 100 ) );
		$parts[] = sprintf(
			'--zs-slider-overlay-bg: rgba(%d,%d,%d,%s)',
			$rgb['r'],
			$rgb['g'],
			$rgb['b'],
			number_format( $alpha, 2, '.', '' )
		);

		$mh_m = get_post_meta( $post_id, ZSkeleton_Sliders::META_MIN_HEIGHT_MOBILE, true );
		$mh_m  = ZSkeleton_Sliders::sanitize_slider_css_token_value( is_string( $mh_m ) ? $mh_m : '' );
		if ( '' !== $mh_m ) {
			$parts[] = '--zs-slider-min-height-mobile: ' . esc_attr( $mh_m );
		}
		$cimh = ZSkeleton_Sliders::sanitize_slider_css_token_value( (string) get_post_meta( $post_id, ZSkeleton_Sliders::META_CONTENT_IMAGE_MAX_HEIGHT, true ) );
		if ( '' === $cimh ) {
			$cimh = '400px';
		}
		$parts[] = '--zs-slider-content-image-max-height: ' . esc_attr( $cimh );
		$cimh_m = ZSkeleton_Sliders::sanitize_slider_css_token_value( (string) get_post_meta( $post_id, ZSkeleton_Sliders::META_CONTENT_IMAGE_MAX_HEIGHT_MOBILE, true ) );
		if ( '' !== $cimh_m ) {
			$parts[] = '--zs-slider-content-image-max-height-mobile: ' . esc_attr( $cimh_m );
		}

		$nav_hex = ZSkeleton_Sliders::sanitize_slider_hex_color( (string) get_post_meta( $post_id, ZSkeleton_Sliders::META_NAV_BG_COLOR, true ) );
		if ( '' !== $nav_hex ) {
			$nav_op_pct = 14;
			$nav_op_raw = get_post_meta( $post_id, ZSkeleton_Sliders::META_NAV_BG_OPACITY, true );
			if ( is_numeric( $nav_op_raw ) && '' !== (string) $nav_op_raw ) {
				$nav_op_pct = min( 100, max( 0, (int) $nav_op_raw ) );
			}
			$parts[] = '--zs-slider-nav-bg: ' . esc_attr( self::rgba_css_from_hex_percent( $nav_hex, $nav_op_pct ) );
		}

		$nav_icon = ZSkeleton_Sliders::sanitize_slider_hex_color( (string) get_post_meta( $post_id, ZSkeleton_Sliders::META_NAV_ICON_COLOR, true ) );
		if ( '' !== $nav_icon ) {
			$parts[] = '--zs-slider-nav-color: ' . $nav_icon;
		}

		$dot_in_hex = ZSkeleton_Sliders::sanitize_slider_hex_color( (string) get_post_meta( $post_id, ZSkeleton_Sliders::META_DOT_INACTIVE_COLOR, true ) );
		if ( '' !== $dot_in_hex ) {
			$dot_op_pct = 35;
			$dot_op_raw = get_post_meta( $post_id, ZSkeleton_Sliders::META_DOT_INACTIVE_OPACITY, true );
			if ( is_numeric( $dot_op_raw ) && '' !== (string) $dot_op_raw ) {
				$dot_op_pct = min( 100, max( 0, (int) $dot_op_raw ) );
			}
			$parts[] = '--zs-slider-dot-inactive-bg: ' . esc_attr( self::rgba_css_from_hex_percent( $dot_in_hex, $dot_op_pct ) );
		}

		$dot_ac = ZSkeleton_Sliders::sanitize_slider_hex_color( (string) get_post_meta( $post_id, ZSkeleton_Sliders::META_DOT_ACTIVE_COLOR, true ) );
		if ( '' !== $dot_ac ) {
			$parts[] = '--zs-slider-dot-active-bg: ' . $dot_ac;
		}

		$ft = ZSkeleton_Sliders::sanitize_slider_css_token_value( (string) get_post_meta( $post_id, ZSkeleton_Sliders::META_FONT_TITLE, true ) );
		if ( '' !== $ft ) {
			$parts[] = '--zs-slider-font-title: ' . esc_attr( $ft );
		}
		$fd = ZSkeleton_Sliders::sanitize_slider_css_token_value( (string) get_post_meta( $post_id, ZSkeleton_Sliders::META_FONT_DESC, true ) );
		if ( '' !== $fd ) {
			$parts[] = '--zs-slider-font-desc: ' . esc_attr( $fd );
		}
		$fbp = ZSkeleton_Sliders::sanitize_slider_css_token_value( (string) get_post_meta( $post_id, ZSkeleton_Sliders::META_FONT_BTN_PRIMARY, true ) );
		if ( '' !== $fbp ) {
			$parts[] = '--zs-slider-font-btn-primary: ' . esc_attr( $fbp );
		}
		$fbs = ZSkeleton_Sliders::sanitize_slider_css_token_value( (string) get_post_meta( $post_id, ZSkeleton_Sliders::META_FONT_BTN_SECONDARY, true ) );
		if ( '' !== $fbs ) {
			$parts[] = '--zs-slider-font-btn-secondary: ' . esc_attr( $fbs );
		}

		$br = ZSkeleton_Sliders::sanitize_slider_css_token_value( (string) get_post_meta( $post_id, ZSkeleton_Sliders::META_BORDER_RADIUS, true ) );
		if ( '' !== $br ) {
			$parts[] = '--zs-slider-border-radius: ' . esc_attr( $br );
		}
		$brc = ZSkeleton_Sliders::sanitize_slider_css_token_value( (string) get_post_meta( $post_id, ZSkeleton_Sliders::META_BORDER_RADIUS_CONTROLS, true ) );
		if ( '' !== $brc ) {
			$parts[] = '--zs-slider-control-radius: ' . esc_attr( $brc );
		}

		$joined = implode( '; ', $parts );

		return $joined;
	}

	/**
	 * @param string $hex #RRGGBB.
	 * @param int    $opacity_pct 0–100.
	 * @return string rgba(...) without extra escaping (caller uses esc_attr on full declaration value if needed).
	 */
	private static function rgba_css_from_hex_percent( $hex, $opacity_pct ) {
		$rgb   = self::hex_to_rgb_components( $hex );
		$alpha = min( 1.0, max( 0.0, $opacity_pct / 100 ) );
		return sprintf(
			'rgba(%d,%d,%d,%s)',
			$rgb['r'],
			$rgb['g'],
			$rgb['b'],
			number_format( $alpha, 2, '.', '' )
		);
	}

	/**
	 * @param string $hex #RRGGBB from sanitize_hex_color().
	 * @return array{r:int,g:int,b:int}
	 */
	private static function hex_to_rgb_components( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
			return array( 'r' => 15, 'g' => 23, 'b' => 42 );
		}
		return array(
			'r' => (int) hexdec( substr( $hex, 0, 2 ) ),
			'g' => (int) hexdec( substr( $hex, 2, 2 ) ),
			'b' => (int) hexdec( substr( $hex, 4, 2 ) ),
		);
	}

	/**
	 * Per-slider control styles under the root id (high specificity, reads this instance's inline --zs-slider-* vars).
	 *
	 * @param string $uid Root element id, expected zskeleton-slider-{post_id}.
	 * @return string Style element markup or empty string.
	 */
	private static function render_slider_scope_styles( $uid ) {
		$uid = (string) $uid;
		if ( ! preg_match( '/^zskeleton-slider-\d+$/', $uid ) ) {
			return '';
		}
		$id  = esc_attr( $uid );
		$css = sprintf(
			'#%1$s button.zskeleton-slider__nav{border:0;border-radius:var(--zs-slider-control-radius,999px);background:var(--zs-slider-nav-bg,var(--zs-slider-glass));color:var(--zs-slider-nav-color,#fff);}' .
			'#%1$s button.zskeleton-slider__nav:focus-visible{outline:2px solid var(--zs-slider-nav-color,#fff);outline-offset:3px;}' .
			'#%1$s button.zskeleton-slider__dot{padding:0;border:0;border-radius:var(--zs-slider-control-radius,999px);-webkit-appearance:none;appearance:none;background:var(--zs-slider-dot-inactive-bg,rgba(255,255,255,.35));}' .
			'#%1$s button.zskeleton-slider__dot.is-active{width:1.6rem;background:var(--zs-slider-dot-active-bg,#fff);}' .
			'#%1$s button.zskeleton-slider__dot:focus-visible{outline:2px solid var(--zs-slider-dot-active-bg,#fff);outline-offset:2px;}',
			$id
		);

		return '<style id="' . $id . '-ctrl-scope">' . $css . '</style>';
	}

	/**
	 * Build markup for one slider post.
	 *
	 * @param WP_Post $slider Slider post.
	 * @param string  $extra_class Extra CSS class on root.
	 * @return string
	 */
	public static function render_slider_html( WP_Post $slider, $extra_class = '' ) {
		$slides = zskeleton_get_repeater( (int) $slider->ID, ZSkeleton_Sliders::REPEATER_SLIDES );
		$slides = self::filter_slides( $slides );
		if ( empty( $slides ) ) {
			return '';
		}

		$show_dots = get_post_meta( $slider->ID, ZSkeleton_Sliders::META_SHOW_DOTS, true );
		$show_nav  = get_post_meta( $slider->ID, ZSkeleton_Sliders::META_SHOW_NAV, true );
		$raw_ap = get_post_meta( $slider->ID, ZSkeleton_Sliders::META_AUTOPLAY_MS, true );
		if ( '' === $raw_ap || false === $raw_ap ) {
			$autoplay = 6000;
		} else {
			$autoplay = max( 0, (int) $raw_ap );
		}
		$effect    = (string) get_post_meta( $slider->ID, ZSkeleton_Sliders::META_EFFECT, true );
		$min_h     = (string) get_post_meta( $slider->ID, ZSkeleton_Sliders::META_MIN_HEIGHT, true );
		$layout    = ZSkeleton_Sliders::sanitize_slider_layout( (string) get_post_meta( $slider->ID, ZSkeleton_Sliders::META_LAYOUT, true ) );
		// CSS modifiers use hyphens (BEM); meta keys use underscores (split_seo → split-seo).
		$layout_css = str_replace( '_', '-', $layout );

		if ( '' === $show_dots ) {
			$show_dots = '1';
		}
		if ( '' === $show_nav ) {
			$show_nav = '1';
		}
		if ( ! in_array( $effect, array( 'fade', 'slide', 'zoom' ), true ) ) {
			$effect = 'fade';
		}

		$dir        = is_rtl() ? 'rtl' : 'ltr';
		$uid        = 'zskeleton-slider-' . (int) $slider->ID;
		$style_attr = self::build_slider_root_style( (int) $slider->ID, $min_h );

		ob_start();
		?>
		<div
			id="<?php echo esc_attr( $uid ); ?>"
			class="zskeleton-slider zskeleton-slider--<?php echo esc_attr( $effect ); ?> zskeleton-slider--layout-<?php echo esc_attr( $layout_css ); ?> <?php echo esc_attr( $extra_class ); ?>"
			data-slider-id="<?php echo esc_attr( (string) (int) $slider->ID ); ?>"
			data-autoplay="<?php echo esc_attr( (string) max( 0, $autoplay ) ); ?>"
			data-show-dots="<?php echo esc_attr( '1' === $show_dots ? '1' : '0' ); ?>"
			data-show-nav="<?php echo esc_attr( '1' === $show_nav ? '1' : '0' ); ?>"
			data-effect="<?php echo esc_attr( $effect ); ?>"
			dir="<?php echo esc_attr( $dir ); ?>"
			role="region"
			aria-roledescription="<?php echo esc_attr__( 'carousel', 'zskeleton' ); ?>"
			aria-label="<?php echo esc_attr( wp_strip_all_tags( get_the_title( $slider ) ) ); ?>"
			style="<?php echo esc_attr( $style_attr ); ?>"
		>
			<div class="zskeleton-slider__viewport">
				<div class="zskeleton-slider__track">
					<?php
					foreach ( $slides as $idx => $row ) {
						self::render_slide( $row, (int) $idx, 0 === (int) $idx, $layout );
					}
					?>
				</div>
				<?php if ( '1' === $show_nav && count( $slides ) > 1 ) : ?>
					<button type="button" class="zskeleton-slider__nav zskeleton-slider__nav--prev" aria-controls="<?php echo esc_attr( $uid ); ?>" aria-label="<?php esc_attr_e( 'Previous slide', 'zskeleton' ); ?>">
						<span class="zskeleton-slider__nav-icon" aria-hidden="true"></span>
					</button>
					<button type="button" class="zskeleton-slider__nav zskeleton-slider__nav--next" aria-controls="<?php echo esc_attr( $uid ); ?>" aria-label="<?php esc_attr_e( 'Next slide', 'zskeleton' ); ?>">
						<span class="zskeleton-slider__nav-icon" aria-hidden="true"></span>
					</button>
				<?php endif; ?>
				<?php if ( '1' === $show_dots && count( $slides ) > 1 ) : ?>
					<div class="zskeleton-slider__dots" role="tablist" aria-label="<?php esc_attr_e( 'Slides', 'zskeleton' ); ?>">
						<?php foreach ( $slides as $idx => $_row ) : ?>
							<button type="button" class="zskeleton-slider__dot<?php echo 0 === (int) $idx ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo 0 === (int) $idx ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number */ __( 'Go to slide %d', 'zskeleton' ), (int) $idx + 1 ) ); ?>" data-go-to="<?php echo esc_attr( (string) (int) $idx ); ?>"></button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static CSS; slider id is validated and escaped in render_slider_scope_styles().
		echo self::render_slider_scope_styles( $uid );

		return (string) ob_get_clean();
	}

	/**
	 * Title, description, and CTA row (shared by all layout styles).
	 *
	 * @param string $title           Slide title.
	 * @param string $desc            Slide description (plain text; escaped in desc output).
	 * @param bool   $primary_btn     Whether primary CTA renders.
	 * @param bool   $secondary_btn   Whether secondary CTA renders.
	 * @param string $p_label         Primary label.
	 * @param string $p_url           Primary URL.
	 * @param string $s_label         Secondary label.
	 * @param string $s_url           Secondary URL.
	 */
	/**
	 * Markup for an attachment used inside a slide (caller escapes context as needed).
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param int    $index         Slide index (first slide eager-loads).
	 * @param string $class_attr    Space-separated classes for the img element.
	 * @return string HTML or empty string.
	 */
	private static function slide_attachment_image_html( $attachment_id, $index, $class_attr ) {
		$attachment_id = absint( $attachment_id );
		if ( ! $attachment_id ) {
			return '';
		}
		$img = wp_get_attachment_image(
			$attachment_id,
			'full',
			false,
			array(
				'class'    => trim( (string) $class_attr ),
				'loading'  => 0 === (int) $index ? 'eager' : 'lazy',
				'decoding' => 'async',
			)
		);
		return is_string( $img ) ? $img : '';
	}

	/**
	 * Style 2: content image as a full-slide floating layer (not inside the copy column).
	 *
	 * @param int $content_id Attachment ID (slide_content_image_id).
	 * @param int $index      Slide index.
	 * @return string Wrapper + img, or empty string.
	 */
	private static function get_seo_floating_content_image_html( $content_id, $index ) {
		$content_id = absint( $content_id );
		if ( ! $content_id ) {
			return '';
		}
		$img = self::slide_attachment_image_html(
			$content_id,
			(int) $index,
			'zskeleton-slider__img zskeleton-slider__img--seo-float'
		);
		if ( '' === $img ) {
			return '';
		}
		return '<div class="zskeleton-slider__seo-float-img" aria-hidden="true">' . $img . '</div>';
	}

	/**
	 * Content image: `<img>` in the copy column (Style 3 / hero only — not Style 2).
	 *
	 * @param int    $content_id Attachment ID (slide_content_image_id).
	 * @param int    $index      Slide index.
	 * @param string $layout     hero|split_seo|split_normal.
	 * @return string HTML wrapper + img, or empty string.
	 */
	private static function get_content_column_image_slot_html( $content_id, $index, $layout ) {
		$content_id = absint( $content_id );
		if ( ! $content_id ) {
			return '';
		}
		$mod = 'split-normal';
		if ( 'hero' === $layout ) {
			$mod = 'hero';
		}
		$img = self::slide_attachment_image_html(
			$content_id,
			(int) $index,
			'zskeleton-slider__img zskeleton-slider__content-inline-img zskeleton-slider__content-inline-img--' . $mod
		);
		if ( '' === $img ) {
			return '';
		}
		return '<div class="zskeleton-slider__content-img-slot zskeleton-slider__content-img-slot--' . esc_attr( $mod ) . '" aria-hidden="true">' . $img . '</div>';
	}

	/**
	 * Copy block: optional content-image column + text/actions column (two columns on wide viewports).
	 *
	 * @param string $html_content_col_img Slot HTML or empty.
	 * @param string $title                Slide title.
	 * @param string $desc                 Slide description.
	 * @param bool   $primary_btn          Primary CTA visible.
	 * @param bool   $secondary_btn        Secondary CTA visible.
	 * @param string $p_label              Primary label.
	 * @param string $p_url                Primary URL.
	 * @param string $s_label              Secondary label.
	 * @param string $s_url                Secondary URL.
	 */
	private static function render_slide_copy_with_column_layout(
		$html_content_col_img,
		$title,
		$desc,
		$primary_btn,
		$secondary_btn,
		$p_label,
		$p_url,
		$s_label,
		$s_url
	) {
		if ( '' !== $html_content_col_img ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- slot built with esc_attr + wp_get_attachment_image.
			echo $html_content_col_img;
			echo '<div class="zskeleton-slider__content-stack">';
			self::render_slide_content_inner( $title, $desc, $primary_btn, $secondary_btn, $p_label, $p_url, $s_label, $s_url );
			echo '</div>';
			return;
		}
		self::render_slide_content_inner( $title, $desc, $primary_btn, $secondary_btn, $p_label, $p_url, $s_label, $s_url );
	}

	private static function render_slide_content_inner(
		$title,
		$desc,
		$primary_btn,
		$secondary_btn,
		$p_label,
		$p_url,
		$s_label,
		$s_url
	) {
		if ( $title !== '' ) {
			echo '<h2 class="zskeleton-slider__title">' . esc_html( $title ) . '</h2>';
		}
		if ( $desc !== '' ) {
			echo '<div class="zskeleton-slider__desc">' . wp_kses_post( wpautop( esc_html( $desc ) ) ) . '</div>';
		}
		if ( $primary_btn || $secondary_btn ) {
			echo '<div class="zskeleton-slider__actions">';
			if ( $primary_btn ) {
				printf(
					'<a class="zskeleton-slider__btn zskeleton-slider__btn--primary btn btn-primary" href="%s">%s</a>',
					esc_url( $p_url ),
					esc_html( $p_label )
				);
			}
			if ( $secondary_btn ) {
				printf(
					'<a class="zskeleton-slider__btn zskeleton-slider__btn--secondary btn btn-secondary" href="%s">%s</a>',
					esc_url( $s_url ),
					esc_html( $s_label )
				);
			}
			echo '</div>';
		}
	}

	/**
	 * @param array<string,mixed> $row Slide row.
	 * @param int                 $index Index.
	 * @param bool                $is_active First slide active.
	 * @param string              $layout  hero|split_seo|split_normal.
	 */
	private static function render_slide( array $row, $index, $is_active, $layout ) {
		$title   = isset( $row['slide_title'] ) ? (string) $row['slide_title'] : '';
		$desc    = isset( $row['slide_description'] ) ? (string) $row['slide_description'] : '';
		$p_label = isset( $row['button_primary_label'] ) ? (string) $row['button_primary_label'] : '';
		$p_url   = isset( $row['button_primary_url'] ) ? (string) $row['button_primary_url'] : '';
		$s_label = isset( $row['button_secondary_label'] ) ? (string) $row['button_secondary_label'] : '';
		$s_url   = isset( $row['button_secondary_url'] ) ? (string) $row['button_secondary_url'] : '';

		$bg_id       = isset( $row['slide_image_id'] ) ? absint( $row['slide_image_id'] ) : 0;
		$content_id  = isset( $row['slide_content_image_id'] ) ? absint( $row['slide_content_image_id'] ) : 0;
		$split_dual_bg = $bg_id && $content_id && $bg_id !== $content_id && ( 'split_seo' === $layout || 'split_normal' === $layout );

		// Hero full-bleed: background image, or legacy single “content” image when no background is set.
		$hero_full_bleed_id = $bg_id ? $bg_id : $content_id;
		// Copy-column content image: Style 3 + hero; Style 2 uses a full-slide floating layer instead.
		$show_content_col_img = false;
		if ( $content_id ) {
			if ( 'split_normal' === $layout ) {
				$show_content_col_img = true;
			} elseif ( 'hero' === $layout && $bg_id ) {
				$show_content_col_img = true;
			}
		}
		$html_content_col_img = $show_content_col_img ? self::get_content_column_image_slot_html( $content_id, (int) $index, $layout ) : '';
		$html_seo_float       = ( 'split_seo' === $layout && $content_id ) ? self::get_seo_floating_content_image_html( $content_id, (int) $index ) : '';

		$content_class = 'zskeleton-slider__content';
		if ( '' !== $html_content_col_img ) {
			$content_class .= ' zskeleton-slider__content--with-col-img';
		}

		// Style 3: media column uses slide background image only. Style 2 uses full-slide bg--seo-full instead.
		$img_class_media = 'zskeleton-slider__img';
		$img_html_media  = '';
		if ( 'split_normal' === $layout ) {
			$img_class_media .= ' zskeleton-slider__img--column';
			$img_html_media   = self::slide_attachment_image_html( $bg_id, (int) $index, $img_class_media );
		}
		$img_html_hero_bg   = self::slide_attachment_image_html( $hero_full_bleed_id, (int) $index, 'zskeleton-slider__img' );
		$img_html_split_bg  = self::slide_attachment_image_html( $bg_id, (int) $index, 'zskeleton-slider__img zskeleton-slider__img--bg-cover' );

		$primary_btn   = ( $p_label !== '' && $p_url !== '' );
		$secondary_btn = ( $s_label !== '' && $s_url !== '' );
		$slide_bg      = ZSkeleton_Sliders::sanitize_slider_hex_color( isset( $row['slide_background_color'] ) ? (string) $row['slide_background_color'] : '' );
		$slide_style   = '';
		if ( '' !== $slide_bg ) {
			$slide_style = ' style="--zs-slide-bg:' . esc_attr( $slide_bg ) . ';background-color:' . esc_attr( $slide_bg ) . ';"';
		}
		?>
		<article class="zskeleton-slider__slide<?php echo $is_active ? ' is-active' : ''; ?>" data-index="<?php echo esc_attr( (string) (int) $index ); ?>" aria-hidden="<?php echo $is_active ? 'false' : 'true'; ?>"<?php echo $slide_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from sanitized hex values only. ?>>
		<?php if ( 'split_seo' === $layout ) : ?>
			<?php
			// Darken base when background photo and floating content image differ (same flag semantics as dual-bg).
			$seo_dual_class = ( $split_dual_bg ? ' zskeleton-slider__slide-split--has-dual-images' : '' );
			// No rendered background or floating content image: single copy column (no spacer half).
			$seo_one_col = ( '' === $html_seo_float && '' === $img_html_split_bg );
			$seo_one_cls = ( $seo_one_col ? ' zskeleton-slider__slide-split--seo-one-col' : '' );
			?>
			<!-- Style 2: one full-slide background + overlay; content image floats above columns; copy has text only. -->
			<div class="zskeleton-slider__slide-split zskeleton-slider__slide-split--columns zskeleton-slider__slide-split--seo<?php echo esc_attr( $seo_dual_class . $seo_one_cls ); ?>">
				<?php if ( $bg_id && $img_html_split_bg ) : ?>
					<div class="zskeleton-slider__bg zskeleton-slider__bg--seo-full" aria-hidden="true">
						<?php echo $img_html_split_bg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image ?>
					</div>
				<?php endif; ?>
				<div class="zskeleton-slider__overlay zskeleton-slider__overlay--seo-full" aria-hidden="true"></div>
				<?php if ( ! $seo_one_col ) : ?>
					<div class="zskeleton-slider__col zskeleton-slider__col--media zskeleton-slider__col--seo-spacer" aria-hidden="true"></div>
				<?php endif; ?>
				<div class="zskeleton-slider__col zskeleton-slider__col--content zskeleton-slider__col--seo-copy">
					<div class="<?php echo esc_attr( $content_class ); ?>">
						<?php
						self::render_slide_copy_with_column_layout(
							'',
							$title,
							$desc,
							$primary_btn,
							$secondary_btn,
							$p_label,
							$p_url,
							$s_label,
							$s_url
						);
						?>
					</div>
				</div>
				<?php
				// After columns so the float stacks above full-width copy on small screens (absolute layer).
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from wp_get_attachment_image in get_seo_floating_content_image_html().
				echo $html_seo_float;
				?>
			</div>
		<?php elseif ( 'split_normal' === $layout ) : ?>
			<!-- Two columns: media = background image only; copy column may include content image img. -->
			<div class="zskeleton-slider__slide-split zskeleton-slider__slide-split--columns<?php echo $split_dual_bg ? ' zskeleton-slider__slide-split--has-dual-images' : ''; ?>">
				<?php if ( $split_dual_bg && $img_html_split_bg ) : ?>
					<div class="zskeleton-slider__bg zskeleton-slider__bg--split-columns" aria-hidden="true">
						<?php echo $img_html_split_bg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image ?>
					</div>
				<?php endif; ?>
				<div class="zskeleton-slider__col zskeleton-slider__col--media">
					<?php if ( $img_html_media ) : ?>
						<div class="zskeleton-slider__media zskeleton-slider__media--column" aria-hidden="true">
							<?php echo $img_html_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image ?>
						</div>
					<?php else : ?>
						<div class="zskeleton-slider__media zskeleton-slider__media--column zskeleton-slider__media--gradient" aria-hidden="true"></div>
					<?php endif; ?>
				</div>
				<div class="zskeleton-slider__col zskeleton-slider__col--content">
					<div class="zskeleton-slider__overlay zskeleton-slider__overlay--column" aria-hidden="true"></div>
					<div class="<?php echo esc_attr( $content_class ); ?>">
						<?php
						self::render_slide_copy_with_column_layout(
							$html_content_col_img,
							$title,
							$desc,
							$primary_btn,
							$secondary_btn,
							$p_label,
							$p_url,
							$s_label,
							$s_url
						);
						?>
					</div>
				</div>
			</div>
		<?php else : ?>
			<?php if ( $img_html_hero_bg ) : ?>
				<div class="zskeleton-slider__media" aria-hidden="true">
					<?php echo $img_html_hero_bg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image ?>
				</div>
			<?php else : ?>
				<div class="zskeleton-slider__media zskeleton-slider__media--gradient" aria-hidden="true"></div>
			<?php endif; ?>
			<div class="zskeleton-slider__overlay" aria-hidden="true"></div>
			<div class="<?php echo esc_attr( $content_class ); ?>">
				<?php
				self::render_slide_copy_with_column_layout(
					$html_content_col_img,
					$title,
					$desc,
					$primary_btn,
					$secondary_btn,
					$p_label,
					$p_url,
					$s_label,
					$s_url
				);
				?>
			</div>
		<?php endif; ?>
		</article>
		<?php
	}

	/**
	 * Drop completely empty rows.
	 *
	 * @param array<int,array<string,mixed>> $slides Raw rows.
	 * @return array<int,array<string,mixed>>
	 */
	private static function filter_slides( array $slides ) {
		$out = array();
		foreach ( $slides as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$title = isset( $row['slide_title'] ) ? trim( (string) $row['slide_title'] ) : '';
			$desc  = isset( $row['slide_description'] ) ? trim( (string) $row['slide_description'] ) : '';
			$p_l   = isset( $row['button_primary_label'] ) ? trim( (string) $row['button_primary_label'] ) : '';
			$p_u   = isset( $row['button_primary_url'] ) ? trim( (string) $row['button_primary_url'] ) : '';
			$s_l   = isset( $row['button_secondary_label'] ) ? trim( (string) $row['button_secondary_label'] ) : '';
			$s_u   = isset( $row['button_secondary_url'] ) ? trim( (string) $row['button_secondary_url'] ) : '';
			$img    = isset( $row['slide_image_id'] ) ? absint( $row['slide_image_id'] ) : 0;
			$c_img  = isset( $row['slide_content_image_id'] ) ? absint( $row['slide_content_image_id'] ) : 0;
			if ( '' === $title && '' === $desc && ( '' === $p_l || '' === $p_u ) && ( '' === $s_l || '' === $s_u ) && ! $img && ! $c_img ) {
				continue;
			}
			$out[] = $row;
		}
		return $out;
	}
}

/**
 * Output slider HTML by post ID or slug (for PHP templates).
 *
 * @param int|string $id_or_slug Post ID or post_name.
 * @param string     $extra_class Optional root CSS class.
 * @return string
 */
function zskeleton_slider_markup( $id_or_slug, $extra_class = '' ) {
	return ZSkeleton_Slider_Frontend::render( $id_or_slug, $extra_class );
}
