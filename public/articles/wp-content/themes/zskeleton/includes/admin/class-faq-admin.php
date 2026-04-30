<?php
/**
 * ZSkeleton FAQ Admin Management
 * 
 * Admin interface for managing FAQ settings and bulk operations
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ZSkeleton_FAQ_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_zskeleton_bulk_faq_action', array($this, 'handle_bulk_faq_action'));
        add_action('wp_ajax_zskeleton_import_default_faqs', array($this, 'import_default_faqs'));
        add_action('wp_ajax_zskeleton_reorder_faqs', array($this, 'reorder_faqs'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=zskeleton_faqs',
            __('FAQ Settings', 'zskeleton'),
            __('Settings', 'zskeleton'),
            'manage_options',
            'zskeleton-faq-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('zskeleton_faq_settings', 'zskeleton_faq_per_page');
        register_setting('zskeleton_faq_settings', 'zskeleton_faq_show_categories');
        register_setting('zskeleton_faq_settings', 'zskeleton_faq_show_search');
        register_setting('zskeleton_faq_settings', 'zskeleton_faq_accordion_style');
        register_setting('zskeleton_faq_settings', 'zskeleton_faq_featured_limit');
        register_setting('zskeleton_faq_settings', 'zskeleton_faq_show_difficulty');
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'zskeleton-faq-settings') !== false || 
            strpos($hook, 'zskeleton_faqs') !== false) {
            
            // Check if minified assets should be used
            $use_minified = get_theme_mod('zskeleton_use_minified_assets', true);
            // Theme version + Unix time (cache bust for admin JS/CSS).
            $asset_version = ZSkeleton_VERSION . '.' . time();
            
            // Enqueue FAQ admin JavaScript - use minified or original based on setting
            $faq_admin_js_file = $use_minified ? 'faq-admin.min.js' : 'faq-admin.js';
            wp_enqueue_script(
                'zskeleton-faq-admin',
                get_template_directory_uri() . '/assets/js/' . $faq_admin_js_file,
                array('jquery', 'jquery-ui-sortable'),
                $asset_version,
                true
            );

            // Enqueue FAQ admin styles - use minified or original based on setting
            $faq_admin_css_file = $use_minified ? 'faq-admin.min.css' : 'faq-admin.css';
            wp_enqueue_style(
                'zskeleton-faq-admin',
                get_template_directory_uri() . '/assets/css/' . $faq_admin_css_file,
                array(),
                $asset_version
            );

            wp_localize_script('zskeleton-faq-admin', 'zskeleton_faq_admin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('zskeleton_faq_admin_nonce'),
                'strings' => array(
                    'confirm_bulk_delete' => __('Are you sure you want to delete the selected FAQs?', 'zskeleton'),
                    'confirm_import' => __('This will create default FAQ categories and sample FAQs. Continue?', 'zskeleton'),
                    'success' => __('Action completed successfully!', 'zskeleton'),
                    'error' => __('An error occurred. Please try again.', 'zskeleton'),
                )
            ));
        }
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (isset($_POST['submit'])) {
            // Handle form submission
            if (wp_verify_nonce($_POST['_wpnonce'], 'zskeleton_faq_settings')) {
                $this->save_settings();
                echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'zskeleton') . '</p></div>';
            }
        }

        $faq_per_page = get_option('zskeleton_faq_per_page', 10);
        $show_categories = get_option('zskeleton_faq_show_categories', '1');
        $show_search = get_option('zskeleton_faq_show_search', '1');
        $accordion_style = get_option('zskeleton_faq_accordion_style', 'modern');
        $featured_limit = get_option('zskeleton_faq_featured_limit', 5);
        $show_difficulty = get_option('zskeleton_faq_show_difficulty', '1');
        ?>
        <div class="wrap">
            <h1><?php _e('FAQ Settings', 'zskeleton'); ?></h1>
            
            <div class="zskeleton-faq-admin-layout">
                <!-- Settings Form -->
                <div class="faq-settings-panel">
                    <h2><?php _e('Display Settings', 'zskeleton'); ?></h2>
                    <form method="post" action="">
                        <?php wp_nonce_field('zskeleton_faq_settings'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('FAQs Per Page', 'zskeleton'); ?></th>
                                <td>
                                    <input type="number" 
                                           name="zskeleton_faq_per_page" 
                                           value="<?php echo esc_attr($faq_per_page); ?>" 
                                           min="1" 
                                           max="100" 
                                           class="small-text" />
                                    <p class="description"><?php _e('Number of FAQs to display per page', 'zskeleton'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php _e('Show Categories Filter', 'zskeleton'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="zskeleton_faq_show_categories" 
                                               value="1" 
                                               <?php checked($show_categories, '1'); ?> />
                                        <?php _e('Display category filter on FAQ page', 'zskeleton'); ?>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php _e('Show Search Box', 'zskeleton'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="zskeleton_faq_show_search" 
                                               value="1" 
                                               <?php checked($show_search, '1'); ?> />
                                        <?php _e('Display search box on FAQ page', 'zskeleton'); ?>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php _e('Show Difficulty Levels', 'zskeleton'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="zskeleton_faq_show_difficulty" 
                                               value="1" 
                                               <?php checked($show_difficulty, '1'); ?> />
                                        <?php _e('Display difficulty indicators', 'zskeleton'); ?>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php _e('Accordion Style', 'zskeleton'); ?></th>
                                <td>
                                    <select name="zskeleton_faq_accordion_style">
                                        <option value="modern" <?php selected($accordion_style, 'modern'); ?>><?php _e('Modern', 'zskeleton'); ?></option>
                                        <option value="classic" <?php selected($accordion_style, 'classic'); ?>><?php _e('Classic', 'zskeleton'); ?></option>
                                        <option value="minimal" <?php selected($accordion_style, 'minimal'); ?>><?php _e('Minimal', 'zskeleton'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><?php _e('Featured FAQs Limit', 'zskeleton'); ?></th>
                                <td>
                                    <input type="number" 
                                           name="zskeleton_faq_featured_limit" 
                                           value="<?php echo esc_attr($featured_limit); ?>" 
                                           min="1" 
                                           max="20" 
                                           class="small-text" />
                                    <p class="description"><?php _e('Maximum number of featured FAQs to show', 'zskeleton'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <?php submit_button(); ?>
                    </form>
                </div>
                
                <!-- Quick Actions -->
                <div class="faq-actions-panel">
                    <h2><?php _e('Quick Actions', 'zskeleton'); ?></h2>
                    
                    <div class="faq-action-card">
                        <h3><?php _e('Import Default FAQs', 'zskeleton'); ?></h3>
                        <p><?php _e('Create default FAQ categories and sample questions to get started quickly.', 'zskeleton'); ?></p>
                        <button type="button" class="button button-secondary" id="import-default-faqs">
                            <?php _e('Import Default FAQs', 'zskeleton'); ?>
                        </button>
                    </div>
                    
                    <div class="faq-action-card">
                        <h3><?php _e('Bulk Actions', 'zskeleton'); ?></h3>
                        <p><?php _e('Perform actions on multiple FAQs at once.', 'zskeleton'); ?></p>
                        <div class="bulk-actions-form">
                            <select id="bulk-action-select">
                                <option value=""><?php _e('Select Action', 'zskeleton'); ?></option>
                                <option value="feature"><?php _e('Mark as Featured', 'zskeleton'); ?></option>
                                <option value="unfeature"><?php _e('Remove Featured', 'zskeleton'); ?></option>
                                <option value="publish"><?php _e('Publish', 'zskeleton'); ?></option>
                                <option value="draft"><?php _e('Move to Draft', 'zskeleton'); ?></option>
                            </select>
                            <button type="button" class="button" id="apply-bulk-action">
                                <?php _e('Apply', 'zskeleton'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <div class="faq-action-card">
                        <h3><?php _e('FAQ Statistics', 'zskeleton'); ?></h3>
                        <?php $this->display_faq_stats(); ?>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .zskeleton-faq-admin-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .faq-settings-panel,
        .faq-actions-panel {
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .faq-action-card {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .faq-action-card h3 {
            margin-top: 0;
            color: #1e3a8a;
        }

        .bulk-actions-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .faq-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .faq-stat-item {
            text-align: center;
            padding: 10px;
            background: #fff;
            border-radius: 4px;
            border: 1px solid #e1e5e9;
        }

        .faq-stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #1e3a8a;
            display: block;
        }

        .faq-stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }

        @media (max-width: 1024px) {
            .zskeleton-faq-admin-layout {
                grid-template-columns: 1fr;
            }
        }
        </style>
        <?php
    }

    /**
     * Save settings
     */
    private function save_settings() {
        if (isset($_POST['zskeleton_faq_per_page'])) {
            update_option('zskeleton_faq_per_page', intval($_POST['zskeleton_faq_per_page']));
        }
        
        update_option('zskeleton_faq_show_categories', isset($_POST['zskeleton_faq_show_categories']) ? '1' : '0');
        update_option('zskeleton_faq_show_search', isset($_POST['zskeleton_faq_show_search']) ? '1' : '0');
        update_option('zskeleton_faq_show_difficulty', isset($_POST['zskeleton_faq_show_difficulty']) ? '1' : '0');
        
        if (isset($_POST['zskeleton_faq_accordion_style'])) {
            $style = sanitize_text_field($_POST['zskeleton_faq_accordion_style']);
            if (in_array($style, array('modern', 'classic', 'minimal'))) {
                update_option('zskeleton_faq_accordion_style', $style);
            }
        }
        
        if (isset($_POST['zskeleton_faq_featured_limit'])) {
            update_option('zskeleton_faq_featured_limit', intval($_POST['zskeleton_faq_featured_limit']));
        }
    }

    /**
     * Display FAQ statistics
     */
    private function display_faq_stats() {
        $total_faqs = wp_count_posts('zskeleton_faqs');
        $featured_faqs = get_posts(array(
            'post_type' => 'zskeleton_faqs',
            'meta_key' => '_zskeleton_faq_featured',
            'meta_value' => '1',
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        $categories = get_terms(array(
            'taxonomy' => 'zskeleton_faq_category',
            'hide_empty' => false
        ));
        ?>
        <div class="faq-stats-grid">
            <div class="faq-stat-item">
                <span class="faq-stat-number"><?php echo esc_html($total_faqs->publish ?? 0); ?></span>
                <span class="faq-stat-label"><?php _e('Published', 'zskeleton'); ?></span>
            </div>
            <div class="faq-stat-item">
                <span class="faq-stat-number"><?php echo esc_html($total_faqs->draft ?? 0); ?></span>
                <span class="faq-stat-label"><?php _e('Drafts', 'zskeleton'); ?></span>
            </div>
            <div class="faq-stat-item">
                <span class="faq-stat-number"><?php echo esc_html(count($featured_faqs)); ?></span>
                <span class="faq-stat-label"><?php _e('Featured', 'zskeleton'); ?></span>
            </div>
            <div class="faq-stat-item">
                <span class="faq-stat-number"><?php echo esc_html(count($categories)); ?></span>
                <span class="faq-stat-label"><?php _e('Categories', 'zskeleton'); ?></span>
            </div>
        </div>
        <?php
    }

    /**
     * Handle bulk FAQ actions
     */
    public function handle_bulk_faq_action() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'zskeleton_faq_admin_nonce')) {
            wp_send_json_error(__('Security check failed.', 'zskeleton'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'zskeleton'));
        }

        $action = sanitize_text_field($_POST['action_type']);
        $faq_ids = array_map('intval', $_POST['faq_ids']);

        if (empty($faq_ids)) {
            wp_send_json_error(__('No FAQs selected.', 'zskeleton'));
        }

        $success_count = 0;

        foreach ($faq_ids as $faq_id) {
            switch ($action) {
                case 'feature':
                    update_post_meta($faq_id, '_zskeleton_faq_featured', '1');
                    $success_count++;
                    break;
                    
                case 'unfeature':
                    delete_post_meta($faq_id, '_zskeleton_faq_featured');
                    $success_count++;
                    break;
                    
                case 'publish':
                    wp_update_post(array(
                        'ID' => $faq_id,
                        'post_status' => 'publish'
                    ));
                    $success_count++;
                    break;
                    
                case 'draft':
                    wp_update_post(array(
                        'ID' => $faq_id,
                        'post_status' => 'draft'
                    ));
                    $success_count++;
                    break;
            }
        }

        wp_send_json_success(sprintf(__('Action applied to %d FAQs.', 'zskeleton'), $success_count));
    }

    /**
     * Import default FAQs
     */
    public function import_default_faqs() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'zskeleton_faq_admin_nonce')) {
            wp_send_json_error(__('Security check failed.', 'zskeleton'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'zskeleton'));
        }

        $created_count = 0;

        // Create default categories
        $categories = array(
            'general' => __('General Questions', 'zskeleton'),
            'membership' => __('Membership', 'zskeleton'),
            'technical' => __('Technical Support', 'zskeleton'),
            'resources' => __('Resources & Guides', 'zskeleton'),
            'policies' => __('Policies & Guidelines', 'zskeleton')
        );

        foreach ($categories as $slug => $name) {
            if (!term_exists($slug, 'zskeleton_faq_category')) {
                wp_insert_term($name, 'zskeleton_faq_category', array('slug' => $slug));
            }
        }

        // Create default FAQs
        $default_faqs = $this->get_default_faq_data();

        foreach ($default_faqs as $faq_data) {
            // Check if FAQ already exists
            $existing = get_posts(array(
                'post_type' => 'zskeleton_faqs',
                'title' => $faq_data['title'],
                'post_status' => 'any',
                'numberposts' => 1
            ));

            if (empty($existing)) {
                $post_id = wp_insert_post(array(
                    'post_title' => $faq_data['title'],
                    'post_content' => $faq_data['content'],
                    'post_type' => 'zskeleton_faqs',
                    'post_status' => 'publish',
                    'menu_order' => $faq_data['order']
                ));

                if ($post_id && !is_wp_error($post_id)) {
                    // Set category
                    wp_set_post_terms($post_id, array($faq_data['category']), 'zskeleton_faq_category');
                    
                    // Set meta data
                    update_post_meta($post_id, '_zskeleton_faq_order', $faq_data['order']);
                    update_post_meta($post_id, '_zskeleton_faq_difficulty', $faq_data['difficulty']);
                    
                    if ($faq_data['featured']) {
                        update_post_meta($post_id, '_zskeleton_faq_featured', '1');
                    }
                    
                    $created_count++;
                }
            }
        }

        wp_send_json_success(sprintf(__('Created %d default FAQs and categories.', 'zskeleton'), $created_count));
    }

    /**
     * Get default FAQ data
     */
    private function get_default_faq_data() {
        return array(
            array(
                'title' => __('What is ZSkeleton?', 'zskeleton'),
                'content' => __('ZSkeleton is a flexible WordPress base theme for building content-driven and membership-enabled websites.', 'zskeleton'),
                'category' => 'general',
                'difficulty' => 'beginner',
                'order' => 1,
                'featured' => true
            ),
            array(
                'title' => __('Who can join ZSkeleton?', 'zskeleton'),
                'content' => __('ZSkeleton membership is open to individuals, teams, and organizations that need access to premium resources and member-only content.', 'zskeleton'),
                'category' => 'membership',
                'difficulty' => 'beginner',
                'order' => 2,
                'featured' => true
            ),
            array(
                'title' => __('Is membership really a one-time payment?', 'zskeleton'),
                'content' => __('Yes! ZSkeleton membership requires only a single payment that provides lifetime access to all resources and benefits.', 'zskeleton'),
                'category' => 'membership',
                'difficulty' => 'beginner',
                'order' => 3,
                'featured' => true
            ),
            array(
                'title' => __('What\'s included in membership?', 'zskeleton'),
                'content' => __('Members get access to premium resources, practical tools, implementation guides, and exclusive content.', 'zskeleton'),
                'category' => 'membership',
                'difficulty' => 'beginner',
                'order' => 4,
                'featured' => false
            ),
            array(
                'title' => __('How do I access member-only content?', 'zskeleton'),
                'content' => __('Once your membership is approved, you\'ll have automatic access to all restricted content when logged in to your account.', 'zskeleton'),
                'category' => 'technical',
                'difficulty' => 'intermediate',
                'order' => 5,
                'featured' => false
            ),
            array(
                'title' => __('Can I contribute to ZSkeleton research?', 'zskeleton'),
                'content' => __('Yes. Members can contribute articles, guides, and curated resources to help grow the shared knowledge base.', 'zskeleton'),
                'category' => 'resources',
                'difficulty' => 'intermediate',
                'order' => 6,
                'featured' => false
            ),
            array(
                'title' => __('What are the ZSkeleton content guidelines?', 'zskeleton'),
                'content' => __('Our guidelines provide a clear framework for quality, consistency, accessibility, and responsible publishing.', 'zskeleton'),
                'category' => 'policies',
                'difficulty' => 'intermediate',
                'order' => 7,
                'featured' => false
            ),
            array(
                'title' => __('How can my organization request partner listing?', 'zskeleton'),
                'content' => __('Organizations can request partner listing by meeting the publication criteria and completing the review process.', 'zskeleton'),
                'category' => 'policies',
                'difficulty' => 'advanced',
                'order' => 8,
                'featured' => false
            )
        );
    }
}

// FAQ admin initialized in functions.php
