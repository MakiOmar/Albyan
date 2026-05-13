<?php
/**
 * Dynamic render: testimonials image slider section.
 *
 * @package ZSkeleton_Theme
 * @var array         $attributes Block attributes.
 * @var WP_Block|null $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'zskeleton_tis_sanitize_hex_color_attr' ) ) {
	/**
	 * Allow only 6-digit hex for inline CSS variables.
	 *
	 * @param mixed $val Raw attribute.
	 * @return string Normalized `#RRGGBB` or empty string.
	 */
	function zskeleton_tis_sanitize_hex_color_attr( $val ): string {
		if ( ! is_string( $val ) ) {
			return '';
		}
		$val = strtoupper( trim( $val ) );
		return preg_match( '/^#[0-9A-F]{6}$/', $val ) ? $val : '';
	}
}

if ( function_exists( 'wp_enqueue_style' ) ) {
	wp_enqueue_style( 'zskeleton-testimonials-image-slider' );
}
if ( function_exists( 'wp_enqueue_script' ) ) {
	wp_enqueue_script( 'zskeleton-testimonials-image-slider-view' );
}

$badge_title = isset( $attributes['badgeTitle'] ) ? (string) $attributes['badgeTitle'] : '';
$title       = isset( $attributes['title'] ) ? (string) $attributes['title'] : '';
$paragraph   = isset( $attributes['paragraph'] ) ? (string) $attributes['paragraph'] : '';
$cta_text    = isset( $attributes['ctaText'] ) ? (string) $attributes['ctaText'] : '';
$btn_label   = isset( $attributes['buttonLabel'] ) ? (string) $attributes['buttonLabel'] : '';
$btn_url     = isset( $attributes['buttonUrl'] ) ? (string) $attributes['buttonUrl'] : '';
$btn_target  = isset( $attributes['buttonTarget'] ) && '_blank' === $attributes['buttonTarget'] ? '_blank' : '_self';

$raw_slides = isset( $attributes['slides'] ) && is_array( $attributes['slides'] ) ? $attributes['slides'] : array();
$slides     = array();
foreach ( $raw_slides as $row ) {
	if ( ! is_array( $row ) ) {
		continue;
	}
	$id = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
	if ( $id < 1 ) {
		continue;
	}
	$slides[] = array(
		'id'      => $id,
		'alt'     => isset( $row['alt'] ) ? sanitize_text_field( (string) $row['alt'] ) : '',
		'caption' => isset( $row['caption'] ) ? sanitize_text_field( (string) $row['caption'] ) : '',
	);
}

$slides_per_view = 1;
if ( isset( $attributes['slidesPerView'] ) && is_numeric( $attributes['slidesPerView'] ) ) {
	$slides_per_view = (int) $attributes['slidesPerView'];
}
$slides_per_view = min( 6, max( 1, $slides_per_view ) );

$slides_per_view_mobile = $slides_per_view;
if ( isset( $attributes['slidesPerViewMobile'] ) && is_numeric( $attributes['slidesPerViewMobile'] ) ) {
	$slides_per_view_mobile = (int) $attributes['slidesPerViewMobile'];
}
$slides_per_view_mobile = min( 6, max( 1, $slides_per_view_mobile ) );

$carousel_style = sprintf(
	'--zskeleton-tis-per-view:%1$d;--zskeleton-tis-per-view-mobile:%2$d;',
	$slides_per_view,
	$slides_per_view_mobile
);
$show_carousel_dots = count( $slides ) > 1 && count( $slides ) > min( $slides_per_view, $slides_per_view_mobile );

$autoplay_enabled = ! empty( $attributes['autoplay'] );
$autoplay_sec     = 5;
if ( isset( $attributes['autoplayIntervalSec'] ) && is_numeric( $attributes['autoplayIntervalSec'] ) ) {
	$autoplay_sec = (int) $attributes['autoplayIntervalSec'];
}
$autoplay_sec           = min( 60, max( 2, $autoplay_sec ) );
$autoplay_interval_ms  = $autoplay_sec * 1000;
$autoplay_data_attr    = $autoplay_enabled ? '1' : '0';

$td = isset( $attributes['textDirection'] ) ? (string) $attributes['textDirection'] : 'auto';

$heading_id = '' !== $title ? wp_unique_id( 'zskeleton-tis-heading-' ) : '';

