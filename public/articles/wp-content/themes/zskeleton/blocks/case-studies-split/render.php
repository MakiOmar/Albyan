<?php
/**
 * Dynamic render: case study split testimonial.
 *
 * @package ZSkeleton_Theme
 * @var array         $attributes Block attributes.
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'zskeleton_cssplit_sanitize_hex_color_attr' ) ) {
	/**
	 * Hex (#RRGGGBB) for CSS variables.
	 *
	 * @param mixed $val Raw attribute.
	 * @return string
	 */
	function zskeleton_cssplit_sanitize_hex_color_attr( $val ): string {
		if ( ! is_string( $val ) ) {
			return '';
		}
		$val = strtoupper( trim( $val ) );
		return preg_match( '/^#[0-9A-F]{6}$/', $val ) ? $val : '';
	}
}

if ( ! function_exists( 'zskeleton_cssplit_hex_to_rgba' ) ) {
	/**
	 * Convert #RRGGBB plus opacity to rgba(...).
	 *
	 * @param string $hex  #RRGGBB.
	 * @param float  $a    Alpha 0-1.
	 * @return string
	 */
	function zskeleton_cssplit_hex_to_rgba( string $hex, float $a ): string {
		if ( ! preg_match( '/^#[0-9A-Fa-f]{6}$/', $hex ) ) {
			return 'rgba(255,255,255,0.88)';
		}
		$r       = hexdec( substr( $hex, 1, 2 ) );
		$g       = hexdec( substr( $hex, 3, 2 ) );
		$b       = hexdec( substr( $hex, 5, 2 ) );
		$a       = min( 1.0, max( 0.0, $a ) );
		$a_print = preg_replace( '/\.?0+$/', '', sprintf( '%.3f', $a ) );

		return sprintf( 'rgba(%d,%d,%d,%s)', (int) $r, (int) $g, (int) $b, $a_print ?: '0' );
	}
}

if ( function_exists( 'wp_enqueue_style' ) ) {
	wp_enqueue_style( 'zskeleton-case-studies-split' );
}

$h_desk = isset( $attributes['stageMinHeightDesktop'] ) ? absint( $attributes['stageMinHeightDesktop'] ) : 460;
$h_desk = min( 1600, max( 200, $h_desk ) );
$h_mob  = isset( $attributes['stageMinHeightMobile'] ) ? absint( $attributes['stageMinHeightMobile'] ) : 480;
$h_mob  = min( 1600, max( 200, $h_mob ) );
$portrait_h_desk = isset( $attributes['portraitMaxHeightDesktop'] ) ? absint( $attributes['portraitMaxHeightDesktop'] ) : 460;
$portrait_h_desk = min( 1600, max( 120, $portrait_h_desk ) );
$portrait_h_mob  = isset( $attributes['portraitMaxHeightMobile'] ) ? absint( $attributes['portraitMaxHeightMobile'] ) : 260;
$portrait_h_mob  = min( 1600, max( 80, $portrait_h_mob ) );

$c1        = zskeleton_cssplit_sanitize_hex_color_attr( isset( $attributes['gradientColor1'] ) ? $attributes['gradientColor1'] : '' );
$c2        = zskeleton_cssplit_sanitize_hex_color_attr( isset( $attributes['gradientColor2'] ) ? $attributes['gradientColor2'] : '' );
$angle_raw = isset( $attributes['gradientAngleDeg'] ) ? absint( $attributes['gradientAngleDeg'] ) : 160;
$r_px      = isset( $attributes['borderRadiusPx'] ) ? absint( $attributes['borderRadiusPx'] ) : 20;
$r_px     = min( 64, max( 0, $r_px ) );
$blur_px  = isset( $attributes['blurRadiusPx'] ) ? absint( $attributes['blurRadiusPx'] ) : 18;
$blur_px  = min( 48, max( 0, $blur_px ) );

if ( '' === $c1 ) {
	$c1 = '#f8fafc';
}
if ( '' === $c2 ) {
	$c2 = '#e2e8f0';
}

