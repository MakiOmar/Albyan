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
	<section class="seo-ar-final-cta" id="contact-cta">
		<div class="<?php echo $zskeleton_seo_ar_container_class; ?>">
			<h2><?php echo esc_html( 'جاهز لنقل مشروعك إلى المستوى التالي؟' ); ?></h2>
			<p><?php echo esc_html( 'ابدأ الآن بجلسة استراتيجية مجانية لتحليل وضعك الحالي وتحديد أسرع فرص النمو.' ); ?></p>
			<a class="seo-ar-btn seo-ar-btn-primary" href="#"><?php echo esc_html( 'ابدأ الآن' ); ?></a>
		</div>
	</section>
