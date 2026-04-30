<?php
/**
 * The template for displaying comments (plugin-friendly: wp_list_comments, comment_form).
 *
 * @package ZSkeleton_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (post_password_required()) {
    return;
}

$zskeleton_comment_count = (int) get_comments_number();
?>
<section id="comments" class="comments-area">
    <?php if (have_comments()) : ?>
        <h2 class="comments-title">
            <?php
            if (1 === $zskeleton_comment_count) {
                esc_html_e('One comment', 'zskeleton');
            } else {
                printf(
                    /* translators: %s: comment count */
                    esc_html(_n('%s comment', '%s comments', $zskeleton_comment_count, 'zskeleton')),
                    esc_html(number_format_i18n($zskeleton_comment_count))
                );
            }
            ?>
        </h2>

        <ol class="comment-list">
            <?php
            wp_list_comments(
                array(
                    'style'       => 'ol',
                    'short_ping'  => true,
                    'avatar_size' => 60,
                    'callback'    => 'zskeleton_comment_callback',
                )
            );
            ?>
        </ol>

        <?php the_comments_navigation(); ?>
    <?php endif; ?>

    <?php if (!comments_open() && $zskeleton_comment_count && post_type_supports(get_post_type(), 'comments')) : ?>
        <p class="no-comments"><?php esc_html_e('Comments are closed.', 'zskeleton'); ?></p>
    <?php endif; ?>

    <?php comment_form(); ?>
</section>
