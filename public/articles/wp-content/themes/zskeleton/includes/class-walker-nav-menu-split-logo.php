<?php
/**
 * Split header: inject site logo as a top-level <li> inside the primary menu (no right-hand menu).
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Synthetic menu item db_id / ID (must not collide with real menu rows). */
const ZSKELETON_SPLIT_LOGO_MENU_ITEM_ID = -910001;

/**
 * Build the synthetic nav menu item object for the logo slot.
 *
 * @return stdClass
 */
function zskeleton_split_header_logo_nav_object() {
	$item                 = new stdClass();
	$item->ID             = ZSKELETON_SPLIT_LOGO_MENU_ITEM_ID;
	$item->db_id          = ZSKELETON_SPLIT_LOGO_MENU_ITEM_ID;
	$item->menu_item_parent = 0;
	$item->object_id      = 0;
	$item->object         = 'zskeleton_split_logo';
	$item->type           = 'zskeleton_split_logo';
	$item->type_label     = '';
	$item->title          = '';
	$item->url            = home_url( '/' );
	$item->target         = '';
	$item->attr_title      = '';
	$item->description    = '';
	$item->xfn            = '';
	$item->classes        = array(
		'menu-item',
		'menu-item-type-zskeleton-split-logo',
		'menu-item-logo-split',
	);
	$item->current                   = false;
	$item->current_item_ancestor     = false;
	$item->current_item_parent       = false;
	$item->menu_order                = 0;

	return $item;
}

/**
 * Markup for the split-header logo block inside the menu-item `<li>` (`.site-logo--split-nav`).
 *
 * @return string
 */
