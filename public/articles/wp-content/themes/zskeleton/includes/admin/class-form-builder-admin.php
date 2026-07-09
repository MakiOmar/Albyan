<?php
/**
 * Visual form builder for zskeleton_form CPT (Vue 3 admin app).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Form builder metaboxes and save handlers.
 */
class ZSkeleton_Form_Builder_Admin {

	const NONCE_ACTION = 'zskeleton_form_builder_save';

	/**
	 * Public field types available in palette.
	 *
	 * @var string[]
	 */
	private static $palette_types = array(
		'text',
		'email',
		'tel',
		'url',
		'textarea',
		'select',
		'checkbox',
		'radio',
		'toggle',
		'number',
		'date',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
		add_action( 'save_post_' . ZSkeleton_Forms_CPT::POST_TYPE, array( $this, 'save_form' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 100 );
		add_action( 'wp_ajax_zskeleton_form_builder_preview', array( $this, 'ajax_preview' ) );
		add_filter( 'script_loader_tag', array( $this, 'filter_script_loader_tag' ), 10, 3 );
		add_filter( 'admin_body_class', array( $this, 'filter_admin_body_class' ) );
		add_action( 'admin_notices', array( $this, 'render_save_error_notice' ) );
	}

	/**
	 * Body class for form CPT layout overrides.
	 *
	 * @param string $classes Space-separated classes.
	 * @return string
	 */
	public function filter_admin_body_class( $classes ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && ZSkeleton_Forms_CPT::POST_TYPE === $screen->post_type ) {
			$classes .= ' zs-form-kit-edit-screen';
		}
		return $classes;
	}

