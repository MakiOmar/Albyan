<?php
/**
 * Template Name: Landing Page
 * Template Post Type: page
 * Description: Full-width canvas with no site header, footer, or sidebar.
 *
 * @package ZSkeleton_Theme
 */

defined( 'ABSPATH' ) || exit;

$user_id    = get_current_user_id();
$has_access = true;
if ( class_exists( 'ZSkeleton_Access_Control' ) ) {
	$access_control = new ZSkeleton_Access_Control();
	$has_access     = $access_control->user_has_content_access( $user_id, get_queried_object_id() );
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'zs-landing-page' ); ?>>
<?php wp_body_open(); ?>

<main id="primary" class="zs-landing-page__main" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'zs-landing-page__content' ); ?>>
			<?php if ( $has_access ) : ?>
				<?php the_content(); ?>
			<?php else : ?>
				<div class="zs-landing-page__access-notice member-access-notice">
					<div class="icon" aria-hidden="true">&#128274;</div>
					<h1><?php esc_html_e( 'Member Access Required', 'zskeleton' ); ?></h1>
					<p><?php echo esc_html( zskeleton_sprintf_site_name( __( 'This content is available exclusively to %s members.', 'zskeleton' ) ) ); ?></p>
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
				</div>
			<?php endif; ?>
		</article>
		<?php
	endwhile;
	?>
</main>

<?php wp_footer(); ?>
</body>
</html>
