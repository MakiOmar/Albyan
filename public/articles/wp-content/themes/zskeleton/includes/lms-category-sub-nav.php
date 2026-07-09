<?php
/**
 * LMS course category sub-navigation (fetched from Rocket LMS API).
 *
 * Renders the same horizontal category carousel used on the Laravel site.
 * Hooked to zskeleton_after_header_search.
 *
 * @package ZSkeleton_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register theme settings for LMS category sub-nav.
 */
function zskeleton_lms_category_subnav_register_settings() {
    if (!class_exists('ZSkeleton_Theme_Settings')) {
        return;
    }

    $group = ZSkeleton_Theme_Settings::OPTION_GROUP;

    register_setting($group, 'zskeleton_lms_category_subnav_enabled', array(
        'type' => 'boolean',
        'sanitize_callback' => function ($value) {
            return !empty($value) ? 1 : 0;
        },
        'default' => 0,
    ));

    register_setting($group, 'zskeleton_lms_api_base_url', array(
        'type' => 'string',
        'sanitize_callback' => 'esc_url_raw',
        'default' => '',
    ));

    register_setting($group, 'zskeleton_lms_category_subnav_cache_ttl', array(
        'type' => 'integer',
        'sanitize_callback' => function ($value) {
            $value = absint($value);
            return $value > 0 ? $value : 300;
        },
        'default' => 300,
    ));

    add_settings_section(
        'zskeleton_lms_category_subnav',
        __('LMS Course Categories Sub-Nav', 'zskeleton'),
        'zskeleton_lms_category_subnav_settings_section_cb',
        'zskeleton-layout-settings'
    );

    add_settings_field(
        'zskeleton_lms_category_subnav_enabled',
        __('Enable LMS category sub-nav', 'zskeleton'),
        'zskeleton_lms_category_subnav_enabled_field_cb',
        'zskeleton-layout-settings',
        'zskeleton_lms_category_subnav'
    );

    add_settings_field(
        'zskeleton_lms_api_base_url',
        __('LMS API base URL', 'zskeleton'),
        'zskeleton_lms_api_base_url_field_cb',
        'zskeleton-layout-settings',
        'zskeleton_lms_category_subnav'
    );

    add_settings_field(
        'zskeleton_lms_category_subnav_cache_ttl',
        __('Cache duration (seconds)', 'zskeleton'),
        'zskeleton_lms_category_subnav_cache_ttl_field_cb',
        'zskeleton-layout-settings',
        'zskeleton_lms_category_subnav'
    );
}
add_action('admin_init', 'zskeleton_lms_category_subnav_register_settings', 20);

/**
 * Persist options when saving ZSkeleton theme settings.
 *
 * @param array $names Option names.
 * @return array
 */
function zskeleton_lms_category_subnav_option_names($names) {
    $names[] = 'zskeleton_lms_category_subnav_enabled';
    $names[] = 'zskeleton_lms_api_base_url';
    $names[] = 'zskeleton_lms_category_subnav_cache_ttl';
    return $names;
}
add_filter('zskeleton_theme_settings_option_names', 'zskeleton_lms_category_subnav_option_names');

/**
 * Settings section description.
 */
function zskeleton_lms_category_subnav_settings_section_cb() {
    echo '<p>' . esc_html__(
        'Fetch top-level course categories from your Rocket LMS installation and show them in a horizontal sub-navigation bar below the header search.',
        'zskeleton'
    ) . '</p>';
    echo '<p><code>' . esc_html__('GET {site}/course-categories/nav?locale=ar', 'zskeleton') . '</code></p>';
    echo '<p class="description">' . esc_html__('Recommended LMS site URL: https://albyan.institute (no /api/development). Legacy API base URLs are still supported.', 'zskeleton') . '</p>';
}

/**
 * Enable checkbox field.
 */
function zskeleton_lms_category_subnav_enabled_field_cb() {
    $enabled = (int) get_option('zskeleton_lms_category_subnav_enabled', 0);
    ?>
    <label>
        <input type="checkbox" name="zskeleton_lms_category_subnav_enabled" value="1" <?php checked(1, $enabled); ?> />
        <?php esc_html_e('Show course categories sub-nav after header search', 'zskeleton'); ?>
    </label>
    <?php
}

