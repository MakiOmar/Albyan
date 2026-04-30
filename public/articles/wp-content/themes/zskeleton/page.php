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
                    
                    <article id="post-<?php the_ID(); ?>" <?php post_class('formal-card page-content'); ?>>
                        
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
                        <section class="comments-section formal-card page-content" style="margin-top: 2rem;">
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
            <div class="<?php echo zskeleton_page_main_container_class( 'wide-container', 'front-page-membership-plans-wrap', get_the_ID() ); ?>" style="margin-top: 2.5rem;">
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

<style>
/* Page Template Specific Styles */

/* Page Layout - Main content gets at least 75% of space */
.page-layout {
    display: flex;
    gap: 2.5rem;
    align-items: flex-start;
}

.main-content {
    flex: 1;
    min-width: 0; /* Prevents flex item from overflowing */
}

.page-sidebar {
    flex: 0 0 300px; /* Fixed width sidebar */
    max-width: 25%; /* Ensures main content gets at least 75% */
}

.page-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    margin-top: 0;
}

.member-only {
    background: var(--accent-gold);
    color: var(--academic-navy);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.page-content-body {
    padding: 40px 30px;
    margin: 0;
}

.page-content-body h2 {
    color: var(--primary-blue);
    font-size: 1.75rem;
    margin: 40px 0 20px 0;
    font-weight: 600;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--border-light);
}

.page-content-body h2:first-child {
    margin-top: 0;
}

.page-content-body h3 {
    color: var(--academic-navy);
    font-size: 1.5rem;
    margin: 30px 0 15px 0;
    font-weight: 600;
}

.page-content-body h4 {
    color: var(--academic-navy);
    font-size: 1.25rem;
    margin: 25px 0 12px 0;
    font-weight: 600;
}

.page-content-body p {
    margin-bottom: 20px;
    line-height: 1.7;
    color: var(--professional-gray);
    font-size: 1rem;
}

.page-content-body ul,
.page-content-body ol {
    margin: 20px 0;
    padding-left: 30px;
}

.page-content-body li {
    margin-bottom: 12px;
    line-height: 1.6;
    color: var(--professional-gray);
    font-size: 1rem;
}

.page-content-body ol li {
    margin-bottom: 15px;
}

.page-content-body ol li strong {
    color: var(--primary-blue);
    font-weight: 600;
}

.page-content-body blockquote {
    border-left: 4px solid var(--primary-blue);
    padding: 25px 30px;
    margin: 30px 0;
    font-style: italic;
    color: var(--academic-navy);
    background: var(--background-light);
    border-radius: 0 8px 8px 0;
    font-size: 1.1rem;
    line-height: 1.6;
}

.page-content-body img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 25px 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.page-content-body table {
    width: 100%;
    border-collapse: collapse;
    margin: 30px 0;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.page-content-body th,
.page-content-body td {
    padding: 15px 20px;
    text-align: left;
    border-bottom: 1px solid var(--border-light);
}

.page-content-body th {
    background: var(--primary-blue);
    color: white;
    font-weight: 600;
    font-size: 1rem;
}

.page-content-body td {
    font-size: 0.95rem;
}

.page-content-body tr:hover {
    background: var(--background-light);
}

.page-links {
    margin: 40px 0;
    text-align: center;
    padding: 20px;
    background: var(--background-light);
    border-radius: 8px;
}

.page-links a {
    display: inline-block;
    padding: 10px 16px;
    margin: 0 8px 8px 0;
    background: white;
    color: var(--primary-blue);
    text-decoration: none;
    border-radius: 6px;
    border: 2px solid var(--border-light);
    transition: all 0.3s ease;
    font-weight: 500;
}

.page-links a:hover {
    background: var(--primary-blue);
    color: white;
    border-color: var(--primary-blue);
}

.member-access-notice {
    text-align: center;
    padding: 32px 28px;
    background: var(--background-light);
    border-radius: 16px;
    border: 1px solid rgba(1, 50, 95, 0.18);
    box-shadow: 0 10px 30px rgba(1, 50, 95, 0.08);
    margin: 30px 0;
    color: var(--primary-blue);
}

.member-access-notice .icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: rgba(1, 50, 95, 0.08);
    color: var(--primary-blue);
    font-size: 2.5rem;
    margin-bottom: 20px;
}

.member-access-notice h3 {
    color: var(--primary-blue);
    font-size: 1.6rem;
    margin-bottom: 16px;
    font-weight: 700;
}

.member-access-notice p {
    color: var(--primary-blue);
    margin: 0 auto 24px;
    max-width: 560px;
    font-size: 1rem;
    line-height: 1.6;
}

.member-access-actions {
    display: flex;
    gap: 20px;
    justify-content: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.free-content-notice {
    margin-top: 25px;
    padding-top: 25px;
    border-top: 1px solid var(--border-light);
}

.free-content-notice a {
    color: var(--primary-blue);
    text-decoration: none;
    font-weight: 500;
}

.free-content-notice a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .page-layout {
        gap: 2rem;
    }
    
    .page-sidebar {
        flex: 0 0 280px;
    }

    .page-content-body {
        padding: 30px 25px;
    }
    
    .page-header {
        padding: 30px 25px;
    }
}

@media (max-width: 768px) {
    .page-layout {
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .page-sidebar {
        flex: none;
        max-width: 100%;
        order: 2;
    }
    
    .main-content {
        order: 1;
    }
    
    .member-access-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .page-content-body {
        padding: 25px 20px;
    }
    
    .page-content-body h2 {
        font-size: 1.5rem;
    }
    
    .page-content-body h3 {
        font-size: 1.25rem;
    }
    
    .page-content-body h4 {
        font-size: 1.125rem;
    }
}

@media (max-width: 480px) {
    .member-access-notice {
        padding: 28px 20px;
    }
    
    .member-access-notice .icon {
        width: 60px;
        height: 60px;
        font-size: 2.1rem;
    }
    
    .page-content-body {
        padding: 20px 15px;
    }
}
</style>

<?php get_footer(); ?>
