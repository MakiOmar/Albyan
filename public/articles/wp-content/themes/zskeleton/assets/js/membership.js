/**
 * ZSkeleton Membership JavaScript
 * 
 * Handles membership-specific functionality and interactions
 */

(function($) {
    'use strict';

    // Membership object
    const ZSkeletonMembership = {
        
        // Initialize membership functionality
        init: function() {
            this.setupMembershipForms();
            this.setupAccessChecks();
            this.setupPaymentHandling();
            this.setupTrialTracking();
            this.setupMemberDashboard();
            this.setupContentGating();
        },

        // Setup membership forms
        setupMembershipForms: function() {
            // Registration form enhancement
            $('#zskeleton-registration-form').on('submit', function(e) {
                e.preventDefault();
                ZSkeletonMembership.handleRegistration($(this));
            });

            // Membership upgrade/downgrade
            $('.membership-change-form').on('submit', function(e) {
                e.preventDefault();
                ZSkeletonMembership.handleMembershipChange($(this));
            });

            // Organization details toggle
            $('input[name="membership_interest"]').on('change', function() {
                const membershipType = $(this).val();
                ZSkeletonMembership.toggleOrganizationFields(membershipType);
            });

            // Real-time form validation
            $('.membership-form input, .membership-form select').on('blur', function() {
                ZSkeletonMembership.validateField($(this));
            });
        },

        // Handle registration form submission
        handleRegistration: function($form) {
            const formData = new FormData($form[0]);
            formData.append('action', 'zskeleton_registration');
            formData.append('nonce', zskeletonAjax.nonce);

            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();

            // Show loading state
            $submitBtn.prop('disabled', true).text('Creating Account...');

            $.ajax({
                url: zskeletonAjax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        ZSkeletonMembership.showSuccessMessage($form, response.data);
                        $form[0].reset();
                        
                        // Redirect to payment if membership selected
                        const membershipType = $form.find('input[name="membership_interest"]:checked').val();
                        if (membershipType && membershipType !== 'information') {
                            setTimeout(() => {
                                window.location.href = response.data.payment_url || '/membership-payment/';
                            }, 2000);
                        }
                    } else {
                        ZSkeletonMembership.showErrorMessage($form, response.data);
                    }
                },
                error: function() {
                    ZSkeletonMembership.showErrorMessage($form, 'Registration failed. Please try again.');
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        // Handle membership changes
        handleMembershipChange: function($form) {
            const changeType = $form.data('change-type');
            const confirmMessage = changeType === 'cancel' 
                ? 'Are you sure you want to cancel your membership?' 
                : 'Confirm membership change?';

            if (!confirm(confirmMessage)) {
                return;
            }

            const formData = $form.serialize();
            
            $.ajax({
                url: zskeletonAjax.ajax_url,
                type: 'POST',
                data: formData + '&action=zskeleton_membership_change&nonce=' + zskeletonAjax.nonce,
                success: function(response) {
                    if (response.success) {
                        ZSkeletonMembership.showSuccessMessage($form, response.data);
                        
                        // Reload page after successful change
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        ZSkeletonMembership.showErrorMessage($form, response.data);
                    }
                }
            });
        },

        // Toggle organization fields based on membership type
        toggleOrganizationFields: function(membershipType) {
            const $orgFields = $('.organization-fields');
            
            if (membershipType === 'organizational') {
                $orgFields.slideDown();
                $orgFields.find('input, select').prop('required', true);
            } else {
                $orgFields.slideUp();
                $orgFields.find('input, select').prop('required', false);
            }
        },

        // Real-time field validation
        validateField: function($field) {
            const fieldType = $field.attr('type') || $field.prop('tagName').toLowerCase();
            const value = $field.val().trim();
            let isValid = true;
            let errorMessage = '';

            // Clear previous errors
            $field.removeClass('is-invalid');
            $field.siblings('.invalid-feedback').remove();

            // Required field check
            if ($field.prop('required') && !value) {
                isValid = false;
                errorMessage = 'This field is required.';
            }

            // Email validation
            if (fieldType === 'email' && value && !ZSkeletonMembership.isValidEmail(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address.';
            }

            // Username validation
            if ($field.attr('name') === 'username' && value) {
                if (value.length < 3) {
                    isValid = false;
                    errorMessage = 'Username must be at least 3 characters.';
                } else if (!/^[a-zA-Z0-9_-]+$/.test(value)) {
                    isValid = false;
                    errorMessage = 'Username can only contain letters, numbers, hyphens, and underscores.';
                }
            }

            // Password validation
            if (fieldType === 'password' && value && value.length < 8) {
                isValid = false;
                errorMessage = 'Password must be at least 8 characters.';
            }

            if (!isValid) {
                $field.addClass('is-invalid');
                $field.after(`<div class="invalid-feedback">${errorMessage}</div>`);
            }

            return isValid;
        },

        // Setup access checks for restricted content
        setupAccessChecks: function() {
            // Check access for gated content
            $('.member-only-content').each(function() {
                const $content = $(this);
                const postId = $content.data('post-id');
                
                if (postId) {
                    ZSkeletonMembership.checkContentAccess(postId, $content);
                }
            });

            // Handle access denied links
            $('.access-denied-link').on('click', function(e) {
                e.preventDefault();
                ZSkeletonMembership.showMembershipModal();
            });
        },

        // Check content access via AJAX
        checkContentAccess: function(postId, $content) {
            $.ajax({
                url: zskeletonAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'zskeleton_check_access',
                    post_id: postId,
                    nonce: zskeletonAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.has_access) {
                            $content.removeClass('access-denied').addClass('access-granted');
                        } else {
                            ZSkeletonMembership.showAccessDeniedMessage($content, response.data);
                        }
                    }
                }
            });
        },

        // Show access denied message
        showAccessDeniedMessage: function($content, accessData) {
            const membershipType = accessData.is_member ? 'upgrade' : 'join';
            const message = membershipType === 'upgrade' 
                ? 'Upgrade your membership to access this content.'
                : 'Become a member to access this exclusive content.';

            const $notice = $(`
                <div class="access-denied-notice">
                    <div class="notice-content">
                        <h4>🔒 Member Access Required</h4>
                        <p>${message}</p>
                        <div class="notice-actions">
                            <a href="/memberships/" class="btn btn-primary">
                                ${membershipType === 'upgrade' ? 'Upgrade Membership' : 'Join ZSkeleton'}
                            </a>
                            <a href="/login/" class="btn btn-secondary">Member Login</a>
                        </div>
                    </div>
                </div>
            `);

            $content.html($notice);
        },

        // Setup payment handling
        setupPaymentHandling: function() {
            // Payment form validation
            $('.payment-form').on('submit', function(e) {
                if (!ZSkeletonMembership.validatePaymentForm($(this))) {
                    e.preventDefault();
                }
            });

            // Payment method selection
            $('input[name="payment_method"]').on('change', function() {
                const method = $(this).val();
                ZSkeletonMembership.showPaymentMethod(method);
            });

            // Coupon code handling
            $('.apply-coupon').on('click', function(e) {
                e.preventDefault();
                ZSkeletonMembership.applyCouponCode();
            });
        },

        // Validate payment form
        validatePaymentForm: function($form) {
            let isValid = true;
            const paymentMethod = $form.find('input[name="payment_method"]:checked').val();

            // Clear previous errors
            $form.find('.is-invalid').removeClass('is-invalid');
            $form.find('.invalid-feedback').remove();

            if (paymentMethod === 'credit_card') {
                // Validate credit card fields (if using custom implementation)
                const requiredFields = ['card_number', 'card_expiry', 'card_cvc', 'card_name'];
                
                requiredFields.forEach(field => {
                    const $field = $form.find(`[name="${field}"]`);
                    if (!$field.val().trim()) {
                        ZSkeletonMembership.showFieldError($field, 'This field is required.');
                        isValid = false;
                    }
                });

                // Validate card number (basic check)
                const cardNumber = $form.find('[name="card_number"]').val().replace(/\s/g, '');
                if (cardNumber && !ZSkeletonMembership.isValidCardNumber(cardNumber)) {
                    const $cardField = $form.find('[name="card_number"]');
                    ZSkeletonMembership.showFieldError($cardField, 'Please enter a valid card number.');
                    isValid = false;
                }
            }

            return isValid;
        },

        // Show payment method fields
        showPaymentMethod: function(method) {
            $('.payment-method-fields').hide();
            $(`.payment-method-${method}`).show();
        },

        // Apply coupon code
        applyCouponCode: function() {
            const couponCode = $('#coupon-code').val().trim();
            
            if (!couponCode) {
                alert('Please enter a coupon code.');
                return;
            }

            $.ajax({
                url: zskeletonAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'zskeleton_apply_coupon',
                    coupon_code: couponCode,
                    nonce: zskeletonAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        ZSkeletonMembership.updatePricing(response.data);
                        $('.coupon-success').show().text(response.data.message);
                    } else {
                        $('.coupon-error').show().text(response.data);
                    }
                }
            });
        },

        // Update pricing display
        updatePricing: function(pricingData) {
            $('.original-price').text('$' + pricingData.original_price);
            $('.discount-amount').text('-$' + pricingData.discount);
            $('.final-price').text('$' + pricingData.final_price);
            $('.pricing-breakdown').show();
        },

        // Setup trial tracking
        setupTrialTracking: function() {
            if ($('body').hasClass('trial-member')) {
                ZSkeletonMembership.initTrialCountdown();
                ZSkeletonMembership.trackTrialUsage();
            }
        },

        // Initialize trial countdown
        initTrialCountdown: function() {
            const trialEndDate = $('.trial-countdown').data('end-date');
            
            if (trialEndDate) {
                const countdown = setInterval(() => {
                    const timeLeft = ZSkeletonMembership.calculateTimeLeft(trialEndDate);
                    
                    if (timeLeft.total <= 0) {
                        clearInterval(countdown);
                        $('.trial-countdown').html('<span class="trial-expired">Trial Expired</span>');
                        ZSkeletonMembership.showTrialExpiredModal();
                    } else {
                        $('.trial-countdown').html(`
                            <span class="trial-time">
                                ${timeLeft.days}d ${timeLeft.hours}h ${timeLeft.minutes}m remaining
                            </span>
                        `);
                    }
                }, 60000); // Update every minute
            }
        },

        // Calculate time left in trial
        calculateTimeLeft: function(endDate) {
            const total = Date.parse(endDate) - Date.parse(new Date());
            const seconds = Math.floor((total / 1000) % 60);
            const minutes = Math.floor((total / 1000 / 60) % 60);
            const hours = Math.floor((total / (1000 * 60 * 60)) % 24);
            const days = Math.floor(total / (1000 * 60 * 60 * 24));

            return { total, days, hours, minutes, seconds };
        },

        // Track trial usage
        trackTrialUsage: function() {
            // Track pages viewed during trial
            $.post(zskeletonAjax.ajax_url, {
                action: 'zskeleton_track_trial_usage',
                page: window.location.pathname,
                nonce: zskeletonAjax.nonce
            });
        },

        // Setup member dashboard functionality
        setupMemberDashboard: function() {
            // Update profile information
            $('.update-profile-form').on('submit', function(e) {
                e.preventDefault();
                ZSkeletonMembership.updateProfile($(this));
            });

            // Download usage reports
            $('.download-usage-report').on('click', function(e) {
                e.preventDefault();
                ZSkeletonMembership.downloadUsageReport();
            });

            // Membership renewal
            $('.renew-membership').on('click', function(e) {
                e.preventDefault();
                ZSkeletonMembership.renewMembership();
            });
        },

        // Update member profile
        updateProfile: function($form) {
            const formData = $form.serialize();
            
            $.ajax({
                url: zskeletonAjax.ajax_url,
                type: 'POST',
                data: formData + '&action=zskeleton_update_profile&nonce=' + zskeletonAjax.nonce,
                success: function(response) {
                    if (response.success) {
                        ZSkeletonMembership.showSuccessMessage($form, 'Profile updated successfully!');
                    } else {
                        ZSkeletonMembership.showErrorMessage($form, response.data);
                    }
                }
            });
        },

        // Download usage report
        downloadUsageReport: function() {
            window.location.href = zskeletonAjax.ajax_url + '?action=zskeleton_download_usage_report&nonce=' + zskeletonAjax.nonce;
        },

        // Renew membership
        renewMembership: function() {
            if (confirm('Proceed with membership renewal?')) {
                window.location.href = '/membership-payment/?action=renewal';
            }
        },

        // Setup content gating
        setupContentGating: function() {
            // Progressive content reveal for non-members
            $('.content-preview').each(function() {
                const $preview = $(this);
                const fullContentUrl = $preview.data('full-content-url');
                
                $preview.find('.read-more-btn').on('click', function(e) {
                    e.preventDefault();
                    
                    if ($('body').hasClass('has-membership')) {
                        // Load full content for members
                        ZSkeletonMembership.loadFullContent($preview, fullContentUrl);
                    } else {
                        // Show membership modal for non-members
                        ZSkeletonMembership.showMembershipModal();
                    }
                });
            });

            // Content interaction tracking
            $('.restricted-content').on('click', function() {
                if (!$('body').hasClass('has-membership')) {
                    ZSkeletonMembership.trackContentInteraction($(this).data('content-id'));
                }
            });
        },

        // Load full content for members
        loadFullContent: function($preview, contentUrl) {
            $preview.addClass('loading');
            
            $.get(contentUrl)
                .done(function(data) {
                    $preview.replaceWith(data);
                })
                .fail(function() {
                    ZSkeletonMembership.showErrorMessage($preview, 'Failed to load content.');
                })
                .always(function() {
                    $preview.removeClass('loading');
                });
        },

        // Track content interaction for analytics
        trackContentInteraction: function(contentId) {
            $.post(zskeletonAjax.ajax_url, {
                action: 'zskeleton_track_content_interaction',
                content_id: contentId,
                nonce: zskeletonAjax.nonce
            });
        },

        // Show membership modal
        showMembershipModal: function() {
            const modalHtml = `
                <div class="membership-modal modal active">
                    <div class="modal-overlay"></div>
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>🏛️ Join ZSkeleton</h3>
                            <button class="modal-close">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>Unlock exclusive access to our comprehensive member library, practical tools, and expert insights.</p>
                            
                            <div class="membership-benefits">
                                <h4>Member Benefits:</h4>
                                <ul>
                                    <li>✓ Exclusive reports and whitepapers</li>
                                    <li>✓ Comprehensive tools library</li>
                                    <li>✓ Professional terminology and resources</li>
                                    <li>✓ Practical guidelines and best practices</li>
                                    <li>✓ Regulatory updates and analysis</li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="/memberships/" class="btn btn-primary">Learn About Membership</a>
                            <a href="/login/" class="btn btn-secondary">Member Login</a>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
        },

        // Show trial expired modal
        showTrialExpiredModal: function() {
            const modalHtml = `
                <div class="trial-expired-modal modal active">
                    <div class="modal-overlay"></div>
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>⏰ Trial Expired</h3>
                        </div>
                        <div class="modal-body">
                            <p>Your ZSkeleton trial has ended. Continue your journey with us by becoming a full member.</p>
                            <p><strong>Don't lose access to:</strong></p>
                            <ul>
                                <li>Exclusive member content</li>
                                <li>Professional tools and resources</li>
                                <li>Expert insights and analysis</li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <a href="/memberships/" class="btn btn-primary">Upgrade to Full Membership</a>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
        },

        // Utility functions
        showSuccessMessage: function($form, message) {
            const $message = $(`<div class="alert alert-success">${message}</div>`);
            $form.before($message);
            
            setTimeout(() => {
                $message.fadeOut(() => $message.remove());
            }, 5000);
        },

        showErrorMessage: function($form, message) {
            const $message = $(`<div class="alert alert-error">${message}</div>`);
            $form.before($message);
            
            setTimeout(() => {
                $message.fadeOut(() => $message.remove());
            }, 7000);
        },

        showFieldError: function($field, message) {
            $field.addClass('is-invalid');
            $field.after(`<div class="invalid-feedback">${message}</div>`);
        },

        isValidEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        isValidCardNumber: function(cardNumber) {
            // Basic Luhn algorithm check
            let sum = 0;
            let alternate = false;
            
            for (let i = cardNumber.length - 1; i >= 0; i--) {
                let n = parseInt(cardNumber.charAt(i), 10);
                
                if (alternate) {
                    n *= 2;
                    if (n > 9) {
                        n = (n % 10) + 1;
                    }
                }
                
                sum += n;
                alternate = !alternate;
            }
            
            return (sum % 10) === 0;
        }
    };

    // Initialize membership functionality when document is ready
    $(document).ready(function() {
        ZSkeletonMembership.init();
    });

    // Make membership object globally available
    window.ZSkeletonMembership = ZSkeletonMembership;

})(jQuery);