/**
 * API base URL field.
 */
function zskeleton_lms_api_base_url_field_cb() {
    $value = get_option('zskeleton_lms_api_base_url', '');
    ?>
    <input type="url" class="regular-text" name="zskeleton_lms_api_base_url" id="zskeleton_lms_api_base_url"
           value="<?php echo esc_attr($value); ?>"
           placeholder="https://albyan.institute" />
    <p class="description">
        <?php esc_html_e('LMS site URL (recommended) or API base URL. Examples: https://albyan.institute or https://albyan.institute/api/development', 'zskeleton'); ?>
    </p>
    <?php
}

/**
 * Cache TTL field.
 */
function zskeleton_lms_category_subnav_cache_ttl_field_cb() {
    $value = (int) get_option('zskeleton_lms_category_subnav_cache_ttl', 300);
    ?>
    <input type="number" min="60" step="60" name="zskeleton_lms_category_subnav_cache_ttl"
           value="<?php echo esc_attr($value); ?>" />
    <?php
}

/**
 * Whether LMS category sub-nav is enabled and configured.
 *
 * @return bool
 */
function zskeleton_lms_category_subnav_is_enabled() {
    if (!(int) get_option('zskeleton_lms_category_subnav_enabled', 0)) {
        return false;
    }

    return '' !== zskeleton_lms_api_base_url();
}

/**
 * LMS API base URL (filterable).
 *
 * @return string
 */
function zskeleton_lms_api_base_url() {
    $default = rtrim((string) get_option('zskeleton_lms_api_base_url', ''), '/');
    return rtrim((string) apply_filters('zskeleton_lms_api_base_url', $default), '/');
}

/**
 * Map WP locale to LMS API locale code.
 *
 * @return string
 */
function zskeleton_lms_api_locale() {
    $locale = determine_locale();
    $short = substr(strtolower($locale), 0, 2);
    return apply_filters('zskeleton_lms_api_locale', $short, $locale);
}

/**
 * Build the category nav JSON endpoint URL.
 *
 * Accepts either LMS site root (https://example.com) or legacy API base
 * (https://example.com/api/development).
 *
 * @return string
 */
function zskeleton_lms_category_nav_endpoint() {
    $base = zskeleton_lms_api_base_url();
    if ('' === $base) {
        return '';
    }

    if (false !== strpos($base, '/api/development')) {
        return $base . '/course-categories/nav';
    }

    return $base . '/course-categories/nav';
}

/**
 * Fetch course categories from LMS API (cached).
 *
 * @return array<int, array<string, mixed>>
 */
function zskeleton_lms_fetch_course_categories() {
    $endpoint_base = zskeleton_lms_category_nav_endpoint();
    if ('' === $endpoint_base) {
        return array();
    }

    $locale = zskeleton_lms_api_locale();
    $cache_key = 'zskeleton_lms_cats_' . md5($endpoint_base . '|' . $locale);
    $cached = get_transient($cache_key);
    if (is_array($cached)) {
        return $cached;
    }

    $endpoint = $endpoint_base . '?locale=' . rawurlencode($locale);
    $response = wp_remote_get($endpoint, array(
        'timeout' => 12,
        'headers' => array(
            'Accept' => 'application/json',
        ),
    ));

    if (is_wp_error($response)) {
        return array();
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        return array();
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($body) || empty($body['success']) || empty($body['data']['categories'])) {
        return array();
    }

    $categories = array();
    foreach ($body['data']['categories'] as $category) {
        if (empty($category['title']) || empty($category['url'])) {
            continue;
        }
        $categories[] = array(
            'id' => isset($category['id']) ? (int) $category['id'] : 0,
            'title' => (string) $category['title'],
            'slug' => isset($category['slug']) ? (string) $category['slug'] : '',
            'url' => (string) $category['url'],
            'icon' => !empty($category['icon']) ? (string) $category['icon'] : '',
        );
    }

    $ttl = (int) get_option('zskeleton_lms_category_subnav_cache_ttl', 300);
    set_transient($cache_key, $categories, max(60, $ttl));

    return $categories;
}

