<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */
?>

    </div><!-- #content -->

    <?php if ( zskeleton_show_newsletter_section() ) : ?>
    <!-- Newsletter Section (ZSkeleton Settings → Newsletter must be configured) -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <?php
                // Theme options (ZSkeleton Settings → Newsletter) override gettext defaults for site-specific copy.
                $zskeleton_nl_title = get_option('zskeleton_newsletter_title', '');
                $zskeleton_nl_desc  = get_option('zskeleton_newsletter_description', '');
                ?>
                <h2><?php echo esc_html('' !== trim((string) $zskeleton_nl_title) ? $zskeleton_nl_title : __('Stay Updated with ZSkeleton Research', 'zskeleton')); ?></h2>
                <p><?php echo esc_html('' !== trim((string) $zskeleton_nl_desc) ? $zskeleton_nl_desc : __('Join our community. Get exclusive insights, updates, and practical resources delivered to your inbox.', 'zskeleton')); ?></p>
                
                <form class="newsletter-form" id="newsletter-form">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="<?php esc_attr_e('Enter your professional email', 'zskeleton'); ?>" required>
                        <button type="submit" class="btn btn-gold">
                            <?php _e('Join Community', 'zskeleton'); ?>
                        </button>
                    </div>
                    <p class="newsletter-disclaimer">
                        <?php _e('By subscribing, you agree to receive updates and can unsubscribe at any time.', 'zskeleton'); ?>
                    </p>
                </form>
                
                <div class="newsletter-message" style="display: none;"></div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <footer id="colophon" class="site-footer">
        <div class="container">
            <?php
            // How many footer columns to output (Appearance → ZSkeleton Settings → General).
            $zskeleton_footer_cols = function_exists( 'zskeleton_get_footer_widget_areas_count' ) ? zskeleton_get_footer_widget_areas_count() : 4;
            ?>
            <div class="footer-content">
                
                <!-- Footer Widget Area 1 -->
                <div class="footer-section">
                    <?php if (is_active_sidebar('footer-1')) : ?>
                        <?php dynamic_sidebar('footer-1'); ?>
                    <?php else : ?>
						<?php
						// Fallback copy for Footer Widget Area 1. Theme options override the defaults.
						$blog_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
						$footer_1_heading = get_option('zskeleton_footer_widget_area_1_heading', '');
						$footer_1_description = get_option('zskeleton_footer_widget_area_1_description', '');

						$heading = '' !== trim((string) $footer_1_heading)
							? $footer_1_heading
							: sprintf(__('About %s', 'zskeleton'), $blog_name);

						$description = '' !== trim((string) $footer_1_description)
							? $footer_1_description
							: sprintf(
								__('%s is a reusable WordPress base theme built for modern membership and content websites.', 'zskeleton'),
								$blog_name
							);
						?>
						<h3><?php echo esc_html($heading); ?></h3>
						<p><?php echo esc_html($description); ?></p>
                        <?php
                        // Social URLs: shared options + Customizer (see zskeleton_get_contact).
                        $facebook_url  = zskeleton_get_contact( 'facebook' );
                        $twitter_url   = zskeleton_get_contact( 'twitter' );
                        $linkedin_url  = zskeleton_get_contact( 'linkedin' );
                        $youtube_url   = zskeleton_get_contact( 'youtube' );
                        $instagram_url = zskeleton_get_contact( 'instagram' );
                        $github_url    = zskeleton_get_contact( 'github' );
                        $snapchat_url  = zskeleton_get_contact( 'snapchat' );
                        $tiktok_url    = zskeleton_get_contact( 'tiktok' );

                        // Check if any social links are set
                        $has_social_links = !empty($facebook_url) || !empty($twitter_url) || !empty($linkedin_url) || !empty($youtube_url) || !empty($instagram_url) || !empty($github_url) || !empty($snapchat_url) || !empty($tiktok_url);
                        
                        if ($has_social_links) : ?>
                        <div class="social-links">
                            <?php if (!empty($facebook_url)) : ?>
                                <a href="<?php echo esc_url($facebook_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Facebook', 'zskeleton'); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($twitter_url)) : ?>
                                <a href="<?php echo esc_url($twitter_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Twitter/X', 'zskeleton'); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($linkedin_url)) : ?>
                                <a href="<?php echo esc_url($linkedin_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('LinkedIn', 'zskeleton'); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($youtube_url)) : ?>
                                <a href="<?php echo esc_url($youtube_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('YouTube', 'zskeleton'); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($instagram_url)) : ?>
                                <a href="<?php echo esc_url($instagram_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Instagram', 'zskeleton'); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($github_url)) : ?>
                                <a href="<?php echo esc_url($github_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('GitHub', 'zskeleton'); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($snapchat_url)) : ?>
                                <a href="<?php echo esc_url($snapchat_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Snapchat', 'zskeleton'); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12.206.793c.99 0 4.347.276 5.93 3.821.529 1.193.403 3.219.299 4.847l-.003.06c-.012.18-.022.345-.03.51.075.045.203.09.401.09.3-.016.659-.12 1.033-.301.165-.088.344-.104.464-.104.182 0 .359.029.509.09.45.149.734.479.734.838.015.449-.39.839-1.213 1.168-.089.029-.209.075-.344.119-.45.135-1.139.36-1.333.81-.09.224-.061.524.12.868l.015.015c.06.136 1.526 3.475 4.791 4.014.255.044.435.27.42.509 0 .075-.015.149-.045.225-.24.569-1.273.988-3.146 1.271-.059.091-.12.375-.164.57-.029.179-.074.36-.134.553-.076.271-.27.405-.555.405h-.03c-.135 0-.313-.031-.538-.074-.36-.075-.765-.135-1.273-.135-.3 0-.599.015-.913.074-.6.104-1.123.464-1.723.884-.853.599-1.826 1.288-3.294 1.288-.06 0-.119-.015-.18-.015h-.149c-1.468 0-2.427-.675-3.279-1.288-.599-.42-1.107-.779-1.707-.884-.314-.045-.629-.074-.928-.074-.54 0-.958.089-1.272.149-.211.043-.391.074-.54.074-.374 0-.523-.224-.583-.42-.061-.192-.09-.389-.135-.567-.046-.181-.105-.494-.166-.57-1.918-.222-2.95-.642-3.189-1.226-.031-.063-.052-.15-.055-.225-.015-.243.165-.465.42-.509 3.264-.54 4.73-3.879 4.791-4.02l.016-.029c.18-.345.224-.645.119-.869-.195-.434-.884-.658-1.332-.809-.121-.029-.24-.074-.346-.119-1.107-.435-1.257-.93-1.197-1.273.09-.479.674-.793 1.168-.793.146 0 .27.029.383.074.42.194.789.3 1.104.3.234 0 .384-.06.465-.105l-.046-.569c-.098-1.626-.225-3.651.307-4.837C7.392 1.077 10.739.807 11.727.807l.419-.015h.06z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($tiktok_url)) : ?>
                                <a href="<?php echo esc_url($tiktok_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('TikTok', 'zskeleton'); ?>">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if ( $zskeleton_footer_cols >= 2 ) : ?>
                <!-- Footer Widget Area 2 -->
                <div class="footer-section">
                    <?php if (is_active_sidebar('footer-2')) : ?>
                        <?php dynamic_sidebar('footer-2'); ?>
                    <?php else : ?>
                        <h3><?php _e('Quick Links', 'zskeleton'); ?></h3>
                        <ul class="footer-links">
                            <li><a href="<?php echo esc_url(zskeleton_get_page_url('about')); ?>"><?php _e('About Us', 'zskeleton'); ?></a></li>
                            <?php if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
                            <li><a href="<?php echo esc_url(zskeleton_get_page_url('memberships')); ?>"><?php _e('Memberships', 'zskeleton'); ?></a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo esc_url(zskeleton_get_page_url('faqs')); ?>"><?php _e('FAQs', 'zskeleton'); ?></a></li>
                            <li><a href="<?php echo esc_url(zskeleton_get_page_url('contact')); ?>"><?php _e('Contact Us', 'zskeleton'); ?></a></li>
                        </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ( $zskeleton_footer_cols >= 3 ) : ?>
                <!-- Footer Widget Area 3 -->
                <div class="footer-section">
                    <?php if (is_active_sidebar('footer-3')) : ?>
                        <?php dynamic_sidebar('footer-3'); ?>
                    <?php else : ?>
                        <h3><?php _e('Resources', 'zskeleton'); ?></h3>
                        <ul class="footer-links">
                            <li><a href="<?php echo esc_url(zskeleton_get_page_url('blog')); ?>"><?php _e('Blog', 'zskeleton'); ?></a></li>
                            <li><a href="<?php echo esc_url(zskeleton_get_page_url('faqs')); ?>"><?php _e('FAQs', 'zskeleton'); ?></a></li>
                            <li><a href="<?php echo esc_url(zskeleton_get_page_url('privacy-policy')); ?>"><?php _e('Privacy Policy', 'zskeleton'); ?></a></li>
                            <li><a href="<?php echo esc_url(zskeleton_get_page_url('terms-conditions')); ?>"><?php _e('Terms & Conditions', 'zskeleton'); ?></a></li>
                        </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ( $zskeleton_footer_cols >= 4 ) : ?>
                <!-- Footer Widget Area 4 -->
                <div class="footer-section">
                    <?php if (is_active_sidebar('footer-4')) : ?>
                        <?php dynamic_sidebar('footer-4'); ?>
                    <?php else : ?>
                        <h3><?php _e('Contact Information', 'zskeleton'); ?></h3>
                        <div class="contact-info">
                            <p>
                                <strong><?php _e('General Inquiries:', 'zskeleton'); ?></strong><br>
                                <a href="mailto:<?php echo esc_attr(get_option('zskeleton_contact_email', 'info@zskeleton.org')); ?>">
                                    <?php echo esc_html(get_option('zskeleton_contact_email', 'info@zskeleton.org')); ?>
                                </a>
                            </p>
                            <p>
                                <strong><?php _e('Support:', 'zskeleton'); ?></strong><br>
                                <a href="mailto:support@zskeleton.org">support@zskeleton.org</a>
                            </p>
                            <p>
                                <strong><?php _e('Partnerships:', 'zskeleton'); ?></strong><br>
                                <a href="mailto:partnerships@zskeleton.org">partnerships@zskeleton.org</a>
                            </p>
                            <?php if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
                            <p>
                                <strong><?php _e('Membership Support:', 'zskeleton'); ?></strong><br>
                                <a href="mailto:membership@zskeleton.org">membership@zskeleton.org</a>
                            </p>
                            <?php endif; ?>
                            <p>
                                <strong><?php _e('Press:', 'zskeleton'); ?></strong><br>
                                <a href="mailto:media@zskeleton.org">media@zskeleton.org</a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div><!-- .footer-content -->

        </div><!-- .container (main footer columns; not wrapping copyright strip) -->

        <!-- Copyright / legal row: full-bleed (no .container side margins on the bar; inner container aligns columns) -->
        <div class="footer-bottom footer-copyright-card">
            <div class="container">
                <div class="footer-bottom-columns">
                    <div class="footer-bottom-column footer-bottom-column--1">
                        <?php if ( is_active_sidebar( 'footer-bottom-1' ) ) : ?>
                            <?php dynamic_sidebar( 'footer-bottom-1' ); ?>
                        <?php else : ?>
                            <!-- Default: copyright when left column has no widgets -->
                            <div class="copyright">
                                <p>&copy; <?php echo esc_html( (string) (int) date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'zskeleton' ); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="footer-bottom-column footer-bottom-column--2">
                        <?php if ( is_active_sidebar( 'footer-bottom-2' ) ) : ?>
                            <?php dynamic_sidebar( 'footer-bottom-2' ); ?>
                        <?php else : ?>
                            <!-- Default: legal links when right column has no widgets -->
                            <div class="footer-legal">
                                <ul class="legal-links">
                                    <li><a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Privacy Policy', 'zskeleton' ); ?></a></li>
                                    <li><a href="<?php echo esc_url( zskeleton_get_page_url( 'terms-conditions' ) ); ?>"><?php esc_html_e( 'Terms & Conditions', 'zskeleton' ); ?></a></li>
                                    <li><a href="<?php echo esc_url( zskeleton_get_page_url( 'faqs' ) ); ?>"><?php esc_html_e( 'FAQs', 'zskeleton' ); ?></a></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div><!-- .footer-bottom -->
    </footer><!-- #colophon -->

