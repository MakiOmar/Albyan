<?php
/**
 * Per-post redirect URL (internal or external) for any supported post type.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/** Post meta key for the redirect destination URL. */
const ZSKELETON_POST_REDIRECT_META = '_zskeleton_redirect_to';

/**
 * Post types that may use the redirect metabox (filterable).
 *
 * @return string[]
 */
function zskeleton_redirect_supported_post_types(): array {
	$types = get_post_types(
		array(
			'show_ui' => true,
		),
		'names'
	);

	$exclude = array(
		'attachment',
		'revision',
		'nav_menu_item',
		'custom_css',
		'customize_changeset',
		'oembed_cache',
		'user_request',
		'wp_block',
		'wp_template',
		'wp_template_part',
		'wp_navigation',
		'wp_global_styles',
	);

	$types = array_values( array_diff( $types, $exclude ) );

	/**
	 * Filters post types that show the redirect metabox and honor redirects on the front end.
	 *
	 * @param string[] $types Post type names.
	 */
	return (array) apply_filters( 'zskeleton_redirect_supported_post_types', $types );
}

/**
 * Sanitize a redirect URL (absolute http(s) or site-relative path).
 *
 * @param mixed $url Raw value.
 * @return string Empty string when invalid.
 */
function zskeleton_sanitize_redirect_url( $url ): string {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '';
	}

	if ( '/' === $url[0] ) {
		$absolute = home_url( $url );
		$parsed   = wp_parse_url( $absolute );
		if ( empty( $parsed['host'] ) ) {
			return '';
		}
		return esc_url_raw( $absolute );
	}

	if ( str_starts_with( $url, '//' ) ) {
		$url = 'https:' . $url;
	}

	$sanitized = esc_url_raw( $url );
	if ( '' === $sanitized ) {
		return '';
	}

	$scheme = wp_parse_url( $sanitized, PHP_URL_SCHEME );
	if ( ! in_array( strtolower( (string) $scheme ), array( 'http', 'https' ), true ) ) {
		return '';
	}

	return $sanitized;
}

/**
 * Stored redirect URL for a post, if any.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function zskeleton_get_post_redirect_url( int $post_id ): string {
	if ( $post_id <= 0 ) {
		return '';
	}

	$raw = get_post_meta( $post_id, ZSKELETON_POST_REDIRECT_META, true );
	if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
		return '';
	}

	return zskeleton_sanitize_redirect_url( $raw );
}

/**
 * Register redirect meta for the block editor REST API.
 *
 * @return void
 */
function zskeleton_register_post_redirect_meta(): void {
	foreach ( zskeleton_redirect_supported_post_types() as $post_type ) {
		register_post_meta(
			$post_type,
			ZSKELETON_POST_REDIRECT_META,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'default'           => '',
				'auth_callback'     => static function ( $allowed, $meta_key, $object_id ) {
					unset( $allowed, $meta_key );
					return current_user_can( 'edit_post', (int) $object_id );
				},
				'sanitize_callback' => 'zskeleton_sanitize_redirect_url',
			)
		);
	}
}
add_action( 'init', 'zskeleton_register_post_redirect_meta', 25 );

/**
 * Register redirect metabox on supported post types.
 *
 * @param string  $post_type Current post type.
 * @param WP_Post $post      Post being edited.
 * @return void
 */
