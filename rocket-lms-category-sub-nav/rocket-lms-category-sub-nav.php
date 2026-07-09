<?php
/**
 * Plugin Name: Rocket LMS Category Sub-Nav
 * Plugin URI: https://github.com/MakiOmar/Albyan
 * Description: Fetches top-level course categories from Rocket LMS and renders a horizontal sub-navigation carousel. Hooks into zskeleton_after_header_search by default.
 * Version: 1.0.0
 * Author: Al Bayan Institute
 * Text Domain: rocket-lms-category-subnav
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package Rocket_LMS_Category_Subnav
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RLMS_CAT_SUBNAV_VERSION', '1.0.2');
define('RLMS_CAT_SUBNAV_FILE', __FILE__);
define('RLMS_CAT_SUBNAV_PATH', plugin_dir_path(__FILE__));
define('RLMS_CAT_SUBNAV_URL', plugin_dir_url(__FILE__));

require_once RLMS_CAT_SUBNAV_PATH . 'includes/class-settings.php';
require_once RLMS_CAT_SUBNAV_PATH . 'includes/class-renderer.php';

/**
 * Bootstrap plugin.
 */
function rlms_cat_subnav_bootstrap() {
    RLMS_Cat_Subnav_Settings::init();
    RLMS_Cat_Subnav_Renderer::init();
}
add_action('plugins_loaded', 'rlms_cat_subnav_bootstrap');

/**
 * Migrate legacy zskeleton theme options on activation.
 */
function rlms_cat_subnav_activate() {
    $map = array(
        'rlms_cat_subnav_enabled' => 'zskeleton_lms_category_subnav_enabled',
        'rlms_cat_subnav_lms_url' => 'zskeleton_lms_api_base_url',
        'rlms_cat_subnav_cache_ttl' => 'zskeleton_lms_category_subnav_cache_ttl',
    );

    foreach ($map as $new_key => $legacy_key) {
        $current = get_option($new_key, null);
        if (null !== $current && '' !== $current && 0 !== $current) {
            continue;
        }

        $legacy = get_option($legacy_key, null);
        if (null !== $legacy && '' !== $legacy) {
            update_option($new_key, $legacy);
        }
    }
}
register_activation_hook(__FILE__, 'rlms_cat_subnav_activate');
