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
	<section class="seo-ar-reviews" aria-labelledby="seo-ar-trust-heading">
		<div class="<?php echo $zskeleton_seo_ar_container_class; ?>">
			<h2 id="seo-ar-trust-heading"><?php echo esc_html( 'موثوقون من مئات العملاء' ); ?></h2>
			<div class="seo-ar-review-logos">
				<div class="seo-ar-logo-cell">
					<img src="<?php echo esc_url($assets_base . '/google-rating-with-100-reviews.svg'); ?>" alt="" width="280" height="90" loading="lazy" decoding="async">
				</div>
				<div class="seo-ar-logo-cell">
					<img src="<?php echo esc_url($assets_base . '/facebook-rating-with-50-reviews.svg'); ?>" alt="" width="280" height="90" loading="lazy" decoding="async">
				</div>
				<div class="seo-ar-logo-cell">
					<img src="<?php echo esc_url($assets_base . '/clutch-rating-with-30-reviews.svg'); ?>" alt="" width="280" height="90" loading="lazy" decoding="async">
				</div>
				<div class="seo-ar-logo-cell">
					<img src="<?php echo esc_url($assets_base . '/2026-Google-Premier-Partner-Colored.svg'); ?>" alt="" width="280" height="90" loading="lazy" decoding="async">
				</div>
				<div class="seo-ar-logo-cell">
					<img src="<?php echo esc_url($ref_base . '/microsoft-case-study.webp'); ?>" alt="" width="200" height="120" loading="lazy" decoding="async">
				</div>
			</div>
			<!-- Grayscale client marks (saved page assets) -->
			<div class="seo-ar-brand-strip" aria-hidden="true">
				<img src="<?php echo esc_url($ref_base . '/roku-logo-2-1.png'); ?>" alt="">
				<img src="<?php echo esc_url($ref_base . '/spiceology-logo-1.png'); ?>" alt="">
				<img src="<?php echo esc_url($ref_base . '/p3-america-logo-1.png'); ?>" alt="">
				<img src="<?php echo esc_url($ref_base . '/leablack-beauty-logo-1.png'); ?>" alt="">
			</div>
		</div>
	</section>
