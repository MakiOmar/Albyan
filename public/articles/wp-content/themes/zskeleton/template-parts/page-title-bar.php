<?php
/**
 * Full-width page title bar: breadcrumbs + title + optional subtitle/meta.
 *
 * Companion plugins should prefer zskeleton_the_plugin_page_title_bar() in functions.php
 * so breadcrumbs, access badge, and defaults stay aligned with the theme.
 *
 * @package ZSkeleton_Theme
 *
 * @param array $args {
 *     @type int|null $post_id            Page ID for breadcrumb chain.
 *     @type string   $title              Main heading (falls back to page title).
 *     @type string   $subtitle           Optional subtitle (allowed HTML via wp_kses_post + wpautop).
 *     @type bool     $show_breadcrumbs   Default true.
 *     @type bool     $show_meta          Published / modified dates.
 *     @type bool     $member_only_badge  Members-only notice.
 * }
 */

if (!defined('ABSPATH')) {
    exit;
}

$post_id = isset($post_id) ? (int) $post_id : get_queried_object_id();
$show_breadcrumbs = !isset($show_breadcrumbs) || $show_breadcrumbs;
$show_meta = !empty($show_meta);
$member_only_badge = !empty($member_only_badge);

if (!isset($title) || '' === $title) {
    $title = $post_id ? get_the_title($post_id) : '';
}
$title = (string) $title;

$subtitle = isset($subtitle) ? (string) $subtitle : '';

$crumbs = array();
if ($show_breadcrumbs && $post_id) {
    $crumbs = zskeleton_get_page_breadcrumb_items($post_id);
}
?>
<section class="zskeleton-page-title-bar" aria-labelledby="zskeleton-page-title-bar-heading">
    <div class="zskeleton-page-title-bar__inner">
        <?php if (!empty($crumbs)) : ?>
            <nav class="zskeleton-page-title-bar__breadcrumbs" aria-label="<?php esc_attr_e('Breadcrumbs', 'zskeleton'); ?>">
                <ol>
                    <?php
                    foreach ($crumbs as $i => $item) :
                        $label = isset($item['label']) ? $item['label'] : '';
                        $url = isset($item['url']) ? $item['url'] : '';
                        $is_current = !empty($item['current']);
                        ?>
                        <li>
                            <?php if ($url && !$is_current) : ?>
                                <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
                            <?php else : ?>
                                <span<?php echo $is_current ? ' aria-current="page"' : ''; ?>><?php echo esc_html($label); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>

        <h1 id="zskeleton-page-title-bar-heading" class="zskeleton-page-title-bar__title"><?php echo esc_html($title); ?></h1>

        <?php if ('' !== $subtitle) : ?>
            <div class="zskeleton-page-title-bar__subtitle"><?php echo wp_kses_post(wpautop($subtitle)); ?></div>
        <?php endif; ?>

        <?php if ($show_meta && $post_id) : ?>
            <div class="zskeleton-page-title-bar__meta">
                <time datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>" class="zskeleton-page-title-bar__date">
                    <?php echo esc_html(get_the_date('', $post_id)); ?>
                </time>
                <?php if (get_the_modified_date('Y-m-d', $post_id) !== get_the_date('Y-m-d', $post_id)) : ?>
                    <span class="zskeleton-page-title-bar__meta-sep" aria-hidden="true">•</span>
                    <time datetime="<?php echo esc_attr(get_the_modified_date('c', $post_id)); ?>">
                        <?php printf(esc_html__('Updated: %s', 'zskeleton'), esc_html(get_the_modified_date('', $post_id))); ?>
                    </time>
                <?php endif; ?>
                <?php if ($member_only_badge) : ?>
                    <span class="zskeleton-page-title-bar__badge member-only"><?php esc_html_e('Members Only', 'zskeleton'); ?></span>
                <?php endif; ?>
            </div>
        <?php elseif ($member_only_badge) : ?>
            <div class="zskeleton-page-title-bar__meta">
                <span class="zskeleton-page-title-bar__badge member-only"><?php esc_html_e('Members Only', 'zskeleton'); ?></span>
            </div>
        <?php endif; ?>
    </div>
</section>
