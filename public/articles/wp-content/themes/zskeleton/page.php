<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
    
    <?php
    // Check if this page requires membership using the access control system (plugin).
    $user_id      = get_current_user_id();
    $has_access   = true;
    if ( class_exists( 'ZSkeleton_Access_Control' ) ) {
        $access_control = new ZSkeleton_Access_Control();
        $has_access     = $access_control->user_has_content_access( $user_id, get_the_ID() );
    }
	$show_sidebar = zskeleton_page_sidebar_enabled( get_the_ID() );
    ?>
    
    <?php
    $zskeleton_page_subtitle = '';
    if (has_excerpt()) {
        $zskeleton_page_subtitle = get_the_excerpt();
    }
    zskeleton_the_page_title_bar(
        array(
            'post_id'            => get_the_ID(),
            'subtitle'           => $zskeleton_page_subtitle,
            'show_meta'          => true,
            'member_only_badge'  => !$has_access && !current_user_can('administrator'),
        )
    );
    ?>

    <!-- Main Content with Sidebar -->
    <main id="main" class="site-main" tabindex="-1">
        <?php do_action('zskeleton_before_main_content'); ?>
        <div class="<?php echo zskeleton_page_main_container_class( 'wide-container', '', get_the_ID() ); ?>">
            <div class="<?php echo zskeleton_page_layout_class( '', get_the_ID() ); ?>">
                <div class="main-content">
                    
                    <article id="post-<?php the_ID(); ?>" <?php post_class('page-content'); ?>>
                        
                        <!-- Page Content -->
                        <div class="page-content-body">
                            <?php if ($has_access) : ?>
                                <?php the_content(); ?>
                                
                                <?php
                                // Page links for paginated content
                                wp_link_pages(array(
                                    'before' => '<div class="page-links">' . __('Pages:', 'zskeleton'),
                                    'after'  => '</div>',
                                ));
                                ?>
                                
                            <?php else : ?>
                                <!-- Member Access Notice -->
                                <div class="member-access-notice">
                                    <div class="icon">🔒</div>
                                    <h3><?php _e('Member Access Required', 'zskeleton'); ?></h3>
                                    <p><?php _e('This content is available exclusively to ZSkeleton members. To access our comprehensive library, reports, and professional resources, please consider joining our membership.', 'zskeleton'); ?></p>
                                    
                                    <div class="member-access-actions">
                                        <?php if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
                                        <a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="btn btn-primary">
                                            <?php echo is_user_logged_in() ? __('Upgrade Membership', 'zskeleton') : __('Learn About Membership', 'zskeleton'); ?>
                                        </a>
                                        <?php endif; ?>
                                        <?php if (!is_user_logged_in()): ?>
                                        <a href="<?php echo esc_url(home_url('/login/')); ?>" class="btn">
                                            <?php _e('Member Login', 'zskeleton'); ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="free-content-notice">
                                        <p><small><?php _e('New to ZSkeleton? Check out our', 'zskeleton'); ?> <a href="<?php echo esc_url(home_url('/blog')); ?>"><?php _e('free articles', 'zskeleton'); ?></a> <?php _e('to get started.', 'zskeleton'); ?></small></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                    </article>

                    <?php if ($has_access && post_type_supports(get_post_type(), 'comments') && (comments_open() || get_comments_number())) : ?>
                        <section class="comments-section page-content comments-section-spaced">
                            <?php comments_template(); ?>
                        </section>
                    <?php endif; ?>
                    
                </div><!-- .main-content -->
                
                <?php if ( $show_sidebar ) : ?>
					<div class="page-sidebar">
						<?php get_sidebar(); ?>
					</div>
				<?php endif; ?>
                
            </div><!-- .page-layout -->
        </div><!-- .wide-container -->

        <?php if ( is_front_page() && function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
            <!-- Membership plans (same template as page-memberships.php) -->
            <div class="<?php echo zskeleton_page_main_container_class( 'wide-container', 'front-page-membership-plans-wrap', get_the_ID() ); ?>">
                <?php
                get_template_part(
                    'template-parts/membership-plans-pricing',
                    null,
                    array(
                        'heading' => __('Choose Your Membership', 'zskeleton'),
                    )
                );
                ?>
            </div>
        <?php endif; ?>

        <?php do_action('zskeleton_after_main_content'); ?>
    </main><!-- #main -->
    
<?php endwhile; ?>

<?php get_footer(); ?>
