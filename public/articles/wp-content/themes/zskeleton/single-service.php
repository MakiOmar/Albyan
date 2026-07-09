<?php
/**
 * Single service template.
 *
 * @package ZSkeleton_Theme
 */

get_header(); ?>

<main id="primary" class="site-main" tabindex="-1">
    <?php do_action('zskeleton_before_main_content'); ?>
    <?php
    while (have_posts()) :
        the_post();
        $icon_id = ZSkeleton_Services::get_icon_attachment_id(get_the_ID());
        ?>
        <section class="service-single-title-bar" aria-labelledby="service-single-title-heading">
            <div class="service-single-title-bar__inner">
                <h1 id="service-single-title-heading" class="service-single-title-bar__title"><?php the_title(); ?></h1>
                <?php if (has_excerpt()) : ?>
                    <p class="service-single-title-bar__lead"><?php echo wp_kses_post(get_the_excerpt()); ?></p>
                <?php endif; ?>
            </div>
        </section>

        <article id="post-<?php the_ID(); ?>" <?php post_class('elevated'); ?>>
            <?php if ($icon_id) : ?>
                <div class="service-single-icon">
                    <?php echo ZSkeleton_Services::get_icon_html(get_the_ID(), 'medium', array('class' => 'service-icon-img', 'alt' => '')); ?>
                </div>
            <?php endif; ?>
            <?php if (has_post_thumbnail()) : ?>
                <div class="service-single-thumb">
                    <?php the_post_thumbnail('large'); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content academic-content">
                <?php the_content(); ?>
            </div>

            <footer class="entry-footer">
                <a href="<?php echo esc_url(get_post_type_archive_link(ZSkeleton_Services::POST_TYPE)); ?>" class="btn btn-secondary">
                    <?php esc_html_e('Back to all services', 'zskeleton'); ?>
                </a>
            </footer>
        </article>
    <?php endwhile; ?>
    <?php do_action('zskeleton_after_main_content'); ?>
</main>

<?php get_footer(); ?>
