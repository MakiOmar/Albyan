<?php
/**
 * BACKUP: Comparison Table Section for Membership Plans
 * 
 * This file contains the comparison table code that was removed from page-memberships.php
 * Keep this for future use if the comparison table needs to be restored.
 * 
 * To restore: Copy the content between the markers and paste it back into page-memberships.php
 * between the pricing cards section and the FAQ section.
 */

// START OF COMPARISON TABLE CODE - COPY FROM HERE
?>

<!-- Comparison Table -->
<?php if (count($plans) > 1): ?>
    <section class="comparison-section">
        <h2>Compare Membership Plans</h2>
        <div class="comparison-table-wrapper">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>Features</th>
                        <?php foreach ($plans as $plan): ?>
                            <th class="plan-column">
                                <?php echo esc_html($plan['name']); ?>
                                <?php if ($plan['popular']): ?>
                                    <span class="popular-badge-small">Popular</span>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr class="price-row">
                        <td><strong>Price</strong></td>
                        <?php foreach ($plans as $plan): ?>
                            <td class="plan-column">
                                <strong><?php echo esc_html($plan['currency'] . ' ' . number_format($plan['price'], 0)); ?></strong>
                                <br><small>One-time</small>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    
                    <?php
                    // Get all unique features across all plans
                    $all_features = array();
                    foreach ($plans as $plan) {
                        $all_features = array_merge($all_features, $plan['features']);
                    }
                    $all_features = array_unique($all_features);
                    
                    foreach ($all_features as $feature):
                    ?>
                        <tr>
                            <td><?php echo esc_html($feature); ?></td>
                            <?php foreach ($plans as $plan): ?>
                                <td class="plan-column">
                                    <?php if (in_array($feature, $plan['features'])): ?>
                                        <span class="check">✓</span>
                                    <?php else: ?>
                                        <span class="cross">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    
                    <tr class="action-row">
                        <td></td>
                        <?php foreach ($plans as $plan): ?>
                            <td class="plan-column">
                                <?php if (is_user_logged_in()): ?>
                                    <?php 
                                    $user_id = get_current_user_id();
                                    if ( class_exists( 'ZSkeleton_User_Profile_Fields' ) && ZSkeleton_User_Profile_Fields::user_has_active_membership( $user_id ) ) :
                                        $current_type = ZSkeleton_User_Profile_Fields::get_user_membership_type( $user_id );
                                        if ($current_type === $plan['type']):
                                    ?>
                                        <button class="btn btn-secondary" disabled>Current</button>
                                    <?php else: ?>
                                        <?php if (!empty($plan['external_url'])): ?>
                                            <a href="<?php echo esc_url($plan['external_url']); ?>" class="btn btn-primary" target="_blank" rel="noopener">
                                                <?php echo $plan['type'] === 'organizational' ? 'Upgrade' : 'Switch'; ?>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo esc_url(add_query_arg('plan', $plan['id'], zskeleton_get_page_url('register'))); ?>" class="btn btn-primary">
                                                <?php echo $plan['type'] === 'organizational' ? 'Upgrade' : 'Switch'; ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php else: ?>
                                        <?php if (!empty($plan['external_url'])): ?>
                                            <a href="<?php echo esc_url($plan['external_url']); ?>" class="btn btn-primary" target="_blank" rel="noopener">
                                                Get Started
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo esc_url(add_query_arg('plan', $plan['id'], zskeleton_get_page_url('register'))); ?>" class="btn btn-primary">
                                                Get Started
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if (!empty($plan['external_url'])): ?>
                                        <a href="<?php echo esc_url($plan['external_url']); ?>" class="btn btn-primary" target="_blank" rel="noopener">
                                            Choose Plan
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url(add_query_arg('plan', $plan['id'], zskeleton_get_page_url('register'))); ?>" class="btn btn-primary">
                                            Choose Plan
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php
// END OF COMPARISON TABLE CODE - COPY UNTIL HERE

/**
 * CSS STYLES FOR COMPARISON TABLE - ALSO BACKED UP
 * 
 * Add these styles to style.css if restoring the comparison table:
 */

/*
.comparison-table-wrapper {
    overflow-x: auto;
    margin-bottom: 40px;
}

.comparison-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.comparison-table th,
.comparison-table td {
    padding: 20px;
    text-align: left;
    border-bottom: 1px solid var(--border-light);
}

.comparison-table th {
    background: var(--background-light);
    font-weight: 600;
    color: var(--primary-blue);
}

.comparison-table .plan-column {
    text-align: center;
    min-width: 200px;
}

.comparison-table .price-row td {
    font-size: 1.125rem;
    background: var(--background-light);
}

.comparison-table .action-row td {
    padding: 25px 20px;
    text-align: center;
}

.comparison-table .check {
    color: var(--success-green);
    font-weight: 600;
    font-size: 1.25rem;
}

.comparison-table .cross {
    color: var(--text-muted);
    font-size: 1.25rem;
}

.popular-badge-small {
    display: inline-block;
    background: var(--accent-orange);
    color: white;
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 4px;
    margin-left: 8px;
    font-weight: 500;
}

@media (max-width: 768px) {
    .comparison-table-wrapper {
        margin: 0 -20px;
    }
    
    .comparison-table th,
    .comparison-table td {
        padding: 15px 10px;
    }
    
    .comparison-table .plan-column {
        min-width: 150px;
    }
}
*/
?>