function zskeleton_split_header_get_split_nav_logo_markup() {
	$home_url = home_url( '/' );
	$label    = sprintf(
		/* translators: %s: site name */
		__( 'Go to %s homepage', 'zskeleton' ),
		get_bloginfo( 'name' )
	);

	$desktop_logo = function_exists( 'zskeleton_get_logo' ) ? zskeleton_get_logo( 'desktop' ) : '';
	$mobile_logo  = function_exists( 'zskeleton_get_logo' ) ? zskeleton_get_logo( 'mobile' ) : '';

	$inner = '';
	if ( $desktop_logo ) {
		$inner .= '<a href="' . esc_url( $home_url ) . '" rel="home" class="split-header-logo-link" aria-label="' . esc_attr( $label ) . '">';
		if ( $mobile_logo && $mobile_logo !== $desktop_logo ) {
			if ( wp_is_mobile() ) {
				$inner .= '<img src="' . esc_url( $mobile_logo ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="custom-logo mobile-logo" width="200" height="50" loading="eager" decoding="async" />';
			} else {
				$inner .= '<img src="' . esc_url( $desktop_logo ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="custom-logo desktop-logo" width="240" height="60" loading="eager" decoding="async" />';
			}
		} else {
			$inner .= '<img src="' . esc_url( $desktop_logo ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="custom-logo" width="240" height="60" loading="eager" decoding="async" />';
		}
		$inner .= '</a>';
	} else {
		$inner .= '<div class="text-logo text-logo--split-nav"><p class="site-title"><a href="' . esc_url( $home_url ) . '" rel="home">' . esc_html( get_bloginfo( 'name' ) ) . '</a></p></div>';
	}

	return '<div class="site-logo site-logo--split-nav">' . $inner . '</div>';
}

/**
 * Primary menu fallback when no menu is assigned: same default links as {@see zskeleton_default_menu()},
 * with the split-header logo `<li>` inserted at the same index as {@see zskeleton_split_header_nav_menu_objects()}.
 *
 * @param stdClass|null $args wp_nav_menu() args object.
 */
function zskeleton_split_header_primary_menu_fallback( $args = null ) {
	$args = is_object( $args ) ? $args : (object) array();

	$items = array();
	$items[] = '<li class="menu-item"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'zskeleton' ) . '</a></li>';
	$items[] = '<li class="menu-item"><a href="' . esc_url( zskeleton_get_page_url( 'about' ) ) . '">' . esc_html__( 'About', 'zskeleton' ) . '</a></li>';
	if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) {
		$items[] = '<li class="menu-item"><a href="' . esc_url( zskeleton_get_page_url( 'memberships' ) ) . '">' . esc_html__( 'Memberships', 'zskeleton' ) . '</a></li>';
	}
	$items[] = '<li class="menu-item"><a href="' . esc_url( zskeleton_get_page_url( 'blog' ) ) . '">' . esc_html__( 'Blog', 'zskeleton' ) . '</a></li>';
	$items[] = '<li class="menu-item"><a href="' . esc_url( zskeleton_get_page_url( 'faqs' ) ) . '">' . esc_html__( 'FAQs', 'zskeleton' ) . '</a></li>';
	$items[] = '<li class="menu-item"><a href="' . esc_url( zskeleton_get_page_url( 'contact' ) ) . '">' . esc_html__( 'Contact', 'zskeleton' ) . '</a></li>';

	$n = count( $items );
	$k = (int) ceil( $n / 2 );
	$logo_li = '<li id="zskeleton-menu-item-split-logo" class="menu-item menu-item-type-zskeleton-split-logo menu-item-logo-split">' . zskeleton_split_header_get_split_nav_logo_markup() . '</li>';
	array_splice( $items, $k, 0, array( $logo_li ) );

	$menu_id    = isset( $args->menu_id ) ? (string) $args->menu_id : 'primary-menu-desktop';
	$menu_class = isset( $args->menu_class ) ? $args->menu_class : 'nav-menu';
	if ( is_array( $menu_class ) ) {
		$menu_class = implode( ' ', $menu_class );
	}

	echo '<ul id="' . esc_attr( $menu_id ) . '" class="' . esc_attr( $menu_class ) . '">';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted composition of escaped fragments.
	echo implode( '', $items );
	echo '</ul>';
}

/**
 * Whether this request should inject the split-header logo into the primary desktop menu.
 *
 * @param stdClass $args wp_nav_menu args object.
 * @return bool
 */
function zskeleton_split_header_should_inject_logo_menu_item( $args ) {
	if ( ! is_object( $args ) ) {
		return false;
	}
	if ( ! empty( $args->zskeleton_split_header_inject_logo ) ) {
		return true;
	}
	// Fallback if another filter stripped our custom flag but this is still the split primary desktop menu.
	if ( ( isset( $args->menu_id ) && 'primary-menu-desktop' === $args->menu_id )
		&& ( isset( $args->theme_location ) && 'primary' === $args->theme_location )
		&& function_exists( 'zskeleton_get_header_layout' )
		&& 'split_top_search' === zskeleton_get_header_layout()
		&& ! has_nav_menu( 'header_nav_right' )
	) {
		return true;
	}
	return false;
}

/**
 * Late args merge so the custom walker + flag survive other `wp_nav_menu_args` filters.
 *
 * @param array<string, mixed> $args Menu args.
 * @return array<string, mixed>
 */
function zskeleton_split_header_wp_nav_menu_args( $args ) {
	if ( ! is_array( $args ) ) {
		return $args;
	}
	if ( ( $args['menu_id'] ?? '' ) !== 'primary-menu-desktop' || ( $args['theme_location'] ?? '' ) !== 'primary' ) {
		return $args;
	}
	if ( ! function_exists( 'zskeleton_get_header_layout' ) || 'split_top_search' !== zskeleton_get_header_layout() ) {
		return $args;
	}
	if ( has_nav_menu( 'header_nav_right' ) ) {
		return $args;
	}
	$args['zskeleton_split_header_inject_logo'] = true;
	if ( class_exists( 'ZSkeleton_Walker_Nav_Menu_Split_Logo' ) ) {
		$args['walker'] = new ZSkeleton_Walker_Nav_Menu_Split_Logo();
	}
	return $args;
}

add_filter( 'wp_nav_menu_args', 'zskeleton_split_header_wp_nav_menu_args', 1000, 1 );

/**
 * Insert the logo pseudo-item between top-level menu blocks (before root index k = ceil(n/2)).
 *
 * @param array<int, stdClass> $sorted_items Menu items keyed by menu_order (see wp_nav_menu).
 * @param stdClass             $args         wp_nav_menu() arguments object.
 * @return array<int, stdClass>
 */
function zskeleton_split_header_nav_menu_objects( $sorted_items, $args ) {
	if ( ! zskeleton_split_header_should_inject_logo_menu_item( $args ) ) {
		return $sorted_items;
	}
	if ( empty( $sorted_items ) || ! is_array( $sorted_items ) ) {
		return $sorted_items;
	}

	ksort( $sorted_items, SORT_NUMERIC );
	$list = array_values( $sorted_items );

	$root_indices = array();
	foreach ( $list as $idx => $item ) {
		$parent = isset( $item->menu_item_parent ) ? (int) $item->menu_item_parent : 0;
		if ( 0 === $parent ) {
			$root_indices[] = $idx;
		}
	}

	$n = count( $root_indices );
	if ( $n < 1 ) {
		return $sorted_items;
	}

	// Odd counts: e.g. 5 roots → logo after the 3rd item (insert before root index ceil(n/2)).
	$k         = (int) ceil( $n / 2 );
	$insert_at = ( $k < $n ) ? $root_indices[ $k ] : count( $list );

	$logo = zskeleton_split_header_logo_nav_object();
	array_splice( $list, $insert_at, 0, array( $logo ) );

	return $list;
}

add_filter( 'wp_nav_menu_objects', 'zskeleton_split_header_nav_menu_objects', 10, 2 );

/**
 * Walker: render the synthetic logo item; otherwise default nav markup.
 */
class ZSkeleton_Walker_Nav_Menu_Split_Logo extends Walker_Nav_Menu {

	/**
	 * @param string   $output            Output.
	 * @param stdClass $data_object       Menu item.
	 * @param int      $depth             Depth.
	 * @param stdClass|null $args         Args.
	 * @param int      $current_object_id Current id.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		if ( isset( $data_object->type ) && 'zskeleton_split_logo' === $data_object->type && 0 === (int) $depth ) {
			// Avoid nav_menu_* filters stripping markup for this synthetic item (stable id/classes).
			$output .= '<li id="zskeleton-menu-item-split-logo" class="menu-item menu-item-type-zskeleton-split-logo menu-item-logo-split">';
			$output .= zskeleton_split_header_get_split_nav_logo_markup();
			return;
		}

		parent::start_el( $output, $data_object, $depth, $args, $current_object_id );
	}
}
