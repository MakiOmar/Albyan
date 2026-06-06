<?php
/**
 * Front page template
 *
 * WordPress loads `front-page.php` before any `page-*.php` file when the front page is visited.
 * Without this file, a static homepage that uses a custom Page Template can be resolved incorrectly
 * in some setups. We delegate to the assigned template (e.g. page-home-seo-ar.php), then fall back to page.php.
 *
 * @package ZSkeleton_Theme
 */

if (!defined('ABSPATH')) {
	exit;
}

$page_on_front = (int) get_option('page_on_front');

// "Your latest posts" on the front: use home.php → index.php (not page.php).
if (!$page_on_front) {
	$home = locate_template(array('home.php', 'index.php'));
	if ($home) {
		load_template($home);
		return;
	}
}

if ($page_on_front) {
	$template_slug = get_post_meta($page_on_front, '_wp_page_template', true);
	if ($template_slug && 'default' !== $template_slug && 0 === validate_file($template_slug)) {
		$located = locate_template($template_slug);
		if ($located) {
			load_template($located);
			return;
		}
	}
}

require get_template_directory() . '/page.php';
