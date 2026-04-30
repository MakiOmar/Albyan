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
	<section class="seo-ar-section seo-ar-section-alt">
		<div class="<?php echo $zskeleton_seo_ar_container_class; ?>">
			<h2><?php echo esc_html( 'خدماتنا الأساسية' ); ?></h2>
			<div class="seo-ar-services-grid">
				<article class="seo-ar-service-item">
					<img src="<?php echo esc_url($assets_base . '/Analytics-1.svg'); ?>" alt="" width="40" height="40" loading="lazy" decoding="async">
					<h3><?php echo esc_html( 'SEO متقدم' ); ?></h3>
					<p><?php echo esc_html( 'تحسين بنية الموقع والمحتوى لرفع الظهور في نتائج البحث التقليدي ونتائج الذكاء الاصطناعي.' ); ?></p>
				</article>
				<article class="seo-ar-service-item">
					<img src="<?php echo esc_url($assets_base . '/Goal.svg'); ?>" alt="" width="40" height="40" loading="lazy" decoding="async">
					<h3><?php echo esc_html( 'إعلانات الأداء' ); ?></h3>
					<p><?php echo esc_html( 'حملات مدفوعة موجهة بدقة لرفع التحويلات وخفض تكلفة الحصول على العميل.' ); ?></p>
				</article>
				<article class="seo-ar-service-item">
					<img src="<?php echo esc_url($assets_base . '/Code-Kit.svg'); ?>" alt="" width="40" height="40" loading="lazy" decoding="async">
					<h3><?php echo esc_html( 'تصميم وتطوير مواقع' ); ?></h3>
					<p><?php echo esc_html( 'واجهات سريعة وواضحة تعزز ثقة الزائر وتزيد فرص التحويل.' ); ?></p>
				</article>
				<article class="seo-ar-service-item">
					<img src="<?php echo esc_url($assets_base . '/Checklist-1.svg'); ?>" alt="" width="40" height="40" loading="lazy" decoding="async">
					<h3><?php echo esc_html( 'خطة تنفيذ شهرية' ); ?></h3>
					<p><?php echo esc_html( 'خارطة طريق واضحة مع تقارير دورية ومؤشرات أداء يمكن تتبعها.' ); ?></p>
				</article>
			</div>
		</div>
	</section>