</div><!-- #page -->

<?php if ( function_exists( 'zskeleton_should_show_back_to_top_button' ) && zskeleton_should_show_back_to_top_button() ) : ?>
<!-- Back to top (Layout / Customizer: Show back to top button) -->
<button id="back-to-top" class="back-to-top" type="button" aria-label="<?php esc_attr_e( 'Back to top', 'zskeleton' ); ?>" style="display: none;">
	<span aria-hidden="true">↑</span>
</button>
<?php endif; ?>

<?php if ( function_exists( 'zskeleton_should_show_whatsapp_float_button' ) && zskeleton_should_show_whatsapp_float_button() ) : ?>
	<?php
	$zskeleton_wa_url = zskeleton_get_whatsapp_float_button_url();
	$zskeleton_wa_is_remote = (bool) preg_match( '#^https?://#i', $zskeleton_wa_url );
	?>
	<!-- Floating WhatsApp (inline-start; mirrors to opposite side in RTL — see footer styles) -->
	<a
		href="<?php echo esc_url( $zskeleton_wa_url ); ?>"
		class="zskeleton-whatsapp-float"
		aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'zskeleton' ); ?>"
		<?php echo $zskeleton_wa_is_remote ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>
	>
		<span class="screen-reader-text"><?php esc_html_e( 'WhatsApp', 'zskeleton' ); ?></span>
		<svg class="zskeleton-whatsapp-float__icon" width="28" height="28" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="currentColor">
			<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
		</svg>
	</a>
