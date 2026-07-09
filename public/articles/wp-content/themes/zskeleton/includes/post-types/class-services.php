<?php
/**
 * ZSkeleton Services Custom Post Type
 *
 * Services with featured image (thumbnail), optional icon image, title, excerpt, and content.
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Services CPT and admin UI.
 */
class ZSkeleton_Services {

    const POST_TYPE = 'zskeleton_services';

    /**
     * Attachment ID for the service icon (distinct from featured image).
     */
    const META_ICON_ID = '_zskeleton_service_icon_id';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', array($this, 'add_admin_columns'));
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array($this, 'show_admin_columns'), 10, 2);
    }

    /**
     * Register Services post type.
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Services', 'Post Type General Name', 'zskeleton'),
            'singular_name'         => _x('Service', 'Post Type Singular Name', 'zskeleton'),
            'menu_name'             => __('Services', 'zskeleton'),
            'name_admin_bar'        => __('Service', 'zskeleton'),
            'archives'              => __('Service Archives', 'zskeleton'),
            'attributes'            => __('Service Attributes', 'zskeleton'),
            'parent_item_colon'     => __('Parent Service:', 'zskeleton'),
            'all_items'             => __('All Services', 'zskeleton'),
            'add_new_item'          => __('Add New Service', 'zskeleton'),
            'add_new'               => __('Add New', 'zskeleton'),
            'new_item'              => __('New Service', 'zskeleton'),
            'edit_item'             => __('Edit Service', 'zskeleton'),
            'update_item'           => __('Update Service', 'zskeleton'),
            'view_item'             => __('View Service', 'zskeleton'),
            'view_items'            => __('View Services', 'zskeleton'),
            'search_items'          => __('Search Services', 'zskeleton'),
            'not_found'             => __('Not found', 'zskeleton'),
            'not_found_in_trash'    => __('Not found in Trash', 'zskeleton'),
            'featured_image'        => __('Featured Image', 'zskeleton'),
            'set_featured_image'    => __('Set featured image', 'zskeleton'),
            'remove_featured_image' => __('Remove featured image', 'zskeleton'),
            'use_featured_image'    => __('Use as featured image', 'zskeleton'),
            'insert_into_item'      => __('Insert into service', 'zskeleton'),
            'uploaded_to_this_item' => __('Uploaded to this service', 'zskeleton'),
            'items_list'            => __('Services list', 'zskeleton'),
            'items_list_navigation' => __('Services list navigation', 'zskeleton'),
            'filter_items_list'     => __('Filter services list', 'zskeleton'),
        );

        $args = array(
            'label'                 => __('Service', 'zskeleton'),
            'description'           => __('Site services', 'zskeleton'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes'),
            'taxonomies'            => array( 'zskeleton_landing' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 26,
            'menu_icon'             => 'dashicons-portfolio',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
            'rewrite'               => array(
                'slug'       => 'services',
                'with_front' => false,
            ),
        );

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Enqueue media and icon picker on service edit screens.
     *
     * @param string $hook_suffix Current admin page.
     */
    public function enqueue_admin_assets($hook_suffix) {
        if (!in_array($hook_suffix, array('post.php', 'post-new.php'), true)) {
            return;
        }

        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || self::POST_TYPE !== $screen->post_type) {
            return;
        }

        wp_enqueue_media();
        $use_minified = (bool) get_option( 'zskeleton_use_minified_assets', true );
        $file         = $use_minified && is_readable( ZSkeleton_THEME_DIR . '/assets/js/admin-service-icon.min.js' )
            ? 'admin-service-icon.min.js'
            : 'admin-service-icon.js';
        $path         = ZSkeleton_THEME_DIR . '/assets/js/' . $file;

        wp_enqueue_script(
            'zskeleton-admin-service-icon',
            ZSkeleton_THEME_URL . '/assets/js/' . $file,
            array('jquery', 'media-editor'),
            is_readable( $path ) ? (string) filemtime( $path ) : ZSkeleton_VERSION,
            true
        );

        wp_localize_script(
            'zskeleton-admin-service-icon',
            'zsSkeletonServiceIcon',
            array(
                'frameTitle'  => __('Choose service icon', 'zskeleton'),
                'frameButton' => __('Use as icon', 'zskeleton'),
            )
        );
    }

    /**
     * Register meta box for icon image.
     */
    public function add_meta_boxes() {
        add_meta_box(
            'zskeleton_service_icon',
            __('Service icon', 'zskeleton'),
            array($this, 'render_icon_meta_box'),
            self::POST_TYPE,
            'side',
            'high'
        );
    }

    /**
     * Icon meta box markup.
     *
     * @param WP_Post $post Post object.
     */
    public function render_icon_meta_box($post) {
        wp_nonce_field('zskeleton_service_save_icon', 'zskeleton_service_icon_nonce');

        $icon_id = absint(get_post_meta($post->ID, self::META_ICON_ID, true));
        $icon_url = '';
        if ($icon_id) {
            $icon_url = wp_get_attachment_image_url($icon_id, 'thumbnail');
        }
        ?>
        <div class="zs-meta-fields zs-meta-fields--compact zs-meta-fields--panel">
            <p class="description"><?php esc_html_e('Optional smaller image or symbol shown next to the title in listings. The featured image is the main thumbnail.', 'zskeleton'); ?></p>
            <input type="hidden" id="zs-service-icon-id" name="zskeleton_service_icon_id" value="<?php echo esc_attr((string) $icon_id); ?>" />
            <div id="zs-service-icon-preview" class="zs-service-icon-preview" style="margin: 8px 0;">
                <?php if ($icon_url) : ?>
                    <img src="<?php echo esc_url($icon_url); ?>" alt="" style="max-width: 80px; height: auto; display: block;" />
                <?php endif; ?>
            </div>
            <p>
                <button type="button" class="button" id="zs-service-icon-select"><?php esc_html_e('Select icon', 'zskeleton'); ?></button>
                <button type="button" class="button" id="zs-service-icon-remove" <?php echo $icon_id ? '' : ' style="display:none;"'; ?>><?php esc_html_e('Remove icon', 'zskeleton'); ?></button>
            </p>
        </div>
        <?php
    }

    /**
     * Save icon meta.
     *
     * @param int $post_id Post ID.
     */
    public function save_meta_boxes($post_id) {
        if (!isset($_POST['zskeleton_service_icon_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['zskeleton_service_icon_nonce'])), 'zskeleton_service_save_icon')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (get_post_type($post_id) !== self::POST_TYPE) {
            return;
        }

        if (!isset($_POST['zskeleton_service_icon_id'])) {
            delete_post_meta($post_id, self::META_ICON_ID);
            return;
        }

        $raw = sanitize_text_field(wp_unslash($_POST['zskeleton_service_icon_id']));
        if ('' === $raw || '0' === $raw) {
            delete_post_meta($post_id, self::META_ICON_ID);
            return;
        }

        $aid = absint($raw);
        if ($aid < 1) {
            delete_post_meta($post_id, self::META_ICON_ID);
            return;
        }

        if ('attachment' !== get_post_type($aid)) {
            return;
        }

        $mime = get_post_mime_type($aid);
        if ($mime && 0 !== strpos($mime, 'image/')) {
            return;
        }

        update_post_meta($post_id, self::META_ICON_ID, $aid);
    }

    /**
     * Admin list columns.
     *
     * @param array<string,string> $columns Columns.
     * @return array<string,string>
     */
    public function add_admin_columns($columns) {
        $new = array();
        foreach ($columns as $key => $label) {
            $new[ $key ] = $label;
            if ('title' === $key) {
                $new['zs_service_thumb'] = __('Thumbnail', 'zskeleton');
                $new['zs_service_icon']  = __('Icon', 'zskeleton');
            }
        }
        return $new;
    }

    /**
     * Admin list column output.
     *
     * @param string $column Column key.
     * @param int    $post_id Post ID.
     */
    public function show_admin_columns($column, $post_id) {
        if ('zs_service_thumb' === $column) {
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, array(50, 50));
            } else {
                echo '—';
            }
            return;
        }

        if ('zs_service_icon' === $column) {
            $id = self::get_icon_attachment_id($post_id);
            if ($id) {
                echo wp_get_attachment_image($id, array(40, 40));
            } else {
                echo '—';
            }
        }
    }

    /**
     * Stored icon attachment ID.
     *
     * @param int $post_id Post ID.
     * @return int Attachment ID or 0.
     */
    public static function get_icon_attachment_id($post_id) {
        $id = get_post_meta($post_id, self::META_ICON_ID, true);
        return $id ? absint($id) : 0;
    }

    /**
     * Icon image HTML or empty string.
     *
     * @param int    $post_id Post ID.
     * @param string $size    Image size.
     * @param array  $attr    Attributes for wp_get_attachment_image.
     * @return string
     */
    public static function get_icon_html($post_id, $size = 'thumbnail', $attr = array()) {
        $aid = self::get_icon_attachment_id($post_id);
        if (!$aid) {
            return '';
        }
        return wp_get_attachment_image($aid, $size, false, $attr);
    }

    /**
     * Query published services.
     *
     * @param int $limit Posts per page, -1 for all.
     * @return WP_Post[]
     */
    public static function get_services($limit = -1) {
        return get_posts(
            array(
                'post_type'      => self::POST_TYPE,
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            )
        );
    }
}
