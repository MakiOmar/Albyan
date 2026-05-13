<?php
/**
 * Template Name: Blog listing
 *
 * Static page that lists blog posts (works whether or not this page is set as “Posts page” under Settings → Reading).
 * First page: featured strip (curated checkbox + sticky only—no newest fill), latest grid, trending/most-read, category term grid, lead CTA (Settings → Content).
 * For a block-built layout, use the “Blog listing (block editor)” template and ZSkeleton blog blocks.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

get_header();

$paged       = zskeleton_blog_hub_get_listing_paged();
$show_hub    = zskeleton_blog_hub_is_first_listing_page();
$blog_query  = zskeleton_blog_hub_main_posts_query();
$page_id = (int) get_queried_object_id();
?>

<?php get_template_part( 'template-parts/blog/listing', 'hero', array( 'page_id' => $page_id ) ); ?>

<main id="main" class="site-main" tabindex="-1">
	<div class="<?php echo esc_attr( zskeleton_page_main_container_class( 'wide-container', '', $page_id ) ); ?>">

		<?php if ( $show_hub ) : ?>
			<?php get_template_part( 'template-parts/blog/section', 'featured' ); ?>
		<?php endif; ?>

		<div class="<?php echo esc_attr( zskeleton_page_layout_class( '', $page_id ) ); ?>">
			<div class="main-content">

				<?php if ( $blog_query->have_posts() ) : ?>

					<?php if ( $show_hub ) : ?>
						<h2 class="blog-latest-heading"><?php echo esc_html( apply_filters( 'zskeleton_blog_hub_latest_title', __( 'Latest articles', 'zskeleton' ) ) ); ?></h2>
					<?php endif; ?>

					<div class="practices-grid">
						<?php
						while ( $blog_query->have_posts() ) :
							$blog_query->the_post();
							get_template_part( 'template-parts/blog/blog', 'card', array( 'post' => get_post() ) );
						endwhile;
						?>
					</div>

					<?php
					// <!-- Pagination for custom blog query on a static page -->
					echo wp_kses_post( zskeleton_blog_hub_pagination_html( $blog_query, $page_id, $paged ) );
					?>

				<?php else : ?>

					<div class="no-posts formal-card">
						<h2><?php esc_html_e( 'Nothing Found', 'zskeleton' ); ?></h2>
						<p><?php esc_html_e( 'It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'zskeleton' ); ?></p>
						<?php get_search_form(); ?>
					</div>

				<?php endif; ?>

				<?php
				// <!-- Hub sections (each template returns early when it has nothing to show). -->
				if ( $show_hub ) {
					get_template_part( 'template-parts/blog/section', 'trending' );
					get_template_part( 'template-parts/blog/section', 'category-blocks' );
					get_template_part( 'template-parts/blog/section', 'lead-gen' );
				}
				?>

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
wp_reset_postdata();
get_footer();
