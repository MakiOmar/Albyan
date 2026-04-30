<?php
/**
 * Template Name: Blog listing (block editor)
 *
 * Same blog listing chrome (hero, wide container, optional sidebar) as the classic
 * “Blog listing” template, but the main column is built only from the page’s block
 * content. Insert ZSkeleton “Blog” blocks (or the “Blog listing (blocks)” pattern).
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$page_id = (int) get_the_ID();
	?>

	<?php get_template_part( 'template-parts/blog/listing', 'hero', array( 'page_id' => $page_id ) ); ?>

	<main id="main" class="site-main" tabindex="-1">
		<div class="<?php echo esc_attr( zskeleton_page_main_container_class( 'wide-container', '', $page_id ) ); ?>">
			<div class="<?php echo esc_attr( zskeleton_page_layout_class( '', $page_id ) ); ?>">
				<div class="main-content zskeleton-blog-blocks-main">

					<div class="entry-content zskeleton-blog-blocks-entry">
						<?php
						the_content();
						wp_link_pages(
							array(
								'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'zskeleton' ),
								'after'  => '</div>',
							)
						);
						?>
					</div>

				</div><!-- .main-content -->

				<?php if ( zskeleton_page_sidebar_enabled( $page_id ) ) : ?>
					<div class="page-sidebar">
						<?php get_sidebar(); ?>
					</div>
				<?php endif; ?>

			</div><!-- .page-layout -->
		</div><!-- .wide-container -->
	</main>

	<?php
endwhile;

get_footer();
