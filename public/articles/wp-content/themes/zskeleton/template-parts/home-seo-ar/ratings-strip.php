<?php
/**
 * Arabic SEO homepage section (static markup; later dynamic).
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$zskeleton_seo_ar_container_class = isset( $args['container_class'] ) ? (string) $args['container_class'] : '';
$assets_base                        = isset( $args['assets_base'] ) ? (string) $args['assets_base'] : '';
$ref_base                           = isset( $args['ref_base'] ) ? (string) $args['ref_base'] : '';
?>
	<!-- Aggregate ratings strip (reference layout) -->
	<section class="seo-ar-ratings-strip" aria-label="<?php echo esc_attr( 'ملخص تقييمات العملاء' ); ?>">
		<div class="<?php echo $zskeleton_seo_ar_container_class; ?>">
			<div class="seo-ar-ratings-row">
				<div class="seo-ar-rating-col">
					<p class="seo-ar-rating-score">4.8 <span class="seo-ar-rating-star" aria-hidden="true">★</span></p>
					<p class="seo-ar-rating-platform"><?php echo esc_html( 'جوجل' ); ?></p>
					<p class="seo-ar-rating-count"><?php echo esc_html( 'أكثر من 230 مراجعة' ); ?></p>
				</div>
				<div class="seo-ar-rating-col">
					<p class="seo-ar-rating-score">4.9 <span class="seo-ar-rating-star" aria-hidden="true">★</span></p>
					<p class="seo-ar-rating-platform"><?php echo esc_html( 'كلتش' ); ?></p>
					<p class="seo-ar-rating-count"><?php echo esc_html( 'أكثر من 158 مراجعة' ); ?></p>
				</div>
				<div class="seo-ar-rating-col">
					<p class="seo-ar-rating-score">4.8 <span class="seo-ar-rating-star" aria-hidden="true">★</span></p>
					<p class="seo-ar-rating-platform"><?php echo esc_html( 'فيسبوك' ); ?></p>
					<p class="seo-ar-rating-count"><?php echo esc_html( 'أكثر من 90 مراجعة' ); ?></p>
				</div>
				<div class="seo-ar-rating-col">
					<p class="seo-ar-rating-score">4.7 <span class="seo-ar-rating-star" aria-hidden="true">★</span></p>
					<p class="seo-ar-rating-platform"><?php echo esc_html( 'إنديد' ); ?></p>
					<p class="seo-ar-rating-count"><?php echo esc_html( 'أكثر من 100 مراجعة' ); ?></p>
				</div>
			</div>
		</div>
	</section>
