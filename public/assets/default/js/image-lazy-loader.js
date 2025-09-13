/**
 * Modern Image Lazy Loading System
 * Uses Intersection Observer API for optimal performance
 * Maintains CLS scores by preserving image dimensions
 */
class ImageLazyLoader {
    constructor() {
        this.observer = null;
        this.loadedImages = new Set();
        this.init();
    }

    init() {
        // First, fix any existing images with "undefined" src
        this.fixUndefinedImages();
        
        // Add global protection against undefined src
        this.setupGlobalProtection();
        
        // Check if Intersection Observer is supported
        if ('IntersectionObserver' in window) {
            this.setupIntersectionObserver();
        } else {
            // Fallback for older browsers
            this.fallbackLazyLoad();
        }
    }

    setupGlobalProtection() {
        // Override the src setter to prevent undefined values
        const originalDescriptor = Object.getOwnPropertyDescriptor(HTMLImageElement.prototype, 'src');
        if (originalDescriptor && originalDescriptor.set) {
            Object.defineProperty(HTMLImageElement.prototype, 'src', {
                set: function(value) {
                    if (value === 'undefined' || value === undefined || value === null) {
                        console.warn('🚫 Preventing undefined src assignment:', value);
                        return; // Don't set the src
                    }
                    originalDescriptor.set.call(this, value);
                },
                get: originalDescriptor.get,
                configurable: true
            });
        }
    }

    fixUndefinedImages() {
        // Fix any images that already have "undefined" as src
        const allImages = document.querySelectorAll('img');
        allImages.forEach(img => {
            if (img.src === 'undefined' || img.src.includes('undefined')) {
                console.warn('🔧 Fixing undefined src for image:', img.alt || 'unnamed image');
                img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                img.classList.add('lazy-error');
                
                // If it has a data-src, try to load it
                if (img.dataset.src && img.dataset.src !== 'undefined') {
                    this.loadImage(img);
                }
            }
        });
        
        // Also fix any images that might be loaded dynamically
        this.setupMutationObserver();
    }