$angle_deg = ( $angle_raw % 360 );

$gradient_css = sprintf( 'linear-gradient(%ddeg,%s,%s)', (int) $angle_deg, $c1, $c2 );

$left_id  = isset( $attributes['leftImageId'] ) ? absint( $attributes['leftImageId'] ) : 0;
$left_url = isset( $attributes['leftImageUrl'] ) ? esc_url_raw( (string) $attributes['leftImageUrl'] ) : '';
$left_alt = isset( $attributes['leftImageAlt'] ) ? sanitize_text_field( (string) $attributes['leftImageAlt'] ) : '';

if ( $left_id && '' === $left_url && function_exists( 'wp_get_attachment_image_url' ) ) {
	$u = wp_get_attachment_image_url( $left_id, 'large' );
	if ( $u ) {
		$left_url = esc_url_raw( $u );
	}
}

$blur_id  = isset( $attributes['rightBlurImageId'] ) ? absint( $attributes['rightBlurImageId'] ) : 0;
$blur_url = isset( $attributes['rightBlurImageUrl'] ) ? esc_url_raw( (string) $attributes['rightBlurImageUrl'] ) : '';
if ( $blur_id && '' === $blur_url && function_exists( 'wp_get_attachment_image_url' ) ) {
	$bu = wp_get_attachment_image_url( $blur_id, 'large' );
	if ( $bu ) {
		$blur_url = esc_url_raw( $bu );
	}
}
if ( '' === $blur_url && '' !== $left_url ) {
	$blur_url = $left_url;
}

$logo_id  = isset( $attributes['brandLogoId'] ) ? absint( $attributes['brandLogoId'] ) : 0;
$logo_url = isset( $attributes['brandLogoUrl'] ) ? esc_url_raw( (string) $attributes['brandLogoUrl'] ) : '';
$logo_alt = isset( $attributes['brandLogoAlt'] ) ? sanitize_text_field( (string) $attributes['brandLogoAlt'] ) : '';
if ( $logo_id && '' === $logo_url && function_exists( 'wp_get_attachment_image_url' ) ) {
	$lu = wp_get_attachment_image_url( $logo_id, 'medium' );
	if ( $lu ) {
		$logo_url = esc_url_raw( $lu );
	}
}

$card_subtitle = isset( $attributes['cardSubtitle'] ) ? sanitize_text_field( (string) $attributes['cardSubtitle'] ) : '';
$quote_html    = isset( $attributes['quoteHtml'] ) ? (string) $attributes['quoteHtml'] : '';
$person_name   = isset( $attributes['personName'] ) ? sanitize_text_field( (string) $attributes['personName'] ) : '';
$person_role   = isset( $attributes['personRole'] ) ? sanitize_text_field( (string) $attributes['personRole'] ) : '';

$section_title       = isset( $attributes['sectionTitle'] ) ? (string) $attributes['sectionTitle'] : '';
$section_description = isset( $attributes['sectionDescription'] ) ? (string) $attributes['sectionDescription'] : '';

$td             = isset( $attributes['textDirection'] ) ? sanitize_key( (string) $attributes['textDirection'] ) : 'auto';
$t_title_stripped = trim( wp_strip_all_tags( $section_title ) );
$heading_id       = '' !== $t_title_stripped ? wp_unique_id( 'zskeleton-cssplit-heading-' ) : '';

$tint_hex = zskeleton_cssplit_sanitize_hex_color_attr( isset( $attributes['colorRightPanelTint'] ) ? $attributes['colorRightPanelTint'] : '' );
if ( '' === $tint_hex ) {
	$tint_hex = '#ffffff';
}

