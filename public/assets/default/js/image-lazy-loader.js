/**
 * Modern Image Lazy Loading System
 * Uses Intersection Observer API for optimal performance
 * Maintains CLS scores by preserving image dimensions
 */
class ImageLazyLoader {
    constructor() {
        this.observer = null;
        this.loadedImages = new Set();
        console.log('🚀 ImageLazyLoader constructor called');
        this.init();
    }

    init() {
        console.log('🔧 ImageLazyLoader init() called');
        // First, fix any existing images with "undefined" src
        this.fixUndefinedImages();
        
        // Add global protection against undefined src
        this.setupGlobalProtection();
        
        // Check if Intersection Observer is supported
        if ('IntersectionObserver' in window) {
            console.log('✅ IntersectionObserver supported, setting up observer');
            this.setupIntersectionObserver();
        } else {
            console.log('⚠️ IntersectionObserver not supported, using fallback');
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
                        console.warn('🚫 Stack trace:', new Error().stack);
                        return; // Don't set the src
                    }
                    console.log('🔧 Setting src to:', value, 'on element:', this);
                    originalDescriptor.set.call(this, value);
                },
                get: originalDescriptor.get,
                configurable: true
            });
        }
        
        // Add global MutationObserver to watch for src changes
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'src') {
                    const img = mutation.target;
                    if (img.tagName === 'IMG' && img.src === 'undefined') {
                        console.error('🚨 MUTATION OBSERVER: img src became undefined!');
                        console.error('🚨 Element:', img);
                        console.error('🚨 Stack trace:', new Error().stack);
                    }
                }
            });
        });
        
        observer.observe(document.body, {
            attributes: true,
            attributeFilter: ['src'],
            subtree: true
        });
        
        console.log('🛡️ Global protection and mutation observer set up');
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
        console.log(`🔍 Found ${lazyImages.length} lazy images to observe`);
        
        lazyImages.forEach((img, index) => {
            console.log(`🔍 Image ${index + 1}:`, {
                alt: img.alt,
                dataSrc: img.dataset.src,
                currentSrc: img.src,
                classes: img.className
            });
            
            // Fix any images that already have "undefined" as src
            if (img.src === 'undefined' || img.src.includes('undefined')) {
                console.warn('⚠️ Found image with undefined src before observing:', img.alt);
                img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                img.classList.add('lazy-error');
            }
            
            if (!this.loadedImages.has(img.dataset.src)) {
                this.observer.observe(img);
                console.log(`👁️ Started observing image ${index + 1}:`, img.alt);
            } else {
                console.log(`⏭️ Image ${index + 1} already loaded, skipping:`, img.alt);
            }
        });
    }

    loadImage(img) {
        console.log('🔍 Loading image:', {
            alt: img.alt,
            dataSrc: img.dataset.src,
            currentSrc: img.src,
            element: img
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
        console.log('🔄 Added lazy-loading class to:', img.alt);
        
        // Create a new image to preload
        const imageLoader = new Image();
        console.log('🖼️ Created new Image object for:', img.dataset.src);
        
        imageLoader.onload = () => {
            // Image loaded successfully
            console.log('✅ Image loaded successfully:', img.alt || img.dataset.src);
            console.log('🔍 Current data-src value:', img.dataset.src);
            console.log('🔍 Data-src type:', typeof img.dataset.src);
            console.log('🔍 Image element before modification:', img);
            console.log('🔍 Current src before modification:', img.src);
            
            // Store the original data-src before any modifications
            const originalDataSrc = img.dataset.src;
            console.log('🔍 Original data-src stored:', originalDataSrc);
            
            // Only set src if data-src is valid
            if (originalDataSrc && originalDataSrc !== 'undefined' && originalDataSrc.trim() !== '') {
                console.log('✅ Setting src to:', originalDataSrc);
                console.log('🔍 About to set img.src =', originalDataSrc);
                
                // Set the src
                img.src = originalDataSrc;
                
                console.log('🔍 After setting src, img.src is now:', img.src);
                console.log('🔍 Image element after setting src:', img);
                
                img.classList.remove('lazy-loading');
                img.classList.add('lazy-loaded');
                console.log('🔄 Updated classes - removed lazy-loading, added lazy-loaded');
                
                // Remove data-src to prevent reloading
                img.removeAttribute('data-src');
                console.log('🗑️ Removed data-src attribute');
                
                // Mark as loaded using the original value
                this.loadedImages.add(originalDataSrc);
                console.log('📝 Added to loaded images set:', originalDataSrc);
                
                // Trigger custom event
                img.dispatchEvent(new CustomEvent('lazyLoaded', {
                    detail: { image: img }
                }));
                console.log('🎉 Dispatched lazyLoaded event');
                
                // Watch for any changes to the src attribute after we set it
                setTimeout(() => {
                    console.log('🔍 Final check - img.src after 100ms:', img.src);
                    if (img.src === 'undefined') {
                        console.error('🚨 CRITICAL: src became undefined after setting!');
                        console.error('🚨 Image element:', img);
                        console.error('🚨 Original data-src was:', originalDataSrc);
                    }
                }, 100);
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
console.log('📜 image-lazy-loader.js script loaded');
document.addEventListener('DOMContentLoaded', () => {
    console.log('📜 DOMContentLoaded event fired, initializing ImageLazyLoader');
    window.imageLazyLoader = new ImageLazyLoader();
    console.log('📜 ImageLazyLoader instance created:', window.imageLazyLoader);
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