/**
 * Render LMS category sub-navigation markup.
 */
function zskeleton_lms_render_category_subnav() {
    if (!zskeleton_lms_category_subnav_is_enabled()) {
        return;
    }

    $categories = zskeleton_lms_fetch_course_categories();
    if (empty($categories)) {
        return;
    }

    static $assets_printed = false;
    $is_rtl = is_rtl();
    ?>
    <nav id="lmsCategorySubNav" class="lms-category-subnav" aria-label="<?php esc_attr_e('Course categories', 'zskeleton'); ?>">
        <div class="container">
            <div class="lms-category-subnav-bar">
                <button type="button" class="lms-category-subnav-btn lms-category-subnav-btn--prev" disabled
                        aria-label="<?php esc_attr_e('Previous', 'zskeleton'); ?>">
                    <?php if ($is_rtl) : ?>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <?php else : ?>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    <?php endif; ?>
                </button>

                <div class="lms-category-subnav-scroll" tabindex="0"<?php echo $is_rtl ? ' dir="rtl"' : ''; ?>>
                    <?php foreach ($categories as $category) : ?>
                        <div class="lms-category-subnav-item">
                            <a href="<?php echo esc_url($category['url']); ?>" class="lms-category-subnav-link"
                               title="<?php echo esc_attr($category['title']); ?>">
                                <?php if (!empty($category['icon'])) : ?>
                                    <img src="<?php echo esc_url($category['icon']); ?>" class="lms-category-subnav-icon" alt="" width="20" height="20" loading="lazy" />
                                <?php endif; ?>
                                <span><?php echo esc_html($category['title']); ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="lms-category-subnav-btn lms-category-subnav-btn--next"
                        aria-label="<?php esc_attr_e('Next', 'zskeleton'); ?>">
                    <?php if ($is_rtl) : ?>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    <?php else : ?>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    <?php endif; ?>
                </button>
            </div>
        </div>
    </nav>
    <?php
    if (!$assets_printed) {
        $assets_printed = true;
        zskeleton_lms_category_subnav_print_assets();
    }
}
add_action('zskeleton_after_header_search', 'zskeleton_lms_render_category_subnav', 10);

/**
 * Print inline CSS/JS once per page.
 */