<?php endif; ?>

<?php
// Mobile bottom navigation (Appearance → ZSkeleton Settings): Style 1 (legacy) or Style 2 (primary bar + popovers).
$zskeleton_mbn_style = function_exists( 'zskeleton_get_mobile_bottom_nav_style' ) ? zskeleton_get_mobile_bottom_nav_style() : 'style1';
?>

<?php if ( 'style2' === $zskeleton_mbn_style ) : ?>
	<?php
	$zskeleton_mbn_share_items = function_exists( 'zskeleton_get_mobile_bottom_nav_share_items' ) ? zskeleton_get_mobile_bottom_nav_share_items() : array();
	$zskeleton_mbn_bell_url    = function_exists( 'zskeleton_get_mobile_bottom_nav_bell_url' ) ? zskeleton_get_mobile_bottom_nav_bell_url() : esc_url( home_url( '/' ) );
	$zskeleton_mbn_cart_url    = function_exists( 'zskeleton_get_mobile_bottom_nav_cart_url' ) ? zskeleton_get_mobile_bottom_nav_cart_url() : esc_url( home_url( '/' ) );
	$zskeleton_mbn_wa_url      = function_exists( 'zskeleton_get_mobile_bottom_nav_whatsapp_url' ) ? zskeleton_get_mobile_bottom_nav_whatsapp_url() : esc_url( home_url( '/' ) );
	$zskeleton_mbn_wa_remote   = (bool) preg_match( '#^https?://#i', (string) $zskeleton_mbn_wa_url );
	?>
	<!-- dir: isolate RTL/LTR for icon order + popovers (avoids LTR wrappers breaking flex/grid flow). -->
	<div class="zskeleton-mbn2" data-zskeleton-mbn2 dir="<?php echo esc_attr( is_rtl() ? 'rtl' : 'ltr' ); ?>">
		<div class="zskeleton-mbn2__backdrop" data-zskeleton-mbn2-backdrop hidden aria-hidden="true"></div>

		<div id="zskeleton-mbn2-popover-share" class="zskeleton-mbn2__popover zskeleton-mbn2__popover--share" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Share', 'zskeleton' ); ?>" hidden>
			<div class="zskeleton-mbn2-share">
				<?php foreach ( $zskeleton_mbn_share_items as $zskeleton_mbn_share_item ) : ?>
					<?php
					$zskeleton_mbn_skey = isset( $zskeleton_mbn_share_item['key'] ) ? sanitize_key( (string) $zskeleton_mbn_share_item['key'] ) : '';
					$zskeleton_mbn_surl = isset( $zskeleton_mbn_share_item['url'] ) ? (string) $zskeleton_mbn_share_item['url'] : '';
					$zskeleton_mbn_slab = isset( $zskeleton_mbn_share_item['label'] ) ? (string) $zskeleton_mbn_share_item['label'] : '';
					$zskeleton_mbn_sena = ! empty( $zskeleton_mbn_share_item['enabled'] );
					$zskeleton_mbn_svg  = ( $zskeleton_mbn_skey && function_exists( 'zskeleton_get_mobile_bottom_nav_share_svg' ) ) ? zskeleton_get_mobile_bottom_nav_share_svg( $zskeleton_mbn_skey ) : '';
					$zskeleton_mbn_sgrid = '';
					if ( 'youtube' === $zskeleton_mbn_skey ) {
						$zskeleton_mbn_sgrid = ' style="grid-column: 1 / 2;"';
					} elseif ( 'tiktok' === $zskeleton_mbn_skey ) {
						$zskeleton_mbn_sgrid = ' style="grid-column: 2 / 3;"';
					} elseif ( 'linkedin' === $zskeleton_mbn_skey ) {
						$zskeleton_mbn_sgrid = ' style="grid-column: 3 / 4;"';
					}
					?>
					<?php if ( $zskeleton_mbn_sena && '' !== $zskeleton_mbn_surl ) : ?>
						<a class="zskeleton-mbn2-share__tile" href="<?php echo esc_url( $zskeleton_mbn_surl ); ?>" target="_blank" rel="noopener noreferrer"<?php echo $zskeleton_mbn_sgrid; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline style attribute is assembled from fixed keys. ?>>
							<span class="screen-reader-text"><?php echo esc_html( $zskeleton_mbn_slab ); ?></span>
							<?php echo $zskeleton_mbn_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline SVG markup (trusted). ?>
						</a>
					<?php else : ?>
						<span class="zskeleton-mbn2-share__tile zskeleton-mbn2-share__tile--disabled" aria-disabled="true"<?php echo $zskeleton_mbn_sgrid; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline style attribute is assembled from fixed keys. ?>>
							<span class="screen-reader-text"><?php echo esc_html( $zskeleton_mbn_slab ); ?></span>
							<?php echo $zskeleton_mbn_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline SVG markup (trusted). ?>
						</span>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>

		<div id="zskeleton-mbn2-popover-search" class="zskeleton-mbn2__popover zskeleton-mbn2__popover--search" role="search" aria-label="<?php esc_attr_e( 'Site search', 'zskeleton' ); ?>" hidden>
			<form class="zskeleton-mbn2-search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="zskeleton-mbn2-search__icon" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="11" cy="11" r="8"></circle>
						<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
					</svg>
				</span>
				<label class="screen-reader-text" for="zskeleton-mbn2-search-field"><?php esc_html_e( 'Search', 'zskeleton' ); ?></label>
				<input
					id="zskeleton-mbn2-search-field"
					class="zskeleton-mbn2-search__input"
					type="search"
					name="s"
					value="<?php echo esc_attr( get_search_query() ); ?>"
					placeholder="<?php echo esc_attr__( '…Search', 'zskeleton' ); ?>"
					dir="auto"
					autocomplete="off"
				/>
				<button class="zskeleton-mbn2-search__submit" type="submit"><?php esc_html_e( 'Search', 'zskeleton' ); ?></button>
			</form>
		</div>

		<nav class="mobile-bottom-nav mobile-bottom-nav--style2" aria-label="<?php esc_attr_e( 'Mobile navigation', 'zskeleton' ); ?>">
			<a class="zskeleton-mbn2__link" href="<?php echo esc_url( $zskeleton_mbn_bell_url ); ?>" aria-label="<?php esc_attr_e( 'Notifications', 'zskeleton' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
					<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
					<path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
				</svg>
			</a>

			<a class="zskeleton-mbn2__link" href="<?php echo esc_url( $zskeleton_mbn_cart_url ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'zskeleton' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
					<circle cx="9" cy="21" r="1"></circle>
					<circle cx="20" cy="21" r="1"></circle>
					<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
				</svg>
			</a>

			<button class="zskeleton-mbn2__btn zskeleton-mbn2__btn--search" type="button" data-zskeleton-mbn2-search-toggle aria-controls="zskeleton-mbn2-popover-search" aria-expanded="false" aria-label="<?php esc_attr_e( 'Search', 'zskeleton' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
					<circle cx="11" cy="11" r="8"></circle>
					<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
				</svg>
			</button>

			<button class="zskeleton-mbn2__btn zskeleton-mbn2__btn--share" type="button" data-zskeleton-mbn2-share-toggle aria-controls="zskeleton-mbn2-popover-share" aria-expanded="false" aria-label="<?php esc_attr_e( 'Share', 'zskeleton' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
					<circle cx="18" cy="5" r="3"></circle>
					<circle cx="6" cy="12" r="3"></circle>
					<circle cx="18" cy="19" r="3"></circle>
					<line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
					<line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
				</svg>
			</button>

			<a class="zskeleton-mbn2__link zskeleton-mbn2__link--whatsapp" href="<?php echo esc_url( $zskeleton_mbn_wa_url ); ?>" aria-label="<?php esc_attr_e( 'WhatsApp', 'zskeleton' ); ?>"<?php echo $zskeleton_mbn_wa_remote ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
				<span class="zskeleton-mbn2__wa-badge" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
						<circle cx="12" cy="12" r="12" fill="#25d366"></circle>
						<path fill="#ffffff" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"></path>
					</svg>
				</span>
			</a>
		</nav>
	</div>
