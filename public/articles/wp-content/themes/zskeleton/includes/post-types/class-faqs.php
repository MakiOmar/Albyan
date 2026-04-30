<?php
/**
 * ZSkeleton FAQs Custom Post Type
 * 
 * Manages frequently asked questions with categories and ordering
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ZSkeleton_FAQs {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_filter('manage_zskeleton_faqs_posts_columns', array($this, 'add_admin_columns'));
        add_action('manage_zskeleton_faqs_posts_custom_column', array($this, 'show_admin_columns'), 10, 2);
        add_filter('manage_edit-zskeleton_faqs_sortable_columns', array($this, 'sortable_columns'));
        add_action('pre_get_posts', array($this, 'orderby_custom_column'));
    }

    /**
     * Register FAQ post type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('FAQs', 'Post Type General Name', 'zskeleton'),
            'singular_name'         => _x('FAQ', 'Post Type Singular Name', 'zskeleton'),
            'menu_name'             => __('FAQs', 'zskeleton'),
            'name_admin_bar'        => __('FAQ', 'zskeleton'),
            'archives'              => __('FAQ Archives', 'zskeleton'),
            'attributes'            => __('FAQ Attributes', 'zskeleton'),
            'parent_item_colon'     => __('Parent FAQ:', 'zskeleton'),
            'all_items'             => __('All FAQs', 'zskeleton'),
            'add_new_item'          => __('Add New FAQ', 'zskeleton'),
            'add_new'               => __('Add New', 'zskeleton'),
            'new_item'              => __('New FAQ', 'zskeleton'),
            'edit_item'             => __('Edit FAQ', 'zskeleton'),
            'update_item'           => __('Update FAQ', 'zskeleton'),
            'view_item'             => __('View FAQ', 'zskeleton'),
            'view_items'            => __('View FAQs', 'zskeleton'),
            'search_items'          => __('Search FAQs', 'zskeleton'),
            'not_found'             => __('Not found', 'zskeleton'),
            'not_found_in_trash'    => __('Not found in Trash', 'zskeleton'),
            'featured_image'        => __('Featured Image', 'zskeleton'),
            'set_featured_image'    => __('Set featured image', 'zskeleton'),
            'remove_featured_image' => __('Remove featured image', 'zskeleton'),
            'use_featured_image'    => __('Use as featured image', 'zskeleton'),
            'insert_into_item'      => __('Insert into FAQ', 'zskeleton'),
            'uploaded_to_this_item' => __('Uploaded to this FAQ', 'zskeleton'),
            'items_list'            => __('FAQs list', 'zskeleton'),
            'items_list_navigation' => __('FAQs list navigation', 'zskeleton'),
            'filter_items_list'     => __('Filter FAQs list', 'zskeleton'),
        );

        $args = array(
            'label'                 => __('FAQ', 'zskeleton'),
            'description'           => __('Frequently Asked Questions', 'zskeleton'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'author', 'revisions', 'page-attributes'),
            'taxonomies'            => array( 'zskeleton_faq_category', 'zskeleton_landing' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 25,
            'menu_icon'             => 'dashicons-editor-help',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
            'rewrite'               => array(
                'slug' => 'faq',
                'with_front' => false
            ),
        );

        register_post_type('zskeleton_faqs', $args);
    }

    /**
     * Register FAQ taxonomies
     */
    public function register_taxonomies() {
        // FAQ Categories
        $labels = array(
            'name'                       => _x('FAQ Categories', 'Taxonomy General Name', 'zskeleton'),
            'singular_name'              => _x('FAQ Category', 'Taxonomy Singular Name', 'zskeleton'),
            'menu_name'                  => __('Categories', 'zskeleton'),
            'all_items'                  => __('All Categories', 'zskeleton'),
            'parent_item'                => __('Parent Category', 'zskeleton'),
            'parent_item_colon'          => __('Parent Category:', 'zskeleton'),
            'new_item_name'              => __('New Category Name', 'zskeleton'),
            'add_new_item'               => __('Add New Category', 'zskeleton'),
            'edit_item'                  => __('Edit Category', 'zskeleton'),
            'update_item'                => __('Update Category', 'zskeleton'),
            'view_item'                  => __('View Category', 'zskeleton'),
            'separate_items_with_commas' => __('Separate categories with commas', 'zskeleton'),
            'add_or_remove_items'        => __('Add or remove categories', 'zskeleton'),
            'choose_from_most_used'      => __('Choose from the most used', 'zskeleton'),
            'popular_items'              => __('Popular Categories', 'zskeleton'),
            'search_items'               => __('Search Categories', 'zskeleton'),
            'not_found'                  => __('Not Found', 'zskeleton'),
            'no_terms'                   => __('No categories', 'zskeleton'),
            'items_list'                 => __('Categories list', 'zskeleton'),
            'items_list_navigation'      => __('Categories list navigation', 'zskeleton'),
        );

        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rewrite'                    => array(
                'slug' => 'faq-category',
                'with_front' => false
            ),
        );

        register_taxonomy('zskeleton_faq_category', array('zskeleton_faqs'), $args);
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'zskeleton_faq_settings',
            __('FAQ Settings', 'zskeleton'),
            array($this, 'faq_settings_meta_box'),
            'zskeleton_faqs',
            'side',
            'high'
        );
    }

    /**
     * FAQ settings meta box
     */
    public function faq_settings_meta_box($post) {
        wp_nonce_field('zskeleton_faq_meta_box', 'zskeleton_faq_nonce');
        
        $display_order = get_post_meta($post->ID, '_zskeleton_faq_order', true) ?: 0;
        $is_featured = get_post_meta($post->ID, '_zskeleton_faq_featured', true);
        $difficulty = get_post_meta($post->ID, '_zskeleton_faq_difficulty', true) ?: 'beginner';
        ?>
        <div class="zs-meta-fields zs-meta-fields--compact zs-meta-fields--panel">
            <div class="zs-meta-field">
                <label class="zs-meta-field__label" for="zskeleton_faq_order"><?php _e('Display Order', 'zskeleton'); ?></label>
                <input type="number"
                    id="zskeleton_faq_order"
                    name="zskeleton_faq_order"
                    class="widefat"
                    value="<?php echo esc_attr($display_order); ?>"
                    min="0"
                    step="1" />
                <p class="zs-meta-field__hint"><?php _e('Lower numbers appear first.', 'zskeleton'); ?></p>
            </div>
            <div class="zs-meta-field">
                <label class="zs-meta-field__label zs-meta-field__label--inline" for="zskeleton_faq_featured">
                    <input type="checkbox"
                        id="zskeleton_faq_featured"
                        name="zskeleton_faq_featured"
                        value="1"
                        <?php checked($is_featured, '1'); ?> />
                    <span><?php _e('Featured FAQ', 'zskeleton'); ?></span>
                </label>
                <p class="zs-meta-field__hint"><?php _e('Show in featured FAQ sections.', 'zskeleton'); ?></p>
            </div>
            <div class="zs-meta-field">
                <label class="zs-meta-field__label" for="zskeleton_faq_difficulty"><?php _e('Difficulty Level', 'zskeleton'); ?></label>
                <select id="zskeleton_faq_difficulty" name="zskeleton_faq_difficulty" class="widefat">
                    <option value="beginner" <?php selected($difficulty, 'beginner'); ?>><?php _e('Beginner', 'zskeleton'); ?></option>
                    <option value="intermediate" <?php selected($difficulty, 'intermediate'); ?>><?php _e('Intermediate', 'zskeleton'); ?></option>
                    <option value="advanced" <?php selected($difficulty, 'advanced'); ?>><?php _e('Advanced', 'zskeleton'); ?></option>
                </select>
            </div>
        </div>
        <?php
    }

    /**
     * Save meta boxes
     */
    public function save_meta_boxes($post_id) {
        // Verify nonce
        if (!isset($_POST['zskeleton_faq_nonce']) || !wp_verify_nonce($_POST['zskeleton_faq_nonce'], 'zskeleton_faq_meta_box')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check post type
        if (get_post_type($post_id) !== 'zskeleton_faqs') {
            return;
        }

        // Save display order
        if (isset($_POST['zskeleton_faq_order'])) {
            update_post_meta($post_id, '_zskeleton_faq_order', intval($_POST['zskeleton_faq_order']));
        }

        // Save featured status
        if (isset($_POST['zskeleton_faq_featured'])) {
            update_post_meta($post_id, '_zskeleton_faq_featured', '1');
        } else {
            delete_post_meta($post_id, '_zskeleton_faq_featured');
        }

        // Save difficulty
        if (isset($_POST['zskeleton_faq_difficulty'])) {
            $difficulty = sanitize_text_field($_POST['zskeleton_faq_difficulty']);
            if (in_array($difficulty, array('beginner', 'intermediate', 'advanced'))) {
                update_post_meta($post_id, '_zskeleton_faq_difficulty', $difficulty);
            }
        }
    }

    /**
     * Add admin columns
     */
    public function add_admin_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['faq_category'] = __('Category', 'zskeleton');
        $new_columns['faq_order'] = __('Order', 'zskeleton');
        $new_columns['faq_featured'] = __('Featured', 'zskeleton');
        $new_columns['faq_difficulty'] = __('Difficulty', 'zskeleton');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }

    /**
     * Show admin columns
     */
    public function show_admin_columns($column, $post_id) {
        switch ($column) {
            case 'faq_category':
                $terms = get_the_terms($post_id, 'zskeleton_faq_category');
                if (!empty($terms)) {
                    $term_names = wp_list_pluck($terms, 'name');
                    echo esc_html(implode(', ', $term_names));
                } else {
                    echo '—';
                }
                break;
                
            case 'faq_order':
                $order = get_post_meta($post_id, '_zskeleton_faq_order', true);
                echo $order ? esc_html($order) : '0';
                break;
                
            case 'faq_featured':
                $featured = get_post_meta($post_id, '_zskeleton_faq_featured', true);
                echo $featured ? '<span class="dashicons dashicons-star-filled" style="color: #ffb900;"></span>' : '—';
                break;
                
            case 'faq_difficulty':
                $difficulty = get_post_meta($post_id, '_zskeleton_faq_difficulty', true) ?: 'beginner';
                $colors = array(
                    'beginner' => '#46b450',
                    'intermediate' => '#ffb900', 
                    'advanced' => '#dc3232'
                );
                echo '<span style="color: ' . esc_attr($colors[$difficulty]) . '; font-weight: bold;">' . esc_html(ucfirst($difficulty)) . '</span>';
                break;
        }
    }

    /**
     * Make columns sortable
     */
    public function sortable_columns($columns) {
        $columns['faq_order'] = 'faq_order';
        $columns['faq_difficulty'] = 'faq_difficulty';
        return $columns;
    }

    /**
     * Handle custom column sorting
     */
    public function orderby_custom_column($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        $orderby = $query->get('orderby');

        if ('faq_order' === $orderby) {
            $query->set('meta_key', '_zskeleton_faq_order');
            $query->set('orderby', 'meta_value_num');
        } elseif ('faq_difficulty' === $orderby) {
            $query->set('meta_key', '_zskeleton_faq_difficulty');
            $query->set('orderby', 'meta_value');
        }
    }

    /**
     * Get FAQs by category
     */
    public static function get_faqs_by_category($category_slug = '', $limit = -1, $featured_only = false) {
        $args = array(
            'post_type' => 'zskeleton_faqs',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'meta_key' => '_zskeleton_faq_order',
            'orderby' => 'meta_value_num',
            'order' => 'ASC'
        );

        if (!empty($category_slug)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'zskeleton_faq_category',
                    'field' => 'slug',
                    'terms' => $category_slug,
                ),
            );
        }

        if ($featured_only) {
            $args['meta_query'] = array(
                array(
                    'key' => '_zskeleton_faq_featured',
                    'value' => '1',
                    'compare' => '='
                )
            );
        }

        return get_posts($args);
    }

    /**
     * Get FAQ categories
     */
    public static function get_faq_categories() {
        return get_terms(array(
            'taxonomy' => 'zskeleton_faq_category',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
    }
}

// FAQ post type initialized in functions.php
