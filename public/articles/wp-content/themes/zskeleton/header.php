<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php do_action('zskeleton_after_body_open'); ?>

<!-- Skip Link - Must be first focusable element -->
<div id="skip">
    <a href="#primary" class="skip-link"><?php _e('Skip to content', 'zskeleton'); ?></a>
</div>

<div id="page" class="site">

    <?php 
    // News banner
    $news_banner = get_theme_mod('zskeleton_news_banner', '');
    if (!empty($news_banner)) : 
    ?>
        <div class="news-banner">
            <div class="container">
                <?php echo wp_kses_post($news_banner); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
    $zskeleton_header_layout = function_exists( 'zskeleton_get_header_layout' ) ? zskeleton_get_header_layout() : 'default';
    $zskeleton_header_class  = 'site-header';
    if ( 'split_top_search' === $zskeleton_header_layout ) {
        $zskeleton_header_class .= ' site-header--split-top-search';
    }
    ?>
    <header id="masthead" class="<?php echo esc_attr( $zskeleton_header_class ); ?>">
<?php if ( 'split_top_search' === $zskeleton_header_layout ) : ?>
        <?php get_template_part( 'template-parts/header', 'split-top-search' ); ?>
<?php else : ?>
        <!-- Header Top -->
        <div class="header-top">
            <div class="container">
                <div class="header-top-content">
                <div class="header-contact">
                    <span><?php bloginfo('name'); ?></span>
                    <span class="separator">•</span>
                    <span><?php bloginfo('description'); ?></span>
                </div>
                    <div class="header-links">
                        <?php if (!is_user_logged_in()) : ?>
                            <a href="<?php echo home_url('/login/'); ?>" class="login-link">
                                <?php _e('Member Login', 'zskeleton'); ?>
                            </a>
                        <?php else : ?>
                            <a href="<?php echo home_url('/profile/'); ?>" class="welcome-text">
                                <?php printf(__('Welcome, %s', 'zskeleton'), wp_get_current_user()->display_name); ?>
                            </a>
                            <a href="<?php echo wp_logout_url(); ?>" class="logout-link">
                                <?php _e('Logout', 'zskeleton'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Main -->
        <div class="header-main">
            <div class="container">
                <div class="header-flex">
                    <div class="site-branding">
                        <div class="site-logo">
                            <?php
                            // Get desktop logo
                            $desktop_logo = zskeleton_get_logo('desktop');
                            $mobile_logo = zskeleton_get_logo('mobile');
                            
                            if ($desktop_logo) : ?>
                                <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" aria-label="<?php echo esc_attr(sprintf(__('Go to %s homepage', 'zskeleton'), get_bloginfo('name'))); ?>">
                                    <?php if ($mobile_logo && $mobile_logo !== $desktop_logo) : ?>
                                        <?php if( wp_is_mobile() ) : ?>
                                            <img src="<?php echo esc_url($mobile_logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="custom-logo mobile-logo" width="200" height="50" />
                                        <?php else : ?>
                                            <img src="<?php echo esc_url($desktop_logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="custom-logo desktop-logo" width="280" height="70" />
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <!-- Single Logo for both devices -->
                                        <img src="<?php echo esc_url($desktop_logo); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="custom-logo" width="280" height="70" />
                                    <?php endif; ?>
                                </a>
                            <?php else : ?>
                                <div class="text-logo">
                                    <h1 class="site-title">
                                        <a href="<?php echo esc_url(home_url('/')); ?>" rel="home" aria-label="<?php echo esc_attr(sprintf(__('Go to %s homepage', 'zskeleton'), get_bloginfo('name'))); ?>">
                                            <?php bloginfo('name'); ?>
                                        </a>
                                    </h1>
                                    <?php
                                    $zskeleton_description = get_bloginfo('description', 'display');
                                    if ($zskeleton_description || is_customize_preview()) :
                                        ?>
                                        <p class="site-tagline"><?php echo esc_html($zskeleton_description ?: get_theme_mod('zskeleton_tagline', __('A flexible WordPress base theme for membership-driven websites.', 'zskeleton'))); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php
                    $mobile_menu_button_style = function_exists( 'zskeleton_get_mobile_menu_button_style' ) ? zskeleton_get_mobile_menu_button_style() : 'style1';
                    $mobile_menu_panel_style   = function_exists( 'zskeleton_get_mobile_menu_panel_style' ) ? zskeleton_get_mobile_menu_panel_style() : 'style1';
                    ?>

                    <!-- Desktop Navigation Menu -->
                    <nav id="site-navigation" class="main-navigation desktop-navigation">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'primary',
                            'menu_id'        => 'primary-menu-desktop',
                            'menu_class'     => 'nav-menu',
                            'fallback_cb'    => 'zskeleton_default_menu',
                        ));
                        ?>
                    </nav>
                    
                    <!-- Mobile Navigation Menu -->
                    <nav id="site-navigation-mobile" class="main-navigation mobile-navigation<?php echo 'style2' === $mobile_menu_panel_style ? ' mobile-navigation--style-2' : ''; ?>">
                        <!-- Close button for mobile -->
                        <button class="menu-close" type="button" aria-label="<?php esc_attr_e( 'Close Menu', 'zskeleton' ); ?>">
                            ×
                        </button>
                        <?php if ( 'style2' === $mobile_menu_panel_style ) : ?>
                            <div class="mobile-menu-panel">
                                <?php
                                wp_nav_menu(
                                    array(
                                        'theme_location' => 'primary',
                                        'menu_id'        => 'primary-menu',
                                        'menu_class'     => 'nav-menu',
                                        'fallback_cb'    => 'zskeleton_default_menu',
                                    )
                                );
                                ?>
                                <?php if ( ! is_user_logged_in() ) : ?>
                                    <script>
                                    (function() {
                                        const primaryMenu = document.getElementById('primary-menu');
                                        if (primaryMenu) {
                                            const loginItem = document.createElement('li');
                                            loginItem.className = 'menu-item-login';
                                            const loginLink = document.createElement('a');
                                            loginLink.href = <?php echo wp_json_encode( home_url( '/login/' ) ); ?>;
                                            loginLink.className = 'mobile-login-menu-item';
                                            loginLink.textContent = <?php echo wp_json_encode( __( 'Member Login', 'zskeleton' ) ); ?>;
                                            loginItem.appendChild(loginLink);
                                            primaryMenu.appendChild(loginItem);
                                        }
                                    })();
                                    </script>
                                <?php endif; ?>
                                <ul class="nav-menu mobile-resources-menu">
                                    <?php if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
                                        <li><a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>"><?php esc_html_e( 'Memberships', 'zskeleton' ); ?></a></li>
                                    <?php endif; ?>
                                    <li><a href="<?php echo esc_url( zskeleton_get_page_url( 'faqs' ) ); ?>"><?php esc_html_e( 'FAQs', 'zskeleton' ); ?></a></li>
                                    <li><a href="<?php echo esc_url( zskeleton_get_page_url( 'blog' ) ); ?>"><?php esc_html_e( 'Blog', 'zskeleton' ); ?></a></li>
                                    <li><a href="<?php echo esc_url( zskeleton_get_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact', 'zskeleton' ); ?></a></li>
                                </ul>
                            </div>
                        <?php else : ?>
                            <!-- Mobile Menu Tabs (Style 1) -->
                            <div class="mobile-menu-tabs">
                                <button type="button" class="mobile-tab-btn active" data-tab="main-menu" aria-label="<?php esc_attr_e( 'Main Menu', 'zskeleton' ); ?>">
                                    <?php esc_html_e( 'Main Menu', 'zskeleton' ); ?>
                                </button>
                                <button type="button" class="mobile-tab-btn" data-tab="members-area" aria-label="<?php esc_attr_e( 'Members Area', 'zskeleton' ); ?>">
                                    <?php esc_html_e( 'Members Area', 'zskeleton' ); ?>
                                </button>
                            </div>

                            <div class="mobile-tab-content active" id="mobile-tab-main-menu">
                                <?php
                                wp_nav_menu(
                                    array(
                                        'theme_location' => 'primary',
                                        'menu_id'        => 'primary-menu',
                                        'menu_class'     => 'nav-menu',
                                        'fallback_cb'    => 'zskeleton_default_menu',
                                    )
                                );
                                ?>
                                <?php if ( ! is_user_logged_in() ) : ?>
                                    <script>
                                    (function() {
                                        const primaryMenu = document.getElementById('primary-menu');
                                        if (primaryMenu) {
                                            const loginItem = document.createElement('li');
                                            loginItem.className = 'menu-item-login';
                                            const loginLink = document.createElement('a');
                                            loginLink.href = <?php echo wp_json_encode( home_url( '/login/' ) ); ?>;
                                            loginLink.className = 'mobile-login-menu-item';
                                            loginLink.textContent = <?php echo wp_json_encode( __( 'Member Login', 'zskeleton' ) ); ?>;
                                            loginItem.appendChild(loginLink);
                                            primaryMenu.appendChild(loginItem);
                                        }
                                    })();
                                    </script>
                                <?php endif; ?>
                            </div>

                            <div class="mobile-tab-content" id="mobile-tab-members-area">
                                <ul class="nav-menu mobile-resources-menu">
                                    <?php if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
                                        <li><a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>"><?php esc_html_e( 'Memberships', 'zskeleton' ); ?></a></li>
                                    <?php endif; ?>
                                    <li><a href="<?php echo esc_url( zskeleton_get_page_url( 'faqs' ) ); ?>"><?php esc_html_e( 'FAQs', 'zskeleton' ); ?></a></li>
                                    <li><a href="<?php echo esc_url( zskeleton_get_page_url( 'blog' ) ); ?>"><?php esc_html_e( 'Blog', 'zskeleton' ); ?></a></li>
                                    <li><a href="<?php echo esc_url( zskeleton_get_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact', 'zskeleton' ); ?></a></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </nav>

                    <div class="header-actions">
                        <button class="search-toggle" type="button" aria-expanded="false" aria-controls="header-search">
                            <span class="screen-reader-text"><?php _e('Search Toggle', 'zskeleton'); ?></span>
                            🔍
                        </button>
                        
                        <?php
                        if ( is_user_logged_in() ) :
                            $user_id = get_current_user_id();
                            if ( class_exists( 'ZSkeleton_User_Profile_Fields' ) && ZSkeleton_User_Profile_Fields::user_has_active_membership( $user_id ) ) :
                                $membership_type = ZSkeleton_User_Profile_Fields::get_user_membership_type( $user_id );
                                ?>
                                <span class="member-status">
                                    <span class="member-badge"><?php echo esc_html(sprintf(/* translators: %s: membership tier name (e.g. Gold) */ __('Member: %s', 'zskeleton'), ucfirst($membership_type))); ?></span>
                                </span>
                            <?php elseif ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
                                <a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="btn btn-primary">
                                    <?php _e( 'Join Membership', 'zskeleton' ); ?>
                                </a>
                            <?php endif; ?>
                        <?php elseif ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
                            <a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="btn btn-primary">
                                <?php _e( 'Join Membership', 'zskeleton' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Mobile Menu Toggle (after header-actions so flex order places logo + burger at outer edges on small screens) -->
                    <button class="menu-toggle <?php echo 'style2' === $mobile_menu_button_style ? 'menu-toggle--style-2' : 'menu-toggle--style-1'; ?>" aria-controls="site-navigation-mobile" aria-expanded="false">
                        <span class="screen-reader-text"><?php _e('Primary Menu', 'zskeleton'); ?></span>
                        <span class="menu-icon"></span>
                    </button>
                </div>

                <?php zskeleton_header_search(); ?>
            </div>
        </div>
<?php endif; ?>

    </header>

    <?php
    // Show bar only when Secondary Sub-Navigation menu location has a menu with items.
    $zskeleton_show_secondary_subnav = function_exists('zskeleton_secondary_subnav_has_items') && zskeleton_secondary_subnav_has_items();
    ?>
    <?php if ($zskeleton_show_secondary_subnav) : ?>
    <!-- Secondary Sub-Navigation (theme location: Secondary Sub-Navigation) -->
    <div class="subnav">
        <div class="container">
            <div class="subnav-inner" role="navigation" aria-label="<?php esc_attr_e('Secondary navigation', 'zskeleton'); ?>">
                <button class="subnav-arrow subnav-prev" type="button" aria-label="<?php esc_attr_e('Previous', 'zskeleton'); ?>">
                    &#8249;
                </button>
                <div class="subnav-viewport">
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'secondary_subnav',
                            'container' => false,
                            'menu_class' => 'subnav-list',
                            'fallback_cb' => false,
                            'depth' => 1,
                        )
                    );
                    ?>
                </div>
                <button class="subnav-arrow subnav-next" type="button" aria-label="<?php esc_attr_e('Next', 'zskeleton'); ?>">
                    &#8250;
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
    <?php if ($zskeleton_show_secondary_subnav) : ?>
    (function() {
        const viewport = document.querySelector('.subnav-viewport');
        const list = document.querySelector('.subnav-list');
        const prev = document.querySelector('.subnav-prev');
        const next = document.querySelector('.subnav-next');
        if (!viewport || !list || !prev || !next) return;

        function itemWidth() {
            // On mobile we show 2 items; use first item's width, fallback to half viewport
            const first = list.querySelector('.subnav-item');
            if (!first) {
                console.log('itemWidth: No first item found, using half viewport');
                return viewport.clientWidth / 2;
            }
            
            const itemRect = first.getBoundingClientRect();
            const itemWidth = itemRect.width;
            
            console.log('itemWidth: First item width:', itemWidth);
            console.log('itemWidth: Viewport width:', viewport.clientWidth);
            
            // On mobile (viewport < 769px), account for gap between items
            if (viewport.clientWidth < 769) {
                // Gap is 12px on mobile, but we need the computed gap
                const computedStyle = window.getComputedStyle(list);
                const gap = parseFloat(computedStyle.gap) || 12;
                const totalWidth = itemWidth + gap;
                console.log('itemWidth: Mobile detected, gap:', gap, 'total width (item + gap):', totalWidth);
                return totalWidth;
            }
            
            console.log('itemWidth: Desktop, returning item width:', itemWidth);
            return itemWidth;
        }

        function scrollByItems(dir) {
            const currentScroll = viewport.scrollLeft;
            const itemWidthValue = itemWidth();
            const maxScroll = Math.max(0, viewport.scrollWidth - viewport.clientWidth);
            
            console.log('=== scrollByItems Debug ===');
            console.log('Direction:', dir < 0 ? 'PREV (back)' : 'NEXT (forward)');
            console.log('Current scrollLeft:', currentScroll);
            console.log('Item width (with gap):', itemWidthValue);
            console.log('Max scroll:', maxScroll);
            console.log('Viewport width:', viewport.clientWidth);
            console.log('Scroll width:', viewport.scrollWidth);
            console.log('Scrollable distance:', maxScroll);
            
            // Get all items and their positions
            const items = Array.from(list.querySelectorAll('.subnav-item'));
            console.log('Total items found:', items.length);
            
            // Check if we're actually at the end when scrolling forward
            if (dir > 0 && currentScroll >= maxScroll - 10) {
                console.log('Within 10px of end, scrolling to max:', maxScroll);
                viewport.scrollTo({ left: maxScroll, behavior: 'smooth' });
                return;
            }
            
            let targetScroll;
            
            if (dir < 0) {
                // Scrolling back - find which item is currently at or near the left edge
                let currentItemIndex = -1;
                let minDistance = Infinity;
                
                // Log all item positions for debugging
                console.log('PREV: Checking all item positions:');
                const viewportRect = viewport.getBoundingClientRect();
                
                for (let i = 0; i < items.length; i++) {
                    const item = items[i];
                    const itemRect = item.getBoundingClientRect();
                    const itemOffsetLeft = item.offsetLeft;
                    const itemText = item.textContent.trim().substring(0, 40);
                    
                    // Check if item is visible or partially visible in viewport
                    const isVisible = itemRect.right > viewportRect.left && itemRect.left < viewportRect.right;
                    const isAtLeftEdge = itemRect.left <= viewportRect.left + 5 && itemRect.right > viewportRect.left;
                    const itemRelativeToViewport = itemRect.left - viewportRect.left;
                    
                    console.log('PREV: Item', i, 'text:', itemText);
                    console.log('  - offsetLeft:', itemOffsetLeft);
                    console.log('  - itemRect.left:', itemRect.left);
                    console.log('  - viewportRect.left:', viewportRect.left);
                    console.log('  - relative to viewport:', itemRelativeToViewport);
                    console.log('  - isVisible:', isVisible);
                    console.log('  - isAtLeftEdge:', isAtLeftEdge);
                    
                    // Find item that's at or closest to the left edge
                    if (itemOffsetLeft <= currentScroll + 10) {
                        const distance = Math.abs(itemOffsetLeft - currentScroll);
                        if (distance < minDistance) {
                            minDistance = distance;
                            currentItemIndex = i;
                        }
                    }
                    
                    // Also check if item is actually at the visual left edge
                    if (isAtLeftEdge) {
                        console.log('PREV: Item', i, 'is at the visual left edge');
                        if (currentItemIndex === -1 || i < currentItemIndex) {
                            currentItemIndex = i;
                        }
                    }
                }
                
                console.log('PREV: Current item at left edge (or closest): index', currentItemIndex);
                
                if (currentItemIndex >= 0) {
                    // We found an item, now find the previous item to scroll to
                    if (currentItemIndex > 0) {
                        // Scroll to show the previous item at the left edge
                        const prevItem = items[currentItemIndex - 1];
                        const prevItemRect = prevItem.getBoundingClientRect();
                        const currentItemRect = items[currentItemIndex].getBoundingClientRect();
                        
                        // Calculate the scroll position needed to bring prevItem to the left edge
                        // If prevItem is at position X relative to list, and we want it at the left edge (position 0 in viewport),
                        // we need to scroll by: prevItem's offsetLeft - viewport's padding/offset
                        // But since offsetLeft can be negative, we need to calculate relative to current scroll
                        const prevItemOffsetLeft = prevItem.offsetLeft;
                        const currentItemOffsetLeft = items[currentItemIndex].offsetLeft;
                        
                        // The difference between current and previous item positions
                        const itemDistance = currentItemOffsetLeft - prevItemOffsetLeft;
                        
                        // Scroll by moving the distance needed to show previous item
                        // If current scroll shows item at position X, and prevItem is at position X-distance,
                        // we need to scroll: currentScroll - distance (or scroll to show prevItem at left edge)
                        targetScroll = Math.max(0, currentScroll + itemDistance);
                        
                        // Alternative: scroll directly to show prevItem at left edge
                        // If prevItem.offsetLeft is negative, it means it's to the left, so we need to scroll right
                        // Actually, to bring a left-positioned item to viewport left edge:
                        // scrollLeft = -item.offsetLeft (if item is at -100px, scroll 100px to bring it to 0)
                        // But we need to account for list padding/positioning
                        const listRect = list.getBoundingClientRect();
                        const viewportRect = viewport.getBoundingClientRect();
                        const listOffsetInViewport = listRect.left - viewportRect.left;
                        
                        // Calculate how much to scroll to bring prevItem to left edge of viewport
                        // If prevItem has offsetLeft = -293, we need to scroll 293px to bring it to position 0
                        // The scroll position needed = absolute value of negative offsetLeft (plus any padding)
                        const listPaddingLeft = parseFloat(window.getComputedStyle(list).paddingLeft) || 0;
                        const listPaddingRight = parseFloat(window.getComputedStyle(list).paddingRight) || 0;
                        
                        // When offsetLeft is negative, the item is to the left of the visible area
                        // To bring it to the left edge: scrollLeft = -offsetLeft + padding
                        // If offsetLeft = -293, scrollLeft = -(-293) = 293
                        let calculatedScroll = -prevItemOffsetLeft + listPaddingLeft;
                        
                        // Alternative: use the distance from current item
                        // Current item at -122, prev item at -293, difference = 171
                        // So scroll: currentScroll (0) + 171 = 171, which brings item 2 closer but not to edge
                        // To bring item 2 to left edge from current position: scroll by 293 (from 0)
                        const scrollDistance = currentItemOffsetLeft - prevItemOffsetLeft; // -122 - (-293) = 171
                        
                        console.log('PREV: Scrolling to previous item at index', currentItemIndex - 1);
                        console.log('  - Item text:', prevItem.textContent.trim().substring(0, 30) + '...');
                        console.log('  - prevItemOffsetLeft:', prevItemOffsetLeft);
                        console.log('  - currentItemOffsetLeft:', currentItemOffsetLeft);
                        console.log('  - itemDistance/scrollDistance:', scrollDistance);
                        console.log('  - currentScroll:', currentScroll);
                        console.log('  - listPaddingLeft:', listPaddingLeft);
                        console.log('  - calculatedScroll (absolute position):', calculatedScroll);
                        console.log('  - scrollFromCurrent (relative):', currentScroll + scrollDistance);
                        
                        // Use absolute position method - scroll to bring prevItem to left edge
                        targetScroll = Math.max(0, calculatedScroll);
                        
                        console.log('  - Final targetScroll:', targetScroll);
                        
                    } else {
                        // We're at the first item, scroll to position 0
                        const firstItem = items[0];
                        const firstItemOffsetLeft = firstItem.offsetLeft;
                        const listPaddingLeft = parseFloat(window.getComputedStyle(list).paddingLeft) || 0;
                        
                        // Calculate scroll to show first item at left edge
                        if (firstItemOffsetLeft < 0) {
                            // Item is to the left, scroll to bring it into view
                            targetScroll = Math.max(0, -firstItemOffsetLeft + listPaddingLeft);
                            console.log('PREV: First item is at negative position, scrolling to show it:', targetScroll);
                        } else {
                            targetScroll = 0;
                            console.log('PREV: Already at first item and position 0');
                        }
                    }
                } else {
                    // Fallback: use calculated method
                    targetScroll = Math.max(0, currentScroll - itemWidthValue);
                    console.log('PREV: No item found at edge, using calculated scroll:', targetScroll);
                    
                    // If very close to start, go to 0
                    if (targetScroll < itemWidthValue / 2) {
                        targetScroll = 0;
                        console.log('PREV: Close to start, snapping to 0');
                    }
                }
            } else {
                // Scrolling forward - find next item position
                let foundTarget = false;
                
                for (let i = 0; i < items.length; i++) {
                    const item = items[i];
                    const itemScrollLeft = item.offsetLeft - list.offsetLeft;
                    
                    // Find first item that's not fully visible on the left
                    if (itemScrollLeft > currentScroll) {
                        console.log('NEXT: Found target item at index', i, 'scroll position:', itemScrollLeft);
                        targetScroll = Math.min(maxScroll, itemScrollLeft);
                        foundTarget = true;
                        break;
                    }
                }
                
                if (!foundTarget) {
                    // Fallback to calculated method
                    targetScroll = Math.min(maxScroll, currentScroll + itemWidthValue);
                    console.log('NEXT: No target item found, using calculated scroll:', targetScroll);
                    
                    // If resulting scroll would be close to end, go directly to max
                    if ((maxScroll - targetScroll) < itemWidthValue / 2) {
                        console.log('NEXT: Target is within half item width of end, snapping to max:', maxScroll);
                        targetScroll = maxScroll;
                    }
                }
            }
            
            console.log('Final target scroll:', targetScroll);
            console.log('Scroll distance:', Math.abs(targetScroll - currentScroll));
            console.log('=== End Debug ===');
            
            viewport.scrollTo({ left: targetScroll, behavior: 'smooth' });
        }

        prev.addEventListener('click', function() { 
            console.log('=== PREV Button Clicked ===');
            console.log('Current scrollLeft:', viewport.scrollLeft);
            
            // Always call scrollByItems to properly calculate based on item positions
            // It will handle the logic for determining the correct scroll position
            scrollByItems(-1);
        });
        
        next.addEventListener('click', function() { 
            console.log('=== NEXT Button Clicked ===');
            console.log('Current scrollLeft:', viewport.scrollLeft);
            scrollByItems(1); 
        });

        // Touch support
        let startX = 0;
        let isDown = false;
        viewport.addEventListener('touchstart', function(e) {
            if (!e.touches || !e.touches[0]) return;
            startX = e.touches[0].clientX;
            isDown = true;
        }, { passive: true });
        viewport.addEventListener('touchmove', function(e) {
            // native scrolling handles this; no-op
        }, { passive: true });
        viewport.addEventListener('touchend', function(e) {
            if (!isDown) return;
            isDown = false;
            const endX = (e.changedTouches && e.changedTouches[0]) ? e.changedTouches[0].clientX : startX;
            const dx = endX - startX;
            const threshold = 30; // minimal swipe distance
            if (dx > threshold) {
                scrollByItems(-1);
            } else if (dx < -threshold) {
                scrollByItems(1);
            }
        });

        // Keyboard accessibility
        viewport.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') { scrollByItems(-1); }
            if (e.key === 'ArrowRight') { scrollByItems(1); }
        });
    })();
    <?php endif; ?>

    // Mobile menu tabs functionality
    (function() {
        const tabButtons = document.querySelectorAll('.mobile-tab-btn');
        const tabContents = document.querySelectorAll('.mobile-tab-content');
        
        if (!tabButtons.length || !tabContents.length) {
            return;
        }
        
        // Function to ensure only one tab is active
        function ensureSingleActive() {
            const activeButtons = Array.from(tabButtons).filter(btn => btn.classList.contains('active'));
            const activeContents = Array.from(tabContents).filter(content => content.classList.contains('active'));
            
            // If more than one button is active, keep only the first one
            if (activeButtons.length > 1) {
                activeButtons.slice(1).forEach(btn => btn.classList.remove('active'));
            }
            
            // If more than one content is active, keep only the first one
            if (activeContents.length > 1) {
                activeContents.slice(1).forEach(content => content.classList.remove('active'));
            }
            
            // Ensure button and content match
            const firstActiveButton = activeButtons[0];
            if (firstActiveButton) {
                const targetTab = firstActiveButton.getAttribute('data-tab');
                const targetContent = document.getElementById('mobile-tab-' + targetTab);
                if (targetContent) {
                    // Remove active from all contents
                    tabContents.forEach(content => content.classList.remove('active'));
                    // Add active to matching content
                    targetContent.classList.add('active');
                }
            }
        }
        
        // Initialize on page load
        ensureSingleActive();
        
        // Ensure single active when mobile menu opens
        const mobileNav = document.querySelector('.mobile-navigation');
        if (mobileNav) {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        if (mobileNav.classList.contains('active')) {
                            ensureSingleActive();
                        }
                    }
                });
            });
            observer.observe(mobileNav, { attributes: true });
        }
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');
                
                // Remove active class from all buttons and contents
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked button and corresponding content
                this.classList.add('active');
                const targetContent = document.getElementById('mobile-tab-' + targetTab);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    })();

    // Subnav dropdown toggle for resources menu
    (function() {
        const dropdown = document.querySelector('.subnav-dropdown');
        if (!dropdown) {
            return;
        }

        const trigger = dropdown.querySelector('.subnav-trigger');
        const submenu = dropdown.querySelector('.subnav-submenu');

        if (!trigger || !submenu) {
            return;
        }

        const openDropdown = () => {
            submenu.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        };

        const closeDropdown = () => {
            submenu.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        };

        trigger.setAttribute('aria-expanded', 'false');

        // Toggle on click (for touch devices)
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            if (submenu.classList.contains('is-open')) {
                closeDropdown();
            } else {
                openDropdown();
            }
        });

        // Open on keyboard focus
        trigger.addEventListener('focus', openDropdown);
        submenu.addEventListener('focusout', function(e) {
            if (!dropdown.contains(e.relatedTarget)) {
                closeDropdown();
            }
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                closeDropdown();
            }
        });

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        // Update aria-expanded for hover interactions
        dropdown.addEventListener('mouseenter', openDropdown);
        dropdown.addEventListener('mouseleave', function() {
            if (!submenu.classList.contains('is-open')) {
                trigger.setAttribute('aria-expanded', 'false');
            } else {
                closeDropdown();
            }
        });
    })();
    </script>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay"></div>

    <div id="content" class="site-content">

<?php
/**
 * Default menu fallback
 */
function zskeleton_default_menu() {
    ?>
    <ul class="nav-menu">
        <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'zskeleton'); ?></a></li>
        <li><a href="<?php echo esc_url(zskeleton_get_page_url('about')); ?>"><?php _e('About', 'zskeleton'); ?></a></li>
        <?php if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
        <li><a href="<?php echo esc_url(zskeleton_get_page_url('memberships')); ?>"><?php _e('Memberships', 'zskeleton'); ?></a></li>
        <?php endif; ?>
        <li><a href="<?php echo esc_url(zskeleton_get_page_url('blog')); ?>"><?php _e('Blog', 'zskeleton'); ?></a></li>
        <li><a href="<?php echo esc_url(zskeleton_get_page_url('faqs')); ?>"><?php _e('FAQs', 'zskeleton'); ?></a></li>
        <li><a href="<?php echo esc_url(zskeleton_get_page_url('contact')); ?>"><?php _e('Contact', 'zskeleton'); ?></a></li>
    </ul>
    <?php
}
?>
