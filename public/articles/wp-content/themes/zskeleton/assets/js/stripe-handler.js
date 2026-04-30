/**
 * ZSkeleton Stripe Payment Handler
 * 
 * Handles Stripe checkout integration
 */

(function($) {
    'use strict';

    // Initialize Stripe
    const stripe = Stripe(zskeletonStripe.publishableKey);
    let elements = null;
    let cardElement = null;

    const ZSkeleton_Stripe = {
        /**
         * Initialize Stripe Elements
         */
        init: function() {
            // Find payment form
            const paymentForm = document.querySelector('.zskeleton-payment-form');
            if (!paymentForm) {
                return;
            }

            // Create Elements instance
            elements = stripe.elements();

            // Create card element
            cardElement = elements.create('card', {
                value: {
                    postalCode: '00000'
                },
                style: {
                    base: {
                        fontSize: '16px',
                        color: '#32325d',
                        fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                        '::placeholder': {
                            color: '#aab7c4',
                        },
                    },
                    invalid: {
                        color: '#fa755a',
                        iconColor: '#fa755a',
                    },
                },
            });

            // Mount card element
            const cardElementDiv = document.getElementById('card-element');
            if (cardElementDiv) {
                cardElement.mount('#card-element');
            }

            // Handle real-time validation errors
            cardElement.on('change', function(event) {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                    displayError.style.display = 'block';
                } else {
                    displayError.textContent = '';
                    displayError.style.display = 'none';
                }
            });

            // Handle form submission
            paymentForm.addEventListener('submit', ZSkeleton_Stripe.handleSubmit);

            console.log('ZSkeleton Stripe: Initialized');
        },

        /**
         * Handle form submission
         */
        handleSubmit: async function(event) {
            event.preventDefault();

            const form = event.target;
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;

            // Disable submit button
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner"></span> Processing...';

            try {
                // Get form data
                const formData = new FormData(form);
                const amount = parseFloat(formData.get('amount'));
                const description = formData.get('description') || 'ZSkeleton Payment';
                const metadata = {};

                // Collect metadata from form
                const metadataFields = form.querySelectorAll('[data-stripe-metadata]');
                metadataFields.forEach(field => {
                    const key = field.getAttribute('data-stripe-metadata');
                    metadata[key] = field.value;
                });

                // Create Payment Intent
                const paymentIntent = await ZSkeleton_Stripe.createPaymentIntent(amount, description, metadata);

                if (!paymentIntent.success) {
                    throw new Error(paymentIntent.data.message || 'Payment initialization failed');
                }

                // Confirm payment with card
                const {error, paymentIntent: confirmedIntent} = await stripe.confirmCardPayment(
                    paymentIntent.data.clientSecret,
                    {
                        payment_method: {
                            card: cardElement,
                            billing_details: ZSkeleton_Stripe.getBillingDetails(form),
                        },
                    }
                );

                if (error) {
                    throw new Error(error.message);
                }

                // Payment succeeded
                if (confirmedIntent.status === 'succeeded') {
                    await ZSkeleton_Stripe.handleSuccess(confirmedIntent, form);
                } else {
                    throw new Error('Payment not completed. Status: ' + confirmedIntent.status);
                }

            } catch (error) {
                console.error('Stripe Error:', error);
                ZSkeleton_Stripe.showError(error.message);
                
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        },

        /**
         * Create Payment Intent via AJAX
         */
        createPaymentIntent: function(amount, description, metadata) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: zskeletonStripe.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'zskeleton_create_stripe_payment',
                        nonce: zskeletonStripe.nonce,
                        amount: amount,
                        description: description,
                        metadata: metadata,
                    },
                    success: function(response) {
                        resolve(response);
                    },
                    error: function(xhr, status, error) {
                        reject(new Error('AJAX Error: ' + error));
                    },
                });
            });
        },

        /**
         * Get billing details from form
         */
        getBillingDetails: function(form) {
            const formData = new FormData(form);
            
            return {
                name: formData.get('billing_name') || formData.get('name') || formData.get('first_name') + ' ' + formData.get('last_name'),
                email: formData.get('billing_email') || formData.get('email'),
                address: {
                    country: formData.get('billing_country') || formData.get('country'),
                    postal_code: '00000',
                },
            };
        },

        /**
         * Handle successful payment
         */
        handleSuccess: async function(paymentIntent, form) {
            // Confirm payment on server
            const confirmation = await ZSkeleton_Stripe.confirmPaymentOnServer(paymentIntent.id);

            if (!confirmation.success) {
                throw new Error(confirmation.data.message || 'Payment verification failed');
            }

            // Show success message
            ZSkeleton_Stripe.showSuccess(confirmation.data.message);

            // Store payment details in hidden fields for form processing
            const paymentIdField = document.createElement('input');
            paymentIdField.type = 'hidden';
            paymentIdField.name = 'stripe_payment_id';
            paymentIdField.value = paymentIntent.id;
            form.appendChild(paymentIdField);

            const paymentStatusField = document.createElement('input');
            paymentStatusField.type = 'hidden';
            paymentStatusField.name = 'payment_status';
            paymentStatusField.value = 'completed';
            form.appendChild(paymentStatusField);

            // Trigger custom event
            const event = new CustomEvent('zskeletonPaymentSuccess', {
                detail: {
                    paymentIntent: paymentIntent,
                    confirmation: confirmation.data,
                },
            });
            document.dispatchEvent(event);

            // Submit form if it's a registration/submission form
            if (form.classList.contains('auto-submit-after-payment')) {
                setTimeout(() => {
                    form.submit();
                }, 2000);
            }
        },

        /**
         * Confirm payment on server
         */
        confirmPaymentOnServer: function(paymentIntentId) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: zskeletonStripe.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'zskeleton_confirm_stripe_payment',
                        nonce: zskeletonStripe.nonce,
                        payment_intent_id: paymentIntentId,
                    },
                    success: function(response) {
                        resolve(response);
                    },
                    error: function(xhr, status, error) {
                        reject(new Error('AJAX Error: ' + error));
                    },
                });
            });
        },

        /**
         * Show error message
         */
        showError: function(message) {
            const errorDiv = document.getElementById('payment-errors') || document.getElementById('card-errors');
            if (errorDiv) {
                errorDiv.textContent = message;
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
                successDiv.innerHTML = '<p><strong>✅ ' + message + '</strong></p>';
                successDiv.style.display = 'block';
                successDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }

            // Hide card element
            const cardElementDiv = document.getElementById('card-element');
            if (cardElementDiv) {
                cardElementDiv.style.display = 'none';
            }

            // Hide error messages
            const errorDiv = document.getElementById('card-errors');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        },
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        if (typeof zskeletonStripe !== 'undefined' && zskeletonStripe.publishableKey) {
            ZSkeleton_Stripe.init();
        }
    });

    // Export for external use
    window.ZSkeleton_Stripe = ZSkeleton_Stripe;

})(jQuery);

