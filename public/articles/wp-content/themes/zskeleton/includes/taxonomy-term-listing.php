<?php
/**
 * Reusable taxonomy term listings: term meta (icon + image), helpers, admin fields.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

/** Term meta: small icon (attachment ID, e.g. SVG/PNG). */
const ZSKELETON_TERM_ICON_ATTACHMENT_META = 'zskeleton_term_icon_attachment_id';

/** Term meta: card / thumbnail image (attachment ID). */
const ZSKELETON_TERM_IMAGE_ATTACHMENT_META = 'zskeleton_term_image_attachment_id';

/**
 * Taxonomies that get icon/image fields in admin and registered term meta.
 * Extend via {@see 'zskeleton_term_listing_meta_taxonomies'}.
 *
 * @return string[]
 */
function zskeleton_term_listing_meta_taxonomies() {
	$taxonomies = apply_filters( 'zskeleton_term_listing_meta_taxonomies', array( 'category' ) );
	if ( ! is_array( $taxonomies ) ) {
		return array( 'category' );
	}
	$out = array();
	foreach ( $taxonomies as $tax ) {
		$tax = sanitize_key( (string) $tax );
		if ( $tax && taxonomy_exists( $tax ) ) {
			$out[] = $tax;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * @param int $attachment_id Attachment post ID.
 * @return int Sanitized ID or 0.
 */
function zskeleton_term_listing_validate_media_attachment_id( $attachment_id ) {
	$attachment_id = absint( $attachment_id );
	if ( $attachment_id < 1 ) {
		return 0;
	}
	$post = get_post( $attachment_id );
	if ( ! $post || 'attachment' !== $post->post_type ) {
		return 0;
	}
	return $attachment_id;
}

/**
 * @param WP_Term $term Term object.
 * @return int Attachment ID or 0.
 */
function zskeleton_term_listing_get_icon_attachment_id( WP_Term $term ) {
	$id = (int) get_term_meta( $term->term_id, ZSKELETON_TERM_ICON_ATTACHMENT_META, true );
	return zskeleton_term_listing_validate_media_attachment_id( $id );
}

/**
 * @param WP_Term $term Term object.
 * @return int Attachment ID or 0.
 */
function zskeleton_term_listing_get_image_attachment_id( WP_Term $term ) {
	$id = (int) get_term_meta( $term->term_id, ZSKELETON_TERM_IMAGE_ATTACHMENT_META, true );
	return zskeleton_term_listing_validate_media_attachment_id( $id );
}

/**
 * Markup for the term icon (attachment image).
 *
 * @param WP_Term $term Term.
 * @param string    $size Image size slug.
 * @param array     $attr Extra attributes for wp_get_attachment_image().
 * @return string HTML or empty string.
 */
function zskeleton_term_listing_get_icon_html( WP_Term $term, $size = 'thumbnail', array $attr = array() ) {
	$aid = zskeleton_term_listing_get_icon_attachment_id( $term );
	if ( $aid < 1 ) {
		return '';
	}
	$attr = array_merge(
		array(
			'class'   => trim( 'zskeleton-term-list__icon-img ' . ( isset( $attr['class'] ) ? (string) $attr['class'] : '' ) ),
			'loading' => 'lazy',
			'decoding' => 'async',
			'alt'     => sprintf(
				/* translators: %s: taxonomy term name */
				__( 'Icon for %s', 'zskeleton' ),
				$term->name
			),
		),
		$attr
	);
	return wp_get_attachment_image( $aid, $size, false, $attr );
}

/**
 * Markup for the term listing image (card thumbnail).
 *
 * @param WP_Term $term Term.
 * @param string    $size Image size slug.
 * @param array     $attr Extra attributes for wp_get_attachment_image().
 * @return string HTML or empty string.
 */
function zskeleton_term_listing_get_image_html( WP_Term $term, $size = 'medium', array $attr = array() ) {
	$aid = zskeleton_term_listing_get_image_attachment_id( $term );
	if ( $aid < 1 ) {
		return '';
	}
	$attr = array_merge(
		array(
			'class'    => trim( 'zskeleton-term-list__thumb-img ' . ( isset( $attr['class'] ) ? (string) $attr['class'] : '' ) ),
			'loading'  => 'lazy',
			'decoding' => 'async',
			'alt'      => sprintf(
				/* translators: %s: taxonomy term name */
				__( 'Image for %s', 'zskeleton' ),
				$term->name
			),
		),
		$attr
	);
	return wp_get_attachment_image( $aid, $size, false, $attr );
}

/**
 * Register term meta for configured taxonomies.
 *
 * @return void
 */
function zskeleton_term_listing_register_meta() {
	foreach ( zskeleton_term_listing_meta_taxonomies() as $taxonomy ) {
		register_term_meta(
			$taxonomy,
			ZSKELETON_TERM_ICON_ATTACHMENT_META,
			array(
				'type'              => 'integer',
				'single'            => true,
				'sanitize_callback' => 'zskeleton_term_listing_validate_media_attachment_id',
				'show_in_rest'      => true,
				'auth_callback'     => static function ( $allowed, $meta_key, $term_id, $user_id = 0, $cap = '', $caps = array() ) {
					unset( $meta_key, $cap, $caps );
					return user_can( (int) $user_id, 'edit_term', (int) $term_id );
				},
			)
		);
		register_term_meta(
			$taxonomy,
			ZSKELETON_TERM_IMAGE_ATTACHMENT_META,
			array(
				'type'              => 'integer',
				'single'            => true,
				'sanitize_callback' => 'zskeleton_term_listing_validate_media_attachment_id',
				'show_in_rest'      => true,
				'auth_callback'     => static function ( $allowed, $meta_key, $term_id, $user_id = 0, $cap = '', $caps = array() ) {
					unset( $meta_key, $cap, $caps );
					return user_can( (int) $user_id, 'edit_term', (int) $term_id );
				},
			)
		);
	}
}
add_action( 'init', 'zskeleton_term_listing_register_meta', 11 );

/**
 * @param string $taxonomy Taxonomy slug.
 * @return bool
 */
function zskeleton_term_listing_user_can_edit_taxonomy( $taxonomy ) {
	$tax = get_taxonomy( $taxonomy );
	if ( ! $tax ) {
		return false;
	}
	$cap = isset( $tax->cap->edit_terms ) ? $tax->cap->edit_terms : 'manage_categories';
	return current_user_can( $cap );
}

/**
 * @param int    $term_id Term ID.
 * @param int    $tt_id Term taxonomy ID.
 * @param string $taxonomy Taxonomy slug.
 * @return void
 */
function zskeleton_term_listing_save_term_meta( $term_id, $tt_id, $taxonomy ) {
	if ( ! in_array( $taxonomy, zskeleton_term_listing_meta_taxonomies(), true ) ) {
		return;
	}
	if ( ! isset( $_POST['zskeleton_term_listing_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['zskeleton_term_listing_nonce'] ) ), 'zskeleton_term_listing_save' ) ) {
		return;
	}
	if ( ! zskeleton_term_listing_user_can_edit_taxonomy( $taxonomy ) ) {
		return;
	}
	$term_id = absint( $term_id );
	if ( $term_id < 1 ) {
		return;
	}
	$icon = isset( $_POST[ ZSKELETON_TERM_ICON_ATTACHMENT_META ] ) ? absint( wp_unslash( $_POST[ ZSKELETON_TERM_ICON_ATTACHMENT_META ] ) ) : 0;
	$img  = isset( $_POST[ ZSKELETON_TERM_IMAGE_ATTACHMENT_META ] ) ? absint( wp_unslash( $_POST[ ZSKELETON_TERM_IMAGE_ATTACHMENT_META ] ) ) : 0;

	$icon = zskeleton_term_listing_validate_media_attachment_id( $icon );
	$img  = zskeleton_term_listing_validate_media_attachment_id( $img );

	if ( $icon > 0 ) {
		update_term_meta( $term_id, ZSKELETON_TERM_ICON_ATTACHMENT_META, $icon );
	} else {
		delete_term_meta( $term_id, ZSKELETON_TERM_ICON_ATTACHMENT_META );
	}
	if ( $img > 0 ) {
		update_term_meta( $term_id, ZSKELETON_TERM_IMAGE_ATTACHMENT_META, $img );
	} else {
		delete_term_meta( $term_id, ZSKELETON_TERM_IMAGE_ATTACHMENT_META );
	}
}
add_action( 'created_term', 'zskeleton_term_listing_save_term_meta', 10, 3 );
add_action( 'edited_term', 'zskeleton_term_listing_save_term_meta', 10, 3 );

/**
 * Print nonce + hidden fields for the "add term" form.
 *
 * @param string $taxonomy Taxonomy slug.
 * @return void
 */
function zskeleton_term_listing_add_form_nonce( $taxonomy ) {
	if ( ! in_array( $taxonomy, zskeleton_term_listing_meta_taxonomies(), true ) ) {
		return;
	}
	wp_nonce_field( 'zskeleton_term_listing_save', 'zskeleton_term_listing_nonce' );
}

/**
 * Add fields on "Add new term" screen.
 *
 * @param string $taxonomy Taxonomy slug.
 * @return void
 */
function zskeleton_term_listing_add_form_fields( $taxonomy ) {
	if ( ! in_array( $taxonomy, zskeleton_term_listing_meta_taxonomies(), true ) ) {
		return;
	}
	?>
	<div class="form-field zskeleton-term-listing-media-wrap">
		<label for="zskeleton-new-term-icon"><?php esc_html_e( 'Listing icon (optional)', 'zskeleton' ); ?></label>
		<input type="hidden" name="<?php echo esc_attr( ZSKELETON_TERM_ICON_ATTACHMENT_META ); ?>" id="zskeleton-new-term-icon" value="" />
		<div class="zskeleton-term-media-preview" data-preview-for="zskeleton-new-term-icon" hidden></div>
		<p>
			<button type="button" class="button zskeleton-term-media-select" data-target="<?php echo esc_attr( ZSKELETON_TERM_ICON_ATTACHMENT_META ); ?>"><?php esc_html_e( 'Select icon', 'zskeleton' ); ?></button>
			<button type="button" class="button zskeleton-term-media-clear" data-target="<?php echo esc_attr( ZSKELETON_TERM_ICON_ATTACHMENT_META ); ?>"><?php esc_html_e( 'Remove', 'zskeleton' ); ?></button>
		</p>
		<p class="description"><?php esc_html_e( 'Small image (SVG or PNG) used in icon-style term listings.', 'zskeleton' ); ?></p>
	</div>
	<div class="form-field zskeleton-term-listing-media-wrap">
		<label for="zskeleton-new-term-image"><?php esc_html_e( 'Listing image (optional)', 'zskeleton' ); ?></label>
		<input type="hidden" name="<?php echo esc_attr( ZSKELETON_TERM_IMAGE_ATTACHMENT_META ); ?>" id="zskeleton-new-term-image" value="" />
		<div class="zskeleton-term-media-preview" data-preview-for="zskeleton-new-term-image" hidden></div>
		<p>
			<button type="button" class="button zskeleton-term-media-select" data-target="<?php echo esc_attr( ZSKELETON_TERM_IMAGE_ATTACHMENT_META ); ?>"><?php esc_html_e( 'Select image', 'zskeleton' ); ?></button>
			<button type="button" class="button zskeleton-term-media-clear" data-target="<?php echo esc_attr( ZSKELETON_TERM_IMAGE_ATTACHMENT_META ); ?>"><?php esc_html_e( 'Remove', 'zskeleton' ); ?></button>
		</p>
		<p class="description"><?php esc_html_e( 'Larger image for thumbnail-style term cards.', 'zskeleton' ); ?></p>
	</div>
	<?php
}

/**
 * Edit fields on term edit screen.
 *
 * @param WP_Term $term Term object.
 * @param string  $taxonomy Taxonomy slug.
 * @return void
 */
function zskeleton_term_listing_edit_form_fields( $term, $taxonomy ) {
	if ( ! in_array( $taxonomy, zskeleton_term_listing_meta_taxonomies(), true ) ) {
		return;
	}
	$icon_id = zskeleton_term_listing_get_icon_attachment_id( $term );
	$img_id  = zskeleton_term_listing_get_image_attachment_id( $term );
	$icon_url = $icon_id ? wp_get_attachment_image_url( $icon_id, 'thumbnail' ) : '';
	$img_url  = $img_id ? wp_get_attachment_image_url( $img_id, 'medium' ) : '';
	wp_nonce_field( 'zskeleton_term_listing_save', 'zskeleton_term_listing_nonce' );
	?>
	<tr class="form-field zskeleton-term-listing-media-wrap">
		<th scope="row"><label for="zskeleton-term-icon"><?php esc_html_e( 'Listing icon', 'zskeleton' ); ?></label></th>
		<td>
			<input type="hidden" name="<?php echo esc_attr( ZSKELETON_TERM_ICON_ATTACHMENT_META ); ?>" id="zskeleton-term-icon" value="<?php echo esc_attr( (string) $icon_id ); ?>" />
			<div class="zskeleton-term-media-preview" data-preview-for="zskeleton-term-icon" <?php echo $icon_url ? '' : 'hidden'; ?>>
				<?php
				if ( $icon_url ) {
					printf(
						'<img src="%s" alt="" style="max-width:80px;height:auto;display:block;margin:0 0 8px;" />',
						esc_url( $icon_url )
					);
				}
				?>
			</div>
			<p>
				<button type="button" class="button zskeleton-term-media-select" data-target="<?php echo esc_attr( ZSKELETON_TERM_ICON_ATTACHMENT_META ); ?>"><?php esc_html_e( 'Select icon', 'zskeleton' ); ?></button>
				<button type="button" class="button zskeleton-term-media-clear" data-target="<?php echo esc_attr( ZSKELETON_TERM_ICON_ATTACHMENT_META ); ?>"><?php esc_html_e( 'Remove', 'zskeleton' ); ?></button>
			</p>
			<p class="description"><?php esc_html_e( 'Optional. Used for icon-style grids and as a badge when a card image is set.', 'zskeleton' ); ?></p>
		</td>
	</tr>
	<tr class="form-field zskeleton-term-listing-media-wrap">
		<th scope="row"><label for="zskeleton-term-image"><?php esc_html_e( 'Listing image', 'zskeleton' ); ?></label></th>
		<td>
			<input type="hidden" name="<?php echo esc_attr( ZSKELETON_TERM_IMAGE_ATTACHMENT_META ); ?>" id="zskeleton-term-image" value="<?php echo esc_attr( (string) $img_id ); ?>" />
			<div class="zskeleton-term-media-preview" data-preview-for="zskeleton-term-image" <?php echo $img_url ? '' : 'hidden'; ?>>
				<?php
				if ( $img_url ) {
					printf(
						'<img src="%s" alt="" style="max-width:160px;height:auto;display:block;margin:0 0 8px;" />',
						esc_url( $img_url )
					);
				}
				?>
			</div>
			<p>
				<button type="button" class="button zskeleton-term-media-select" data-target="<?php echo esc_attr( ZSKELETON_TERM_IMAGE_ATTACHMENT_META ); ?>"><?php esc_html_e( 'Select image', 'zskeleton' ); ?></button>
				<button type="button" class="button zskeleton-term-media-clear" data-target="<?php echo esc_attr( ZSKELETON_TERM_IMAGE_ATTACHMENT_META ); ?>"><?php esc_html_e( 'Remove', 'zskeleton' ); ?></button>
			</p>
			<p class="description"><?php esc_html_e( 'Optional. Main visual for thumbnail-style term cards.', 'zskeleton' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Wire taxonomy admin hooks once.
 *
 * @return void
 */
function zskeleton_term_listing_register_admin_hooks() {
	foreach ( zskeleton_term_listing_meta_taxonomies() as $taxonomy ) {
		add_action( "{$taxonomy}_add_form_fields", 'zskeleton_term_listing_add_form_fields' );
		add_action( "{$taxonomy}_edit_form_fields", 'zskeleton_term_listing_edit_form_fields', 10, 2 );
		add_action( "{$taxonomy}_add_form", 'zskeleton_term_listing_add_form_nonce' );
	}
}
add_action( 'init', 'zskeleton_term_listing_register_admin_hooks', 20 );

/**
 * Enqueue media picker on term screens.
 *
 * @param string $hook_suffix Current admin page.
 * @return void
 */
function zskeleton_term_listing_admin_assets( $hook_suffix ) {
	if ( 'edit-tags.php' !== $hook_suffix && 'term.php' !== $hook_suffix ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : '';
	if ( ! $taxonomy || ! in_array( $taxonomy, zskeleton_term_listing_meta_taxonomies(), true ) ) {
		return;
	}
	if ( ! zskeleton_term_listing_user_can_edit_taxonomy( $taxonomy ) ) {
		return;
	}
	wp_enqueue_media();
	$use_minified = (bool) get_option( 'zskeleton_use_minified_assets', true );
	$file         = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/js/admin-term-listing.min.js' )
		? 'admin-term-listing.min.js'
		: 'admin-term-listing.js';
	$path = ZSkeleton_THEME_DIR . '/assets/js/' . $file;
	if ( is_readable( $path ) ) {
		wp_enqueue_script(
			'zskeleton-admin-term-listing',
			ZSkeleton_THEME_URL . '/assets/js/' . $file,
			array( 'jquery' ),
			(string) filemtime( $path ),
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'zskeleton_term_listing_admin_assets', 10 );
