/**
 * ZSkeleton Membership Admin JavaScript
 * 
 * Handles admin user profile membership management functionality
 */

(function($) {
    'use strict';

    // Initialize admin membership functionality
    $(document).ready(function() {
        initMembershipFields();
        initQuickActions();
    });

    /**
     * Initialize membership form fields
     */
    function initMembershipFields() {
        // Show/hide organization field based on membership type
        $('#zskeleton_membership_type').on('change', function() {
            const membershipType = $(this).val();
            const $organizationField = $('.organization-field');
            
            if (membershipType === 'organizational') {
                $organizationField.slideDown();
                $organizationField.find('input').prop('required', true);
            } else {
                $organizationField.slideUp();
                $organizationField.find('input').prop('required', false);
            }
        });

        // Auto-calculate end date when start date changes
        $('#zskeleton_membership_start_date').on('change', function() {
            const startDate = $(this).val();
            const $endDateField = $('#zskeleton_membership_end_date');
            
            if (startDate && !$endDateField.val()) {
                const start = new Date(startDate);
                const end = new Date(start.getFullYear() + 1, start.getMonth(), start.getDate());
                const endDateString = end.toISOString().split('T')[0];
                $endDateField.val(endDateString);
            }
        });

        // Visual status indicators
        $('#zskeleton_membership_status').on('change', function() {
            const status = $(this).val();
            const $statusField = $(this);
            
            // Remove existing status classes
            $statusField.removeClass('status-active status-pending status-expired status-cancelled status-suspended');
            
            // Add status-specific class
            if (status) {
                $statusField.addClass('status-' + status);
            }
        });

        // Initialize status styling
        $('#zskeleton_membership_status').trigger('change');

        // Warn about unsaved changes
        let formChanged = false;
        $('#zskeleton-membership-fields input, #zskeleton-membership-fields select').on('change', function() {
            formChanged = true;
        });

        $(window).on('beforeunload', function() {
            if (formChanged) {
                return 'You have unsaved changes to the membership information. Are you sure you want to leave?';
            }
        });

        // Clear warning when form is submitted
        $('form').on('submit', function() {
            formChanged = false;
        });
    }

    /**
     * Initialize quick action buttons
     */
    function initQuickActions() {
        // Style quick action buttons
        $('.zskeleton-membership-actions button').each(function() {
            const $button = $(this);
            const action = $button.text().toLowerCase();
            
            if (action.includes('grant')) {
                $button.addClass('button-primary');
            } else if (action.includes('suspend') || action.includes('cancel')) {
                $button.addClass('button-secondary');
            }
        });
    }

    /**
     * Grant membership to user
     */
    window.zskeletonGrantMembership = function(userId, membershipType) {
        if (!confirm(zskeletonUserProfile.strings.confirm_grant)) {
            return;
        }

        const $button = $('button[onclick*="zskeletonGrantMembership(' + userId + ', \'' + membershipType + '\')"]');
        const originalText = $button.text();
        
        $button.prop('disabled', true).text('Granting...');

        $.ajax({
            url: zskeletonUserProfile.ajax_url,
            type: 'POST',
            data: {
                action: 'zskeleton_grant_membership',
                user_id: userId,
                membership_type: membershipType,
                nonce: zskeletonUserProfile.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update form fields
                    $('#zskeleton_membership_type').val(membershipType);
                    $('#zskeleton_membership_status').val('active');
                    
                    const today = new Date().toISOString().split('T')[0];
                    const nextYear = new Date();
                    nextYear.setFullYear(nextYear.getFullYear() + 1);
                    const endDate = nextYear.toISOString().split('T')[0];
                    
                    $('#zskeleton_membership_start_date').val(today);
                    $('#zskeleton_membership_end_date').val(endDate);
                    
                    if (membershipType === 'organizational') {
                        $('.organization-field').show();
                    }
                    
                    // Trigger change events to update styling
                    $('#zskeleton_membership_type, #zskeleton_membership_status').trigger('change');
                    
                    showAdminNotice(zskeletonUserProfile.strings.success, 'success');
                } else {
                    showAdminNotice(response.data || zskeletonUserProfile.strings.error, 'error');
                }
            },
            error: function() {
                showAdminNotice(zskeletonUserProfile.strings.error, 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    };

    /**
     * Extend membership
     */
    window.zskeletonExtendMembership = function(userId) {
        if (!confirm(zskeletonUserProfile.strings.confirm_extend)) {
            return;
        }

        const $button = $('button[onclick*="zskeletonExtendMembership(' + userId + ')"]');
        const originalText = $button.text();
        
        $button.prop('disabled', true).text('Extending...');

        $.ajax({
            url: zskeletonUserProfile.ajax_url,
            type: 'POST',
            data: {
                action: 'zskeleton_extend_membership',
                user_id: userId,
                nonce: zskeletonUserProfile.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update end date field
                    if (response.data.new_end_date) {
                        $('#zskeleton_membership_end_date').val(response.data.new_end_date);
                    }
                    
                    // Reactivate if was expired
                    if ($('#zskeleton_membership_status').val() === 'expired') {
                        $('#zskeleton_membership_status').val('active').trigger('change');
                    }
                    
                    showAdminNotice(zskeletonUserProfile.strings.success, 'success');
                } else {
                    showAdminNotice(response.data || zskeletonUserProfile.strings.error, 'error');
                }
            },
            error: function() {
                showAdminNotice(zskeletonUserProfile.strings.error, 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    };

    /**
     * Suspend membership
     */
    window.zskeletonSuspendMembership = function(userId) {
        if (!confirm(zskeletonUserProfile.strings.confirm_suspend)) {
            return;
        }

        const $button = $('button[onclick*="zskeletonSuspendMembership(' + userId + ')"]');
        const originalText = $button.text();
        
        $button.prop('disabled', true).text('Suspending...');

        $.ajax({
            url: zskeletonUserProfile.ajax_url,
            type: 'POST',
            data: {
                action: 'zskeleton_suspend_membership',
                user_id: userId,
                nonce: zskeletonUserProfile.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update status field
                    $('#zskeleton_membership_status').val('suspended').trigger('change');
                    
                    // Hide suspend button, show reactivate button
                    $button.hide();
                    
                    showAdminNotice(zskeletonUserProfile.strings.success, 'success');
                } else {
                    showAdminNotice(response.data || zskeletonUserProfile.strings.error, 'error');
                }
            },
            error: function() {
                showAdminNotice(zskeletonUserProfile.strings.error, 'error');
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    };

    /**
     * Send welcome email
     */
    window.zskeletonSendWelcomeEmail = function(userId) {
        if (!confirm('Send welcome email to this user?')) {
            return;
        }

        $.ajax({
            url: zskeletonUserProfile.ajax_url,
            type: 'POST',
            data: {
                action: 'zskeleton_send_welcome_email',
                user_id: userId,
                nonce: zskeletonUserProfile.nonce
            },
            success: function(response) {
                if (response.success) {
                    showAdminNotice('Welcome email sent successfully!', 'success');
                } else {
                    showAdminNotice(response.data || 'Failed to send email.', 'error');
                }
            },
            error: function() {
                showAdminNotice('Failed to send email.', 'error');
            }
        });
    };

    /**
     * Show admin notice
     */
    function showAdminNotice(message, type = 'info') {
        const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        
        // Insert after the page title
        $('h1').first().after($notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $notice.remove();
            });
        }, 5000);
        
        // Manual dismiss
        $notice.on('click', '.notice-dismiss', function() {
            $notice.fadeOut(function() {
                $notice.remove();
            });
        });
    }

    /**
     * Add AJAX handlers for membership actions
     */
    
    // Grant membership AJAX handler
    $(document).on('wp_ajax_zskeleton_grant_membership', function() {
        // This would be handled in PHP - just here for reference
    });

})(jQuery);

// Add CSS for membership status styling
if (document.head) {
    const style = document.createElement('style');
    style.textContent = `
        #zskeleton_membership_status.status-active {
            border-left: 4px solid #28a745;
            background-color: #d4edda;
        }
        
        #zskeleton_membership_status.status-pending {
            border-left: 4px solid #ffc107;
            background-color: #fff3cd;
        }
        
        #zskeleton_membership_status.status-expired {
            border-left: 4px solid #dc3545;
            background-color: #f8d7da;
        }
        
        #zskeleton_membership_status.status-cancelled,
        #zskeleton_membership_status.status-suspended {
            border-left: 4px solid #6c757d;
            background-color: #e2e3e5;
        }
        
        .zskeleton-membership-actions button {
            margin-right: 8px;
            margin-bottom: 4px;
        }
        
        .organization-field {
            background: #f0f8ff;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #007cba;
        }
        
        #zskeleton-membership-fields tr:hover {
            background-color: #f5f5f5;
        }
        
        .membership-quick-info {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin: 10px 0;
        }
        
        .membership-quick-info .status {
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .membership-quick-info .status.active {
            background: #d4edda;
            color: #155724;
        }
        
        .membership-quick-info .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .membership-quick-info .status.expired {
            background: #f8d7da;
            color: #721c24;
        }
        
        .membership-quick-info .status.suspended,
        .membership-quick-info .status.cancelled {
            background: #e2e3e5;
            color: #383d41;
        }
    `;
    document.head.appendChild(style);
}