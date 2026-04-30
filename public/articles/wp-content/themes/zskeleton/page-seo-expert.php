<?php
/**
 * Template Name: SEO Expert
 *
 * Arabic-market SEO expert landing (configurable meta + repeaters + FAQs/Services by landing term).
 * Uses theme {@see get_header()} and {@see get_footer()} like other page templates; content sits inside
 * `#content.site-content` from the header. The `.seo-expert-end-strip` block lives inside `#seo-expert-contact`
 * (AI lead copy column), then related posts if any, then the global colophon/newsletter from `footer.php`.
 *
 * @package ZSkeleton_Theme
 */

get_header();

while ( have_posts() ) :
	the_post();

	$post_id = get_the_ID();
	$c       = zskeleton_page_main_container_class( 'seo-expert-container', '', $post_id );

	global $zskeleton_seo_expert_context;
	$zskeleton_seo_expert_context = array(
		'post_id'         => $post_id,
		'container_class' => $c,
	);
	?>
	<main id="primary" class="site-main seo-expert-homepage" dir="rtl" lang="ar" tabindex="-1">
		<?php do_action( 'zskeleton_before_main_content' ); ?>
		<?php get_template_part( 'template-parts/seo-expert/landing' ); ?>
	</main>
	<?php
endwhile;

get_footer();
