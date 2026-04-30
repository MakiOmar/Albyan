<?php
/**
 * Archive template for displaying FAQs
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

get_header(); ?>

<div class="site-content">
    <div class="container">
        <div class="content-sidebar-layout">
            <main class="faq-page main-content">
        <header class="page-header">
            <h1 class="page-title"><?php _e('Frequently Asked Questions', 'zskeleton'); ?></h1>
            <p class="page-description">
                <?php _e('Find answers to common questions about ZSkeleton membership, services, and resources.', 'zskeleton'); ?>
            </p>
        </header>

        <div class="faq-controls">
            <?php if (get_option('zskeleton_faq_show_search', '1') === '1'): ?>
            <div class="faq-search">
                <input type="search" id="faq-search" placeholder="<?php _e('Search FAQs...', 'zskeleton'); ?>" class="faq-search-input" />
                <button type="button" class="faq-search-btn">
                    <span class="dashicons dashicons-search"></span>
                </button>
            </div>
            <?php endif; ?>

            <?php if (get_option('zskeleton_faq_show_categories', '1') === '1'): ?>
            <div class="faq-category-filter">
                <select id="faq-category-filter">
                    <option value=""><?php _e('All Categories', 'zskeleton'); ?></option>
                    <?php
                    $categories = ZSkeleton_FAQs::get_faq_categories();
                    if (is_array($categories)) {
                        foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category->slug); ?>">
                                <?php echo esc_html($category->name); ?> (<?php echo $category->count; ?>)
                            </option>
                        <?php endforeach;
                    } ?>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <?php
        $featured_limit = get_option('zskeleton_faq_featured_limit', 5);
        $featured_faqs = ZSkeleton_FAQs::get_faqs_by_category('', $featured_limit, true);
        if (!empty($featured_faqs)): ?>
        <section class="featured-faqs">
            <h2><?php _e('Most Asked Questions', 'zskeleton'); ?></h2>
            <div class="faq-grid featured">
                <?php foreach ($featured_faqs as $faq):
                    $difficulty = get_post_meta($faq->ID, '_zskeleton_faq_difficulty', true) ?: 'beginner';
                    $categories = get_the_terms($faq->ID, 'zskeleton_faq_category');
                ?>
                <article class="faq-item featured-faq" data-category="<?php echo $categories ? esc_attr($categories[0]->slug) : ''; ?>" data-difficulty="<?php echo esc_attr($difficulty); ?>">
                    <details class="faq-details">
                        <summary class="faq-question">
                            <?php echo esc_html($faq->post_title); ?>
                            <span class="faq-icon"></span>
                        </summary>
                        <div class="faq-answer">
                            <?php echo wpautop($faq->post_content); ?>
                        </div>
                    </details>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <section class="all-faqs">
            <h2><?php _e('All Questions', 'zskeleton'); ?></h2>
            <div class="faq-grid">
                <?php
                $all_faqs = get_posts(array(
                    'post_type' => 'zskeleton_faqs',
                    'posts_per_page' => get_option('zskeleton_faq_per_page', 10),
                    'post_status' => 'publish',
                    'orderby' => 'meta_value_num',
                    'meta_key' => '_zskeleton_faq_order',
                    'order' => 'ASC'
                ));
                if (!empty($all_faqs)):
                    foreach ($all_faqs as $faq): ?>
                        <article class="faq-item">
                            <details class="faq-details">
                                <summary class="faq-question">
                                    <?php echo esc_html($faq->post_title); ?>
                                    <span class="faq-icon"></span>
                                </summary>
                                <div class="faq-answer">
                                    <?php echo wpautop($faq->post_content); ?>
                                </div>
                            </details>
                        </article>
                    <?php endforeach;
                else: ?>
                    <div class="no-faqs-message">
                        <h3><?php _e('No FAQs found', 'zskeleton'); ?></h3>
                    </div>
                <?php endif; ?>
            </div>
        </section>
            </main>
            <?php get_sidebar(); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