<?php else : ?>
	<!-- Mobile Bottom Navigation (Style 1) -->
	<div class="zskeleton-mbn2 zskeleton-mbn2--style1" data-zskeleton-mbn2 dir="<?php echo esc_attr( is_rtl() ? 'rtl' : 'ltr' ); ?>">
		<div class="zskeleton-mbn2__backdrop" data-zskeleton-mbn2-backdrop hidden aria-hidden="true"></div>

		<div id="zskeleton-mbn2-popover-search" class="zskeleton-mbn2__popover zskeleton-mbn2__popover--search" role="search" aria-label="<?php esc_attr_e( 'Site search', 'zskeleton' ); ?>" hidden>
			<form class="zskeleton-mbn2-search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<span class="zskeleton-mbn2-search__icon" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="11" cy="11" r="8"></circle>
						<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
					</svg>
				</span>
				<label class="screen-reader-text" for="zskeleton-mbn2-search-field"><?php esc_html_e( 'Search', 'zskeleton' ); ?></label>
				<input
					id="zskeleton-mbn2-search-field"
					class="zskeleton-mbn2-search__input"
					type="search"
					name="s"
					value="<?php echo esc_attr( get_search_query() ); ?>"
					placeholder="<?php echo esc_attr__( '…Search', 'zskeleton' ); ?>"
					dir="auto"
					autocomplete="off"
				/>
				<button class="zskeleton-mbn2-search__submit" type="submit"><?php esc_html_e( 'Search', 'zskeleton' ); ?></button>
			</form>
		</div>

	<nav class="mobile-bottom-nav" aria-label="<?php esc_attr_e( 'Mobile navigation', 'zskeleton' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav-item <?php echo is_front_page() ? 'active' : ''; ?>" aria-label="<?php esc_attr_e( 'Home', 'zskeleton' ); ?>">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
				<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
				<polyline points="9 22 9 12 15 12 15 22"></polyline>
			</svg>
			<span><?php esc_html_e( 'Home', 'zskeleton' ); ?></span>
		</a>

		<button type="button" class="nav-item nav-item--search mobile-bottom-nav__search-trigger" data-zskeleton-mbn2-search-toggle aria-expanded="false" aria-controls="zskeleton-mbn2-popover-search" aria-label="<?php esc_attr_e( 'Search', 'zskeleton' ); ?>">
			<span class="screen-reader-text"><?php esc_html_e( 'Search', 'zskeleton' ); ?></span>
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
				<circle cx="11" cy="11" r="8"></circle>
				<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
			</svg>
			<span><?php esc_html_e( 'Search', 'zskeleton' ); ?></span>
		</button>

		<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( home_url( '/profile/' ) ); ?>" class="nav-item <?php echo is_page( 'profile' ) ? 'active' : ''; ?>" aria-label="<?php esc_attr_e( 'Profile', 'zskeleton' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
					<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
					<circle cx="12" cy="7" r="4"></circle>
				</svg>
				<span><?php esc_html_e( 'Profile', 'zskeleton' ); ?></span>
			</a>

			<?php
			// Third slot: Join (guest funnel), Logout (member), or Logout when memberships UI is disabled for non-members.
			$user_id              = get_current_user_id();
			$zskeleton_mshow      = function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled();
			$zskeleton_is_member  = class_exists( 'ZSkeleton_User_Profile_Fields' ) && ZSkeleton_User_Profile_Fields::user_has_active_membership( $user_id );
			if ( $zskeleton_mshow && ( ! class_exists( 'ZSkeleton_User_Profile_Fields' ) || ! $zskeleton_is_member ) ) :
				?>
				<a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="nav-item <?php echo function_exists( 'zskeleton_is_membership_page' ) && zskeleton_is_membership_page() ? 'active' : ''; ?>" aria-label="<?php esc_attr_e( 'Join', 'zskeleton' ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
						<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
						<circle cx="8.5" cy="7" r="4"></circle>
						<line x1="20" y1="8" x2="20" y2="14"></line>
						<line x1="23" y1="11" x2="17" y2="11"></line>
					</svg>
					<span><?php esc_html_e( 'Join', 'zskeleton' ); ?></span>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="nav-item" aria-label="<?php esc_attr_e( 'Logout', 'zskeleton' ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
						<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
						<polyline points="16 17 21 12 16 7"></polyline>
						<line x1="21" y1="12" x2="9" y2="12"></line>
					</svg>
					<span><?php esc_html_e( 'Logout', 'zskeleton' ); ?></span>
				</a>
			<?php endif; ?>
		<?php elseif ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
			<a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="nav-item <?php echo ( ( function_exists( 'zskeleton_is_membership_page' ) && zskeleton_is_membership_page() ) || is_page( 'register' ) ) ? 'active' : ''; ?>" aria-label="<?php esc_attr_e( 'Join', 'zskeleton' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
					<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
					<circle cx="8.5" cy="7" r="4"></circle>
					<line x1="20" y1="8" x2="20" y2="14"></line>
					<line x1="23" y1="11" x2="17" y2="11"></line>
				</svg>
				<span><?php esc_html_e( 'Join', 'zskeleton' ); ?></span>
			</a>
		<?php else : ?>
			<a href="<?php echo esc_url( zskeleton_get_page_url( 'contact' ) ); ?>" class="nav-item <?php echo is_page( 'contact' ) ? 'active' : ''; ?>" aria-label="<?php esc_attr_e( 'Contact', 'zskeleton' ); ?>">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
					<path d="M4 4h16v12H4z"></path>
					<polyline points="22,6 12,13 2,6"></polyline>
				</svg>
				<span><?php esc_html_e( 'Contact', 'zskeleton' ); ?></span>
			</a>
		<?php endif; ?>
	</nav>
	</div>
<?php endif; ?>

<?php wp_footer(); ?>

<script>
// Newsletter form handling
document.addEventListener('DOMContentLoaded', function() {
    const newsletterForm = document.getElementById('newsletter-form');
    const messageDiv = document.querySelector('.newsletter-message');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'zskeleton_newsletter');
            formData.append('email', this.email.value);
            formData.append('nonce', zskeletonAjax.nonce);
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = '<?php esc_js(_e('Subscribing...', 'zskeleton')); ?>';
            submitBtn.disabled = true;
            
            fetch(zskeletonAjax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                messageDiv.style.display = 'block';
                
                if (data.success) {
                    messageDiv.innerHTML = '<p class="success-message">' + data.data + '</p>';
                    newsletterForm.reset();
                } else {
                    messageDiv.innerHTML = '<p class="error-message">' + data.data + '</p>';
                }
                
                // Reset button
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                
                // Hide message after 5 seconds
                setTimeout(() => {
                    messageDiv.style.display = 'none';
                }, 5000);
            })
            .catch(error => {
                messageDiv.style.display = 'block';
                messageDiv.innerHTML = '<p class="error-message"><?php esc_js(_e('An error occurred. Please try again.', 'zskeleton')); ?></p>';
                
                // Reset button
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    // Back to top functionality
    const backToTopBtn = document.getElementById('back-to-top');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.style.display = 'block';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });
        
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Header search toggle is handled by assets/js/main.js.
    
    // Mobile menu toggle
    const menuToggle = document.querySelector('.menu-toggle');
    const primaryMenu = document.getElementById('primary-menu');
    
    if (menuToggle && primaryMenu) {
        menuToggle.addEventListener('click', function() {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            this.setAttribute('aria-expanded', !isExpanded);
            primaryMenu.classList.toggle('active');
        });
    }
    
    // Scroll to newsletter functionality
    const scrollToNewsletterBtn = document.querySelector('.scroll-to-newsletter');
    if (scrollToNewsletterBtn) {
        scrollToNewsletterBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const newsletterSection = document.querySelector('.newsletter-section');
            if (newsletterSection) {
                newsletterSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                // Focus on email input after scroll
                setTimeout(() => {
                    const emailInput = document.querySelector('#newsletter-form input[type="email"]');
                    if (emailInput) {
                        emailInput.focus();
                    }
                }, 500);
            }
        });
    }
});
</script>