function zskeleton_lms_category_subnav_print_assets() {
    ?>
    <style id="lms-category-subnav-css">
        #lmsCategorySubNav.lms-category-subnav{background:#f8fafc;border-bottom:1px solid #e2e8f0;z-index:490}
        #lmsCategorySubNav .lms-category-subnav-bar{display:flex;flex-direction:row;flex-wrap:nowrap;align-items:center;gap:8px;padding:10px 0}
        #lmsCategorySubNav .lms-category-subnav-scroll{flex:1 1 auto;min-width:0;display:flex;flex-wrap:nowrap;align-items:stretch;gap:6px;overflow-x:auto;overflow-y:visible;scroll-behavior:smooth;-webkit-overflow-scrolling:touch;scrollbar-width:none;padding:2px 0}
        #lmsCategorySubNav .lms-category-subnav-scroll::-webkit-scrollbar{display:none}
        #lmsCategorySubNav .lms-category-subnav-item{flex:0 0 auto;min-width:0}
        #lmsCategorySubNav .lms-category-subnav-link{display:flex;align-items:center;justify-content:center;gap:6px;min-height:44px;padding:10px 14px;border-radius:6px;font-size:13px;font-weight:600;color:#1e3a5f;background:transparent;white-space:nowrap;line-height:1.4;text-decoration:none;box-sizing:border-box}
        #lmsCategorySubNav .lms-category-subnav-link:hover{color:#01477d;background:rgba(1,71,125,.07);text-decoration:none}
        #lmsCategorySubNav .lms-category-subnav-link span{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.4}
        #lmsCategorySubNav .lms-category-subnav-icon{width:20px;height:20px;object-fit:contain;flex-shrink:0}
        #lmsCategorySubNav .lms-category-subnav-btn{flex:0 0 36px;width:36px;height:36px;padding:0;border:1px solid #d8e0ea;border-radius:50%;background:#fff;color:#01477d;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;-webkit-appearance:none;appearance:none;flex-shrink:0}
        #lmsCategorySubNav .lms-category-subnav-btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2.5;pointer-events:none}
        #lmsCategorySubNav .lms-category-subnav-btn:hover:not(:disabled){background:#01477d;border-color:#01477d;color:#fff}
        #lmsCategorySubNav .lms-category-subnav-btn:disabled{opacity:.35;cursor:default}
        @media (max-width:991px){
            #lmsCategorySubNav .lms-category-subnav-bar{gap:6px;padding:8px 0}
            #lmsCategorySubNav .lms-category-subnav-scroll{scroll-snap-type:x mandatory;gap:8px}
            #lmsCategorySubNav .lms-category-subnav-item{flex:0 0 calc((100% - 8px)/2);width:calc((100% - 8px)/2);scroll-snap-align:start;scroll-snap-stop:always}
            #lmsCategorySubNav .lms-category-subnav-link{width:100%;min-height:48px;padding:10px 6px;font-size:11px}
            #lmsCategorySubNav .lms-category-subnav-icon{width:18px;height:18px}
            #lmsCategorySubNav .lms-category-subnav-btn{flex:0 0 32px;width:32px;height:32px}
        }
    </style>
    <script id="lms-category-subnav-js">
    (function(){
        function initLmsCategorySubNav(){
            var root=document.getElementById('lmsCategorySubNav');
            if(!root){return;}
            var scrollEl=root.querySelector('.lms-category-subnav-scroll');
            var prevBtn=root.querySelector('.lms-category-subnav-btn--prev');
            var nextBtn=root.querySelector('.lms-category-subnav-btn--next');
            if(!scrollEl||!prevBtn||!nextBtn){return;}
            var isRtl=scrollEl.getAttribute('dir')==='rtl'||document.documentElement.getAttribute('dir')==='rtl';
            function isMobile(){return window.matchMedia('(max-width:991px)').matches;}
            function maxScroll(){return Math.max(0,scrollEl.scrollWidth-scrollEl.clientWidth);}
            function normalizedScrollPos(){
                var max=maxScroll();
                if(max<=1){return 0;}
                var sl=scrollEl.scrollLeft;
                if(isRtl){
                    if(sl<0){return Math.min(max,Math.abs(sl));}
                    return Math.min(max,Math.max(0,max-sl));
                }
                return Math.min(max,Math.max(0,sl));
            }
            function scrollState(){
                var max=maxScroll();
                if(max<=1){return{atStart:true,atEnd:true};}
                var pos=normalizedScrollPos();
                return{atStart:pos<=2,atEnd:pos>=max-2};
            }
            function updateButtons(){
                var state=scrollState();
                prevBtn.disabled=state.atStart;
                nextBtn.disabled=state.atEnd;
            }
            function scrollByPage(direction){
                var max=maxScroll();
                if(max<=1){return;}
                var step=isMobile()?scrollEl.clientWidth:Math.round(scrollEl.clientWidth*0.85);
                var pos=normalizedScrollPos();
                var target=direction==='next'?Math.min(max,pos+step):Math.max(0,pos-step);
                if(isRtl){
                    if(scrollEl.scrollLeft<0){scrollEl.scrollTo({left:-target,behavior:'smooth'});}
                    else{scrollEl.scrollTo({left:max-target,behavior:'smooth'});}
                }else{
                    scrollEl.scrollTo({left:target,behavior:'smooth'});
                }
            }
            prevBtn.addEventListener('click',function(){scrollByPage('prev');});
            nextBtn.addEventListener('click',function(){scrollByPage('next');});
            scrollEl.addEventListener('scroll',updateButtons,{passive:true});
            window.addEventListener('resize',updateButtons);
            updateButtons();
        }
        if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',initLmsCategorySubNav);}
        else{initLmsCategorySubNav();}
    })();
    </script>
    <?php
}
