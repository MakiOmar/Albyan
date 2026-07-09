<?php
/**
 * Curate which posts appear in the blog hub “Featured” strip (post meta + editor UI).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a raw meta value counts as checked.
 *
 * @param mixed $value Raw REST or POST value.
 * @return bool
 */
function zskeleton_blog_hub_featured_meta_is_enabled( $value ) {
	return true === $value
		|| 1 === $value
		|| '1' === (string) $value
		|| 'on' === strtolower( (string) $value )
		|| 'true' === strtolower( (string) $value );
}

/**
 * Register `_zskeleton_blog_hub_featured` for the block editor REST API.
 *
 * @return void
 */
function zskeleton_blog_hub_register_post_featured_meta() {
	register_post_meta(
		'post',
		ZSKELETON_BLOG_HUB_POST_FEATURED_META,
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'default'           => '',
			'auth_callback'     => static function (): bool {
				return current_user_can( 'edit_posts' );
			},
			'sanitize_callback' => static function ( $meta_value ): string {
				return zskeleton_blog_hub_featured_meta_is_enabled( $meta_value ) ? '1' : '';
			},
		)
	);
}
add_action( 'init', 'zskeleton_blog_hub_register_post_featured_meta', 25 );

/**
 * Sidebar meta box on the post edit screen.
 *
 * @return void
 */
function zskeleton_blog_hub_add_blog_featured_meta_box() {
	add_meta_box(
		'zskeleton_blog_hub_featured_mb',
		__( 'Blog listing', 'zskeleton' ),
		'zskeleton_blog_hub_render_blog_featured_meta_box',
		'post',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'zskeleton_blog_hub_add_blog_featured_meta_box', 18 );

/**
 * Outputs the Featured strip checkbox in the sidebar.
 *
 * @param WP_Post $post Current post object.
 * @return void
 */
function zskeleton_blog_hub_render_blog_featured_meta_box( $post ) {
	wp_nonce_field( 'zskeleton_blog_hub_featured_save', 'zskeleton_blog_hub_featured_nonce' );
	$on = get_post_meta( $post->ID, ZSKELETON_BLOG_HUB_POST_FEATURED_META, true );
	?>
	<div class="zs-meta-fields zs-meta-fields--compact zs-meta-fields--panel">
		<div class="zs-meta-field">
			<label class="zs-meta-field__label zs-meta-field__label--inline" for="zskeleton_blog_hub_featured_checkbox">
				<input
					type="checkbox"
					id="zskeleton_blog_hub_featured_checkbox"
					name="zskeleton_blog_hub_featured_field"
					value="1"
					<?php checked( $on, '1', true ); ?> />
				<span><?php esc_html_e( 'Show in blog “Featured” strip', 'zskeleton' ); ?></span>
			</label>
			<p class="zs-meta-field__hint"><?php esc_html_e( 'These posts appear first in the strip (Menu order in Quick Edit, then by date). Sticky posts appear after them; newer posts are not added automatically.', 'zskeleton' ); ?></p>
		</div>
	</div>
	<?php
}

/**
 * Persist meta box (classic submit + block editor bundles meta boxes).
 *
 * @param int $post_id Post ID.
 * @return void
 */
function zskeleton_blog_hub_save_blog_featured_meta_box( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['zskeleton_blog_hub_featured_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zskeleton_blog_hub_featured_nonce'] ) ), 'zskeleton_blog_hub_featured_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( 'post' !== get_post_type( $post_id ) ) {
		return;
	}

	$featured_val = isset( $_POST['zskeleton_blog_hub_featured_field'] )
		? sanitize_text_field( wp_unslash( (string) $_POST['zskeleton_blog_hub_featured_field'] ) )
		: '';

	if ( '1' === $featured_val ) {
		update_post_meta( $post_id, ZSKELETON_BLOG_HUB_POST_FEATURED_META, '1' );
	} else {
		delete_post_meta( $post_id, ZSKELETON_BLOG_HUB_POST_FEATURED_META );
	}
}
add_action( 'save_post_post', 'zskeleton_blog_hub_save_blog_featured_meta_box' );

/**
 * Insert a “Blog featured” marker column after the title.
 *
 * @param array<string, string> $columns Columns.
 * @return array<string, string>
 */
function zskeleton_blog_hub_manage_posts_columns( $columns ) {
	if ( ! is_array( $columns ) ) {
		return array();
	}
	$new_columns = array();
	$slug        = 'zskeleton_blog_hub_featured_col';
	foreach ( $columns as $key => $label ) {
		$new_columns[ $key ] = $label;
		if ( 'title' === $key ) {
			$new_columns[ $slug ] = __( 'Blog featured', 'zskeleton' );
		}
	}
	if ( ! isset( $new_columns[ $slug ] ) ) {
		$new_columns[ $slug ] = __( 'Blog featured', 'zskeleton' );
	}
	return $new_columns;
}
add_filter( 'manage_posts_columns', 'zskeleton_blog_hub_manage_posts_columns' );

/**
 * Display the star or em dash in the posts list table.
 *
 * @param string $column Column key.
 * @param int    $post_id Post ID.
 * @return void
 */
function zskeleton_blog_hub_manage_custom_column( $column, $post_id ) {
	if ( 'zskeleton_blog_hub_featured_col' !== $column ) {
		return;
	}
	if ( get_post_type( $post_id ) !== 'post' ) {
		return;
	}
	$on = get_post_meta( $post_id, ZSKELETON_BLOG_HUB_POST_FEATURED_META, true );
	if ( '1' === (string) $on ) {
		echo '<span class="dashicons dashicons-star-filled" style="color:#ffb900" aria-hidden="true"></span><span class="screen-reader-text">' . esc_html__( 'Featured on blog hub', 'zskeleton' ) . '</span>';
	} else {
		echo '&mdash;';
	}
}
add_action( 'manage_posts_custom_column', 'zskeleton_blog_hub_manage_custom_column', 10, 2 );
