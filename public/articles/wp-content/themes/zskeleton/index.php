<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

get_header();

$zskeleton_index_layout_pid = 0;
if ( is_home() && ! is_front_page() ) {
	$zskeleton_index_layout_pid = function_exists( 'zskeleton_get_page_for_posts_id' ) ? zskeleton_get_page_for_posts_id() : (int) get_option( 'page_for_posts', 0 );
}
?>

<?php get_template_part( 'template-parts/blog/listing', 'hero', array( 'page_id' => 0 ) ); ?>

<main id="main" class="site-main" tabindex="-1">
    <div class="<?php echo zskeleton_page_main_container_class( 'wide-container', '', $zskeleton_index_layout_pid > 0 ? $zskeleton_index_layout_pid : null ); ?>">
        <div class="<?php echo zskeleton_page_layout_class( '', $zskeleton_index_layout_pid > 0 ? $zskeleton_index_layout_pid : null ); ?>">
            <div class="main-content">
        
        <?php if ( have_posts() ) : ?>

            <div class="practices-grid">
                <?php
                while ( have_posts() ) :
                    the_post();
					get_template_part( 'template-parts/blog/blog', 'card', array( 'post' => get_post() ) );
				endwhile;
				?>
            </div>
            
            <?php
            // Pagination.
            zskeleton_pagination();
            ?>
            
        <?php else : ?>
            
            <div class="no-posts formal-card">
                <h2><?php _e( 'Nothing Found', 'zskeleton' ); ?></h2>
                
                <?php if ( is_search() ) : ?>
                    <p><?php _e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'zskeleton' ); ?></p>
                    <?php get_search_form(); ?>
                <?php else : ?>
                    <p><?php _e( 'It seems we can\'t find what you\'re looking for. Perhaps searching can help.', 'zskeleton' ); ?></p>
                    <?php get_search_form(); ?>
                <?php endif; ?>
            </div>
            
        <?php endif; ?>
        
            </div><!-- .main-content -->
            
            <?php if ( zskeleton_index_should_show_sidebar() ) : ?>
            <div class="page-sidebar">
                <?php get_sidebar(); ?>
            </div>
            <?php endif; ?>
            
        </div><!-- .page-layout -->
    </div><!-- .wide-container -->
</main>

<style>
/* Blog Page Styles */

/* Wide container: keep a readable measure and horizontal padding (do not stretch edge-to-edge). */
.wide-container {
    max-width: min(1400px, 100%);
    width: 100%;
    margin-left: auto;
    margin-right: auto;
    padding-left: clamp(1rem, 4vw, 2.75rem);
    padding-right: clamp(1rem, 4vw, 2.75rem);
    box-sizing: border-box;
}

/* Page Hero Section */
.page-hero {
        background: linear-gradient(135deg, var(--primary-blue), var(--academic-navy));
        color: white;
        padding: 80px 0;
        text-align: center;
        margin-bottom: 0;
    }

    .hero-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }

    .hero-title {
        color: white;
        font-size: 3rem;
        font-weight: 700;
        margin: 0 0 20px 0;
        line-height: 1.2;
    }

    .hero-description {
        font-size: 1.25rem;
        color: rgba(255,255,255,0.9);
        margin-bottom: 30px;
        line-height: 1.6;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
    }

    /* Page Layout */
    .page-layout {
        display: flex;
        gap: 2.5rem;
        align-items: flex-start;
    }

    .main-content {
        flex: 1;
        min-width: 0;
    }

    .page-sidebar {
        flex: 0 0 300px;
        max-width: 25%;
    }

/* Featured Image */
.content-card-image {
    margin-bottom: 20px;
    overflow: hidden;
    border-radius: 8px;
}

.content-card-image img {
    width: 100%;
    height: auto;
    display: block;
    transition: transform 0.3s ease;
}

.content-card-image a:hover img {
    transform: scale(1.05);
}

/* No Posts Card */
.no-posts {
    text-align: center;
    padding: 60px 30px;
}

.no-posts h2 {
    color: var(--primary-blue);
    margin-bottom: 20px;
}

.no-posts p {
    margin-bottom: 25px;
    color: var(--professional-gray);
}

    /* Responsive Design */
    @media (max-width: 1024px) {
        .hero-title {
            font-size: 2.5rem;
        }
        
        .hero-description {
            font-size: 1.125rem;
        }
        
        .page-layout {
            gap: 2rem;
        }
        
        .page-sidebar {
            flex: 0 0 280px;
        }
    }

    @media (max-width: 768px) {
        .page-hero {
            padding: 60px 0;
        }
        
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-description {
            font-size: 1rem;
        }
        
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
    }

    @media (max-width: 480px) {
        .page-hero {
            padding: 50px 0;
        }
        
        .hero-title {
            font-size: 1.75rem;
        }
        
        .hero-description {
            font-size: 0.95rem;
        }
    }
</style>

<?php get_footer(); ?>
