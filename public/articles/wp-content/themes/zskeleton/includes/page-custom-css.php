<?php
/**
 * Per-page custom CSS (inline in wp_head on the front end).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/** Post meta key for page-specific CSS. */
const ZSKELETON_PAGE_CUSTOM_CSS_META = '_zskeleton_page_custom_css';

/**
 * Post types that may use the custom CSS metabox.
 *
 * @return string[]
 */
function zskeleton_page_custom_css_post_types(): array {
	/**
	 * Filters post types that show the page custom CSS field.
	 *
	 * @param string[] $types Post type names.
	 */
	return (array) apply_filters( 'zskeleton_page_custom_css_post_types', array( 'page' ) );
}

/**
 * @param mixed $css Raw CSS.
 * @return string
 */
function zskeleton_sanitize_page_custom_css( $css ): string {
	$css = is_string( $css ) ? $css : '';
	$css = wp_unslash( $css );
	$css = wp_check_invalid_utf8( $css );
	$css = wp_strip_all_tags( $css );
	$css = str_replace( array( '</style', '<script' ), '', $css );

	return trim( $css );
}

/**
 * @param int $post_id Post ID.
 * @return string
 */
function zskeleton_get_page_custom_css( $post_id = 0 ): string {
	$post_id = $post_id ? (int) $post_id : (int) get_queried_object_id();
	if ( $post_id < 1 ) {
		return '';
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	if ( ! in_array( $post->post_type, zskeleton_page_custom_css_post_types(), true ) ) {
		return '';
	}

	$css = get_post_meta( $post_id, ZSKELETON_PAGE_CUSTOM_CSS_META, true );
	return is_string( $css ) ? trim( $css ) : '';
}

/**
 * Register post meta for the block editor REST API.
 */
function zskeleton_register_page_custom_css_meta(): void {
	foreach ( zskeleton_page_custom_css_post_types() as $post_type ) {
		register_post_meta(
			$post_type,
			ZSKELETON_PAGE_CUSTOM_CSS_META,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
					unset( $allowed, $meta_key );
					return current_user_can( 'edit_post', (int) $post_id );
				},
				'sanitize_callback' => 'zskeleton_sanitize_page_custom_css',
			)
		);
	}
}
add_action( 'init', 'zskeleton_register_page_custom_css_meta', 25 );

/**
 * Admin meta box + save (same pattern as SEO Expert / repeater).
 */
class ZSkeleton_Page_Custom_Css_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post_page', array( $this, 'save_post' ), 10, 2 );
	}

	/**
	 * Register the Page CSS meta box (same hook signature as {@see ZSkeleton_Repeater_Admin}).
	 *
	 * @param string  $post_type Post type.
	 * @param WP_Post $post      Post object.
	 */
	public function add_meta_boxes( $post_type, $post ) {
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		foreach ( zskeleton_page_custom_css_post_types() as $pt ) {
			if ( $post_type !== $pt ) {
				continue;
			}

			add_meta_box(
				'zskeleton-page-custom-css',
				__( 'Page CSS', 'zskeleton' ),
				array( $this, 'render_metabox' ),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	/**
	 * @param WP_Post $post Current page.
	 */
	public function render_metabox( $post ) {
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		wp_nonce_field( 'zskeleton_save_page_custom_css', 'zskeleton_page_custom_css_nonce' );

		$css = zskeleton_get_page_custom_css( $post->ID );
		?>
		<div class="zs-meta-fields">
			<div class="zs-meta-field">
				<label class="zs-meta-field__label" for="zskeleton_page_custom_css_field"><?php esc_html_e( 'Custom CSS', 'zskeleton' ); ?></label>
				<p class="zs-meta-field__hint description"><?php esc_html_e( 'CSS added here loads only on this page. Use it to tweak layout, colors, or spacing for this page’s content.', 'zskeleton' ); ?></p>
				<textarea
					id="zskeleton_page_custom_css_field"
					name="zskeleton_page_custom_css"
					class="large-text code"
					rows="12"
					spellcheck="false"
					style="font-family: Consolas, Monaco, monospace; width: 100%;"
				><?php echo esc_textarea( $css ); ?></textarea>
			</div>
		</div>
		<?php
	}

	/**
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_post( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		if ( ! isset( $_POST['zskeleton_page_custom_css_nonce'] ) ) {
			return;
		}

		$nonce = (string) wp_unslash( $_POST['zskeleton_page_custom_css_nonce'] );
		if ( ! wp_verify_nonce( $nonce, 'zskeleton_save_page_custom_css' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, zskeleton_page_custom_css_post_types(), true ) ) {
			return;
		}

		$raw = isset( $_POST['zskeleton_page_custom_css'] ) ? (string) wp_unslash( $_POST['zskeleton_page_custom_css'] ) : '';
		$css = zskeleton_sanitize_page_custom_css( $raw );

		if ( '' === $css ) {
			delete_post_meta( $post_id, ZSKELETON_PAGE_CUSTOM_CSS_META );
			return;
		}

		update_post_meta( $post_id, ZSKELETON_PAGE_CUSTOM_CSS_META, $css );
	}
}

/**
 * Bootstrap Page CSS admin UI.
 */
function zskeleton_page_custom_css_bootstrap() {
	new ZSkeleton_Page_Custom_Css_Admin();
}
add_action( 'after_setup_theme', 'zskeleton_page_custom_css_bootstrap', 20 );

/**
 * Print page-specific CSS in the document head (after theme styles).
 */
function zskeleton_output_page_custom_css(): void {
	if ( ! is_singular() ) {
		return;
	}

	$post_id = get_queried_object_id();
	$css     = zskeleton_get_page_custom_css( $post_id );
	if ( '' === $css ) {
		return;
	}

	printf(
		"<style id=\"zskeleton-page-custom-css-%s\">\n%s\n</style>\n",
		esc_attr( (string) $post_id ),
		wp_strip_all_tags( $css ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- tags stripped; CSS is author-controlled for editors.
	);
}
add_action( 'wp_head', 'zskeleton_output_page_custom_css', 100 );
