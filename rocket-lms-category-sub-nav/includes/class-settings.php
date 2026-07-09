<?php
/**
 * Plugin settings (Settings → LMS Category Sub-Nav).
 *
 * @package Rocket_LMS_Category_Subnav
 */

if (!defined('ABSPATH')) {
    exit;
}

class RLMS_Cat_Subnav_Settings {

    const OPTION_GROUP = 'rlms_cat_subnav_settings';
    const OPTION_ENABLED = 'rlms_cat_subnav_enabled';
    const OPTION_LMS_URL = 'rlms_cat_subnav_lms_url';
    const OPTION_CACHE_TTL = 'rlms_cat_subnav_cache_ttl';

    /**
     * Register hooks.
     */
    public static function init() {
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_menu', array(__CLASS__, 'register_menu'));
    }

    /**
     * Add settings page under Settings.
     */
    public static function register_menu() {
        add_options_page(
            __('LMS Category Sub-Nav', 'rocket-lms-category-subnav'),
            __('LMS Category Sub-Nav', 'rocket-lms-category-subnav'),
            'manage_options',
            'rlms-category-subnav',
            array(__CLASS__, 'render_page')
        );
    }

    /**
     * Register options and fields.
     */
    public static function register_settings() {
        register_setting(self::OPTION_GROUP, self::OPTION_ENABLED, array(
            'type' => 'boolean',
            'sanitize_callback' => function ($value) {
                return !empty($value) ? 1 : 0;
            },
            'default' => 0,
        ));

        register_setting(self::OPTION_GROUP, self::OPTION_LMS_URL, array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => '',
        ));

        register_setting(self::OPTION_GROUP, self::OPTION_CACHE_TTL, array(
            'type' => 'integer',
            'sanitize_callback' => function ($value) {
                $value = absint($value);
                return $value > 0 ? $value : 300;
            },
            'default' => 300,
        ));

        add_settings_section(
            'rlms_cat_subnav_main',
            __('Configuration', 'rocket-lms-category-subnav'),
            array(__CLASS__, 'section_description'),
            'rlms-category-subnav'
        );

        add_settings_field(
            self::OPTION_ENABLED,
            __('Enable sub-nav', 'rocket-lms-category-subnav'),
            array(__CLASS__, 'field_enabled'),
            'rlms-category-subnav',
            'rlms_cat_subnav_main'
        );

        add_settings_field(
            self::OPTION_LMS_URL,
            __('LMS site URL', 'rocket-lms-category-subnav'),
            array(__CLASS__, 'field_lms_url'),
            'rlms-category-subnav',
            'rlms_cat_subnav_main'
        );

        add_settings_field(
            self::OPTION_CACHE_TTL,
            __('Cache duration (seconds)', 'rocket-lms-category-subnav'),
            array(__CLASS__, 'field_cache_ttl'),
            'rlms-category-subnav',
            'rlms_cat_subnav_main'
        );
    }

    /**
     * Section intro.
     */
    public static function section_description() {
        echo '<p>' . esc_html__(
            'Fetch top-level course categories from Rocket LMS and display them in a horizontal carousel below the header search area.',
            'rocket-lms-category-subnav'
        ) . '</p>';
        echo '<p><code>GET {site}/course-categories/nav?locale=ar</code></p>';
        echo '<p class="description">' . esc_html__(
            'Default action hook: zskeleton_after_header_search (ZSkeleton theme). Override with the rlms_cat_subnav_action_hook filter.',
            'rocket-lms-category-subnav'
        ) . '</p>';
    }

    /**
     * Enabled checkbox.
     */
    public static function field_enabled() {
        $enabled = (int) get_option(self::OPTION_ENABLED, 0);
        ?>
        <label>
            <input type="checkbox" name="<?php echo esc_attr(self::OPTION_ENABLED); ?>" value="1" <?php checked(1, $enabled); ?> />
            <?php esc_html_e('Show course categories sub-nav on the front end', 'rocket-lms-category-subnav'); ?>
        </label>
        <?php
    }

    /**
     * LMS URL field.
     */
    public static function field_lms_url() {
        $value = get_option(self::OPTION_LMS_URL, '');
        ?>
        <input type="url" class="regular-text" name="<?php echo esc_attr(self::OPTION_LMS_URL); ?>"
               value="<?php echo esc_attr($value); ?>" placeholder="https://albyan.institute" />
        <p class="description">
            <?php esc_html_e('Rocket LMS site root URL (recommended) or legacy API base (…/api/development). No trailing slash.', 'rocket-lms-category-subnav'); ?>
        </p>
        <?php
    }

    /**
     * Cache TTL field.
     */
    public static function field_cache_ttl() {
        $value = (int) get_option(self::OPTION_CACHE_TTL, 300);
        ?>
        <input type="number" min="60" step="60" name="<?php echo esc_attr(self::OPTION_CACHE_TTL); ?>"
               value="<?php echo esc_attr($value); ?>" />
        <?php
    }

    /**
     * Settings page markup.
     */
    public static function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields(self::OPTION_GROUP);
                do_settings_sections('rlms-category-subnav');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Whether the sub-nav is enabled and configured.
     *
     * @return bool
     */
    public static function is_enabled() {
        if (!(int) get_option(self::OPTION_ENABLED, 0)) {
            return false;
        }

        return '' !== self::lms_base_url();
    }

    /**
     * LMS base URL (filterable).
     *
     * @return string
     */
    public static function lms_base_url() {
        $url = rtrim((string) get_option(self::OPTION_LMS_URL, ''), '/');
        return rtrim((string) apply_filters('rlms_cat_subnav_lms_url', $url), '/');
    }

    /**
     * Cache TTL in seconds.
     *
     * @return int
     */
    public static function cache_ttl() {
        return max(60, (int) get_option(self::OPTION_CACHE_TTL, 300));
    }
}
