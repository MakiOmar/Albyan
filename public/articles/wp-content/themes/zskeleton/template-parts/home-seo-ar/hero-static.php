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
	<!-- Hero section (background: office photo from reference site assets) -->
	<section class="seo-ar-hero">
		<div class="seo-ar-hero-photo" style="background-image:url('<?php echo esc_url( $assets_base . '/hero-office-bg.jpg' ); ?>');" aria-hidden="true"></div>
		<div class="<?php echo $zskeleton_seo_ar_container_class; ?>">
			<p class="seo-ar-mini-badge"><?php echo esc_html( 'شركة سيو رائدة مدعومة بالذكاء الاصطناعي' ); ?></p>
			<h1><?php echo esc_html( 'تحتاج نمواً حقيقياً في الإيرادات؟' ); ?><br><?php echo esc_html( 'نحن فريقك الرقمي الجديد' ); ?></h1>
			<p class="seo-ar-subtitle">
				<?php echo esc_html( 'نقدم خدمات SEO والتسويق الرقمي وتصميم المواقع بأسلوب يعتمد على النتائج، مع تجربة شبيهة بالصفحة المرجعية التي زودتنا بها.' ); ?>
			</p>

			<div class="seo-ar-hero-stats">
				<div class="seo-ar-stat-card">
					<strong>19,478,369</strong>
					<span><?php echo esc_html( 'عملية تجارة إلكترونية' ); ?></span>
				</div>
				<div class="seo-ar-stat-card">
					<strong>5,621,177</strong>
					<span><?php echo esc_html( 'عميل محتمل مؤهل' ); ?></span>
				</div>
				<div class="seo-ar-stat-card">
					<strong>+800</strong>
					<span><?php echo esc_html( 'دراسة حالة' ); ?></span>
				</div>
			</div>

			<div class="seo-ar-actions">
				<a class="seo-ar-btn seo-ar-btn-primary" href="#contact-cta"><?php echo esc_html( 'احصل على استشارة مجانية' ); ?></a>
				<a class="seo-ar-btn seo-ar-btn-outline" href="#why-us"><?php echo esc_html( 'لماذا نحن؟' ); ?></a>
			</div>
		</div>
	</section>