$color_style_bits = array(
	'background'                                                                      => $gradient_css,
	'--zskeleton-cssplit-stage-desktop'                                                   => sprintf( '%dpx', $h_desk ),
	'--zskeleton-cssplit-stage-mobile'                                                    => sprintf( '%dpx', $h_mob ),
	'--zskeleton-cssplit-portrait-max-h-desktop'                                          => sprintf( '%dpx', $portrait_h_desk ),
	'--zskeleton-cssplit-portrait-max-h-mobile'                                           => sprintf( '%dpx', $portrait_h_mob ),
	'--zskeleton-cssplit-radius'                                                         => sprintf( '%dpx', $r_px ),
	'--zskeleton-cssplit-blur'                                                           => sprintf( '%dpx', $blur_px ),
	'--zskeleton-cssplit-right-tint'                                                     => zskeleton_cssplit_hex_to_rgba( $tint_hex, 0.88 ),
);

$txt_map = array(
	'colorSectionTitle'   => '--zskeleton-cssplit-title',
	'colorSectionDesc'    => '--zskeleton-cssplit-desc',
	'colorCardSubtitle'   => '--zskeleton-cssplit-card-subtitle',
	'colorQuote'          => '--zskeleton-cssplit-quote',
	'colorPersonName'     => '--zskeleton-cssplit-name',
	'colorPersonRole'     => '--zskeleton-cssplit-role',
);
foreach ( $txt_map as $attr_key => $var_name ) {
	$h = zskeleton_cssplit_sanitize_hex_color_attr( isset( $attributes[ $attr_key ] ) ? $attributes[ $attr_key ] : '' );
	if ( '' !== $h ) {
		$color_style_bits[ $var_name ] = $h;
	}
}

// Blur BG image CSS variable on full section (pseudo-element uses it).
$section_style_parts = array();
if ( '' !== $blur_url ) {
	$b_u                = esc_url( $blur_url );
	$section_style_parts[] = '--zskeleton-cssplit-blur-img:url("' . $b_u . '")';
}

$style_sections = implode(
	';',
	array_map(
		function ( $k, $v ) {
			return $k . ':' . $v;
		},
		array_keys( $color_style_bits ),
		array_values( $color_style_bits )
	)
);
if ( count( $section_style_parts ) > 0 ) {
	$style_sections .= ';' . implode( ';', $section_style_parts );
}

$wrapper_attrs = array(
	'class' => 'zskeleton-cssplit',
	'style' => $style_sections,
);
if ( 'rtl' === $td || 'ltr' === $td ) {
	$wrapper_attrs['dir'] = $td;
}
if ( '' !== $heading_id ) {
	$wrapper_attrs['aria-labelledby'] = $heading_id;
} else {
	$wrapper_attrs['aria-label'] = esc_attr__( 'Case study section', 'zskeleton' );
}

$wrapper = '';
if ( isset( $block ) && $block instanceof WP_Block ) {
	$wrapper = get_block_wrapper_attributes(
		$wrapper_attrs,
		'',
		$block
	);
} else {
	// Hydration fallback path may include this file without a WP_Block instance.
	$wrapper = get_block_wrapper_attributes( $wrapper_attrs );
}

