<?php
/**
 * Membership pricing grid (same markup/logic as page-memberships.php).
 *
 * @package ZSkeleton_Theme
 *
 * @param array $args {
 *     Optional. Passed via get_template_part( ..., null, $args ). WP 5.5+ extracts keys as variables.
 *
 *     @type array  $plans               Active plans; defaults to ZSkeleton_Membership_Plans::get_active_plans().
 *     @type string $heading             Section heading.
 *     @type string $section_extra_class Extra classes on the root section element.
 * }
 */

if (!defined('ABSPATH')) {
    exit;
}

if ( function_exists( 'zskeleton_is_memberships_feature_enabled' ) && ! zskeleton_is_memberships_feature_enabled() ) {
    return;
}

if ( ! isset( $plans ) || ! is_array( $plans ) ) {
    $plans = class_exists( 'ZSkeleton_Membership_Plans' ) ? ZSkeleton_Membership_Plans::get_active_plans() : array();
}

if (empty($plans)) {
    return;
}

if (!isset($heading)) {
    $heading = __('Choose Your Membership', 'zskeleton');
}

$section_extra_class = isset($section_extra_class) ? trim((string) $section_extra_class) : '';
?>
<section class="zskeleton-membership-pricing pricing-section <?php echo esc_attr($section_extra_class); ?>">
    <h2><?php echo esc_html($heading); ?></h2>

    <div class="pricing-grid">
        <?php foreach ($plans as $plan) : ?>
            <?php if (empty($plan['id'])) { continue; } ?>
            <div class="pricing-card formal-card <?php echo !empty($plan['popular']) ? 'popular' : ''; ?>">

                <?php if (!empty($plan['popular'])) : ?>
                    <div class="popular-badge"><?php esc_html_e('Most Popular', 'zskeleton'); ?></div>
                <?php endif; ?>

                <div class="plan-header">
                    <h3 class="plan-name"><?php echo esc_html($plan['name'] ?? ''); ?></h3>
                    <?php
                    $cur_code = isset( $plan['currency'] ) ? $plan['currency'] : 'USD';
                    $cur_sym  = $cur_code;
                    $cur_name = $cur_code;
                    if ( class_exists( 'ZSkeleton_Currency_Labels' ) ) {
                        $cur_code = ZSkeleton_Currency_Labels::get_resolved_currency_code( $cur_code );
                        $cur_sym  = ZSkeleton_Currency_Labels::get_symbol( $cur_code );
                        $cur_name = ZSkeleton_Currency_Labels::get_public_name( $cur_code );
                    }
                    ?>
                    <div class="plan-price" title="<?php echo esc_attr( $cur_name ); ?>">
                        <span class="screen-reader-text"><?php echo esc_html( $cur_name ); ?></span>
                        <span class="currency" aria-hidden="true"><?php echo esc_html( $cur_sym ); ?></span>
                        <span class="amount"><?php echo esc_html( number_format( (float) ( $plan['price'] ?? 0 ), 0 ) ); ?></span>
                    </div>
                    <p class="plan-period"><?php esc_html_e('One-time Annual Payment', 'zskeleton'); ?></p>
                    <p class="plan-description"><?php echo esc_html($plan['description'] ?? ''); ?></p>
                </div>

                <div class="plan-features">
                    <ul>
                        <?php
                        $features = isset($plan['features']) && is_array($plan['features']) ? $plan['features'] : array();
                        foreach ($features as $feature) :
                            ?>
                            <li>
                                <span class="checkmark" aria-hidden="true">✓</span>
                                <?php echo esc_html($feature); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="plan-action">
                    <?php
                    $register_page   = zskeleton_get_page_url( 'register' );
                    $checkout_start  = zskeleton_get_membership_checkout_url( $plan['id'] ?? '' );
                    $subscribe_fallback = add_query_arg( 'plan', $plan['id'] ?? '', $register_page );
                    ?>
                    <?php if (is_user_logged_in()) : ?>
                        <?php
                        $user_id = get_current_user_id();
                        if ( class_exists( 'ZSkeleton_User_Profile_Fields' ) && ZSkeleton_User_Profile_Fields::user_has_active_membership( $user_id ) ) :
                            $current_type = ZSkeleton_User_Profile_Fields::get_user_membership_type( $user_id );
                            if ($current_type === ($plan['type'] ?? '')) :
                                ?>
                                <button type="button" class="btn" disabled>
                                    <?php esc_html_e('Current Plan', 'zskeleton'); ?>
                                </button>
                            <?php elseif (in_array($plan['type'] ?? '', array('corporate', 'organizational'), true) && $current_type === 'individual') : ?>
                                <?php if (!empty($plan['external_url'])) : ?>
                                    <a href="<?php echo esc_url($plan['external_url']); ?>" class="btn" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html($plan['button_text'] ?: __('Upgrade', 'zskeleton')); ?>
                                    </a>
                                <?php else : ?>
                                    <a href="<?php echo esc_url( $checkout_start ? $checkout_start : $subscribe_fallback ); ?>" class="btn">
                                        <?php echo esc_html($plan['button_text'] ?: __('Upgrade', 'zskeleton')); ?>
                                    </a>
                                <?php endif; ?>
                            <?php else : ?>
                                <?php if (!empty($plan['external_url'])) : ?>
                                    <a href="<?php echo esc_url($plan['external_url']); ?>" class="btn" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html($plan['button_text'] ?: __('Change Plan', 'zskeleton')); ?>
                                    </a>
                                <?php else : ?>
                                    <a href="<?php echo esc_url( $checkout_start ? $checkout_start : $subscribe_fallback ); ?>" class="btn">
                                        <?php echo esc_html($plan['button_text'] ?: __('Change Plan', 'zskeleton')); ?>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php else : ?>
                            <?php if (!empty($plan['external_url'])) : ?>
                                <a href="<?php echo esc_url($plan['external_url']); ?>" class="btn" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html($plan['button_text'] ?: __('Get Started', 'zskeleton')); ?>
                                </a>
                            <?php else : ?>
                                <a href="<?php echo esc_url( $checkout_start ? $checkout_start : $subscribe_fallback ); ?>" class="btn">
                                    <?php echo esc_html($plan['button_text'] ?: __('Get Started', 'zskeleton')); ?>
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else : ?>
                        <?php if (!empty($plan['external_url'])) : ?>
                            <a href="<?php echo esc_url($plan['external_url']); ?>" class="btn" target="_blank" rel="noopener noreferrer">
                                <?php echo esc_html($plan['button_text'] ?: __('Get Started', 'zskeleton')); ?>
                            </a>
                        <?php else : ?>
                            <a href="<?php echo esc_url( $checkout_start ? $checkout_start : $subscribe_fallback ); ?>" class="btn">
                                <?php echo esc_html($plan['button_text'] ?: __('Get Started', 'zskeleton')); ?>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
