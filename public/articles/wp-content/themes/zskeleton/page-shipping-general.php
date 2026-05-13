<?php
/**
 * Template Name: Shipping Policy (general)
 * Description: Boilerplate shipping and fulfillment information plus the page editor content.
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
				'subtitle'  => __( 'Delivery timelines, regions, and digital fulfillment.', 'zskeleton' ),
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
						'kind' => 'shipping',
					)
				);
				?>
				<?php if ( get_the_content() ) : ?>
					<article class="page-content legal-policy-page__editor">
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
