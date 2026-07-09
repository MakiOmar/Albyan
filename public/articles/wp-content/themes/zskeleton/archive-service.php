<?php
/**
 * Archive template for services.
 *
 * @package ZSkeleton_Theme
 */

get_header(); ?>

<div class="site-content">
    <div class="container">
        <div class="content-sidebar-layout">
            <main class="main-content">
                <header class="page-header">
                    <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
                    <?php
                    $desc = get_the_archive_description();
                    if ($desc) {
                        echo '<div class="archive-description">' . wp_kses_post($desc) . '</div>';
                    }
                    ?>
                </header>

                <div class="services-archive-grid" style="display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); margin-top: 2rem;">
                    <?php
                    if (have_posts()) :
                        while (have_posts()) :
                            the_post();
                        $icon_id = ZSkeleton_Services::get_icon_attachment_id(get_the_ID());
                        ?>
                        <article <?php post_class('formal-card elevated'); ?> id="post-<?php the_ID(); ?>">
                            <?php if ($icon_id) : ?>
                                <div class="service-card-icon" style="margin-bottom: 0.75rem;">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php echo ZSkeleton_Services::get_icon_html(get_the_ID(), 'thumbnail', array('class' => 'service-icon-img', 'alt' => '')); ?>
                                    </a>
                                </div>
                            <?php elseif (has_post_thumbnail()) : ?>
                                <div class="service-card-thumb" style="margin-bottom: 0.75rem;">
                                    <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium'); ?></a>
                                </div>
                            <?php endif; ?>
                            <h2 class="entry-title" style="font-size: 1.25rem;">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <?php if (has_excerpt()) : ?>
                                <div class="entry-summary">
                                    <?php the_excerpt(); ?>
                                </div>
                            <?php endif; ?>
                            <p style="margin-top: 1rem;">
                                <a class="btn btn-secondary" href="<?php the_permalink(); ?>"><?php esc_html_e('Read more', 'zskeleton'); ?></a>
                            </p>
                        </article>
                        <?php
                        endwhile;
                    else :
                        ?>
                        <p><?php esc_html_e('No services found.', 'zskeleton'); ?></p>
                        <?php
                    endif;
                    ?>
                </div>

                <?php the_posts_pagination(); ?>
            </main>
        </div>
    </div>
</div>

<?php get_footer(); ?>
