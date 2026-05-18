<?php
/**
 * ZSkeleton Theme Settings
 * 
 * Main theme settings panel and customization options
 *
 * @package ZSkeleton_Theme
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class ZSkeleton_Theme_Settings {

    /**
     * Prevent duplicate hook registration if the class is instantiated twice.
     *
     * @var bool
     */
    private static $hooks_registered = false;

    /**
     * Settings API group for Appearance → ZSkeleton Settings.
     *
     * Every option shown on that screen must be registered with
     * register_setting( self::OPTION_GROUP, 'option_name', $args ) so it is included in saves.
     */
    public const OPTION_GROUP = 'zskeleton_theme_settings';

    /**
     * Constructor
     */
    public function __construct() {
        if (self::$hooks_registered) {
            return;
        }
        self::$hooks_registered = true;

        add_action('admin_menu', array($this, 'add_theme_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_post_zskeleton_clear_update_cache', array($this, 'handle_clear_update_cache'));
        add_action('admin_post_zskeleton_set_default_auth_pages', array($this, 'handle_set_default_auth_pages'));
        add_action('admin_post_zskeleton_install_common_pages', array($this, 'handle_install_common_pages'));
        add_action('admin_post_zskeleton_save_theme_settings', array($this, 'handle_admin_post_save_theme_settings'));
        add_action('admin_notices', array($this, 'render_clear_cache_notice'));
        add_action('admin_notices', array($this, 'render_auth_pages_default_notice'));
        add_action('admin_notices', array($this, 'render_common_pages_install_notice'));
        add_filter('wp_redirect', array($this, 'fix_settings_api_redirect'), 999, 2);

        // AJAX handlers
        add_action('wp_ajax_zskeleton_bulk_action', array($this, 'handle_bulk_action'));
        add_action('wp_ajax_zskeleton_export_settings', array($this, 'export_settings'));
        add_action('wp_ajax_zskeleton_import_settings', array($this, 'import_settings'));
        add_action('wp_ajax_zskeleton_reset_settings', array($this, 'reset_settings'));
        add_action('wp_ajax_zskeleton_get_statistics', array($this, 'get_statistics'));
    }

    /**
     * When the HTTP referer is missing or rejected, options.php redirects using the
     * current REQUEST_URI, which leaves users on options.php. Send them back to the
     * correct ZSkeleton settings screen instead.
     *
     * @param string $location Redirect target.
     * @param int    $status   HTTP status code.
     * @return string
     */
    public function fix_settings_api_redirect($location, $status) {
        if (empty($_SERVER['REQUEST_METHOD']) || 'POST' !== strtoupper(wp_unslash($_SERVER['REQUEST_METHOD']))) {
            return $location;
        }
        if (empty($_POST['action']) || 'update' !== $_POST['action'] || empty($_POST['option_page'])) {
            return $location;
        }
        $loc = (string) $location;
        if (false === strpos($loc, 'settings-updated')) {
            return $location;
        }
        // When wp_get_referer() is empty or rejected, options.php redirects using REQUEST_URI,
        // so users stay on options.php (All Settings). Match any Location that still targets options.php.
        if (false === stripos($loc, 'options.php')) {
            return $location;
        }

        $option_page = sanitize_text_field(wp_unslash($_POST['option_page']));
        $targets = apply_filters(
            'zskeleton_settings_api_redirect_targets',
            array(
                self::OPTION_GROUP => admin_url('themes.php?page=zskeleton-theme-settings'),
                'zskeleton_restriction_settings' => admin_url('admin.php?page=zskeleton-content-restrictions'),
                'zskeleton_membership_settings' => admin_url('admin.php?page=zskeleton-membership-settings'),
                'zskeleton_membership_payment_group' => admin_url('admin.php?page=zskeleton-membership-payment'),
            )
        );
        if (empty($targets[$option_page]) || !is_string($targets[$option_page])) {
            return $location;
        }

        $target = $targets[$option_page];
        // Prefer the URL posted with the form (must match expected) so redirect works even when
        // Referer / _wp_http_referer fail validation (common on host mismatches, e.g. localhost vs 127.0.0.1).
        if (!empty($_POST['zskeleton_options_return_url'])) {
            $posted = esc_url_raw(wp_unslash($_POST['zskeleton_options_return_url']));
            $expected = esc_url_raw($target);
            if ($posted && $expected && untrailingslashit($posted) === untrailingslashit($expected)) {
                $target = $posted;
            }
        }

        return add_query_arg('settings-updated', 'true', $target);
    }

    /**
     * Save theme options via admin-post.php (bypasses wp-admin/options.php).
     *
     * Some hosts populate $_REQUEST['action'] in a way that breaks the core check
     * `if ( 'update' === $action )`, so the save block never runs and the screen
     * falls through to “All Settings” with no updates and no redirect.
     *
     * @return void
     */
    public function handle_admin_post_save_theme_settings() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to manage options for this site.', 'zskeleton'));
        }

        check_admin_referer('zskeleton_save_theme_settings');

        $option_names = self::collect_theme_settings_option_names($_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        foreach ($option_names as $option) {
            $option = trim((string) $option);
            if ('' === $option) {
                continue;
            }

            // Never call update_option() with a missing POST key: that passes null and can clear options
            // (e.g. max_input_vars truncation, partial POST) or fight sanitize_option.
            if (!isset($_POST[ $option ])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- checked above.
                continue;
            }

            $value = wp_unslash($_POST[ $option ]); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- update_option runs sanitize_option.
            if (!is_array($value)) {
                $value = trim((string) $value);
            }

            update_option($option, $value);
        }


        $expected = esc_url_raw(admin_url('themes.php?page=zskeleton-theme-settings'));
        $redirect = $expected;
        if (!empty($_POST['zskeleton_options_return_url'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $posted = esc_url_raw(wp_unslash($_POST['zskeleton_options_return_url']));
            if ($posted && untrailingslashit($posted) === untrailingslashit($expected)) {
                $redirect = $posted;
            }
        }

        add_settings_error(
            'general',
            'settings_updated',
            __('Settings saved.', 'zskeleton'),
            'success'
        );
        set_transient('settings_errors', get_settings_errors(), 30);

        wp_safe_redirect(add_query_arg('settings-updated', 'true', $redirect));
        exit;
    }

    /**
     * Option names to persist for this screen: union of $new_allowed_options and get_registered_settings(),
     * then {@see 'zskeleton_theme_settings_option_names'}.
     *
     * @param array $filter_context Optional context for the filter (use $_POST on form save; use array() elsewhere).
     * @return string[]
     */
    public static function collect_theme_settings_option_names($filter_context = array()) {
        global $new_allowed_options;

        $group = self::OPTION_GROUP;
        $names = array();

        if (isset($new_allowed_options[ $group ]) && is_array($new_allowed_options[ $group ])) {
            foreach ($new_allowed_options[ $group ] as $n) {
                $names[] = (string) $n;
            }
        }

        $registered = get_registered_settings();
        if (is_array($registered)) {
            foreach ($registered as $option_name => $args) {
                if (! is_array($args)) {
                    continue;
                }
                $g = isset($args['group']) ? (string) $args['group'] : '';
                if ($g === $group) {
                    $names[] = (string) $option_name;
                }
            }
        }

        $names = array_values(array_unique(array_filter(array_map('trim', $names))));

        if (! is_array($filter_context)) {
            $filter_context = array();
        }

        /**
         * Filter which options are saved/exported/reset for ZSkeleton theme settings.
         *
         * @param string[] $names   Option names (wp_options keys).
         * @param string   $group   Settings group slug ({@see ZSkeleton_Theme_Settings::OPTION_GROUP}).
         * @param array    $context Same as $filter_context passed to collect_theme_settings_option_names().
         */
        return apply_filters('zskeleton_theme_settings_option_names', $names, $group, $filter_context);
    }

    /**
     * Add theme settings page to admin menu
     */
    public function add_theme_settings_page() {
        add_theme_page(
            __('ZSkeleton Theme Settings', 'zskeleton'),
            __('ZSkeleton Settings', 'zskeleton'),
            'manage_options',
            'zskeleton-theme-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register theme settings for Appearance → ZSkeleton Settings.
     *
     * Add new options with register_setting( self::OPTION_GROUP, 'your_option_name', $args )
     * and add_settings_field so they are saved by handle_admin_post_save_theme_settings().
     *
     * @return void
     */
    public function register_settings() {
        // Branding: logos, site name, tagline.
        add_settings_section(
            'zskeleton_branding_settings',
            '',
            array($this, 'branding_settings_callback'),
            'zskeleton-branding-settings'
        );

        // Appearance: palette and typography.
        add_settings_section(
            'zskeleton_appearance_settings',
            '',
            array($this, 'appearance_settings_callback'),
            'zskeleton-appearance-settings'
        );

        // Appearance: header top bar and footer copyright strip (colors; spacing stays under Layout).
        add_settings_section(
            'zskeleton_appearance_header_footer',
            __( 'Top bar and footer copyright (colors)', 'zskeleton' ),
            array( $this, 'appearance_header_footer_bars_callback' ),
            'zskeleton-appearance-settings'
        );

        // Layout: header and footer structure.
        add_settings_section(
            'zskeleton_layout_settings',
            '',
            array($this, 'layout_settings_callback'),
            'zskeleton-layout-settings'
        );

        // Layout: top bar and footer copyright strip (spacing; colors are under Appearance).
        add_settings_section(
            'zskeleton_layout_bars_spacing',
            __( 'Top bar and footer copyright (spacing)', 'zskeleton' ),
            array( $this, 'layout_bars_spacing_settings_callback' ),
            'zskeleton-layout-settings'
        );

        // Contact & social: email, default map, profile URLs.
        add_settings_section(
            'zskeleton_contact_social_settings',
            '',
            array($this, 'contact_social_settings_callback'),
            'zskeleton-contact-social-settings'
        );

        // Homepage Settings Section
        add_settings_section(
            'zskeleton_homepage_settings',
            '', // Empty title to avoid duplication with tab heading
            array($this, 'homepage_settings_callback'),
            'zskeleton-homepage-settings'
        );

        // Content Settings Section
        add_settings_section(
            'zskeleton_content_settings',
            '', // Empty title to avoid duplication with tab heading
            array($this, 'content_settings_callback'),
            'zskeleton-content-settings'
        );

        // Newsletter Settings Section
        add_settings_section(
            'zskeleton_newsletter_settings',
            '', // Empty title to avoid duplication with tab heading
            array($this, 'newsletter_settings_callback'),
            'zskeleton-newsletter-settings'
        );

        // Performance Settings Section
        add_settings_section(
            'zskeleton_performance_settings',
            '', // Empty title to avoid duplication with tab heading
            array($this, 'performance_settings_callback'),
            'zskeleton-performance-settings'
        );

        // Security Settings Section (reCAPTCHA)
        add_settings_section(
            'zskeleton_security_settings',
            '', // Empty title to avoid duplication with tab heading
            array($this, 'security_settings_callback'),
            'zskeleton-security-settings'
        );

        // Register all settings fields
        $this->register_branding_settings();
        $this->register_appearance_settings();
        $this->register_layout_settings();
        $this->register_contact_social_settings();
        $this->register_homepage_settings();
        $this->register_content_settings();
        $this->register_newsletter_settings();
        $this->register_performance_settings();
        $this->register_security_settings();
    }

    /**
     * Register branding settings (logos and site identity text).
     *
     * @return void
     */
    private function register_branding_settings() {
        // Site Logo
        register_setting(self::OPTION_GROUP, 'zskeleton_site_logo', array(
            'sanitize_callback' => array($this, 'sanitize_logo_setting')
        ));
        add_settings_field(
            'zskeleton_site_logo',
            __('Site Logo', 'zskeleton'),
            array($this, 'image_field_callback'),
            'zskeleton-branding-settings',
            'zskeleton_branding_settings',
            array(
                'id' => 'zskeleton_site_logo',
                'default' => '',
                'description' => __('Upload your site logo. Recommended size: 200px × 50px', 'zskeleton')
            )
        );

        // Mobile Logo
        register_setting(self::OPTION_GROUP, 'zskeleton_mobile_logo', array(
            'sanitize_callback' => array($this, 'sanitize_logo_setting')
        ));
        add_settings_field(
            'zskeleton_mobile_logo',
            __('Mobile Logo', 'zskeleton'),
            array($this, 'image_field_callback'),
            'zskeleton-branding-settings',
            'zskeleton_branding_settings',
            array(
                'id' => 'zskeleton_mobile_logo',
                'default' => '',
                'description' => __('Upload a mobile-optimized logo. Recommended size: 150px × 40px. If not set, the main logo will be used.', 'zskeleton')
            )
        );

        // Site Logo for non-Arabic locales (multilingual / LTR).
        register_setting(self::OPTION_GROUP, 'zskeleton_site_logo_ltr', array(
            'sanitize_callback' => array($this, 'sanitize_logo_setting')
        ));
        add_settings_field(
            'zskeleton_site_logo_ltr',
            __('Site Logo (non-Arabic)', 'zskeleton'),
            array($this, 'image_field_callback'),
            'zskeleton-branding-settings',
            'zskeleton_branding_settings',
            array(
                'id' => 'zskeleton_site_logo_ltr',
                'default' => '',
                'description' => __('Used when the site locale is not Arabic (e.g. English). If empty, the main Site Logo is used.', 'zskeleton')
            )
        );

        // Mobile logo for non-Arabic locales.
        register_setting(self::OPTION_GROUP, 'zskeleton_mobile_logo_ltr', array(
            'sanitize_callback' => array($this, 'sanitize_logo_setting')
        ));
        add_settings_field(
            'zskeleton_mobile_logo_ltr',
            __('Mobile Logo (non-Arabic)', 'zskeleton'),
            array($this, 'image_field_callback'),
            'zskeleton-branding-settings',
            'zskeleton_branding_settings',
            array(
                'id' => 'zskeleton_mobile_logo_ltr',
                'default' => '',
                'description' => __('Mobile variant for non-Arabic locales. If empty, falls back to the non-Arabic site logo, then the main mobile logo, then the main site logo.', 'zskeleton')
            )
        );

        // Site Name
        register_setting(self::OPTION_GROUP, 'zskeleton_site_name');
        add_settings_field(
            'zskeleton_site_name',
            __('Site Name', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-branding-settings',
            'zskeleton_branding_settings',
            array(
                'id' => 'zskeleton_site_name',
                'default' => 'ZSkeleton',
                'description' => __('Full site name displayed in headers', 'zskeleton')
            )
        );

        // Site Tagline
        register_setting(self::OPTION_GROUP, 'zskeleton_site_tagline');
        add_settings_field(
            'zskeleton_site_tagline',
            __('Site Tagline', 'zskeleton'),
            array($this, 'textarea_field_callback'),
            'zskeleton-branding-settings',
            'zskeleton_branding_settings',
            array(
                'id' => 'zskeleton_site_tagline',
                'default' => 'A flexible WordPress base theme for membership-driven websites.',
                'description' => __('Tagline displayed under the site name', 'zskeleton')
            )
        );
    }

    /**
     * Register appearance settings (colors and typography).
     *
     * @return void
     */
    private function register_appearance_settings() {
        // Primary Color
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_primary_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_primary_color',
                'default'           => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['primary'] : '#647FBC',
            )
        );
        add_settings_field(
            'zskeleton_primary_color',
            __('Primary Color', 'zskeleton'),
            array($this, 'color_field_callback'),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_primary_color',
                'default' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['primary'] : '#647FBC',
                'description' => __('Headers, key actions, and brand emphasis.', 'zskeleton')
            )
        );

        // Secondary Color
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_secondary_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_secondary_color',
                'default'           => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['secondary'] : '#91ADC8',
            )
        );
        add_settings_field(
            'zskeleton_secondary_color',
            __('Secondary Color', 'zskeleton'),
            array($this, 'color_field_callback'),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_secondary_color',
                'default' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['secondary'] : '#91ADC8',
                'description' => __('Borders, soft chrome, and secondary UI.', 'zskeleton')
            )
        );

        // Accent Color
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_accent_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_accent_color',
                'default'           => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['accent'] : '#AED6CF',
            )
        );
        add_settings_field(
            'zskeleton_accent_color',
            __('Accent Color', 'zskeleton'),
            array($this, 'color_field_callback'),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_accent_color',
                'default' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['accent'] : '#AED6CF',
                'description' => __('Highlights, badges, and gentle section tints.', 'zskeleton')
            )
        );

        // Page background
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_background_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_background_color',
                'default'           => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['background'] : '#FAFDD6',
            )
        );
        add_settings_field(
            'zskeleton_background_color',
            __('Page Background', 'zskeleton'),
            array($this, 'color_field_callback'),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_background_color',
                'default' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['background'] : '#FAFDD6',
                'description' => __('Main page canvas; warm off-white reads well on small screens.', 'zskeleton')
            )
        );

        // Primary-style button background (CTA and default buttons).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_button_background_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_button_background_color',
                'default'           => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['button_background'] : '#647FBC',
            )
        );
        add_settings_field(
            'zskeleton_button_background_color',
            __('Buttons Background', 'zskeleton'),
            array($this, 'color_field_callback'),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_button_background_color',
                'default' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['button_background'] : '#647FBC',
                'description' => __('Fill color for primary buttons and default form buttons site-wide.', 'zskeleton')
            )
        );

        // Primary-style button label color.
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_button_text_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_button_text_color',
                'default'           => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['button_text'] : '#000000',
            )
        );
        add_settings_field(
            'zskeleton_button_text_color',
            __('Buttons Text Color', 'zskeleton'),
            array($this, 'color_field_callback'),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_button_text_color',
                'default' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['button_text'] : '#000000',
                'description' => __('Text color on primary-style and default form buttons (default black).', 'zskeleton')
            )
        );

        // Stat / counter numbers (hero and stat blocks).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_counter_text_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_counter_text_color',
                'default'           => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['counter_text'] : '#647FBC',
            )
        );
        add_settings_field(
            'zskeleton_counter_text_color',
            __('Counters Text Color', 'zskeleton'),
            array($this, 'color_field_callback'),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_counter_text_color',
                'default' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['counter_text'] : '#647FBC',
                'description' => __('Color for large stat numbers in hero and stat blocks.', 'zskeleton')
            )
        );

        // Primary header menu: optional hover and current-item link backgrounds (empty = no fill).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_nav_item_hover_bg',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_nav_item_background',
                'default'           => '',
            )
        );
        add_settings_field(
            'zskeleton_nav_item_hover_bg',
            __( 'Primary menu — hover / focus link background', 'zskeleton' ),
            array( $this, 'text_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_nav_item_hover_bg',
                'default' => '',
                'description' => __( 'Optional. Hex or rgba, e.g. rgba(30, 58, 138, 0.1). Leave empty for no background (only text color change on hover).', 'zskeleton' ),
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_nav_item_active_bg',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_nav_item_background',
                'default'           => '',
            )
        );
        add_settings_field(
            'zskeleton_nav_item_active_bg',
            __( 'Primary menu — current page link background', 'zskeleton' ),
            array( $this, 'text_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_nav_item_active_bg',
                'default' => '',
                'description' => __( 'Optional. Leave empty to use the same as hover, or set a different color for the current menu item.', 'zskeleton' ),
            )
        );

        // Mobile menu toggle button appearance.
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_mobile_menu_button_style',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_mobile_menu_button_style',
                'default'           => 'style1',
            )
        );
        add_settings_field(
            'zskeleton_mobile_menu_button_style',
            __( 'Mobile menu button style', 'zskeleton' ),
            array( $this, 'select_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id'      => 'zskeleton_mobile_menu_button_style',
                'default' => 'style1',
                'options' => array(
                    'style1' => __( 'Style 1 (current)', 'zskeleton' ),
                    'style2' => __( 'Style 2 (equal parallel bars)', 'zskeleton' ),
                ),
                'description' => __( 'Choose the mobile menu icon style for the header toggle button.', 'zskeleton' ),
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_mobile_menu_button_bar_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_mobile_menu_button_bar_color',
                'default'           => '#ffffff',
            )
        );
        add_settings_field(
            'zskeleton_mobile_menu_button_bar_color',
            __( 'Mobile menu button bars color', 'zskeleton' ),
            array( $this, 'color_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_mobile_menu_button_bar_color',
                'default' => '#ffffff',
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_mobile_menu_button_background_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_mobile_menu_button_background_color',
                'default'           => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['primary'] : '#647FBC',
            )
        );
        add_settings_field(
            'zskeleton_mobile_menu_button_background_color',
            __( 'Mobile menu button background', 'zskeleton' ),
            array( $this, 'color_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_mobile_menu_button_background_color',
                'default' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['primary'] : '#647FBC',
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_mobile_menu_button_border_width',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_mobile_menu_button_border_width',
                'default'           => '2',
            )
        );
        add_settings_field(
            'zskeleton_mobile_menu_button_border_width',
            __( 'Mobile menu button border width (px)', 'zskeleton' ),
            array( $this, 'number_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_mobile_menu_button_border_width',
                'default' => '2',
                'min' => '0',
                'max' => '10',
                'step' => '0.5',
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_mobile_menu_button_border_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_mobile_menu_button_border_color',
                'default'           => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['primary'] : '#647FBC',
            )
        );
        add_settings_field(
            'zskeleton_mobile_menu_button_border_color',
            __( 'Mobile menu button border color', 'zskeleton' ),
            array( $this, 'color_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_mobile_menu_button_border_color',
                'default' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['primary'] : '#647FBC',
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_mobile_menu_close_background',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_layout_optional_hex_color',
                'default'           => '',
            )
        );
        add_settings_field(
            'zskeleton_mobile_menu_close_background',
            __( 'Mobile menu close button background', 'zskeleton' ),
            array( $this, 'color_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id'          => 'zskeleton_mobile_menu_close_background',
                'default'     => '',
                'description' => __( 'Optional. Clear the color for a transparent background (theme default).', 'zskeleton' ),
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_mobile_menu_close_text_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_mobile_menu_close_text_color',
                'default'           => '#374151',
            )
        );
        add_settings_field(
            'zskeleton_mobile_menu_close_text_color',
            __( 'Mobile menu close button icon color', 'zskeleton' ),
            array( $this, 'color_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id'          => 'zskeleton_mobile_menu_close_text_color',
                'default'     => '#374151',
                'description' => __( 'Color for the × icon (default matches body text gray).', 'zskeleton' ),
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_mobile_menu_close_border_radius',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_mobile_menu_close_border_radius',
                'default'           => '50%',
            )
        );
        add_settings_field(
            'zskeleton_mobile_menu_close_border_radius',
            __( 'Mobile menu close button border radius', 'zskeleton' ),
            array( $this, 'text_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id'          => 'zskeleton_mobile_menu_close_border_radius',
                'default'     => '50%',
                'description' => __( 'CSS length, e.g. 50% (circular, default), 12px, or 0 for square corners.', 'zskeleton' ),
            )
        );

        // Mobile slide-out menu: tabbed (Main / Members) vs single scroll list (no tab strip).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_mobile_menu_panel_style',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_mobile_menu_panel_style',
                'default'           => 'style1',
            )
        );
        add_settings_field(
            'zskeleton_mobile_menu_panel_style',
            __( 'Mobile slide-out menu layout', 'zskeleton' ),
            array( $this, 'select_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id'          => 'zskeleton_mobile_menu_panel_style',
                'default'     => 'style1',
                'options'     => array(
                    'style1' => __( 'Style 1 (Main Menu / Members Area tabs)', 'zskeleton' ),
                    'style2' => __( 'Style 2 (single list — primary menu + links, no tabs)', 'zskeleton' ),
                ),
                'description' => __( 'Style 2 hides the tab strip and shows one scrollable list: the primary menu plus the same resource links that used to live under Members Area.', 'zskeleton' ),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_mobile_menu_drawer_width',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_mobile_menu_drawer_width',
                'default'           => 'default',
            )
        );
        add_settings_field(
            'zskeleton_mobile_menu_drawer_width',
            __( 'Mobile slide-out drawer width', 'zskeleton' ),
            array( $this, 'select_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id'          => 'zskeleton_mobile_menu_drawer_width',
                'default'     => 'default',
                'options'     => array(
                    'default' => __( 'Default (85% wide, max 320px)', 'zskeleton' ),
                    'full'    => __( 'Full viewport width', 'zskeleton' ),
                ),
                'description' => __( 'Applies to the primary mobile menu drawer (both header layouts).', 'zskeleton' ),
            )
        );

        // Mobile bottom navigation bar style (fixed tab bar on small screens).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_mobile_bottom_nav_style',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_mobile_bottom_nav_style',
                'default'           => 'style1',
            )
        );
        add_settings_field(
            'zskeleton_mobile_bottom_nav_style',
            __( 'Mobile bottom navigation style', 'zskeleton' ),
            array( $this, 'select_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id'          => 'zskeleton_mobile_bottom_nav_style',
                'default'     => 'style1',
                'options'     => array(
                    'style1' => __( 'Style 1 (current)', 'zskeleton' ),
                    'style2' => __( 'Style 2 (primary bar + bell/cart/search/share/WhatsApp)', 'zskeleton' ),
                ),
                'description' => __( 'Style 2 uses the theme primary color as the bar background and opens popovers for Search and Share.', 'zskeleton' ),
            )
        );

        $layout_bars_appear = function_exists( 'zskeleton_get_layout_bars_default_option_values' ) ? zskeleton_get_layout_bars_default_option_values() : array();
        $lb_appear = function ( $key, $fallback ) use ( $layout_bars_appear ) {
            return isset( $layout_bars_appear[ $key ] ) ? $layout_bars_appear[ $key ] : $fallback;
        };

        // Header top bar: gradient and text (colors; defaults follow palette — see ZSkeleton → Appearance → this section).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_top_bar_gradient_color_start',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_layout_optional_hex_color',
                'default' => $lb_appear( 'zskeleton_top_bar_gradient_color_start', '#647FBC' ),
            )
        );
        add_settings_field(
            'zskeleton_top_bar_gradient_color_start',
            __( 'Top bar gradient — start color', 'zskeleton' ),
            array( $this, 'color_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_header_footer',
            array(
                'id' => 'zskeleton_top_bar_gradient_color_start',
                'default' => $lb_appear( 'zskeleton_top_bar_gradient_color_start', '#647FBC' ),
                'description' => __( 'Defaults to your Primary color. Paired with the end color, forms the same 135° gradient as the rest of the theme; change both to override. Clear both to use only the main palette (no bar-specific gradient).', 'zskeleton' ),
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_top_bar_gradient_color_end',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_layout_optional_hex_color',
                'default' => $lb_appear( 'zskeleton_top_bar_gradient_color_end', '#0f172a' ),
            )
        );
        add_settings_field(
            'zskeleton_top_bar_gradient_color_end',
            __( 'Top bar gradient — end color', 'zskeleton' ),
            array( $this, 'color_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_header_footer',
            array(
                'id' => 'zskeleton_top_bar_gradient_color_end',
                'default' => $lb_appear( 'zskeleton_top_bar_gradient_color_end', '#0f172a' ),
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_top_bar_text_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_top_bar_text_color',
                'default' => $lb_appear( 'zskeleton_top_bar_text_color', '#ffffff' ),
            )
        );
        add_settings_field(
            'zskeleton_top_bar_text_color',
            __( 'Top bar text and icons (optional)', 'zskeleton' ),
            array( $this, 'color_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_header_footer',
            array(
                'id' => 'zskeleton_top_bar_text_color',
                'default' => $lb_appear( 'zskeleton_top_bar_text_color', '#ffffff' ),
                'description' => __( 'Default is white. Adjust if your top bar needs higher contrast.', 'zskeleton' ),
            )
        );

        // Footer copyright strip: gradient, text, border (colors).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_footer_copyright_card_gradient_start',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_layout_optional_hex_color',
                'default' => $lb_appear( 'zskeleton_footer_copyright_card_gradient_start', '#647FBC' ),
            )
        );
        add_settings_field(
            'zskeleton_footer_copyright_card_gradient_start',
            __( 'Footer copyright strip gradient — start color', 'zskeleton' ),
            array( $this, 'color_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_header_footer',
            array(
                'id' => 'zskeleton_footer_copyright_card_gradient_start',
                'default' => $lb_appear( 'zskeleton_footer_copyright_card_gradient_start', '#647FBC' ),
                'description' => __( 'Defaults to Primary, same as the main footer. Clear both stops to use only the global footer background for this row.', 'zskeleton' ),
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_footer_copyright_card_gradient_end',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_layout_optional_hex_color',
                'default' => $lb_appear( 'zskeleton_footer_copyright_card_gradient_end', '#0f172a' ),
            )
        );
        add_settings_field(
            'zskeleton_footer_copyright_card_gradient_end',
            __( 'Footer copyright strip gradient — end color', 'zskeleton' ),
            array( $this, 'color_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_header_footer',
            array(
                'id' => 'zskeleton_footer_copyright_card_gradient_end',
                'default' => $lb_appear( 'zskeleton_footer_copyright_card_gradient_end', '#0f172a' ),
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_footer_copyright_card_text_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_footer_copyright_text_color',
                'default' => $lb_appear( 'zskeleton_footer_copyright_card_text_color', '#ffffff' ),
            )
        );
        add_settings_field(
            'zskeleton_footer_copyright_card_text_color',
            __( 'Footer copyright strip text (optional)', 'zskeleton' ),
            array( $this, 'color_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_header_footer',
            array(
                'id' => 'zskeleton_footer_copyright_card_text_color',
                'default' => $lb_appear( 'zskeleton_footer_copyright_card_text_color', '#ffffff' ),
                'description' => __( 'Default is white, matching the default footer text.', 'zskeleton' ),
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_footer_copyright_card_border_top_color',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_footer_copyright_border_color',
                'default' => $lb_appear( 'zskeleton_footer_copyright_card_border_top_color', '#374151' ),
            )
        );
        add_settings_field(
            'zskeleton_footer_copyright_card_border_top_color',
            __( 'Footer copyright strip top border color', 'zskeleton' ),
            array( $this, 'color_field_callback' ),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_header_footer',
            array(
                'id' => 'zskeleton_footer_copyright_card_border_top_color',
                'default' => $lb_appear( 'zskeleton_footer_copyright_card_border_top_color', '#374151' ),
                'description' => __( 'Default is #374151, matching the stylesheet. Used when the top border is not hidden (Layout → spacing).', 'zskeleton' ),
            )
        );

        // Google Font for Arabic locales (dropdown of curated fonts).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_google_font_arabic',
            array(
                'sanitize_callback' => array($this, 'sanitize_google_font_arabic'),
            )
        );
        add_settings_field(
            'zskeleton_google_font_arabic',
            __('Google Font (Arabic Locales)', 'zskeleton'),
            array($this, 'google_font_select_callback'),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_google_font_arabic',
                'default' => 'Cairo:wght@400;500;600;700',
                'options' => function_exists('zskeleton_get_google_font_choices_arabic') ? zskeleton_get_google_font_choices_arabic() : array(),
                'description' => __('Typography for Arabic locales (ar, ar_SA, etc.).', 'zskeleton'),
            )
        );

        // Google Font for non-Arabic locales.
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_google_font_default',
            array(
                'sanitize_callback' => array($this, 'sanitize_google_font_default'),
            )
        );
        add_settings_field(
            'zskeleton_google_font_default',
            __('Google Font (Non-Arabic Locales)', 'zskeleton'),
            array($this, 'google_font_select_callback'),
            'zskeleton-appearance-settings',
            'zskeleton_appearance_settings',
            array(
                'id' => 'zskeleton_google_font_default',
                'default' => 'Inter:wght@400;500;600;700',
                'options' => function_exists('zskeleton_get_google_font_choices_latin') ? zskeleton_get_google_font_choices_latin() : array(),
                'description' => __('Typography for English and all other non-Arabic locales.', 'zskeleton'),
            )
        );
    }

    /**
     * Register layout settings (header and footer structure).
     *
     * @return void
     */
    private function register_layout_settings() {
        // Back to top (fixed float; same option used as Customizer if registered).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_back_to_top_enabled',
            array(
                'sanitize_callback' => array($this, 'sanitize_on_off_checkbox'),
                'default' => '1',
            )
        );
        add_settings_field(
            'zskeleton_back_to_top_enabled',
            __('Back to top button', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-layout-settings',
            'zskeleton_layout_settings',
            array(
                'id' => 'zskeleton_back_to_top_enabled',
                'default' => '1',
                'description' => __('Show a fixed back to top control after the visitor scrolls. Uses the inline end of the screen in LTR and mirrors in RTL (opposite the floating WhatsApp control when both are on).', 'zskeleton'),
            )
        );

        // Footer: how many widget columns to show in the footer layout (sidebars footer-1…footer-4 stay registered).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_footer_widget_areas_count',
            array(
                'sanitize_callback' => array($this, 'sanitize_footer_widget_areas_count'),
                'default' => '4',
            )
        );
        add_settings_field(
            'zskeleton_footer_widget_areas_count',
            __('Footer widget columns', 'zskeleton'),
            array($this, 'select_field_callback'),
            'zskeleton-layout-settings',
            'zskeleton_layout_settings',
            array(
                'id' => 'zskeleton_footer_widget_areas_count',
                'default' => '4',
                'options' => array(
                    '1' => __('1 column', 'zskeleton'),
                    '2' => __('2 columns', 'zskeleton'),
                    '3' => __('3 columns', 'zskeleton'),
                    '4' => __('4 columns', 'zskeleton'),
                ),
                'description' => __('How many footer widget areas appear in the front-end layout. Unused areas stay available under Appearance → Widgets.', 'zskeleton'),
            )
        );

        // Header layout (default vs split with top search + centered logo).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_header_layout',
            array(
                'sanitize_callback' => array($this, 'sanitize_header_layout_setting'),
                'default' => 'default',
            )
        );
        add_settings_field(
            'zskeleton_header_layout',
            __('Header layout', 'zskeleton'),
            array($this, 'select_field_callback'),
            'zskeleton-layout-settings',
            'zskeleton_layout_settings',
            array(
                'id' => 'zskeleton_header_layout',
                'default' => 'default',
                'options' => function_exists( 'zskeleton_get_header_layout_choices' ) ? zskeleton_get_header_layout_choices() : array(
                    'default' => __( 'Default (tagline bar + logo row + search toggle)', 'zskeleton' ),
                    'split_top_search' => __( 'Split: top search & socials, logo centered between menus', 'zskeleton' ),
                ),
                'description' => __('Split layout shows an inline search and social icons in the top bar, login on the right, and the logo centered between the primary menu (left) and the “Header — right of logo” menu (assign under Appearance → Menus).', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_split_logo_height',
            array(
                'sanitize_callback' => array($this, 'sanitize_split_logo_height'),
                'default' => '56',
            )
        );
        add_settings_field(
            'zskeleton_split_logo_height',
            __('Split logo height (px)', 'zskeleton'),
            array($this, 'number_field_callback'),
            'zskeleton-layout-settings',
            'zskeleton_layout_settings',
            array(
                'id' => 'zskeleton_split_logo_height',
                'default' => '56',
                'min' => '24',
                'max' => '120',
                'step' => '1',
                'description' => __('Desktop split-header logo max height. Default: 56px.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_split_logo_side_padding',
            array(
                'sanitize_callback' => array($this, 'sanitize_split_logo_side_padding'),
                'default' => '72',
            )
        );
        add_settings_field(
            'zskeleton_split_logo_side_padding',
            __('Split logo side padding (px)', 'zskeleton'),
            array($this, 'number_field_callback'),
            'zskeleton-layout-settings',
            'zskeleton_layout_settings',
            array(
                'id' => 'zskeleton_split_logo_side_padding',
                'default' => '72',
                'min' => '0',
                'max' => '180',
                'step' => '1',
                'description' => __('Left/right spacing reserved around the split logo between menu groups. Default: 72px.', 'zskeleton'),
            )
        );

        $layout_bars = function_exists( 'zskeleton_get_layout_bars_default_option_values' ) ? zskeleton_get_layout_bars_default_option_values() : array();
        $lb = function ( $key, $fallback ) use ( $layout_bars ) {
            return isset( $layout_bars[ $key ] ) ? $layout_bars[ $key ] : $fallback;
        };

        // Header top bar: vertical spacing (colors are under Appearance → Top bar and footer copyright).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_top_bar_padding_y',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_top_bar_padding_y',
                'default' => $lb( 'zskeleton_top_bar_padding_y', '8px' ),
            )
        );
        add_settings_field(
            'zskeleton_top_bar_padding_y',
            __('Top bar vertical padding (top and bottom)', 'zskeleton'),
            array( $this, 'text_field_callback' ),
            'zskeleton-layout-settings',
            'zskeleton_layout_bars_spacing',
            array(
                'id' => 'zskeleton_top_bar_padding_y',
                'default' => $lb( 'zskeleton_top_bar_padding_y', '8px' ),
                'description' => __( 'Default: 8px. Use a length such as 8px, 0.5rem, or 12px.', 'zskeleton' ),
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_top_bar_content_min_height',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_top_bar_content_min_height',
                'default' => $lb( 'zskeleton_top_bar_content_min_height', '25px' ),
            )
        );
        add_settings_field(
            'zskeleton_top_bar_content_min_height',
            __( 'Top bar row min-height', 'zskeleton' ),
            array( $this, 'text_field_callback' ),
            'zskeleton-layout-settings',
            'zskeleton_layout_bars_spacing',
            array(
                'id' => 'zskeleton_top_bar_content_min_height',
                'default' => $lb( 'zskeleton_top_bar_content_min_height', '25px' ),
                'description' => __( 'Applied to the inner .header-top-content row. Default: 25px.', 'zskeleton' ),
            )
        );

        // Footer copyright strip: border visibility, padding, min-height (colors are under Appearance).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_footer_copyright_card_border_top_hidden',
            array(
                'sanitize_callback' => array( $this, 'sanitize_on_off_checkbox' ),
                'default' => $lb( 'zskeleton_footer_copyright_card_border_top_hidden', '0' ),
            )
        );
        add_settings_field(
            'zskeleton_footer_copyright_card_border_top_hidden',
            __( 'Footer copyright strip: hide top border', 'zskeleton' ),
            array( $this, 'checkbox_field_callback' ),
            'zskeleton-layout-settings',
            'zskeleton_layout_bars_spacing',
            array(
                'id' => 'zskeleton_footer_copyright_card_border_top_hidden',
                'default' => $lb( 'zskeleton_footer_copyright_card_border_top_hidden', '0' ),
                'description' => __( 'When enabled, the 1px line above the copyright strip is removed. When off, the color set in Appearance → Top bar and footer copyright (colors) is used.', 'zskeleton' ),
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_footer_copyright_card_padding_y',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_footer_copyright_padding_y',
                'default' => $lb( 'zskeleton_footer_copyright_card_padding_y', '20px' ),
            )
        );
        add_settings_field(
            'zskeleton_footer_copyright_card_padding_y',
            __( 'Footer copyright strip vertical padding', 'zskeleton' ),
            array( $this, 'text_field_callback' ),
            'zskeleton-layout-settings',
            'zskeleton_layout_bars_spacing',
            array(
                'id' => 'zskeleton_footer_copyright_card_padding_y',
                'default' => $lb( 'zskeleton_footer_copyright_card_padding_y', '20px' ),
                'description' => __( 'Vertical padding above and below the copyright row. Default: 20px.', 'zskeleton' ),
            )
        );
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_footer_copyright_card_min_height',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_option_footer_copyright_min_height',
                'default' => $lb( 'zskeleton_footer_copyright_card_min_height', '' ),
            )
        );
        add_settings_field(
            'zskeleton_footer_copyright_card_min_height',
            __( 'Footer copyright strip min-height (optional)', 'zskeleton' ),
            array( $this, 'text_field_callback' ),
            'zskeleton-layout-settings',
            'zskeleton_layout_bars_spacing',
            array(
                'id' => 'zskeleton_footer_copyright_card_min_height',
                'default' => $lb( 'zskeleton_footer_copyright_card_min_height', '' ),
                'description' => __( 'Leave empty to let content set the height. Otherwise use a length (e.g. 48px).', 'zskeleton' ),
            )
        );
    }

    /**
     * Register contact email, default map location, and social profile URLs.
     *
     * @return void
     */
    private function register_contact_social_settings() {
        // Contact Email
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_contact_email',
            array(
                'sanitize_callback' => array($this, 'sanitize_contact_email_setting'),
            )
        );
        add_settings_field(
            'zskeleton_contact_email',
            __('Contact Email', 'zskeleton'),
            array($this, 'email_field_callback'),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id' => 'zskeleton_contact_email',
                'default' => 'info@zskeleton.org',
                'description' => __('Main contact email address', 'zskeleton')
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_membership_email',
            array(
                'sanitize_callback' => array( $this, 'sanitize_membership_email_setting' ),
            )
        );
        add_settings_field(
            'zskeleton_membership_email',
            __( 'Membership email', 'zskeleton' ),
            array( $this, 'email_field_callback' ),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id'          => 'zskeleton_membership_email',
                'default'     => 'membership@zskeleton.org',
                'description' => __( 'Membership and account inquiries (sidebar, footer).', 'zskeleton' ),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_media_email',
            array(
                'sanitize_callback' => array( $this, 'sanitize_media_email_setting' ),
            )
        );
        add_settings_field(
            'zskeleton_media_email',
            __( 'Media & press email', 'zskeleton' ),
            array( $this, 'email_field_callback' ),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id'          => 'zskeleton_media_email',
                'default'     => 'media@zskeleton.org',
                'description' => __( 'Press and media contact (sidebar, footer).', 'zskeleton' ),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_contact_phone',
            array(
                'sanitize_callback' => array( $this, 'sanitize_phone_setting' ),
                'default'           => '',
            )
        );
        add_settings_field(
            'zskeleton_contact_phone',
            __( 'Primary phone', 'zskeleton' ),
            array( $this, 'text_field_callback' ),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id'          => 'zskeleton_contact_phone',
                'default'     => '',
                'description' => __( 'Main public phone (digits, spaces, and common punctuation). Synced with Appearance → Customize → Contact & social. Legacy theme mod value is used if this is empty.', 'zskeleton' ),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_contact_phone_secondary',
            array(
                'sanitize_callback' => array( $this, 'sanitize_phone_setting' ),
                'default'           => '',
            )
        );
        add_settings_field(
            'zskeleton_contact_phone_secondary',
            __( 'Secondary phone', 'zskeleton' ),
            array( $this, 'text_field_callback' ),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id'          => 'zskeleton_contact_phone_secondary',
                'default'     => '',
                'description' => __( 'Optional second line (e.g. support or international).', 'zskeleton' ),
            )
        );

        // Google Map default location (used by zskeleton_render_google_map() when no args are passed).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_map_latitude',
            array(
                'sanitize_callback' => array( $this, 'sanitize_map_latitude_setting' ),
                'default'           => '',
            )
        );
        add_settings_field(
            'zskeleton_map_latitude',
            __( 'Map latitude', 'zskeleton' ),
            array( $this, 'text_field_callback' ),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id'          => 'zskeleton_map_latitude',
                'default'     => '',
                'description' => __( 'Decimal degrees (e.g. 24.7136). Leave empty if you use “Map address” instead.', 'zskeleton' ),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_map_longitude',
            array(
                'sanitize_callback' => array( $this, 'sanitize_map_longitude_setting' ),
                'default'           => '',
            )
        );
        add_settings_field(
            'zskeleton_map_longitude',
            __( 'Map longitude', 'zskeleton' ),
            array( $this, 'text_field_callback' ),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id'          => 'zskeleton_map_longitude',
                'default'     => '',
                'description' => __( 'Decimal degrees (e.g. 46.6753). Required together with latitude unless you use address.', 'zskeleton' ),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_map_address',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            )
        );
        add_settings_field(
            'zskeleton_map_address',
            __( 'Map address (optional)', 'zskeleton' ),
            array( $this, 'text_field_callback' ),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id'          => 'zskeleton_map_address',
                'default'     => '',
                'description' => __( 'Used as the map search query when latitude and longitude are both empty (e.g. street, city, country).', 'zskeleton' ),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_map_zoom',
            array(
                'sanitize_callback' => array( $this, 'sanitize_map_zoom_setting' ),
                'default'           => '14',
            )
        );
        add_settings_field(
            'zskeleton_map_zoom',
            __( 'Map zoom level', 'zskeleton' ),
            array( $this, 'number_field_callback' ),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id'          => 'zskeleton_map_zoom',
                'default'     => '14',
                'min'         => '1',
                'max'         => '20',
                'description' => __( '1 (world) to 20 (building). Used for the default embed.', 'zskeleton' ),
            )
        );

        // Social Media Links (same wp_options as Appearance → Customize → Contact & social).
        $social_urls_customizer_note = __( 'Same values as Appearance → Customize → Contact & social.', 'zskeleton' );
        register_setting(self::OPTION_GROUP, 'zskeleton_facebook_url');
        add_settings_field(
            'zskeleton_facebook_url',
            __('Facebook URL', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id' => 'zskeleton_facebook_url',
                'default' => '',
                'description' => sprintf(
                    /* translators: %s: sentence that social URLs sync with the Customizer. */
                    __('Facebook page URL (leave empty to hide). %s', 'zskeleton'),
                    $social_urls_customizer_note
                ),
            )
        );

        register_setting(self::OPTION_GROUP, 'zskeleton_twitter_url');
        add_settings_field(
            'zskeleton_twitter_url',
            __('Twitter/X URL', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id' => 'zskeleton_twitter_url',
                'default' => '',
                'description' => sprintf(
                    /* translators: %s: sentence that social URLs sync with the Customizer. */
                    __('Twitter/X profile URL (leave empty to hide). %s', 'zskeleton'),
                    $social_urls_customizer_note
                ),
            )
        );

        register_setting(self::OPTION_GROUP, 'zskeleton_linkedin_url');
        add_settings_field(
            'zskeleton_linkedin_url',
            __('LinkedIn URL', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id' => 'zskeleton_linkedin_url',
                'default' => '',
                'description' => sprintf(
                    /* translators: %s: sentence that social URLs sync with the Customizer. */
                    __('LinkedIn profile URL (leave empty to hide). %s', 'zskeleton'),
                    $social_urls_customizer_note
                ),
            )
        );

        register_setting(self::OPTION_GROUP, 'zskeleton_youtube_url');
        add_settings_field(
            'zskeleton_youtube_url',
            __('YouTube URL', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id' => 'zskeleton_youtube_url',
                'default' => '',
                'description' => sprintf(
                    /* translators: %s: sentence that social URLs sync with the Customizer. */
                    __('YouTube channel URL (leave empty to hide). %s', 'zskeleton'),
                    $social_urls_customizer_note
                ),
            )
        );

        register_setting(self::OPTION_GROUP, 'zskeleton_instagram_url');
        add_settings_field(
            'zskeleton_instagram_url',
            __('Instagram URL', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id' => 'zskeleton_instagram_url',
                'default' => '',
                'description' => sprintf(
                    /* translators: %s: sentence that social URLs sync with the Customizer. */
                    __('Instagram profile URL (leave empty to hide). %s', 'zskeleton'),
                    $social_urls_customizer_note
                ),
            )
        );

        register_setting(self::OPTION_GROUP, 'zskeleton_github_url');
        add_settings_field(
            'zskeleton_github_url',
            __('GitHub URL', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id' => 'zskeleton_github_url',
                'default' => '',
                'description' => sprintf(
                    /* translators: %s: sentence that social URLs sync with the Customizer. */
                    __('GitHub organization URL (leave empty to hide). %s', 'zskeleton'),
                    $social_urls_customizer_note
                ),
            )
        );

        register_setting(self::OPTION_GROUP, 'zskeleton_snapchat_url');
        add_settings_field(
            'zskeleton_snapchat_url',
            __('Snapchat URL', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id' => 'zskeleton_snapchat_url',
                'default' => '',
                'description' => sprintf(
                    /* translators: %s: sentence that social URLs sync with the Customizer. */
                    __('Snapchat profile URL (leave empty to hide). %s', 'zskeleton'),
                    $social_urls_customizer_note
                ),
            )
        );

        register_setting(self::OPTION_GROUP, 'zskeleton_tiktok_url');
        add_settings_field(
            'zskeleton_tiktok_url',
            __('TikTok URL', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id' => 'zskeleton_tiktok_url',
                'default' => '',
                'description' => sprintf(
                    /* translators: %s: sentence that social URLs sync with the Customizer. */
                    __('TikTok profile URL (leave empty to hide). %s', 'zskeleton'),
                    $social_urls_customizer_note
                ),
            )
        );

        // Floating WhatsApp (fixed bottom-left, opposite the Back to top button).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_whatsapp_float_enabled',
            array(
                'sanitize_callback' => array($this, 'sanitize_on_off_checkbox'),
                'default' => '0',
            )
        );
        add_settings_field(
            'zskeleton_whatsapp_float_enabled',
            __('Floating WhatsApp button', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id' => 'zskeleton_whatsapp_float_enabled',
                'default' => '0',
                'description' => __('Show a fixed WhatsApp link on the bottom inline-start (left in LTR, right in RTL) — opposite the back to top button, which uses inline-end when both are enabled.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_whatsapp_float_url',
            array(
                'sanitize_callback' => array($this, 'sanitize_whatsapp_float_url_setting'),
                'default' => '',
            )
        );
        add_settings_field(
            'zskeleton_whatsapp_float_url',
            __('WhatsApp link URL', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-contact-social-settings',
            'zskeleton_contact_social_settings',
            array(
                'id' => 'zskeleton_whatsapp_float_url',
                'default' => '',
                'description' => sprintf(
                    /* translators: %s: sentence that options sync with the Customizer. */
                    __('Full URL to open when the button is clicked (for example a wa.me link or https://api.whatsapp.com/send?…). Required for the button to appear. %s', 'zskeleton'),
                    $social_urls_customizer_note
                ),
            )
        );
    }

    /**
     * Register homepage settings
     */
    private function register_homepage_settings() {
        // Sidebar when Reading → Homepage = "Your latest posts" (off by default = full-width post list).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_posts_home_show_sidebar',
            array(
                'sanitize_callback' => array($this, 'sanitize_on_off_checkbox'),
                'default' => '0',
            )
        );
        add_settings_field(
            'zskeleton_posts_home_show_sidebar',
            __('Posts homepage sidebar', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-homepage-settings',
            'zskeleton_homepage_settings',
            array(
                'id' => 'zskeleton_posts_home_show_sidebar',
                'default' => '0',
                'description' => __('Show the main sidebar next to the post list when the site homepage is set to your latest posts (Settings → Reading). Leave unchecked for a full-width blog feed.', 'zskeleton'),
            )
        );

        // Hero Title
        register_setting(self::OPTION_GROUP, 'zskeleton_hero_title');
        add_settings_field(
            'zskeleton_hero_title',
            __('Hero Section Title', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-homepage-settings',
            'zskeleton_homepage_settings',
            array(
                'id' => 'zskeleton_hero_title',
                'default' => 'ZSkeleton',
                'description' => __('Main title displayed in the homepage hero section', 'zskeleton')
            )
        );

        // Hero Subtitle
        register_setting(self::OPTION_GROUP, 'zskeleton_hero_subtitle');
        add_settings_field(
            'zskeleton_hero_subtitle',
            __('Hero Section Subtitle', 'zskeleton'),
            array($this, 'textarea_field_callback'),
            'zskeleton-homepage-settings',
            'zskeleton_homepage_settings',
            array(
                'id' => 'zskeleton_hero_subtitle',
                'default' => 'Launch your next WordPress project faster with reusable templates and core features.',
                'description' => __('Subtitle text in the hero section', 'zskeleton')
            )
        );

        // Show Featured Content
        register_setting(self::OPTION_GROUP, 'zskeleton_show_featured_content');
        add_settings_field(
            'zskeleton_show_featured_content',
            __('Show Featured Content', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-homepage-settings',
            'zskeleton_homepage_settings',
            array(
                'id' => 'zskeleton_show_featured_content',
                'default' => '1',
                'description' => __('Display featured reports and research on homepage', 'zskeleton')
            )
        );

        // Featured Content Count
        register_setting(self::OPTION_GROUP, 'zskeleton_featured_content_count');
        add_settings_field(
            'zskeleton_featured_content_count',
            __('Featured Content Count', 'zskeleton'),
            array($this, 'number_field_callback'),
            'zskeleton-homepage-settings',
            'zskeleton_homepage_settings',
            array(
                'id' => 'zskeleton_featured_content_count',
                'default' => '3',
                'min' => '1',
                'max' => '12',
                'description' => __('Number of featured items to display', 'zskeleton')
            )
        );

        // News Banner
        register_setting(self::OPTION_GROUP, 'zskeleton_news_banner');
        add_settings_field(
            'zskeleton_news_banner',
            __('News Banner', 'zskeleton'),
            array($this, 'textarea_field_callback'),
            'zskeleton-homepage-settings',
            'zskeleton_homepage_settings',
            array(
                'id' => 'zskeleton_news_banner',
                'default' => '',
                'description' => __('HTML content for the news banner (leave empty to hide)', 'zskeleton')
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_homepage_seo_ar_slider_id',
            array(
                'sanitize_callback' => array($this, 'sanitize_homepage_seo_ar_slider_id'),
                'default' => 0,
            )
        );
        add_settings_field(
            'zskeleton_homepage_seo_ar_slider_id',
            __('Arabic SEO homepage hero slider', 'zskeleton'),
            array($this, 'homepage_seo_ar_slider_field_callback'),
            'zskeleton-homepage-settings',
            'zskeleton_homepage_settings',
            array(
                'description' => __('When a slider is selected, the “الصفحة الرئيسية للسيو بالعربية” template shows it instead of the built-in hero. Create sliders under Theme Features → Sliders.', 'zskeleton'),
            )
        );
    }

    /**
     * Register content settings
     */
    private function register_content_settings() {
        // Free Articles Limit
        register_setting(self::OPTION_GROUP, 'zskeleton_free_articles_limit');
        add_settings_field(
            'zskeleton_free_articles_limit',
            __('Free Articles Limit', 'zskeleton'),
            array($this, 'number_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_free_articles_limit',
                'default' => '3',
                'min' => '0',
                'max' => '20',
                'description' => __('Number of blog articles accessible to non-members. Restriction mode and messages are configured under Memberships → Content Restrictions in the ZSkeleton Membership plugin.', 'zskeleton')
            )
        );

        $auth_page_sanitize = array(
            'sanitize_callback' => array($this, 'sanitize_optional_page_id_setting'),
        );

        register_setting(self::OPTION_GROUP, 'zskeleton_auth_login_page_id', $auth_page_sanitize);
        add_settings_field(
            'zskeleton_auth_login_page_id',
            __('Login page', 'zskeleton'),
            array($this, 'page_select_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_auth_login_page_id',
                'none_label' => __('— WordPress default (wp-login.php)', 'zskeleton'),
                'description' => __('Used when a visitor must sign in before membership checkout (WooCommerce guest checkout disabled). Supports redirect back after login.', 'zskeleton'),
            )
        );

        register_setting(self::OPTION_GROUP, 'zskeleton_auth_register_page_id', $auth_page_sanitize);
        add_settings_field(
            'zskeleton_auth_register_page_id',
            __('Registration page', 'zskeleton'),
            array($this, 'page_select_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_auth_register_page_id',
                'none_label' => __('— WordPress default', 'zskeleton'),
                'description' => __('Theme registration template (e.g. membership signup).', 'zskeleton'),
            )
        );

        register_setting(self::OPTION_GROUP, 'zskeleton_auth_lost_password_page_id', $auth_page_sanitize);
        add_settings_field(
            'zskeleton_auth_lost_password_page_id',
            __('Forgot password page', 'zskeleton'),
            array($this, 'page_select_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_auth_lost_password_page_id',
                'none_label' => __('— WordPress default', 'zskeleton'),
                'description' => __('Page where users request a password reset email.', 'zskeleton'),
            )
        );

        register_setting(self::OPTION_GROUP, 'zskeleton_auth_reset_password_page_id', $auth_page_sanitize);
        add_settings_field(
            'zskeleton_auth_reset_password_page_id',
            __('Set new password page', 'zskeleton'),
            array($this, 'page_select_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_auth_reset_password_page_id',
                'none_label' => __('— WordPress default (link from email)', 'zskeleton'),
                'description' => __('Optional branded page for entering a new password. Email links still carry the key; defaults to wp-login.php?action=rp when unset.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_theme_contact_page_id',
            array(
                'sanitize_callback' => array($this, 'sanitize_optional_page_id_setting'),
                'default' => 0,
            )
        );
        add_settings_field(
            'zskeleton_theme_contact_page_id',
            __('Contact page (theme links)', 'zskeleton'),
            array($this, 'page_select_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_theme_contact_page_id',
                'none_label' => __('— Use main Contact page (slug / theme mapping)', 'zskeleton'),
                'description' => __('Optional page used wherever the theme outputs a “contact” URL from settings (e.g. FAQ CTA, patterns). When unset, matches the main Contact link (header/footer).', 'zskeleton'),
            )
        );

        add_settings_field(
            'zskeleton_auth_pages_set_defaults',
            __('Common pages & auth', 'zskeleton'),
            array($this, 'auth_pages_set_defaults_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings'
        );

        // Archive Page Size
        register_setting(self::OPTION_GROUP, 'zskeleton_archive_posts_per_page');
        add_settings_field(
            'zskeleton_archive_posts_per_page',
            __('Archive Posts Per Page', 'zskeleton'),
            array($this, 'number_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_archive_posts_per_page',
                'default' => '12',
                'min' => '6',
                'max' => '50',
                'description' => __('Number of posts to show on archive pages', 'zskeleton')
            )
        );

        // Blog listing templates (page-blog.php, page-blog-blocks.php) and ZSkeleton blog blocks.
        add_settings_field(
            'zskeleton_blog_listing_intro',
            __('Blog listing page', 'zskeleton'),
            array($this, 'blog_listing_hub_intro_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings'
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_theme_blog_listing_page_id',
            array(
                'sanitize_callback' => array( $this, 'sanitize_optional_page_id_setting' ),
                'default'           => 0,
            )
        );
        add_settings_field(
            'zskeleton_theme_blog_listing_page_id',
            __( 'Blog listing page (theme links)', 'zskeleton' ),
            array( $this, 'page_select_field_callback' ),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id'          => 'zskeleton_theme_blog_listing_page_id',
                'none_label'  => __( '— Use main Blog link (Posts page, /blog/, or theme mapping)', 'zskeleton' ),
                'description' => __( 'Optional page for the sidebar “View All Posts” button. When unset, uses the main Blog link (Reading → Posts page, /blog/, or theme mapping).', 'zskeleton' ),
            )
        );

        $sidebar_browse_link_options = array(
            'zskeleton_sidebar_browse_show_about'        => __( 'About', 'zskeleton' ),
            'zskeleton_sidebar_browse_show_faqs'         => __( 'FAQs', 'zskeleton' ),
            'zskeleton_sidebar_browse_show_memberships'  => __( 'Memberships', 'zskeleton' ),
            'zskeleton_sidebar_browse_show_contact'      => __( 'Contact', 'zskeleton' ),
        );
        foreach ( $sidebar_browse_link_options as $option_name => $label ) {
            register_setting(
                self::OPTION_GROUP,
                $option_name,
                array(
                    'sanitize_callback' => array( $this, 'sanitize_on_off_checkbox' ),
                    'default'           => '1',
                )
            );
        }
        add_settings_field(
            'zskeleton_sidebar_browse_links',
            __( 'Sidebar “Browse by Page” links', 'zskeleton' ),
            array( $this, 'sidebar_browse_links_field_callback' ),
            'zskeleton-content-settings',
            'zskeleton_content_settings'
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_show_featured',
            array(
                'sanitize_callback' => array($this, 'sanitize_on_off_checkbox'),
                'default' => '1',
            )
        );
        add_settings_field(
            'zskeleton_blog_show_featured',
            __('Featured posts strip', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_show_featured',
                'default' => '1',
                'description' => __('Show a featured row: posts flagged “Show in blog Featured strip” first, then sticky posts. Remaining slots stay empty—recent posts are not pulled in.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_featured_count',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_blog_featured_count',
                'default' => '3',
            )
        );
        add_settings_field(
            'zskeleton_blog_featured_count',
            __('Featured posts count', 'zskeleton'),
            array($this, 'number_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_featured_count',
                'default' => '3',
                'min' => '1',
                'max' => '12',
                'description' => __('How many cards appear in the featured strip.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_exclude_featured_from_latest',
            array(
                'sanitize_callback' => array($this, 'sanitize_on_off_checkbox'),
                'default' => '1',
            )
        );
        add_settings_field(
            'zskeleton_blog_exclude_featured_from_latest',
            __('De-duplicate latest list', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_exclude_featured_from_latest',
                'default' => '1',
                'description' => __('On the first page, hide posts that already appear in the featured strip from the paginated “Latest” grid.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_show_category_blocks',
            array(
                'sanitize_callback' => array($this, 'sanitize_on_off_checkbox'),
                'default' => '1',
            )
        );
        add_settings_field(
            'zskeleton_blog_show_category_blocks',
            __('Category blocks', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_show_category_blocks',
                'default' => '1',
                'description' => __('Show a grid of top-level categories (term archive links), not recent posts under each heading.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_category_blocks_count',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_blog_category_blocks_count',
                'default' => '6',
            )
        );
        add_settings_field(
            'zskeleton_blog_category_blocks_count',
            __('Number of categories to list', 'zskeleton'),
            array($this, 'number_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_category_blocks_count',
                'default' => '6',
                'min' => '1',
                'max' => '12',
                'description' => __('How many top-level categories to show (ordered by number of posts).', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_category_terms_layout',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_blog_category_terms_layout',
                'default' => 'thumbnails',
            )
        );
        add_settings_field(
            'zskeleton_blog_category_terms_layout',
            __('Category listing style', 'zskeleton'),
            array($this, 'select_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_category_terms_layout',
                'default' => 'thumbnails',
                'options' => array(
                    'thumbnails' => __('Thumbnails (listing image + optional icon)', 'zskeleton'),
                    'icons' => __('Icons (term icon or initial)', 'zskeleton'),
                    'simple' => __('Simple (text cards; description when set)', 'zskeleton'),
                ),
                'description' => __('Uses the shared term listing template. Icons and images are set per category under Posts → Categories.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_show_trending',
            array(
                'sanitize_callback' => array($this, 'sanitize_on_off_checkbox'),
                'default' => '1',
            )
        );
        add_settings_field(
            'zskeleton_blog_show_trending',
            __('Trending / most-read section', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_show_trending',
                'default' => '1',
                'description' => __('Show a row of popular posts after the latest grid and before the category listing (when both sections are enabled).', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_trending_count',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_blog_trending_count',
                'default' => '5',
            )
        );
        add_settings_field(
            'zskeleton_blog_trending_count',
            __('Trending posts count', 'zskeleton'),
            array($this, 'number_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_trending_count',
                'default' => '5',
                'min' => '1',
                'max' => '12',
                'description' => __('How many posts appear in the trending / most-read row.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_trending_mode',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_blog_trending_mode',
                'default' => 'comments',
            )
        );
        add_settings_field(
            'zskeleton_blog_trending_mode',
            __('Trending ranking', 'zskeleton'),
            array($this, 'select_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_trending_mode',
                'default' => 'comments',
                'options' => array(
                    'comments' => __('Most commented (built-in)', 'zskeleton'),
                    'views' => __('Most read (theme view counter)', 'zskeleton'),
                ),
                'description' => __('“Most read” requires view tracking below and enough time for counts to accumulate.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_track_post_views',
            array(
                'sanitize_callback' => array($this, 'sanitize_on_off_checkbox'),
                'default' => '1',
            )
        );
        add_settings_field(
            'zskeleton_blog_track_post_views',
            __('Track post views', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_track_post_views',
                'default' => '1',
                'description' => __('Increment a simple read counter when visitors open single posts (throttled per visitor). Used when trending ranking is “Most read”.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_show_lead_block',
            array(
                'sanitize_callback' => array($this, 'sanitize_on_off_checkbox'),
                'default' => '1',
            )
        );
        add_settings_field(
            'zskeleton_blog_show_lead_block',
            __('Lead generation block', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_show_lead_block',
                'default' => '1',
                'description' => __('Show a call-to-action band at the bottom of the first blog listing page when at least one lead field or the button URL is saved.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_lead_title',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            )
        );
        add_settings_field(
            'zskeleton_blog_lead_title',
            __('Lead block title', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_lead_title',
                'default' => '',
                'description' => __('Leave empty to use the theme default (translatable).', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_lead_text',
            array(
                'sanitize_callback' => 'sanitize_textarea_field',
                'default' => '',
            )
        );
        add_settings_field(
            'zskeleton_blog_lead_text',
            __('Lead block text', 'zskeleton'),
            array($this, 'textarea_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_lead_text',
                'default' => '',
                'description' => __('Supporting sentence or two. Leave empty for the default.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_lead_button_text',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '',
            )
        );
        add_settings_field(
            'zskeleton_blog_lead_button_text',
            __('Lead block button label', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_lead_button_text',
                'default' => '',
                'description' => __('Leave empty for the default “Subscribe”.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_blog_lead_button_url',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_blog_lead_button_url',
                'default' => '',
            )
        );
        add_settings_field(
            'zskeleton_blog_lead_button_url',
            __('Lead block button URL', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_blog_lead_button_url',
                'default' => '',
                'description' => __('Link for the button (newsletter page, contact form, external URL, etc.). Saving only this URL is enough to show the block (title, text, and label use defaults until you customize them).', 'zskeleton'),
            )
        );

        // Show Excerpts
        register_setting(self::OPTION_GROUP, 'zskeleton_show_excerpts');
        add_settings_field(
            'zskeleton_show_excerpts',
            __('Show Excerpts', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_show_excerpts',
                'default' => '1',
                'description' => __('Show excerpts instead of full content in archives', 'zskeleton')
            )
        );

        // Media uploads: WebP / SVG (disabled by default).
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_allow_upload_webp',
            array(
                'sanitize_callback' => array($this, 'sanitize_on_off_checkbox'),
                'default' => '0',
            )
        );
        add_settings_field(
            'zskeleton_allow_upload_webp',
            __('Allow WebP uploads', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_allow_upload_webp',
                'default' => '0',
                'description' => __('Allow uploading .webp images to the Media Library. If PHP cannot resize WebP, WordPress may still store the full-size file only (enable GD or Imagick with WebP on the server for thumbnails).', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_allow_upload_svg',
            array(
                'sanitize_callback' => array($this, 'sanitize_on_off_checkbox'),
                'default' => '0',
            )
        );
        add_settings_field(
            'zskeleton_allow_upload_svg',
            __('Allow SVG uploads', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_allow_upload_svg',
                'default' => '0',
                'description' => __('Allow uploading .svg images. SVG can contain scripts; enable only if you trust everyone who can upload files.', 'zskeleton'),
            )
        );

        // Footer Widget Area 1 fallback copy
        $blog_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_footer_widget_area_1_heading',
            array(
                'sanitize_callback' => 'sanitize_text_field',
            )
        );
        add_settings_field(
            'zskeleton_footer_widget_area_1_heading',
            __('Footer Widget Area 1 Heading', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_footer_widget_area_1_heading',
                'default' => sprintf(__('About %s', 'zskeleton'), $blog_name),
                'description' => __('Default heading shown in Footer Widget Area 1 when the area has no widgets.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_footer_widget_area_1_description',
            array(
                'sanitize_callback' => 'sanitize_textarea_field',
            )
        );
        add_settings_field(
            'zskeleton_footer_widget_area_1_description',
            __('Footer Widget Area 1 Description', 'zskeleton'),
            array($this, 'textarea_field_callback'),
            'zskeleton-content-settings',
            'zskeleton_content_settings',
            array(
                'id' => 'zskeleton_footer_widget_area_1_description',
                'default' => sprintf(
                    __('%s is a reusable WordPress base theme built for modern membership and content websites.', 'zskeleton'),
                    $blog_name
                ),
                'description' => __('Default description shown in Footer Widget Area 1 when the area has no widgets.', 'zskeleton'),
            )
        );
    }

    /**
     * Register newsletter settings
     */
    private function register_newsletter_settings() {
        // Newsletter Title
        register_setting(self::OPTION_GROUP, 'zskeleton_newsletter_title');
        add_settings_field(
            'zskeleton_newsletter_title',
            __('Newsletter Section Title', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-newsletter-settings',
            'zskeleton_newsletter_settings',
            array(
                'id' => 'zskeleton_newsletter_title',
                'default' => 'Stay Updated with ZSkeleton',
                'description' => __('Title for the newsletter signup section', 'zskeleton')
            )
        );

        // Newsletter Description
        register_setting(self::OPTION_GROUP, 'zskeleton_newsletter_description');
        add_settings_field(
            'zskeleton_newsletter_description',
            __('Newsletter Description', 'zskeleton'),
            array($this, 'textarea_field_callback'),
            'zskeleton-newsletter-settings',
            'zskeleton_newsletter_settings',
            array(
                'id' => 'zskeleton_newsletter_description',
                'default' => 'Get updates, releases, and useful content delivered to your inbox.',
                'description' => __('Description text for newsletter signup', 'zskeleton')
            )
        );

        // Newsletter Service
        register_setting(self::OPTION_GROUP, 'zskeleton_newsletter_service');
        add_settings_field(
            'zskeleton_newsletter_service',
            __('Newsletter Service', 'zskeleton'),
            array($this, 'select_field_callback'),
            'zskeleton-newsletter-settings',
            'zskeleton_newsletter_settings',
            array(
                'id' => 'zskeleton_newsletter_service',
                'default' => 'wordpress',
                'options' => array(
                    'wordpress' => __('WordPress (Store in database)', 'zskeleton'),
                    'mailchimp' => __('Mailchimp', 'zskeleton'),
                    'constantcontact' => __('Constant Contact', 'zskeleton'),
                    'custom' => __('Custom Integration', 'zskeleton')
                ),
                'description' => __('Newsletter service provider', 'zskeleton')
            )
        );

        // Newsletter API Key
        register_setting(self::OPTION_GROUP, 'zskeleton_newsletter_api_key');
        add_settings_field(
            'zskeleton_newsletter_api_key',
            __('Newsletter API Key', 'zskeleton'),
            array($this, 'password_field_callback'),
            'zskeleton-newsletter-settings',
            'zskeleton_newsletter_settings',
            array(
                'id' => 'zskeleton_newsletter_api_key',
                'default' => '',
                'description' => __('API key for newsletter service (if applicable)', 'zskeleton')
            )
        );

        // MailerLite Integration Status
        add_settings_field(
            'zskeleton_mailerlite_status',
            __('MailerLite Integration', 'zskeleton'),
            array($this, 'mailerlite_status_callback'),
            'zskeleton-newsletter-settings',
            'zskeleton_newsletter_settings',
            array(
                'description' => __('Status of MailerLite plugin integration', 'zskeleton')
            )
        );

        // Enable MailerLite Integration
        register_setting(self::OPTION_GROUP, 'zskeleton_enable_mailerlite');
        add_settings_field(
            'zskeleton_enable_mailerlite',
            __('Enable MailerLite', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-newsletter-settings',
            'zskeleton_newsletter_settings',
            array(
                'id' => 'zskeleton_enable_mailerlite',
                'default' => false,
                'description' => __('Enable MailerLite integration for newsletter subscriptions', 'zskeleton')
            )
        );

        // MailerLite Individual Members Group
        register_setting(self::OPTION_GROUP, 'zskeleton_mailerlite_individual_group');
        add_settings_field(
            'zskeleton_mailerlite_individual_group',
            __('Individual Members Group ID', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-newsletter-settings',
            'zskeleton_newsletter_settings',
            array(
                'id' => 'zskeleton_mailerlite_individual_group',
                'default' => '',
                'description' => __('MailerLite group ID for individual member registrations', 'zskeleton'),
                'placeholder' => __('e.g., 123456789', 'zskeleton')
            )
        );

        // MailerLite Organizational Members Group
        register_setting(self::OPTION_GROUP, 'zskeleton_mailerlite_organization_group');
        add_settings_field(
            'zskeleton_mailerlite_organization_group',
            __('Organizational Members Group ID', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-newsletter-settings',
            'zskeleton_newsletter_settings',
            array(
                'id' => 'zskeleton_mailerlite_organization_group',
                'default' => '',
                'description' => __('MailerLite group ID for organizational member registrations', 'zskeleton'),
                'placeholder' => __('e.g., 987654321', 'zskeleton')
            )
        );

        // MailerLite Corporate Members Group
        register_setting(self::OPTION_GROUP, 'zskeleton_mailerlite_corporate_group');
        add_settings_field(
            'zskeleton_mailerlite_corporate_group',
            __('Corporate Members Group ID', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-newsletter-settings',
            'zskeleton_newsletter_settings',
            array(
                'id' => 'zskeleton_mailerlite_corporate_group',
                'default' => '',
                'description' => __('MailerLite group ID for corporate member registrations', 'zskeleton'),
                'placeholder' => __('e.g., 111222333', 'zskeleton')
            )
        );

        // MailerLite General Newsletter Group
        register_setting(self::OPTION_GROUP, 'zskeleton_mailerlite_general_group');
        add_settings_field(
            'zskeleton_mailerlite_general_group',
            __('General Newsletter Group ID', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-newsletter-settings',
            'zskeleton_newsletter_settings',
            array(
                'id' => 'zskeleton_mailerlite_general_group',
                'default' => '',
                'description' => __('MailerLite group ID for general newsletter signups (contact form, glossary, etc.)', 'zskeleton'),
                'placeholder' => __('e.g., 444555666', 'zskeleton')
            )
        );

        // MailerLite Tool Submitters Group
        register_setting(self::OPTION_GROUP, 'zskeleton_mailerlite_tools_group');
        add_settings_field(
            'zskeleton_mailerlite_tools_group',
            __('Tool Submitters Group ID', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-newsletter-settings',
            'zskeleton_newsletter_settings',
            array(
                'id' => 'zskeleton_mailerlite_tools_group',
                'default' => '',
                'description' => __('MailerLite group ID for submission form signups', 'zskeleton'),
                'placeholder' => __('e.g., 777888999', 'zskeleton')
            )
        );

        // MailerLite Groups List Helper
        add_settings_field(
            'zskeleton_mailerlite_groups_list',
            __('Available Groups', 'zskeleton'),
            array($this, 'mailerlite_groups_list_callback'),
            'zskeleton-newsletter-settings',
            'zskeleton_newsletter_settings',
            array(
                'description' => __('List of available MailerLite groups', 'zskeleton')
            )
        );
    }

    /**
     * Register performance settings
     */
    private function register_performance_settings() {
        // Use Minified Assets
        register_setting(self::OPTION_GROUP, 'zskeleton_use_minified_assets');
        add_settings_field(
            'zskeleton_use_minified_assets',
            __('Use Minified Assets', 'zskeleton'),
            array($this, 'checkbox_field_callback'),
            'zskeleton-performance-settings',
            'zskeleton_performance_settings',
            array(
                'id' => 'zskeleton_use_minified_assets',
                'default' => true,
                'description' => __('Enable minified CSS and JavaScript files for better performance. Disable for development/debugging.', 'zskeleton')
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_combine_theme_css',
            array(
                'sanitize_callback' => array( $this, 'sanitize_on_off_checkbox' ),
                'default'           => '0',
            )
        );
        add_settings_field(
            'zskeleton_combine_theme_css',
            __( 'Combine global CSS', 'zskeleton' ),
            array( $this, 'checkbox_field_callback' ),
            'zskeleton-performance-settings',
            'zskeleton_performance_settings',
            array(
                'id'          => 'zskeleton_combine_theme_css',
                'default'     => '0',
                'description' => __( 'Merge the core theme stylesheets (main, components, widgets, page title bar) into one cached file to cut HTTP requests. Optional paths below are appended to that bundle. Page-specific styles still load separately when their templates enqueue them—avoid listing the same file here unless you want it globally and accept possible duplication. Requires a writable uploads directory.', 'zskeleton' ),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_combine_theme_css_extra_list',
            array(
                'sanitize_callback' => 'zskeleton_sanitize_combine_theme_css_extra_list_option',
                'default'           => '',
            )
        );
        add_settings_field(
            'zskeleton_combine_theme_css_extra_list',
            __( 'Extra CSS files in combined bundle', 'zskeleton' ),
            array( $this, 'textarea_field_callback' ),
            'zskeleton-performance-settings',
            'zskeleton_performance_settings',
            array(
                'id'          => 'zskeleton_combine_theme_css_extra_list',
                'default'     => '',
                'rows'        => 10,
                'description' => __( 'One theme-relative path per line (forward slashes), under this theme directory only, ending in .css — for example: assets/css/page-contact.min.css Lines starting with # are comments. Invalid or unsafe paths are dropped on save; missing files are skipped until they exist. Shown order is concatenation order after the core stack. If you bundle page-single-shared, also list single-post (.min).css so post-hero rules stay complete.', 'zskeleton' ),
            )
        );

        // Performance Information
        add_settings_field(
            'zskeleton_performance_info',
            __('Performance Information', 'zskeleton'),
            array($this, 'performance_info_callback'),
            'zskeleton-performance-settings',
            'zskeleton_performance_settings',
            array(
                'description' => __('Current performance statistics and recommendations.', 'zskeleton')
            )
        );
    }

    /**
     * Register security settings
     */
    private function register_security_settings() {
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_captcha_provider',
            array(
                'sanitize_callback' => array($this, 'sanitize_captcha_provider'),
                'default' => 'google_recaptcha',
            )
        );
        add_settings_field(
            'zskeleton_captcha_provider',
            __('Bot protection', 'zskeleton'),
            array($this, 'select_field_callback'),
            'zskeleton-security-settings',
            'zskeleton_security_settings',
            array(
                'id' => 'zskeleton_captcha_provider',
                'default' => 'google_recaptcha',
                'options' => array(
                    'google_recaptcha' => __('Google reCAPTCHA v3', 'zskeleton'),
                    'cloudflare_turnstile' => __('Cloudflare Turnstile', 'zskeleton'),
                ),
                'description' => __('Choose which service validates submissions. Configure the matching keys below.', 'zskeleton'),
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_turnstile_site_key',
            array('sanitize_callback' => 'sanitize_text_field')
        );
        add_settings_field(
            'zskeleton_turnstile_site_key',
            __('Turnstile site key', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-security-settings',
            'zskeleton_security_settings',
            array(
                'id' => 'zskeleton_turnstile_site_key',
                'default' => '',
                'description' => __('Public site key from Cloudflare Turnstile (used when Turnstile is selected).', 'zskeleton'),
                'placeholder' => '0x...',
            )
        );

        register_setting(
            self::OPTION_GROUP,
            'zskeleton_turnstile_secret_key',
            array('sanitize_callback' => 'sanitize_text_field')
        );
        add_settings_field(
            'zskeleton_turnstile_secret_key',
            __('Turnstile secret key', 'zskeleton'),
            array($this, 'password_field_callback'),
            'zskeleton-security-settings',
            'zskeleton_security_settings',
            array(
                'id' => 'zskeleton_turnstile_secret_key',
                'default' => '',
                'description' => __('Secret key from Cloudflare Turnstile. Keep this confidential.', 'zskeleton'),
                'placeholder' => '0x...',
            )
        );

        // reCAPTCHA Site Key
        register_setting(self::OPTION_GROUP, 'zskeleton_recaptcha_site_key');
        add_settings_field(
            'zskeleton_recaptcha_site_key',
            __('reCAPTCHA site key', 'zskeleton'),
            array($this, 'text_field_callback'),
            'zskeleton-security-settings',
            'zskeleton_security_settings',
            array(
                'id' => 'zskeleton_recaptcha_site_key',
                'default' => '',
                'description' => __('Google reCAPTCHA v3 site key (used when Google reCAPTCHA is selected). Get keys at Google reCAPTCHA Admin.', 'zskeleton'),
                'placeholder' => '6Lc...'
            )
        );

        // reCAPTCHA Secret Key
        register_setting(self::OPTION_GROUP, 'zskeleton_recaptcha_secret_key');
        add_settings_field(
            'zskeleton_recaptcha_secret_key',
            __('reCAPTCHA secret key', 'zskeleton'),
            array($this, 'password_field_callback'),
            'zskeleton-security-settings',
            'zskeleton_security_settings',
            array(
                'id' => 'zskeleton_recaptcha_secret_key',
                'default' => '',
                'description' => __('Google reCAPTCHA v3 secret key (used when Google reCAPTCHA is selected). Keep this confidential.', 'zskeleton'),
                'placeholder' => '6Lc...'
            )
        );

        // reCAPTCHA Score Threshold
        register_setting(self::OPTION_GROUP, 'zskeleton_recaptcha_threshold');
        add_settings_field(
            'zskeleton_recaptcha_threshold',
            __('reCAPTCHA score threshold', 'zskeleton'),
            array($this, 'number_field_callback'),
            'zskeleton-security-settings',
            'zskeleton_security_settings',
            array(
                'id' => 'zskeleton_recaptcha_threshold',
                'default' => '0.5',
                'description' => __('Google reCAPTCHA v3 only: minimum score (0.0 to 1.0). Default 0.5.', 'zskeleton'),
                'min' => '0',
                'max' => '1',
                'step' => '0.1'
            )
        );

        // GitHub Access Token for theme updates
        register_setting(
            self::OPTION_GROUP,
            'zskeleton_github_access_token',
            array(
                'sanitize_callback' => 'sanitize_text_field',
            )
        );
        add_settings_field(
            'zskeleton_github_access_token',
            __('GitHub Access Token', 'zskeleton'),
            array($this, 'password_field_callback'),
            'zskeleton-security-settings',
            'zskeleton_security_settings',
            array(
                'id' => 'zskeleton_github_access_token',
                'default' => '',
                'description' => __('Personal access token used to authenticate private GitHub repository requests for theme updates. Keep this token secure.', 'zskeleton'),
                'placeholder' => __('ghp_xxxxxxxxxxxxxxxxxxxx', 'zskeleton'),
            )
        );

        // Bot protection status panel
        add_settings_field(
            'zskeleton_recaptcha_status',
            __('Status', 'zskeleton'),
            array($this, 'recaptcha_status_callback'),
            'zskeleton-security-settings',
            'zskeleton_security_settings'
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'appearance_page_zskeleton-theme-settings') {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_media();
        
        // Check if minified assets should be used
        $use_minified = get_theme_mod('zskeleton_use_minified_assets', true);
        // Theme version + Unix time (cache bust for admin JS/CSS).
        $asset_version = ZSkeleton_VERSION . '.' . time();
        
        // Enqueue admin settings styles - use minified or original based on setting
        $admin_settings_css_file = $use_minified ? 'admin-settings.min.css' : 'admin-settings.css';
        wp_enqueue_style(
            'zskeleton-admin-settings',
            ZSkeleton_THEME_URL . '/assets/css/' . $admin_settings_css_file,
            array(),
            $asset_version
        );

        // Enqueue admin settings JavaScript.
        // Note: we intentionally load the non-minified script here so the validation/focus behavior is consistent.
        $admin_settings_js_file = 'admin-settings.js';
        wp_enqueue_script(
            'zskeleton-admin-settings',
            ZSkeleton_THEME_URL . '/assets/js/' . $admin_settings_js_file,
            array('jquery', 'wp-color-picker'),
            $asset_version,
            true
        );

        // Localize script for AJAX
        wp_localize_script('zskeleton-admin-settings', 'zskeletonAdminAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('zskeleton_admin_nonce'),
            'strings' => array(
                'confirm_reset' => __('Are you sure you want to reset all settings to defaults? This action cannot be undone.', 'zskeleton'),
                'confirm_bulk_action' => __('Are you sure you want to perform this bulk action?', 'zskeleton'),
                'success' => __('Action completed successfully!', 'zskeleton'),
                'error' => __('An error occurred. Please try again.', 'zskeleton'),
                'loading' => __('Loading...', 'zskeleton'),
                'saved' => __('Settings saved!', 'zskeleton')
            )
        ));
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap zskeleton-settings-wrap">
            <h1><?php _e('ZSkeleton Theme Settings', 'zskeleton'); ?></h1>
            <?php settings_errors(); ?>
            <?php
            // Separate POST target for "Set default theme auth pages" (must NOT nest <form> inside .zskeleton-settings-form — that breaks the main Save button in browsers).
            ?>
            <form id="zskeleton-auth-pages-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" hidden>
                <input type="hidden" name="action" value="zskeleton_set_default_auth_pages" />
                <?php wp_nonce_field('zskeleton_set_default_auth_pages'); ?>
            </form>
            <form id="zskeleton-common-pages-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" hidden>
                <input type="hidden" name="action" value="zskeleton_install_common_pages" />
                <?php wp_nonce_field('zskeleton_install_common_pages'); ?>
            </form>
            <div class="zskeleton-settings-header">
                <p><?php _e('Configure your ZSkeleton theme settings to customize your website appearance and functionality.', 'zskeleton'); ?></p>
            </div>

            <div class="zskeleton-settings-tabs">
                <nav class="nav-tab-wrapper zskeleton-settings-nav">
                    <a href="#branding" class="nav-tab nav-tab-active"><?php _e('Branding', 'zskeleton'); ?></a>
                    <a href="#appearance" class="nav-tab"><?php _e('Appearance', 'zskeleton'); ?></a>
                    <a href="#layout" class="nav-tab"><?php _e('Layout', 'zskeleton'); ?></a>
                    <a href="#contact-social" class="nav-tab"><?php _e('Contact & social', 'zskeleton'); ?></a>
                    <a href="#homepage" class="nav-tab"><?php _e('Homepage', 'zskeleton'); ?></a>
                    <a href="#content" class="nav-tab"><?php _e('Content', 'zskeleton'); ?></a>
                    <a href="#newsletter" class="nav-tab"><?php _e('Newsletter', 'zskeleton'); ?></a>
                    <a href="#security" class="nav-tab"><?php _e('Security', 'zskeleton'); ?></a>
                    <a href="#performance" class="nav-tab"><?php _e('Performance', 'zskeleton'); ?></a>
                </nav>

                <?php
                // Use admin-post.php so saving does not depend on options.php seeing $_REQUEST['action'] === 'update'
                // (some environments break that and show “All Settings” with no save).
                $zskeleton_theme_return = admin_url('themes.php?page=zskeleton-theme-settings');
                ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="zskeleton-settings-form" novalidate="novalidate">
                    <input type="hidden" name="action" value="zskeleton_save_theme_settings" />
                    <?php wp_nonce_field('zskeleton_save_theme_settings'); ?>
                    <input type="hidden" name="zskeleton_options_return_url" value="<?php echo esc_attr(esc_url($zskeleton_theme_return)); ?>" />

                    <div id="branding" class="tab-content active">
                        <h2><?php _e('Branding', 'zskeleton'); ?></h2>
                        <?php do_settings_sections('zskeleton-branding-settings'); ?>
                    </div>

                    <div id="appearance" class="tab-content">
                        <h2><?php _e('Appearance', 'zskeleton'); ?></h2>
                        <?php do_settings_sections('zskeleton-appearance-settings'); ?>
                    </div>

                    <div id="layout" class="tab-content">
                        <h2><?php _e('Layout', 'zskeleton'); ?></h2>
                        <?php do_settings_sections('zskeleton-layout-settings'); ?>
                    </div>

                    <div id="contact-social" class="tab-content">
                        <h2><?php _e('Contact & social', 'zskeleton'); ?></h2>
                        <?php do_settings_sections('zskeleton-contact-social-settings'); ?>
                    </div>

                    <div id="homepage" class="tab-content">
                        <h2><?php _e('Homepage Settings', 'zskeleton'); ?></h2>
                        <?php do_settings_sections('zskeleton-homepage-settings'); ?>
                    </div>

                    <div id="content" class="tab-content">
                        <h2><?php _e('Content Settings', 'zskeleton'); ?></h2>
                        <?php do_settings_sections('zskeleton-content-settings'); ?>
                    </div>

                    <div id="newsletter" class="tab-content">
                        <h2><?php _e('Newsletter Settings', 'zskeleton'); ?></h2>
                        <?php do_settings_sections('zskeleton-newsletter-settings'); ?>
                    </div>

                    <div id="security" class="tab-content">
                        <h2><?php _e('Security Settings', 'zskeleton'); ?></h2>
                        <?php do_settings_sections('zskeleton-security-settings'); ?>
                    </div>

                    <div id="performance" class="tab-content">
                        <h2><?php _e('Performance Settings', 'zskeleton'); ?></h2>
                        <?php do_settings_sections('zskeleton-performance-settings'); ?>
                    </div>

                    <?php
                    // Avoid name="submit" — it shadows the form's submit() method in the DOM and can break submits in some browsers.
                    submit_button(__('Save Settings', 'zskeleton'), 'primary large', 'zskeleton_theme_settings_save');
                    ?>
                </form>

                <div style="margin-top: 16px;">
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="zskeleton_clear_update_cache">
                        <?php wp_nonce_field( 'zskeleton_clear_update_cache_nonce', 'zskeleton_clear_update_cache_nonce' ); ?>
                        <?php submit_button( __('Clear Update Cache', 'zskeleton'), 'secondary', 'submit', false ); ?>
                        <p class="description" style="margin-top:4px;"><?php _e('Force refresh of theme update data (clears cached update/transient).', 'zskeleton'); ?></p>
                    </form>
                </div>
            </div>

            <div class="zskeleton-settings-sidebar">
                <div class="zskeleton-help-box">
                    <h3><?php _e('Need Help?', 'zskeleton'); ?></h3>
                    <p><?php _e('For detailed documentation and support, visit:', 'zskeleton'); ?></p>
                    <ul>
                        <li><a href="#" target="_blank"><?php _e('Theme Documentation', 'zskeleton'); ?></a></li>
                        <li><a href="#" target="_blank"><?php _e('Support Forum', 'zskeleton'); ?></a></li>
                        <li><a href="mailto:support@zskeleton.org"><?php _e('Contact Support', 'zskeleton'); ?></a></li>
                    </ul>
                </div>

                <div class="zskeleton-stats-box">
                    <h3><?php _e('Theme Statistics', 'zskeleton'); ?></h3>
                    <?php if ( class_exists( 'ZSkeleton_Membership_Manager' ) ) : ?>
                        <?php $stats = ZSkeleton_Membership_Manager::get_membership_statistics(); ?>
                        <ul>
                            <li><?php printf( __( 'Total Members: %d', 'zskeleton' ), $stats['total_members'] ); ?></li>
                            <li><?php printf( __( 'Individual: %d', 'zskeleton' ), $stats['individual_members'] ); ?></li>
                            <li><?php printf( __( 'Organizational: %d', 'zskeleton' ), $stats['organizational_members'] ); ?></li>
                        </ul>
                    <?php else : ?>
                        <p><?php _e( 'Activate the ZSkeleton Membership & Payments plugin to show membership statistics.', 'zskeleton' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <style>
        .zskeleton-settings-wrap {
            max-width: 1200px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
        }

        .zskeleton-settings-header {
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 20px;
            grid-column: 1 / -1;
        }

        .zskeleton-settings-tabs {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .zskeleton-settings-nav {
            flex-wrap: wrap;
            row-gap: 4px;
        }

        .tab-content {
            display: none;
            padding: 20px;
        }

        .tab-content.active {
            display: block;
        }

        .zskeleton-settings-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .zskeleton-help-box,
        .zskeleton-stats-box {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 20px;
        }

        .zskeleton-help-box h3,
        .zskeleton-stats-box h3 {
            margin-top: 0;
            color: #1e3a8a;
        }

        @media (max-width: 1024px) {
            .zskeleton-settings-wrap {
                grid-template-columns: 1fr;
            }
        }
        </style>
        <?php
    }

    /**
     * Section callbacks
     */
    public function branding_settings_callback() {
        echo '<p>' . esc_html__('Logos and site title text shown in the header and elsewhere.', 'zskeleton') . '</p>';
    }

    public function appearance_settings_callback() {
        echo '<p>' . esc_html__('Theme palette and web fonts (mirrors Customizer color controls where applicable).', 'zskeleton') . '</p>';
    }

    public function layout_settings_callback() {
        echo '<p>' . esc_html__('Structural choices for the header, the footer widget row, and the floating back to top control.', 'zskeleton') . '</p>';
    }

    /**
     * Section intro: top bar and footer copyright strip (colors, Appearance tab).
     *
     * @return void
     */
    public function appearance_header_footer_bars_callback() {
        echo '<p>' . esc_html__( 'Gradients and text for the header top bar and the footer copyright row. They default to your palette Primary, academic navy, and white; clear both gradient stops on a row to follow only the global background for that strip.', 'zskeleton' ) . '</p>';
    }

    /**
     * Section intro: top bar and footer copyright (spacing, Layout tab).
     *
     * @return void
     */
    public function layout_bars_spacing_settings_callback() {
        echo '<p>' . esc_html__( 'Padding and min-heights for the top bar and the copyright row. For gradients and text colors, use Appearance → Top bar and footer copyright (colors).', 'zskeleton' ) . '</p>';
    }

    public function contact_social_settings_callback() {
        echo '<p>' . esc_html__('Phone numbers, contact email, default map embed, and social profile URLs (synced with Appearance → Customize → Contact & social).', 'zskeleton') . '</p>';
    }

    public function homepage_settings_callback() {
        echo '<p>' . __('Customize your homepage layout and content.', 'zskeleton') . '</p>';
    }

    public function content_settings_callback() {
        echo '<p>' . __('Manage content display and access restrictions.', 'zskeleton') . '</p>';
        echo '<p>' . __('Auth pages control where users go for login and password flows. When WooCommerce “Allow customers to place orders without an account” is enabled, guests can go straight to checkout for a plan; otherwise they are sent to the login page below first.', 'zskeleton') . '</p>';
    }

    /**
     * Intro text for Blog listing template options (no option value).
     *
     * @return void
     */
    public function blog_listing_hub_intro_callback() {
        echo '<p class="description">' . esc_html__('Applies to the classic “Blog listing” page template and to ZSkeleton blog blocks when you build a page with the “Blog listing (block editor)” template: featured strip, latest grid, trending or most-read, category term listing, and lead CTA.', 'zskeleton') . '</p>';
    }

    public function newsletter_settings_callback() {
        echo '<p>' . __('Configure newsletter signup and integration.', 'zskeleton') . '</p>';
    }

    public function performance_settings_callback() {
        echo '<p>' . __('Optimize your website performance with minified assets and caching options.', 'zskeleton') . '</p>';
    }

    public function security_settings_callback() {
        echo '<p>' . __('Use Google reCAPTCHA v3 (invisible score) or Cloudflare Turnstile. Protection applies only when the selected provider has both keys saved.', 'zskeleton') . '</p>';
    }

    /**
     * Field callbacks
     */
    public function text_field_callback($args) {
        $value = get_option($args['id'], $args['default']);
        printf(
            '<input type="text" id="%s" name="%s" value="%s" class="regular-text" />',
            esc_attr($args['id']),
            esc_attr($args['id']),
            esc_attr($value)
        );
        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    public function textarea_field_callback($args) {
        $value = get_option($args['id'], $args['default']);
        printf(
            '<textarea id="%s" name="%s" rows="%d" class="large-text">%s</textarea>',
            esc_attr($args['id']),
            esc_attr($args['id']),
            isset($args['rows']) ? max(2, min(40, (int) $args['rows'])) : 3,
            esc_textarea($value)
        );
        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    public function color_field_callback($args) {
        $value = get_option($args['id'], $args['default']);
        printf(
            '<input type="text" id="%s" name="%s" value="%s" class="color-picker" />',
            esc_attr($args['id']),
            esc_attr($args['id']),
            esc_attr($value)
        );
        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    public function email_field_callback($args) {
        $value = get_option($args['id'], $args['default']);
        printf(
            '<input type="email" id="%s" name="%s" value="%s" class="regular-text" />',
            esc_attr($args['id']),
            esc_attr($args['id']),
            esc_attr($value)
        );
        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    /**
     * Slider post ID for the Arabic SEO homepage template hero (0 = use static hero).
     *
     * @param mixed $value Raw value.
     * @return int
     */
    public function sanitize_homepage_seo_ar_slider_id($value) {
        $id = absint($value);
        if (!$id) {
            return 0;
        }
        $post = get_post($id);
        if (!$post instanceof WP_Post) {
            return 0;
        }
        if ('zskeleton_slider' !== $post->post_type || 'publish' !== $post->post_status) {
            return 0;
        }
        return $id;
    }

    /**
     * Dropdown: published sliders for the Arabic SEO homepage hero.
     *
     * @param array<string,string> $args Field args.
     * @return void
     */
    public function homepage_seo_ar_slider_field_callback($args) {
        $current = absint(get_option('zskeleton_homepage_seo_ar_slider_id', 0));
        $sliders = get_posts(
            array(
                'post_type'      => 'zskeleton_slider',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'title',
                'order'          => 'ASC',
                'no_found_rows'  => true,
            )
        );
        echo '<select id="zskeleton_homepage_seo_ar_slider_id" name="zskeleton_homepage_seo_ar_slider_id" class="regular-text">';
        printf(
            '<option value="0"%s>%s</option>',
            selected($current, 0, false),
            esc_html__('— Built-in hero (no slider) —', 'zskeleton')
        );
        foreach ($sliders as $slider) {
            printf(
                '<option value="%d"%s>%s</option>',
                (int) $slider->ID,
                selected($current, (int) $slider->ID, false),
                esc_html(get_the_title($slider))
            );
        }
        echo '</select>';
        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    /**
     * Sanitize optional page ID (must be page post type).
     *
     * @param mixed $value Raw value.
     * @return int
     */
    public function sanitize_optional_page_id_setting($value) {
        $id = absint($value);
        if ($id && 'page' !== get_post_type($id)) {
            return 0;
        }
        return $id;
    }

    /**
     * Footer columns setting (1–4).
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_footer_widget_areas_count($value) {
        $n = absint($value);
        if ($n < 1) {
            $n = 1;
        }
        if ($n > 4) {
            $n = 4;
        }
        return (string) $n;
    }

    /**
     * Header layout slug.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    /**
     * Captcha provider: Google reCAPTCHA v3 or Cloudflare Turnstile.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_captcha_provider($value) {
        $value = is_string($value) ? $value : 'google_recaptcha';
        $allowed = array('google_recaptcha', 'cloudflare_turnstile');
        return in_array($value, $allowed, true) ? $value : 'google_recaptcha';
    }

    public function sanitize_header_layout_setting($value) {
        $v = (string) $value;
        $allowed = function_exists( 'zskeleton_get_header_layout_choices' )
            ? array_keys( zskeleton_get_header_layout_choices() )
            : array( 'default', 'split_top_search' );
        return in_array( $v, $allowed, true ) ? $v : 'default';
    }

    /**
     * Split header logo height in px.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_split_logo_height($value) {
        $n = absint($value);
        if ($n < 24) {
            $n = 56;
        }
        if ($n > 120) {
            $n = 120;
        }
        return (string) $n;
    }

    /**
     * Split header logo side padding in px.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_split_logo_side_padding($value) {
        $n = absint($value);
        if ($n > 180) {
            $n = 180;
        }
        return (string) $n;
    }

    /**
     * Checkbox stored as "1" or "0".
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_on_off_checkbox($value) {
        return '1' === (string) $value ? '1' : '0';
    }

    /**
     * WhatsApp float button target URL (https/wa.me/tel allowed).
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_whatsapp_float_url_setting($value) {
        $v = trim((string) $value);
        if ('' === $v) {
            return '';
        }
        $sanitized = esc_url_raw($v);
        if ('' === $sanitized) {
            return '';
        }
        $lower = strtolower($sanitized);
        if (str_starts_with($lower, 'https://') || str_starts_with($lower, 'http://') || str_starts_with($lower, 'tel:')) {
            return $sanitized;
        }
        return '';
    }

    /**
     * Map latitude (-90..90) or empty.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_map_latitude_setting($value) {
        $v = trim((string) $value);
        if ('' === $v) {
            return '';
        }
        if (!is_numeric($v)) {
            return '';
        }
        $f = (float) $v;
        if ($f < -90.0 || $f > 90.0) {
            return '';
        }
        return (string) $f;
    }

    /**
     * Map longitude (-180..180) or empty.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_map_longitude_setting($value) {
        $v = trim((string) $value);
        if ('' === $v) {
            return '';
        }
        if (!is_numeric($v)) {
            return '';
        }
        $f = (float) $v;
        if ($f < -180.0 || $f > 180.0) {
            return '';
        }
        return (string) $f;
    }

    /**
     * Map zoom 1–20.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_map_zoom_setting($value) {
        $n = absint($value);
        if ($n < 1) {
            $n = 1;
        }
        if ($n > 20) {
            $n = 20;
        }
        return (string) $n;
    }

    /**
     * Dropdown of pages for optional membership landing URL.
     *
     * @param array<string, string> $args Field args.
     */
    public function page_select_field_callback($args) {
        $id    = isset($args['id']) ? (string) $args['id'] : 'zskeleton_auth_login_page_id';
        $value = (int) get_option($id, 0);
        $none  = isset($args['none_label']) ? (string) $args['none_label'] : __( '— Select page', 'zskeleton' );
        wp_dropdown_pages(
            array(
                'name'              => $id,
                'id'                => $id,
                'selected'          => $value,
                'show_option_none'  => $none,
                'option_none_value' => '0',
            )
        );
        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    public function number_field_callback($args) {
        $value = get_option($args['id'], $args['default']);
        printf(
            '<input type="number" id="%s" name="%s" value="%s" min="%s" max="%s" step="%s" class="small-text" />',
            esc_attr($args['id']),
            esc_attr($args['id']),
            esc_attr($value),
            esc_attr($args['min'] ?? ''),
            esc_attr($args['max'] ?? ''),
            esc_attr($args['step'] ?? '1')
        );
        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    public function checkbox_field_callback($args) {
        $value = get_option($args['id'], $args['default']);
        // Hidden 0 ensures the key is always present in POST when unchecked (otherwise save would skip the field).
        printf(
            '<input type="hidden" name="%s" value="0" />',
            esc_attr($args['id'])
        );
        printf(
            '<label><input type="checkbox" id="%s" name="%s" value="1" %s /> %s</label>',
            esc_attr($args['id']),
            esc_attr($args['id']),
            checked($value, '1', false),
            esc_html($args['description'] ?? '')
        );
    }

    /**
     * Checkboxes for sidebar “Browse by Page” quick links.
     *
     * @return void
     */
    public function sidebar_browse_links_field_callback() {
        $links = array(
            'zskeleton_sidebar_browse_show_about'       => __( 'About', 'zskeleton' ),
            'zskeleton_sidebar_browse_show_faqs'        => __( 'FAQs', 'zskeleton' ),
            'zskeleton_sidebar_browse_show_memberships' => __( 'Memberships', 'zskeleton' ),
            'zskeleton_sidebar_browse_show_contact'     => __( 'Contact', 'zskeleton' ),
        );
        echo '<fieldset class="zskeleton-sidebar-browse-links">';
        foreach ( $links as $option_name => $label ) {
            $value = get_option( $option_name, '1' );
            printf( '<input type="hidden" name="%s" value="0" />', esc_attr( $option_name ) );
            printf(
                '<label style="display:block;margin-bottom:8px;"><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s /> %3$s</label>',
                esc_attr( $option_name ),
                checked( $value, '1', false ),
                esc_html( $label )
            );
        }
        echo '</fieldset>';
        echo '<p class="description">' . esc_html__( 'Choose which pages appear under “Browse by Page” in the sidebar. Memberships still requires the memberships feature to be enabled.', 'zskeleton' ) . '</p>';
    }

    public function radio_field_callback($args) {
        $value = get_option($args['id'], $args['default']);
        echo '<fieldset>';
        foreach ($args['options'] as $option_value => $option_label) {
            printf(
                '<label style="display: block; margin-bottom: 8px;"><input type="radio" name="%s" value="%s" %s /> %s</label>',
                esc_attr($args['id']),
                esc_attr($option_value),
                checked($value, $option_value, false),
                esc_html($option_label)
            );
        }
        echo '</fieldset>';
        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', wp_kses_post($args['description']));
        }
    }

    public function select_field_callback($args) {
        $value = get_option($args['id'], $args['default']);
        printf('<select id="%s" name="%s">', esc_attr($args['id']), esc_attr($args['id']));
        foreach ($args['options'] as $option_value => $option_label) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($option_value),
                selected($value, $option_value, false),
                esc_html($option_label)
            );
        }
        echo '</select>';
        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    /**
     * Google font dropdown: show curated list; if saved value is legacy/custom, prepend it so the choice is preserved.
     *
     * @param array $args Field args with id, default, options, description.
     */
    public function google_font_select_callback($args) {
        $value = get_option($args['id'], $args['default']);
        $options = isset($args['options']) && is_array($args['options']) ? $args['options'] : array();
        if ($value !== '' && ! array_key_exists($value, $options)) {
            $options = array_merge(
                array( $value => sprintf( __( 'Custom (saved): %s', 'zskeleton' ), $value ) ),
                $options
            );
        }
        $args['options'] = $options;
        $this->select_field_callback($args);
    }

    public function password_field_callback($args) {
        $value = get_option($args['id'], $args['default']);
        printf(
            '<input type="password" id="%s" name="%s" value="%s" class="regular-text" />',
            esc_attr($args['id']),
            esc_attr($args['id']),
            esc_attr($value)
        );
        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    /**
     * Image field callback
     */
    public function image_field_callback($args) {
        $value = get_option($args['id'], $args['default']);
        $image_url = '';
        $image_id = '';
        
        if ($value) {
            if (is_numeric($value)) {
                $image_id = $value;
                $image_url = wp_get_attachment_url($value);
            } else {
                $image_url = $value;
            }
        }
        
        echo '<div class="zskeleton-image-field">';
        echo '<div class="image-preview" style="margin-bottom: 10px;">';
        
        if ($image_url) {
            printf(
                '<img src="%s" alt="%s" style="max-width: 200px; max-height: 100px; display: block; margin-bottom: 10px;" />',
                esc_url($image_url),
                esc_attr__('Preview', 'zskeleton')
            );
        }
        
        echo '</div>';
        
        printf(
            '<input type="hidden" id="%s" name="%s" value="%s" class="image-field-input" />',
            esc_attr($args['id']),
            esc_attr($args['id']),
            esc_attr($value)
        );
        
        printf(
            '<button type="button" class="button image-upload-button" data-field="%s">%s</button>',
            esc_attr($args['id']),
            esc_html__('Upload Image', 'zskeleton')
        );
        
        if ($value) {
            printf(
                ' <button type="button" class="button image-remove-button" data-field="%s">%s</button>',
                esc_attr($args['id']),
                esc_html__('Remove Image', 'zskeleton')
            );
        }
        
        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
        
        echo '</div>';
    }

    /**
     * Performance info callback
     */
    public function performance_info_callback($args) {
        $use_minified = get_option('zskeleton_use_minified_assets', true);
        $combine_css  = '1' === (string) get_option( 'zskeleton_combine_theme_css', '0' );
        
        echo '<div class="zskeleton-performance-info" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin: 10px 0;">';
        
        echo '<h4 style="margin-top: 0; color: #1e3a8a;">' . __('Current Performance Status', 'zskeleton') . '</h4>';
        
        if ($use_minified) {
            echo '<div style="color: #059669; font-weight: 600; margin-bottom: 15px;">';
            echo '✅ ' . __('Minified assets are enabled', 'zskeleton');
            echo '</div>';
            
            echo '<div style="background: #d1fae5; border: 1px solid #a7f3d0; border-radius: 4px; padding: 15px; margin-bottom: 15px;">';
            echo '<h5 style="margin-top: 0; color: #065f46;">' . __('Performance Benefits', 'zskeleton') . '</h5>';
            echo '<ul style="margin: 0; padding-left: 20px; color: #047857;">';
            echo '<li>' . __('46.8% smaller total asset size', 'zskeleton') . '</li>';
            echo '<li>' . __('Faster page load times', 'zskeleton') . '</li>';
            echo '<li>' . __('Better caching performance', 'zskeleton') . '</li>';
            echo '<li>' . __('Improved user experience', 'zskeleton') . '</li>';
            echo '</ul>';
            echo '</div>';
        } else {
            echo '<div style="color: #dc2626; font-weight: 600; margin-bottom: 15px;">';
            echo '⚠️ ' . __('Minified assets are disabled', 'zskeleton');
            echo '</div>';
            
            echo '<div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 4px; padding: 15px; margin-bottom: 15px;">';
            echo '<h5 style="margin-top: 0; color: #991b1b;">' . __('Performance Impact', 'zskeleton') . '</h5>';
            echo '<p style="margin: 0; color: #b91c1c;">' . __('Original assets are being loaded, which may result in slower page load times. Enable minified assets for optimal performance.', 'zskeleton') . '</p>';
            echo '</div>';
        }

        if ( $combine_css ) {
            echo '<div style="color: #059669; font-weight: 600; margin-bottom: 10px;">';
            echo '✅ ' . esc_html__( 'Global theme CSS is combined into one cached file', 'zskeleton' );
            echo '</div>';
        } else {
            echo '<div style="color: #64748b; font-weight: 500; margin-bottom: 10px;">';
            echo esc_html__( 'Global theme CSS is loaded as separate files (enable “Combine global CSS” above to merge the core stack).', 'zskeleton' );
            echo '</div>';
        }
        
        echo '<div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 15px;">';
        echo '<h5 style="margin-top: 0; color: #1e40af;">' . __('Asset Statistics', 'zskeleton') . '</h5>';
        echo '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px;">';
        echo '<div>';
        echo '<strong>' . __('CSS Files:', 'zskeleton') . '</strong><br>';
        echo $use_minified ? '58.8KB (minified)' : '90.0KB (original)';
        echo '</div>';
        echo '<div>';
        echo '<strong>' . __('JavaScript Files:', 'zskeleton') . '</strong><br>';
        echo $use_minified ? '49.8KB (minified)' : '114.2KB (original)';
        echo '</div>';
        echo '</div>';
        echo '<div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #bfdbfe;">';
        echo '<strong>' . __('Total Size:', 'zskeleton') . '</strong> ';
        echo $use_minified ? '108.6KB (46.8% reduction)' : '204.3KB (original)';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        
        if (!empty($args['description'])) {
            printf('<p class="description">%s</p>', esc_html($args['description']));
        }
    }

    /**
     * reCAPTCHA status callback
     */
    public function recaptcha_status_callback() {
        $provider = get_option('zskeleton_captcha_provider', 'google_recaptcha');
        if (!in_array($provider, array('google_recaptcha', 'cloudflare_turnstile'), true)) {
            $provider = 'google_recaptcha';
        }

        $google_site = get_option('zskeleton_recaptcha_site_key', '');
        $google_secret = get_option('zskeleton_recaptcha_secret_key', '');
        $ts_site = get_option('zskeleton_turnstile_site_key', '');
        $ts_secret = get_option('zskeleton_turnstile_secret_key', '');
        $threshold = get_option('zskeleton_recaptcha_threshold', '0.5');

        $google_ready = !empty($google_site) && !empty($google_secret);
        $ts_ready = !empty($ts_site) && !empty($ts_secret);
        $active_ready = ('cloudflare_turnstile' === $provider) ? $ts_ready : $google_ready;

        echo '<div class="zskeleton-recaptcha-status" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin: 10px 0;">';

        echo '<h4 style="margin-top: 0; color: #1e3a8a;">' . __('Bot protection status', 'zskeleton') . '</h4>';

        echo '<p style="margin: 0 0 12px; font-size: 14px;"><strong>' . __('Selected provider:', 'zskeleton') . '</strong> ';
        echo esc_html('cloudflare_turnstile' === $provider ? __('Cloudflare Turnstile', 'zskeleton') : __('Google reCAPTCHA v3', 'zskeleton'));
        echo '</p>';

        if ($active_ready) {
            echo '<div style="color: #059669; font-weight: 600; margin-bottom: 15px;">';
            echo '✅ ' . __('Protection is enabled for your forms', 'zskeleton');
            echo '</div>';

            echo '<div style="background: #d1fae5; border: 1px solid #a7f3d0; border-radius: 4px; padding: 15px; margin-bottom: 15px;">';
            echo '<h5 style="margin-top: 0; color: #065f46;">' . __('Protected forms', 'zskeleton') . '</h5>';
            echo '<ul style="margin: 0; padding-left: 20px; color: #047857;">';
            echo '<li>' . __('User registration', 'zskeleton') . '</li>';
            echo '<li>' . __('Login', 'zskeleton') . '</li>';
            echo '<li>' . __('Contact form', 'zskeleton') . '</li>';
            echo '<li>' . __('Password reset', 'zskeleton') . '</li>';
            echo '</ul>';
            echo '</div>';

            if ('google_recaptcha' === $provider) {
                echo '<div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 15px;">';
                echo '<h5 style="margin-top: 0; color: #1e40af;">' . __('reCAPTCHA', 'zskeleton') . '</h5>';
                echo '<p style="margin: 0; font-size: 14px;"><strong>' . __('Score threshold:', 'zskeleton') . '</strong> ' . esc_html((string) $threshold) . '</p>';
                echo '</div>';
            }
        } else {
            echo '<div style="color: #d97706; font-weight: 600; margin-bottom: 15px;">';
            echo '⚠️ ' . __('Protection is not active for the selected provider — add both keys and save.', 'zskeleton');
            echo '</div>';

            echo '<div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 4px; padding: 15px; margin-bottom: 15px;">';
            echo '<h5 style="margin-top: 0; color: #92400e;">' . __('How to enable', 'zskeleton') . '</h5>';
            if ('cloudflare_turnstile' === $provider) {
                echo '<ol style="margin: 0; padding-left: 20px; color: #78350f;">';
                echo '<li>' . __('Open Cloudflare dashboard → Turnstile and create a widget', 'zskeleton') . '</li>';
                echo '<li>' . __('Add your site hostnames', 'zskeleton') . '</li>';
                echo '<li>' . __('Paste the site key and secret key above, then save', 'zskeleton') . '</li>';
                echo '</ol>';
            } else {
                echo '<ol style="margin: 0; padding-left: 20px; color: #78350f;">';
                echo '<li>' . __('Go to Google reCAPTCHA Admin and register a v3 site', 'zskeleton') . '</li>';
                echo '<li>' . __('Add your domain, then paste site and secret keys above', 'zskeleton') . '</li>';
                echo '</ol>';
            }
            echo '</div>';

            echo '<div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 15px;">';
            echo '<p style="margin: 0; font-size: 14px; color: #1e40af;"><strong>' . __('Note:', 'zskeleton') . '</strong> ' . __('Forms still work without keys, but are not protected against automated abuse.', 'zskeleton') . '</p>';
            echo '</div>';
        }

        echo '<p style="margin: 16px 0 0; font-size: 13px; color: #6b7280;">';
        echo '<strong>' . __('Key readiness', 'zskeleton') . ':</strong> ';
        echo esc_html(sprintf(
            /* translators: 1: Google ready yes/no, 2: Turnstile ready yes/no */
            __('Google: %1$s · Turnstile: %2$s', 'zskeleton'),
            $google_ready ? __('ready', 'zskeleton') : __('incomplete', 'zskeleton'),
            $ts_ready ? __('ready', 'zskeleton') : __('incomplete', 'zskeleton')
        ));
        echo '</p>';

        echo '</div>';
    }

    /**
     * MailerLite status callback
     */
    public function mailerlite_status_callback() {
        $is_enabled = get_option('zskeleton_enable_mailerlite', false);
        $is_plugin_active = function_exists('zskeleton_is_mailerlite_active') && zskeleton_is_mailerlite_active();
        
        echo '<div class="zskeleton-mailerlite-status" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin: 10px 0;">';
        
        echo '<h4 style="margin-top: 0; color: #1e3a8a;">' . __('MailerLite Integration Status', 'zskeleton') . '</h4>';
        
        if ($is_enabled && $is_plugin_active) {
            echo '<div style="color: #059669; font-weight: 600; margin-bottom: 15px;">';
            echo '✅ ' . __('MailerLite integration is ACTIVE', 'zskeleton');
            echo '</div>';
            
            echo '<div style="background: #d1fae5; border: 1px solid #a7f3d0; border-radius: 4px; padding: 15px; margin-bottom: 15px;">';
            echo '<h5 style="margin-top: 0; color: #065f46;">' . __('Integration Points', 'zskeleton') . '</h5>';
            echo '<ul style="margin: 0; padding-left: 20px; color: #047857;">';
            echo '<li>' . __('Individual Member Registration', 'zskeleton') . '</li>';
            echo '<li>' . __('Organizational Member Registration', 'zskeleton') . '</li>';
            echo '<li>' . __('Corporate Member Registration', 'zskeleton') . '</li>';
            echo '<li>' . __('Contact Form Newsletter Signup', 'zskeleton') . '</li>';
            echo '<li>' . __('Glossary Term Submission', 'zskeleton') . '</li>';
            echo '<li>' . __('Submission Form', 'zskeleton') . '</li>';
            echo '</ul>';
            echo '</div>';
            
        } elseif (!$is_enabled) {
            echo '<div style="color: #d97706; font-weight: 600; margin-bottom: 15px;">';
            echo '⚠️ ' . __('MailerLite integration is DISABLED', 'zskeleton');
            echo '</div>';
            
            echo '<div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 4px; padding: 15px;">';
            echo '<p style="margin: 0; color: #78350f;">' . __('Enable the checkbox above to activate MailerLite integration for newsletter subscriptions.', 'zskeleton') . '</p>';
            echo '</div>';
            
        } elseif (!$is_plugin_active) {
            echo '<div style="color: #dc2626; font-weight: 600; margin-bottom: 15px;">';
            echo '❌ ' . __('MailerLite plugin not configured', 'zskeleton');
            echo '</div>';
            
            echo '<div style="background: #fee2e2; border: 1px solid #fecaca; border-radius: 4px; padding: 15px; margin-bottom: 15px;">';
            echo '<h5 style="margin-top: 0; color: #991b1b;">' . __('Required Steps', 'zskeleton') . '</h5>';
            echo '<ol style="margin: 0; padding-left: 20px; color: #7f1d1d;">';
            
            if (!class_exists('MailerLiteForms\Api\PlatformAPI')) {
                echo '<li><strong>' . __('Install and activate the MailerLite plugin', 'zskeleton') . '</strong></li>';
                echo '<li>' . __('Go to WordPress Admin → Plugins → Add New', 'zskeleton') . '</li>';
                echo '<li>' . __('Search for "MailerLite - Signup forms"', 'zskeleton') . '</li>';
                echo '<li>' . __('Install and activate the official MailerLite plugin', 'zskeleton') . '</li>';
            } else {
                echo '<li><strong>' . __('Configure MailerLite API key', 'zskeleton') . '</strong></li>';
                echo '<li>' . __('Go to WordPress Admin → MailerLite → Settings', 'zskeleton') . '</li>';
                echo '<li>' . __('Enter your MailerLite API key', 'zskeleton') . '</li>';
            }
            
            echo '</ol>';
            echo '</div>';
        }
        
        echo '<div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 15px;">';
        echo '<p style="margin: 0; font-size: 14px; color: #1e40af;"><strong>' . __('Note:', 'zskeleton') . '</strong> ' . __('Newsletter subscriptions will work without MailerLite, storing email addresses in WordPress. Configure MailerLite for professional email marketing features.', 'zskeleton') . '</p>';
        echo '</div>';
        
        echo '</div>';
    }

    /**
     * MailerLite groups list callback
     */
    public function mailerlite_groups_list_callback() {
        if (!function_exists('zskeleton_is_mailerlite_active') || !zskeleton_is_mailerlite_active()) {
            echo '<div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 4px; padding: 15px; margin: 10px 0;">';
            echo '<p style="margin: 0; color: #78350f;">' . __('MailerLite is not configured. Please configure the plugin first to see available groups.', 'zskeleton') . '</p>';
            echo '</div>';
            return;
        }
        
        if (!function_exists('zskeleton_mailerlite_get_groups')) {
            echo '<div style="background: #fee2e2; border: 1px solid #fecaca; border-radius: 4px; padding: 15px; margin: 10px 0;">';
            echo '<p style="margin: 0; color: #7f1d1d;">' . __('MailerLite helper functions not available. Please check your theme installation.', 'zskeleton') . '</p>';
            echo '</div>';
            return;
        }
        
        $groups = zskeleton_mailerlite_get_groups(100);
        
        if ($groups === false || empty($groups)) {
            echo '<div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 4px; padding: 15px; margin: 10px 0;">';
            echo '<p style="margin: 0; color: #78350f;">' . __('No groups found or unable to retrieve groups. Check your MailerLite API key configuration.', 'zskeleton') . '</p>';
            echo '</div>';
            return;
        }
        
        echo '<div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin: 10px 0;">';
        echo '<h4 style="margin-top: 0; color: #1e3a8a;">' . __('Your MailerLite Groups', 'zskeleton') . '</h4>';
        echo '<p style="color: #6b7280; font-size: 14px; margin-bottom: 15px;">' . __('Copy the Group ID and paste it in the appropriate field above.', 'zskeleton') . '</p>';
        
        echo '<table class="widefat" style="margin-top: 10px;">';
        echo '<thead>';
        echo '<tr>';
        echo '<th style="padding: 12px;">' . __('Group Name', 'zskeleton') . '</th>';
        echo '<th style="padding: 12px;">' . __('Group ID', 'zskeleton') . '</th>';
        echo '<th style="padding: 12px;">' . __('Subscribers', 'zskeleton') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($groups as $group) {
            echo '<tr>';
            echo '<td style="padding: 12px;"><strong>' . esc_html($group->name) . '</strong></td>';
            echo '<td style="padding: 12px;"><code style="background: #f3f4f6; padding: 4px 8px; border-radius: 4px; font-size: 13px;">' . esc_html($group->id) . '</code></td>';
            echo '<td style="padding: 12px;">' . (isset($group->total) ? number_format($group->total) : '0') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    /**
     * Sanitize logo setting
     */
    public function sanitize_logo_setting($value) {
        // Allow empty values
        if (empty($value)) {
            return '';
        }
        
        // If it's a numeric value (attachment ID), validate it
        if (is_numeric($value)) {
            $attachment = get_post($value);
            if ($attachment && $attachment->post_type === 'attachment') {
                return intval($value);
            }
        }
        
        // If it's a URL, sanitize it
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return esc_url_raw($value);
        }
        
        // If nothing matches, return empty
        return '';
    }

    /**
     * Sanitize and validate contact email.
     *
     * If invalid, we keep the existing stored value and show a settings error.
     *
     * @param mixed $value Raw value.
     * @return string Sanitized email (or existing value if invalid).
     */
    public function sanitize_contact_email_setting( $value ) {
        return $this->sanitize_theme_email_option( $value, 'zskeleton_contact_email', 'invalid_contact_email', __( 'Please enter a valid Contact Email.', 'zskeleton' ) );
    }

    /**
     * Sanitize membership email option.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_membership_email_setting( $value ) {
        return $this->sanitize_theme_email_option( $value, 'zskeleton_membership_email', 'invalid_membership_email', __( 'Please enter a valid Membership email.', 'zskeleton' ) );
    }

    /**
     * Sanitize media & press email option.
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_media_email_setting( $value ) {
        return $this->sanitize_theme_email_option( $value, 'zskeleton_media_email', 'invalid_media_email', __( 'Please enter a valid Media & press email.', 'zskeleton' ) );
    }

    /**
     * Sanitize a theme email wp_option; keep the previous value when invalid.
     *
     * @param mixed  $value        Raw value.
     * @param string $option_name  Option key.
     * @param string $error_code   Settings error code.
     * @param string $error_message User-facing error message.
     * @return string
     */
    private function sanitize_theme_email_option( $value, $option_name, $error_code, $error_message ) {
        $raw = sanitize_text_field( (string) $value );

        if ( '' === $raw ) {
            return '';
        }

        if ( ! filter_var( $raw, FILTER_VALIDATE_EMAIL ) ) {
            add_settings_error(
                self::OPTION_GROUP,
                $error_code,
                esc_html( $error_message ),
                'error'
            );

            return (string) get_option( $option_name, '' );
        }

        return $raw;
    }

    /**
     * Sanitize phone lines (one line; keeps digits, punctuation, and non-HTML text).
     *
     * @param mixed $value Raw value.
     * @return string
     */
    public function sanitize_phone_setting( $value ) {
        return sanitize_text_field( (string) $value );
    }

    /**
     * Sanitize Google font family query values.
     *
     * @param string $value Raw option value.
     * @return string
     */
    public function sanitize_font_family_setting($value) {
        $value = sanitize_text_field((string) $value);
        // Keep only expected Google Fonts query characters.
        $value = preg_replace('/[^a-zA-Z0-9\s:\@\;\,\+\-\_\|&=]/', '', $value);
        return trim($value);
    }

    /**
     * Sanitize Arabic locale font: must match curated list or legacy custom pattern.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public function sanitize_google_font_arabic($value) {
        return $this->sanitize_google_font_choice(
            $value,
            function_exists('zskeleton_get_google_font_choices_arabic') ? zskeleton_get_google_font_choices_arabic() : array(),
            'Cairo:wght@400;500;600;700'
        );
    }

    /**
     * Sanitize default (non-Arabic) font: must match curated list or legacy custom pattern.
     *
     * @param mixed $value Raw option value.
     * @return string
     */
    public function sanitize_google_font_default($value) {
        return $this->sanitize_google_font_choice(
            $value,
            function_exists('zskeleton_get_google_font_choices_latin') ? zskeleton_get_google_font_choices_latin() : array(),
            'Inter:wght@400;500;600;700'
        );
    }

    /**
     * @param mixed  $value        Raw value.
     * @param array  $allowed_map  Map of stored value => label.
     * @param string $default      Fallback when invalid.
     * @return string
     */
    private function sanitize_google_font_choice($value, array $allowed_map, $default) {
        $value = $this->sanitize_font_family_setting($value);
        if ($value !== '' && array_key_exists($value, $allowed_map)) {
            return $value;
        }
        // Preserve legacy custom entries that still pass the font query character set.
        if ($value !== '' && preg_match('/^[a-zA-Z0-9\+:\@;\,\-\_]+$/', $value)) {
            return $value;
        }
        return $default;
    }

    /**
     * AJAX Handlers
     */

    /**
     * Handle bulk actions
     */
    public function handle_bulk_action() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'zskeleton_admin_nonce')) {
            wp_send_json_error(__('Security check failed.', 'zskeleton'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'zskeleton'));
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $items = array_map('sanitize_text_field', $_POST['items']);

        // Placeholder for bulk actions
        wp_send_json_success(sprintf(__('Bulk action "%s" applied to %d items.', 'zskeleton'), $action, count($items)));
    }

    /**
     * Export settings
     */
    public function export_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'zskeleton_admin_nonce')) {
            wp_send_json_error(__('Security check failed.', 'zskeleton'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'zskeleton'));
        }

        $settings = array();
        foreach (self::collect_theme_settings_option_names(array()) as $key) {
            $settings[ $key ] = get_option($key);
        }

        wp_send_json_success($settings);
    }

    /**
     * Import settings
     */
    public function import_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'zskeleton_admin_nonce')) {
            wp_send_json_error(__('Security check failed.', 'zskeleton'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'zskeleton'));
        }

        $settings = json_decode(stripslashes($_POST['settings']), true);

        if (!$settings) {
            wp_send_json_error(__('Invalid settings data.', 'zskeleton'));
        }

        $allowed = array_flip(self::collect_theme_settings_option_names(array()));

        foreach ($settings as $key => $value) {
            if (! isset($allowed[ $key ])) {
                continue;
            }
            // update_option() runs sanitize_option_* from register_setting().
            update_option($key, wp_unslash($value));
        }

        wp_send_json_success(__('Settings imported successfully.', 'zskeleton'));
    }

    /**
     * Reset settings to defaults
     */
    public function reset_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'zskeleton_admin_nonce')) {
            wp_send_json_error(__('Security check failed.', 'zskeleton'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'zskeleton'));
        }

        // Reset to default values
        $blog_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        $defaults = array(
            'zskeleton_site_logo' => '',
            'zskeleton_mobile_logo' => '',
            'zskeleton_site_logo_ltr' => '',
            'zskeleton_mobile_logo_ltr' => '',
            'zskeleton_site_name' => 'ZSkeleton',
            'zskeleton_site_tagline' => 'A flexible WordPress base theme for membership-driven websites.',
            'zskeleton_primary_color' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['primary'] : '#647FBC',
            'zskeleton_secondary_color' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['secondary'] : '#91ADC8',
            'zskeleton_accent_color' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['accent'] : '#AED6CF',
            'zskeleton_background_color' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['background'] : '#FAFDD6',
            'zskeleton_button_background_color' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['button_background'] : '#647FBC',
            'zskeleton_button_text_color' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['button_text'] : '#000000',
            'zskeleton_counter_text_color' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['counter_text'] : '#647FBC',
            'zskeleton_nav_item_hover_bg'  => '',
            'zskeleton_nav_item_active_bg'  => '',
            'zskeleton_mobile_menu_button_style' => 'style1',
            'zskeleton_mobile_menu_button_bar_color' => '#ffffff',
            'zskeleton_mobile_menu_button_background_color' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['primary'] : '#647FBC',
            'zskeleton_mobile_menu_button_border_width' => '2',
            'zskeleton_mobile_menu_button_border_color' => function_exists( 'zskeleton_get_theme_color_defaults' ) ? zskeleton_get_theme_color_defaults()['primary'] : '#647FBC',
            'zskeleton_mobile_menu_close_background' => '',
            'zskeleton_mobile_menu_close_text_color' => '#374151',
            'zskeleton_mobile_menu_close_border_radius' => '50%',
            'zskeleton_mobile_menu_panel_style' => 'style1',
            'zskeleton_mobile_menu_drawer_width' => 'default',
            'zskeleton_mobile_bottom_nav_style' => 'style1',
            'zskeleton_google_font_arabic' => 'Cairo:wght@400;500;600;700',
            'zskeleton_google_font_default' => 'Inter:wght@400;500;600;700',
            'zskeleton_contact_email' => 'info@zskeleton.org',
            'zskeleton_membership_email' => 'membership@zskeleton.org',
            'zskeleton_media_email' => 'media@zskeleton.org',
            'zskeleton_contact_phone' => '',
            'zskeleton_contact_phone_secondary' => '',
            'zskeleton_footer_widget_areas_count' => '4',
            'zskeleton_header_layout' => 'default',
            'zskeleton_split_logo_height' => '56',
            'zskeleton_split_logo_side_padding' => '72',
            'zskeleton_back_to_top_enabled' => '1',
            'zskeleton_combine_theme_css' => '0',
            'zskeleton_combine_theme_css_extra_list' => '',
            'zskeleton_whatsapp_float_enabled' => '0',
            'zskeleton_whatsapp_float_url' => '',
            'zskeleton_map_latitude' => '',
            'zskeleton_map_longitude' => '',
            'zskeleton_map_address' => '',
            'zskeleton_map_zoom' => '14',
            'zskeleton_theme_contact_page_id' => 0,
            'zskeleton_theme_blog_listing_page_id' => 0,
            'zskeleton_sidebar_browse_show_about' => '1',
            'zskeleton_sidebar_browse_show_faqs' => '1',
            'zskeleton_sidebar_browse_show_memberships' => '1',
            'zskeleton_sidebar_browse_show_contact' => '1',
            'zskeleton_hero_title' => 'ZSkeleton',
            'zskeleton_hero_subtitle' => 'Launch your next WordPress project faster with reusable templates and core features.',
            'zskeleton_newsletter_title' => 'Stay Updated with ZSkeleton',
            'zskeleton_footer_widget_area_1_heading' => sprintf(__('About %s', 'zskeleton'), $blog_name),
            'zskeleton_footer_widget_area_1_description' => sprintf(
                __('%s is a reusable WordPress base theme built for modern membership and content websites.', 'zskeleton'),
                $blog_name
            ),
        );

        if ( function_exists( 'zskeleton_get_layout_bars_default_option_values' ) ) {
            $defaults = array_merge( $defaults, zskeleton_get_layout_bars_default_option_values() );
        }

        $registered = get_registered_settings();
        foreach (self::collect_theme_settings_option_names(array()) as $key) {
            if (array_key_exists($key, $defaults)) {
                update_option($key, $defaults[ $key ]);
                continue;
            }
            if (is_array($registered) && isset($registered[ $key ]) && is_array($registered[ $key ]) && array_key_exists('default', $registered[ $key ])) {
                update_option($key, $registered[ $key ]['default']);
                continue;
            }
            delete_option($key);
        }

        wp_send_json_success(__('Settings reset to defaults.', 'zskeleton'));
    }

    /**
     * Output button to map auth page settings from common theme page slugs.
     */
    public function auth_pages_set_defaults_field_callback() {
        ?>
        <p>
            <button type="submit" class="button button-primary" id="zskeleton-install-common-pages" form="zskeleton-common-pages-form"><?php esc_html_e('Create & sync common pages', 'zskeleton'); ?></button>
        </p>
        <p class="description"><?php esc_html_e('Creates or updates published pages (login, register, forgot-password, reset-password, memberships, blog) with the correct templates, maps auth fields below, and assigns the WordPress “Posts page” when you use a static front page without a posts page. The ZSkeleton Membership plugin syncs the membership landing page option when it is still unset.', 'zskeleton'); ?></p>
        <p>
            <button type="submit" class="button button-secondary" id="zskeleton-set-default-auth-pages" form="zskeleton-auth-pages-form"><?php esc_html_e('Set default theme auth pages (slugs only)', 'zskeleton'); ?></button>
        </p>
        <p class="description"><?php esc_html_e('Only updates auth dropdowns from existing published pages (slugs: login, register, forgot-password or lost-password, reset-password). Use this when pages already exist and you do not want to change templates or titles.', 'zskeleton'); ?></p>
        <?php
    }

    /**
     * Create common pages and sync theme options.
     *
     * @return void
     */
    public function handle_install_common_pages() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to perform this action.', 'zskeleton' ) );
        }
        if ( ! current_user_can( 'publish_pages' ) ) {
            wp_die( esc_html__( 'You need permission to publish pages to run the installer.', 'zskeleton' ) );
        }
        check_admin_referer( 'zskeleton_install_common_pages' );

        $result = function_exists( 'zskeleton_install_common_pages' ) ? zskeleton_install_common_pages( array( 'context' => 'admin_post' ) ) : array();

        if ( is_array( $result ) ) {
            set_transient( 'zskeleton_common_pages_install_notice_' . get_current_user_id(), $result, 120 );
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'                         => 'zskeleton-theme-settings',
                    'zskeleton_common_pages_notice' => '1',
                ),
                admin_url( 'themes.php' )
            )
        );
        exit;
    }

    /**
     * Apply default auth page IDs from theme slugs.
     */
    public function handle_set_default_auth_pages() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to perform this action.', 'zskeleton' ) );
        }
        check_admin_referer( 'zskeleton_set_default_auth_pages' );

        if ( function_exists( 'zskeleton_sync_auth_page_options_from_theme_slugs' ) ) {
            zskeleton_sync_auth_page_options_from_theme_slugs();
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'                   => 'zskeleton-theme-settings',
                    'zskeleton_auth_defaults' => '1',
                ),
                admin_url( 'themes.php' )
            )
        );
        exit;
    }

    /**
     * Notice after running the common pages installer.
     *
     * @return void
     */
    public function render_common_pages_install_notice() {
        if ( ! isset( $_GET['zskeleton_common_pages_notice'] ) || '1' !== $_GET['zskeleton_common_pages_notice'] ) {
            return;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $uid = get_current_user_id();
        $data = get_transient( 'zskeleton_common_pages_install_notice_' . $uid );
        delete_transient( 'zskeleton_common_pages_install_notice_' . $uid );
        if ( ! is_array( $data ) ) {
            return;
        }
        if ( isset( $data['skipped'] ) && true === $data['skipped'] ) {
            ?>
            <div class="notice notice-info is-dismissible">
                <p><?php esc_html_e( 'Common pages installer was skipped by a filter.', 'zskeleton' ); ?></p>
            </div>
            <?php
            return;
        }
        if ( isset( $data['error'] ) && 'capability' === $data['error'] ) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php esc_html_e( 'You do not have permission to publish pages.', 'zskeleton' ); ?></p>
            </div>
            <?php
            return;
        }
        $created = isset( $data['created'] ) && is_array( $data['created'] ) ? $data['created'] : array();
        $updated = isset( $data['updated'] ) && is_array( $data['updated'] ) ? $data['updated'] : array();
        $skipped = isset( $data['skipped'] ) && is_array( $data['skipped'] ) ? $data['skipped'] : array();
        $errors  = isset( $data['errors'] ) && is_array( $data['errors'] ) ? $data['errors'] : array();
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong><?php esc_html_e( 'Common pages installer finished.', 'zskeleton' ); ?></strong></p>
            <?php if ( ! empty( $created ) ) : ?>
                <p><?php echo esc_html( sprintf( __( 'Created: %s', 'zskeleton' ), implode( ', ', array_map( 'sanitize_title', $created ) ) ) ); ?></p>
            <?php endif; ?>
            <?php if ( ! empty( $updated ) ) : ?>
                <p><?php echo esc_html( sprintf( __( 'Updated templates or status: %s', 'zskeleton' ), implode( ', ', array_map( 'sanitize_title', $updated ) ) ) ); ?></p>
            <?php endif; ?>
            <?php if ( ! empty( $skipped ) ) : ?>
                <p><?php echo esc_html( sprintf( __( 'Skipped (template missing): %s', 'zskeleton' ), implode( ', ', array_map( 'sanitize_title', $skipped ) ) ) ); ?></p>
            <?php endif; ?>
            <?php if ( ! empty( $errors ) ) : ?>
                <p><?php echo esc_html( sprintf( __( 'Could not create: %s', 'zskeleton' ), implode( ', ', array_map( 'sanitize_title', $errors ) ) ) ); ?></p>
            <?php endif; ?>
            <p class="description"><?php esc_html_e( 'Review the Content tab page dropdowns and save settings if you changed anything else.', 'zskeleton' ); ?></p>
        </div>
        <?php
    }

    /**
     * Notice after applying default auth pages.
     */
    public function render_auth_pages_default_notice() {
        if ( ! isset( $_GET['zskeleton_auth_defaults'] ) || '1' !== $_GET['zskeleton_auth_defaults'] ) {
            return;
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( 'Auth page settings were updated from theme slugs. Review the dropdowns under Content and save if needed.', 'zskeleton' ); ?></p>
        </div>
        <?php
    }

    /**
     * Handle update cache clearing.
     */
    public function handle_clear_update_cache() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have permission to perform this action.', 'zskeleton' ) );
        }

        check_admin_referer( 'zskeleton_clear_update_cache_nonce', 'zskeleton_clear_update_cache_nonce' );

        delete_site_transient( 'update_themes' );

        if ( isset( $GLOBALS['zskeleton_github_update_checker'] ) && is_object( $GLOBALS['zskeleton_github_update_checker'] ) && method_exists( $GLOBALS['zskeleton_github_update_checker'], 'resetUpdateState' ) ) {
            $GLOBALS['zskeleton_github_update_checker']->resetUpdateState();
        }

        wp_safe_redirect(
            add_query_arg(
                array(
                    'page'                => 'zskeleton-theme-settings',
                    'zskeleton_cache_cleared' => '1',
                ),
                admin_url( 'themes.php' )
            )
        );
        exit;
    }

    /**
     * Render notice after clearing update cache.
     */
    public function render_clear_cache_notice() {
        if ( ! isset( $_GET['zskeleton_cache_cleared'] ) || '1' !== $_GET['zskeleton_cache_cleared'] ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( 'Update cache cleared. Please run "Check now" again.', 'zskeleton' ); ?></p>
        </div>
        <?php
    }

    /**
     * Get statistics
     */
    public function get_statistics() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'zskeleton_admin_nonce')) {
            wp_send_json_error(__('Security check failed.', 'zskeleton'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions.', 'zskeleton'));
        }

        // Get basic statistics
        $user_counts = count_users();
        $post_counts = wp_count_posts();
        $page_counts = wp_count_posts('page');
        
        // Get membership statistics
        $total_members = 0;
        $individual_members = 0;
        $organizational_members = 0;
        
        // Count users with membership
        $users = get_users(array('meta_key' => 'zskeleton_membership_type'));
        foreach ($users as $user) {
            $membership_type = get_user_meta($user->ID, 'zskeleton_membership_type', true);
            if ($membership_type) {
                $total_members++;
                if ($membership_type === 'individual') {
                    $individual_members++;
                } elseif ($membership_type === 'organizational') {
                    $organizational_members++;
                }
            }
        }
        
        $stats = array(
            'total_users' => $user_counts['total_users'],
            'total_posts' => $post_counts->publish,
            'total_pages' => $page_counts->publish,
            'total_members' => $total_members,
            'individual_members' => $individual_members,
            'organizational_members' => $organizational_members,
            'revenue' => 0, // Placeholder since we don't track actual revenue
            'active_theme' => get_option('stylesheet'),
            'wordpress_version' => get_bloginfo('version'),
            'chartData' => array(
                'membership' => array(
                    'individual' => $individual_members,
                    'organizational' => $organizational_members
                ),
                'revenue' => array() // Placeholder for revenue chart data
            )
        );

        wp_send_json_success($stats);
    }
}

/**
 * Settings API group slug for Appearance → ZSkeleton Settings.
 *
 * Use as the first argument to register_setting() for options on that screen
 * (same value as {@see ZSkeleton_Theme_Settings::OPTION_GROUP}).
 *
 * @return string
 */
function zskeleton_theme_settings_option_group() {
    return ZSkeleton_Theme_Settings::OPTION_GROUP;
}

// Theme settings initialized in functions.php
