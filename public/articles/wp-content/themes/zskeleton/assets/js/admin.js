/**
 * ZSkeleton Admin JavaScript
 * 
 * General admin functionality for the ZSkeleton theme
 */

(function($) {
    'use strict';

    // Admin object
    const ZSkeletonAdmin = {
        
        // Initialize admin functionality
        init: function() {
            this.setupNotices();
            this.setupTooltips();
            this.setupConfirmations();
            this.setupAjaxForms();
            this.setupTabs();
            this.setupProgressBars();
        },

        // Setup dismissible notices
        setupNotices: function() {
            $(document).on('click', '.zskeleton-admin-notice .notice-dismiss', function() {
                $(this).closest('.zskeleton-admin-notice').fadeOut();
            });

            // Auto-dismiss success notices
            setTimeout(function() {
                $('.zskeleton-admin-notice.success').fadeOut();
            }, 5000);
        },

        // Setup tooltips
        setupTooltips: function() {
            $('.zskeleton-tooltip').hover(
                function() {
                    $(this).addClass('tooltip-visible');
                },
                function() {
                    $(this).removeClass('tooltip-visible');
                }
            );
        },

        // Setup confirmation dialogs
        setupConfirmations: function() {
            $('[data-confirm]').on('click', function(e) {
                const message = $(this).data('confirm');
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        // Setup AJAX forms
        setupAjaxForms: function() {
            $('.zskeleton-ajax-form').on('submit', function(e) {
                e.preventDefault();
                
                const $form = $(this);
                const $submitBtn = $form.find('[type="submit"]');
                const originalText = $submitBtn.val() || $submitBtn.text();
                
                // Show loading state
                $form.addClass('zskeleton-loading');
                $submitBtn.prop('disabled', true);
                if ($submitBtn.is('input')) {
                    $submitBtn.val('Processing...');
                } else {
                    $submitBtn.text('Processing...');
                }
                
                $.ajax({
                    url: $form.attr('action') || ajaxurl,
                    type: $form.attr('method') || 'POST',
                    data: $form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            ZSkeletonAdmin.showNotice('success', response.data.message || 'Action completed successfully!');
                            
                            // Reset form if specified
                            if ($form.data('reset-on-success')) {
                                $form[0].reset();
                            }
                            
                            // Redirect if specified
                            if (response.data.redirect) {
                                window.location.href = response.data.redirect;
                            }
                        } else {
                            ZSkeletonAdmin.showNotice('error', response.data || 'An error occurred.');
                        }
                    },
                    error: function() {
                        ZSkeletonAdmin.showNotice('error', 'Network error. Please try again.');
                    },
                    complete: function() {
                        // Remove loading state
                        $form.removeClass('zskeleton-loading');
                        $submitBtn.prop('disabled', false);
                        if ($submitBtn.is('input')) {
                            $submitBtn.val(originalText);
                        } else {
                            $submitBtn.text(originalText);
                        }
                    }
                });
            });
        },

        // Setup tabs
        setupTabs: function() {
            $('.zskeleton-tab').on('click', function(e) {
                e.preventDefault();
                
                const $tab = $(this);
                const target = $tab.attr('href') || $tab.data('target');
                
                // Update tab states
                $tab.siblings('.zskeleton-tab').removeClass('active');
                $tab.addClass('active');
                
                // Show/hide content
                $(target).siblings('.zskeleton-tab-content').hide();
                $(target).show();
                
                // Update URL hash if applicable
                if ($tab.attr('href') && $tab.attr('href').startsWith('#')) {
                    window.location.hash = $tab.attr('href');
                }
            });

            // Handle initial tab from URL hash
            if (window.location.hash) {
                const $targetTab = $(`.zskeleton-tab[href="${window.location.hash}"]`);
                if ($targetTab.length) {
                    $targetTab.trigger('click');
                }
            }
        },

        // Setup progress bars
        setupProgressBars: function() {
            $('.zskeleton-progress').each(function() {
                const $progress = $(this);
                const $bar = $progress.find('.zskeleton-progress-bar');
                const percentage = $bar.data('percentage') || 0;
                
                // Animate progress bar
                setTimeout(function() {
                    $bar.css('width', percentage + '%');
                }, 100);
            });
        },

        // Show admin notice
        showNotice: function(type, message) {
            const $notice = $(`
                <div class="zskeleton-admin-notice ${type} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);
            
            // Insert notice
            if ($('.zskeleton-admin-notices').length) {
                $('.zskeleton-admin-notices').prepend($notice);
            } else {
                $('.wrap h1').first().after($notice);
            }
            
            // Auto-dismiss success notices
            if (type === 'success') {
                setTimeout(function() {
                    $notice.fadeOut(function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        },

        // Confirm action
        confirm: function(message, callback) {
            if (confirm(message)) {
                if (typeof callback === 'function') {
                    callback();
                }
                return true;
            }
            return false;
        },

        // Show loading state
        showLoading: function($element) {
            $element.addClass('zskeleton-loading').prop('disabled', true);
        },

        // Hide loading state
        hideLoading: function($element) {
            $element.removeClass('zskeleton-loading').prop('disabled', false);
        },

        // Format number with commas
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },

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

        // Validate email
        validateEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        // Copy to clipboard
        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    ZSkeletonAdmin.showNotice('success', 'Copied to clipboard!');
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                ZSkeletonAdmin.showNotice('success', 'Copied to clipboard!');
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        ZSkeletonAdmin.init();
        
        // Members List functionality
        if (typeof zskeletonMembers !== 'undefined') {
            console.log('ZSkeleton Members: Script loaded', zskeletonMembers);
            
            // Activate membership button
            $(document).on('click', '.activate-membership', function(e) {
                e.preventDefault();
                console.log('Activate button clicked');
                
                const $btn = $(this);
                const userId = $btn.data('user-id');
                console.log('User ID:', userId);
                
                if (!confirm(zskeletonMembers.strings.confirm_activate)) {
                    return;
                }
                
                const originalText = $btn.text();
                $btn.text('Activating...').prop('disabled', true);
                
                $.ajax({
                    url: zskeletonMembers.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'zskeleton_activate_membership',
                        nonce: zskeletonMembers.nonce,
                        user_id: userId
                    },
                    success: function(response) {
                        if (response.success) {
                            ZSkeletonAdmin.showNotice('success', response.data.message);
                            // Reload page to show updated status
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            ZSkeletonAdmin.showNotice('error', response.data || zskeletonMembers.strings.error);
                            $btn.text(originalText).prop('disabled', false);
                        }
                    },
                    error: function() {
                        ZSkeletonAdmin.showNotice('error', zskeletonMembers.strings.error);
                        $btn.text(originalText).prop('disabled', false);
                    }
                });
            });
            
            // Export members button
            $('#export-members').on('click', function() {
                const $btn = $(this);
                const originalText = $btn.text();
                
                $btn.text('Exporting...').prop('disabled', true);
                
                $.ajax({
                    url: zskeletonMembers.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'zskeleton_export_members',
                        nonce: zskeletonMembers.nonce,
                        membership_type: $('select[name="membership_type"]').val(),
                        membership_status: $('select[name="membership_status"]').val(),
                        search: $('input[name="s"]').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            // Convert array to CSV
                            const csvContent = response.data.csv_data.map(row => 
                                row.map(cell => {
                                    // Escape quotes and wrap in quotes if contains comma
                                    const cellStr = String(cell || '');
                                    if (cellStr.includes(',') || cellStr.includes('"') || cellStr.includes('\n')) {
                                        return '"' + cellStr.replace(/"/g, '""') + '"';
                                    }
                                    return cellStr;
                                }).join(',')
                            ).join('\n');
                            
                            // Create download link
                            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                            const link = document.createElement('a');
                            const url = URL.createObjectURL(blob);
                            
                            link.setAttribute('href', url);
                            link.setAttribute('download', response.data.filename);
                            link.style.visibility = 'hidden';
                            
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            
                            ZSkeletonAdmin.showNotice('success', 'Members exported successfully!');
                        } else {
                            ZSkeletonAdmin.showNotice('error', response.data || 'Export failed');
                        }
                    },
                    error: function() {
                        ZSkeletonAdmin.showNotice('error', 'Network error during export');
                    },
                    complete: function() {
                        $btn.text(originalText).prop('disabled', false);
                    }
                });
            });

            // Import memberships (CSV upload).
            $('#zskeleton-import-members-btn').on('click', function() {
                const fileInput = document.getElementById('zskeleton-members-csv-file');
                const $status = $('#zskeleton-import-status');
                if (!fileInput || !fileInput.files.length) {
                    ZSkeletonAdmin.showNotice('error', zskeletonMembers.strings.import_pick_file);
                    return;
                }
                const $btn = $(this);
                const original = $btn.text();
                $btn.prop('disabled', true).text('…');
                $status.text('');

                const fd = new FormData();
                fd.append('action', 'zskeleton_import_members');
                fd.append('nonce', zskeletonMembers.import_nonce);
                fd.append('zskeleton_members_csv', fileInput.files[0]);
                fd.append('update_existing', $('#zskeleton-import-update-existing').is(':checked') ? '1' : '');
                fd.append('create_users', $('#zskeleton-import-create-users').is(':checked') ? '1' : '');

                $.ajax({
                    url: zskeletonMembers.ajax_url,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success && response.data && response.data.message) {
                            ZSkeletonAdmin.showNotice('success', response.data.message);
                            setTimeout(function() { location.reload(); }, 2000);
                        } else {
                            var err = zskeletonMembers.strings.error;
                            if (typeof response.data === 'string') {
                                err = response.data;
                            } else if (response.data && response.data.message) {
                                err = response.data.message;
                            }
                            ZSkeletonAdmin.showNotice('error', err);
                        }
                    },
                    error: function() {
                        ZSkeletonAdmin.showNotice('error', zskeletonMembers.strings.error);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text(original);
                    }
                });
            });
        }
    });

    // Make ZSkeletonAdmin globally available
    window.ZSkeletonAdmin = ZSkeletonAdmin;

})(jQuery);
