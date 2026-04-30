<?php
/**
 * Default SEO expert data (README-shaped) and merge-on-empty helpers.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load defaults array from data file.
 *
 * @return array<string,mixed>
 */
function zskeleton_seo_expert_load_default_data() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}
	$path = __DIR__ . '/data/defaults-ahmad-maki.php';
	if ( is_readable( $path ) ) {
		$data = include $path;
	} else {
		$data = array();
	}
	return is_array( $data ) ? $data : array();
}

/**
 * Get scalar meta with optional default merge.
 *
 * @param int    $post_id Post ID.
 * @param string $key     Key without prefix (e.g. expert_name).
 * @param string $default Fallback if empty.
 * @return string
 */
function zskeleton_seo_expert_get( $post_id, $key, $default = '' ) {
	$full = ZSkeleton_Seo_Expert_Meta::meta_key( $key );
	$val  = get_post_meta( $post_id, $full, true );
	if ( is_string( $val ) && '' !== $val ) {
		return $val;
	}
	if ( is_numeric( $val ) ) {
		return (string) $val;
	}

	$defaults = zskeleton_seo_expert_load_default_data();
	if ( isset( $defaults['scalars'][ $key ] ) ) {
		return (string) $defaults['scalars'][ $key ];
	}

	return $default;
}

/**
 * Apply defaults only for empty meta keys (merge-on-empty).
 *
 * @param int $post_id Post ID.
 * @return void
 */
function zskeleton_seo_expert_apply_defaults_if_empty( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id < 1 ) {
		return;
	}

	$data = zskeleton_seo_expert_load_default_data();
	if ( empty( $data['scalars'] ) || ! is_array( $data['scalars'] ) ) {
		return;
	}

	foreach ( $data['scalars'] as $key => $value ) {
		$mk = ZSkeleton_Seo_Expert_Meta::meta_key( $key );
		$ex = get_post_meta( $post_id, $mk, true );
		if ( '' !== $ex && false !== $ex && null !== $ex ) {
			continue;
		}
		update_post_meta( $post_id, $mk, $value );
	}

	// Repeaters: only if completely missing.
	$rep_keys = array( 'seo_stats', 'seo_ratings', 'seo_why_us', 'seo_methodology', 'seo_tools' );
	foreach ( $rep_keys as $gid ) {
		$mkey = ZSkeleton_Repeater_Registry::meta_key( $gid );
		$ex   = get_post_meta( $post_id, $mkey, true );
		if ( '' !== $ex && false !== $ex && null !== $ex ) {
			continue;
		}
		if ( empty( $data['repeaters'][ $gid ] ) || ! is_array( $data['repeaters'][ $gid ] ) ) {
			continue;
		}
		update_post_meta( $post_id, $mkey, wp_json_encode( $data['repeaters'][ $gid ], JSON_UNESCAPED_UNICODE ) );
	}
}
