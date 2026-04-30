/**
 * ZSkeleton PayPal Payment Handler
 * 
 * Handles PayPal checkout integration
 */

(function($) {
    'use strict';

    const ZSkeleton_PayPal = {
        /**
         * Initialize PayPal Buttons
         */
        init: function() {
            // Find PayPal button container
            const paypalButtonContainer = document.getElementById('paypal-button-container');
            if (!paypalButtonContainer) {
                return;
            }

            // Get payment form
            const paymentForm = document.querySelector('.zskeleton-payment-form');
            if (!paymentForm) {
                return;
            }

            // Initialize PayPal Buttons
            paypal.Buttons({
                style: {
                    layout: 'vertical',
                    color: 'blue',
                    shape: 'rect',
                    label: 'paypal',
                },

                // Create order
                createOrder: function(data, actions) {
                    return ZSkeleton_PayPal.createOrder(paymentForm);
                },

                // On approve (payment authorized)
                onApprove: function(data, actions) {
                    return ZSkeleton_PayPal.captureOrder(data.orderID, paymentForm);
                },

                // On error
                onError: function(err) {
                    console.error('PayPal Error:', err);
                    ZSkeleton_PayPal.showError('Payment error occurred. Please try again.');
                },

                // On cancel
                onCancel: function(data) {
                    ZSkeleton_PayPal.showError('Payment was cancelled.');
                },
            }).render('#paypal-button-container');

            console.log('ZSkeleton PayPal: Initialized');
        },

        /**
         * Create PayPal Order
         */
        createOrder: function(form) {
            return new Promise((resolve, reject) => {
                // Get form data
                const formData = new FormData(form);
                const amount = parseFloat(formData.get('amount'));
                const description = formData.get('description') || 'ZSkeleton Payment';

                // Validate amount
                if (!amount || amount <= 0) {
                    reject(new Error('Invalid payment amount'));
                    return;
                }

                // Disable form
                ZSkeleton_PayPal.setFormDisabled(form, true);

                // Create order via AJAX
                $.ajax({
                    url: zskeletonPayPal.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'zskeleton_create_paypal_order',
                        nonce: zskeletonPayPal.nonce,
                        amount: amount,
                        description: description,
                    },
                    success: function(response) {
                        if (response.success && response.data.orderID) {
                            resolve(response.data.orderID);
                        } else {
                            const errorMessage = response.data && response.data.message ? response.data.message : 'Order creation failed';
                            reject(new Error(errorMessage));
                        }
                    },
                    error: function(xhr, status, error) {
                        reject(new Error('AJAX Error: ' + error));
                    },
                });
            });
        },

        /**
         * Capture PayPal Order
         */
        captureOrder: function(orderID, form) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: zskeletonPayPal.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'zskeleton_capture_paypal_order',
                        nonce: zskeletonPayPal.nonce,
                        order_id: orderID,
                    },
                    success: function(response) {
                        if (response.success) {
                            ZSkeleton_PayPal.handleSuccess(response.data, orderID, form);
                            resolve();
                        } else {
                            const errorMessage = response.data && response.data.message ? response.data.message : 'Payment capture failed';
                            ZSkeleton_PayPal.showError(errorMessage);
                            ZSkeleton_PayPal.setFormDisabled(form, false);
                            reject(new Error(errorMessage));
                        }
                    },
                    error: function(xhr, status, error) {
                        const errorMessage = 'AJAX Error: ' + error;
                        ZSkeleton_PayPal.showError(errorMessage);
                        ZSkeleton_PayPal.setFormDisabled(form, false);
                        reject(new Error(errorMessage));
                    },
                });
            });
        },

        /**
         * Handle successful payment
         */
        handleSuccess: function(data, orderID, form) {
            // Show success message
            ZSkeleton_PayPal.showSuccess(data.message);

            // Store payment details in hidden fields
            const orderIdField = document.createElement('input');
            orderIdField.type = 'hidden';
            orderIdField.name = 'paypal_order_id';
            orderIdField.value = orderID;
            form.appendChild(orderIdField);

            const paymentStatusField = document.createElement('input');
            paymentStatusField.type = 'hidden';
            paymentStatusField.name = 'payment_status';
            paymentStatusField.value = 'completed';
            form.appendChild(paymentStatusField);

            // Trigger custom event
            const event = new CustomEvent('zskeletonPaymentSuccess', {
                detail: {
                    orderID: orderID,
                    data: data,
                },
            });
            document.dispatchEvent(event);

            // Submit form if it's a registration/submission form
            if (form.classList.contains('auto-submit-after-payment')) {
                setTimeout(() => {
                    form.submit();
                }, 2000);
            } else {
                // Re-enable form
                ZSkeleton_PayPal.setFormDisabled(form, false);
            }
        },

        /**
         * Show error message
         */
        showError: function(message) {
            const errorDiv = document.getElementById('payment-errors') || document.getElementById('paypal-errors');
            if (errorDiv) {
                errorDiv.innerHTML = '<p style="color: #d97706; background: #fef3c7; padding: 12px; border-radius: 4px; border: 1px solid #fde68a;">' + message + '</p>';
                errorDiv.style.display = 'block';
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                alert('Payment Error: ' + message);
            }
        },

        /**
         * Show success message
         */
        showSuccess: function(message) {
            const successDiv = document.getElementById('payment-success');
            if (successDiv) {
                successDiv.innerHTML = '<p style="color: #059669; background: #d1fae5; padding: 12px; border-radius: 4px; border: 1px solid #a7f3d0;"><strong>✅ ' + message + '</strong></p>';
                successDiv.style.display = 'block';
                successDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            // Hide PayPal buttons
            const paypalContainer = document.getElementById('paypal-button-container');
            if (paypalContainer) {
                paypalContainer.style.display = 'none';
            }

            // Hide error messages
            const errorDiv = document.getElementById('paypal-errors');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        },

        /**
         * Set form disabled state
         */
        setFormDisabled: function(form, disabled) {
            const inputs = form.querySelectorAll('input, select, textarea, button');
            inputs.forEach(input => {
                input.disabled = disabled;
            });
        },
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Wait for PayPal SDK to load
        if (typeof paypal !== 'undefined') {
            ZSkeleton_PayPal.init();
        } else {
            // Retry after a short delay
            setTimeout(() => {
                if (typeof paypal !== 'undefined') {
                    ZSkeleton_PayPal.init();
                }
            }, 1000);
        }
    });

    // Export for external use
    window.ZSkeleton_PayPal = ZSkeleton_PayPal;

})(jQuery);

