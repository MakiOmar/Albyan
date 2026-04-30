<?php
/**
 * Arabic SEO homepage hero: theme slider (if set) or static hero fallback.
 *
 * @package ZSkeleton_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$slider_id = (int) get_option( 'zskeleton_homepage_seo_ar_slider_id', 0 );
$use_slider = $slider_id > 0 && class_exists( 'ZSkeleton_Sliders' ) && ZSkeleton_Sliders::get_slider_post( $slider_id );

if ( $use_slider ) {
	if ( class_exists( 'ZSkeleton_Slider_Frontend' ) ) {
		ZSkeleton_Slider_Frontend::enqueue_assets();
	}
	?>
	<!-- Homepage hero: slider from Appearance → ZSkeleton Settings → Homepage. -->
	<div class="seo-ar-homepage__hero-slider">
		<?php echo do_shortcode( sprintf( '[zskeleton_slider id="%d"]', $slider_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shortcode HTML ?>
	</div>
	<?php
} else {
	get_template_part( 'template-parts/home-seo-ar/hero', 'static', $args );
}
