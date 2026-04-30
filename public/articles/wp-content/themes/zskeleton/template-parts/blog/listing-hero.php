<?php
/**
 * Shared blog / archive hero: same branches as index.php, navy styling class, search below copy.
 *
 * Loaded via get_template_part( 'template-parts/blog/listing', 'hero', array( 'page_id' => int ) ).
 * Pass `page_id` for static “Blog listing” pages so the editor title and excerpt are used; use 0 on index.
 *
 * @package ZSkeleton_Theme
 * @var array $args Optional. @type int $page_id Static page ID for blog listing templates (0 = omit).
 */

defined( 'ABSPATH' ) || exit;

$args    = isset( $args ) && is_array( $args ) ? $args : array();
$page_id = isset( $args['page_id'] ) ? absint( $args['page_id'] ) : 0;

$crumbs = array(
	array(
		'label' => __( 'Home', 'zskeleton' ),
		'url'   => home_url( '/' ),
	),
);

if ( is_home() && ! is_front_page() ) {
	$posts_page_id = function_exists( 'zskeleton_get_page_for_posts_id' ) ? (int) zskeleton_get_page_for_posts_id() : (int) get_option( 'page_for_posts', 0 );
	if ( $posts_page_id > 0 ) {
		$crumbs = function_exists( 'zskeleton_get_page_breadcrumb_items' ) ? zskeleton_get_page_breadcrumb_items( $posts_page_id ) : $crumbs;
	} else {
		$crumbs[] = array(
			'label'   => single_post_title( '', false ),
			'url'     => '',
			'current' => true,
		);
	}
} elseif ( is_search() ) {
	$crumbs[] = array(
		'label'   => sprintf(
			/* translators: %s: search query */
			__( 'Search results for: %s', 'zskeleton' ),
			get_search_query()
		),
		'url'     => '',
		'current' => true,
	);
} elseif ( is_archive() ) {
	$crumbs[] = array(
		'label'   => wp_strip_all_tags( get_the_archive_title() ),
		'url'     => '',
		'current' => true,
	);
} elseif ( $page_id > 0 && function_exists( 'zskeleton_get_page_breadcrumb_items' ) ) {
	$crumbs = zskeleton_get_page_breadcrumb_items( $page_id );
}
?>

<!-- Page hero: same logic as index.php + navy banner + search (assets/css/blog-listing-hero.css). -->
<section class="page-hero page-hero--zskeleton-blog" aria-labelledby="zskeleton-blog-hero-heading">
	<div class="hero-content">
		<?php if ( ! empty( $crumbs ) ) : ?>
			<div class="hero-breadcrumbs">
				<nav class="zskeleton-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumbs', 'zskeleton' ); ?>">
					<ol class="zskeleton-breadcrumbs__list">
						<?php foreach ( $crumbs as $crumb ) : ?>
							<?php
							$label = isset( $crumb['label'] ) ? (string) $crumb['label'] : '';
							$url   = isset( $crumb['url'] ) ? (string) $crumb['url'] : '';
							if ( '' === trim( $label ) ) {
								continue;
							}
							?>
							<li class="zskeleton-breadcrumbs__item">
								<?php if ( '' !== $url ) : ?>
									<a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
								<?php else : ?>
									<span aria-current="page"><?php echo esc_html( $label ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ol>
				</nav>
			</div>
		<?php endif; ?>
		<?php if ( is_home() && ! is_front_page() ) : ?>
			<h1 class="hero-title" id="zskeleton-blog-hero-heading"><?php single_post_title(); ?></h1>
		<?php elseif ( is_search() ) : ?>
			<h1 class="hero-title" id="zskeleton-blog-hero-heading">
				<?php
				printf(
					/* translators: %s: Search keywords entered by the visitor. */
					esc_html__( 'Search Results for: %s', 'zskeleton' ),
					esc_html( get_search_query() )
				);
				?>
			</h1>
		<?php elseif ( is_archive() ) : ?>
			<h1 class="hero-title" id="zskeleton-blog-hero-heading"><?php the_archive_title(); ?></h1>
			<?php if ( get_the_archive_description() ) : ?>
				<p class="hero-description"><?php echo wp_kses_post( get_the_archive_description() ); ?></p>
			<?php endif; ?>
		<?php elseif ( $page_id > 0 ) : ?>
			<?php
			$hero_post = get_post( $page_id );
			if ( $hero_post instanceof WP_Post && 'page' === $hero_post->post_type && 'publish' === get_post_status( $hero_post ) ) :
				$hero_title        = get_the_title( $hero_post );
				$hero_excerpt      = trim( (string) get_post_field( 'post_excerpt', $page_id ) );
				$hero_description  = '' !== $hero_excerpt
					? $hero_excerpt
					: __( 'Read the latest insights, practical guidance, and updates from our community.', 'zskeleton' );
				?>
				<h1 class="hero-title" id="zskeleton-blog-hero-heading"><?php echo esc_html( $hero_title ); ?></h1>
				<p class="hero-description"><?php echo esc_html( $hero_description ); ?></p>
			<?php else : ?>
				<h1 class="hero-title" id="zskeleton-blog-hero-heading"><?php esc_html_e( 'News & Insights', 'zskeleton' ); ?></h1>
				<p class="hero-description"><?php esc_html_e( 'Read the latest insights, practical guidance, and updates from our community.', 'zskeleton' ); ?></p>
			<?php endif; ?>
		<?php else : ?>
			<h1 class="hero-title" id="zskeleton-blog-hero-heading"><?php esc_html_e( 'News & Insights', 'zskeleton' ); ?></h1>
			<p class="hero-description"><?php esc_html_e( 'Read the latest insights, practical guidance, and updates from our community.', 'zskeleton' ); ?></p>
		<?php endif; ?>

		<div class="zskeleton-blog-hero-search" role="search" aria-label="<?php esc_attr_e( 'Search blog posts', 'zskeleton' ); ?>">
			<?php get_search_form(); ?>
		</div>
	</div>
</section>
