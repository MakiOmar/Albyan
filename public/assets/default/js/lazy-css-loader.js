/**
 * Lazy CSS Loader Utility
 * Loads CSS files only when needed based on user interaction
 * Supports theme-specific CSS files and minified versions
 */
class LazyCSSLoader {
    constructor() {
        this.loadedCSS = new Set();
        this.pendingCSS = new Map();
        this._vendorCssCallbacks = {};
        this.theme = this.detectTheme();
        this.isRtl = this.detectRtl();
        this.init();
    }

    init() {
        // Set up event listeners for different interactions
        this.setupEventListeners();
    }

    detectTheme() {
        // Try to detect theme from meta tag or data attribute
        const themeMeta = document.querySelector('meta[name="theme"]');
        if (themeMeta) {
            return themeMeta.getAttribute('content');
        }
        
        // Try to detect from body class or data attribute
        const body = document.body;
        const themeClass = Array.from(body.classList).find(cls => cls.startsWith('theme-'));
        if (themeClass) {
            return themeClass.replace('theme-', '');
        }
        
        // Default to 'default' theme
        return 'default';
    }

    detectRtl() {
        // Check if RTL is enabled
        const html = document.documentElement;
        const body = document.body;
        return html.dir === 'rtl' || html.lang === 'ar' || html.lang === 'fa' ||
               (body && body.classList.contains('rtl')) || html.classList.contains('rtl');
    }

    /** Skip fetch if a matching stylesheet link is already in the document (e.g. page pushed critical CSS). */
    isStylesheetPresent(href) {
        const file = href.split('/').pop().split('?')[0];
        if (!file) {
            return false;
        }
        return [...document.querySelectorAll('link[rel="stylesheet"]')].some((link) => {
            const h = (link.getAttribute('href') || '').split('/').pop().split('?')[0];
            return h === file;
        });
    }

    getCSSPath(cssFile, useMinified = true) {
        // Get the appropriate CSS path based on theme and RTL
        const theme = this.theme;
        const isRtl = this.isRtl;
        const minified = useMinified ? '.min' : '';
        
        // Define CSS file mappings (only for lazy-loaded CSS files)
        const cssMappings = {
            'sweetalert2': `/assets/${theme}/vendors/sweetalert2/dist/sweetalert2${minified}.css`,
            'toast': `/assets/${theme}/vendors/toast/jquery.toast${minified}.css`,
            'swiper': `/assets/${theme}/vendors/swiper/swiper-bundle${minified}.css`,
            'simplebar': `/assets/${theme}/vendors/simplebar/simplebar${minified}.css`,
            'owl-carousel': `/assets/${theme}/vendors/owl-carousel2/owl.carousel${minified}.css`
        };
        
        // Return the mapped path or fallback to default
        return cssMappings[cssFile] || `/assets/${theme}/vendors/${cssFile}/${cssFile}${minified}.css`;
    }

    setupEventListeners() {
        // SweetAlert2 - Load on any button click that might trigger alerts
        this.setupSweetAlert2Listeners();
        
        // Toast - Load on any form submission or action that might show toasts
        this.setupToastListeners();
        
        // Swiper + Owl Carousel CSS: first user interaction only (not render-blocking)
        this.setupCarouselVendorCSSOnInteraction();
        
        // SimpleBar - Load when scrollable containers are detected
        this.setupSimpleBarListeners();
    }

    setupSweetAlert2Listeners() {
        // Load SweetAlert2 CSS on any button click that might trigger alerts
        document.addEventListener('click', (e) => {
            const target = e.target.closest('button, a, [data-confirm], [data-swal], .btn-delete, .btn-remove');
            if (target && this.mightTriggerAlert(target)) {
                this.loadCSSWithFallback('sweetalert2');
            }
        }, { once: false, passive: true });
    }

    setupToastListeners() {
        // Load Toast CSS on form submissions or actions that might show toasts
        document.addEventListener('submit', () => {
            this.loadCSSWithFallback('toast');
        }, { once: false, passive: true });

        // Also load on any AJAX calls that might show toasts
        if (window.jQuery) {
            $(document).ajaxComplete(() => {
                this.loadCSSWithFallback('toast');
            });
        }
    }

    /**
     * Load Swiper / Owl vendor CSS after first user gesture (scroll, wheel, touch, pointer, key, click).
     * Page scripts should defer Swiper/Owl init via onVendorCssReady() until this runs.
     */
    setupCarouselVendorCSSOnInteraction() {
        const needSwiper = document.querySelector('.swiper, [data-swiper], .swiper-container');
        const needOwl = document.querySelector('.owl-carousel, [data-owl-carousel]');
        if (!needSwiper && !needOwl) {
            return;
        }

        let fired = false;
        const events = ['scroll', 'wheel', 'touchstart', 'pointerdown', 'keydown', 'click'];
        const opts = { passive: true };

        const cleanup = () => {
            events.forEach((ev) => {
                window.removeEventListener(ev, onInteract, opts);
                document.removeEventListener(ev, onInteract, opts);
            });
        };

        const onInteract = () => {
            if (fired) {
                return;
            }
            fired = true;
            cleanup();
            const promises = [];
            if (needSwiper) {
                promises.push(this.loadCSSWithFallback('swiper'));
            }
            if (needOwl) {
                promises.push(this.loadCSSWithFallback('owl-carousel'));
            }
            Promise.all(promises).catch(() => {});
        };

        events.forEach((ev) => {
            window.addEventListener(ev, onInteract, opts);
            document.addEventListener(ev, onInteract, opts);
        });
    }

