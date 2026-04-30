<?php
/**
 * The sidebar containing the main widget area
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

// Always show sidebar content even if no widgets are active
$zskeleton_sidebar_mship = function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled();
?>

<aside id="secondary" class="widget-area sidebar">
    
    <?php if ( $zskeleton_sidebar_mship ) : ?>
    <!-- Membership Status Widget -->
    <?php if (is_user_logged_in()) : ?>
        <?php
        $user_id          = get_current_user_id();
        $has_membership   = class_exists( 'ZSkeleton_User_Profile_Fields' ) && ZSkeleton_User_Profile_Fields::user_has_active_membership( $user_id );
        $membership_type  = class_exists( 'ZSkeleton_User_Profile_Fields' ) ? ZSkeleton_User_Profile_Fields::get_user_membership_type( $user_id ) : 'none';
        ?>
        
        <section class="widget membership-widget formal-card">
            <h3 class="widget-title"><?php _e('Membership Status', 'zskeleton'); ?></h3>
            
            <?php if ($has_membership) : ?>
                <div class="membership-active">
                    <div class="membership-badge">
                        <span class="member-type"><?php echo esc_html(ucfirst($membership_type)); ?> Member</span>
                        <span class="status-active">Active</span>
                    </div>
                    
                    <div class="quick-links">
                        <a href="<?php echo esc_url(get_permalink(get_page_by_path('profile'))); ?>" class="btn btn-secondary btn-small">
                            <?php _e('View Profile', 'zskeleton'); ?>
                        </a>
                    </div>
                </div>
            <?php else : ?>
                <div class="membership-inactive">
                    <p><?php _e('Unlock exclusive ZSkeleton content with a membership.', 'zskeleton'); ?></p>
                    <a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="btn btn-primary">
                        <?php _e('Join Now', 'zskeleton'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </section>
        
    <?php else : ?>
        
        <!-- Join Membership Widget -->
        <section class="widget join-widget formal-card">
            <h3 class="widget-title"><?php _e('Join ZSkeleton', 'zskeleton'); ?></h3>
            <p><?php _e('Become a member of ZSkeleton and access exclusive content and resources.', 'zskeleton'); ?></p>
            
            <div class="join-actions">
                <a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="btn btn-primary">
                    <?php _e('Learn More', 'zskeleton'); ?>
                </a>
                <a href="<?php echo home_url('/login/'); ?>" class="btn btn-secondary">
                    <?php _e('Member Login', 'zskeleton'); ?>
                </a>
            </div>
        </section>
        
    <?php endif; ?>
    <?php endif; ?>
    
    <!-- Quick Search Widget -->
    <section class="widget search-widget formal-card">
        <h3 class="widget-title"><?php _e('Search Resources', 'zskeleton'); ?></h3>
        <?php get_search_form(); ?>
        
        <div class="search-categories">
            <h4><?php _e('Browse by Page', 'zskeleton'); ?></h4>
            <ul class="category-links">
                <li><a href="<?php echo esc_url(zskeleton_get_page_url('about')); ?>"><?php _e('About', 'zskeleton'); ?></a></li>
                <li><a href="<?php echo esc_url(zskeleton_get_page_url('faqs')); ?>"><?php _e('FAQs', 'zskeleton'); ?></a></li>
                <?php if ( $zskeleton_sidebar_mship ) : ?>
                <li><a href="<?php echo esc_url(zskeleton_get_page_url('memberships')); ?>"><?php _e('Memberships', 'zskeleton'); ?></a></li>
                <?php endif; ?>
                <li><a href="<?php echo esc_url(zskeleton_get_page_url('contact')); ?>"><?php _e('Contact', 'zskeleton'); ?></a></li>
            </ul>
        </div>
    </section>
    
    <!-- Recent Content Widget -->
    <section class="widget recent-content-widget formal-card">
        <h3 class="widget-title"><?php _e('Latest Updates', 'zskeleton'); ?></h3>
        
        <?php
        // Get recent posts
        $recent_content = new WP_Query(array(
            'post_type' => array('post'),
            'posts_per_page' => 5,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        if ($recent_content->have_posts()) :
        ?>
            <ul class="recent-content-list">
                <?php while ($recent_content->have_posts()) : $recent_content->the_post(); ?>
                    <li class="recent-item">
                        <h4 class="recent-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h4>
                        <div class="recent-meta">
                            <span class="recent-type"><?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name); ?></span>
                            
                            <?php
                            // Check if content requires membership (ZSkeleton Membership & Payments plugin).
                            $user_id    = get_current_user_id();
                            $has_access = true;
                            if ( class_exists( 'ZSkeleton_Access_Control' ) ) {
                                $access_control = new ZSkeleton_Access_Control();
                                $has_access     = $access_control->user_has_content_access( $user_id, get_the_ID() );
                            }

                            if ( ! $has_access && ! current_user_can( 'administrator' ) ) :
                            ?>
                                <span class="member-only-small">🔒</span>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php
        wp_reset_postdata();
        endif;
        ?>
        
        <div class="view-all">
            <a href="<?php echo esc_url(home_url('/blog')); ?>" class="btn btn-secondary btn-small">
                <?php _e('View All Posts', 'zskeleton'); ?>
            </a>
        </div>
    </section>
    
    <!-- Contact Widget -->
    <section class="widget contact-widget formal-card">
        <h3 class="widget-title"><?php _e('Get in Touch', 'zskeleton'); ?></h3>
        <p><?php _e('Have questions about membership or your account?', 'zskeleton'); ?></p>
        
        <div class="contact-links">
            <a href="<?php echo esc_url(get_permalink(get_page_by_path('contact'))); ?>" class="contact-link">
                <span class="contact-icon">✉️</span>
                <span class="contact-text"><?php _e('Contact Us', 'zskeleton'); ?></span>
            </a>
            
            <a href="mailto:membership@zskeleton.org" class="contact-link">
                <span class="contact-icon">👥</span>
                <span class="contact-text"><?php _e('Membership Inquiries', 'zskeleton'); ?></span>
            </a>
            
            <a href="mailto:media@zskeleton.org" class="contact-link">
                <span class="contact-icon">📰</span>
                <span class="contact-text"><?php _e('Media & Press', 'zskeleton'); ?></span>
            </a>
        </div>
    </section>
    
    <?php dynamic_sidebar('sidebar-1'); ?>
    
</aside><!-- #secondary -->

<style>
/* Sidebar Specific Styles */
.sidebar {
    max-width: 350px;
    margin-top: 0;
}