<style>
/* Additional Footer Styles */
.newsletter-section {
    background: var(--background-light);
    padding: 60px 0;
    text-align: center;
}

.newsletter-content h2 {
    color: var(--primary-blue);
    margin-bottom: 15px;
}

.newsletter-form {
    max-width: 500px;
    margin: 30px auto 0;
    display: flex;
    gap: 10px;
}

.newsletter-form input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid var(--border-light);
    border-radius: 6px;
    font-size: 1rem;
}

.success-message {
    color: var(--success-green);
    font-weight: 600;
}

.error-message {
    color: var(--alert-red);
    font-weight: 600;
}

.footer-links {
    list-style: none;
    padding: 0;
}

.footer-links li {
    margin-bottom: 8px;
}

.social-links {
    margin-top: 20px;
    display: flex;
    gap: 15px;
    align-items: center;
}

.social-links a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    text-decoration: none;
    opacity: 0.9;
    transition: all 0.3s ease;
    color: white;
}

.social-links a:hover {
    opacity: 1;
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.social-links svg {
    width: 20px;
    height: 20px;
    fill: currentColor;
}

.legal-links {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 20px;
}

.footer-bottom-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    gap: 1.25rem 2rem;
    width: 100%;
}

.footer-bottom-column--1 {
    text-align: left;
    justify-self: start;
}