$color_map = array(
	'colorSectionBg'      => '--zskeleton-tis-section-bg',
	'colorBadgeBg'        => '--zskeleton-tis-badge-bg',
	'colorBadgeText'      => '--zskeleton-tis-badge-text',
	'colorTitleText'      => '--zskeleton-tis-heading',
	'colorIntroText'      => '--zskeleton-tis-intro',
	'colorCardBg'         => '--zskeleton-tis-card-bg',
	'colorFigcaptionText' => '--zskeleton-tis-figcaption',
	'colorDotInactive'    => '--zskeleton-tis-dot',
	'colorDotActive'      => '--zskeleton-tis-dot-active',
	'colorCtaText'        => '--zskeleton-tis-cta',
	'colorButtonBg'       => '--zskeleton-tis-btn-bg',
	'colorButtonText'     => '--zskeleton-tis-btn-text',
);
$style_parts = array();
foreach ( $color_map as $attr_key => $css_var ) {
	$hex = isset( $attributes[ $attr_key ] ) ? zskeleton_tis_sanitize_hex_color_attr( $attributes[ $attr_key ] ) : '';
	if ( '' !== $hex ) {
		$style_parts[] = $css_var . ':' . $hex;
	}
}
$inline_style = count( $style_parts ) > 0 ? implode( ';', $style_parts ) : '';

$wrapper_attrs = array(
	'class' => 'zskeleton-tis',
);
if ( '' !== $inline_style ) {
	$wrapper_attrs['style'] = $inline_style;
}
if ( 'rtl' === $td || 'ltr' === $td ) {
	$wrapper_attrs['dir'] = $td;
}
if ( '' !== $title && '' !== $heading_id ) {
	$wrapper_attrs['aria-labelledby'] = $heading_id;
} else {
	$wrapper_attrs['aria-label'] = __( 'Customer testimonials section', 'zskeleton' );
}

$wrapper = get_block_wrapper_attributes(
	$wrapper_attrs,
	'',
	isset( $block ) ? $block : null
);