	/**
	 * Show schema validation errors after a failed form save.
	 */
	public function render_save_error_notice() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ZSkeleton_Forms_CPT::POST_TYPE !== $screen->post_type ) {
			return;
		}

		$key     = 'zskeleton_form_builder_save_error_' . get_current_user_id();
		$message = get_transient( $key );
		if ( ! is_string( $message ) || '' === $message ) {
			return;
		}

		delete_transient( $key );
		echo '<div class="notice notice-error is-dismissible"><p><strong>' . esc_html__( 'Form could not be saved:', 'zskeleton' ) . '</strong> ' . esc_html( $message ) . '</p></div>';
	}

	/**
	 * Keep loader/bundle out of concat and avoid defer/async execution order issues.
	 *
	 * @param string $tag    Script tag.
	 * @param string $handle Handle.
	 * @param string $src    Source URL.
	 * @return string
	 */
	public function filter_script_loader_tag( $tag, $handle, $src ) {
		if ( ! in_array( $handle, array( 'zskeleton-form-builder-admin-loader' ), true ) ) {
			return $tag;
		}
		$tag = preg_replace( '/\s(defer|async)(=(["\'])[^"\']*\3)?/i', '', $tag );
		return $tag;
	}

	/**
	 * Register builder meta box; hide block editor.
	 */
	public function register_meta_boxes() {
		remove_post_type_support( ZSkeleton_Forms_CPT::POST_TYPE, 'editor' );

		add_meta_box(
			'zskeleton-form-kit',
			__( 'Form', 'zskeleton' ),
			array( $this, 'render_form_metabox' ),
			ZSkeleton_Forms_CPT::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Whether the current admin screen is editing a zskeleton_form post.
	 *
	 * @param string $hook Hook suffix.
	 * @return bool
	 */
	private function is_form_edit_screen( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return false;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && ZSkeleton_Forms_CPT::POST_TYPE === $screen->post_type ) {
			return true;
		}

		if ( 'post-new.php' === $hook && isset( $_GET['post_type'] ) ) {
			return ZSkeleton_Forms_CPT::POST_TYPE === sanitize_key( wp_unslash( $_GET['post_type'] ) );
		}

		global $post;
		return $post && ZSkeleton_Forms_CPT::POST_TYPE === $post->post_type;
	}

	/**
	 * Resolve the post being edited for bootstrap data.
	 *
	 * @param string $hook Hook suffix.
	 * @return WP_Post|null
	 */
	private function get_edit_post_for_screen( $hook ) {
		global $post;

		if ( $post instanceof WP_Post && ZSkeleton_Forms_CPT::POST_TYPE === $post->post_type ) {
			return $post;
		}

		if ( 'post.php' === $hook && isset( $_GET['post'] ) ) {
			$edit_post = get_post( absint( $_GET['post'] ) );
			if ( $edit_post instanceof WP_Post && ZSkeleton_Forms_CPT::POST_TYPE === $edit_post->post_type ) {
				return $edit_post;
			}
		}

		if ( 'post-new.php' === $hook ) {
			$edit_post = get_default_post_to_edit( ZSkeleton_Forms_CPT::POST_TYPE, false );
			if ( $edit_post instanceof WP_Post ) {
				return $edit_post;
			}
		}

		return null;
	}

	/**
	 * @param string $hook Hook suffix.
	 */
	public function enqueue_assets( $hook ) {
		if ( ! $this->is_form_edit_screen( $hook ) ) {
			return;
		}

		$edit_post = $this->get_edit_post_for_screen( $hook );
		if ( ! $edit_post ) {
			return;
		}

		$theme_dir    = get_template_directory();
		$theme_uri    = get_template_directory_uri();
		$css_path     = $theme_dir . '/assets/css/form-builder-admin.css';
		$js_path      = $theme_dir . '/assets/js/form-builder-admin.js';
		$loader_path  = $theme_dir . '/assets/js/form-builder-admin-loader.js';

		if ( ! file_exists( $js_path ) || ! file_exists( $loader_path ) ) {
			return;
		}

		$css_ver    = file_exists( $css_path ) ? (string) filemtime( $css_path ) : ZSkeleton_FORM_KIT_VERSION;
		$js_ver     = (string) filemtime( $js_path );
		$loader_ver = (string) filemtime( $loader_path );

		wp_enqueue_style(
			'zskeleton-form-builder-admin',
			$theme_uri . '/assets/css/form-builder-admin.css',
			array( 'common', 'forms', 'zskeleton-admin' ),
			$css_ver
		);

		// Front-end form styles for the live preview panel (AJAX returns HTML only).
		$use_min = (bool) get_option( 'zskeleton_use_minified_assets', true );
		$fk_css  = $use_min && file_exists( $theme_dir . '/assets/css/form-kit.min.css' ) ? 'form-kit.min.css' : 'form-kit.css';
		$fk_path = $theme_dir . '/assets/css/' . $fk_css;
		if ( file_exists( $fk_path ) ) {
			wp_enqueue_style(
				'zskeleton-form-kit',
				$theme_uri . '/assets/css/' . $fk_css,
				array( 'zskeleton-form-builder-admin' ),
				(string) filemtime( $fk_path )
			);
		}

		if ( class_exists( 'ZSkeleton_Form_Assets' ) ) {
			ZSkeleton_Form_Assets::enqueue_intl_tel_assets();
		}

		$bootstrap = $this->get_bootstrap( $edit_post );
		$json      = wp_json_encode( $bootstrap, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
		if ( ! is_string( $json ) ) {
			$json = '{}';
		}

		$bundle_url = add_query_arg( 'ver', $js_ver, $theme_uri . '/assets/js/form-builder-admin.js' );

		wp_register_script(
			'zskeleton-form-builder-admin-loader',
			$theme_uri . '/assets/js/form-builder-admin-loader.js',
			array(),
			$loader_ver,
			true
		);

		global $wp_scripts;
		if ( $wp_scripts instanceof WP_Scripts ) {
			$wp_scripts->add_data( 'zskeleton-form-builder-admin-loader', 'group', 1 );
		}

		wp_add_inline_script(
			'zskeleton-form-builder-admin-loader',
			'window.zsFormKitBootstrap = ' . $json . ';',
			'before'
		);

		wp_add_inline_script(
			'zskeleton-form-builder-admin-loader',
			'window.zsFormKitBuilderUrl = ' . wp_json_encode( $bundle_url ) . ';',
			'before'
		);

		wp_enqueue_script( 'zskeleton-form-builder-admin-loader' );

		wp_add_inline_style(
			'zskeleton-form-builder-admin',
			$this->get_critical_builder_css()
			. $this->get_admin_layout_css()
			. $this->get_portal_critical_css()
		);
	}

	/**
	 * Full-width form editor shell (metabox + postbox layout).
	 *
	 * @return string
	 */
	private function get_admin_layout_css() {
		return 'body.zs-form-kit-edit-screen #poststuff{max-width:none;min-width:0}'
			. 'body.zs-form-kit-edit-screen #poststuff #post-body.columns-2{margin-right:300px}'
			. 'body.zs-form-kit-edit-screen #zskeleton-form-kit.postbox{max-width:100%;margin:0 0 12px;box-sizing:border-box}'
			. 'body.zs-form-kit-edit-screen #zskeleton-form-kit.postbox>.inside{margin:0;padding:0;border:0;background:transparent;box-shadow:none}'
			. 'body.zs-form-kit-edit-screen #zskeleton-form-kit.postbox>.postbox-header{border-bottom:1px solid #e2e8f0;background:#fff}'
			. 'body.zs-form-kit-edit-screen #zs-form-kit-app{width:100%;max-width:100%;padding:16px 20px 20px;background:#f1f5f9;box-sizing:border-box;overflow:hidden}'
			. 'body.zs-form-kit-edit-screen #zs-form-kit-app>.description,body.zs-form-kit-edit-screen #zs-form-kit-app>p.description{display:none}'
			. '@media screen and (max-width:850px){body.zs-form-kit-edit-screen #poststuff #post-body.columns-2{margin-right:0}}';
	}

	/**
	 * Inline critical CSS (tabs + grid) to avoid FOUC before the main bundle paints.
	 *
	 * @return string
	 */
	private function get_critical_builder_css() {
		$btn = '#poststuff #zs-form-kit-app button.zs-fb-btn{display:inline-flex;align-items:center;gap:.35rem;width:auto;max-width:none;padding:.5rem .9rem;min-height:2.125rem;font-size:13px;font-weight:500;line-height:1.35;color:#334155;background:#fff;border:1px solid #cbd5e1;border-radius:.5rem;cursor:pointer;box-shadow:none}';
		$btn_pri = '#poststuff #zs-form-kit-app button.zs-fb-btn.zs-fb-btn--primary{color:#fff;background:#4f46e5;border-color:#4f46e5}';
		$palette = '#poststuff #zs-form-kit-app button.zs-fb-palette-btn{display:flex;align-items:center;gap:.5rem;width:100%;padding:.6rem .75rem;font-size:13px;font-weight:500;text-align:left;color:#334155;background:#fff;border:1px solid #e2e8f0;border-radius:.5rem;cursor:pointer;box-shadow:none}';
		$search = '#poststuff #zs-form-kit-app input.zs-fb-search[type="search"]{display:block;width:100%;margin:0;padding:.55rem .75rem .55rem 2rem;font-size:13px;color:#334155;background:#fff url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'14\' height=\'14\' fill=\'%2394a3b8\' viewBox=\'0 0 16 16\'%3E%3Cpath d=\'M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.242 1.01a5 5 0 1 1 0-10 5 5 0 0 1 0 10z\'/%3E%3C/svg%3E") no-repeat .55rem center;border:1px solid #e2e8f0;border-radius:.5rem;box-shadow:none}';

		return '#zs-form-kit-app:not(.is-ready){visibility:hidden;min-height:280px}'
			. '#zs-form-kit-app{--zs-fb-accent:#4f46e5;--zs-fb-border:#e2e8f0;--zs-fb-surface:#fff;font-family:ui-sans-serif,system-ui,sans-serif;font-size:13px;color:#1e293b}'
			. '#zs-form-kit-app .zs-fb-tabs{display:inline-flex;flex-wrap:wrap;gap:.25rem;padding:.25rem;margin-bottom:1.25rem;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:.75rem}'
			. '#zs-form-kit-app .zs-fb-tab{padding:.5rem 1rem;font-size:13px;font-weight:500;color:#64748b;background:transparent;border:0;border-radius:.5rem;cursor:pointer}'
			. '#zs-form-kit-app .zs-fb-tab.is-active{color:#4f46e5;font-weight:600;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.08)}'
			. '#zs-form-kit-app .zs-fb-toolbar{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:1rem 1.5rem;align-items:center;padding:1rem 1.25rem;margin-bottom:1.25rem;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem}'
			. '#zs-form-kit-app .zs-fb-toolbar__actions{display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:.5rem}'
			. '#zs-form-kit-app .zs-fb-toolbar__group{display:flex;flex-wrap:wrap;gap:.5rem;padding:.25rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.5rem}'
			. '#zs-form-kit-app .zs-fb-layout{display:grid;grid-template-columns:1fr;gap:1.25rem;min-width:0}'
			. '@media(min-width:900px){#zs-form-kit-app .zs-fb-layout{grid-template-columns:minmax(15rem,17rem) minmax(0,1fr);gap:1.5rem}}'
			. '#zs-form-kit-app .zs-fb-panel{background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;box-shadow:0 1px 2px rgba(15,23,42,.06);overflow:hidden;min-width:0}'
			. '#zs-form-kit-app .zs-fb-panel__head{margin:0;padding:.75rem 1rem;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;background:#f8fafc;border-bottom:1px solid #e2e8f0}'
			. '#zs-form-kit-app .zs-fb-panel__body{padding:1rem 1.25rem 1.25rem}'
			. '#zs-form-kit-app button.zs-fb-tab,#zs-form-kit-app button.zs-fb-btn,#zs-form-kit-app button.zs-fb-palette-btn,#zs-form-kit-app button.zs-fb-icon-btn{appearance:none;-webkit-appearance:none;margin:0;background-image:none;text-shadow:none}'
			. $btn . $btn_pri . $palette . $search
			. '#poststuff #zs-form-kit-app .zs-fb-field-card__surface{background:linear-gradient(135deg,#f8fafc,#fff 52%,#f1f5f9);border:1px solid #dbeafe;border-radius:.625rem;box-shadow:0 1px 2px rgba(15,23,42,.05),inset 0 1px 0 rgba(255,255,255,.85)}'
			. '#poststuff #zs-form-kit-app .zs-fb-field-card__head{display:flex;align-items:center;gap:.75rem;padding:.75rem .85rem .75rem .75rem}'
			. '#poststuff #zs-form-kit-app .zs-fb-field-card__lead{display:flex;align-items:center;gap:.65rem;flex:1 1 auto;min-width:0;cursor:grab}'
			. '#poststuff #zs-form-kit-app .zs-fb-grip{display:inline-flex;align-items:center;justify-content:center;width:1.75rem;height:2rem;color:#64748b;background:#fff;border:1px solid #cbd5e1;border-radius:.375rem;cursor:grab;flex-shrink:0}'
			. '#poststuff #zs-form-kit-app .zs-fb-grip__icon{display:block;width:.875rem;height:.875rem}'
			. '#poststuff #zs-form-kit-app .zs-fb-field-card__actions{display:flex;align-items:center;gap:.5rem}'
			. '#poststuff #zs-form-kit-app button.zs-fb-icon-btn{display:inline-flex;align-items:center;justify-content:center;width:2.125rem;height:2.125rem;padding:0;background:#fff;border:1px solid #e2e8f0;border-radius:.5rem;box-shadow:none}'
			. '#poststuff #zs-form-kit-app button.zs-fb-icon-btn.zs-fb-icon-btn--edit{color:#4f46e5;background:#eef2ff;border-color:#a5b4fc}'
			. '#poststuff #zs-form-kit-app button.zs-fb-icon-btn.zs-fb-icon-btn--danger{color:#dc2626;background:#fff;border-color:#fca5a5}'
			. '#poststuff #zs-form-kit-app button.zs-fb-btn.zs-fb-btn--ghost{color:#64748b;background:#fff;border-color:#e2e8f0}'
			. '#poststuff #zs-form-kit-app .zs-fb-settings-card,#poststuff #zs-form-kit-app .zs-fb-event-card{padding:1rem 1.15rem;background:linear-gradient(135deg,#f8fafc,#fff 55%,#f1f5f9);border:1px solid #e2e8f0;border-radius:.625rem}'
			. '#poststuff #zs-form-kit-app .zs-fb-control__label{display:block;margin:0 0 .4rem;font-size:12px;font-weight:600;color:#475569}'
			. '#poststuff #zs-form-kit-app .zs-fb-settings input.zs-fb-input,#poststuff #zs-form-kit-app .zs-fb-settings textarea.zs-fb-textarea,#poststuff #zs-form-kit-app .zs-fb-settings select.zs-fb-select,#poststuff #zs-form-kit-app .zs-fb-event-card input.zs-fb-input,#poststuff #zs-form-kit-app .zs-fb-event-card select.zs-fb-select{display:block;width:100%;margin:0;padding:.6rem .75rem;font-size:13px;color:#0f172a;background:#fff;border:1px solid #cbd5e1;border-radius:.5rem;box-shadow:none;pointer-events:auto}'
			. '#poststuff #zs-form-kit-app .zs-fb-event-card select.zs-fb-select{appearance:none;padding-right:2rem;background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' fill=\'%2364748b\' viewBox=\'0 0 16 16\'%3E%3Cpath d=\'M4.646 6.646a.5.5 0 0 1 .708 0L8 9.293l2.646-2.647a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-3-3a.5.5 0 0 1 0-.708z\'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right .65rem center;cursor:pointer}'
			. '#poststuff #zs-form-kit-app .zs-fb-toggle-row--card{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.85rem 1rem;background:#fff;border:1px solid #e2e8f0;border-radius:.5rem}'
			. '.zs-fb-modal,body.zs-form-kit-edit-screen .zs-fb-modal{position:fixed;inset:0;z-index:160001;display:flex;align-items:center;justify-content:center;padding:1.5rem;background-color:rgba(15,23,42,.55);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px)}'
			. '.zs-fb-modal__dialog,body.zs-form-kit-edit-screen .zs-fb-modal__dialog{display:flex;flex-direction:column;width:100%;max-width:32rem;max-height:calc(100vh - 3rem);margin:auto;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;box-shadow:0 25px 50px -12px rgba(15,23,42,.35)}'
			. '.zs-fb-modal__body,body.zs-form-kit-edit-screen .zs-fb-modal__body{background:#fff}'
			. 'body.zs-form-kit-edit-screen .zs-fb-modal input.zs-fb-input,body.zs-form-kit-edit-screen .zs-fb-modal select.zs-fb-select,body.zs-form-kit-edit-screen .zs-fb-modal textarea.zs-fb-textarea{display:block;width:100%;margin:0;padding:.6rem .75rem;font-size:13px;color:#0f172a;background:#fff;border:1px solid #cbd5e1;border-radius:.5rem;box-shadow:none}'
			. 'body.zs-form-kit-edit-screen .zs-fb-modal .zs-fb-control label{display:block;margin:0 0 .4rem;font-size:12px;font-weight:600;color:#475569}';
	}

	/**
	 * Portal modal CSS (teleported #zs-form-kit-portal) — inline fallback if bundle is cached.
	 *
	 * @return string
	 */
	private function get_portal_critical_css() {
		return '#zs-form-kit-portal{font-family:ui-sans-serif,system-ui,sans-serif;font-size:13px;color:#1e293b}'
			. '#zs-form-kit-portal .zs-fb-modal{position:fixed;inset:0;z-index:160001;display:flex;align-items:center;justify-content:center;padding:1.5rem;background-color:rgba(15,23,42,.58);backdrop-filter:blur(6px)}'
			. '#zs-form-kit-portal .zs-fb-modal__dialog{display:flex;flex-direction:column;width:100%;max-width:26rem;max-height:min(calc(100vh - 2rem),36rem);margin:auto;overflow:hidden;background:#fff;border:1px solid #e2e8f0;border-radius:.875rem;box-shadow:0 25px 50px -12px rgba(15,23,42,.38)}'
			. '#zs-form-kit-portal .zs-fb-inspector__head{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-shrink:0;padding:1.5rem 1.75rem 1.35rem;background:linear-gradient(180deg,#f8fafc,#fff);border-bottom:1px solid #e2e8f0}'
			. '#zs-form-kit-portal .zs-fb-inspector__body,#zs-form-kit-portal .zs-fb-modal__body{flex:1 1 auto;min-height:0;overflow-y:auto;padding:0;background:#f1f5f9}'
			. '#zs-form-kit-portal .zs-fb-inspector__sections{display:flex;flex-direction:column;gap:1rem;padding:1.25rem 1.5rem 1.5rem}'
			. '#zs-form-kit-portal .zs-fb-inspector__section{padding:1.25rem 1.35rem;background:#fff;border:1px solid #e2e8f0;border-radius:.75rem}'
			. '#zs-form-kit-portal .zs-fb-inspector__fields{display:flex;flex-direction:column;gap:1.15rem}'
			. '#zs-form-kit-portal .zs-fb-control label{display:block;margin:0 0 .45rem;font-size:12px;font-weight:600;color:#475569}'
			. '#zs-form-kit-portal input.zs-fb-input,#zs-form-kit-portal select.zs-fb-select,#zs-form-kit-portal textarea.zs-fb-textarea{display:block;width:100%;margin:0;padding:.65rem .85rem;font-size:13px;color:#0f172a;background:#fff;border:1px solid #cbd5e1;border-radius:.5rem;box-shadow:none}'
			. '#zs-form-kit-portal .zs-fb-toggle-row--card{display:flex;align-items:center;justify-content:space-between;gap:1.25rem;padding:1rem 1.1rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.625rem}'
			. '#zs-form-kit-portal .zs-fb-switch{position:relative;display:inline-block;width:2.875rem;height:1.625rem;flex-shrink:0}'
			. '#zs-form-kit-portal .zs-fb-switch input{opacity:0;width:0;height:0;position:absolute}'
			. '#zs-form-kit-portal .zs-fb-switch__track{position:absolute;inset:0;background:#cbd5e1;border-radius:999px;cursor:pointer}'
			. '#zs-form-kit-portal .zs-fb-switch input:checked+.zs-fb-switch__track{background:#4f46e5}'
			. '#zs-form-kit-portal .zs-fb-modal__foot{display:flex;justify-content:flex-end;gap:.65rem;flex-shrink:0;padding:1rem 1.75rem;border-top:1px solid #e2e8f0;background:#fff}'
			. '#zs-form-kit-portal button.zs-fb-btn{display:inline-flex;align-items:center;padding:.55rem 1rem;min-height:2.25rem;font-size:13px;font-weight:500;color:#334155;background:#fff;border:1px solid #cbd5e1;border-radius:.5rem;cursor:pointer;box-shadow:none}'
			. '#zs-form-kit-portal button.zs-fb-btn.zs-fb-btn--primary{color:#fff;background:#4f46e5;border-color:#4f46e5}'
			. '#zs-form-kit-portal button.zs-fb-icon-btn.zs-fb-modal__close{width:2.5rem;height:2.5rem;min-width:2.5rem;padding:0;display:inline-flex;align-items:center;justify-content:center;background:#fff;border:1px solid #e2e8f0;border-radius:.5rem}'
			. '#zs-form-kit-portal .zs-fb-icon-btn__svg{display:block;width:1.125rem;height:1.125rem}';
	}

	/**
	 * Build bootstrap payload for the Vue app.
	 *
	 * @param WP_Post $post Post.
	 * @return array<string,mixed>
	 */
	private function get_bootstrap( WP_Post $post ) {
		$schema      = ZSkeleton_Forms_CPT::get_schema( $post->ID );
		$layout_tree = ! empty( $schema['layout_tree'] ) ? $schema['layout_tree'] : array();
		if ( empty( $layout_tree ) && ! empty( $schema['fields'] ) ) {
			foreach ( $schema['fields'] as $field ) {
				$layout_tree[] = array(
					'type'  => 'field',
					'field' => $field,
				);
			}
		}

		$events = ZSkeleton_Forms_CPT::get_events( $post->ID );
		if ( empty( $events ) ) {
			$events = array(
				array( 'type' => 'save_submission', 'enabled' => true ),
				array(
					'type'    => 'email_admin',
					'enabled' => true,
					'subject' => __( 'New form submission', 'zskeleton' ),
				),
			);
		}
		$events = $this->migrate_legacy_redirect_event(
			$events,
			isset( $schema['redirect_url'] ) ? (string) $schema['redirect_url'] : ''
		);

		$form_id   = ZSkeleton_Forms_CPT::get_form_id_for_post( $post->ID );
		$shortcode = '' !== $form_id ? '[zskeleton_form id="' . $form_id . '"]' : '';

		$event_types = array(
			array(
				'value' => 'save_submission',
				'label' => __( 'Save submission', 'zskeleton' ),
			),
			array(
				'value' => 'email_admin',
				'label' => __( 'Email admin', 'zskeleton' ),
			),
			array(
				'value' => 'email_user',
				'label' => __( 'Email user', 'zskeleton' ),
			),
			array(
				'value' => 'mailerlite_subscribe',
				'label' => __( 'MailerLite subscribe', 'zskeleton' ),
			),
			array(
				'value' => 'redirect',
				'label' => __( 'Redirect visitor', 'zskeleton' ),
			),
		);

		return array(
			'postId'      => (int) $post->ID,
			'formId'      => $form_id,
			'shortcode'   => $shortcode,
			'layoutTree'  => $layout_tree,
			'assetDebug'  => $this->get_asset_debug(),
			'settings'    => array(
				'allowPublic'      => ! empty( $schema['allow_public_submission'] ),
				'honeypot'         => isset( $schema['honeypot'] ) ? (string) $schema['honeypot'] : 'company_website',
				'successMessage'   => isset( $schema['success_message'] ) ? (string) $schema['success_message'] : '',
				'redirectUrl'      => isset( $schema['redirect_url'] ) ? (string) $schema['redirect_url'] : '',
				'submitButtonText' => isset( $schema['submit_button_text'] ) ? (string) $schema['submit_button_text'] : '',
				'submissionsManagerRoles' => self::access_list_to_csv( $schema['submissions_manager_roles'] ?? '' ),
				'submissionsManagerUsers' => self::access_list_to_csv( $schema['submissions_manager_users'] ?? '' ),
				'mobileStackRows'  => ! isset( $schema['layout']['mobile_stack_rows'] ) || ! empty( $schema['layout']['mobile_stack_rows'] ),
			),
			'events'      => $events,
			'fieldTypes'  => self::$palette_types,
			'eventTypes'  => $event_types,
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'zskeleton_form_builder' ),
			'strings'     => array(
				'tabBuilder'           => __( 'Builder', 'zskeleton' ),
				'tabSettings'          => __( 'Settings', 'zskeleton' ),
				'tabEvents'            => __( 'After submit', 'zskeleton' ),
				'shortcode'            => __( 'Shortcode', 'zskeleton' ),
				'addRow2'              => __( 'Add 2-column row', 'zskeleton' ),
				'addRow3'              => __( 'Add 3-column row', 'zskeleton' ),
				'refreshPreview'       => __( 'Refresh preview', 'zskeleton' ),
				'openPreview'          => __( 'Open preview', 'zskeleton' ),
				'dropField'            => __( 'Drop field here', 'zskeleton' ),
				'canvasEmpty'          => __( 'Add fields from the palette or add a row.', 'zskeleton' ),
				'dropFieldHint'        => __( 'Drag to reorder · Click a field to edit', 'zskeleton' ),
				'expand'               => __( 'Show', 'zskeleton' ),
				'collapse'             => __( 'Hide', 'zskeleton' ),
				'cols'                 => __( 'cols', 'zskeleton' ),
				'copy'                 => __( 'Copy', 'zskeleton' ),
				'close'                => __( 'Close', 'zskeleton' ),
				'done'                 => __( 'Done', 'zskeleton' ),
				'copied'               => __( 'Copied!', 'zskeleton' ),
				'searchFields'         => __( 'Search field types…', 'zskeleton' ),
				'noFieldMatch'         => __( 'No matching field types.', 'zskeleton' ),
				'formLayout'           => __( 'Form layout', 'zskeleton' ),
				'layoutGroup'          => __( 'Layout', 'zskeleton' ),
				'live'                 => __( 'Live', 'zskeleton' ),
				'sectionGeneral'       => __( 'General', 'zskeleton' ),
				'sectionValidation'    => __( 'Validation', 'zskeleton' ),
				'sectionDisplay'       => __( 'Display', 'zskeleton' ),
				'sectionSubmission'    => __( 'Submission', 'zskeleton' ),
				'sectionSecurity'      => __( 'Security', 'zskeleton' ),
				'sectionAfterSubmit'   => __( 'After submit', 'zskeleton' ),
				'fields'               => __( 'Fields', 'zskeleton' ),
				'fieldsPalette'        => __( 'Field types', 'zskeleton' ),
				'fieldLabel'           => __( 'Field', 'zskeleton' ),
				'rowLabel'             => __( 'Row', 'zskeleton' ),
				'stackMobile'          => __( 'Stack on mobile', 'zskeleton' ),
				'confirmDelete'        => __( 'Remove this item?', 'zskeleton' ),
				'inspectorTitle'       => __( 'Field settings', 'zskeleton' ),
				'label'                => __( 'Label', 'zskeleton' ),
				'name'                 => __( 'Field name', 'zskeleton' ),
				'nameHelp'             => __( 'Latin letters, numbers, and underscores only. Used when saving submissions.', 'zskeleton' ),
				'type'                 => __( 'Type', 'zskeleton' ),
				'required'             => __( 'Required', 'zskeleton' ),
				'requiredHelp'         => __( 'Visitors must fill this field before submitting.', 'zskeleton' ),
				'pattern'              => __( 'Regex pattern', 'zskeleton' ),
				'patternPlaceholder'   => __( '^[A-Za-z0-9]+$', 'zskeleton' ),
				'patternHelp'          => __( 'Optional. Validates the value on submit. Use a JavaScript-style pattern (slashes and flags are optional).', 'zskeleton' ),
				'patternMessage'       => __( 'Validation message', 'zskeleton' ),
				'patternMessagePlaceholder' => __( 'Invalid format.', 'zskeleton' ),
				'patternMessageHelp'   => __( 'Shown when the value does not match the pattern.', 'zskeleton' ),
				'sectionPhone'         => __( 'Phone input', 'zskeleton' ),
				'intlTel'              => __( 'Country dial codes', 'zskeleton' ),
				'intlTelHelp'          => __( 'Show a country selector with international dial codes (intl-tel-input).', 'zskeleton' ),
				'initialCountry'       => __( 'Default country', 'zskeleton' ),
				'initialCountryAuto'   => __( 'Auto-detect visitor country', 'zskeleton' ),
				'intlTelPreview'       => __( 'Preview', 'zskeleton' ),
				'placeholder'          => __( 'Placeholder', 'zskeleton' ),
				'description'          => __( 'Description', 'zskeleton' ),
				'choices'              => __( 'Choices (one per line: value|Label)', 'zskeleton' ),
				'preview'              => __( 'Preview', 'zskeleton' ),
				'previewFailed'        => __( 'Preview failed.', 'zskeleton' ),
				'loading'              => __( 'Loading…', 'zskeleton' ),
				'selectField'          => __( 'Select a field to edit its settings.', 'zskeleton' ),
				'edit'                 => __( 'Edit', 'zskeleton' ),
				'remove'               => __( 'Remove', 'zskeleton' ),
				'allowPublic'          => __( 'Allow public submissions', 'zskeleton' ),
				'honeypot'             => __( 'Honeypot field name', 'zskeleton' ),
				'successMessage'       => __( 'Success message', 'zskeleton' ),
				'submitButtonText'     => __( 'Submit button text', 'zskeleton' ),
				'submitButtonTextHint' => __( 'Label on the primary submit button. Leave empty for the default “Submit”.', 'zskeleton' ),
				'submitButtonTextPlaceholder' => __( 'Submit', 'zskeleton' ),
				'sectionSubmissionsManager' => __( 'Submissions manager', 'zskeleton' ),
				'submissionsManagerRoles' => __( 'Who can manage submissions (roles)', 'zskeleton' ),
				'submissionsManagerRolesHint' => __( 'Comma-separated WordPress role slugs (e.g. editor, administrator). When set here, shortcode roles/users attributes are ignored.', 'zskeleton' ),
				'submissionsManagerUsers' => __( 'Who can manage submissions (users)', 'zskeleton' ),
				'submissionsManagerUsersHint' => __( 'Comma-separated user IDs. Combined with roles above (either match grants access). Site admins always have access.', 'zskeleton' ),
				'sectionLayout'        => __( 'Layout', 'zskeleton' ),
				'mobileStackRows'      => __( 'Stack columns on mobile', 'zskeleton' ),
				'mobileStackRowsHint'  => __( 'Force multi-column rows to stack into a single column on small screens.', 'zskeleton' ),
				'redirectUrl'          => __( 'Redirect URL', 'zskeleton' ),
				'redirectUrlHint'      => __( 'Absolute URL or site path (e.g. /thank-you). Use {field_name} tokens from submitted values.', 'zskeleton' ),
				'redirectMovedHint'    => __( 'To redirect visitors after submit, add a Redirect action on the After submit tab and drag it to the desired position in the list.', 'zskeleton' ),
				'successMessageHint'   => __( 'Shown after submit when no redirect action runs.', 'zskeleton' ),
				'eventsOrderHint'      => __( 'Actions run top to bottom. Drag to reorder — e.g. save first, email next, redirect last.', 'zskeleton' ),
				'eventsEmpty'          => __( 'No actions yet. Add one to save submissions, send email, or redirect visitors.', 'zskeleton' ),
				'dragAction'           => __( 'Drag to reorder action', 'zskeleton' ),
				'actionType'           => __( 'Action', 'zskeleton' ),
				'addAction'            => __( 'Add action', 'zskeleton' ),
				'enabled'              => __( 'Enabled', 'zskeleton' ),
				'adminEmail'           => __( 'Admin email', 'zskeleton' ),
				'subject'              => __( 'Subject', 'zskeleton' ),
				'toField'              => __( 'Email field name', 'zskeleton' ),
				'body'                 => __( 'Email body', 'zskeleton' ),
				'emailField'           => __( 'Email field name', 'zskeleton' ),
				'groupKey'             => __( 'MailerLite group key', 'zskeleton' ),
				'defaultAdminSubject'  => __( 'New form submission', 'zskeleton' ),
			),
		);
	}

	/**
	 * Debug metadata for the Vue admin app (WP_DEBUG only).
	 *
	 * @return array<string,mixed>
	 */
	private function get_asset_debug() {
		$theme_dir = get_template_directory();
		$theme_uri = get_template_directory_uri();
		$css_path  = $theme_dir . '/assets/css/form-builder-admin.css';
		$js_path   = $theme_dir . '/assets/js/form-builder-admin.js';

		return array(
			'enabled'  => ( defined( 'WP_DEBUG' ) && WP_DEBUG )
				|| ( isset( $_GET['zs_fb_debug'] ) && current_user_can( 'edit_posts' ) ),
			'cssVer'   => file_exists( $css_path ) ? (string) filemtime( $css_path ) : '',
			'cssBytes' => file_exists( $css_path ) ? (int) filesize( $css_path ) : 0,
			'jsVer'    => file_exists( $js_path ) ? (string) filemtime( $js_path ) : '',
			'cssUrl'   => $theme_uri . '/assets/css/form-builder-admin.css',
		);
	}

	/**
	 * @param WP_Post $post Post.
	 */
	public function render_form_metabox( $post ) {
		wp_nonce_field( self::NONCE_ACTION, 'zskeleton_form_builder_nonce' );
		?>
		<div id="zs-form-kit-app">
			<p class="description"><?php esc_html_e( 'Loading form builder…', 'zskeleton' ); ?></p>
		</div>
		<?php
	}

	/**
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post.
	 */
	public function save_form( $post_id, $post ) {
		if ( ! isset( $_POST['zskeleton_form_builder_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zskeleton_form_builder_nonce'] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$schema_raw = isset( $_POST['zskeleton_form_schema_json'] ) ? wp_unslash( $_POST['zskeleton_form_schema_json'] ) : '';
		$schema     = json_decode( $schema_raw, true );
		if ( ! is_array( $schema ) ) {
			$layout_raw = isset( $_POST['zskeleton_form_layout_tree_json'] ) ? wp_unslash( $_POST['zskeleton_form_layout_tree_json'] ) : '[]';
			$layout     = json_decode( $layout_raw, true );
			if ( ! is_array( $layout ) ) {
				$layout = array();
			}
			$schema = array(
				'context'                 => 'public',
				'allow_public_submission' => true,
				'use_ajax'                => true,
				'fallback'                => 'long_page',
				'layout_tree'             => $layout,
				'layout'                  => array( 'mobile_stack_rows' => true ),
			);
		}

		$layout = isset( $schema['layout_tree'] ) && is_array( $schema['layout_tree'] ) ? $schema['layout_tree'] : array();
		$is_public = ! empty( $schema['allow_public_submission'] );

		$save_schema = array(
			'context'                 => 'public',
			'allow_public_submission' => $is_public,
			'use_ajax'                => true,
			'fallback'                => 'long_page',
			'layout_tree'             => $layout,
			'honeypot'                => isset( $schema['honeypot'] ) ? sanitize_key( (string) $schema['honeypot'] ) : '',
			'success_message'         => isset( $schema['success_message'] ) ? sanitize_textarea_field( (string) $schema['success_message'] ) : '',
			'submit_button_text'      => isset( $schema['submit_button_text'] ) ? sanitize_text_field( (string) $schema['submit_button_text'] ) : '',
			'submissions_manager_roles' => $schema['submissions_manager_roles'] ?? '',
			'submissions_manager_users' => $schema['submissions_manager_users'] ?? '',
			'layout'                  => array(
				'mobile_stack_rows' => ! isset( $schema['layout']['mobile_stack_rows'] ) || ! empty( $schema['layout']['mobile_stack_rows'] ),
			),
		);

		$clean_schema = ZSkeleton_Form_Schema_Sanitizer::sanitize_schema( $save_schema, $is_public );
		if ( is_wp_error( $clean_schema ) ) {
			set_transient(
				'zskeleton_form_builder_save_error_' . get_current_user_id(),
				$clean_schema->get_error_message(),
				MINUTE_IN_SECONDS
			);
			return;
		}

		$events_raw = isset( $_POST['zskeleton_form_events_json'] ) ? wp_unslash( $_POST['zskeleton_form_events_json'] ) : '[]';
		$events     = json_decode( $events_raw, true );
		if ( ! is_array( $events ) ) {
			$events = array();
		}
		$clean_events = ZSkeleton_Form_Schema_Sanitizer::sanitize_events( $events );

		unset( $clean_schema['redirect_url'] );

		update_post_meta( $post_id, ZSkeleton_Forms_CPT::META_SCHEMA, wp_json_encode( $clean_schema, JSON_UNESCAPED_UNICODE ) );
		update_post_meta( $post_id, ZSkeleton_Forms_CPT::META_EVENTS, wp_json_encode( $clean_events, JSON_UNESCAPED_UNICODE ) );

		ZSkeleton_Form_Registry_Loader::bust_cache( $post_id );
	}

	/**
	 * AJAX preview of draft layout.
	 */
	public function ajax_preview() {
		check_ajax_referer( 'zskeleton_form_builder', 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( $post_id > 0 ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Permission denied.', 'zskeleton' ) ), 403 );
			}
		} elseif ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'zskeleton' ) ), 403 );
		}

		$layout_raw = isset( $_POST['layout_tree'] ) ? wp_unslash( $_POST['layout_tree'] ) : '[]';
		$layout     = json_decode( $layout_raw, true );
		if ( ! is_array( $layout ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid layout JSON.', 'zskeleton' ) ), 400 );
		}

		$schema = array(
			'context'                 => 'public',
			'allow_public_submission' => true,
			'layout_tree'             => $layout,
			'honeypot'                => 'preview_hp',
		);
		$clean  = ZSkeleton_Form_Schema_Sanitizer::sanitize_schema( $schema, true );
		if ( is_wp_error( $clean ) ) {
			wp_send_json_error( array( 'message' => $clean->get_error_message() ), 400 );
		}

		$temp_id = sanitize_key( 'preview_' . wp_generate_password( 6, false, false ) );
		if ( '' === $temp_id ) {
			$temp_id = 'preview_' . wp_generate_password( 8, false, false );
			$temp_id = sanitize_key( $temp_id );
		}

		$clean['id']        = $temp_id;
		$clean['honeypot']  = 'preview_hp';
		$clean['use_ajax']  = true;
		$clean['fallback']  = 'long_page';

		add_filter(
			'zskeleton_form_kit_forms',
			function ( $forms ) use ( $temp_id, $clean ) {
				$forms[ $temp_id ] = $clean;
				return $forms;
			},
			99
		);

		ZSkeleton_Form_Assets::request_enqueue( 'public' );
		$html = ZSkeleton_Form_Renderer::render( $temp_id );
		if ( '' === $html ) {
			wp_send_json_error( array( 'message' => __( 'Could not render preview.', 'zskeleton' ) ), 500 );
		}

		wp_send_json_success( array( 'html' => $html ) );
	}

	/**
	 * Move legacy schema redirect_url into the ordered events list once.
	 *
	 * @param array<int,array<string,mixed>> $events Events.
	 * @param string                         $redirect_url Legacy redirect URL.
	 * @return array<int,array<string,mixed>>
	 */
	private function migrate_legacy_redirect_event( array $events, $redirect_url ) {
		$redirect_url = trim( (string) $redirect_url );
		if ( '' === $redirect_url ) {
			return $events;
		}

		foreach ( $events as $event ) {
			if ( is_array( $event ) && isset( $event['type'] ) && 'redirect' === sanitize_key( (string) $event['type'] ) ) {
				return $events;
			}
		}

		$events[] = array(
			'type'    => 'redirect',
			'enabled' => true,
			'url'     => $redirect_url,
		);

		return $events;
	}

	/**
	 * @param mixed $values Role/user list from schema.
	 * @return string Comma-separated for the form builder UI.
	 */
	private static function access_list_to_csv( $values ) {
		if ( is_string( $values ) ) {
			return trim( $values );
		}
		if ( ! is_array( $values ) || empty( $values ) ) {
			return '';
		}
		return implode( ', ', array_map( 'strval', $values ) );
	}
}