.widget {
    margin-bottom: 30px;
}

.widget-title {
    color: var(--primary-blue);
    font-size: 1.125rem;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--border-light);
}

.membership-widget .membership-badge {
    background: linear-gradient(135deg, var(--primary-blue), var(--academic-navy));
    color: white;
    padding: 15px;
    border-radius: 6px;
    text-align: center;
    margin-bottom: 20px;
}

.member-type {
    display: block;
    font-weight: 600;
    font-size: 1.125rem;
}

.status-active {
    display: block;
    font-size: 0.875rem;
    opacity: 0.9;
    margin-top: 5px;
}

.quick-links {
    margin-top: 20px;
}

.btn-small {
    padding: 8px 16px;
    font-size: 0.875rem;
}

.join-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 20px;
}

.search-categories {
    margin-top: 20px;
}

.search-categories h4 {
    font-size: 1rem;
    margin-bottom: 10px;
    color: var(--primary-blue);
}

.category-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.category-links li {
    margin-bottom: 5px;
}

.category-links a {
    color: var(--professional-gray);
    text-decoration: none;
    font-size: 0.875rem;
    transition: color 0.3s ease;
}

.category-links a:hover {
    color: var(--primary-blue);
}

.recent-content-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.recent-item {
    padding: 15px 0;
    border-bottom: 1px solid var(--border-light);
}

.recent-item:last-child {
    border-bottom: none;
}

.recent-title {
    margin: 0 0 8px 0;
    font-size: 0.875rem;
    line-height: 1.4;
}

.recent-title a {
    color: var(--professional-gray);
    text-decoration: none;
    transition: color 0.3s ease;
}

.recent-title a:hover {
    color: var(--primary-blue);
}

.recent-meta {
    font-size: 0.75rem;
    color: var(--neutral-silver);
    display: flex;
    align-items: center;
    gap: 8px;
}

.recent-type {
    background: var(--background-light);
    padding: 2px 6px;
    border-radius: 3px;
    font-weight: 500;
}

.member-only-small {
    font-size: 0.75rem;
}

.contact-links {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.contact-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    background: var(--background-light);
    border-radius: 6px;
    text-decoration: none;
    color: var(--professional-gray);
    transition: all 0.3s ease;
}

.contact-link:hover {
    background: var(--primary-blue);
    color: white;
}

.contact-icon {
    font-size: 1.25rem;
}

.contact-text {
    font-size: 0.875rem;
    font-weight: 500;
}

.view-all {
    margin-top: 20px;
    text-align: center;
}

@media (max-width: 1024px) {
    .sidebar {
        max-width: 100%;
        margin-top: 40px;
    }
    
    .join-actions {
        flex-direction: row;
    }
}

@media (max-width: 768px) {
    .join-actions {
        flex-direction: column;
    }
}
</style>