ob_start();
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped - get_block_wrapper_attributes escapes. ?>>
	<?php if ( '' !== $blur_url ) : ?>
	<div class="zskeleton-cssplit__blur-layer" aria-hidden="true"></div>
	<div class="zskeleton-cssplit__tint" aria-hidden="true"></div>
	<?php endif; ?>

	<!-- Case study split testimonial markup -->
	<div class="zskeleton-cssplit__inner">
		<?php if ( '' !== trim( wp_strip_all_tags( $section_title ) ) || '' !== trim( $section_description ) ) : ?>
		<header class="zskeleton-cssplit__header">
			<?php if ( '' !== trim( wp_strip_all_tags( $section_title ) ) ) : ?>
				<div class="zskeleton-cssplit__titles">
					<?php
					if ( function_exists( 'zskeleton_render_block_heading_title_row' ) ) {
						$row = zskeleton_render_block_heading_title_row(
							array(
								'title_inner_html' => esc_html( $section_title ),
								'heading_tag'      => 'h2',
								'attributes'       => is_array( $attributes ) ? $attributes : array(),
								'title_class'      => 'zskeleton-cssplit__heading',
								'align'            => 'center',
								'heading_id'       => $heading_id,
							)
						);
						if ( '' !== $row ) {
							echo $row; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} else {
							echo '<h2 class="zskeleton-cssplit__heading" id="' . esc_attr( $heading_id ) . '">' . esc_html( $section_title ) . '</h2>';
						}
					} else {
						echo '<h2 class="zskeleton-cssplit__heading" id="' . esc_attr( $heading_id ) . '">' . esc_html( $section_title ) . '</h2>';
					}
					?>
				</div>
			<?php endif; ?>
			<?php if ( '' !== $section_description ) : ?>
				<p class="zskeleton-cssplit__intro"><?php echo esc_html( $section_description ); ?></p>
			<?php endif; ?>
		</header>
		<?php endif; ?>

		<div class="zskeleton-cssplit__stage">
			<div class="zskeleton-cssplit__card">
				<!-- Left half: portrait photo -->
				<div class="zskeleton-cssplit__col zskeleton-cssplit__col--photo">
					<?php if ( '' !== $left_url ) : ?>
						<?php
						if ( $left_id ) {
							echo wp_get_attachment_image(
								$left_id,
								'large',
								false,
								array(
									'class'    => 'zskeleton-cssplit__portrait',
									'alt'      => '' !== $left_alt ? $left_alt : '',
									'loading'  => 'lazy',
									'decoding' => 'async',
								)
							);
						} else {
							?>
							<img class="zskeleton-cssplit__portrait" src="<?php echo esc_url( $left_url ); ?>" alt="<?php echo esc_attr( $left_alt ); ?>" loading="lazy" decoding="async" />
							<?php
						}
						?>
					<?php else : ?>
						<p class="zskeleton-cssplit__empty-hint"><?php esc_html_e( 'Choose a portrait image in the sidebar for the left column.', 'zskeleton' ); ?></p>
					<?php endif; ?>
				</div>
				<!-- Right half: content -->
				<div class="zskeleton-cssplit__col zskeleton-cssplit__col--content">
					<div class="zskeleton-cssplit__content-inner">

						<?php if ( '' !== $logo_url || '' !== $card_subtitle ) : ?>
							<div class="zskeleton-cssplit__meta-row">
								<?php if ( '' !== $logo_url ) : ?>
									<div class="zskeleton-cssplit__logo-wrap">
										<?php
										if ( $logo_id ) {
											echo wp_get_attachment_image(
												$logo_id,
												'medium',
												false,
												array(
													'class'    => 'zskeleton-cssplit__logo',
													'alt'      => $logo_alt !== '' ? $logo_alt : '',
													'loading'  => 'lazy',
													'decoding' => 'async',
												)
											);
										} else {
											echo '<img class="zskeleton-cssplit__logo" src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $logo_alt ) . '" loading="lazy" decoding="async" />';
										}
										?>
									</div>
								<?php endif; ?>

								<?php if ( '' !== $card_subtitle ) : ?>
									<p class="zskeleton-cssplit__card-subtitle"><?php echo esc_html( $card_subtitle ); ?></p>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( '' !== trim( wp_strip_all_tags( $quote_html ) ) ) : ?>
							<div class="zskeleton-cssplit__quote"><?php echo wp_kses_post( wpautop( $quote_html, false ) ); ?></div>
						<?php else : ?>
							<p class="zskeleton-cssplit__quote"><?php esc_html_e( 'Enter the testimonial quote in the block editor.', 'zskeleton' ); ?></p>
						<?php endif; ?>

						<div class="zskeleton-cssplit__sig">
							<?php if ( '' !== $person_name ) : ?>
								<p class="zskeleton-cssplit__name"><?php echo esc_html( $person_name ); ?></p>
							<?php endif; ?>
							<?php if ( '' !== $person_role ) : ?>
								<p class="zskeleton-cssplit__role"><?php echo esc_html( $person_role ); ?></p>
							<?php endif; ?>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<?php

return (string) ob_get_clean();

