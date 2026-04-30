<?php
/**
 * ZSkeleton Content Restrictions Admin
 * 
 * Admin interface for managing content access restrictions
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ZSkeleton_Content_Restrictions {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_restrictions_menu'));
        add_action('admin_init', array($this, 'register_restriction_settings'));
        add_action('add_meta_boxes', array($this, 'add_restriction_meta_boxes'), 10, 2);
        add_action('save_post', array($this, 'save_restriction_meta_boxes'));
        add_action('save_post_page', array($this, 'clear_restrictions_for_editor_only_legal_pages'), 5, 2);
        add_filter('manage_posts_columns', array($this, 'add_restriction_column'));
        add_action('manage_posts_custom_column', array($this, 'show_restriction_column'), 10, 2);
        add_action('wp_ajax_zskeleton_bulk_restrict', array($this, 'handle_bulk_restriction'));
        add_action('wp_ajax_zskeleton_update_free_articles', array($this, 'update_free_articles'));
        add_filter('allowed_options', array($this, 'add_shared_options_to_restriction_page'), 20);
    }

    /**
     * Add restrictions menu
     */
    public function add_restrictions_menu() {
        add_submenu_page(
            'zskeleton-memberships',
            __('Content Restrictions', 'zskeleton'),
            __('Content Restrictions', 'zskeleton'),
            'manage_options',
            'zskeleton-content-restrictions',
            array($this, 'render_restrictions_page')
        );
    }

    /**
     * Register restriction settings
     */
    public function register_restriction_settings() {
        register_setting('zskeleton_restriction_settings', 'zskeleton_restricted_post_types');
        register_setting('zskeleton_restriction_settings', 'zskeleton_free_article_ids');
        register_setting(
            'zskeleton_restriction_settings',
            'zskeleton_restriction_message',
            array(
                'type'              => 'string',
                'sanitize_callback' => 'wp_kses_post',
                'default'           => __('This content is available exclusively to ZSkeleton members. Join our membership to access comprehensive resources and professional content.', 'zskeleton'),
            )
        );
        register_setting('zskeleton_restriction_settings', 'zskeleton_excerpt_length');
    }

    /**
     * Allow saving shared keys on this page without a second register_setting()
     * (avoids overwriting zskeleton_theme_settings registration for the same options).
     *
     * @param array<string, string[]> $allowed Allowed option names keyed by option group.
     * @return array<string, string[]>
     */
    public function add_shared_options_to_restriction_page($allowed) {
        if (!is_array($allowed)) {
            $allowed = array();
        }
        if (!isset($allowed['zskeleton_restriction_settings']) || !is_array($allowed['zskeleton_restriction_settings'])) {
            $allowed['zskeleton_restriction_settings'] = array();
        }
        foreach (array('zskeleton_free_articles_limit', 'zskeleton_restriction_mode') as $key) {
            if (!in_array($key, $allowed['zskeleton_restriction_settings'], true)) {
                $allowed['zskeleton_restriction_settings'][] = $key;
            }
        }
        return $allowed;
    }

    /**
     * Add restriction meta boxes
     *
     * @param string  $post_type Post type of the current edit screen.
     * @param WP_Post $post      Post being edited.
     */
    public function add_restriction_meta_boxes($post_type, $post = null) {
        $post_types = array('post', 'page', 'zskeleton_faqs');
        if (!in_array($post_type, $post_types, true)) {
            return;
        }

        if ('page' === $post_type && $post instanceof WP_Post && function_exists('zskeleton_page_is_editor_only_legal_template') && zskeleton_page_is_editor_only_legal_template($post->ID)) {
            return;
        }

        add_meta_box(
            'zskeleton_content_restrictions',
            __('ZSkeleton Content Restrictions', 'zskeleton'),
            array($this, 'restriction_meta_box_callback'),
            $post_type,
            'side',
            'high'
        );
    }

    /**
     * Strip restriction meta when saving editor-only legal pages (refund/terms general templates).
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     */
    public function clear_restrictions_for_editor_only_legal_pages($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($post_id)) {
            return;
        }
        if (!$post instanceof WP_Post || 'page' !== $post->post_type) {
            return;
        }
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
        if (!function_exists('zskeleton_page_is_editor_only_legal_template') || !zskeleton_page_is_editor_only_legal_template($post_id)) {
            return;
        }

        delete_post_meta($post_id, '_zskeleton_member_only');
        delete_post_meta($post_id, '_zskeleton_access_level');
        delete_post_meta($post_id, '_zskeleton_sample_content');
        delete_post_meta($post_id, '_zskeleton_custom_restriction_message');
    }

    /**
     * Restriction meta box callback
     */
    public function restriction_meta_box_callback($post) {
        wp_nonce_field('zskeleton_restriction_meta_box', 'zskeleton_restriction_nonce');

        $member_only = get_post_meta($post->ID, '_zskeleton_member_only', true);
        $access_level = get_post_meta($post->ID, '_zskeleton_access_level', true);
        $sample_content = get_post_meta($post->ID, '_zskeleton_sample_content', true);
        $custom_message = get_post_meta($post->ID, '_zskeleton_custom_restriction_message', true);
        ?>
        <div class="zs-meta-fields zs-meta-fields--compact zs-meta-fields--panel">
            <div class="zs-meta-field">
                <label class="zs-meta-field__label zs-meta-field__label--inline" for="zskeleton_member_only">
                    <input type="checkbox" id="zskeleton_member_only" name="zskeleton_member_only" value="1" <?php checked($member_only, '1'); ?> />
                    <span><?php _e('Restrict to Members Only', 'zskeleton'); ?></span>
                </label>
            </div>

            <div class="zs-meta-field">
                <label class="zs-meta-field__label" for="zskeleton_access_level"><?php _e('Access Level', 'zskeleton'); ?></label>
                <select name="zskeleton_access_level" id="zskeleton_access_level" class="widefat">
                    <option value="" <?php selected($access_level, ''); ?>><?php _e('All Members', 'zskeleton'); ?></option>
                    <option value="individual" <?php selected($access_level, 'individual'); ?>><?php _e('Individual Members+', 'zskeleton'); ?></option>
                    <option value="organizational" <?php selected($access_level, 'organizational'); ?>><?php _e('Organizational Only', 'zskeleton'); ?></option>
                </select>
            </div>

            <div class="zs-meta-field">
                <label class="zs-meta-field__label zs-meta-field__label--inline" for="zskeleton_sample_content">
                    <input type="checkbox" id="zskeleton_sample_content" name="zskeleton_sample_content" value="1" <?php checked($sample_content, '1'); ?> />
                    <span><?php _e('Show as Sample Content', 'zskeleton'); ?></span>
                </label>
                <p class="zs-meta-field__hint"><?php _e('Display this content to non-members as a preview.', 'zskeleton'); ?></p>
            </div>

            <div class="zs-meta-field">
                <label class="zs-meta-field__label" for="zskeleton_custom_restriction_message_ed"><?php _e('Custom Restriction Message', 'zskeleton'); ?></label>
                <?php
                if (function_exists('zskeleton_render_meta_wysiwyg')) {
                    zskeleton_render_meta_wysiwyg(
                        'zskeleton_custom_restriction_message_ed',
                        'zskeleton_custom_restriction_message',
                        is_string($custom_message) ? $custom_message : '',
                        array('textarea_rows' => 5)
                    );
                } else {
                    ?>
                    <textarea name="zskeleton_custom_restriction_message" id="zskeleton_custom_restriction_message" rows="3" class="widefat"><?php echo esc_textarea($custom_message); ?></textarea>
                    <?php
                }
                ?>
                <p class="zs-meta-field__hint"><?php _e('Leave empty to use the default message.', 'zskeleton'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Save restriction meta boxes
     */
    public function save_restriction_meta_boxes($post_id) {
        if (!isset($_POST['zskeleton_restriction_nonce']) || !wp_verify_nonce($_POST['zskeleton_restriction_nonce'], 'zskeleton_restriction_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save restriction settings
        $member_only = isset($_POST['zskeleton_member_only']) ? '1' : '0';
        update_post_meta($post_id, '_zskeleton_member_only', $member_only);

        if (isset($_POST['zskeleton_access_level'])) {
            update_post_meta($post_id, '_zskeleton_access_level', sanitize_text_field($_POST['zskeleton_access_level']));
        }

        $sample_content = isset($_POST['zskeleton_sample_content']) ? '1' : '0';
        update_post_meta($post_id, '_zskeleton_sample_content', $sample_content);

        if (isset($_POST['zskeleton_custom_restriction_message'])) {
            update_post_meta($post_id, '_zskeleton_custom_restriction_message', wp_kses_post(wp_unslash($_POST['zskeleton_custom_restriction_message'])));
        }
    }

    /**
     * Add restriction column to posts list
     */
    public function add_restriction_column($columns) {
        $columns['zskeleton_restrictions'] = __('Access', 'zskeleton');
        return $columns;
    }

    /**
     * Show restriction column content
     */
    public function show_restriction_column($column, $post_id) {
        if ($column !== 'zskeleton_restrictions') {
            return;
        }

        $post_type = get_post_type($post_id);
        $restricted_post_types = get_option('zskeleton_restricted_post_types', array());
        $member_only = get_post_meta($post_id, '_zskeleton_member_only', true);
        $access_level = get_post_meta($post_id, '_zskeleton_access_level', true);

        $restrictions = array();

        // Check if post type is restricted by default
        if (in_array($post_type, $restricted_post_types)) {
            $restrictions[] = '<span class="restriction-badge type-restricted">Type Restricted</span>';
        }

        // Check if post is individually restricted
        if ($member_only) {
            $restrictions[] = '<span class="restriction-badge member-only">Members Only</span>';
        }

        // Check access level
        if (!empty($access_level)) {
            $restrictions[] = '<span class="restriction-badge access-level">' . ucfirst($access_level) . '+</span>';
        }

        // Check if it's sample content
        $sample_content = get_post_meta($post_id, '_zskeleton_sample_content', true);
        if ($sample_content) {
            $restrictions[] = '<span class="restriction-badge sample">Sample</span>';
        }

        if (empty($restrictions)) {
            echo '<span class="restriction-badge public">Public</span>';
        } else {
            echo implode('<br>', $restrictions);
        }

        // Add inline styles
        echo '<style>
        .restriction-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            white-space: nowrap;
        }
        
        .restriction-badge.public {
            background: #d1fae5;
            color: #065f46;
        }
        
        .restriction-badge.member-only,
        .restriction-badge.type-restricted {
            background: #fef3c7;
            color: #92400e;
        }
        
        .restriction-badge.access-level {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .restriction-badge.sample {
            background: #e0e7ff;
            color: #5b21b6;
        }
        </style>';
    }

    /**
     * Render restrictions page
     */
    public function render_restrictions_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Content Restrictions', 'zskeleton'); ?></h1>

            <div class="zskeleton-restrictions-layout">
                <!-- Settings Form -->
                <div class="restrictions-settings">
                    <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
                        <?php settings_fields('zskeleton_restriction_settings'); ?>
                        <?php
                        // Same as theme settings: options.php must redirect back here if referer validation fails.
                        $zskeleton_restrictions_return = admin_url('admin.php?page=zskeleton-content-restrictions');
                        ?>
                        <input type="hidden" name="zskeleton_options_return_url" value="<?php echo esc_attr(esc_url($zskeleton_restrictions_return)); ?>" />

                        <div class="settings-section">
                            <h2><?php _e('Global Restriction Settings', 'zskeleton'); ?></h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Restricted Post Types', 'zskeleton'); ?></th>
                                    <td>
                                        <?php
                                        $restricted_types = get_option('zskeleton_restricted_post_types', array());
                                        $post_types = get_post_types(array('public' => true), 'objects');
                                        
                                        foreach ($post_types as $post_type) {
                                            if ($post_type->name === 'attachment') continue;
                                            
                                            printf(
                                                '<label><input type="checkbox" name="zskeleton_restricted_post_types[]" value="%s" %s /> %s</label><br>',
                                                esc_attr($post_type->name),
                                                checked(in_array($post_type->name, $restricted_types), true, false),
                                                esc_html($post_type->labels->name)
                                            );
                                        }
                                        ?>
                                        <p class="description"><?php _e('Post types that require membership by default', 'zskeleton'); ?></p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php _e('Restriction Mode', 'zskeleton'); ?></th>
                                    <td>
                                        <?php $restriction_mode = get_option('zskeleton_restriction_mode', 'partial'); ?>
                                        <label>
                                            <input type="radio" name="zskeleton_restriction_mode" value="partial" <?php checked($restriction_mode, 'partial'); ?> />
                                            <?php _e('Partial - Show preview with membership notice', 'zskeleton'); ?>
                                        </label><br>
                                        <label>
                                            <input type="radio" name="zskeleton_restriction_mode" value="full" <?php checked($restriction_mode, 'full'); ?> />
                                            <?php _e('Full - Hide restricted content completely', 'zskeleton'); ?>
                                        </label>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php _e('Free Articles Limit', 'zskeleton'); ?></th>
                                    <td>
                                        <input type="number" name="zskeleton_free_articles_limit" 
                                               value="<?php echo esc_attr(get_option('zskeleton_free_articles_limit', 3)); ?>" 
                                               min="0" max="20" class="small-text" />
                                        <p class="description"><?php _e('Number of blog articles accessible to non-members', 'zskeleton'); ?></p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php _e('Excerpt Length', 'zskeleton'); ?></th>
                                    <td>
                                        <input type="number" name="zskeleton_excerpt_length" 
                                               value="<?php echo esc_attr(get_option('zskeleton_excerpt_length', 30)); ?>" 
                                               min="10" max="100" class="small-text" />
                                        <span><?php _e('words', 'zskeleton'); ?></span>
                                        <p class="description"><?php _e('Length of excerpts shown to non-members', 'zskeleton'); ?></p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php _e('Default Restriction Message', 'zskeleton'); ?></th>
                                    <td>
                                        <?php
                                        $def_msg = get_option(
                                            'zskeleton_restriction_message',
                                            __('This content is available exclusively to ZSkeleton members. Join our membership to access comprehensive resources and professional content.', 'zskeleton')
                                        );
                                        if (function_exists('zskeleton_render_meta_wysiwyg')) {
                                            zskeleton_render_meta_wysiwyg(
                                                'zskeleton_restriction_message_ed',
                                                'zskeleton_restriction_message',
                                                is_string($def_msg) ? $def_msg : '',
                                                array('textarea_rows' => 6)
                                            );
                                        } else {
                                            ?>
                                            <textarea name="zskeleton_restriction_message" rows="4" class="large-text"><?php echo esc_textarea($def_msg); ?></textarea>
                                            <?php
                                        }
                                        ?>
                                        <p class="description"><?php _e('Default message shown to non-members for restricted content', 'zskeleton'); ?></p>
                                    </td>
                                </tr>
                            </table>

                            <?php submit_button(); ?>
                        </div>
                    </form>
                </div>

                <!-- Free Articles Management -->
                <div class="free-articles-management">
                    <h2><?php _e('Free Articles Management', 'zskeleton'); ?></h2>
                    <?php $this->render_free_articles_manager(); ?>
                </div>

                <!-- Bulk Actions -->
                <div class="bulk-restrictions">
                    <h2><?php _e('Bulk Actions', 'zskeleton'); ?></h2>
                    <?php $this->render_bulk_actions(); ?>
                </div>

                <!-- Restrictions Overview -->
                <div class="restrictions-overview">
                    <h2><?php _e('Restrictions Overview', 'zskeleton'); ?></h2>
                    <?php $this->render_restrictions_overview(); ?>
                </div>
            </div>
        </div>

        <style>
        .zskeleton-restrictions-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .restrictions-settings {
            grid-column: 1 / -1;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .free-articles-management,
        .bulk-restrictions,
        .restrictions-overview {
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .free-articles-management {
            grid-column: 1;
        }

        .bulk-restrictions,
        .restrictions-overview {
            grid-column: 2;
        }

        @media (max-width: 1024px) {
            .zskeleton-restrictions-layout {
                grid-template-columns: 1fr;
            }
            
            .restrictions-settings,
            .free-articles-management,
            .bulk-restrictions,
            .restrictions-overview {
                grid-column: 1;
            }
        }
        </style>
        <?php
    }

    /**
     * Render free articles manager
     */
    private function render_free_articles_manager() {
        $free_article_ids = get_option('zskeleton_free_article_ids', array());
        $free_limit = get_option('zskeleton_free_articles_limit', 3);
        
        // Get all published posts
        $all_posts = get_posts(array(
            'numberposts' => 50,
            'post_type' => 'post',
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ));

        ?>
        <div class="free-articles-selector">
            <p><?php printf(__('Select up to %d articles to be freely accessible to non-members:', 'zskeleton'), $free_limit); ?></p>
            
            <form id="free-articles-form">
                <div class="articles-list">
                    <?php foreach ($all_posts as $post): ?>
                        <label class="article-option">
                            <input type="checkbox" name="free_articles[]" value="<?php echo $post->ID; ?>" 
                                   <?php checked(in_array($post->ID, $free_article_ids)); ?> />
                            <span class="article-title"><?php echo esc_html($post->post_title); ?></span>
                            <span class="article-date"><?php echo get_the_date('M j, Y', $post); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <p>
                    <button type="button" class="button button-primary" onclick="updateFreeArticles()">
                        <?php _e('Update Free Articles', 'zskeleton'); ?>
                    </button>
                </p>
            </form>
        </div>

        <style>
        .articles-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            background: #fafafa;
        }

        .article-option {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }

        .article-option:hover {
            background: #f0f0f0;
        }

        .article-title {
            flex: 1;
            font-weight: 500;
            margin-left: 8px;
        }

        .article-date {
            color: #666;
            font-size: 0.85rem;
        }
        </style>

        <script>
        function updateFreeArticles() {
            const form = document.getElementById('free-articles-form');
            const formData = new FormData(form);
            formData.append('action', 'zskeleton_update_free_articles');
            formData.append('nonce', '<?php echo wp_create_nonce('zskeleton_update_free_articles'); ?>');

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php esc_js(_e('Free articles updated successfully!', 'zskeleton')); ?>');
                } else {
                    alert('<?php esc_js(_e('Error updating free articles.', 'zskeleton')); ?>');
                }
            });
        }

        // Limit selection to free articles limit
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[name="free_articles[]"]');
            const limit = <?php echo intval($free_limit); ?>;
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const checked = document.querySelectorAll('input[name="free_articles[]"]:checked');
                    if (checked.length >= limit) {
                        checkboxes.forEach(cb => {
                            if (!cb.checked) {
                                cb.disabled = true;
                            }
                        });
                    } else {
                        checkboxes.forEach(cb => {
                            cb.disabled = false;
                        });
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Render bulk actions
     */
    private function render_bulk_actions() {
        ?>
        <div class="bulk-actions-panel">
            <h3><?php _e('Bulk Restriction Actions', 'zskeleton'); ?></h3>
            
            <div class="action-group">
                <h4><?php _e('Restrict All Posts in Category', 'zskeleton'); ?></h4>
                <select id="category-selector">
                    <option value=""><?php _e('Select Category', 'zskeleton'); ?></option>
                    <?php
                    $categories = get_categories();
                    foreach ($categories as $category) {
                        printf('<option value="%d">%s (%d posts)</option>', 
                            $category->term_id, 
                            esc_html($category->name), 
                            $category->count
                        );
                    }
                    ?>
                </select>
                <button type="button" class="button" onclick="bulkRestrictCategory()">
                    <?php _e('Restrict Category', 'zskeleton'); ?>
                </button>
            </div>

            <div class="action-group">
                <h4><?php _e('Restrict Posts by Date Range', 'zskeleton'); ?></h4>
                <input type="date" id="date-from" placeholder="<?php esc_attr_e('From Date', 'zskeleton'); ?>">
                <input type="date" id="date-to" placeholder="<?php esc_attr_e('To Date', 'zskeleton'); ?>">
                <button type="button" class="button" onclick="bulkRestrictDateRange()">
                    <?php _e('Restrict Date Range', 'zskeleton'); ?>
                </button>
            </div>

            <div class="action-group">
                <h4><?php _e('Remove All Restrictions', 'zskeleton'); ?></h4>
                <p><small><?php _e('Warning: This will remove individual post restrictions (not post type restrictions)', 'zskeleton'); ?></small></p>
                <button type="button" class="button button-secondary" onclick="removeAllRestrictions()">
                    <?php _e('Remove All Restrictions', 'zskeleton'); ?>
                </button>
            </div>
        </div>

        <style>
        .action-group {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 6px;
        }

        .action-group h4 {
            margin-top: 0;
            color: #1e3a8a;
        }

        .action-group input,
        .action-group select {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        </style>

        <script>
        function bulkRestrictCategory() {
            const categoryId = document.getElementById('category-selector').value;
            if (!categoryId) {
                alert('<?php esc_js(_e('Please select a category.', 'zskeleton')); ?>');
                return;
            }
            
            if (!confirm('<?php esc_js(_e('Restrict all posts in this category to members only?', 'zskeleton')); ?>')) {
                return;
            }
            
            performBulkAction('restrict_category', {category_id: categoryId});
        }

        function bulkRestrictDateRange() {
            const dateFrom = document.getElementById('date-from').value;
            const dateTo = document.getElementById('date-to').value;
            
            if (!dateFrom || !dateTo) {
                alert('<?php esc_js(_e('Please select both from and to dates.', 'zskeleton')); ?>');
                return;
            }
            
            if (!confirm('<?php esc_js(_e('Restrict all posts in this date range to members only?', 'zskeleton')); ?>')) {
                return;
            }
            
            performBulkAction('restrict_date_range', {date_from: dateFrom, date_to: dateTo});
        }

        function removeAllRestrictions() {
            if (!confirm('<?php esc_js(_e('Remove all individual post restrictions? This cannot be undone.', 'zskeleton')); ?>')) {
                return;
            }
            
            performBulkAction('remove_all_restrictions', {});
        }

        function performBulkAction(action, params) {
            const data = {
                action: 'zskeleton_bulk_restrict',
                nonce: '<?php echo wp_create_nonce('zskeleton_bulk_restrict'); ?>',
                bulk_action: action,
                ...params
            };
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.data);
                    location.reload();
                } else {
                    alert('<?php esc_js(_e('Error performing bulk action.', 'zskeleton')); ?>');
                }
            });
        }
        </script>
        <?php
    }

    /**
     * Render restrictions overview
     */
    private function render_restrictions_overview() {
        $stats = class_exists( 'ZSkeleton_Access_Control' )
            ? ZSkeleton_Access_Control::get_restriction_statistics()
            : array(
                'restricted_posts'   => 0,
                'restricted_by_type' => 0,
                'free_articles'      => 0,
                'total_restricted'   => 0,
            );
        
        ?>
        <div class="restrictions-stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo esc_html($stats['restricted_posts']); ?></div>
                <div class="stat-label"><?php _e('Individually Restricted', 'zskeleton'); ?></div>
            </div>
            
            <div class="stat-item">
                <div class="stat-number"><?php echo esc_html($stats['restricted_by_type']); ?></div>
                <div class="stat-label"><?php _e('Restricted by Type', 'zskeleton'); ?></div>
            </div>
            
            <div class="stat-item">
                <div class="stat-number"><?php echo esc_html($stats['free_articles']); ?></div>
                <div class="stat-label"><?php _e('Free Articles', 'zskeleton'); ?></div>
            </div>
            
            <div class="stat-item total">
                <div class="stat-number"><?php echo esc_html($stats['total_restricted']); ?></div>
                <div class="stat-label"><?php _e('Total Restricted', 'zskeleton'); ?></div>
            </div>
        </div>

        <style>
        .restrictions-stats {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 6px;
        }

        .stat-item.total {
            background: #1e3a8a;
            color: white;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.85rem;
            opacity: 0.8;
        }
        </style>
        <?php
    }

    /**
     * Handle bulk restriction actions
     */
    public function handle_bulk_restriction() {
        check_ajax_referer('zskeleton_bulk_restrict', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'zskeleton'));
        }

        $bulk_action = sanitize_text_field($_POST['bulk_action']);
        
        switch ($bulk_action) {
            case 'restrict_category':
                $this->bulk_restrict_category();
                break;
                
            case 'restrict_date_range':
                $this->bulk_restrict_date_range();
                break;
                
            case 'remove_all_restrictions':
                $this->remove_all_restrictions();
                break;
                
            default:
                wp_send_json_error(__('Invalid bulk action.', 'zskeleton'));
        }
    }

    /**
     * Bulk restrict category
     */
    private function bulk_restrict_category() {
        $category_id = intval($_POST['category_id']);
        
        $posts = get_posts(array(
            'category' => $category_id,
            'numberposts' => -1,
            'post_status' => 'publish'
        ));

        $count = 0;
        foreach ($posts as $post) {
            update_post_meta($post->ID, '_zskeleton_member_only', '1');
            $count++;
        }

        wp_send_json_success(sprintf(__('Restricted %d posts in the selected category.', 'zskeleton'), $count));
    }

    /**
     * Bulk restrict date range
     */
    private function bulk_restrict_date_range() {
        $date_from = sanitize_text_field($_POST['date_from']);
        $date_to = sanitize_text_field($_POST['date_to']);
        
        $posts = get_posts(array(
            'date_query' => array(
                array(
                    'after' => $date_from,
                    'before' => $date_to,
                    'inclusive' => true,
                ),
            ),
            'numberposts' => -1,
            'post_status' => 'publish'
        ));

        $count = 0;
        foreach ($posts as $post) {
            update_post_meta($post->ID, '_zskeleton_member_only', '1');
            $count++;
        }

        wp_send_json_success(sprintf(__('Restricted %d posts in the selected date range.', 'zskeleton'), $count));
    }

    /**
     * Remove all restrictions
     */
    private function remove_all_restrictions() {
        global $wpdb;
        
        $result = $wpdb->delete(
            $wpdb->postmeta,
            array('meta_key' => '_zskeleton_member_only'),
            array('%s')
        );

        wp_send_json_success(sprintf(__('Removed restrictions from %d posts.', 'zskeleton'), $result));
    }

    /**
     * Update free articles
     */
    public function update_free_articles() {
        check_ajax_referer('zskeleton_update_free_articles', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'zskeleton'));
        }

        $free_articles = array();
        if (isset($_POST['free_articles']) && is_array($_POST['free_articles'])) {
            $free_articles = array_map('intval', $_POST['free_articles']);
        }

        update_option('zskeleton_free_article_ids', $free_articles);
        
        wp_send_json_success(__('Free articles updated successfully.', 'zskeleton'));
    }
}

// Content restrictions initialized in functions.php
