<?php
/**
 * The sidebar containing the main widget area.
 *
 * Manage widgets under Appearance → Widgets → Primary Sidebar.
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>

<aside id="secondary" class="widget-area sidebar">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside><!-- #secondary -->