    setupMutationObserver() {
        // Watch for dynamically added images
        if ('MutationObserver' in window) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) { // Element node
                            if (node.tagName === 'IMG') {
                                this.fixSingleImage(node);
                            }
                            // Check for images within added nodes
                            const images = node.querySelectorAll && node.querySelectorAll('img');
                            if (images) {
                                images.forEach(img => this.fixSingleImage(img));
                            }
                        }
                    });
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    }

    fixSingleImage(img) {
        if (img.src === 'undefined' || img.src.includes('undefined')) {
            console.warn('🔧 Fixing dynamically added undefined src for image:', img.alt || 'unnamed image');
            img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
            img.classList.add('lazy-error');
            
            // If it has a valid data-src, try to load it
            if (img.dataset.src && img.dataset.src !== 'undefined' && img.dataset.src.trim() !== '') {
                this.loadImage(img);
            }
        }
    }

    setupIntersectionObserver() {
        const options = {
            root: null,
            rootMargin: '50px', // Start loading 50px before image enters viewport
            threshold: 0.1
        };

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    console.log('🖼️ Image entering viewport:', entry.target.alt || entry.target.dataset.src);
                    this.loadImage(entry.target);
                    this.observer.unobserve(entry.target);
                }
            });
        }, options);

        // Observe all lazy images
        this.observeImages();
    }

    observeImages() {
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => {
            // Fix any images that already have "undefined" as src
            if (img.src === 'undefined' || img.src.includes('undefined')) {
                img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                img.classList.add('lazy-error');
            }
            
            if (!this.loadedImages.has(img.dataset.src)) {
                this.observer.observe(img);
            }
        });
    }

    loadImage(img) {
        console.log('🔍 Loading image:', {
            alt: img.alt,
            dataSrc: img.dataset.src,
            currentSrc: img.src
        });
        
        // Check if data-src exists and is not empty
        if (!img.dataset.src || img.dataset.src === 'undefined' || img.dataset.src.trim() === '') {
            console.warn('⚠️ No valid image source found for:', img.alt || 'unnamed image', 'data-src:', img.dataset.src);
            img.classList.add('lazy-error');
            return;
        }

        if (this.loadedImages.has(img.dataset.src)) {
            console.log('⏭️ Image already loaded:', img.dataset.src);
            return;
        }

        // Add loading class
        img.classList.add('lazy-loading');
        
        // Create a new image to preload
        const imageLoader = new Image();
        
        imageLoader.onload = () => {
            // Image loaded successfully
            console.log('✅ Image loaded successfully:', img.alt || img.dataset.src);
            console.log('🔍 Current data-src value:', img.dataset.src);
            console.log('🔍 Data-src type:', typeof img.dataset.src);
            
            // Store the original data-src before any modifications
            const originalDataSrc = img.dataset.src;
            
            // Only set src if data-src is valid
            if (originalDataSrc && originalDataSrc !== 'undefined' && originalDataSrc.trim() !== '') {
                console.log('✅ Setting src to:', originalDataSrc);
                img.src = originalDataSrc;
                img.classList.remove('lazy-loading');
                img.classList.add('lazy-loaded');
                
                // Remove data-src to prevent reloading
                img.removeAttribute('data-src');
                
                // Mark as loaded using the original value
                this.loadedImages.add(originalDataSrc);
                
                // Trigger custom event
                img.dispatchEvent(new CustomEvent('lazyLoaded', {
                    detail: { image: img }
                }));
            } else {
                // Invalid data-src, show error state
                console.warn('⚠️ Invalid data-src, showing error state:', img.alt || 'unnamed image');
                console.warn('⚠️ Data-src value was:', originalDataSrc);
                img.classList.remove('lazy-loading');
                img.classList.add('lazy-error');
            }
        };

        imageLoader.onerror = () => {
            // Handle loading error
            console.error('❌ Failed to load image:', img.dataset.src);
            console.error('❌ Image alt:', img.alt);
            console.error('❌ Attempted URL:', imageLoader.src);
            
            img.classList.remove('lazy-loading');
            img.classList.add('lazy-error');
            
            // Set fallback image if available
            if (img.dataset.fallback) {
                console.log('🔄 Using fallback image:', img.dataset.fallback);
                img.src = img.dataset.fallback;
            } else {
                console.log('🔄 No fallback available, keeping error state');
            }
        };

        // Start loading
        console.log('🚀 Starting to load image:', img.dataset.src);
        imageLoader.src = img.dataset.src;
    }

    fallbackLazyLoad() {
        // Fallback for browsers without Intersection Observer
        const lazyImages = document.querySelectorAll('img[data-src]');
        
        const checkImages = () => {
            lazyImages.forEach(img => {
                if (this.isInViewport(img) && !this.loadedImages.has(img.src)) {
                    this.loadImage(img);
                }
            });
        };

        // Check on scroll and resize
        window.addEventListener('scroll', this.throttle(checkImages, 100));
        window.addEventListener('resize', this.throttle(checkImages, 100));
        
        // Initial check
        checkImages();
    }

    isInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    // Public method to refresh lazy loading for dynamically added images
    refresh() {
        if (this.observer) {
            this.observeImages();
        } else {
            this.fallbackLazyLoad();
        }
    }

    // Public method to manually load an image
    loadImageNow(img) {
        if (img && img.dataset.src) {
            this.loadImage(img);
        }
    }

    // Public method to fix undefined images and refresh lazy loading
    fixAndRefresh() {
        this.fixUndefinedImages();
        this.refresh();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.imageLazyLoader = new ImageLazyLoader();
});

// Also initialize if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.imageLazyLoader = new ImageLazyLoader();
    });
} else {
    window.imageLazyLoader = new ImageLazyLoader();
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ImageLazyLoader;
}