    /** True if swiper or owl-carousel stylesheet is already present or loaded. */
    isVendorCssReady(vendorKey) {
        const href = this.getCSSPath(vendorKey, true);
        return this.loadedCSS.has(href) || this.isStylesheetPresent(href);
    }

    /**
     * Run callback after vendor carousel CSS is available (already loaded or after interaction load).
     * Use from page scripts so Swiper/Owl init runs with styles applied.
     */
    onVendorCssReady(vendorKey, callback) {
        if (typeof callback !== 'function') {
            return;
        }
        if (this.isVendorCssReady(vendorKey)) {
            try {
                callback();
            } catch (e) {
                console.error(e);
            }
            return;
        }
        if (!this._vendorCssCallbacks[vendorKey]) {
            this._vendorCssCallbacks[vendorKey] = [];
        }
        this._vendorCssCallbacks[vendorKey].push(callback);
    }

    _flushVendorCallbacks(vendorKey) {
        const q = this._vendorCssCallbacks[vendorKey];
        if (!q || !q.length) {
            return;
        }
        const cbs = q.splice(0, q.length);
        cbs.forEach((cb) => {
            try {
                cb();
            } catch (e) {
                console.error(e);
            }
        });
    }

    setupSimpleBarListeners() {
        const scrollableContainers = document.querySelectorAll('[data-simplebar], .simplebar, .scrollable, .custom-scrollbar');
        if (!scrollableContainers.length) {
            return;
        }
        const observer = new IntersectionObserver((entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    this.loadCSSWithFallback('simplebar');
                    observer.disconnect();
                    return;
                }
            }
        }, { rootMargin: '160px 0px', threshold: 0.01 });
        scrollableContainers.forEach((c) => observer.observe(c));
    }

    mightTriggerAlert(element) {
        // Check if element might trigger an alert
        const alertIndicators = [
            'data-confirm',
            'data-swal',
            'btn-delete',
            'btn-remove',
            'btn-danger',
            'delete',
            'remove',
            'confirm'
        ];

        const className = element.className.toLowerCase();
        const id = element.id ? element.id.toLowerCase() : '';
        const dataAttributes = Array.from(element.attributes)
            .filter(attr => attr.name.startsWith('data-'))
            .map(attr => attr.name.toLowerCase());

        return alertIndicators.some(indicator => 
            className.includes(indicator) || 
            id.includes(indicator) || 
            dataAttributes.some(attr => attr.includes(indicator))
        );
    }

    loadCSS(href, fallbackHref = null) {
        if (this.loadedCSS.has(href)) {
            return Promise.resolve();
        }
        if (this.pendingCSS.has(href)) {
            return this.pendingCSS.get(href);
        }
        if (this.isStylesheetPresent(href)) {
            this.loadedCSS.add(href);
            return Promise.resolve();
        }

        const promise = new Promise((resolve, reject) => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.onload = () => {
                this.loadedCSS.add(href);
                resolve();
            };
            link.onerror = () => {
                if (fallbackHref && !this.loadedCSS.has(fallbackHref)) {
                    console.warn(`Failed to load CSS: ${href}, trying fallback: ${fallbackHref}`);
                    this.loadCSS(fallbackHref).then(resolve).catch(reject);
                } else {
                    reject(new Error(`Failed to load CSS: ${href}`));
                }
            };

            document.head.appendChild(link);
        });

        this.pendingCSS.set(href, promise);
        promise.finally(() => {
            if (this.pendingCSS.get(href) === promise) {
                this.pendingCSS.delete(href);
            }
        });

        return promise;
    }

    loadCSSWithFallback(cssFile) {
        // Try theme-specific minified first, then fallback to default
        const themePath = this.getCSSPath(cssFile, true);
        const fallbackPath = this.getCSSPath(cssFile, true).replace(`/assets/${this.theme}/`, '/assets/default/');
        
        return this.loadCSS(themePath, fallbackPath)
            .then(() => {
                this._flushVendorCallbacks(cssFile);
            })
            .catch(() => {
                this._flushVendorCallbacks(cssFile);
            });
    }

    // Public method to manually load CSS
    loadCSSManually(href) {
        return this.loadCSS(href);
    }

    // Check if CSS is already loaded
    isLoaded(href) {
        return this.loadedCSS.has(href);
    }

    // Get current theme information
    getThemeInfo() {
        return {
            theme: this.theme,
            isRtl: this.isRtl,
            loadedCSS: Array.from(this.loadedCSS),
            pendingCSS: Array.from(this.pendingCSS.keys())
        };
    }

    // Preload CSS files (useful for critical CSS)
    preloadCSS(cssFile) {
        return this.loadCSSWithFallback(cssFile);
    }

    // Batch load multiple CSS files
    loadMultipleCSS(cssFiles) {
        return Promise.all(cssFiles.map(cssFile => this.loadCSSWithFallback(cssFile)));
    }

    // Note: Main application CSS files (app.min.css, rtl-app.min.css, panel.min.css) 
    // are loaded immediately in the HTML head and should NOT be lazy loaded
    // as they contain critical styles needed for initial page render
}

// Single init: script runs at end of body after parse; DOM is ready enough for querySelector probes
window.lazyCSSLoader = new LazyCSSLoader();
