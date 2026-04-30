<?php
/**
 * Single FAQ template.
 *
 * @package ZSkeleton_Theme
 */

get_header(); ?>

<main id="primary" class="site-main" tabindex="-1">
    <?php do_action('zskeleton_before_main_content'); ?>
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('formal-card elevated'); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header>

            <div class="entry-content academic-content">
                <?php the_content(); ?>
            </div>

            <footer class="entry-footer">
                <a href="<?php echo esc_url(get_post_type_archive_link('zskeleton_faqs')); ?>" class="btn btn-secondary">
                    <?php _e('Back to All FAQs', 'zskeleton'); ?>
                </a>
            </footer>
        </article>

        <?php if (post_type_supports(get_post_type(), 'comments') && (comments_open() || get_comments_number())) : ?>
            <section class="comments-section formal-card elevated" style="margin-top: 2rem;">
                <?php comments_template(); ?>
            </section>
        <?php endif; ?>
    <?php endwhile; ?>
    <?php do_action('zskeleton_after_main_content'); ?>
</main>

<?php get_footer(); ?>
