<?php
/**
 * Hero / marketing sliders (CPT + custom slide meta UI for Classic Editor + display options).
 *
 * Slide data is stored under the same meta key as the repeater API (`_zskeleton_rep_slider_slides`).
 * Each slide may include `slide_image_id` (background / media column / corner figure) and `slide_content_image_id` (copy column <img>, layout-specific).
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers CPT, stacked slides meta UI, and slider-level options meta.
 */
class ZSkeleton_Sliders {

	const POST_TYPE = 'zskeleton_slider';

	const REPEATER_SLIDES = 'slider_slides';

	const META_SHOW_DOTS = '_zskeleton_slider_show_dots';

	const META_SHOW_NAV = '_zskeleton_slider_show_nav';

	const META_AUTOPLAY_MS = '_zskeleton_slider_autoplay_ms';

	const META_EFFECT = '_zskeleton_slider_effect';

	const META_LAYOUT = '_zskeleton_slider_layout';

	const META_MIN_HEIGHT = '_zskeleton_slider_min_height';

	const META_COLOR_TITLE = '_zskeleton_slider_color_title';

	const META_COLOR_DESC = '_zskeleton_slider_color_desc';

	const META_COLOR_ACCENT = '_zskeleton_slider_color_accent';

	const META_OVERLAY_COLOR = '_zskeleton_slider_overlay_color';

	const META_OVERLAY_OPACITY = '_zskeleton_slider_overlay_opacity';

	const META_MIN_HEIGHT_MOBILE = '_zskeleton_slider_min_height_mobile';

	const META_CONTENT_IMAGE_MAX_HEIGHT = '_zskeleton_slider_content_image_max_height';

	const META_CONTENT_IMAGE_MAX_HEIGHT_MOBILE = '_zskeleton_slider_content_image_max_height_mobile';

	const META_NAV_BG_COLOR = '_zskeleton_slider_nav_bg_color';

	const META_NAV_BG_OPACITY = '_zskeleton_slider_nav_bg_opacity';

	const META_NAV_ICON_COLOR = '_zskeleton_slider_nav_icon_color';

	const META_DOT_INACTIVE_COLOR = '_zskeleton_slider_dot_inactive_color';

	const META_DOT_INACTIVE_OPACITY = '_zskeleton_slider_dot_inactive_opacity';

	const META_DOT_ACTIVE_COLOR = '_zskeleton_slider_dot_active_color';

	const META_FONT_TITLE = '_zskeleton_slider_font_title';

	const META_FONT_DESC = '_zskeleton_slider_font_desc';

	const META_FONT_BTN_PRIMARY = '_zskeleton_slider_font_btn_primary';

	const META_FONT_BTN_SECONDARY = '_zskeleton_slider_font_btn_secondary';

	const META_BORDER_RADIUS = '_zskeleton_slider_border_radius';

	const META_BORDER_RADIUS_CONTROLS = '_zskeleton_slider_border_radius_controls';

	/**
	 * Normalize a color string to #rrggbb for storage/output.
	 *
	 * WordPress `sanitize_hex_color()` requires a leading hash; Iris / pasted values
	 * may omit it, which previously cleared meta so front-end CSS variables never applied.
	 *
	 * @param mixed $raw Post value or meta.
	 * @return string Empty string if invalid or blank, else #rrggbb.
	 */
	public static function sanitize_slider_hex_color( $raw ) {
		if ( ! is_string( $raw ) ) {
			return '';
		}
		$raw = trim( $raw );
		if ( '' === $raw ) {
			return '';
		}
		if ( function_exists( 'maybe_hash_hex_color' ) ) {
			$raw = maybe_hash_hex_color( $raw );
		}
		$hex = sanitize_hex_color( $raw );
		return ( is_string( $hex ) && '' !== $hex ) ? $hex : '';
	}

	/**
	 * Sanitize a short CSS fragment for font-size / min-height (no url(), var(), etc.).
	 *
	 * @param mixed $raw Raw POST or meta.
	 * @return string Empty if invalid.
	 */
	/**
	 * Slider layout presets (controls slide markup + image placement).
	 *
	 * @return array<string,string> Layout key => admin label.
	 */
	public static function get_slider_layout_choices() {
		return array(
			'hero'         => __( 'Style 1 — Fullscreen hero', 'zskeleton' ),
			'split_seo'    => __( 'Style 2 — Split (SEO hero image position)', 'zskeleton' ),
			'split_normal' => __( 'Style 3 — Two columns (image + content)', 'zskeleton' ),
		);
	}

	/**
	 * @param mixed $raw Meta or POST value.
	 * @return string One of hero|split_seo|split_normal.
	 */
	public static function sanitize_slider_layout( $raw ) {
		$key = is_string( $raw ) ? sanitize_key( $raw ) : '';
		if ( ! in_array( $key, array( 'hero', 'split_seo', 'split_normal' ), true ) ) {
			return 'hero';
		}
		return $key;
	}

