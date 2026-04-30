/**
 * ZSkeleton FAQ Admin JavaScript
 * 
 * Handles FAQ management interface interactions
 */

jQuery(document).ready(function($) {
    'use strict';

    // Import default FAQs
    $('#import-default-faqs').on('click', function() {
        if (!confirm(zskeleton_faq_admin.strings.confirm_import)) {
            return;
        }

        const $button = $(this);
        const originalText = $button.text();
        
        $button.prop('disabled', true).text('Importing...');

        $.ajax({
            url: zskeleton_faq_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'zskeleton_import_default_faqs',
                nonce: zskeleton_faq_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data);
                    // Refresh the page to show updated stats
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotice('error', response.data || zskeleton_faq_admin.strings.error);
                }
            },
            error: function() {
                showNotice('error', zskeleton_faq_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Bulk actions
    $('#apply-bulk-action').on('click', function() {
        const action = $('#bulk-action-select').val();
        if (!action) {
            alert('Please select an action first.');
            return;
        }

        // Get selected FAQs (this would work on the FAQ list page)
        const selectedIds = [];
        $('input[name="post[]"]:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select FAQs to perform bulk actions on.');
            return;
        }

        if (action === 'delete' && !confirm(zskeleton_faq_admin.strings.confirm_bulk_delete)) {
            return;
        }

        const $button = $(this);
        const originalText = $button.text();
        
        $button.prop('disabled', true).text('Processing...');

        $.ajax({
            url: zskeleton_faq_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'zskeleton_bulk_faq_action',
                action_type: action,
                faq_ids: selectedIds,
                nonce: zskeleton_faq_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data);
                    // Refresh the page to show changes
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showNotice('error', response.data || zskeleton_faq_admin.strings.error);
                }
            },
            error: function() {
                showNotice('error', zskeleton_faq_admin.strings.error);
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
                $('#bulk-action-select').val('');
            }
        });
    });

    // FAQ reordering (if on FAQ list page)
    if ($('.wp-list-table tbody').length) {
        $('.wp-list-table tbody').sortable({
            items: 'tr',
            cursor: 'move',
            axis: 'y',
            handle: '.column-title',
            placeholder: 'ui-sortable-placeholder',
            update: function(event, ui) {
                const order = [];
                $('.wp-list-table tbody tr').each(function(index) {
                    const postId = $(this).find('input[name="post[]"]').val();
                    if (postId) {
                        order.push({
                            id: postId,
                            order: index + 1
                        });
                    }
                });

                // Save new order
                $.ajax({
                    url: zskeleton_faq_admin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'zskeleton_reorder_faqs',
                        order: order,
                        nonce: zskeleton_faq_admin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotice('success', 'FAQ order updated successfully!');
                        }
                    }
                });
            }
        });

        // Add visual indicators for sortable rows
        $('.wp-list-table tbody tr').each(function() {
            $(this).find('.column-title').css('cursor', 'move').attr('title', 'Drag to reorder');
        });
    }

    // Enhanced form interactions
    $('input[type="checkbox"]').on('change', function() {
        $(this).closest('tr').toggleClass('selected', $(this).is(':checked'));
    });

    // Auto-save settings
    let settingsTimeout;
    $('.form-table input, .form-table select').on('change', function() {
        clearTimeout(settingsTimeout);
        settingsTimeout = setTimeout(function() {
            showNotice('info', 'Don\'t forget to save your settings!');
        }, 2000);
    });

    // Show/hide advanced options
    $('.toggle-advanced').on('click', function(e) {
        e.preventDefault();
        $(this).next('.advanced-options').slideToggle();
        $(this).text(function(i, text) {
            return text === 'Show Advanced Options' ? 'Hide Advanced Options' : 'Show Advanced Options';
        });
    });

    // Form validation
    $('form').on('submit', function() {
        let isValid = true;
        
        // Validate number inputs
        $(this).find('input[type="number"]').each(function() {
            const $input = $(this);
            const min = parseInt($input.attr('min'));
            const max = parseInt($input.attr('max'));
            const value = parseInt($input.val());
            
            if (value < min || value > max) {
                $input.addClass('error');
                isValid = false;
            } else {
                $input.removeClass('error');
            }
        });

        if (!isValid) {
            showNotice('error', 'Please check your input values.');
            return false;
        }
    });

    /**
     * Show admin notice
     */
    function showNotice(type, message) {
        const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap h1').after($notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);

        // Add dismiss button functionality
        $notice.find('.notice-dismiss').on('click', function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        });
    }

    // Initialize tooltips
    $('[title]').tooltip();

    // Character counter for text areas
    $('textarea').each(function() {
        const $textarea = $(this);
        const maxLength = $textarea.attr('maxlength');
        
        if (maxLength) {
            const $counter = $('<div class="char-counter"></div>');
            $textarea.after($counter);
            
            const updateCounter = function() {
                const remaining = maxLength - $textarea.val().length;
                $counter.text(remaining + ' characters remaining');
                $counter.toggleClass('warning', remaining < 50);
            };
            
            $textarea.on('input', updateCounter);
            updateCounter();
        }
    });
});
