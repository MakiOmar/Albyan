<?php
/**
 * Front-end category sub-nav renderer and API client.
 *
 * @package Rocket_LMS_Category_Subnav
 */

if (!defined('ABSPATH')) {
    exit;
}

class RLMS_Cat_Subnav_Renderer {

    /**
     * Whether front-end script should load on this request.
     *
     * @var bool
     */
    private static $script_needed = false;

    /**
     * Register front-end hook.
     */
    public static function init() {
        $hook = apply_filters('rlms_cat_subnav_action_hook', 'zskeleton_after_header_search');
        if (!empty($hook) && is_string($hook)) {
            add_action($hook, array(__CLASS__, 'render'), 10);
        }

        add_action('wp_footer', array(__CLASS__, 'print_script'), 20);
    }

    /**
     * Map WP locale to LMS locale code.
     *
     * @return string
     */
    public static function api_locale() {
        $locale = determine_locale();
        $short = substr(strtolower($locale), 0, 2);
        return apply_filters('rlms_cat_subnav_api_locale', $short, $locale);
    }

    /**
     * Build JSON endpoint URL.
     *
     * @return string
     */
    public static function endpoint_url() {
        $base = RLMS_Cat_Subnav_Settings::lms_base_url();
        if ('' === $base) {
            return '';
        }

        return $base . '/course-categories/nav';
    }