	public static function sanitize_slider_css_token_value( $raw ) {
		$v = is_string( $raw ) ? trim( $raw ) : '';
		if ( '' === $v ) {
			return '';
		}
		if ( strlen( $v ) > 200 ) {
			return '';
		}
		if ( preg_match( '/[<>\'\\\\`]|\/\*|\*\/|\\\\$/', $v ) ) {
			return '';
		}
		if ( preg_match( '/\b(url|expression|javascript|import|at\-import|var)\s*\(/i', $v ) ) {
			return '';
		}
		if ( ! preg_match( '/^[0-9a-zA-Z%,._+\s()\/-]+$/', $v ) ) {
			return '';
		}
		return $v;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ), 5 );
		add_action( 'save_post', array( $this, 'save_slider_options' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_slides_meta' ), 15, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_slider_admin' ) );
		// Meta boxes live in the classic editor layout; block editor often hides or relocates them.
		add_filter( 'use_block_editor_for_post_type', array( $this, 'force_classic_editor_for_slider' ), 10000, 2 );
		add_filter( 'use_block_editor_for_post', array( $this, 'force_classic_editor_for_slider_post' ), 10000, 2 );
	}

	/**
	 * @param bool   $use_block_editor Whether to use the block editor.
	 * @param string $post_type        Post type.
	 * @return bool
	 */
	public function force_classic_editor_for_slider( $use_block_editor, $post_type ) {
		if ( self::POST_TYPE === $post_type ) {
			return false;
		}
		return $use_block_editor;
	}

	/**
	 * @param bool    $use_block_editor Whether to use the block editor.
	 * @param WP_Post $post             Post being edited.
	 * @return bool
	 */
	public function force_classic_editor_for_slider_post( $use_block_editor, $post ) {
		if ( $post instanceof WP_Post && self::POST_TYPE === $post->post_type ) {
			return false;
		}
		return $use_block_editor;
	}

	/**
	 * Field schema (shared by sanitize + admin markup).
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_slide_fields_config() {
		return array(
			'slide_title'            => array(
				'type'  => 'text',
				'label' => __( 'Title (optional)', 'zskeleton' ),
			),
			'slide_description'      => array(
				'type'  => 'textarea',
				'label' => __( 'Short description (optional)', 'zskeleton' ),
			),
			'button_primary_label'   => array(
				'type'  => 'text',
				'label' => __( 'Primary button label', 'zskeleton' ),
			),
			'button_primary_url'     => array(
				'type'  => 'url',
				'label' => __( 'Primary button URL', 'zskeleton' ),
			),
			'button_secondary_label' => array(
				'type'  => 'text',
				'label' => __( 'Secondary button label', 'zskeleton' ),
			),
			'button_secondary_url'   => array(
				'type'  => 'url',
				'label' => __( 'Secondary button URL', 'zskeleton' ),
			),
			'slide_image_id'           => array(
				'type'  => 'image_id',
				'label' => __( 'Background image', 'zskeleton' ),
			),
			'slide_content_image_id'   => array(
				'type'  => 'image_id',
				'label' => __( 'Content image', 'zskeleton' ),
			),
			'slide_background_color'   => array(
				'type'  => 'text',
				'label' => __( 'Slide background color', 'zskeleton' ),
			),
		);
	}

	/**
	 * Register slider post type (under Theme Features menu).
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Sliders', 'post type general name', 'zskeleton' ),
			'singular_name'      => _x( 'Slider', 'post type singular name', 'zskeleton' ),
			'menu_name'          => __( 'Sliders', 'zskeleton' ),
			'name_admin_bar'     => __( 'Slider', 'zskeleton' ),
			'add_new'            => __( 'Add New', 'zskeleton' ),
			'add_new_item'       => __( 'Add slider', 'zskeleton' ),
			'new_item'           => __( 'New slider', 'zskeleton' ),
			'edit_item'          => __( 'Edit slider', 'zskeleton' ),
			'view_item'          => __( 'View slider', 'zskeleton' ),
			'all_items'          => __( 'All sliders', 'zskeleton' ),
			'search_items'       => __( 'Search sliders', 'zskeleton' ),
			'not_found'          => __( 'No sliders found.', 'zskeleton' ),
			'not_found_in_trash' => __( 'No sliders found in Trash.', 'zskeleton' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'             => $labels,
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_in_menu'       => ZSkeleton_Theme_Features_Admin::MENU_SLUG,
				'query_var'          => false,
				'rewrite'            => false,
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'supports'           => array( 'title', 'revisions' ),
				'show_in_rest'       => false,
				'menu_icon'          => 'dashicons-images-alt2',
			)
		);
	}

	/**
	 * Meta boxes (Slides first so it stays visible above display options).
	 */
	public function register_meta_boxes() {
		add_meta_box(
			'zskeleton_slider_slides',
			__( 'Slides', 'zskeleton' ),
			array( $this, 'render_slides_meta' ),
			self::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'zskeleton_slider_options',
			__( 'Slider display', 'zskeleton' ),
			array( $this, 'render_options_meta' ),
			self::POST_TYPE,
			'normal',
			'default'
		);
	}

	/**
	 * Admin assets for slide editor (Classic Editor compatible).
	 *
	 * @param string $hook_suffix Screen hook.
	 */
	public function enqueue_slider_admin( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();

		$use_minified      = (bool) get_option( 'zskeleton_use_minified_assets', true );
		$slider_admin_file = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/css/slider-admin.min.css' )
			? 'slider-admin.min.css'
			: 'slider-admin.css';
		$slider_admin_path = ZSkeleton_THEME_DIR . '/assets/css/' . $slider_admin_file;

		wp_enqueue_style(
			'zskeleton-slider-admin',
			ZSkeleton_THEME_URL . '/assets/css/' . $slider_admin_file,
			array(),
			is_readable( $slider_admin_path ) ? (string) filemtime( $slider_admin_path ) : ZSkeleton_VERSION
		);

		wp_enqueue_style( 'wp-color-picker' );

		$slider_admin_js_file = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/js/slider-admin.min.js' )
			? 'slider-admin.min.js'
			: 'slider-admin.js';
		$slider_admin_js      = ZSkeleton_THEME_DIR . '/assets/js/' . $slider_admin_js_file;
		$slider_admin_ver     = is_readable( $slider_admin_js ) ? (string) filemtime( $slider_admin_js ) : ZSkeleton_VERSION;

		wp_enqueue_script(
			'zskeleton-slider-admin',
			ZSkeleton_THEME_URL . '/assets/js/' . $slider_admin_js_file,
			array( 'jquery', 'wp-color-picker' ),
			$slider_admin_ver,
			true
		);

		wp_localize_script(
			'zskeleton-slider-admin',
			'zskeletonSliderAdmin',
			array(
				'chooseImage'        => __( 'Choose background image', 'zskeleton' ),
				'chooseContentImage' => __( 'Choose content image', 'zskeleton' ),
			)
		);
	}

	/**
	 * @param WP_Post $post Post.
	 */
	public function render_slides_meta( $post ) {
		wp_nonce_field( 'zskeleton_slider_slides_save', 'zskeleton_slider_slides_nonce' );

		$rows = ZSkeleton_Repeater_Registry::get_rows( (int) $post->ID, self::REPEATER_SLIDES );
		if ( empty( $rows ) ) {
			$rows = array( array() );
		}

		echo '<p class="description">' . esc_html__( 'Add one or more slides. All fields are optional. Use background + content image together for split layouts (column image vs full-bleed backdrop) or hero (backdrop + optional foreground visual above the text).', 'zskeleton' ) . '</p>';
		echo '<div id="zskeleton-slider-slides-list" class="zskeleton-slider-slides-wrap">';

		foreach ( $rows as $idx => $row ) {
			if ( ! is_array( $row ) ) {
				$row = array();
			}
			echo self::render_slide_card( (string) (int) $idx, $row ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside renderer.
		}

		echo '</div>';

		printf(
			'<p class="zskeleton-slider-slides-actions"><button type="button" class="button" id="zskeleton-slider-add-slide">%s</button></p>',
			esc_html__( 'Add slide', 'zskeleton' )
		);

		// Hidden div (not <script>) so the block editor / KSES never strips the slide template markup.
		echo '<div id="zskeleton-slider-slide-template" class="zskeleton-slider-slide-template-root" style="display:none;" aria-hidden="true">';
		echo self::render_slide_card( '__IDX__', array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}

	/**
	 * One slide card (existing index or template token __IDX__).
	 *
	 * @param string              $index Index or "__IDX__" for JS template.
	 * @param array<string,mixed> $row   Row values.
	 * @return string
	 */
	private static function render_slide_card( $index, array $row ) {
		$fields = self::get_slide_fields_config();
		$img_id = isset( $row['slide_image_id'] ) ? absint( $row['slide_image_id'] ) : 0;
		$thumb  = '';
		if ( $img_id ) {
			$url = wp_get_attachment_image_url( $img_id, 'medium' );
			if ( $url ) {
				$thumb = '<img src="' . esc_url( $url ) . '" alt="" style="max-width:160px;height:auto;border-radius:4px;border:1px solid #dcdcde;" />';
			}
		}
		$content_img_id = isset( $row['slide_content_image_id'] ) ? absint( $row['slide_content_image_id'] ) : 0;
		$content_thumb  = '';
		if ( $content_img_id ) {
			$c_url = wp_get_attachment_image_url( $content_img_id, 'medium' );
			if ( $c_url ) {
				$content_thumb = '<img src="' . esc_url( $c_url ) . '" alt="" style="max-width:160px;height:auto;border-radius:4px;border:1px solid #dcdcde;" />';
			}
		}

		ob_start();
		?>
		<div class="zskeleton-slider-slide-card" data-slide-index="<?php echo esc_attr( $index ); ?>">
			<div class="zskeleton-slider-slide-card__head">
				<span><?php esc_html_e( 'Slide', 'zskeleton' ); ?></span>
				<button type="button" class="button-link zskeleton-slider-remove-slide"><?php esc_html_e( 'Remove', 'zskeleton' ); ?></button>
			</div>
			<div class="zskeleton-slider-slide-card__body">
				<div class="zskeleton-slider-slide-card__field">
					<label for="zs-slide-<?php echo esc_attr( $index ); ?>-title"><?php echo esc_html( $fields['slide_title']['label'] ); ?></label>
					<input type="text" class="widefat" id="zs-slide-<?php echo esc_attr( $index ); ?>-title" name="zskeleton_slider_slides[<?php echo esc_attr( $index ); ?>][slide_title]" value="<?php echo esc_attr( isset( $row['slide_title'] ) ? (string) $row['slide_title'] : '' ); ?>" />
				</div>
				<div class="zskeleton-slider-slide-card__field">
					<label for="zs-slide-<?php echo esc_attr( $index ); ?>-desc"><?php echo esc_html( $fields['slide_description']['label'] ); ?></label>
					<textarea class="widefat" id="zs-slide-<?php echo esc_attr( $index ); ?>-desc" name="zskeleton_slider_slides[<?php echo esc_attr( $index ); ?>][slide_description]" rows="3"><?php echo esc_textarea( isset( $row['slide_description'] ) ? (string) $row['slide_description'] : '' ); ?></textarea>
				</div>
				<div class="zskeleton-slider-slide-card__row">
					<div class="zskeleton-slider-slide-card__field">
						<label for="zs-slide-<?php echo esc_attr( $index ); ?>-p-l"><?php echo esc_html( $fields['button_primary_label']['label'] ); ?></label>
						<input type="text" class="widefat" id="zs-slide-<?php echo esc_attr( $index ); ?>-p-l" name="zskeleton_slider_slides[<?php echo esc_attr( $index ); ?>][button_primary_label]" value="<?php echo esc_attr( isset( $row['button_primary_label'] ) ? (string) $row['button_primary_label'] : '' ); ?>" />
					</div>
					<div class="zskeleton-slider-slide-card__field">
						<label for="zs-slide-<?php echo esc_attr( $index ); ?>-p-u"><?php echo esc_html( $fields['button_primary_url']['label'] ); ?></label>
						<input type="url" class="widefat" id="zs-slide-<?php echo esc_attr( $index ); ?>-p-u" name="zskeleton_slider_slides[<?php echo esc_attr( $index ); ?>][button_primary_url]" value="<?php echo esc_attr( isset( $row['button_primary_url'] ) ? (string) $row['button_primary_url'] : '' ); ?>" placeholder="https://…" />
					</div>
				</div>
				<div class="zskeleton-slider-slide-card__row">
					<div class="zskeleton-slider-slide-card__field">
						<label for="zs-slide-<?php echo esc_attr( $index ); ?>-s-l"><?php echo esc_html( $fields['button_secondary_label']['label'] ); ?></label>
						<input type="text" class="widefat" id="zs-slide-<?php echo esc_attr( $index ); ?>-s-l" name="zskeleton_slider_slides[<?php echo esc_attr( $index ); ?>][button_secondary_label]" value="<?php echo esc_attr( isset( $row['button_secondary_label'] ) ? (string) $row['button_secondary_label'] : '' ); ?>" />
					</div>
					<div class="zskeleton-slider-slide-card__field">
						<label for="zs-slide-<?php echo esc_attr( $index ); ?>-s-u"><?php echo esc_html( $fields['button_secondary_url']['label'] ); ?></label>
						<input type="url" class="widefat" id="zs-slide-<?php echo esc_attr( $index ); ?>-s-u" name="zskeleton_slider_slides[<?php echo esc_attr( $index ); ?>][button_secondary_url]" value="<?php echo esc_attr( isset( $row['button_secondary_url'] ) ? (string) $row['button_secondary_url'] : '' ); ?>" placeholder="https://…" />
					</div>
				</div>
				<div class="zskeleton-slider-slide-card__row">
					<div class="zskeleton-slider-slide-card__field">
						<label for="zs-slide-<?php echo esc_attr( $index ); ?>-bg-c"><?php echo esc_html( $fields['slide_background_color']['label'] ); ?></label>
						<input
							type="color"
							class="widefat"
							id="zs-slide-<?php echo esc_attr( $index ); ?>-bg-c"
							name="zskeleton_slider_slides[<?php echo esc_attr( $index ); ?>][slide_background_color]"
							value="<?php echo esc_attr( self::sanitize_slider_hex_color( isset( $row['slide_background_color'] ) ? (string) $row['slide_background_color'] : '' ) ?: '#0f172a' ); ?>"
						/>
					</div>
				</div>
				<div class="zskeleton-slider-slide-card__images-row">
					<div class="zskeleton-slider-slide-card__field">
						<span class="label-like" style="display:block;font-weight:600;margin-bottom:6px;font-size:12px;"><?php echo esc_html( $fields['slide_image_id']['label'] ); ?></span>
						<div class="zskeleton-slider-slide-card__media">
							<input type="hidden" class="zskeleton-slide-image-id" name="zskeleton_slider_slides[<?php echo esc_attr( $index ); ?>][slide_image_id]" value="<?php echo esc_attr( $img_id ? (string) $img_id : '' ); ?>" />
							<button type="button" class="button zskeleton-slider-set-image"><?php esc_html_e( 'Set image', 'zskeleton' ); ?></button>
							<button type="button" class="button zskeleton-slider-clear-image"><?php esc_html_e( 'Clear', 'zskeleton' ); ?></button>
							<span class="zskeleton-slider-image-preview"><?php echo $thumb ? wp_kses_post( $thumb ) : ''; ?></span>
						</div>
					</div>
					<div class="zskeleton-slider-slide-card__field">
						<span class="label-like" style="display:block;font-weight:600;margin-bottom:6px;font-size:12px;"><?php echo esc_html( $fields['slide_content_image_id']['label'] ); ?></span>
						<div class="zskeleton-slider-slide-card__media">
							<input type="hidden" class="zskeleton-slide-content-image-id" name="zskeleton_slider_slides[<?php echo esc_attr( $index ); ?>][slide_content_image_id]" value="<?php echo esc_attr( $content_img_id ? (string) $content_img_id : '' ); ?>" />
							<button type="button" class="button zskeleton-slider-set-content-image"><?php esc_html_e( 'Set image', 'zskeleton' ); ?></button>
							<button type="button" class="button zskeleton-slider-clear-content-image"><?php esc_html_e( 'Clear', 'zskeleton' ); ?></button>
							<span class="zskeleton-slider-content-image-preview"><?php echo $content_thumb ? wp_kses_post( $content_thumb ) : ''; ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @param WP_Post $post Post.
	 */
	public function render_options_meta( $post ) {
		wp_nonce_field( 'zskeleton_slider_options_save', 'zskeleton_slider_options_nonce' );

		$show_dots  = get_post_meta( $post->ID, self::META_SHOW_DOTS, true );
		$show_nav   = get_post_meta( $post->ID, self::META_SHOW_NAV, true );
		$autoplay   = get_post_meta( $post->ID, self::META_AUTOPLAY_MS, true );
		$effect     = get_post_meta( $post->ID, self::META_EFFECT, true );
		$layout     = get_post_meta( $post->ID, self::META_LAYOUT, true );
		$min_height = get_post_meta( $post->ID, self::META_MIN_HEIGHT, true );
		$color_title   = get_post_meta( $post->ID, self::META_COLOR_TITLE, true );
		$color_desc    = get_post_meta( $post->ID, self::META_COLOR_DESC, true );
		$color_accent  = get_post_meta( $post->ID, self::META_COLOR_ACCENT, true );
		$overlay_color = get_post_meta( $post->ID, self::META_OVERLAY_COLOR, true );
		$overlay_op    = get_post_meta( $post->ID, self::META_OVERLAY_OPACITY, true );
		$min_height_m  = get_post_meta( $post->ID, self::META_MIN_HEIGHT_MOBILE, true );
		$content_img_h = get_post_meta( $post->ID, self::META_CONTENT_IMAGE_MAX_HEIGHT, true );
		$content_img_h_m = get_post_meta( $post->ID, self::META_CONTENT_IMAGE_MAX_HEIGHT_MOBILE, true );
		$nav_bg_c      = get_post_meta( $post->ID, self::META_NAV_BG_COLOR, true );
		$nav_bg_op     = get_post_meta( $post->ID, self::META_NAV_BG_OPACITY, true );
		$nav_icon_c    = get_post_meta( $post->ID, self::META_NAV_ICON_COLOR, true );
		$dot_in_c      = get_post_meta( $post->ID, self::META_DOT_INACTIVE_COLOR, true );
		$dot_in_op     = get_post_meta( $post->ID, self::META_DOT_INACTIVE_OPACITY, true );
		$dot_ac_c      = get_post_meta( $post->ID, self::META_DOT_ACTIVE_COLOR, true );
		$font_title    = get_post_meta( $post->ID, self::META_FONT_TITLE, true );
		$font_desc     = get_post_meta( $post->ID, self::META_FONT_DESC, true );
		$font_btn_p    = get_post_meta( $post->ID, self::META_FONT_BTN_PRIMARY, true );
		$font_btn_s    = get_post_meta( $post->ID, self::META_FONT_BTN_SECONDARY, true );
		$border_r       = get_post_meta( $post->ID, self::META_BORDER_RADIUS, true );
		$border_rc      = get_post_meta( $post->ID, self::META_BORDER_RADIUS_CONTROLS, true );

		if ( '' === $show_dots ) {
			$show_dots = '1';
		}
		if ( '' === $show_nav ) {
			$show_nav = '1';
		}
		if ( '' === $autoplay || ! is_numeric( $autoplay ) ) {
			$autoplay = '6000';
		}
		if ( ! is_string( $effect ) || '' === $effect ) {
			$effect = 'fade';
		}
		if ( ! in_array( $effect, array( 'fade', 'slide', 'zoom' ), true ) ) {
			$effect = 'fade';
		}
		$layout = self::sanitize_slider_layout( is_string( $layout ) ? $layout : '' );
		?>
		<!-- Slider display options (dots, nav, autoplay, motion preset). -->
		<div class="zs-meta-fields zs-meta-fields--compact zs-meta-fields--panel">
			<div class="zs-meta-field">
				<label class="zs-meta-field__label zs-meta-field__label--inline" for="zskeleton_slider_show_dots">
					<input type="checkbox" id="zskeleton_slider_show_dots" name="zskeleton_slider_show_dots" value="1" <?php checked( $show_dots, '1' ); ?> />
					<span><?php esc_html_e( 'Show dot indicators', 'zskeleton' ); ?></span>
				</label>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label zs-meta-field__label--inline" for="zskeleton_slider_show_nav">
					<input type="checkbox" id="zskeleton_slider_show_nav" name="zskeleton_slider_show_nav" value="1" <?php checked( $show_nav, '1' ); ?> />
					<span><?php esc_html_e( 'Show prev / next arrows', 'zskeleton' ); ?></span>
				</label>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_autoplay_ms"><?php esc_html_e( 'Autoplay interval (ms)', 'zskeleton' ); ?></label>
				<input type="number" class="small-text" id="zskeleton_slider_autoplay_ms" name="zskeleton_slider_autoplay_ms" min="0" max="120000" step="500" value="<?php echo esc_attr( (string) (int) $autoplay ); ?>" />
				<p class="zs-meta-field__hint"><?php esc_html_e( 'Use 0 to disable autoplay.', 'zskeleton' ); ?></p>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_effect"><?php esc_html_e( 'Transition style', 'zskeleton' ); ?></label>
				<select class="widefat" id="zskeleton_slider_effect" name="zskeleton_slider_effect">
					<option value="fade" <?php selected( $effect, 'fade' ); ?>><?php esc_html_e( 'Fade', 'zskeleton' ); ?></option>
					<option value="slide" <?php selected( $effect, 'slide' ); ?>><?php esc_html_e( 'Slide', 'zskeleton' ); ?></option>
					<option value="zoom" <?php selected( $effect, 'zoom' ); ?>><?php esc_html_e( 'Zoom', 'zskeleton' ); ?></option>
				</select>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_layout"><?php esc_html_e( 'Slider style', 'zskeleton' ); ?></label>
				<select class="widefat" id="zskeleton_slider_layout" name="zskeleton_slider_layout">
					<?php foreach ( self::get_slider_layout_choices() as $layout_key => $layout_label ) : ?>
						<option value="<?php echo esc_attr( $layout_key ); ?>" <?php selected( $layout, $layout_key ); ?>><?php echo esc_html( $layout_label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="zs-meta-field__hint"><?php esc_html_e( 'Style 1 is the default layered hero. Style 2 uses a two-zone layout with the image anchored like the SEO Expert hero. Style 3 uses a simple image column beside the text.', 'zskeleton' ); ?></p>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_min_height"><?php esc_html_e( 'Minimum height (CSS)', 'zskeleton' ); ?></label>
				<input type="text" class="widefat" id="zskeleton_slider_min_height" name="zskeleton_slider_min_height" value="<?php echo esc_attr( (string) $min_height ); ?>" placeholder="min(72vh, 520px)" />
				<p class="zs-meta-field__hint"><?php esc_html_e( 'Example: 420px or min(70vh, 560px). Leave empty for default.', 'zskeleton' ); ?></p>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_border_radius"><?php esc_html_e( 'Slider border radius (CSS)', 'zskeleton' ); ?></label>
				<input type="text" class="widefat" id="zskeleton_slider_border_radius" name="zskeleton_slider_border_radius" value="<?php echo esc_attr( is_string( $border_r ) ? $border_r : '' ); ?>" placeholder="clamp(12px, 2.5vw, 28px)" />
				<p class="zs-meta-field__hint"><?php esc_html_e( 'Outer rounded corners of the slider. Example: 20px or clamp(10px, 2vw, 24px).', 'zskeleton' ); ?></p>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_border_radius_controls"><?php esc_html_e( 'Arrows & dots border radius (CSS)', 'zskeleton' ); ?></label>
				<input type="text" class="widefat" id="zskeleton_slider_border_radius_controls" name="zskeleton_slider_border_radius_controls" value="<?php echo esc_attr( is_string( $border_rc ) ? $border_rc : '' ); ?>" placeholder="999px" />
				<p class="zs-meta-field__hint"><?php esc_html_e( 'Prev/next buttons and dot indicators. Use 999px for pills, or 8px for soft squares.', 'zskeleton' ); ?></p>
			</div>
			<!-- Colors and overlay tint (optional; overlay defaults to slate at 45% opacity). -->
			<hr />
			<p class="zs-meta-field__hint" style="margin-top:0;"><strong><?php esc_html_e( 'Colors & overlay', 'zskeleton' ); ?></strong> — <?php esc_html_e( 'Leave blank to use theme defaults for text and buttons.', 'zskeleton' ); ?></p>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_color_title"><?php esc_html_e( 'Title color', 'zskeleton' ); ?></label>
				<input type="text" class="zskeleton-slider-color-field" id="zskeleton_slider_color_title" name="zskeleton_slider_color_title" value="<?php echo esc_attr( is_string( $color_title ) ? $color_title : '' ); ?>" data-default-color="#ffffff" />
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_color_desc"><?php esc_html_e( 'Description color', 'zskeleton' ); ?></label>
				<input type="text" class="zskeleton-slider-color-field" id="zskeleton_slider_color_desc" name="zskeleton_slider_color_desc" value="<?php echo esc_attr( is_string( $color_desc ) ? $color_desc : '' ); ?>" data-default-color="#f1f5f9" />
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_color_accent"><?php esc_html_e( 'Primary button accent', 'zskeleton' ); ?></label>
				<input type="text" class="zskeleton-slider-color-field" id="zskeleton_slider_color_accent" name="zskeleton_slider_color_accent" value="<?php echo esc_attr( is_string( $color_accent ) ? $color_accent : '' ); ?>" data-default-color="#2563eb" />
				<p class="zs-meta-field__hint"><?php esc_html_e( 'Used for the main call-to-action gradient. Leave empty to use the theme primary color.', 'zskeleton' ); ?></p>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_overlay_color"><?php esc_html_e( 'Overlay color', 'zskeleton' ); ?></label>
				<input type="text" class="zskeleton-slider-color-field" id="zskeleton_slider_overlay_color" name="zskeleton_slider_overlay_color" value="<?php echo esc_attr( is_string( $overlay_color ) ? $overlay_color : '' ); ?>" data-default-color="#0f172a" />
				<p class="zs-meta-field__hint"><?php esc_html_e( 'Tint over the slide background. Default: slate blue (#0f172a).', 'zskeleton' ); ?></p>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_overlay_opacity"><?php esc_html_e( 'Overlay opacity (%)', 'zskeleton' ); ?></label>
				<input type="number" class="small-text" id="zskeleton_slider_overlay_opacity" name="zskeleton_slider_overlay_opacity" min="0" max="100" step="1" value="<?php echo esc_attr( ( '' !== $overlay_op && is_numeric( $overlay_op ) ) ? (string) (int) $overlay_op : '' ); ?>" placeholder="45" />
				<p class="zs-meta-field__hint"><?php esc_html_e( '0–100. Default 45% if left empty (readable text on most photos).', 'zskeleton' ); ?></p>
			</div>
			<!-- Mobile height + navigation / dots styling (optional). -->
			<hr />
			<p class="zs-meta-field__hint" style="margin-top:0;"><strong><?php esc_html_e( 'Layout & controls (optional)', 'zskeleton' ); ?></strong></p>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_min_height_mobile"><?php esc_html_e( 'Minimum height on small screens (CSS)', 'zskeleton' ); ?></label>
				<input type="text" class="widefat" id="zskeleton_slider_min_height_mobile" name="zskeleton_slider_min_height_mobile" value="<?php echo esc_attr( is_string( $min_height_m ) ? $min_height_m : '' ); ?>" placeholder="min(52dvh, 420px)" />
				<p class="zs-meta-field__hint"><?php esc_html_e( 'Applied below ~768px. Example: 360px or min(55vh, 480px). Leave empty to match desktop min-height.', 'zskeleton' ); ?></p>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_content_image_max_height"><?php esc_html_e( 'Content image max height (CSS)', 'zskeleton' ); ?></label>
				<input type="text" class="widefat" id="zskeleton_slider_content_image_max_height" name="zskeleton_slider_content_image_max_height" value="<?php echo esc_attr( is_string( $content_img_h ) ? $content_img_h : '' ); ?>" placeholder="400px" />
				<p class="zs-meta-field__hint"><?php esc_html_e( 'Controls the image inserted from slide Content image (all layouts). Example: 400px or min(46vh, 420px). Leave empty for default 400px.', 'zskeleton' ); ?></p>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_content_image_max_height_mobile"><?php esc_html_e( 'Content image max height on mobile (CSS)', 'zskeleton' ); ?></label>
				<input type="text" class="widefat" id="zskeleton_slider_content_image_max_height_mobile" name="zskeleton_slider_content_image_max_height_mobile" value="<?php echo esc_attr( is_string( $content_img_h_m ) ? $content_img_h_m : '' ); ?>" placeholder="280px" />
				<p class="zs-meta-field__hint"><?php esc_html_e( 'Applied below ~768px. Leave empty to reuse desktop content image max height.', 'zskeleton' ); ?></p>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_nav_bg_color"><?php esc_html_e( 'Arrow button background', 'zskeleton' ); ?></label>
				<input type="text" class="zskeleton-slider-color-field" id="zskeleton_slider_nav_bg_color" name="zskeleton_slider_nav_bg_color" value="<?php echo esc_attr( is_string( $nav_bg_c ) ? $nav_bg_c : '' ); ?>" data-default-color="#ffffff" />
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_nav_bg_opacity"><?php esc_html_e( 'Arrow button background opacity (%)', 'zskeleton' ); ?></label>
				<input type="number" class="small-text" id="zskeleton_slider_nav_bg_opacity" name="zskeleton_slider_nav_bg_opacity" min="0" max="100" step="1" value="<?php echo esc_attr( ( '' !== $nav_bg_op && is_numeric( $nav_bg_op ) ) ? (string) (int) $nav_bg_op : '' ); ?>" placeholder="14" />
				<p class="zs-meta-field__hint"><?php esc_html_e( 'Used with the background color above (glass effect). Default 14% if empty.', 'zskeleton' ); ?></p>
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_nav_icon_color"><?php esc_html_e( 'Arrow icon color', 'zskeleton' ); ?></label>
				<input type="text" class="zskeleton-slider-color-field" id="zskeleton_slider_nav_icon_color" name="zskeleton_slider_nav_icon_color" value="<?php echo esc_attr( is_string( $nav_icon_c ) ? $nav_icon_c : '' ); ?>" data-default-color="#ffffff" />
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_dot_inactive_color"><?php esc_html_e( 'Dot (inactive) color', 'zskeleton' ); ?></label>
				<input type="text" class="zskeleton-slider-color-field" id="zskeleton_slider_dot_inactive_color" name="zskeleton_slider_dot_inactive_color" value="<?php echo esc_attr( is_string( $dot_in_c ) ? $dot_in_c : '' ); ?>" data-default-color="#ffffff" />
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_dot_inactive_opacity"><?php esc_html_e( 'Dot (inactive) opacity (%)', 'zskeleton' ); ?></label>
				<input type="number" class="small-text" id="zskeleton_slider_dot_inactive_opacity" name="zskeleton_slider_dot_inactive_opacity" min="0" max="100" step="1" value="<?php echo esc_attr( ( '' !== $dot_in_op && is_numeric( $dot_in_op ) ) ? (string) (int) $dot_in_op : '' ); ?>" placeholder="35" />
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_dot_active_color"><?php esc_html_e( 'Dot (active) color', 'zskeleton' ); ?></label>
				<input type="text" class="zskeleton-slider-color-field" id="zskeleton_slider_dot_active_color" name="zskeleton_slider_dot_active_color" value="<?php echo esc_attr( is_string( $dot_ac_c ) ? $dot_ac_c : '' ); ?>" data-default-color="#ffffff" />
			</div>
			<hr />
			<p class="zs-meta-field__hint" style="margin-top:0;"><strong><?php esc_html_e( 'Typography (optional)', 'zskeleton' ); ?></strong> — <?php esc_html_e( 'Any valid font-size value, e.g. clamp(1.25rem, 3vw, 2rem) or 1.125rem.', 'zskeleton' ); ?></p>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_font_title"><?php esc_html_e( 'Title font size', 'zskeleton' ); ?></label>
				<input type="text" class="widefat" id="zskeleton_slider_font_title" name="zskeleton_slider_font_title" value="<?php echo esc_attr( is_string( $font_title ) ? $font_title : '' ); ?>" placeholder="clamp(1.75rem, 4vw, 3.25rem)" />
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_font_desc"><?php esc_html_e( 'Description font size', 'zskeleton' ); ?></label>
				<input type="text" class="widefat" id="zskeleton_slider_font_desc" name="zskeleton_slider_font_desc" value="<?php echo esc_attr( is_string( $font_desc ) ? $font_desc : '' ); ?>" placeholder="clamp(1rem, 2vw, 1.2rem)" />
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_font_btn_primary"><?php esc_html_e( 'Primary button font size', 'zskeleton' ); ?></label>
				<input type="text" class="widefat" id="zskeleton_slider_font_btn_primary" name="zskeleton_slider_font_btn_primary" value="<?php echo esc_attr( is_string( $font_btn_p ) ? $font_btn_p : '' ); ?>" placeholder="0.95rem" />
			</div>
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_slider_font_btn_secondary"><?php esc_html_e( 'Secondary button font size', 'zskeleton' ); ?></label>
				<input type="text" class="widefat" id="zskeleton_slider_font_btn_secondary" name="zskeleton_slider_font_btn_secondary" value="<?php echo esc_attr( is_string( $font_btn_s ) ? $font_btn_s : '' ); ?>" placeholder="0.95rem" />
			</div>
		</div>
		<?php
	}

	/**
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public function save_slides_meta( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['zskeleton_slider_slides_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zskeleton_slider_slides_nonce'] ) ), 'zskeleton_slider_slides_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $post instanceof WP_Post || self::POST_TYPE !== $post->post_type ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$raw = array();
		if ( isset( $_POST['zskeleton_slider_slides'] ) && is_array( $_POST['zskeleton_slider_slides'] ) ) {
			$raw = wp_unslash( $_POST['zskeleton_slider_slides'] );
		}

		// Preserve numeric slide order only. The hidden JS template uses key __IDX__ — those fields must not persist as a slide row.
		$ordered = array();
		foreach ( $raw as $key => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			if ( '__IDX__' === (string) $key ) {
				continue;
			}
			if ( ! is_numeric( $key ) ) {
				continue;
			}
			$ordered[ (int) $key ] = $row;
		}
		ksort( $ordered, SORT_NUMERIC );
		$ordered = array_values( $ordered );

		$group = array( 'fields' => self::get_slide_fields_config() );
		$clean = ZSkeleton_Repeater_Registry::sanitize_rows( $group, $ordered );
		$key   = ZSkeleton_Repeater_Registry::meta_key( self::REPEATER_SLIDES );
		update_post_meta( $post_id, $key, wp_json_encode( $clean, JSON_UNESCAPED_UNICODE ) );
	}

	/**
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public function save_slider_options( $post_id, $post ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! isset( $_POST['zskeleton_slider_options_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zskeleton_slider_options_nonce'] ) ), 'zskeleton_slider_options_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! $post instanceof WP_Post || self::POST_TYPE !== $post->post_type ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		update_post_meta( $post_id, self::META_SHOW_DOTS, ! empty( $_POST['zskeleton_slider_show_dots'] ) ? '1' : '0' );
		update_post_meta( $post_id, self::META_SHOW_NAV, ! empty( $_POST['zskeleton_slider_show_nav'] ) ? '1' : '0' );

		$ap = isset( $_POST['zskeleton_slider_autoplay_ms'] ) ? (int) $_POST['zskeleton_slider_autoplay_ms'] : 0;
		if ( $ap < 0 ) {
			$ap = 0;
		}
		if ( $ap > 120000 ) {
			$ap = 120000;
		}
		update_post_meta( $post_id, self::META_AUTOPLAY_MS, $ap );

		$effect = isset( $_POST['zskeleton_slider_effect'] ) ? sanitize_key( wp_unslash( $_POST['zskeleton_slider_effect'] ) ) : 'fade';
		if ( ! in_array( $effect, array( 'fade', 'slide', 'zoom' ), true ) ) {
			$effect = 'fade';
		}
		update_post_meta( $post_id, self::META_EFFECT, $effect );

		$layout = isset( $_POST['zskeleton_slider_layout'] ) ? self::sanitize_slider_layout( wp_unslash( $_POST['zskeleton_slider_layout'] ) ) : 'hero';
		update_post_meta( $post_id, self::META_LAYOUT, $layout );

		$min_h = isset( $_POST['zskeleton_slider_min_height'] ) ? sanitize_text_field( wp_unslash( $_POST['zskeleton_slider_min_height'] ) ) : '';
		update_post_meta( $post_id, self::META_MIN_HEIGHT, $min_h );

		$ct = isset( $_POST['zskeleton_slider_color_title'] ) ? self::sanitize_slider_hex_color( wp_unslash( (string) $_POST['zskeleton_slider_color_title'] ) ) : '';
		update_post_meta( $post_id, self::META_COLOR_TITLE, $ct );

		$cd = isset( $_POST['zskeleton_slider_color_desc'] ) ? self::sanitize_slider_hex_color( wp_unslash( (string) $_POST['zskeleton_slider_color_desc'] ) ) : '';
		update_post_meta( $post_id, self::META_COLOR_DESC, $cd );

		$ca = isset( $_POST['zskeleton_slider_color_accent'] ) ? self::sanitize_slider_hex_color( wp_unslash( (string) $_POST['zskeleton_slider_color_accent'] ) ) : '';
		update_post_meta( $post_id, self::META_COLOR_ACCENT, $ca );

		$ovc = isset( $_POST['zskeleton_slider_overlay_color'] ) ? self::sanitize_slider_hex_color( wp_unslash( (string) $_POST['zskeleton_slider_overlay_color'] ) ) : '';
		update_post_meta( $post_id, self::META_OVERLAY_COLOR, $ovc );

		if ( isset( $_POST['zskeleton_slider_overlay_opacity'] ) && is_numeric( $_POST['zskeleton_slider_overlay_opacity'] ) && '' !== (string) $_POST['zskeleton_slider_overlay_opacity'] ) {
			$ovo = min( 100, max( 0, (int) $_POST['zskeleton_slider_overlay_opacity'] ) );
			update_post_meta( $post_id, self::META_OVERLAY_OPACITY, $ovo );
		} else {
			delete_post_meta( $post_id, self::META_OVERLAY_OPACITY );
		}

		$min_h_m = isset( $_POST['zskeleton_slider_min_height_mobile'] ) ? self::sanitize_slider_css_token_value( wp_unslash( (string) $_POST['zskeleton_slider_min_height_mobile'] ) ) : '';
		update_post_meta( $post_id, self::META_MIN_HEIGHT_MOBILE, $min_h_m );

		$content_img_h = isset( $_POST['zskeleton_slider_content_image_max_height'] ) ? self::sanitize_slider_css_token_value( wp_unslash( (string) $_POST['zskeleton_slider_content_image_max_height'] ) ) : '';
		update_post_meta( $post_id, self::META_CONTENT_IMAGE_MAX_HEIGHT, $content_img_h );
		$content_img_h_m = isset( $_POST['zskeleton_slider_content_image_max_height_mobile'] ) ? self::sanitize_slider_css_token_value( wp_unslash( (string) $_POST['zskeleton_slider_content_image_max_height_mobile'] ) ) : '';
		update_post_meta( $post_id, self::META_CONTENT_IMAGE_MAX_HEIGHT_MOBILE, $content_img_h_m );

		$nav_bg = isset( $_POST['zskeleton_slider_nav_bg_color'] ) ? self::sanitize_slider_hex_color( wp_unslash( (string) $_POST['zskeleton_slider_nav_bg_color'] ) ) : '';
		update_post_meta( $post_id, self::META_NAV_BG_COLOR, $nav_bg );
		if ( isset( $_POST['zskeleton_slider_nav_bg_opacity'] ) && is_numeric( $_POST['zskeleton_slider_nav_bg_opacity'] ) && '' !== (string) $_POST['zskeleton_slider_nav_bg_opacity'] ) {
			update_post_meta( $post_id, self::META_NAV_BG_OPACITY, min( 100, max( 0, (int) $_POST['zskeleton_slider_nav_bg_opacity'] ) ) );
		} else {
			delete_post_meta( $post_id, self::META_NAV_BG_OPACITY );
		}

		$nav_ic = isset( $_POST['zskeleton_slider_nav_icon_color'] ) ? self::sanitize_slider_hex_color( wp_unslash( (string) $_POST['zskeleton_slider_nav_icon_color'] ) ) : '';
		update_post_meta( $post_id, self::META_NAV_ICON_COLOR, $nav_ic );

		$dot_in = isset( $_POST['zskeleton_slider_dot_inactive_color'] ) ? self::sanitize_slider_hex_color( wp_unslash( (string) $_POST['zskeleton_slider_dot_inactive_color'] ) ) : '';
		update_post_meta( $post_id, self::META_DOT_INACTIVE_COLOR, $dot_in );
		if ( isset( $_POST['zskeleton_slider_dot_inactive_opacity'] ) && is_numeric( $_POST['zskeleton_slider_dot_inactive_opacity'] ) && '' !== (string) $_POST['zskeleton_slider_dot_inactive_opacity'] ) {
			update_post_meta( $post_id, self::META_DOT_INACTIVE_OPACITY, min( 100, max( 0, (int) $_POST['zskeleton_slider_dot_inactive_opacity'] ) ) );
		} else {
			delete_post_meta( $post_id, self::META_DOT_INACTIVE_OPACITY );
		}

		$dot_ac = isset( $_POST['zskeleton_slider_dot_active_color'] ) ? self::sanitize_slider_hex_color( wp_unslash( (string) $_POST['zskeleton_slider_dot_active_color'] ) ) : '';
		update_post_meta( $post_id, self::META_DOT_ACTIVE_COLOR, $dot_ac );

		$ft = isset( $_POST['zskeleton_slider_font_title'] ) ? self::sanitize_slider_css_token_value( wp_unslash( (string) $_POST['zskeleton_slider_font_title'] ) ) : '';
		update_post_meta( $post_id, self::META_FONT_TITLE, $ft );
		$fd = isset( $_POST['zskeleton_slider_font_desc'] ) ? self::sanitize_slider_css_token_value( wp_unslash( (string) $_POST['zskeleton_slider_font_desc'] ) ) : '';
		update_post_meta( $post_id, self::META_FONT_DESC, $fd );
		$fbp = isset( $_POST['zskeleton_slider_font_btn_primary'] ) ? self::sanitize_slider_css_token_value( wp_unslash( (string) $_POST['zskeleton_slider_font_btn_primary'] ) ) : '';
		update_post_meta( $post_id, self::META_FONT_BTN_PRIMARY, $fbp );
		$fbs = isset( $_POST['zskeleton_slider_font_btn_secondary'] ) ? self::sanitize_slider_css_token_value( wp_unslash( (string) $_POST['zskeleton_slider_font_btn_secondary'] ) ) : '';
		update_post_meta( $post_id, self::META_FONT_BTN_SECONDARY, $fbs );

		$br = isset( $_POST['zskeleton_slider_border_radius'] ) ? self::sanitize_slider_css_token_value( wp_unslash( (string) $_POST['zskeleton_slider_border_radius'] ) ) : '';
		update_post_meta( $post_id, self::META_BORDER_RADIUS, $br );
		$brc = isset( $_POST['zskeleton_slider_border_radius_controls'] ) ? self::sanitize_slider_css_token_value( wp_unslash( (string) $_POST['zskeleton_slider_border_radius_controls'] ) ) : '';
		update_post_meta( $post_id, self::META_BORDER_RADIUS_CONTROLS, $brc );
	}

	/**
	 * Published slider post or null.
	 *
	 * @param int|string $id_or_slug Post ID or post_name.
	 * @return WP_Post|null
	 */
	public static function get_slider_post( $id_or_slug ) {
		if ( is_numeric( $id_or_slug ) ) {
			$p = get_post( (int) $id_or_slug );
			if ( $p instanceof WP_Post && self::POST_TYPE === $p->post_type && 'publish' === $p->post_status ) {
				return $p;
			}
			return null;
		}
		$slug = sanitize_title( (string) $id_or_slug );
		if ( '' === $slug ) {
			return null;
		}
		$q = new WP_Query(
			array(
				'post_type'      => self::POST_TYPE,
				'name'           => $slug,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'no_found_rows'  => true,
			)
		);
		if ( $q->have_posts() ) {
			return $q->posts[0];
		}
		return null;
	}
}
