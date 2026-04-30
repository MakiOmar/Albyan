<?php
/**
 * Template Name: Privacy Policy (general)
 * Description: Boilerplate privacy copy plus the page editor content. For SEO-rich policy pages use the SEO Agency Kit managed template.
 *
 * @package ZSkeleton_Theme
 */

get_header();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		zskeleton_the_page_title_bar(
			array(
				'post_id'   => get_the_ID(),
				'title'     => get_the_title(),
				'subtitle'  => __( 'How we handle information on this site.', 'zskeleton' ),
				'show_meta' => false,
			)
		);
		?>
		<div class="site-content legal-policy-page">
			<div class="<?php echo zskeleton_page_main_container_class( 'container', '', get_the_ID() ); ?>">
				<?php
				get_template_part(
					'template-parts/legal-policy-general',
					null,
					array(
						'kind' => 'privacy',
					)
				);
				?>
				<?php if ( get_the_content() ) : ?>
					<article class="formal-card page-content legal-policy-page__editor">
						<div class="entry-content academic-content">
							<?php the_content(); ?>
						</div>
					</article>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

get_footer();
