/**
 * Modern Image Lazy Loading System - VERSION 4.1
 * Uses Intersection Observer API for optimal performance
 * Maintains CLS scores by preserving image dimensions
 * Applies lazy loading to ALL img tags except logos
 * Automatically sets up lazy loading for images without data-src
 * Enhanced error handling with timeout and retry mechanisms
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
        // Get all img tags except logos
        const allImages = document.querySelectorAll('img');
        const lazyImages = Array.from(allImages).filter(img => {
            // Skip logos - check for logo-related classes, alt text, or src patterns
            const isLogo = img.classList.contains('logo') || 
                          img.classList.contains('navbar-logo') ||
                          img.classList.contains('footer-logo') ||
                          img.classList.contains('site-logo') ||
                          img.alt.toLowerCase().includes('logo') ||
                          img.src.toLowerCase().includes('logo') ||
                          img.src.toLowerCase().includes('favicon') ||
                          img.id.toLowerCase().includes('logo');
            
            return !isLogo;
        });
        
        console.log(`🔍 Found ${allImages.length} total images, ${lazyImages.length} non-logo images to observe`);
        
        lazyImages.forEach((img, index) => {
            console.log(`🔍 Image ${index + 1}:`, {
                alt: img.alt,
                dataSrc: img.dataset.src,
                currentSrc: img.src,
                classes: img.className,
                id: img.id
            });
            
            // If image doesn't have data-src, set it up for lazy loading
            if (!img.dataset.src && img.src && !img.src.includes('data:image/gif')) {
                console.log(`🔄 Setting up lazy loading for image:`, img.alt);
                img.dataset.src = img.src;
                img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                img.classList.add('lazy-loading');
            }
            
            // Check if image already has a real src (not placeholder)
            if (img.src && img.src !== 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' && !img.src.includes('undefined')) {
                console.log(`⏭️ Image ${index + 1} already has real src, marking as loaded:`, img.alt);
                img.classList.remove('lazy-loading');
                img.classList.add('lazy-loaded');
                if (img.dataset.src) {
                    this.loadedImages.add(img.dataset.src);
                }
                return; // Skip observing this image
            }
            
            // Fix any images that already have "undefined" as src
            if (img.src === 'undefined' || img.src.includes('undefined')) {
                console.warn('⚠️ Found image with undefined src before observing:', img.alt);
                img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                img.classList.add('lazy-error');
            }
            
            // Only observe if we have a data-src and haven't loaded it yet
            if (img.dataset.src && !this.loadedImages.has(img.dataset.src)) {
                this.observer.observe(img);
                console.log(`👁️ Started observing image ${index + 1}:`, img.alt);
            } else if (img.dataset.src) {
                console.log(`⏭️ Image ${index + 1} already loaded, skipping:`, img.alt);
            }
        });
    }

    loadImage(img) {
        console.log('🔍 Loading image - VERSION 3.0:', {
            alt: img.alt,
            dataSrc: img.dataset.src,
            currentSrc: img.src,
            element: img
        });
        console.log('🔍 loadImage method called at:', new Date().toISOString());
        
        // Check if data-src exists and is not empty
        if (!img.dataset.src || img.dataset.src === 'undefined' || img.dataset.src.trim() === '') {
            console.warn('⚠️ No valid image source found for:', img.alt || 'unnamed image', 'data-src:', img.dataset.src);
            img.classList.add('lazy-error');
            return;
        }

        // Check if image is already loaded (has a real src, not placeholder)
        if (img.src && img.src !== 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' && !img.src.includes('undefined')) {
            console.log('⏭️ Image already has real src, skipping lazy load:', img.src);
            img.classList.remove('lazy-loading');
            img.classList.add('lazy-loaded');
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
        imageLoader.crossOrigin = 'anonymous'; // Allow cross-origin requests
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

        imageLoader.onerror = (error) => {
            // Handle loading error
            console.error('❌ Failed to load image:', img.dataset.src);
            console.error('❌ Image alt:', img.alt);
            console.error('❌ Attempted URL:', imageLoader.src);
            console.error('❌ Error details:', error);
            console.error('❌ Error type:', error.type);
            console.error('❌ Error target:', error.target);
            
            img.classList.remove('lazy-loading');
            img.classList.add('lazy-error');
            
            // Set fallback image if available
            if (img.dataset.fallback) {
                console.log('🔄 Using fallback image:', img.dataset.fallback);
                img.src = img.dataset.fallback;
                img.classList.remove('lazy-error');
                img.classList.add('lazy-loaded');
            } else {
                // Try to load the image directly as a fallback
                console.log('🔄 No fallback available, trying direct load');
                const directImage = new Image();
                directImage.onload = () => {
                    console.log('✅ Direct load successful:', img.dataset.src);
                    img.src = img.dataset.src;
                    img.classList.remove('lazy-error');
                    img.classList.add('lazy-loaded');
                };
                directImage.onerror = () => {
                    console.log('🔄 Direct load failed, using placeholder');
                    img.src = '/assets/default/img/placeholder.svg';
                    img.classList.remove('lazy-error');
                    img.classList.add('lazy-loaded');
                };
                directImage.src = img.dataset.src;
            }
        };

        // Set a timeout for image loading
        const loadTimeout = setTimeout(() => {
            console.warn('⏰ Image loading timeout:', img.dataset.src);
            imageLoader.onerror(new Error('Loading timeout'));
        }, 10000); // 10 second timeout

        // Clear timeout when image loads successfully
        const originalOnload = imageLoader.onload;
        imageLoader.onload = () => {
            clearTimeout(loadTimeout);
            originalOnload();
        };

        // Clear timeout when image fails to load
        const originalOnerror = imageLoader.onerror;
        imageLoader.onerror = (error) => {
            clearTimeout(loadTimeout);
            originalOnerror(error);
        };

        // Start loading
        console.log('🚀 Starting to load image:', img.dataset.src);
        imageLoader.src = img.dataset.src;
    }

    fallbackLazyLoad() {
        // Fallback for browsers without Intersection Observer
        const allImages = document.querySelectorAll('img');
        const lazyImages = Array.from(allImages).filter(img => {
            // Skip logos - check for logo-related classes, alt text, or src patterns
            const isLogo = img.classList.contains('logo') || 
                          img.classList.contains('navbar-logo') ||
                          img.classList.contains('footer-logo') ||
                          img.classList.contains('site-logo') ||
                          img.alt.toLowerCase().includes('logo') ||
                          img.src.toLowerCase().includes('logo') ||
                          img.src.toLowerCase().includes('favicon') ||
                          img.id.toLowerCase().includes('logo');
            
            return !isLogo;
        });
        
        const checkImages = () => {
            lazyImages.forEach(img => {
                // Set up lazy loading if not already done
                if (!img.dataset.src && img.src && !img.src.includes('data:image/gif')) {
                    img.dataset.src = img.src;
                    img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                    img.classList.add('lazy-loading');
                }
                
                if (img.dataset.src && this.isInViewport(img) && !this.loadedImages.has(img.dataset.src)) {
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

    // Public method to retry failed images
    retryFailedImages() {
        const failedImages = document.querySelectorAll('img.lazy-error');
        console.log(`🔄 Retrying ${failedImages.length} failed images`);
        
        failedImages.forEach(img => {
            if (img.dataset.src) {
                console.log('🔄 Retrying image:', img.alt || img.dataset.src);
                img.classList.remove('lazy-error');
                img.classList.add('lazy-loading');
                this.loadImage(img);
            }
        });
    }

    // Method to check if an image URL is accessible
    async checkImageAccessibility(url) {
        try {
            const response = await fetch(url, { method: 'HEAD' });
            return response.ok;
        } catch (error) {
            console.warn('⚠️ Image accessibility check failed:', url, error);
            return false;
        }
    }
}

// Initialize when DOM is ready
console.log('📜 image-lazy-loader.js script loaded - VERSION 4.1');
console.log('📜 Current time:', new Date().toISOString());
document.addEventListener('DOMContentLoaded', () => {
    console.log('📜 DOMContentLoaded event fired, initializing ImageLazyLoader');
    window.imageLazyLoader = new ImageLazyLoader();
    console.log('📜 ImageLazyLoader instance created:', window.imageLazyLoader);
    
    // Add global method to retry failed images
    window.retryFailedImages = () => {
        if (window.imageLazyLoader) {
            window.imageLazyLoader.retryFailedImages();
        } else {
            console.error('❌ ImageLazyLoader not available');
        }
    };
    
    console.log('📜 Global method available: window.retryFailedImages()');
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
