<?php
/**
 * Template for displaying FAQs page
 * 
 * This template displays frequently asked questions
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

get_header();

$zskeleton_faqs_page_id = (int) get_queried_object_id();

zskeleton_the_page_title_bar(
    array(
        'title'    => __('Frequently Asked Questions', 'zskeleton'),
        'subtitle' => __('Find answers to common questions about ZSkeleton membership, services, and resources.', 'zskeleton'),
    )
);
?>

<div class="site-content">
    <div class="<?php echo zskeleton_page_main_container_class( 'container', '', $zskeleton_faqs_page_id ); ?>">
        <main class="faq-page-simple">
            <!-- FAQ List -->
            <div class="faq-list-simple">
                <?php
                $faq_per_page = (int) get_option('zskeleton_faq_per_page', 10);
                if ($faq_per_page <= 0) {
                    $faq_per_page = 10;
                }

                $faqs = get_posts(
                    array(
                        'post_type'      => 'zskeleton_faqs',
                        'posts_per_page' => $faq_per_page,
                        'post_status'    => 'publish',
                        'orderby'        => 'meta_value_num',
                        'meta_key'       => '_zskeleton_faq_order',
                        'order'          => 'ASC',
                    )
                );
                ?>

                <?php if (!empty($faqs)) : ?>
                    <?php foreach ($faqs as $faq) : ?>
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
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="no-faqs-message">
                        <h3><?php _e('No FAQs found', 'zskeleton'); ?></h3>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Contact CTA -->
            <section class="faq-cta">
                <div class="faq-cta-content">
                    <h3><?php _e('Still have questions?', 'zskeleton'); ?></h3>
                    <p><?php _e("Can't find what you're looking for? Our team is here to help.", 'zskeleton'); ?></p>
                    <a href="<?php echo esc_url(zskeleton_get_page_url('contact')); ?>" class="btn btn-primary">
                        <?php _e('Contact Us', 'zskeleton'); ?>
                    </a>
                </div>
            </section>
        </main>
    </div>
</div>

<style>
/* FAQ Page Simple Styles */
.faq-page-simple {
    max-width: 900px;
    margin: 0 auto;
    padding: 60px 20px;
}

.site-content {
    background: #f8fafc;
    padding: 20px 0;
}

.page-header {
    text-align: center;
    margin-bottom: 60px;
    padding-bottom: 30px;
    border-bottom: 2px solid #e2e8f0;
}

.page-title {
    font-size: 2.75rem;
    color: var(--primary-blue);
    margin-bottom: 15px;
    font-weight: 700;
}

.page-description {
    font-size: 1.2rem;
    color: #64748b;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;
}

/* FAQ List */
.faq-list-simple {
    margin-bottom: 60px;
}

/* FAQ Items */
.faq-item {
    background: white;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.faq-item:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.faq-details {
    border: none;
}

.faq-question {
    padding: 24px 28px;
    font-size: 1.15rem;
    font-weight: 600;
    color: var(--primary-blue);
    cursor: pointer;
    list-style: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.3s ease;
    position: relative;
    gap: 20px;
}

.faq-question:hover {
    background: #f8fafc;
    color: var(--academic-navy);
}

.faq-question::-webkit-details-marker {
    display: none;
}

.faq-icon {
    width: 28px;
    height: 28px;
    border: 2px solid currentColor;
    border-radius: 50%;
    position: relative;
    transition: transform 0.3s ease, background-color 0.3s ease;
    flex-shrink: 0;
}

.faq-icon::before,
.faq-icon::after {
    content: '';
    position: absolute;
    background: currentColor;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    transition: all 0.3s ease;
}

.faq-icon::before {
    width: 12px;
    height: 2px;
}

.faq-icon::after {
    width: 2px;
    height: 12px;
}

.faq-details[open] .faq-icon {
    background: var(--primary-blue);
}

.faq-details[open] .faq-icon::before,
.faq-details[open] .faq-icon::after {
    background: white;
}

.faq-details[open] .faq-icon::after {
    transform: translate(-50%, -50%) rotate(90deg);
    opacity: 0;
}

.faq-answer {
    padding: 0 28px 28px;
    color: #475569;
    line-height: 1.8;
    font-size: 1.05rem;
    animation: fadeIn 0.3s ease;
}

.faq-answer p {
    margin-bottom: 15px;
}

.faq-answer p:last-child {
    margin-bottom: 0;
}

/* Contact CTA */
.faq-cta {
    margin-top: 80px;
    padding: 50px 40px;
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--academic-navy) 100%);
    border-radius: 16px;
    text-align: center;
    color: white;
    box-shadow: 0 8px 24px rgba(30, 58, 138, 0.2);
}

.faq-cta h3 {
    font-size: 2rem;
    margin-bottom: 15px;
    color: white;
}

.faq-cta p {
    font-size: 1.15rem;
    margin-bottom: 30px;
    opacity: 0.95;
}

.faq-cta .btn {
    background: white;
    color: var(--primary-blue);
    font-weight: 600;
    padding: 14px 36px;
    border-radius: 30px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.faq-cta .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(255,255,255,0.3);
    background: #f8fafc;
}

/* Animations */
@keyframes fadeIn {
    from { 
        opacity: 0; 
        transform: translateY(-10px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .faq-page-simple {
        padding: 40px 15px;
    }

    .page-title {
        font-size: 2.2rem;
    }
    
    .page-description {
        font-size: 1.05rem;
    }
    
    .page-header {
        margin-bottom: 40px;
        padding-bottom: 20px;
    }
    
    .faq-question {
        padding: 18px 20px;
        font-size: 1.05rem;
    }
    
    .faq-answer {
        padding: 0 20px 20px;
        font-size: 1rem;
    }
    
    .faq-cta {
        padding: 40px 25px;
        margin-top: 60px;
    }
    
    .faq-cta h3 {
        font-size: 1.6rem;
    }

    .faq-cta p {
        font-size: 1rem;
    }
}

@media (max-width: 480px) {
    .page-title {
        font-size: 1.9rem;
    }
    
    .page-description {
        font-size: 1rem;
    }
    
    .faq-question {
        padding: 16px 18px;
        font-size: 1rem;
        gap: 12px;
    }
    
    .faq-answer {
        padding: 0 18px 18px;
        font-size: 0.95rem;
    }

    .faq-icon {
        width: 24px;
        height: 24px;
    }

    .faq-icon::before {
        width: 10px;
    }

    .faq-icon::after {
        height: 10px;
    }
}
</style>

<?php get_footer(); ?>
