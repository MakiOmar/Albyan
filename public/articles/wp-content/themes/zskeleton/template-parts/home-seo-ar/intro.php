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
	<section class="seo-ar-intro">
		<div class="<?php echo zskeleton_page_main_container_class( 'seo-ar-container', 'seo-ar-intro-grid', (int) get_queried_object_id() ); ?>">
			<div class="seo-ar-intro-text">
				<h2><?php echo esc_html( 'هل أنت مستعد لتنمية عملك على الإنترنت؟' ); ?></h2>
				<p><?php echo esc_html( 'نجمع بين SEO المبني على البيانات، والإعلانات المدفوعة، والتصميم الموجّه للتحويل—لكي تنمو إيراداتك مع معالم واضحة قابلة للقياس.' ); ?></p>
				<a class="seo-ar-btn seo-ar-btn-solid" href="#why-us"><?php echo esc_html( 'اعرف المزيد' ); ?></a>
			</div>
			<div class="seo-ar-intro-media">
				<img src="<?php echo esc_url($ref_base . '/performance-dashboard.jpg'); ?>" width="560" height="420" loading="lazy" decoding="async" alt="<?php echo esc_attr( 'لوحة تحليلات الأداء' ); ?>">
			</div>
		</div>
	</section>
