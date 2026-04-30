/**
 * ZSkeleton Theme Main JavaScript
 * 
 * Core functionality for the formal design system
 */

(function($) {
    'use strict';

    // jQuery only ships linear/swing; register cubic easing used by smooth scroll (avoids jQuery UI dependency).
    if ( ! $.easing.easeInOutCubic ) {
        $.easing.easeInOutCubic = function( p ) {
            return p < 0.5 ? 4 * p * p * p : 1 - Math.pow( -2 * p + 2, 3 ) / 2;
        };
    }

    // Theme object
    const ZSkeleton = {
        
        // Initialize theme
        init: function() {
            this.setupMobileMenu();
            this.setupMobileBottomNavStyle2();
            this.setupSearch();
            this.setupSmoothScrolling();
            this.setupAccessibilityFeatures();
            this.setupFormValidation();
            this.setupLoadingStates();
            this.setupTooltips();
            this.setupModalHandlers();
            this.trackMemberActivity();
        },

        // Mobile menu functionality
        setupMobileMenu: function() {
            const $menuToggle = $('.menu-toggle');
            const $menuClose = $('.menu-close');
            const $navigation = $('.mobile-navigation');
            const $overlay = $('.mobile-overlay');
            const $body = $('body');
            
            // Open menu
            $menuToggle.on('click', function(e) {
                e.preventDefault();
                
                console.log('Menu toggle clicked');
                console.log('Navigation element:', $navigation);
                console.log('Navigation HTML:', $navigation.html());
                
                $navigation.addClass('active');
                $overlay.addClass('active');
                $body.addClass('menu-open');
                
                $(this).attr('aria-expanded', 'true');
                
                // Focus first menu item for accessibility
                setTimeout(function() {
                    $navigation.find('a:first').focus();
                }, 300);
            });
            
            // Close menu function
            function closeMenu() {
                console.log('Closing menu');
                $navigation.removeClass('active');
                $overlay.removeClass('active');
                $body.removeClass('menu-open');
                $menuToggle.attr('aria-expanded', 'false');
                
                // Close all open submenus
                $('.nav-menu .menu-item-has-children').removeClass('active');
                
                // Return focus to menu toggle
                $menuToggle.focus();
            }
            
            // Close menu handlers
            $menuClose.on('click', closeMenu);
            $overlay.on('click', closeMenu);
            
            // Close menu on escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $navigation.hasClass('active')) {
                    closeMenu();
                }
            });
            
            // Submenu toggle functionality for mobile
            $('.nav-menu .menu-item-has-children > a').on('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent event bubbling
                    
                    const $parent = $(this).parent();
                    const isActive = $parent.hasClass('active');
                    
                    // Close all other open submenus first
                    $('.nav-menu .menu-item-has-children').removeClass('active');
                    
                    // If this submenu wasn't active, open it
                    if (!isActive) {
                        $parent.addClass('active');
                    }
                }
            });
            
            // Close menu when clicking regular menu links (not submenu toggles)
            $('.nav-menu a').on('click', function(e) {
                if (window.innerWidth <= 768) {
                    // Don't close menu if this is a submenu toggle
                    if (!$(this).parent().hasClass('menu-item-has-children')) {
                        setTimeout(closeMenu, 300);
                    }
                }
            });
        },

        /**
         * Mobile bottom navigation — Style 2 popovers (search + share).
         */
        setupMobileBottomNavStyle2: function() {
            const root = document.querySelector('[data-zskeleton-mbn2]');
            if (!root) {
                return;
            }

            const backdrop = root.querySelector('[data-zskeleton-mbn2-backdrop]');
            const shareBtn = root.querySelector('[data-zskeleton-mbn2-share-toggle]');
            const searchBtn = root.querySelector('[data-zskeleton-mbn2-search-toggle]');
            const sharePopover = document.getElementById('zskeleton-mbn2-popover-share');
            const searchPopover = document.getElementById('zskeleton-mbn2-popover-search');
            const searchField = document.getElementById('zskeleton-mbn2-search-field');

            function setHidden(el, hidden) {
                if (!el) {
                    return;
                }
                if (hidden) {
                    el.setAttribute('hidden', 'hidden');
                    el.setAttribute('aria-hidden', 'true');
                } else {
                    el.removeAttribute('hidden');
                    el.setAttribute('aria-hidden', 'false');
                }
            }

            function closeAll() {
                setHidden(backdrop, true);
                setHidden(sharePopover, true);
                setHidden(searchPopover, true);

                if (shareBtn) {
                    shareBtn.setAttribute('aria-expanded', 'false');
                }
                if (searchBtn) {
                    searchBtn.setAttribute('aria-expanded', 'false');
                }

                document.body.classList.remove('zskeleton-mbn2-open');
            }

            function openShare() {
                if (!sharePopover || !shareBtn) {
                    return;
                }
                setHidden(searchPopover, true);
                if (searchBtn) {
                    searchBtn.setAttribute('aria-expanded', 'false');
                }

                setHidden(backdrop, false);
                setHidden(sharePopover, false);
                shareBtn.setAttribute('aria-expanded', 'true');
                document.body.classList.add('zskeleton-mbn2-open');
            }

            function openSearch() {
                if (!searchPopover || !searchBtn) {
                    return;
                }
                setHidden(sharePopover, true);
                if (shareBtn) {
                    shareBtn.setAttribute('aria-expanded', 'false');
                }

                setHidden(backdrop, false);
                setHidden(searchPopover, false);
                searchBtn.setAttribute('aria-expanded', 'true');
                document.body.classList.add('zskeleton-mbn2-open');

                window.setTimeout(function() {
                    if (searchField && typeof searchField.focus === 'function') {
                        searchField.focus();
                    }
                }, 0);
            }

            function toggleShare() {
                const isOpen = sharePopover && !sharePopover.hasAttribute('hidden');
                if (isOpen) {
                    closeAll();
                    if (shareBtn && typeof shareBtn.focus === 'function') {
                        shareBtn.focus();
                    }
                    return;
                }
                openShare();
            }

            function toggleSearch() {
                const isOpen = searchPopover && !searchPopover.hasAttribute('hidden');
                if (isOpen) {
                    closeAll();
                    if (searchBtn && typeof searchBtn.focus === 'function') {
                        searchBtn.focus();
                    }
                    return;
                }
                openSearch();
            }

            if (shareBtn) {
                shareBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleShare();
                });
            }

            if (searchBtn) {
                searchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleSearch();
                });
            }

            if (backdrop) {
                backdrop.addEventListener('click', function() {
                    closeAll();
                });
            }

            root.addEventListener('click', function(e) {
                const t = e.target;
                if (!(t instanceof Element)) {
                    return;
                }
                if (t.closest('.zskeleton-mbn2__popover')) {
                    e.stopPropagation();
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key !== 'Escape') {
                    return;
                }
                const anyOpen =
                    (sharePopover && !sharePopover.hasAttribute('hidden')) ||
                    (searchPopover && !searchPopover.hasAttribute('hidden'));
                if (!anyOpen) {
                    return;
                }
                closeAll();
            });
        },

        // Enhanced search functionality
        setupSearch: function() {
            const $searchToggle = $('.search-toggle');
            const $searchClose = $('.search-close');
            const $searchForm = $('.header-search');
            
            // Open search
            $searchToggle.on('click', function(e) {
                e.preventDefault();
                
                const isExpanded = $(this).attr('aria-expanded') === 'true';
                
                $(this).attr('aria-expanded', !isExpanded);
                $searchForm.toggleClass('active');
                
                if (!isExpanded) {
                    setTimeout(function() {
                        $searchForm.find('.search-field').focus();
                    }, 300);
                }
            });
            
            // Close search
            function closeSearch() {
                $searchToggle.attr('aria-expanded', 'false');
                $searchForm.removeClass('active');
            }
            
            $searchClose.on('click', closeSearch);
            
            // Close search on escape key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $searchForm.hasClass('active')) {
                    closeSearch();
                }
            });

            // Live search functionality
            let searchTimeout;
            $('.search-field').on('input', function() {
                const $this = $(this);
                const query = $this.val().trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length >= 3) {
                    searchTimeout = setTimeout(function() {
                        ZSkeleton.performLiveSearch(query);
                    }, 300);
                } else {
                    $('.live-search-results').hide();
                }
            });
        },

        // Live search implementation
        performLiveSearch: function(query) {
            $.ajax({
                url: zskeletonAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'zskeleton_search',
                    query: query,
                    nonce: zskeletonAjax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        ZSkeleton.displaySearchResults(response.data);
                    } else {
                        $('.live-search-results').hide();
                    }
                }
            });
        },

        // Display search results
        displaySearchResults: function(results) {
            let resultsHtml = '<div class="live-search-results formal-card">';
            
            results.forEach(function(result) {
                resultsHtml += `
                    <div class="search-result-item">
                        <h4><a href="${result.url}">${result.title}</a></h4>
                        <p>${result.excerpt}</p>
                        <span class="result-type">${result.type}</span>
                    </div>
                `;
            });
            
            resultsHtml += '</div>';
            
            $('.header-search').append(resultsHtml);
            $('.live-search-results').show();
        },

        // Smooth scrolling for anchor links
        setupSmoothScrolling: function() {
            $('a[href*="#"]:not([href="#"])').on('click', function(e) {
                const target = $(this.hash);
                
                if (target.length) {
                    e.preventDefault();
                    
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 600, 'easeInOutCubic');
                    
                    // Update focus for accessibility
                    target.focus();
                }
            });
        },

        // Accessibility enhancements
        setupAccessibilityFeatures: function() {
            // Skip link functionality
            $('.skip-link').on('click', function(e) {
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    target.focus();
                }
            });

            // Keyboard navigation for dropdowns
            $('.menu-item-has-children > a').on('keydown', function(e) {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    $(this).siblings('.sub-menu').find('a:first').focus();
                }
            });

            // High contrast mode toggle
            if (localStorage.getItem('zskeleton-high-contrast') === 'true') {
                $('body').addClass('high-contrast-mode');
            }

            // Font size controls
            $('.font-size-controls .increase').on('click', function() {
                ZSkeleton.adjustFontSize('increase');
            });

            $('.font-size-controls .decrease').on('click', function() {
                ZSkeleton.adjustFontSize('decrease');
            });
        },

        // Font size adjustment
        adjustFontSize: function(direction) {
            const $body = $('body');
            let currentSize = parseInt($body.css('font-size')) || 16;
            
            if (direction === 'increase' && currentSize < 24) {
                currentSize += 2;
            } else if (direction === 'decrease' && currentSize > 12) {
                currentSize -= 2;
            }
            
            $body.css('font-size', currentSize + 'px');
            localStorage.setItem('zskeleton-font-size', currentSize);
        },

        // Form validation
        setupFormValidation: function() {
            $('form[data-validate="true"]').on('submit', function(e) {
                const $form = $(this);
                let isValid = true;
                
                // Clear previous errors
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').remove();
                
                // Validate required fields
                $form.find('[required]').each(function() {
                    const $field = $(this);
                    
                    if (!$field.val().trim()) {
                        ZSkeleton.showFieldError($field, 'This field is required.');
                        isValid = false;
                    }
                });
                
                // Validate email fields
                $form.find('input[type="email"]').each(function() {
                    const $field = $(this);
                    const email = $field.val().trim();
                    
                    if (email && !ZSkeleton.isValidEmail(email)) {
                        ZSkeleton.showFieldError($field, 'Please enter a valid email address.');
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    $form.find('.is-invalid:first').focus();
                }
            });
        },

        // Show field error
        showFieldError: function($field, message) {
            $field.addClass('is-invalid');
            $field.after(`<div class="form-text invalid-feedback">${message}</div>`);
        },

        // Email validation
        isValidEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        // Loading states
        setupLoadingStates: function() {
            // Show loading on form submit
            $('form[data-loading="true"]').on('submit', function() {
                const $form = $(this);
                const $submitBtn = $form.find('button[type="submit"]');
                
                $submitBtn.prop('disabled', true);
                $submitBtn.html('<span class="loading-spinner"></span> Processing...');
            });

            // AJAX loading indicator
            $(document).ajaxStart(function() {
                $('body').addClass('ajax-loading');
            }).ajaxStop(function() {
                $('body').removeClass('ajax-loading');
            });
        },

        // Tooltip functionality
        setupTooltips: function() {
            $('[data-tooltip]').each(function() {
                const $this = $(this);
                const tooltipText = $this.data('tooltip');
                
                $this.addClass('tooltip');
                $this.append(`<span class="tooltip-text">${tooltipText}</span>`);
            });
        },

        // Modal handlers
        setupModalHandlers: function() {
            // Open modal
            $('[data-modal]').on('click', function(e) {
                e.preventDefault();
                const modalId = $(this).data('modal');
                ZSkeleton.openModal(modalId);
            });

            // Close modal
            $(document).on('click', '.modal-close, .modal-overlay', function() {
                ZSkeleton.closeModal();
            });

            // Close modal on escape
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    ZSkeleton.closeModal();
                }
            });
        },

        // Open modal
        openModal: function(modalId) {
            const $modal = $('#' + modalId);
            
            if ($modal.length) {
                $modal.addClass('active');
                $('body').addClass('modal-open');
                
                // Focus first focusable element
                setTimeout(() => {
                    $modal.find('input, button, select, textarea, a').first().focus();
                }, 100);
            }
        },

        // Close modal
        closeModal: function() {
            $('.modal.active').removeClass('active');
            $('body').removeClass('modal-open');
        },

        // Track member activity for analytics
        trackMemberActivity: function() {
            if ($('body').hasClass('logged-in-user')) {
                // Track page views
                const data = {
                    action: 'zskeleton_track_activity',
                    page: window.location.pathname,
                    timestamp: Date.now(),
                    nonce: zskeletonAjax.nonce
                };

                // Send tracking data using jQuery AJAX instead of sendBeacon
                $.ajax({
                    url: zskeletonAjax.ajax_url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        // Tracking successful - no action needed
                    },
                    error: function(xhr, status, error) {
                        // Silently fail - don't interrupt user experience
                        console.log('Activity tracking failed:', error);
                    }
                });
                
                // Update last access on membership content
                if ($('body').hasClass('has-membership')) {
                    setTimeout(() => {
                        $.post(zskeletonAjax.ajax_url, {
                            action: 'zskeleton_update_last_access',
                            nonce: zskeletonAjax.nonce
                        });
                    }, 5000);
                }
            }
        },

        // Utility functions
        utils: {
            // Debounce function
            debounce: function(func, wait, immediate) {
                let timeout;
                return function executedFunction() {
                    const context = this;
                    const args = arguments;
                    const later = function() {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                    };
                    const callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func.apply(context, args);
                };
            },

            // Throttle function
            throttle: function(func, limit) {
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
            },

            // Format currency
            formatCurrency: function(amount, currency = 'USD') {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: currency
                }).format(amount);
            },

            // Format date
            formatDate: function(date, options = {}) {
                const defaultOptions = {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                
                return new Intl.DateTimeFormat('en-US', {
                    ...defaultOptions,
                    ...options
                }).format(new Date(date));
            },

            // Show notification
            showNotification: function(message, type = 'info', duration = 5000) {
                const $notification = $(`
                    <div class="notification notification-${type}">
                        <span class="notification-message">${message}</span>
                        <button class="notification-close">&times;</button>
                    </div>
                `);

                $('body').append($notification);
                
                setTimeout(() => {
                    $notification.addClass('show');
                }, 100);

                // Auto-hide notification
                setTimeout(() => {
                    ZSkeleton.utils.hideNotification($notification);
                }, duration);

                // Close button handler
                $notification.find('.notification-close').on('click', () => {
                    ZSkeleton.utils.hideNotification($notification);
                });
            },

            // Hide notification
            hideNotification: function($notification) {
                $notification.removeClass('show');
                setTimeout(() => {
                    $notification.remove();
                }, 300);
            }
        }
    };

    // Initialize theme when document is ready
    $(document).ready(function() {
        ZSkeleton.init();
    });

    // Make ZSkeleton object globally available
    window.ZSkeleton = ZSkeleton;

    // Handle window resize
    $(window).on('resize', ZSkeleton.utils.throttle(function() {
        // Close mobile menu on resize to desktop
        if ($(window).width() > 768) {
            $('.menu-toggle[aria-expanded="true"]').trigger('click');
        }
    }, 250));

    // Handle scroll events
    $(window).on('scroll', ZSkeleton.utils.throttle(function() {
        const scrollTop = $(window).scrollTop();
        
        // Add/remove scrolled class to header
        if (scrollTop > 100) {
            $('.site-header').addClass('scrolled');
        } else {
            $('.site-header').removeClass('scrolled');
        }
        
        // Show/hide back to top (omit when the control is disabled in theme settings).
        const $btt = $('.back-to-top');
        if ($btt.length) {
            if (scrollTop > 300) {
                $btt.addClass('visible');
            } else {
                $btt.removeClass('visible');
            }
        }
    }, 100));

    // Print page functionality
    $('.print-page').on('click', function(e) {
        e.preventDefault();
        window.print();
    });

    // Share functionality
    $('.share-button').on('click', function(e) {
        e.preventDefault();
        
        if (navigator.share) {
            navigator.share({
                title: document.title,
                url: window.location.href
            });
        } else {
            // Fallback: copy to clipboard
            navigator.clipboard.writeText(window.location.href).then(() => {
                ZSkeleton.utils.showNotification('Link copied to clipboard!', 'success');
            });
        }
    });

})(jQuery);

// Vanilla JS for critical functionality (no jQuery dependency)
document.addEventListener('DOMContentLoaded', function() {
    
    // Apply saved preferences
    const savedFontSize = localStorage.getItem('zskeleton-font-size');
    if (savedFontSize) {
        document.body.style.fontSize = savedFontSize + 'px';
    }

    const savedContrast = localStorage.getItem('zskeleton-high-contrast');
    if (savedContrast === 'true') {
        document.body.classList.add('high-contrast-mode');
    }

    // Progressive enhancement for forms
    const forms = document.querySelectorAll('form[data-enhance="true"]');
    forms.forEach(form => {
        // Add novalidate to use custom validation
        form.setAttribute('novalidate', 'true');
        
        // Add loading states
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('loading');
            }
        });
    });

    // Lazy loading for images
    const images = document.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    } else {
        // Fallback for older browsers
        images.forEach(img => {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
        });
    }


});