function zskeleton_add_post_redirect_meta_box( string $post_type, $post = null ): void {
	if ( ! in_array( $post_type, zskeleton_redirect_supported_post_types(), true ) ) {
		return;
	}

	if ( 'page' === $post_type && $post instanceof WP_Post && function_exists( 'zskeleton_page_is_editor_only_legal_template' ) && zskeleton_page_is_editor_only_legal_template( $post->ID ) ) {
		return;
	}

	add_meta_box(
		'zskeleton_post_redirect_mb',
		__( 'Redirect', 'zskeleton' ),
		'zskeleton_render_post_redirect_meta_box',
		$post_type,
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'zskeleton_add_post_redirect_meta_box', 20, 2 );

/**
 * Metabox markup for redirect URL.
 *
 * @param WP_Post $post Current post.
 * @return void
 */
function zskeleton_render_post_redirect_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'zskeleton_post_redirect_save', 'zskeleton_post_redirect_nonce' );
	$url = zskeleton_get_post_redirect_url( (int) $post->ID );
	?>
	<div class="zs-meta-fields zs-meta-fields--compact zs-meta-fields--panel">
		<div class="zs-meta-field">
			<label class="zs-meta-field__label" for="zskeleton_redirect_to_field"><?php esc_html_e( 'Redirect to', 'zskeleton' ); ?></label>
			<input
				type="text"
				class="widefat"
				id="zskeleton_redirect_to_field"
				name="zskeleton_redirect_to_field"
				value="<?php echo esc_attr( $url ); ?>"
				placeholder="<?php echo esc_attr( home_url( '/example/' ) ); ?>"
				inputmode="url"
				autocomplete="url"
				spellcheck="false" />
			<p class="zs-meta-field__hint">
				<?php esc_html_e( 'Visitors opening this entry are sent to this URL (301). Use a full URL or a path starting with /. Leave empty for normal viewing.', 'zskeleton' ); ?>
			</p>
		</div>
	</div>
	<?php
}

/**
 * Save redirect metabox value.
 *
 * @param int $post_id Post ID.
 * @return void
 */
function zskeleton_save_post_redirect_meta_box( int $post_id ): void {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['zskeleton_post_redirect_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['zskeleton_post_redirect_nonce'] ) ), 'zskeleton_post_redirect_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$post_type = get_post_type( $post_id );
	if ( ! is_string( $post_type ) || ! in_array( $post_type, zskeleton_redirect_supported_post_types(), true ) ) {
		return;
	}

	if ( 'page' === $post_type && function_exists( 'zskeleton_page_is_editor_only_legal_template' ) && zskeleton_page_is_editor_only_legal_template( $post_id ) ) {
		delete_post_meta( $post_id, ZSKELETON_POST_REDIRECT_META );
		return;
	}

	$raw = isset( $_POST['zskeleton_redirect_to_field'] )
		? sanitize_text_field( wp_unslash( (string) $_POST['zskeleton_redirect_to_field'] ) )
		: '';

	$url = zskeleton_sanitize_redirect_url( $raw );
	if ( '' === $url ) {
		delete_post_meta( $post_id, ZSKELETON_POST_REDIRECT_META );
		return;
	}

	update_post_meta( $post_id, ZSKELETON_POST_REDIRECT_META, $url );
}
add_action( 'save_post', 'zskeleton_save_post_redirect_meta_box' );

/**
 * Redirect singular views when a destination URL is set.
 *
 * @return void
 */
function zskeleton_maybe_redirect_singular_post(): void {
	if ( is_admin() || wp_doing_ajax() || is_preview() || ! is_singular() ) {
		return;
	}

	$post_id = (int) get_queried_object_id();
	if ( $post_id <= 0 ) {
		return;
	}

	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	if ( ! in_array( $post->post_type, zskeleton_redirect_supported_post_types(), true ) ) {
		return;
	}

	if ( 'publish' !== $post->post_status && ! current_user_can( 'read_post', $post_id ) ) {
		return;
	}

	$redirect_url = zskeleton_get_post_redirect_url( $post_id );
	if ( '' === $redirect_url ) {
		return;
	}

	$permalink = get_permalink( $post_id );
	if ( is_string( $permalink ) && '' !== $permalink ) {
		if ( untrailingslashit( strtolower( $redirect_url ) ) === untrailingslashit( strtolower( $permalink ) ) ) {
			return;
		}
	}

	$status = 301;
	/**
	 * Filters the HTTP status code used for per-post redirects.
	 *
	 * @param int $status     Default 301.
	 * @param int $post_id    Post ID.
	 * @param string $redirect_url Destination URL.
	 */
	$status = (int) apply_filters( 'zskeleton_post_redirect_status_code', $status, $post_id, $redirect_url );
	if ( $status < 300 || $status > 399 ) {
		$status = 301;
	}

	// phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Editor-configured internal and external destinations.
	wp_redirect( $redirect_url, $status );
	exit;
}
add_action( 'template_redirect', 'zskeleton_maybe_redirect_singular_post', 1 );