    /**
     * Fetch categories from LMS (cached transient).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function fetch_categories() {
        $endpoint_base = self::endpoint_url();
        if ('' === $endpoint_base) {
            return array();
        }

        $locale = self::api_locale();
        $cache_key = 'rlms_cat_subnav_' . md5($endpoint_base . '|' . $locale);
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

        set_transient($cache_key, $categories, RLMS_Cat_Subnav_Settings::cache_ttl());

        return $categories;
    }

    /**
     * Output sub-nav markup.
     */
    public static function render() {
        if (!RLMS_Cat_Subnav_Settings::is_enabled()) {
            return;
        }

        $categories = self::fetch_categories();
        if (empty($categories)) {
            return;
        }

        static $assets_printed = false;
        $is_rtl = is_rtl();
        $container_class = apply_filters('rlms_cat_subnav_container_class', 'container');
        self::$script_needed = true;
        ?>
        <nav id="lmsCategorySubNav" class="lms-category-subnav" aria-label="<?php esc_attr_e('Course categories', 'rocket-lms-category-subnav'); ?>">
            <div class="<?php echo esc_attr($container_class); ?>">
                <div class="lms-category-subnav-bar">
                    <button type="button" class="lms-category-subnav-btn lms-category-subnav-btn--prev" disabled
                            aria-label="<?php esc_attr_e('Previous', 'rocket-lms-category-subnav'); ?>">
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
                            aria-label="<?php esc_attr_e('Next', 'rocket-lms-category-subnav'); ?>">
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
            self::print_styles();
        }
    }

    /**
     * Inline CSS (self-contained; no theme dependency).
     */
    public static function print_styles() {
        ?>
        <style id="lms-category-subnav-css">
            #lmsCategorySubNav.lms-category-subnav{background:#f8fafc;border-bottom:1px solid #e2e8f0;z-index:490}
            #lmsCategorySubNav .lms-category-subnav-bar{display:flex;flex-direction:row;flex-wrap:nowrap;align-items:center;gap:8px;padding:10px 0}
            #lmsCategorySubNav .lms-category-subnav-scroll{flex:1 1 auto;min-width:0;display:flex;flex-wrap:nowrap;align-items:stretch;gap:6px;overflow-x:auto;overflow-y:visible;scroll-behavior:smooth;-webkit-overflow-scrolling:touch;touch-action:pan-x;cursor:grab;scrollbar-width:none;padding:2px 0}
            #lmsCategorySubNav .lms-category-subnav-scroll::-webkit-scrollbar{display:none}
            #lmsCategorySubNav .lms-category-subnav-scroll.is-dragging{cursor:grabbing;scroll-behavior:auto;scroll-snap-type:none;user-select:none}
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
        <?php
    }

    /**
     * Front-end navigation script (footer so layout is ready).
     */
    public static function print_script() {
        if (!self::$script_needed) {
            return;
        }
        ?>
        <script id="lms-category-subnav-js">
        (function(){
            function initLmsCategorySubNav(){
                var root=document.getElementById('lmsCategorySubNav');
                if(!root||root.dataset.rlmsInit==='1'){return;}
                root.dataset.rlmsInit='1';

                var scrollEl=root.querySelector('.lms-category-subnav-scroll');
                var prevBtn=root.querySelector('.lms-category-subnav-btn--prev');
                var nextBtn=root.querySelector('.lms-category-subnav-btn--next');
                if(!scrollEl||!prevBtn||!nextBtn){return;}

                var isRtl=scrollEl.getAttribute('dir')==='rtl'
                    ||document.documentElement.getAttribute('dir')==='rtl'
                    ||window.getComputedStyle(scrollEl).direction==='rtl';

                function maxScroll(){return Math.max(0,scrollEl.scrollWidth-scrollEl.clientWidth);}

                function normalizedScrollPos(){
                    var max=maxScroll();
                    if(max<=1){return 0;}
                    var sl=scrollEl.scrollLeft;
                    if(!isRtl){return Math.min(max,Math.max(0,sl));}
                    if(sl<0){return Math.min(max,Math.abs(sl));}
                    if(sl>0){return Math.min(max,Math.max(0,max-sl));}
                    return 0;
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

                function scrollByOne(direction){
                    if(maxScroll()<=1){return;}
                    var items=Array.prototype.slice.call(scrollEl.querySelectorAll('.lms-category-subnav-item'));
                    if(!items.length){return;}
                    var scrollRect=scrollEl.getBoundingClientRect();
                    var leadIndex=0;
                    var bestDist=Infinity;
                    for(var i=0;i<items.length;i++){
                        var rect=items[i].getBoundingClientRect();
                        var dist=isRtl?Math.abs(rect.right-scrollRect.right):Math.abs(rect.left-scrollRect.left);
                        if(dist<bestDist){bestDist=dist;leadIndex=i;}
                    }
                    var targetIndex=direction==='next'
                        ?Math.min(items.length-1,leadIndex+1)
                        :Math.max(0,leadIndex-1);
                    if(targetIndex===leadIndex&&bestDist<=2){return;}
                    var targetRect=items[targetIndex].getBoundingClientRect();
                    var delta=isRtl?targetRect.right-scrollRect.right:targetRect.left-scrollRect.left;
                    if(Math.abs(delta)<1){return;}
                    scrollEl.scrollBy({left:delta,behavior:'smooth'});
                }

                prevBtn.addEventListener('click',function(e){
                    e.preventDefault();
                    scrollByOne('prev');
                });
                nextBtn.addEventListener('click',function(e){
                    e.preventDefault();
                    scrollByOne('next');
                });

                scrollEl.addEventListener('scroll',updateButtons,{passive:true});
                window.addEventListener('resize',updateButtons);
                window.addEventListener('load',updateButtons);

                (function bindDragScroll(el){
                    var dragging=false;
                    var startX=0;
                    var startScrollLeft=0;
                    var moved=false;
                    var activePointer=null;
                    function endDrag(e){
                        if(!dragging||(e&&activePointer!==null&&e.pointerId!==activePointer)){return;}
                        dragging=false;
                        activePointer=null;
                        el.classList.remove('is-dragging');
                        if(e&&el.releasePointerCapture){
                            try{el.releasePointerCapture(e.pointerId);}catch(err){}
                        }
                        if(moved){
                            el.dataset.suppressClick='1';
                            window.setTimeout(function(){delete el.dataset.suppressClick;},0);
                        }
                        updateButtons();
                    }
                    el.addEventListener('pointerdown',function(e){
                        if(e.pointerType!=='mouse'||e.button!==0){return;}
                        dragging=true;
                        moved=false;
                        activePointer=e.pointerId;
                        startX=e.clientX;
                        startScrollLeft=el.scrollLeft;
                        el.classList.add('is-dragging');
                        if(el.setPointerCapture){el.setPointerCapture(e.pointerId);}
                    });
                    el.addEventListener('pointermove',function(e){
                        if(!dragging||e.pointerId!==activePointer){return;}
                        var dx=e.clientX-startX;
                        if(Math.abs(dx)>3){moved=true;}
                        el.scrollLeft=startScrollLeft-dx;
                    });
                    el.addEventListener('pointerup',endDrag);
                    el.addEventListener('pointercancel',endDrag);
                    el.addEventListener('lostpointercapture',function(){
                        if(dragging){endDrag(null);}
                    });
                    el.addEventListener('click',function(e){
                        if(el.dataset.suppressClick==='1'){
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    },true);
                })(scrollEl);

                requestAnimationFrame(function(){
                    requestAnimationFrame(updateButtons);
                });
            }

            function boot(){initLmsCategorySubNav();}
            if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',boot);}
            else{boot();}
            window.addEventListener('load',boot);
        })();
        </script>
        <?php
    }
}