ob_start();
?>
<section <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — wrapper is escaped by get_block_wrapper_attributes. ?>>
	<?php if ( '' !== $badge_title || '' !== $title || '' !== $paragraph ) : ?>
		<!-- Header -->
		<header class="zskeleton-tis__header">
			<?php if ( '' !== $badge_title ) : ?>
				<p class="zskeleton-tis__badge"><?php echo esc_html( $badge_title ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $title && '' !== $heading_id ) : ?>
				<?php
				$t_heading = '';
				if ( function_exists( 'zskeleton_render_block_heading_title_row' ) ) {
					$t_heading = zskeleton_render_block_heading_title_row(
						array(
							'title_inner_html' => esc_html( $title ),
							'heading_tag'      => 'h2',
							'attributes'       => is_array( $attributes ) ? $attributes : array(),
							'title_class'      => 'zskeleton-tis__title',
							'align'            => 'center',
							'heading_id'       => $heading_id,
						)
					);
				}
				if ( '' !== $t_heading ) {
					echo $t_heading; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup from zskeleton_render_block_heading_title_row().
				} else {
					?>
				<h2 class="zskeleton-tis__title" id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $title ); ?></h2>
					<?php
				}
				?>
			<?php endif; ?>
			<?php if ( '' !== $paragraph ) : ?>
				<p class="zskeleton-tis__intro"><?php echo esc_html( $paragraph ); ?></p>
			<?php endif; ?>
		</header>
	<?php endif; ?>

	<?php if ( count( $slides ) > 0 ) : ?>
		<!-- Image carousel -->
		<div
			class="zskeleton-tis__carousel"
			data-zskeleton-tis-carousel
			data-slides-per-view="<?php echo esc_attr( (string) (int) $slides_per_view ); ?>"
			data-slides-per-view-mobile="<?php echo esc_attr( (string) (int) $slides_per_view_mobile ); ?>"
			data-autoplay="<?php echo esc_attr( $autoplay_data_attr ); ?>"
			data-autoplay-interval-ms="<?php echo esc_attr( (string) (int) $autoplay_interval_ms ); ?>"
			style="<?php echo esc_attr( $carousel_style ); ?>"
		>
			<div class="zskeleton-tis__viewport" data-zskeleton-tis-viewport tabindex="0" role="region" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Testimonial images', 'zskeleton' ); ?>">
				<div class="zskeleton-tis__track" data-zskeleton-tis-track>
					<?php foreach ( $slides as $index => $slide ) : ?>
						<?php
						$img_id = (int) $slide['id'];
						$alt    = '' !== $slide['alt'] ? $slide['alt'] : wp_strip_all_tags( get_post_field( 'post_title', $img_id ) );
						$cap    = $slide['caption'];
						?>
						<figure class="zskeleton-tis__slide" data-zskeleton-tis-slide id="<?php echo esc_attr( 'zskeleton-tis-slide-' . ( $index + 1 ) ); ?>">
							<?php
							echo wp_get_attachment_image(
								$img_id,
								'large',
								false,
								array(
									'class'    => 'zskeleton-tis__img',
									'alt'      => $alt,
									'loading'  => 0 === $index ? 'eager' : 'lazy',
									'decoding' => 'async',
								)
							);
							?>
							<?php if ( '' !== $cap ) : ?>
								<figcaption class="zskeleton-tis__figcaption"><?php echo esc_html( $cap ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
			<?php if ( $show_carousel_dots ) : ?>
				<div class="zskeleton-tis__dots" role="tablist" aria-label="<?php esc_attr_e( 'Slide navigation', 'zskeleton' ); ?>">
					<?php foreach ( $slides as $index => $_s ) : ?>
						<button
							type="button"
							class="zskeleton-tis__dot"
							role="tab"
							data-zskeleton-tis-dot
							data-index="<?php echo esc_attr( (string) $index ); ?>"
							aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
							aria-controls="<?php echo esc_attr( 'zskeleton-tis-slide-' . ( $index + 1 ) ); ?>"
							aria-label="<?php echo esc_attr( sprintf( /* translators: 1: slide number, 2: total slides */ __( 'Go to slide %1$d of %2$d', 'zskeleton' ), $index + 1, count( $slides ) ) ); ?>"
						></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php elseif ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) : ?>
		<p class="zskeleton-tis__empty"><?php esc_html_e( 'Add images in the block sidebar to build the carousel.', 'zskeleton' ); ?></p>
	<?php endif; ?>

	<?php if ( '' !== $cta_text || ( '' !== $btn_label && '' !== $btn_url ) ) : ?>
		<!-- CTA -->
		<footer class="zskeleton-tis__footer">
			<?php if ( '' !== $cta_text ) : ?>
				<p class="zskeleton-tis__cta-text"><?php echo esc_html( $cta_text ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $btn_label && '' !== $btn_url ) : ?>
				<?php
				$rel_parts = array();
				if ( ! empty( $attributes['buttonRelNofollow'] ) ) {
					$rel_parts[] = 'nofollow';
				}
				if ( ! empty( $attributes['buttonRelSponsored'] ) ) {
					$rel_parts[] = 'sponsored';
				}
				if ( '_blank' === $btn_target ) {
					$rel_parts[] = 'noopener';
					$rel_parts[] = 'noreferrer';
				}
				$rel = implode( ' ', array_unique( array_filter( $rel_parts ) ) );
				$btn_aria = isset( $attributes['buttonAriaLabel'] ) ? sanitize_text_field( (string) $attributes['buttonAriaLabel'] ) : '';
				$btn_title = isset( $attributes['buttonTitleAttr'] ) ? sanitize_text_field( (string) $attributes['buttonTitleAttr'] ) : '';
				?>
				<p class="zskeleton-tis__cta-btn-wrap">
					<a
						class="zskeleton-tis__btn"
						href="<?php echo esc_url( $btn_url ); ?>"
						<?php echo '_blank' === $btn_target ? ' target="_blank"' : ''; ?>
						<?php echo '' !== $rel ? ' rel="' . esc_attr( $rel ) . '"' : ''; ?>
						<?php echo '' !== $btn_aria ? ' aria-label="' . esc_attr( $btn_aria ) . '"' : ''; ?>
						<?php echo '' !== $btn_title ? ' title="' . esc_attr( $btn_title ) . '"' : ''; ?>
					><?php echo esc_html( $btn_label ); ?></a>
				</p>
			<?php endif; ?>
		</footer>
	<?php endif; ?>
</section>
<?php
$html = (string) ob_get_clean();

// JSON-LD: ItemList of ImageObject entries (fits image-based testimonial galleries).
if ( count( $slides ) > 0 ) {
	$list_items = array();
	foreach ( $slides as $i => $slide ) {
		$url = wp_get_attachment_image_url( (int) $slide['id'], 'full' );
		if ( ! $url ) {
			continue;
		}
		$item = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'item'     => array(
				'@type' => 'ImageObject',
				'url'   => $url,
			),
		);
		if ( '' !== $slide['caption'] ) {
			$item['item']['caption'] = $slide['caption'];
		}
		if ( '' !== $slide['alt'] ) {
			$item['item']['name'] = $slide['alt'];
		}
		$list_items[] = $item;
	}
	if ( count( $list_items ) > 0 ) {
		$schema = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'ItemList',
			'name'            => '' !== $title ? $title : __( 'Testimonials', 'zskeleton' ),
			'description'     => '' !== $paragraph ? wp_strip_all_tags( $paragraph ) : '',
			'numberOfItems'   => count( $list_items ),
			'itemListElement' => $list_items,
		);
		$html .= sprintf(
			'<script type="application/ld+json">%s</script>',
			wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		);
	}
}

echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — built from escaped fragments + JSON-LD.
