<?php
/**
 * ZSkeleton Sitemap Status Admin
 * 
 * Admin page to check WordPress sitemap status and accessibility
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ZSkeleton_Sitemap_Status {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_sitemap_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Add sitemap status menu to admin
     */
    public function add_sitemap_menu() {
        add_submenu_page(
            'tools.php',
            __('Sitemap Status', 'zskeleton'),
            __('Sitemap Status', 'zskeleton'),
            'manage_options',
            'zskeleton-sitemap-status',
            array($this, 'render_sitemap_status_page')
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'tools_page_zskeleton-sitemap-status') {
            return;
        }

        $use_min  = (bool) get_option( 'zskeleton_use_minified_assets', true );
        $css_file = $use_min && is_readable( ZSkeleton_THEME_DIR . '/assets/css/admin-sitemap-status.min.css' )
            ? 'admin-sitemap-status.min.css'
            : 'admin-sitemap-status.css';
        $css_path = ZSkeleton_THEME_DIR . '/assets/css/' . $css_file;

        wp_enqueue_style(
            'zskeleton-sitemap-status',
            ZSkeleton_THEME_URL . '/assets/css/' . $css_file,
            array(),
            is_readable( $css_path ) ? (string) filemtime( $css_path ) : ZSkeleton_VERSION
        );
    }

    /**
     * Render sitemap status page
     */
    public function render_sitemap_status_page() {
        // Check if WordPress sitemaps are available
        $sitemaps_available = function_exists('wp_sitemaps_get_server');
        
        // Get sitemap server if available
        $sitemap_server = null;
        if ($sitemaps_available) {
            $sitemap_server = wp_sitemaps_get_server();
        }
        
        // Check if sitemaps are enabled
        $sitemaps_enabled = true;
        if ($sitemap_server) {
            $sitemaps_enabled = $sitemap_server->sitemaps_enabled();
        }
        
        // Get all registered post types
        $post_types = get_post_types(array('public' => true), 'objects');
        
        // ZSkeleton custom post types
        $zskeleton_post_types = array(
            'zskeleton_faqs'
        );
        
        // Get site URL
        $site_url = home_url('/');
        $main_sitemap_url = $site_url . 'wp-sitemap.xml';
        
        ?>
        <div class="wrap zskeleton-sitemap-status-wrap">
            <h1><?php _e('Sitemap Status', 'zskeleton'); ?></h1>
            
            <div class="zskeleton-sitemap-status-header">
                <p><?php _e('Monitor WordPress built-in sitemaps and check accessibility of all custom post type sitemaps.', 'zskeleton'); ?></p>
            </div>

            <?php
            // Main Status Section
            $this->render_main_status($sitemaps_available, $sitemaps_enabled, $main_sitemap_url);
            
            // WordPress Core Post Types Section
            $this->render_core_post_types_section($post_types, $site_url);
            
            // ZSkeleton Custom Post Types Section
            $this->render_custom_post_types_section($zskeleton_post_types, $site_url);
            
            // Taxonomies Section
            $this->render_taxonomies_section($site_url);
            
            // Users Section (excluded)
            // Note: Users are excluded from sitemaps - section removed
            
            // Quick Actions Section
            $this->render_quick_actions($main_sitemap_url);
            ?>
        </div>
        <?php
    }

    /**
     * Render main status section
     */
    private function render_main_status($sitemaps_available, $sitemaps_enabled, $main_sitemap_url) {
        ?>
        <div class="zskeleton-status-card">
            <h2><?php _e('WordPress Sitemaps Status', 'zskeleton'); ?></h2>
            
            <div class="status-grid">
                <div class="status-item">
                    <span class="status-label"><?php _e('Sitemaps Available:', 'zskeleton'); ?></span>
                    <span class="status-value <?php echo $sitemaps_available ? 'status-success' : 'status-error'; ?>">
                        <?php echo $sitemaps_available ? '✅ ' . __('Yes (WordPress 5.5+)', 'zskeleton') : '❌ ' . __('No (Upgrade WordPress)', 'zskeleton'); ?>
                    </span>
                </div>
                
                <div class="status-item">
                    <span class="status-label"><?php _e('Sitemaps Enabled:', 'zskeleton'); ?></span>
                    <span class="status-value <?php echo $sitemaps_enabled ? 'status-success' : 'status-warning'; ?>">
                        <?php echo $sitemaps_enabled ? '✅ ' . __('Enabled', 'zskeleton') : '⚠️ ' . __('Disabled', 'zskeleton'); ?>
                    </span>
                </div>
            </div>
            
            <div class="main-sitemap-url">
                <label><?php _e('Main Sitemap Index:', 'zskeleton'); ?></label>
                <div class="url-box">
                    <code><?php echo esc_url($main_sitemap_url); ?></code>
                    <a href="<?php echo esc_url($main_sitemap_url); ?>" target="_blank" class="button button-small">
                        <?php _e('View Sitemap', 'zskeleton'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render core post types section
     */
    private function render_core_post_types_section($post_types, $site_url) {
        $core_post_types = array('post', 'page');
        ?>
        <div class="zskeleton-status-card">
            <h2><?php _e('WordPress Core Post Types', 'zskeleton'); ?></h2>
            
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 20%;"><?php _e('Post Type', 'zskeleton'); ?></th>
                        <th style="width: 15%;"><?php _e('Published', 'zskeleton'); ?></th>
                        <th style="width: 50%;"><?php _e('Sitemap URL', 'zskeleton'); ?></th>
                        <th style="width: 15%;"><?php _e('Status', 'zskeleton'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($core_post_types as $post_type) {
                        if (!isset($post_types[$post_type])) {
                            continue;
                        }
                        
                        $post_type_obj = $post_types[$post_type];
                        $count = wp_count_posts($post_type)->publish;
                        $sitemap_url = $site_url . 'wp-sitemap-posts-' . $post_type . '-1.xml';
                        $status = $this->check_sitemap_accessible($sitemap_url);
                        
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($post_type_obj->label); ?></strong></td>
                            <td><?php echo number_format($count); ?></td>
                            <td>
                                <code style="font-size: 11px;"><?php echo esc_url($sitemap_url); ?></code>
                                <a href="<?php echo esc_url($sitemap_url); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">
                                    <?php _e('View', 'zskeleton'); ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                if ($status['accessible']) {
                                    echo '<span class="status-badge status-success">✅ ' . __('OK', 'zskeleton') . '</span>';
                                } else {
                                    echo '<span class="status-badge status-error">❌ ' . __('Error', 'zskeleton') . '</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render custom post types section
     */
    private function render_custom_post_types_section($zskeleton_post_types, $site_url) {
        ?>
        <div class="zskeleton-status-card">
            <h2><?php _e('ZSkeleton Custom Post Types', 'zskeleton'); ?></h2>
            
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 20%;"><?php _e('Post Type', 'zskeleton'); ?></th>
                        <th style="width: 15%;"><?php _e('Published', 'zskeleton'); ?></th>
                        <th style="width: 50%;"><?php _e('Sitemap URL', 'zskeleton'); ?></th>
                        <th style="width: 15%;"><?php _e('Status', 'zskeleton'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($zskeleton_post_types as $post_type) {
                        $post_type_obj = get_post_type_object($post_type);
                        
                        if (!$post_type_obj) {
                            continue;
                        }
                        
                        // Count published posts (WordPress sitemaps only include published posts)
                        $count = wp_count_posts($post_type);
                        $published_count = isset($count->publish) ? $count->publish : 0;
                        
                        // Count all statuses for reference
                        $pending_count = isset($count->pending) ? $count->pending : 0;
                        $draft_count = isset($count->draft) ? $count->draft : 0;
                        
                        // WordPress won't generate a sitemap file if there are 0 published posts
                        $sitemap_available = $published_count > 0;
                        
                        // Calculate number of sitemap files needed (2000 URLs per file)
                        $num_files = $sitemap_available ? ceil($published_count / 2000) : 0;
                        $num_files = max(1, $num_files); // At least 1 file if published posts exist
                        
                        // First sitemap URL
                        $sitemap_url = $site_url . 'wp-sitemap-posts-' . $post_type . '-1.xml';
                        $status = $sitemap_available ? $this->check_sitemap_accessible($sitemap_url) : array('accessible' => false, 'error' => __('No published posts - sitemap not generated', 'zskeleton'));
                        
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($post_type_obj->label); ?></strong></td>
                            <td>
                                <?php echo number_format($published_count); ?>
                                <?php if ($pending_count > 0 || $draft_count > 0) : ?>
                                    <br><small style="color: #d97706;">
                                        <?php
                                        $other_statuses = array();
                                        if ($pending_count > 0) {
                                            $other_statuses[] = sprintf(__('%d pending', 'zskeleton'), $pending_count);
                                        }
                                        if ($draft_count > 0) {
                                            $other_statuses[] = sprintf(__('%d draft', 'zskeleton'), $draft_count);
                                        }
                                        echo '(' . implode(', ', $other_statuses) . ')';
                                        ?>
                                    </small>
                                <?php endif; ?>
                                <?php if ($num_files > 1 && $sitemap_available) : ?>
                                    <br><small style="color: #666;"><?php printf(__('(%d files)', 'zskeleton'), $num_files); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code style="font-size: 11px;"><?php echo esc_url($sitemap_url); ?></code>
                                <?php if ($num_files > 1) : ?>
                                    <br><small><?php printf(__('(+%d more files if needed)', 'zskeleton'), $num_files - 1); ?></small>
                                <?php endif; ?>
                                <a href="<?php echo esc_url($sitemap_url); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">
                                    <?php _e('View', 'zskeleton'); ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                if ($status['accessible']) {
                                    echo '<span class="status-badge status-success">✅ ' . __('OK', 'zskeleton') . '</span>';
                                } else {
                                    if (!$sitemap_available && ($pending_count > 0 || $draft_count > 0)) {
                                        echo '<span class="status-badge" style="background: #fef3c7; color: #92400e;">⚠️ ' . __('No published posts', 'zskeleton') . '</span>';
                                        echo '<br><small style="color: #d97706;">' . __('WordPress only includes published posts in sitemaps. Publish posts to generate sitemap.', 'zskeleton') . '</small>';
                                    } else {
                                        echo '<span class="status-badge status-error">❌ ' . __('Error', 'zskeleton') . '</span>';
                                        if (!empty($status['error'])) {
                                            echo '<br><small style="color: #dc2626;">' . esc_html($status['error']) . '</small>';
                                        }
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render taxonomies section
     */
    private function render_taxonomies_section($site_url) {
        // WordPress core taxonomies
        $core_taxonomies = array(
            'category' => __('Categories', 'zskeleton'),
            'post_tag' => __('Tags', 'zskeleton'),
        );
        
        // ZSkeleton custom taxonomies
        $zskeleton_taxonomies = array(
            'zskeleton_faq_category' => __('FAQ Category', 'zskeleton'),
        );
        
        $all_taxonomies = array_merge($core_taxonomies, $zskeleton_taxonomies);
        
        ?>
        <div class="zskeleton-status-card">
            <h2><?php _e('Taxonomies', 'zskeleton'); ?></h2>
            
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 25%;"><?php _e('Taxonomy', 'zskeleton'); ?></th>
                        <th style="width: 15%;"><?php _e('Terms', 'zskeleton'); ?></th>
                        <th style="width: 45%;"><?php _e('Sitemap URL', 'zskeleton'); ?></th>
                        <th style="width: 15%;"><?php _e('Status', 'zskeleton'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($all_taxonomies as $taxonomy => $label) {
                        $taxonomy_obj = get_taxonomy($taxonomy);
                        
                        if (!$taxonomy_obj) {
                            continue;
                        }
                        
                        // Count terms
                        $term_count = wp_count_terms(array(
                            'taxonomy' => $taxonomy,
                            'hide_empty' => false,
                        ));
                        
                        if (is_wp_error($term_count)) {
                            $term_count = 0;
                        }
                        
                        // Sitemap URL
                        $sitemap_url = $site_url . 'wp-sitemap-taxonomies-' . $taxonomy . '-1.xml';
                        $status = $this->check_sitemap_accessible($sitemap_url);
                        
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($label); ?></strong></td>
                            <td><?php echo number_format($term_count); ?></td>
                            <td>
                                <code style="font-size: 11px;"><?php echo esc_url($sitemap_url); ?></code>
                                <a href="<?php echo esc_url($sitemap_url); ?>" target="_blank" class="button button-small" style="margin-left: 8px;">
                                    <?php _e('View', 'zskeleton'); ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                if ($status['accessible']) {
                                    echo '<span class="status-badge status-success">✅ ' . __('OK', 'zskeleton') . '</span>';
                                } else {
                                    echo '<span class="status-badge status-error">❌ ' . __('Error', 'zskeleton') . '</span>';
                                    if (!empty($status['error'])) {
                                        echo '<br><small style="color: #dc2626;">' . esc_html($status['error']) . '</small>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }


    /**
     * Render quick actions section
     */
    private function render_quick_actions($main_sitemap_url) {
        ?>
        <div class="zskeleton-status-card">
            <h2><?php _e('Quick Actions', 'zskeleton'); ?></h2>
            
            <div class="quick-actions-grid">
                <div class="action-item">
                    <h3><?php _e('Submit to Search Engines', 'zskeleton'); ?></h3>
                    <p><?php _e('Submit your sitemap to Google Search Console and Bing Webmaster Tools.', 'zskeleton'); ?></p>
                    <div class="action-buttons">
                        <a href="https://search.google.com/search-console" target="_blank" class="button button-primary">
                            <?php _e('Google Search Console', 'zskeleton'); ?>
                        </a>
                        <a href="https://www.bing.com/webmasters" target="_blank" class="button button-primary">
                            <?php _e('Bing Webmaster Tools', 'zskeleton'); ?>
                        </a>
                    </div>
                    <p class="description" style="margin-top: 10px;">
                        <?php _e('Sitemap URL to submit:', 'zskeleton'); ?>
                        <code><?php echo esc_url($main_sitemap_url); ?></code>
                    </p>
                </div>
                
                <div class="action-item">
                    <h3><?php _e('Documentation', 'zskeleton'); ?></h3>
                    <p><?php _e('View detailed documentation about WordPress sitemaps.', 'zskeleton'); ?></p>
                    <div class="action-buttons">
                        <a href="<?php echo admin_url('admin.php?page=zskeleton-sitemap-status&view=docs'); ?>" class="button">
                            <?php _e('View Sitemap README', 'zskeleton'); ?>
                        </a>
                        <a href="https://wordpress.org/support/article/sitemaps/" target="_blank" class="button">
                            <?php _e('WordPress Docs', 'zskeleton'); ?>
                        </a>
                    </div>
                </div>
                
                <div class="action-item">
                    <h3><?php _e('Check robots.txt', 'zskeleton'); ?></h3>
                    <p><?php _e('Verify that your sitemap is referenced in robots.txt.', 'zskeleton'); ?></p>
                    <a href="<?php echo home_url('/robots.txt'); ?>" target="_blank" class="button">
                        <?php _e('View robots.txt', 'zskeleton'); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Check if sitemap URL is accessible
     */
    private function check_sitemap_accessible($url) {
        // Use wp_remote_get to check if URL is accessible
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'sslverify' => false,
        ));
        
        if (is_wp_error($response)) {
            return array(
                'accessible' => false,
                'error' => $response->get_error_message()
            );
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        
        if ($status_code === 200) {
            return array(
                'accessible' => true,
                'status_code' => $status_code
            );
        } else {
            return array(
                'accessible' => false,
                'error' => sprintf(__('HTTP %d', 'zskeleton'), $status_code)
            );
        }
    }
}

// Initialize the admin class
new ZSkeleton_Sitemap_Status();

