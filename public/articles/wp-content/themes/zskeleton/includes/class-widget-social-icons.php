<?php
/**
 * Widget: theme social profile links with icon color and grid (max 4 per row).
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output social icons from Appearance → ZSkeleton contact/social URLs.
 */
class ZSkeleton_Widget_Social_Icons extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'zskeleton_social_icons',
			__( 'ZSkeleton: Social icons', 'zskeleton' ),
			array(
				'description' => __( 'Displays social links from theme settings (Contact & social), including WhatsApp from the contact field. Style 1: card grid. Style 2: circular buttons in a row-or-grid. Icons per row applies to both styles; icon and title options apply as noted in the form.', 'zskeleton' ),
				'classname'   => 'widget_zskeleton_social_icons',
			)
		);
	}

	/**
	 * @param array<string, string> $args     Sidebar args.
	 * @param array<string, string> $instance Settings.
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );

		$links = $this->collect_profile_links();
		if ( empty( $links ) ) {
			if ( current_user_can( 'edit_theme_options' ) ) {
				echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<p class="zskeleton-social-icons-widget__empty">';
				esc_html_e( 'No social URLs are set. Add them under Appearance → ZSkeleton Settings → Contact & social.', 'zskeleton' );
				echo '</p>';
				echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			return;
		}

		$icon_color = $this->sanitize_icon_color( $instance['icon_color'] );
		// Default empty = currentColor (matches surrounding text on light sidebars; header uses its own hardcoded top-bar styles).
		$icon_color_css = ( '' === $icon_color ) ? 'currentColor' : $icon_color;
		$display_style  = $this->sanitize_display_style( $instance['display_style'] ?? '1' );
		$cols        = $this->sanitize_icons_per_row( $instance['icons_per_row'] );
		$uw          = $this->sanitize_underline_width( $instance['underline_width'] );
		$ut          = $this->sanitize_underline_thickness( $instance['underline_thickness'] );
		$u_color     = $this->sanitize_optional_color( $instance['underline_color'] );
		$icon_gap    = $this->sanitize_icon_horizontal_gap( $instance['icon_gap'] ?? '15px' );
		$max_w          = $this->sanitize_social_max_width( $instance['max_width'] ?? '' );
		$no_max_class   = ( 'none' === $max_w ) ? ' zskeleton-social-icons-widget--no-max-width' : '';
		$max_w_css      = ( '' !== $max_w && 'none' !== $max_w )
			? '--zskeleton-header-social-icons-max-width: ' . esc_attr( $max_w ) . '; --zskeleton-social-icons-max-width: ' . esc_attr( $max_w ) . ';'
			: '';

		$title = trim( (string) $instance['title'] );
		$title = '' !== $title ? apply_filters( 'widget_title', $title, $instance, $this->id_base ) : '';

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( '2' === $display_style ) {
			$links = $this->order_links_for_topbar_style( $links );
			$tb_style = sprintf(
				'%s--zskeleton-social-cols: %d; --zskeleton-social-icon-color: %s; --zskeleton-social-gap: %s;',
				'' !== $max_w_css ? $max_w_css : '',
				$cols,
				esc_attr( $icon_color_css ),
				esc_attr( $icon_gap )
			);
			echo '<div class="zskeleton-social-icons-widget zskeleton-social-icons-widget--style-topbar' . esc_attr( $no_max_class ) . '" style="' . esc_attr( $tb_style ) . '">';
			if ( '' !== $title ) {
				$u_style  = ' --zskeleton-widget-title-color: ' . esc_attr( $icon_color_css ) . '; --zskeleton-widget-underline-width: ' . esc_attr( $uw ) . '; --zskeleton-widget-underline-thickness: ' . esc_attr( $ut ) . ';';
				$u_style .= ( '' !== $u_color )
					? ' --zskeleton-widget-underline-color: ' . esc_attr( $u_color ) . ';'
					: ' --zskeleton-widget-underline-color: ' . esc_attr( $icon_color_css ) . ';';
				echo '<div class="zskeleton-widget-heading" style="' . esc_attr( $u_style ) . '">';
				echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</div>';
			}
			printf( '<div class="header-social-icons" aria-label="%s">', esc_attr__( 'Social media', 'zskeleton' ) );
			foreach ( $links as $item ) {
				$key = $item['key'];
				$url = $item['url'];
				if ( '' === $url ) {
					continue;
				}
				$label = $item['label'];
				$svg   = $this->get_svg_topbar( $key );
				if ( '' === $svg ) {
					$svg = $this->get_svg( $key );
				}
				if ( '' === $svg ) {
					continue;
				}
				$target = ( 0 === strpos( $url, 'http' ) || 0 === strpos( $url, '//' ) ) ? ' target="_blank" rel="noopener noreferrer"' : '';
				printf(
					'<a href="%s"%s aria-label="%s">%s</a>',
					esc_url( $url ),
					$target, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					esc_attr( $label ),
					$svg // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			}
			echo '</div></div>';
		} else {
			$style  = sprintf(
				'%s--zskeleton-social-cols: %d; --zskeleton-social-icon-color: %s; --zskeleton-social-gap: %s; --zskeleton-widget-underline-width: %s; --zskeleton-widget-underline-thickness: %s;',
				'' !== $max_w_css ? $max_w_css : '',
				$cols,
				esc_attr( $icon_color_css ),
				esc_attr( $icon_gap ),
				esc_attr( $uw ),
				esc_attr( $ut )
			);
			if ( '' !== $u_color ) {
				$style .= ' --zskeleton-widget-underline-color: ' . esc_attr( $u_color ) . ';';
			} else {
				$style .= ' --zskeleton-widget-underline-color: ' . esc_attr( $icon_color_css ) . ';';
			}

			echo '<div class="zskeleton-social-icons-widget' . esc_attr( $no_max_class ) . '" style="' . esc_attr( $style ) . '">';

			if ( '' !== $title ) {
				echo '<div class="zskeleton-widget-heading" style="--zskeleton-widget-title-color: ' . esc_attr( $icon_color_css ) . ';">';
				echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</div>';
			}

			echo '<ul class="zskeleton-social-icons" role="list">';

			foreach ( $links as $item ) {
				$key = $item['key'];
				$url = $item['url'];
				if ( '' === $url ) {
					continue;
				}
				$label = $item['label'];
				$svg   = $this->get_svg( $key );
				if ( '' === $svg ) {
					continue;
				}
				$target = ( 0 === strpos( $url, 'http' ) || 0 === strpos( $url, '//' ) ) ? ' target="_blank" rel="noopener noreferrer"' : '';

				printf(
					'<li class="zskeleton-social-icons__item"><a class="zskeleton-social-icons__link" href="%s"%s aria-label="%s">%s</a></li>',
					esc_url( $url ),
					$target, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					esc_attr( $label ),
					$svg // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			}

			echo '</ul></div>';
		}

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Top bar order: matches template-parts/header-split-top-search.php.
	 *
	 * @param array<int, array{key:string,url:string,label:string}> $links Collected links.
	 * @return array<int, array{key:string,url:string,label:string}>
	 */
	private function order_links_for_topbar_style( array $links ) {
		$order  = array( 'instagram', 'whatsapp', 'facebook', 'twitter', 'linkedin', 'youtube', 'github', 'snapchat', 'tiktok' );
		$by_key = array();
		foreach ( $links as $row ) {
			if ( isset( $row['key'] ) ) {
				$by_key[ (string) $row['key'] ] = $row;
			}
		}
		$out = array();
		foreach ( $order as $k ) {
			if ( isset( $by_key[ $k ] ) ) {
				$out[] = $by_key[ $k ];
			}
		}
		return $out;
	}

	/**
	 * SVG markup identical to header-split-top-search.php (split top bar).
	 *
	 * @param string $key Network key.
	 * @return string Unescaped HTML; escaped in widget().
	 */
	private function get_svg_topbar( $key ) {
		$key = sanitize_key( (string) $key );
		switch ( $key ) {
			case 'instagram':
				return '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.35 3.608 1.325.975.975 1.263 2.242 1.325 3.608.058 1.266.069 1.646.069 4.85s-.012 3.584-.069 4.85c-.062 1.366-.35 2.633-1.325 3.608-.975.975-2.242 1.263-3.608 1.325-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.35-3.608-1.325-.975-.975-1.263-2.242-1.325-3.608-.058-1.266-.07-1.646-.07-4.85s.012-3.584.07-4.85c.062-1.366.35-2.633 1.325-3.608.975-.975 2.242-1.263 3.608-1.325 1.266-.058 1.646-.07 4.85-.07zM12 0C8.741 0 8.333.014 7.053.072 5.771.132 4.659.333 3.67.63c-.987.306-1.87.717-2.648 1.496S.936 3.672.63 4.64C.333 5.631.131 6.743.072 8.025.012 9.305 0 9.713 0 12s.012 2.695.072 3.975c.059 1.281.261 2.394.63 3.36.306.968.717 1.85 1.496 2.628.778.779 1.66 1.19 2.628 1.496.966.369 2.08.57 3.36.63 1.28.06 1.688.072 3.947.072s2.667-.012 3.947-.072c1.281-.059 2.394-.261 3.36-.63.968-.306 1.85-.717 2.628-1.496.779-.778 1.19-1.66 1.496-2.628.369-.966.57-2.079.63-3.36.06-1.28.072-1.689.072-3.947s-.012-2.667-.072-3.947c-.059-1.281-.261-2.394-.63-3.36-.306-.968-.717-1.85-1.496-2.628-.778-.779-1.66-1.19-2.628-1.496-.966-.369-2.08-.57-3.36-.63C14.667.014 14.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>';
			case 'whatsapp':
				return '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>';
			case 'facebook':
				return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>';
			case 'twitter':
				return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>';
			case 'linkedin':
				return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>';
			case 'youtube':
				return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>';
			case 'github':
				return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>';
			case 'snapchat':
				return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12 1.033-.301.165-.088.344-.104.464-.104.182 0 .359.029.509.09.45.149.734.479.734.838.015.449-.39.839-1.213 1.168-.089.029-.209.075-.344.119-.45.135-1.139.36-1.333.81-.09.224-.061.524.12.868l.015.015c.06.136 1.526 3.475 4.791 4.014.255.044.435.27.42.509 0 .075-.015.149-.045.225-.24.569-1.273.988-3.146 1.271-.059.091-.12.375-.164.57-.029.179-.074.36-.134.553-.076.271-.27.405-.555.405h-.03c-.135 0-.313-.031-.538-.074-.36-.075-.765-.135-1.273-.135-.3 0-.599.015-.913.074-.6.104-1.123.464-1.723.884-.853.599-1.826 1.288-3.294 1.288-.06 0-.119-.015-.18-.015h-.149c-1.468 0-2.427-.675-3.279-1.288-.599-.42-1.107-.779-1.707-.884-.314-.045-.629-.074-.928-.074-.54 0-.958.089-1.272.149-.211.043-.391.074-.54.074-.374 0-.523-.224-.583-.42-.061-.192-.09-.389-.135-.567-.046-.181-.105-.494-.166-.57-1.918-.222-2.95-.642-3.189-1.226-.031-.063-.052-.15-.055-.225-.015-.243.165-.465.42-.509 3.264-.54 4.73-3.879 4.791-4.02l.016-.029c.18-.345.224-.645.119-.869-.195-.434-.884-.658-1.332-.809-.121-.029-.24-.074-.346-.119-1.107-.435-1.257-.93-1.197-1.273.09-.479.674-.793 1.168-.793.146 0 .27.029.383.074.42.194.789.3 1.104.3.234 0 .384-.06.465-.105l-.046-.569c-.098-1.626-.225-3.651.307-4.837C7.392 1.077 10.739.807 11.727.807l.419-.015h.06z"/></svg>';
			case 'tiktok':
				return '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>';
			default:
				return '';
		}
	}

	/**
	 * @return array<int, array{key:string,url:string,label:string}>
	 */
	private function collect_profile_links() {
		$out   = array();
		$order = apply_filters(
			'zskeleton_social_icons_widget_order',
			array( 'facebook', 'twitter', 'linkedin', 'youtube', 'instagram', 'whatsapp', 'github', 'snapchat', 'tiktok' )
		);
		$map   = function_exists( 'zskeleton_get_social_profile_options_map' ) ? zskeleton_get_social_profile_options_map() : array();

		foreach ( $order as $key ) {
			$key = sanitize_key( $key );
			if ( 'whatsapp' === $key ) {
				$url = $this->get_whatsapp_url();
				if ( '' === $url ) {
					continue;
				}
				$out[] = array(
					'key'   => 'whatsapp',
					'url'   => $url,
					'label' => $this->get_network_label( 'whatsapp' ),
				);
				continue;
			}
			if ( ! isset( $map[ $key ] ) ) {
				continue;
			}
			$raw = function_exists( 'zskeleton_get_contact' ) ? zskeleton_get_contact( $key ) : '';
			$raw = is_string( $raw ) ? trim( $raw ) : '';
			if ( '' === $raw ) {
				continue;
			}
			$raw = $this->normalize_social_href( $raw );
			// Match header templates (e.g. header-split-top-search.php): esc_url( $url ) with default protocols — not only http/https (avoids empty hrefs for valid stored URLs).
			$url = esc_url( $raw );
			if ( '' === $url ) {
				continue;
			}
			$out[] = array(
				'key'   => $key,
				'url'   => $url,
				'label' => $this->get_network_label( $key ),
			);
		}

		return $out;
	}

	/**
	 * Add https:// to host-like values so esc_url_raw() does not drop theme entries pasted without a scheme.
	 *
	 * @param string $raw Raw value from options / theme.
	 * @return string
	 */
	private function normalize_social_href( $raw ) {
		$raw = trim( (string) $raw );
		if ( '' === $raw ) {
			return '';
		}
		// Protocol-relative //host/... → https://host/... (consistent with esc_url() + href).
		if ( strlen( $raw ) >= 2 && '//' === substr( $raw, 0, 2 ) ) {
			$raw = 'https:' . $raw;
		}
		if ( preg_match( '#^(https?|mailto:|tel:)#i', $raw ) ) {
			return $raw;
		}
		// e.g. www.facebook.com/… or x.com/…
		if ( preg_match( '/^[a-z0-9.-]+\.[a-z]{2,}[\w\-./?#&=:%+~]*$/i', $raw ) ) {
			return 'https://' . $raw;
		}
		return $raw;
	}

	/**
	 * @return string
	 */
	private function get_whatsapp_url() {
		if ( ! function_exists( 'zskeleton_get_contact' ) ) {
			return '';
		}
		$raw = trim( (string) zskeleton_get_contact( 'whatsapp' ) );
		if ( '' === $raw ) {
			return '';
		}
		if ( strlen( $raw ) >= 2 && '//' === substr( $raw, 0, 2 ) ) {
			$raw = 'https:' . $raw;
		}
		if ( preg_match( '#^https?://#i', $raw ) ) {
			$out = esc_url_raw( $raw );
			return is_string( $out ) && '' !== $out ? $out : '';
		}
		$digits = preg_replace( '/\D+/', '', $raw );
		return '' !== $digits ? 'https://wa.me/' . $digits : '';
	}

	/**
	 * @param string $key Network key.
	 * @return string
	 */
	private function get_network_label( $key ) {
		$labels = array(
			'facebook'  => _x( 'Facebook', 'social icon', 'zskeleton' ),
			'twitter'   => _x( 'X (Twitter)', 'social icon', 'zskeleton' ),
			'linkedin'  => _x( 'LinkedIn', 'social icon', 'zskeleton' ),
			'youtube'   => _x( 'YouTube', 'social icon', 'zskeleton' ),
			'instagram' => _x( 'Instagram', 'social icon', 'zskeleton' ),
			'github'    => _x( 'GitHub', 'social icon', 'zskeleton' ),
			'snapchat'  => _x( 'Snapchat', 'social icon', 'zskeleton' ),
			'tiktok'    => _x( 'TikTok', 'social icon', 'zskeleton' ),
			'whatsapp'  => _x( 'WhatsApp', 'social icon', 'zskeleton' ),
		);
		$key    = sanitize_key( $key );
		return isset( $labels[ $key ] ) ? $labels[ $key ] : ucfirst( $key );
	}

	/**
	 * @param string $key Network key.
	 * @return string Unescaped HTML (inline SVG with aria-hidden); escaped at output in widget().
	 */
	private function get_svg( $key ) {
		$key = sanitize_key( $key );
		$common = 'class="zskeleton-social-icons__icon" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="currentColor"';

		switch ( $key ) {
			case 'facebook':
				return '<svg ' . $common . '><path d="M14 13.5h2.5l1-3H14v-1.2c0-.9.3-1.5 1.5-1.5H18V4.1h-2.4c-2.4 0-3.6 1.1-3.6 3.2V10.5H9v3h2.9V24h3.1V13.5z"/></svg>';
			case 'twitter':
				return '<svg ' . $common . '><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>';
			case 'linkedin':
				return '<svg ' . $common . '><path d="M4.98 3.5C4.98 2.1 5.9 1.2 7.1 1.2c1.1 0 1.9.8 1.9 1.9 0 1.3-.8 2.1-1.9 2.1-1.1.1-2.1-.7-2.1-1.7zM.5 22.5h4.6V8.3H.5v14.2zm7.2-14.2h4.3v1.8h.1c.6-1.1 2-2.2 4.1-2.2 4.3 0 5.1 2.6 5.1 5.4v6.1h-4.5v-5.1c0-1.5-.1-3.1-1.3-3.1-1.4 0-1.6 1-1.6 2.1v6.1H7.6V8.3z"/></svg>';
			case 'youtube':
				return '<svg ' . $common . '><path d="M23.5 6.2c-.2-1-1-1.7-1.9-1.8C19.1 3.7 12 3.7 12 3.7s-7.1 0-9.5.2c-1 .1-1.7.8-1.9 1.8C.4 8.1.4 12 .4 12s0 3.8.2 5.7c.2 1 1 1.7 1.9 1.8C4.8 20.2 12 20.2 12 20.2s7.1 0 9.5-.2c.9-.1 1.7-.8 1.9-1.8.2-1.8.2-5.5.2-5.5s0-3.8-.1-5.7zM9.5 15.1V8.7l5.5 3.2-5.5 3.2z"/></svg>';
			case 'instagram':
				return '<svg ' . $common . '><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7zm5 2.2a4.8 4.8 0 1 1 0 9.5 4.8 4.8 0 0 1 0-9.5zM12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8zm5.2-2.4a1.1 1.1 0 1 0 0 2.1 1.1 1.1 0 0 0 0-2.1z"/></svg>';
			case 'github':
				return '<svg ' . $common . '><path d="M12 .5C5.65.5.5 5.5.5 11.8c0 4.7 3.1 8.6 7.2 9.8.5.1.7-.2.7-.4v-1.6c-2.8.5-3.4-1.2-3.4-1.2-.5-1-1.1-1.2-1.1-1.2-.9-.6.1-.5.1-.5 1 .1 1.5 1 1.5 1 .9 1.5 2.2 1.1 2.7.8.1-.6.3-1.1.5-1.3-2.2-.2-4.4-1-4.4-4.3 0-1 .4-1.7 1-2.2-.1-.2-.4-1 .1-2.1 0 0 .8-.3 2.6.8.8-.1 1.5-.1 2.1-.1.7 0 1.3.1 2.1.2 1.8-1.1 2.5-.8 2.5-.8.4 1.1.1 1.8.1 2.1.6.5 1 1.2 1 2.2 0 3.3-2.2 4-4.2 4.2.3.2.5.5.5 1.1V21c0 .2.2.4.6.3 4.1-1.2 7.1-5.1 7.1-9.6C23.5 5.5 18.4.5 12 .5z"/></svg>';
			case 'snapchat':
				return '<svg ' . $common . '><path d="M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12 1.033-.301.165-.088.344-.104.464-.104.182 0 .359.029.509.09.45.149.734.479.734.838.015.449-.39.839-1.213 1.168-.089.029-.209.075-.344.119-.45.135-1.139.36-1.333.81-.09.224-.061.524.12.868l.015.015c.06.136 1.526 3.475 4.791 4.014.255.044.435.27.42.509 0 .075-.015.149-.045.225-.24.569-1.273.988-3.146 1.271-.059.091-.12.375-.164.57-.029.179-.074.36-.134.553-.076.271-.27.405-.555.405h-.03c-.135 0-.313-.031-.538-.074-.36-.075-.765-.135-1.273-.135-.3 0-.599.015-.913.074-.6.104-1.123.464-1.723.884-.853.599-1.826 1.288-3.294 1.288-.06 0-.119-.015-.18-.015h-.149c-1.468 0-2.427-.675-3.279-1.288-.599-.42-1.107-.779-1.707-.884-.314-.045-.629-.074-.928-.074-.54 0-.958.089-1.272.149-.211.043-.391.074-.54.074-.374 0-.523-.224-.583-.42-.061-.192-.09-.389-.135-.567-.046-.181-.105-.494-.166-.57-1.918-.222-2.95-.642-3.189-1.226-.031-.063-.052-.15-.055-.225-.015-.243.165-.465.42-.509 3.264-.54 4.73-3.879 4.791-4.02l.016-.029c.18-.345.224-.645.119-.869-.195-.434-.884-.658-1.332-.809-.121-.029-.24-.074-.346-.119-1.107-.435-1.257-.93-1.197-1.273.09-.479.674-.793 1.168-.793.146 0 .27.029.383.074.42.194.789.3 1.104.3.234 0 .384-.06.465-.105l-.046-.569c-.098-1.626-.225-3.651.307-4.837C7.392 1.077 10.739.807 11.727.807l.419-.015h.06z"/></svg>';
			case 'tiktok':
				return '<svg ' . $common . '><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>';
			case 'whatsapp':
				return '<svg ' . $common . '><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
			default:
				return '';
		}
	}

	/**
	 * @param array<string, string> $instance Instance.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['title'] ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>"><?php esc_html_e( 'Display style', 'zskeleton' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'display_style' ) ); ?>">
				<option value="1" <?php selected( (string) ( $instance['display_style'] ?? '1' ), '1' ); ?>><?php esc_html_e( 'Style 1 — icon grid (cards)', 'zskeleton' ); ?></option>
				<option value="2" <?php selected( (string) ( $instance['display_style'] ?? '1' ), '2' ); ?>><?php esc_html_e( 'Style 2 — same as top bar (circles, row)', 'zskeleton' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'icon_color' ) ); ?>"><?php esc_html_e( 'Icon & title color', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'icon_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'icon_color' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['icon_color'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. #fff on dark footer, or leave empty to match text', 'zskeleton' ); ?>" />
		</p>
		<p class="description">
			<?php esc_html_e( 'The header top bar always uses a dark bar and light icons. This widget uses your color here—leave empty for automatic contrast on light sidebars, or set #ffffff for a dark footer.', 'zskeleton' ); ?>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'icons_per_row' ) ); ?>"><?php esc_html_e( 'Icons per row (max 4)', 'zskeleton' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'icons_per_row' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'icons_per_row' ) ); ?>">
				<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
					<option value="<?php echo esc_attr( (string) $i ); ?>" <?php selected( (int) $instance['icons_per_row'], $i ); ?>><?php echo esc_html( (string) $i ); ?></option>
				<?php endfor; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'icon_gap' ) ); ?>"><?php esc_html_e( 'Horizontal space between icons (CSS gap)', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'icon_gap' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'icon_gap' ) ); ?>" type="text" value="<?php echo esc_attr( (string) ( $instance['icon_gap'] ?? '15px' ) ); ?>" placeholder="15px" />
		</p>
		<p class="description"><?php esc_html_e( 'Also sets vertical gap between grid rows. Default 15px.', 'zskeleton' ); ?></p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'max_width' ) ); ?>"><?php esc_html_e( 'Max width of icon area', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'max_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'max_width' ) ); ?>" type="text" value="<?php echo esc_attr( (string) ( $instance['max_width'] ?? '' ) ); ?>" placeholder="24rem" />
		</p>
		<p class="description"><?php esc_html_e( 'Caps how wide the social icons can grow (e.g. 24rem, 400px, 100%). Style 2 matches the header default (24rem) when left empty. Use none to remove the cap on Style 1.', 'zskeleton' ); ?></p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'underline_width' ) ); ?>"><?php esc_html_e( 'Title underline length (CSS width)', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'underline_width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'underline_width' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['underline_width'] ); ?>" placeholder="3rem" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'underline_thickness' ) ); ?>"><?php esc_html_e( 'Title underline thickness (CSS height)', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'underline_thickness' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'underline_thickness' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['underline_thickness'] ); ?>" placeholder="2px" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'underline_color' ) ); ?>"><?php esc_html_e( 'Title underline color (optional)', 'zskeleton' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'underline_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'underline_color' ) ); ?>" type="text" value="<?php echo esc_attr( (string) $instance['underline_color'] ); ?>" placeholder="<?php esc_attr_e( 'Empty = use icon color', 'zskeleton' ); ?>" />
		</p>
		<?php
	}

	/**
	 * @param array<string, string> $new_instance New.
	 * @param array<string, string> $old_instance Old.
	 * @return array<string, string>
	 */
	public function update( $new_instance, $old_instance ) {
		unset( $old_instance );
		$out                    = $this->defaults();
		$out['title']           = sanitize_text_field( (string) ( $new_instance['title'] ?? '' ) );
		$out['icon_color']        = $this->sanitize_icon_color( $new_instance['icon_color'] ?? '' );
		$out['display_style']     = $this->sanitize_display_style( $new_instance['display_style'] ?? '1' );
		$out['icons_per_row']     = (string) $this->sanitize_icons_per_row( $new_instance['icons_per_row'] ?? '4' );
		$out['icon_gap']          = $this->sanitize_icon_horizontal_gap( $new_instance['icon_gap'] ?? '15px' );
		$out['underline_width']     = $this->sanitize_underline_width( $new_instance['underline_width'] ?? '' );
		$out['underline_thickness'] = $this->sanitize_underline_thickness( $new_instance['underline_thickness'] ?? '' );
		$out['underline_color']     = $this->sanitize_optional_color( $new_instance['underline_color'] ?? '' );
		$out['max_width']             = $this->sanitize_social_max_width( $new_instance['max_width'] ?? '' );
		return $out;
	}

	/**
	 * @return array<string, string>
	 */
	private function defaults() {
		return array(
			'title'                 => '',
			'icon_color'            => '',
			'display_style'         => '1',
			'icons_per_row'         => '4',
			'icon_gap'              => '15px',
			'underline_width'       => '3rem',
			'underline_thickness'   => '2px',
			'underline_color'       => '',
			'max_width'              => '',
		);
	}

	/**
	 * @param mixed $v Raw.
	 * @return string "1" or "2"
	 */
	private function sanitize_display_style( $v ) {
		$v = (string) $v;
		return in_array( $v, array( '1', '2' ), true ) ? $v : '1';
	}

	/**
	 * @param mixed $v Raw.
	 * @return string
	 */
	private function sanitize_icon_color( $v ) {
		$v = trim( (string) $v );
		if ( '' === $v ) {
			return '';
		}
		$hex = sanitize_hex_color( $v );
		if ( is_string( $hex ) && '' !== $hex ) {
			return $hex;
		}
		return '';
	}

	/**
	 * @param mixed $v Raw.
	 * @return string Empty or hex.
	 */
	private function sanitize_optional_color( $v ) {
		$v = trim( (string) $v );
		if ( '' === $v ) {
			return '';
		}
		$hex = sanitize_hex_color( $v );
		return is_string( $hex ) && '' !== $hex ? $hex : '';
	}

	/**
	 * @param mixed $v Raw.
	 * @return int
	 */
	private function sanitize_icons_per_row( $v ) {
		$n = (int) $v;
		if ( $n < 1 ) {
			$n = 1;
		}
		if ( $n > 4 ) {
			$n = 4;
		}
		return $n;
	}

	/**
	 * @param mixed $v Raw gap value.
	 * @return string Sanitized non-empty length (e.g. 15px).
	 */
	private function sanitize_icon_horizontal_gap( $v ) {
		$v = trim( (string) $v );
		if ( '' === $v ) {
			return '15px';
		}
		if ( preg_match( '/^[0-9.]+\s*(px|rem|em|%|ch|vw|vh|ex|cm|mm|in|pt|pc)$/i', $v ) ) {
			return $v;
		}
		return '15px';
	}

	/**
	 * Max width for the widget’s icon area (grid and “top bar” style).
	 * Empty: leave CSS default (Style 2 uses 24rem via var fallback when unset).
	 *
	 * @param mixed $v Raw.
	 * @return string Sanitized max-width value or empty.
	 */
	private function sanitize_social_max_width( $v ) {
		$v = trim( (string) $v );
		if ( '' === $v ) {
			return '';
		}
		if ( 'none' === strtolower( $v ) ) {
			return 'none';
		}
		if ( preg_match( '/^[0-9.]+\s*(px|rem|em|%|ch|vw|vh|ex|cm|mm|in|pt|pc)$/i', $v ) ) {
			return $v;
		}
		return '';
	}

	/**
	 * @param mixed $v Raw.
	 * @return string
	 */
	private function sanitize_underline_width( $v ) {
		$v = trim( (string) $v );
		if ( '' === $v ) {
			return '3rem';
		}
		if ( preg_match( '/^[0-9.]+\s*(px|rem|em|%|ch|vw|vh|cm|mm|in|pt|pc)$/i', $v ) ) {
			return $v;
		}
		return '3rem';
	}

	/**
	 * @param mixed $v Raw CSS height.
	 * @return string
	 */
	private function sanitize_underline_thickness( $v ) {
		$v = trim( (string) $v );
		if ( '' === $v ) {
			return '2px';
		}
		if ( preg_match( '/^[0-9.]+\s*(px|rem|em|%|ch|ex|cm|mm|in|pt|pc)$/i', $v ) ) {
			return $v;
		}
		return '2px';
	}
}
