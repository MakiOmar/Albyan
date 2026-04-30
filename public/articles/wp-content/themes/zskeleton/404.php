<?php
/**
 * 404 Error Page Template (404.php)
 * 
 * Template for 404 error pages
 * WordPress will automatically use this template when a page is not found
 *
 * @package ZSkeleton_Theme
 */

get_header(); ?>

<main id="primary" class="site-main error-404-page" tabindex="-1">
    
    <!-- Hero Section -->
    <section class="error-hero formal-card elevated">
        <div class="hero-content">
            <div class="error-code">404</div>
            <h1 class="error-title">This page is missing — but your next idea doesn't have to be.</h1>
            <p class="error-description">The page you are looking for could not be found.<br>It may have been moved, updated, or no longer exists.</p>
        </div>
    </section>

    <!-- Alternative Actions -->
    <div class="page-content">
        <div class="container">
            
            <section class="alternatives-section formal-card">
                <h2>What you can do instead:</h2>
                
                <div class="alternatives-grid">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="alternative-item">
                        <div class="alternative-icon">🔗</div>
                        <div class="alternative-content">
                            <h3>Go back to the Home Page</h3>
                            <p>Return to our homepage and explore our main sections</p>
                        </div>
                    </a>

                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('faqs'))); ?>" class="alternative-item">
                        <div class="alternative-icon">📚</div>
                        <div class="alternative-content">
                            <h3>Browse Frequently Asked Questions</h3>
                            <p>Find quick answers to common topics</p>
                        </div>
                    </a>

                    <a href="<?php echo esc_url(home_url('/blog/')); ?>" class="alternative-item">
                        <div class="alternative-icon">📰</div>
                        <div class="alternative-content">
                            <h3>Read the latest Articles</h3>
                            <p>Stay updated with recent posts and updates</p>
                        </div>
                    </a>

                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('about'))); ?>" class="alternative-item">
                        <div class="alternative-icon">🎓</div>
                        <div class="alternative-content">
                            <h3>Learn more About Us</h3>
                            <p>Discover who we are and what we provide</p>
                        </div>
                    </a>

                    <?php if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && zskeleton_is_memberships_feature_enabled() ) : ?>
                    <a href="<?php echo esc_url( zskeleton_get_page_url( 'memberships' ) ); ?>" class="alternative-item">
                        <div class="alternative-icon">🌍</div>
                        <div class="alternative-content">
                            <h3>Learn more about Memberships</h3>
                            <p>Join our community and unlock member features</p>
                        </div>
                    </a>
                    <?php endif; ?>

                    <a href="<?php echo esc_url(get_permalink(get_page_by_path('contact'))); ?>" class="alternative-item">
                        <div class="alternative-icon">✉️</div>
                        <div class="alternative-content">
                            <h3>Contact Us if you need assistance</h3>
                            <p>Get help from our support team</p>
                        </div>
                    </a>
                </div>
            </section>

            <!-- Thank You Message -->
            <section class="thank-you-section formal-card bordered">
                <p>Thank you for visiting ZSkeleton.</p>
            </section>

            <!-- Search Form -->
            <section class="search-section formal-card">
                <h2>Or search for what you're looking for:</h2>
                <div class="search-form-container">
                    <?php get_search_form(); ?>
                </div>
            </section>

        </div>
    </div>
    
</main>

<style>
.error-404-page {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.error-hero {
    text-align: center;
    padding: 80px 40px;
    margin-bottom: 40px;
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--academic-navy) 100%);
    color: white;
}

.error-code {
    font-size: 8rem;
    font-weight: bold;
    margin-bottom: 20px;
    color: white;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.error-title {
    font-size: 2.5rem;
    margin-bottom: 20px;
    color: white;
    line-height: 1.2;
}

.error-description {
    font-size: 1.25rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

.page-content {
    flex: 1;
    padding: 0 20px 60px 20px;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
}

/* Alternatives Section */
.alternatives-section {
    padding: 40px;
    margin-bottom: 30px;
}

.alternatives-section h2 {
    color: var(--primary-blue);
    margin-bottom: 30px;
    text-align: center;
    font-size: 1.75rem;
}

.alternatives-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.alternative-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
    background: var(--background-light);
    border-radius: 12px;
    border: 2px solid transparent;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
}

.alternative-item:hover {
    border-color: var(--primary-blue);
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    text-decoration: none;
    color: inherit;
}

.alternative-icon {
    font-size: 2.5rem;
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary-blue);
    border-radius: 50%;
    color: white;
}

.alternative-content {
    flex: 1;
}

.alternative-content h3 {
    color: var(--primary-blue);
    margin: 0 0 8px 0;
    font-size: 1.25rem;
}

.alternative-content p {
    margin: 0;
    color: var(--professional-gray);
    font-size: 0.95rem;
    line-height: 1.5;
}

/* Thank You Section */
.thank-you-section {
    padding: 30px 40px;
    margin-bottom: 30px;
    text-align: center;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.thank-you-section p {
    font-size: 1.25rem;
    color: var(--professional-gray);
    margin: 0;
    font-style: italic;
}

/* Search Section */
.search-section {
    padding: 40px;
    text-align: center;
}

.search-section h2 {
    color: var(--primary-blue);
    margin-bottom: 25px;
}

.search-form-container {
    max-width: 500px;
    margin: 0 auto;
}

.search-form-container input[type="search"] {
    width: 100%;
    padding: 15px 20px;
    border: 2px solid var(--border-light);
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.search-form-container input[type="search"]:focus {
    outline: none;
    border-color: var(--primary-blue);
}

.search-form-container input[type="submit"] {
    margin-top: 15px;
    padding: 12px 30px;
    background: var(--primary-blue);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.search-form-container input[type="submit"]:hover {
    background: var(--academic-navy);
}

/* Responsive Design */
@media (max-width: 768px) {
    .error-hero {
        padding: 60px 20px;
    }
    
    .error-code {
        font-size: 6rem;
    }
    
    .error-title {
        font-size: 2rem;
    }
    
    .error-description {
        font-size: 1.125rem;
    }
    
    .page-content {
        padding: 0 10px 40px 10px;
    }
    
    .alternatives-section,
    .search-section {
        padding: 30px 20px;
    }
    
    .alternatives-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .alternative-item {
        padding: 20px;
        gap: 15px;
    }
    
    .alternative-icon {
        width: 50px;
        height: 50px;
        font-size: 2rem;
    }
    
    .alternative-content h3 {
        font-size: 1.125rem;
    }
    
    .thank-you-section {
        padding: 25px 20px;
    }
}

@media (max-width: 480px) {
    .error-code {
        font-size: 4rem;
    }
    
    .error-title {
        font-size: 1.5rem;
    }
    
    .alternative-item {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .alternative-icon {
        width: 45px;
        height: 45px;
        font-size: 1.75rem;
    }
}
</style>

<?php get_footer(); ?>
