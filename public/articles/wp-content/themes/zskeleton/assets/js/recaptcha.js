/**
 * ZSkeleton reCAPTCHA v3 Handler
 * 
 * Handles Google reCAPTCHA v3 token generation for forms
 */

(function($) {
    'use strict';

    const ZSkeletonRecaptcha = {
        
        // Initialize reCAPTCHA
        init: function() {
            if (typeof zskeletonRecaptcha === 'undefined' || !zskeletonRecaptcha.enabled) {
                console.log('ZSkeleton reCAPTCHA: Not enabled, forms will work normally');
                return;
            }

            console.log('ZSkeleton reCAPTCHA: Initialized with site key:', zskeletonRecaptcha.siteKey);

            // Wait for reCAPTCHA to load
            this.waitForRecaptcha(function() {
                ZSkeletonRecaptcha.attachToForms();
            });
        },

        // Wait for reCAPTCHA to be ready
        waitForRecaptcha: function(callback) {
            if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.ready === 'function') {
                grecaptcha.ready(callback);
            } else {
                setTimeout(function() {
                    ZSkeletonRecaptcha.waitForRecaptcha(callback);
                }, 100);
            }
        },

        // Attach reCAPTCHA to all forms with recaptcha_token field
        attachToForms: function() {
            const $forms = $('form').has('input[name="recaptcha_token"]');
            
            console.log('ZSkeleton reCAPTCHA: Found', $forms.length, 'forms to protect');

            $forms.each(function() {
                const $form = $(this);
                const $tokenField = $form.find('input[name="recaptcha_token"]');
                const $actionField = $form.find('input[name="recaptcha_action"]');
                const action = $actionField.val() || 'submit';

                // Generate token on form submit
                $form.on('submit', function(e) {
                    const currentToken = $tokenField.val();
                    
                    console.log('ZSkeleton reCAPTCHA: Form submit triggered, token:', currentToken ? 'EXISTS' : 'EMPTY');
                    
                    // If token is already set, allow submission
                    if (currentToken && currentToken.length > 0) {
                        console.log('ZSkeleton reCAPTCHA: Token valid, allowing form submission');
                        return true;
                    }

                    // Prevent default submission
                    e.preventDefault();
                    console.log('ZSkeleton reCAPTCHA: Prevented default, generating token for action:', action);

                    // Generate token
                    grecaptcha.execute(zskeletonRecaptcha.siteKey, { action: action })
                        .then(function(token) {
                            console.log('ZSkeleton reCAPTCHA: Token generated:', token.substring(0, 20) + '...');
                            $tokenField.val(token);
                            
                            // Add hidden field to indicate form was submitted programmatically
                            // (native submit() doesn't include button name)
                            if (!$form.find('input[name="form_submitted"]').length) {
                                $form.append('<input type="hidden" name="form_submitted" value="1">');
                            }
                            
                            console.log('ZSkeleton reCAPTCHA: Submitting form programmatically');
                            // Submit the form using native submit (bypasses jQuery handlers)
                            $form[0].submit();
                        })
                        .catch(function(error) {
                            console.error('ZSkeleton reCAPTCHA: Error generating token', error);
                            alert('Security verification failed. Please refresh the page and try again.');
                        });

                    return false;
                });
            });
        },

        // Manually generate token for a specific action
        generateToken: function(action, callback) {
            if (typeof grecaptcha === 'undefined') {
                console.error('ZSkeleton reCAPTCHA: grecaptcha not loaded');
                callback(null);
                return;
            }

            grecaptcha.execute(zskeletonRecaptcha.siteKey, { action: action })
                .then(function(token) {
                    callback(token);
                })
                .catch(function(error) {
                    console.error('ZSkeleton reCAPTCHA: Error generating token', error);
                    callback(null);
                });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        ZSkeletonRecaptcha.init();
    });

    // Make available globally
    window.ZSkeletonRecaptcha = ZSkeletonRecaptcha;

})(jQuery);