.footer-bottom-column--2 {
    text-align: right;
    justify-self: end;
}

.footer-bottom-widget {
    margin: 0;
}

.footer-bottom-widget-title {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: #fff;
}

/* ZSkeleton nav menu widgets: no bullets or discs */
.zskeleton-nav-menu__list,
.zskeleton-nav-menu__list ul {
    list-style: none;
    list-style-type: none;
    margin: 0;
    padding: 0;
}

.zskeleton-nav-menu__list li::marker {
    content: none;
}

.zskeleton-nav-menu--horizontal .zskeleton-nav-menu__list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem 1.25rem;
    align-items: center;
    justify-content: flex-start;
}

.zskeleton-nav-menu--horizontal .zskeleton-nav-menu__list > li {
    margin: 0;
}

.zskeleton-nav-menu--vertical .zskeleton-nav-menu__list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
}

.footer-bottom-column--2 .zskeleton-nav-menu--vertical .zskeleton-nav-menu__list {
    align-items: flex-end;
}

.footer-bottom-column--1 .zskeleton-nav-menu--vertical .zskeleton-nav-menu__list {
    align-items: flex-start;
}

.zskeleton-nav-menu--vertical .zskeleton-nav-menu__list .sub-menu {
    margin-top: 0.35rem;
    padding-left: 1rem;
}

