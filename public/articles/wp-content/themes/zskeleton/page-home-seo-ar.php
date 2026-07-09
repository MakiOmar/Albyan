<?php
/**
 * Template Name: الصفحة الرئيسية للسيو بالعربية
 *
 * Static Arabic SEO homepage; visible copy is explicit Arabic (not passed through gettext).
 * Sections live under template-parts/home-seo-ar/. The AI lead band can be edited as the block “SEO AR: AI lead (contact)” in the page editor; if absent, the static template part is used. Hero uses theme slider when set in ZSkeleton Settings.
 *
 * @package ZSkeleton_Theme
 */

get_header();

$zskeleton_seo_ar_container_class = zskeleton_page_main_container_class( 'seo-ar-container', '', (int) get_queried_object_id() );
$assets_base                      = get_template_directory_uri() . '/assets/seo-homepage';
$ref_base                         = $assets_base . '/reference';

$zskeleton_seo_ar_tpl_args = array(
	'container_class' => $zskeleton_seo_ar_container_class,
	'assets_base'     => $assets_base,
	'ref_base'        => $ref_base,
);
?>

<main class="seo-ar-homepage" dir="rtl" lang="ar">
	<?php
	get_template_part( 'template-parts/home-seo-ar/hero', null, $zskeleton_seo_ar_tpl_args );
	get_template_part( 'template-parts/home-seo-ar/ratings-strip', null, $zskeleton_seo_ar_tpl_args );
	get_template_part( 'template-parts/home-seo-ar/top-rated', null, $zskeleton_seo_ar_tpl_args );
	get_template_part( 'template-parts/home-seo-ar/reviews', null, $zskeleton_seo_ar_tpl_args );
	get_template_part( 'template-parts/home-seo-ar/intro', null, $zskeleton_seo_ar_tpl_args );
	get_template_part( 'template-parts/home-seo-ar/why-us', null, $zskeleton_seo_ar_tpl_args );
	$zskeleton_seo_ar_pid = (int) get_queried_object_id();
	if ( ! function_exists( 'zskeleton_seo_ar_try_render_ai_lead_block_from_page' ) || ! zskeleton_seo_ar_try_render_ai_lead_block_from_page( $zskeleton_seo_ar_pid, $zskeleton_seo_ar_tpl_args ) ) {
		get_template_part( 'template-parts/home-seo-ar/ai-lead', null, $zskeleton_seo_ar_tpl_args );
	}
	get_template_part( 'template-parts/home-seo-ar/services', null, $zskeleton_seo_ar_tpl_args );
	get_template_part( 'template-parts/home-seo-ar/final-cta', null, $zskeleton_seo_ar_tpl_args );
	?>
</main>

<?php
get_footer();
