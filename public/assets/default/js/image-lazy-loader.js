/**
 * Modern Image Lazy Loading System - VERSION 4.3 (Production)
 * Uses Intersection Observer API for optimal performance
 * Maintains CLS scores by preserving image dimensions
 * Applies lazy loading to ALL img tags except logos
 * Automatically sets up lazy loading for images without data-src
 * Enhanced error handling with timeout and retry mechanisms
 * Preserves original URL format (relative vs absolute) for compatibility
 * Skips lazy loading for already loaded and visible images
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
                        return; // Don't set the src
                    }
                    originalDescriptor.set.call(this, value);
                },
                get: originalDescriptor.get,
                configurable: true
            });
        }

        // Set up mutation observer to catch dynamically added images
        const mutationObserver = new MutationObserver((mutations) => {
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

        mutationObserver.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    setupIntersectionObserver() {
        const options = {
            root: null,
            rootMargin: '50px',
            threshold: 0.1
        };

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    this.loadImage(img);
                    this.observer.unobserve(img);
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
        
        lazyImages.forEach((img, index) => {
            // If image doesn't have data-src, set it up for lazy loading
            if (!img.dataset.src && img.src && !img.src.includes('data:image/gif')) {
                // Check if image is already loaded and visible (skip lazy loading)
                if (img.complete && img.naturalWidth > 0) {
                    img.classList.add('lazy-loaded');
                    return;
                }
                
                // Preserve the original src format (relative vs absolute)
                img.dataset.src = img.src;
                img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                img.classList.add('lazy-loading');
            }
            
            // Check if image already has a real src (not placeholder)
            if (img.src && img.src !== 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' && !img.src.includes('undefined')) {
                img.classList.remove('lazy-loading');
                img.classList.add('lazy-loaded');
                if (img.dataset.src) {
                    this.loadedImages.add(img.dataset.src);
                }
                return; // Skip observing this image
            }
            
            // Fix any images that already have "undefined" as src
            if (img.src === 'undefined' || img.src.includes('undefined')) {
                img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                img.classList.add('lazy-error');
            }
            
            // Only observe if we have a data-src and haven't loaded it yet
            if (img.dataset.src && !this.loadedImages.has(img.dataset.src)) {
                this.observer.observe(img);
            }
        });
    }

    loadImage(img) {
        // Check if data-src exists and is not empty
        if (!img.dataset.src || img.dataset.src === 'undefined' || img.dataset.src.trim() === '') {
            img.classList.add('lazy-error');
            return;
        }

        // Check if image is already loaded (has a real src, not placeholder)
        if (img.src && img.src !== 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' && !img.src.includes('undefined')) {
            img.classList.remove('lazy-loading');
            img.classList.add('lazy-loaded');
            return;
        }

        if (this.loadedImages.has(img.dataset.src)) {
            return;
        }

        // Add loading class
        img.classList.add('lazy-loading');
        
        // Create a new image to preload
        const imageLoader = new Image();
        imageLoader.crossOrigin = 'anonymous'; // Allow cross-origin requests
        
        imageLoader.onload = () => {
            // Image loaded successfully
            // Store the original data-src before any modifications
            const originalDataSrc = img.dataset.src;
            
            // Mark as loaded
            this.loadedImages.add(originalDataSrc);
            
            // Update the actual image
            img.src = originalDataSrc;
            img.classList.remove('lazy-loading');
            img.classList.add('lazy-loaded');
        };

        imageLoader.onerror = (error) => {
            // Handle loading error
            img.classList.remove('lazy-loading');
            img.classList.add('lazy-error');
            
            // Set fallback image if available
            if (img.dataset.fallback) {
                img.src = img.dataset.fallback;
                img.classList.remove('lazy-error');
                img.classList.add('lazy-loaded');
            } else {
                // Try to load the image directly as a fallback
                const directImage = new Image();
                directImage.onload = () => {
                    img.src = img.dataset.src;
                    img.classList.remove('lazy-error');
                    img.classList.add('lazy-loaded');
                };
                directImage.onerror = () => {
                    img.src = '/assets/default/img/placeholder.svg';
                    img.classList.remove('lazy-error');
                    img.classList.add('lazy-loaded');
                };
                directImage.src = img.dataset.src;
            }
        };

        // Set a timeout for image loading
        const loadTimeout = setTimeout(() => {
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
                    // Preserve the original src format (relative vs absolute)
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

    fixUndefinedImages() {
        // Fix any images that already have "undefined" as src
        const allImages = document.querySelectorAll('img');
        allImages.forEach(img => {
            if (img.src === 'undefined' || img.src.includes('undefined')) {
                img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
                img.classList.add('lazy-error');
                
                // If it has a data-src, try to load it
                if (img.dataset.src && img.dataset.src !== 'undefined') {
                    this.loadImage(img);
                }
            }
        });
    }

    fixSingleImage(img) {
        if (img.src === 'undefined' || img.src.includes('undefined')) {
            img.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
            img.classList.add('lazy-error');
            
            if (img.dataset.src && img.dataset.src !== 'undefined') {
                this.loadImage(img);
            }
        }
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
        
        failedImages.forEach(img => {
            if (img.dataset.src) {
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
            return false;
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.imageLazyLoader = new ImageLazyLoader();
    
    // Add global method to retry failed images
    window.retryFailedImages = () => {
        if (window.imageLazyLoader) {
            window.imageLazyLoader.retryFailedImages();
        }
    };
});

// Also initialize if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.imageLazyLoader = new ImageLazyLoader();
    });
} else {
    window.imageLazyLoader = new ImageLazyLoader();
}