.footer-bottom-column--2 .zskeleton-nav-menu--vertical .zskeleton-nav-menu__list .sub-menu {
    text-align: right;
}

.footer-bottom-column--1 .zskeleton-nav-menu--vertical .zskeleton-nav-menu__list .sub-menu {
    text-align: left;
}

.site-footer .zskeleton-nav-menu a {
    display: inline-block;
    margin-bottom: 0;
    text-decoration: none;
    color: inherit;
}

.zskeleton-widget-placeholder {
    margin: 0;
    font-size: 0.875rem;
    opacity: 0.85;
}

.back-to-top {
    position: fixed;
    bottom: 30px;
    inset-inline-end: 30px;
    inset-inline-start: auto;
    background: var(--primary-blue);
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
    cursor: pointer;
    z-index: 1000;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.back-to-top:hover {
    background: var(--primary-blue);
    transform: translateY(-2px);
}

/* Floating WhatsApp: inline-start (LTR: left, RTL: right) — opposite .back-to-top which uses inline-end. */
.zskeleton-whatsapp-float {
    position: fixed;
    bottom: 30px;
    inset-inline-start: 30px;
    inset-inline-end: auto;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #25d366;
    color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    text-decoration: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.zskeleton-whatsapp-float:hover,
.zskeleton-whatsapp-float:focus {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
    outline: none;
}

.zskeleton-whatsapp-float__icon {
    display: block;
    width: 28px;
    height: 28px;
}

.header-links a:hover {
    text-decoration: underline;
}

.member-badge {
    background: var(--primary-blue);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Mobile Bottom Navigation */
.mobile-bottom-nav {
	display: none;
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	background: white;
	box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
	z-index: 1000;
	padding: 8px 0;
	padding-bottom: calc(8px + env(safe-area-inset-bottom));
	border-top: 1px solid var(--border-light);
	justify-content: space-around;
	align-items: center;
	-webkit-transform: translateZ(0);
	transform: translateZ(0);
	-webkit-backface-visibility: hidden;
	backface-visibility: hidden;
}

.mobile-bottom-nav .nav-item {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	text-decoration: none;
	color: var(--neutral-silver);
	transition: all 0.3s ease;
	padding: 8px 12px;
	border-radius: 8px;
	min-width: 70px;
}

.mobile-bottom-nav .nav-item:hover,
.mobile-bottom-nav .nav-item:focus {
	color: var(--primary-blue);
	background: var(--background-light);
}

.mobile-bottom-nav .nav-item.active {
	color: var(--primary-blue);
	font-weight: 600;
}

.mobile-bottom-nav .nav-item svg {
	width: 24px;
	height: 24px;
	margin-bottom: 4px;
}

.mobile-bottom-nav .nav-item span {
	font-size: 0.75rem;
	font-weight: 500;
}

.mobile-bottom-nav button.nav-item {
	appearance: none;
	-webkit-appearance: none;
	font: inherit;
	line-height: normal;
	text-align: center;
	cursor: pointer;
	width: auto;
	box-sizing: border-box;
	border: none!important;
	border-radius: 8px;
	background: transparent!important;
	color: inherit!important;
}

.mobile-bottom-nav button.nav-item:focus {
	outline: none;
}

.mobile-bottom-nav button.nav-item:focus-visible {
	outline: 2px solid var(--primary-blue, #2563eb);
	outline-offset: 2px;
}

/* Style 1: bottom-bar search should be icon-only, black, and no background */
.mobile-bottom-nav .nav-item--search {
	color: #000;
	background: transparent;
	border: 0;
}

.mobile-bottom-nav .nav-item--search:hover,
.mobile-bottom-nav .nav-item--search:focus {
	color: #000;
	background: transparent;
}

.mobile-bottom-nav .nav-item--search svg {
	margin-bottom: 0;
}

/* Mobile bottom navigation — Style 2 (primary bar + popovers) */
.zskeleton-mbn2 {
	display: none;
	position: fixed;
	inset-inline: 0;
	bottom: 0;
	z-index: 1000;
}

.zskeleton-mbn2__backdrop {
	position: fixed;
	inset: 0;
	z-index: 10000;
	background: rgba(15, 23, 42, 0.45);
	pointer-events: auto;
}

.zskeleton-mbn2__backdrop[hidden] {
	display: none;
}

.zskeleton-mbn2__popover {
	position: absolute;
	left: 12px;
	right: 12px;
	bottom: calc(64px + env(safe-area-inset-bottom));
	z-index: 10002;
	border-radius: 14px 14px 14px 14px;
	background: var(--zskeleton-color-primary, var(--primary-blue));
	color: #fff;
	box-shadow: 0 10px 30px rgba(15, 23, 42, 0.25);
	padding: 12px;
	pointer-events: auto;
}

.zskeleton-mbn2__popover[hidden] {
	display: none;
}

.mobile-bottom-nav.mobile-bottom-nav--style2 {
	background: var(--zskeleton-color-primary, var(--primary-blue));
	color: #fff;
	border-top: 0;
	box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.22), 0 -10px 30px rgba(15, 23, 42, 0.18);
	padding: 10px 10px;
	z-index: 10001;
	pointer-events: auto;
}

/* RTL: reverse flex row so icon order mirrors LTR bar (WhatsApp … Bell from inline-start). */
html[dir="rtl"] .mobile-bottom-nav.mobile-bottom-nav--style2,
body.rtl .mobile-bottom-nav.mobile-bottom-nav--style2 {
	flex-direction: row-reverse;
}

.mobile-bottom-nav.mobile-bottom-nav--style2 .zskeleton-mbn2__link,
.mobile-bottom-nav.mobile-bottom-nav--style2 .zskeleton-mbn2__btn {
	flex: 1 1 0;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 0;
	height: 44px;
	border-radius: 12px;
	text-decoration: none;
	color: #fff;
	background: transparent;
	border: 0;
	padding: 0;
	margin: 0;
	cursor: pointer;
	transition: background 0.2s ease, transform 0.15s ease;
}

.mobile-bottom-nav.mobile-bottom-nav--style2 .zskeleton-mbn2__link:focus,
.mobile-bottom-nav.mobile-bottom-nav--style2 .zskeleton-mbn2__btn:focus {
	outline: 2px solid rgba(255, 255, 255, 0.85);
	outline-offset: 2px;
}

.mobile-bottom-nav.mobile-bottom-nav--style2 .zskeleton-mbn2__btn[aria-expanded="true"],
.mobile-bottom-nav.mobile-bottom-nav--style2 .zskeleton-mbn2__link:active {
	background: rgba(255, 255, 255, 0.16);
}

.zskeleton-mbn2-share {
	display: grid;
	grid-template-columns: repeat(5, minmax(0, 1fr));
	gap: 10px;
}

.zskeleton-mbn2-share__tile {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 100%;
	aspect-ratio: 1 / 1;
	border-radius: 12px;
	background: rgba(255, 255, 255, 0.12);
	color: #fff;
	text-decoration: none;
	border: 1px solid rgba(255, 255, 255, 0.12);
}

.zskeleton-mbn2-share__tile--disabled {
	opacity: 0.35;
	pointer-events: none;
}

.zskeleton-mbn2-share__icon {
	display: block;
}

.zskeleton-mbn2-search {
	display: flex;
	align-items: center;
	gap: 10px;
}

.zskeleton-mbn2-search__icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	color: #fff;
	flex: 0 0 auto;
}

.zskeleton-mbn2-search__input {
	flex: 1 1 auto;
	width: 100%;
	border: 0;
	border-radius: 12px;
	padding: 12px 12px;
	background: #fff;
	color: #0f172a;
}

.zskeleton-mbn2-search__input::placeholder {
	color: rgba(15, 23, 42, 0.45);
}

.zskeleton-mbn2-search__submit {
	flex: 0 0 auto;
	border-radius: 12px;
	padding: 10px 12px;
	border: 0;
	cursor: pointer;
}

@media (max-width: 768px) {
    .header-top-content,
    .footer-bottom-columns {
        grid-template-columns: 1fr;
        text-align: center;
    }

    .footer-bottom-column--1,
    .footer-bottom-column--2 {
        text-align: center;
        justify-self: center;
    }

    .zskeleton-nav-menu--horizontal .zskeleton-nav-menu__list {
        justify-content: center;
    }

    .zskeleton-nav-menu--vertical .zskeleton-nav-menu__list {
        align-items: center;
    }

    .zskeleton-nav-menu--vertical .zskeleton-nav-menu__list .sub-menu {
        text-align: center;
        padding-left: 0;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
    
    .legal-links {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    /* Show mobile navigation on mobile devices */
    .mobile-bottom-nav {
        display: flex;
    }

    .zskeleton-mbn2 {
        display: block;
    }
    
    /* Add padding to footer to prevent content being hidden */
    .site-footer {
        padding-bottom: calc(80px + env(safe-area-inset-bottom));
    }
    
    /* Add padding to body to prevent content being hidden by fixed nav */
    body {
        padding-bottom: calc(70px + env(safe-area-inset-bottom));
    }
    
    /* Adjust back-to-top button position */
    .back-to-top {
        bottom: calc(90px + env(safe-area-inset-bottom));
    }

    /* Match vertical offset so both floats clear the mobile tab bar. */
    .zskeleton-whatsapp-float {
        bottom: calc(90px + env(safe-area-inset-bottom));
    }
    
    /* iOS specific fixes for fixed positioning */
    @supports (-webkit-touch-callout: none) {
        .mobile-bottom-nav {
            /* Ensure proper rendering on iOS Safari */
            -webkit-overflow-scrolling: touch;
            will-change: transform;
        }
    }
}
</style>

</body>
</html>
