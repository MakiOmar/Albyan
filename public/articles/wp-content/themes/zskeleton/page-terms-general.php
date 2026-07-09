<?php
/**
 * Template Name: Terms & Conditions (general)
 * Description: Full-width page: use the block editor for all terms copy (no theme boilerplate).
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
				'subtitle'  => '',
				'show_meta' => false,
			)
		);
		?>
		<div class="site-content legal-policy-page">
			<div class="<?php echo zskeleton_page_main_container_class( 'container', '', get_the_ID() ); ?>">
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
