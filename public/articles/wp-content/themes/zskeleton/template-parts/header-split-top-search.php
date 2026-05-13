<?php
/**
 * Alternate header: top bar with inline search + socials + login; logo centered between left/right nav.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$facebook_url  = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'facebook' ) : '';
$twitter_url   = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'twitter' ) : '';
$linkedin_url  = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'linkedin' ) : '';
$youtube_url   = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'youtube' ) : '';
$instagram_url = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'instagram' ) : '';
$github_url    = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'github' ) : '';
$snapchat_url  = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'snapchat' ) : '';
$tiktok_url    = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'tiktok' ) : '';
$whatsapp_raw  = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( 'whatsapp' ) : '';
$whatsapp_url  = '';
if ( '' !== trim( (string) $whatsapp_raw ) ) {
	if ( preg_match( '#^https?://#i', $whatsapp_raw ) ) {
		$whatsapp_url = $whatsapp_raw;
	} else {
		$digits = preg_replace( '/\D+/', '', (string) $whatsapp_raw );
		$whatsapp_url = '' !== $digits ? 'https://wa.me/' . $digits : '';
	}
}
$has_social                 = ! empty( $facebook_url ) || ! empty( $twitter_url ) || ! empty( $linkedin_url ) || ! empty( $youtube_url ) || ! empty( $instagram_url ) || ! empty( $github_url ) || ! empty( $snapchat_url ) || ! empty( $tiktok_url ) || ! empty( $whatsapp_url );
$show_topbar_social_desktop = $has_social && ! wp_is_mobile();
$show_topbar_search_desktop = ! wp_is_mobile();
$show_topbar_sep_desktop    = ! wp_is_mobile();
$mobile_menu_button_style   = function_exists( 'zskeleton_get_mobile_menu_button_style' ) ? zskeleton_get_mobile_menu_button_style() : 'style1';
$mobile_menu_panel_style    = function_exists( 'zskeleton_get_mobile_menu_panel_style' ) ? zskeleton_get_mobile_menu_panel_style() : 'style1';
$has_right_nav = has_nav_menu( 'header_nav_right' );
?>

<!-- Top bar: search + socials | login status (reuse .header-top + .header-top-content for same gradient/typography as default header) -->
<div class="header-top header-topbar-split">
	<div class="container">
		<div class="header-top-content header-topbar-split__inner">
			<div class="header-topbar-split__left">
				<?php zskeleton_render_split_header_wpml_switcher(); ?>
				<?php if ( $show_topbar_search_desktop ) : ?>
					<div class="header-inline-search" role="search">
						<form class="header-inline-search__form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
							<span class="header-inline-search__icon" aria-hidden="true">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" focusable="false">
									<circle cx="11" cy="11" r="7"></circle>
									<line x1="16.65" y1="16.65" x2="21" y2="21"></line>
								</svg>
							</span>
							<label class="screen-reader-text" for="header-inline-search-field"><?php esc_html_e( 'Search', 'zskeleton' ); ?></label>
							<input type="search" id="header-inline-search-field" class="header-inline-search__input" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr__( 'Search…', 'zskeleton' ); ?>" autocomplete="off" />
						</form>
					</div>
				<?php endif; ?>
				<?php if ( $show_topbar_social_desktop ) : ?>
				<div class="header-social-icons" aria-label="<?php esc_attr_e( 'Social media', 'zskeleton' ); ?>">
					<?php if ( ! empty( $instagram_url ) ) : ?>
					<a href="<?php echo esc_url( $instagram_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Instagram', 'zskeleton' ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.35 3.608 1.325.975.975 1.263 2.242 1.325 3.608.058 1.266.069 1.646.069 4.85s-.012 3.584-.069 4.85c-.062 1.366-.35 2.633-1.325 3.608-.975.975-2.242 1.263-3.608 1.325-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.35-3.608-1.325-.975-.975-1.263-2.242-1.325-3.608-.058-1.266-.07-1.646-.07-4.85s.012-3.584.07-4.85c.062-1.366.35-2.633 1.325-3.608.975-.975 2.242-1.263 3.608-1.325 1.266-.058 1.646-.07 4.85-.07zM12 0C8.741 0 8.333.014 7.053.072 5.771.132 4.659.333 3.67.63c-.987.306-1.87.717-2.648 1.496S.936 3.672.63 4.64C.333 5.631.131 6.743.072 8.025.012 9.305 0 9.713 0 12s.012 2.695.072 3.975c.059 1.281.261 2.394.63 3.36.306.968.717 1.85 1.496 2.628.778.779 1.66 1.19 2.628 1.496.966.369 2.08.57 3.36.63 1.28.06 1.688.072 3.947.072s2.667-.012 3.947-.072c1.281-.059 2.394-.261 3.36-.63.968-.306 1.85-.717 2.628-1.496.779-.778 1.19-1.66 1.496-2.628.369-.966.57-2.079.63-3.36.06-1.28.072-1.689.072-3.947s-.012-2.667-.072-3.947c-.059-1.281-.261-2.394-.63-3.36-.306-.968-.717-1.85-1.496-2.628-.778-.779-1.66-1.19-2.628-1.496-.966-.369-2.08-.57-3.36-.63C14.667.014 14.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
					</a>
					<?php endif; ?>
					<?php if ( ! empty( $whatsapp_url ) ) : ?>
					<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'WhatsApp', 'zskeleton' ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
					</a>
					<?php endif; ?>
					<?php if ( ! empty( $facebook_url ) ) : ?>
						<a href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Facebook', 'zskeleton' ); ?>">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $twitter_url ) ) : ?>
						<a href="<?php echo esc_url( $twitter_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Twitter/X', 'zskeleton' ); ?>">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $linkedin_url ) ) : ?>
						<a href="<?php echo esc_url( $linkedin_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'LinkedIn', 'zskeleton' ); ?>">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $youtube_url ) ) : ?>
						<a href="<?php echo esc_url( $youtube_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'YouTube', 'zskeleton' ); ?>">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $github_url ) ) : ?>
						<a href="<?php echo esc_url( $github_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'GitHub', 'zskeleton' ); ?>">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $snapchat_url ) ) : ?>
						<a href="<?php echo esc_url( $snapchat_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Snapchat', 'zskeleton' ); ?>">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12 1.033-.301.165-.088.344-.104.464-.104.182 0 .359.029.509.09.45.149.734.479.734.838.015.449-.39.839-1.213 1.168-.089.029-.209.075-.344.119-.45.135-1.139.36-1.333.81-.09.224-.061.524.12.868l.015.015c.06.136 1.526 3.475 4.791 4.014.255.044.435.27.42.509 0 .075-.015.149-.045.225-.24.569-1.273.988-3.146 1.271-.059.091-.12.375-.164.57-.029.179-.074.36-.134.553-.076.271-.27.405-.555.405h-.03c-.135 0-.313-.031-.538-.074-.36-.075-.765-.135-1.273-.135-.3 0-.599.015-.913.074-.6.104-1.123.464-1.723.884-.853.599-1.826 1.288-3.294 1.288-.06 0-.119-.015-.18-.015h-.149c-1.468 0-2.427-.675-3.279-1.288-.599-.42-1.107-.779-1.707-.884-.314-.045-.629-.074-.928-.074-.54 0-.958.089-1.272.149-.211.043-.391.074-.54.074-.374 0-.523-.224-.583-.42-.061-.192-.09-.389-.135-.567-.046-.181-.105-.494-.166-.57-1.918-.222-2.95-.642-3.189-1.226-.031-.063-.052-.15-.055-.225-.015-.243.165-.465.42-.509 3.264-.54 4.73-3.879 4.791-4.02l.016-.029c.18-.345.224-.645.119-.869-.195-.434-.884-.658-1.332-.809-.121-.029-.24-.074-.346-.119-1.107-.435-1.257-.93-1.197-1.273.09-.479.674-.793 1.168-.793.146 0 .27.029.383.074.42.194.789.3 1.104.3.234 0 .384-.06.465-.105l-.046-.569c-.098-1.626-.225-3.651.307-4.837C7.392 1.077 10.739.807 11.727.807l.419-.015h.06z"/></svg>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $tiktok_url ) ) : ?>
						<a href="<?php echo esc_url( $tiktok_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'TikTok', 'zskeleton' ); ?>">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
						</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
			<?php if ( $show_topbar_sep_desktop ) : ?>
				<span class="header-topbar-split__sep" aria-hidden="true"></span>
			<?php endif; ?>
			<div class="header-topbar-split__right header-links header-links--split-top">
				<?php if ( ! is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="login-link"><?php esc_html_e( 'Member Login', 'zskeleton' ); ?></a>
				<?php else : ?>
					<a href="<?php echo esc_url( home_url( '/profile/' ) ); ?>" class="welcome-text">
						<?php
						printf(
							/* translators: %s: display name */
							esc_html__( 'Welcome, %s', 'zskeleton' ),
							esc_html( wp_get_current_user()->display_name )
						);
						?>
					</a>
					<a href="<?php echo esc_url( wp_logout_url() ); ?>" class="logout-link"><?php esc_html_e( 'Logout', 'zskeleton' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<!-- Main row: nav left | logo | nav right | membership actions -->
<div class="header-main header-main--split">
	<div class="container">
		<div class="header-main-split<?php echo $has_right_nav ? '' : ' header-main-split--no-right-nav'; ?>">
			<button class="menu-toggle <?php echo 'style2' === $mobile_menu_button_style ? 'menu-toggle--style-2' : 'menu-toggle--style-1'; ?>" type="button" aria-controls="site-navigation-mobile" aria-expanded="false">
				<span class="screen-reader-text"><?php esc_html_e( 'Primary Menu', 'zskeleton' ); ?></span>
				<span class="menu-icon"></span>
			</button>

			<nav id="site-navigation" class="main-navigation desktop-navigation header-nav header-nav--left" aria-label="<?php esc_attr_e( 'Primary navigation', 'zskeleton' ); ?>">
				<?php
				$primary_nav_args = array(
					'theme_location' => 'primary',
					'menu_id'        => 'primary-menu-desktop',
					'menu_class'     => 'nav-menu',
					// When no menu is assigned, wp_nav_menu uses fallback_cb and never runs wp_nav_menu_objects — inject split logo here instead (see zskeleton_split_header_primary_menu_fallback).
					'fallback_cb'    => ( ! $has_right_nav && ! has_nav_menu( 'primary' ) && function_exists( 'zskeleton_split_header_primary_menu_fallback' ) )
						? 'zskeleton_split_header_primary_menu_fallback'
						: 'zskeleton_default_menu',
				);
				// Logo as a centered <li> inside the primary menu when there is no "right of logo" menu.
				if ( ! $has_right_nav && class_exists( 'ZSkeleton_Walker_Nav_Menu_Split_Logo' ) ) {
					$primary_nav_args['zskeleton_split_header_inject_logo'] = true;
					$primary_nav_args['walker']                           = new ZSkeleton_Walker_Nav_Menu_Split_Logo();
				}
				wp_nav_menu( $primary_nav_args );
				?>
			</nav>

			<?php if ( $has_right_nav ) : ?>
			<div class="site-branding site-branding--split">
				<div class="site-logo">
					<?php
					$desktop_logo = zskeleton_get_logo( 'desktop' );
					$mobile_logo  = zskeleton_get_logo( 'mobile' );
					if ( $desktop_logo ) :
						?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( sprintf( __( 'Go to %s homepage', 'zskeleton' ), get_bloginfo( 'name' ) ) ); ?>">
							<?php if ( $mobile_logo && $mobile_logo !== $desktop_logo ) : ?>
								<?php if ( wp_is_mobile() ) : ?>
									<img src="<?php echo esc_url( $mobile_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="custom-logo mobile-logo" width="200" height="50" loading="eager" decoding="async" />
								<?php else : ?>
									<img src="<?php echo esc_url( $desktop_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="custom-logo desktop-logo" width="240" height="60" loading="eager" decoding="async" />
								<?php endif; ?>
							<?php else : ?>
								<img src="<?php echo esc_url( $desktop_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="custom-logo" width="240" height="60" loading="eager" decoding="async" />
							<?php endif; ?>
						</a>
					<?php else : ?>
						<div class="text-logo">
							<p class="site-title">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
							</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( ! $has_right_nav ) : ?>
			<!-- Logo lives in the primary <ul> on desktop; duplicate for small screens where desktop nav is hidden. -->
			<div class="site-branding site-branding--split site-branding--split-mobile-only">
				<div class="site-logo">
					<?php
					$desktop_logo = zskeleton_get_logo( 'desktop' );
					$mobile_logo  = zskeleton_get_logo( 'mobile' );
					if ( $desktop_logo ) :
						?>
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( sprintf( __( 'Go to %s homepage', 'zskeleton' ), get_bloginfo( 'name' ) ) ); ?>">
							<?php if ( $mobile_logo && $mobile_logo !== $desktop_logo ) : ?>
								<?php if ( wp_is_mobile() ) : ?>
									<img src="<?php echo esc_url( $mobile_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="custom-logo mobile-logo" width="200" height="50" loading="eager" decoding="async" />
								<?php else : ?>
									<img src="<?php echo esc_url( $desktop_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="custom-logo desktop-logo" width="240" height="60" loading="eager" decoding="async" />
								<?php endif; ?>
							<?php else : ?>
								<img src="<?php echo esc_url( $desktop_logo ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="custom-logo" width="240" height="60" loading="eager" decoding="async" />
							<?php endif; ?>
						</a>
					<?php else : ?>
						<div class="text-logo">
							<p class="site-title">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
							</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>

			<nav class="main-navigation desktop-navigation header-nav header-nav--right" aria-label="<?php esc_attr_e( 'Header links right of logo', 'zskeleton' ); ?>">
				<?php
				if ( $has_right_nav ) {
					wp_nav_menu(
						array(
							'theme_location' => 'header_nav_right',
							'menu_id'        => 'header-nav-right-desktop',
							'menu_class'     => 'nav-menu',
							'container'      => false,
							'fallback_cb'    => false,
							'depth'          => 2,
						)
					);
				}
				?>
			</nav>

			<div class="header-actions header-actions--split">
				<?php
				if ( is_user_logged_in() ) :
					$user_id = get_current_user_id();
					if ( class_exists( 'ZSkeleton_User_Profile_Fields' ) && ZSkeleton_User_Profile_Fields::user_has_active_membership( $user_id ) ) :
						$membership_type = ZSkeleton_User_Profile_Fields::get_user_membership_type( $user_id );
						?>
						<span class="member-status">
							<span class="member-badge"><?php echo esc_html( sprintf( __( 'Member: %s', 'zskeleton' ), ucfirst( $membership_type ) ) ); ?></span>
						</span>
					<?php elseif ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
						<a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="btn btn-primary btn--header-split"><?php esc_html_e( 'Join Membership', 'zskeleton' ); ?></a>
					<?php endif; ?>
				<?php elseif ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
					<a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="btn btn-primary btn--header-split"><?php esc_html_e( 'Join Membership', 'zskeleton' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<!-- Mobile drawer (same behaviour as default header) -->
		<nav id="site-navigation-mobile" class="main-navigation mobile-navigation<?php echo 'style2' === $mobile_menu_panel_style ? ' mobile-navigation--style-2' : ''; ?>">
			<button class="menu-close" type="button" aria-label="<?php esc_attr_e( 'Close Menu', 'zskeleton' ); ?>">×</button>
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
				<div class="mobile-menu-tabs">
					<button class="mobile-tab-btn active" type="button" data-tab="main-menu" aria-label="<?php esc_attr_e( 'Main Menu', 'zskeleton' ); ?>"><?php esc_html_e( 'Main Menu', 'zskeleton' ); ?></button>
					<button class="mobile-tab-btn" type="button" data-tab="members-area" aria-label="<?php esc_attr_e( 'Members Area', 'zskeleton' ); ?>"><?php esc_html_e( 'Members Area', 'zskeleton' ); ?></button>
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

		<?php zskeleton_header_search(); ?>
	</div>
</div>
