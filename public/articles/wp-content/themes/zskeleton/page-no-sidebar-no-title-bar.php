<?php
/**
 * Template Name: No Sidebar / No Title Bar
 *
 * Full-width page template that omits the zskeleton page title bar.
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

	<?php
	// Check if this page requires membership using the access control system (plugin).
	$user_id    = get_current_user_id();
	$has_access = true;
	if ( class_exists( 'ZSkeleton_Access_Control' ) ) {
		$access_control = new ZSkeleton_Access_Control();
		$has_access     = $access_control->user_has_content_access( $user_id, get_the_ID() );
	}
	?>

	<main id="main" class="site-main" tabindex="-1">
		<?php do_action( 'zskeleton_before_main_content' ); ?>
		<div class="wide-container wide-container--no-sidebar">
			<div class="page-layout page-layout--no-sidebar">
				<div class="main-content">

					<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-content' ); ?>>

						<div class="page-content-body">
							<?php if ( $has_access ) : ?>
								<?php the_content(); ?>

								<?php
								wp_link_pages(
									array(
										'before' => '<div class="page-links">' . __( 'Pages:', 'zskeleton' ),
										'after'  => '</div>',
									)
								);
								?>

							<?php else : ?>
								<div class="member-access-notice">
									<div class="icon">🔒</div>
									<h3><?php esc_html_e( 'Member Access Required', 'zskeleton' ); ?></h3>
									<p><?php esc_html_e( 'This content is available exclusively to ZSkeleton members. To access our comprehensive library, reports, and professional resources, please consider joining our membership.', 'zskeleton' ); ?></p>

									<div class="member-access-actions">
										<?php if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
											<a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="btn btn-primary">
												<?php echo is_user_logged_in() ? esc_html__( 'Upgrade Membership', 'zskeleton' ) : esc_html__( 'Learn About Membership', 'zskeleton' ); ?>
											</a>
										<?php endif; ?>
										<?php if ( ! is_user_logged_in() ) : ?>
											<a href="<?php echo esc_url( home_url( '/login/' ) ); ?>" class="btn">
												<?php esc_html_e( 'Member Login', 'zskeleton' ); ?>
											</a>
										<?php endif; ?>
									</div>

									<div class="free-content-notice">
										<p><small><?php esc_html_e( 'New to ZSkeleton? Check out our', 'zskeleton' ); ?> <a href="<?php echo esc_url( home_url( '/blog' ) ); ?>"><?php esc_html_e( 'free articles', 'zskeleton' ); ?></a> <?php esc_html_e( 'to get started.', 'zskeleton' ); ?></small></p>
									</div>
								</div>
							<?php endif; ?>
						</div>

					</article>

					<?php if ( $has_access && post_type_supports( get_post_type(), 'comments' ) && ( comments_open() || get_comments_number() ) ) : ?>
						<section class="comments-section page-content comments-section-spaced">
							<?php comments_template(); ?>
						</section>
					<?php endif; ?>

				</div><!-- .main-content -->
			</div><!-- .page-layout -->
		</div><!-- .wide-container -->

		<?php if ( is_front_page() && function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
			<div class="wide-container wide-container--no-sidebar front-page-membership-plans-wrap">
				<?php
				get_template_part(
					'template-parts/membership-plans-pricing',
					null,
					array(
						'heading' => __( 'Choose Your Membership', 'zskeleton' ),
					)
				);
				?>
			</div>
		<?php endif; ?>

		<?php do_action( 'zskeleton_after_main_content' ); ?>
	</main><!-- #main -->

<?php endwhile; ?>

<?php get_footer(); ?>
