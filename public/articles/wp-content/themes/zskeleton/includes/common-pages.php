<?php
/**
 * Create common theme pages (auth, memberships, blog) and sync theme options.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find a page by slug (any non-trash status).
 *
 * @param string $slug Post name.
 * @return WP_Post|null
 */
function zskeleton_find_page_by_slug( $slug ) {
	$slug = sanitize_title( (string) $slug );
	if ( '' === $slug ) {
		return null;
	}
	$posts = get_posts(
		array(
			'post_type'              => 'page',
			'name'                   => $slug,
			'post_status'            => array( 'publish', 'draft', 'pending', 'future', 'private' ),
			'posts_per_page'         => 1,
			'orderby'                => 'post_date',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	return ! empty( $posts[0] ) && $posts[0] instanceof WP_Post ? $posts[0] : null;
}

/**
 * Definitions for pages the theme can install (slug => title + page template file).
 *
 * @return array<string, array{title: string, template: string}>
 */
function zskeleton_get_common_page_definitions() {
	$defs = array(
		'login'            => array(
			'title'    => __( 'Log In', 'zskeleton' ),
			'template' => 'page-login.php',
		),
		'register'         => array(
			'title'    => __( 'Register', 'zskeleton' ),
			'template' => 'page-register.php',
		),
		'forgot-password'  => array(
			'title'    => __( 'Forgot Password', 'zskeleton' ),
			'template' => 'page-forgot-password.php',
		),
		'reset-password'   => array(
			'title'    => __( 'Reset Password', 'zskeleton' ),
			'template' => 'page-reset-password.php',
		),
		'memberships'      => array(
			'title'    => __( 'Memberships', 'zskeleton' ),
			'template' => 'page-memberships.php',
		),
		'blog'             => array(
			'title'    => __( 'Blog', 'zskeleton' ),
			'template' => 'page-blog.php',
		),
	);

	/**
	 * Filter common page definitions before install/update.
	 *
	 * @param array<string, array{title: string, template: string}> $defs Slug => data.
	 */
	return apply_filters( 'zskeleton_common_page_definitions', $defs );
}

/**
 * Map auth-related options from published pages by slug (same rules as legacy “Set default theme auth pages”).
 *
 * @return void
 */
function zskeleton_sync_auth_page_options_from_theme_slugs() {
	$pairs = array(
		'zskeleton_auth_login_page_id'          => array( 'login' ),
		'zskeleton_auth_register_page_id'       => array( 'register' ),
		'zskeleton_auth_lost_password_page_id'  => array( 'forgot-password', 'lost-password' ),
		'zskeleton_auth_reset_password_page_id' => array( 'reset-password' ),
	);

	foreach ( $pairs as $option => $slugs ) {
		$found = 0;
		foreach ( (array) $slugs as $slug ) {
			$page = zskeleton_find_page_by_slug( $slug );
			if ( $page && 'publish' === $page->post_status ) {
				update_option( $option, (int) $page->ID );
				$found = 1;
				break;
			}
		}
		if ( ! $found ) {
			update_option( $option, 0 );
		}
	}
}

/**
 * When Reading settings use a static front page but no Posts page, assign the Blog page.
 *
 * @return void
 */
function zskeleton_maybe_set_posts_page_for_blog_slug() {
	$show = (string) get_option( 'show_on_front', 'posts' );
	$pfp  = (int) get_option( 'page_for_posts', 0 );
	$pof  = (int) get_option( 'page_on_front', 0 );
	$blog = zskeleton_find_page_by_slug( 'blog' );
	if ( ! $blog || 'publish' !== $blog->post_status ) {
		return;
	}
	$blog_id = (int) $blog->ID;
	if ( 'page' === $show && $pfp === 0 && $pof > 0 && $blog_id !== $pof ) {
		update_option( 'page_for_posts', $blog_id );
	}
}

/**
 * Create or update a single common page from definition.
 *
 * @param string               $slug Page slug (post_name).
 * @param array{title: string, template: string} $def  Definition.
 * @return array{action: string, id: int}
 */
function zskeleton_ensure_common_page( $slug, array $def ) {
	$slug     = sanitize_title( (string) $slug );
	$title    = isset( $def['title'] ) ? (string) $def['title'] : $slug;
	$template = isset( $def['template'] ) ? basename( (string) $def['template'] ) : '';

	if ( '' === $slug || '' === $template ) {
		return array( 'action' => 'skip', 'id' => 0 );
	}

	$located = locate_template( array( $template ), false, false );
	if ( ! $located || ! is_readable( $located ) ) {
		return array( 'action' => 'skip', 'id' => 0 );
	}

	$existing = zskeleton_find_page_by_slug( $slug );
	if ( $existing instanceof WP_Post ) {
		$update = array(
			'ID'          => (int) $existing->ID,
			'post_status' => 'publish',
		);
		if ( '' === (string) $existing->post_title ) {
			$update['post_title'] = $title;
		}
		wp_update_post( wp_slash( $update ) );
		update_post_meta( (int) $existing->ID, '_wp_page_template', $template );
		return array( 'action' => 'updated', 'id' => (int) $existing->ID );
	}

	$author = get_current_user_id();
	if ( $author < 1 ) {
		$author = 1;
	}

	$page_id = wp_insert_post(
		wp_slash(
			array(
				'post_title'  => $title,
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_type'   => 'page',
				'post_author' => $author,
			)
		),
		true
	);

	if ( is_wp_error( $page_id ) || $page_id < 1 ) {
		return array( 'action' => 'error', 'id' => 0 );
	}

	update_post_meta( (int) $page_id, '_wp_page_template', $template );

	return array( 'action' => 'created', 'id' => (int) $page_id );
}

/**
 * Install or refresh common pages and sync Content tab options.
 *
 * @param array<string, mixed> $args {
 *     @type string $context Arbitrary context for filters/logging.
 * }
 * @return array<string, array<int, array<string, int|string>>|string> Result summary.
 */
function zskeleton_install_common_pages( $args = array() ) {
	if ( ! current_user_can( 'publish_pages' ) ) {
		return array( 'error' => 'capability' );
	}

	$args = is_array( $args ) ? $args : array();

	/**
	 * Skip automatic or manual common page installation.
	 *
	 * @param bool  $skip Whether to skip.
	 * @param array $args Arguments passed to zskeleton_install_common_pages().
	 */
	if ( apply_filters( 'zskeleton_skip_install_common_pages', false, $args ) ) {
		return array( 'skipped' => true );
	}

	$created = array();
	$updated = array();
	$skipped = array();
	$errors  = array();

	foreach ( zskeleton_get_common_page_definitions() as $slug => $def ) {
		if ( ! is_array( $def ) ) {
			continue;
		}
		$res = zskeleton_ensure_common_page( $slug, $def );
		if ( 'created' === $res['action'] && $res['id'] > 0 ) {
			$created[] = $slug;
		} elseif ( 'updated' === $res['action'] && $res['id'] > 0 ) {
			$updated[] = $slug;
		} elseif ( 'skip' === $res['action'] ) {
			$skipped[] = $slug;
		} else {
			$errors[] = $slug;
		}
	}

	zskeleton_sync_auth_page_options_from_theme_slugs();
	zskeleton_maybe_set_posts_page_for_blog_slug();

	$out = array(
		'created' => $created,
		'updated' => $updated,
		'skipped' => $skipped,
		'errors'  => $errors,
	);

	/**
	 * After common theme pages are installed (auth, blog, etc.). Plugins may sync membership options.
	 *
	 * @param array<string, mixed> $out  Install result.
	 * @param array<string, mixed> $args Context passed to zskeleton_install_common_pages().
	 */
	do_action( 'zskeleton_after_common_pages_install', $out, $args );

	/**
	 * Filter install result array.
	 *
	 * @param array<string, mixed> $out  Result.
	 * @param array<string, mixed> $args Original args.
	 */
	return apply_filters( 'zskeleton_install_common_pages_result', $out, $args );
}

/**
 * First activation of this template: create common pages once unless opted out.
 *
 * @param string         $old_name Old stylesheet (WordPress passes stylesheet string).
 * @param WP_Theme|null  $old_theme Old theme object (WP 4.7+).
 * @return void
 */
function zskeleton_maybe_auto_install_common_pages_on_switch( $old_name, $old_theme = null ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
	if ( 'zskeleton' !== get_template() ) {
		return;
	}

	if ( get_option( 'zskeleton_common_pages_auto_installed' ) ) {
		return;
	}

	/**
	 * Skip one-time auto install after theme switch.
	 *
	 * @param bool $skip Default false.
	 */
	if ( apply_filters( 'zskeleton_skip_auto_install_common_pages_on_theme_switch', false ) ) {
		return;
	}

	// Runs in admin when switching themes; require a user who can publish pages (skip WP-CLI / programmatic switches).
	if ( ! is_user_logged_in() || ! current_user_can( 'publish_pages' ) ) {
		return;
	}

	zskeleton_install_common_pages( array( 'context' => 'after_switch_theme' ) );

	update_option( 'zskeleton_common_pages_auto_installed', '1' );
}

add_action( 'after_switch_theme', 'zskeleton_maybe_auto_install_common_pages_on_switch', 30, 2 );
