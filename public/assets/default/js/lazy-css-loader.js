/**
 * Lazy CSS Loader Utility
 * Loads CSS files only when needed based on user interaction
 */
class LazyCSSLoader {
    constructor() {
        this.loadedCSS = new Set();
        this.pendingCSS = new Map();
        this.init();
    }

    init() {
        // Set up event listeners for different interactions
        this.setupEventListeners();
    }

    setupEventListeners() {
        // SweetAlert2 - Load on any button click that might trigger alerts
        this.setupSweetAlert2Listeners();
        
        // Toast - Load on any form submission or action that might show toasts
        this.setupToastListeners();
        
        // Swiper - Load when swiper containers are in viewport
        this.setupSwiperListeners();
        
        // SimpleBar - Load when scrollable containers are detected
        this.setupSimpleBarListeners();
        
        // Owl Carousel - Load when carousel containers are in viewport
        this.setupOwlCarouselListeners();
    }

    setupSweetAlert2Listeners() {
        // Load SweetAlert2 CSS on any button click that might trigger alerts
        document.addEventListener('click', (e) => {
            const target = e.target.closest('button, a, [data-confirm], [data-swal], .btn-delete, .btn-remove');
            if (target && this.mightTriggerAlert(target)) {
                this.loadCSS('/assets/default/vendors/sweetalert2/dist/sweetalert2.min.css');
            }
        }, { once: false, passive: true });
    }

    setupToastListeners() {
        // Load Toast CSS on form submissions or actions that might show toasts
        document.addEventListener('submit', () => {
            this.loadCSS('/assets/default/vendors/toast/jquery.toast.min.css');
        }, { once: false, passive: true });

        // Also load on any AJAX calls that might show toasts
        if (window.jQuery) {
            $(document).ajaxComplete(() => {
                this.loadCSS('/assets/default/vendors/toast/jquery.toast.min.css');
            });
        }
    }

    setupSwiperListeners() {
        // Load Swiper CSS when swiper containers come into view
        const swiperContainers = document.querySelectorAll('.swiper, [data-swiper], .swiper-container');
        if (swiperContainers.length > 0) {
            this.loadCSS('/assets/default/vendors/swiper/swiper-bundle.min.css');
        }

        // Also load on any element with swiper-related classes
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadCSS('/assets/default/vendors/swiper/swiper-bundle.min.css');
                    observer.unobserve(entry.target);
                }
            });
        });

        swiperContainers.forEach(container => {
            observer.observe(container);
        });
    }

    setupSimpleBarListeners() {
        // Load SimpleBar CSS when scrollable containers are detected
        const scrollableContainers = document.querySelectorAll('[data-simplebar], .simplebar, .scrollable, .custom-scrollbar');
        if (scrollableContainers.length > 0) {
            this.loadCSS('/assets/default/vendors/simplebar/simplebar.css');
        }
    }

    setupOwlCarouselListeners() {
        // Load Owl Carousel CSS when carousel containers come into view
        const carouselContainers = document.querySelectorAll('.owl-carousel, [data-owl-carousel], .carousel');
        if (carouselContainers.length > 0) {
            this.loadCSS('/assets/default/vendors/owl-carousel2/owl.carousel.min.css');
        }

        // Also load on any element with carousel-related classes
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadCSS('/assets/default/vendors/owl-carousel2/owl.carousel.min.css');
                    observer.unobserve(entry.target);
                }
            });
        });

        carouselContainers.forEach(container => {
            observer.observe(container);
        });
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

    loadCSS(href) {
        // Don't load if already loaded or loading
        if (this.loadedCSS.has(href) || this.pendingCSS.has(href)) {
            return Promise.resolve();
        }

        // Mark as pending
        this.pendingCSS.set(href, true);

        return new Promise((resolve, reject) => {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.onload = () => {
                this.loadedCSS.add(href);
                this.pendingCSS.delete(href);
                resolve();
            };
            link.onerror = () => {
                this.pendingCSS.delete(href);
                reject(new Error(`Failed to load CSS: ${href}`));
            };

            document.head.appendChild(link);
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
}

// Initialize the lazy CSS loader when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.lazyCSSLoader = new LazyCSSLoader();
});

// Also initialize immediately if DOM is already ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.lazyCSSLoader = new LazyCSSLoader();
    });
} else {
    window.lazyCSSLoader = new LazyCSSLoader();
}
