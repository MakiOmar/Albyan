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
	<!-- Why us section -->
	<section class="seo-ar-section" id="why-us">
		<div class="<?php echo $zskeleton_seo_ar_container_class; ?>">
			<h2><?php echo esc_html( 'لماذا ستحب العمل معنا' ); ?></h2>
			<div class="seo-ar-feature-grid">
				<div class="seo-ar-feature-card">
					<img src="<?php echo esc_url($assets_base . '/Ranking.svg'); ?>" alt="" width="40" height="40" loading="lazy" decoding="async">
					<h3><?php echo esc_html( 'نتائج قوية وقابلة للقياس' ); ?></h3>
					<p><?php echo esc_html( 'نركز على نمو الزيارات والمبيعات بعقليات تحليلية واضحة، وليس مجرد وعود تسويقية.' ); ?></p>
				</div>
				<div class="seo-ar-feature-card">
					<img src="<?php echo esc_url($assets_base . '/Person-Search-1.svg'); ?>" alt="" width="40" height="40" loading="lazy" decoding="async">
					<h3><?php echo esc_html( 'فريق متخصص متعدد الخبرات' ); ?></h3>
					<p><?php echo esc_html( 'SEO، إعلانات مدفوعة، محتوى، وتجربة مستخدم ضمن فريق واحد متكامل.' ); ?></p>
				</div>
				<div class="seo-ar-feature-card">
					<img src="<?php echo esc_url($assets_base . '/Code.svg'); ?>" alt="" width="40" height="40" loading="lazy" decoding="async">
					<h3><?php echo esc_html( 'تنفيذ تقني سريع' ); ?></h3>
					<p><?php echo esc_html( 'نحول الاستراتيجية إلى تنفيذ فعلي على موقعك مع تحسينات مستمرة.' ); ?></p>
				</div>
			</div>
		</div>
	</section>
