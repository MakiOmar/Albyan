/**
 * ZSkeleton Admin Settings JavaScript
 * 
 * Handles admin panel functionality and settings management
 */

(function($) {
    'use strict';

    // Admin object
    const ZSkeletonAdmin = {
        
        // Initialize admin functionality
        init: function() {
            this.setupTabNavigation();
            this.setupColorPickers();
            this.setupImageUploaders();
            this.setupFormValidation();
            this.setupBulkActions();
            this.setupLivePreview();
            this.setupImportExport();
        },

        // Setup tab navigation
        setupTabNavigation: function() {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                const $this = $(this);
                const targetTab = $this.attr('href');
                
                // Update tab active states
                $('.nav-tab').removeClass('nav-tab-active');
                $this.addClass('nav-tab-active');
                
                // Show/hide tab content
                $('.tab-content').removeClass('active');
                $(targetTab).addClass('active');
                
                // Update URL hash
                window.location.hash = targetTab;
            });

            // Handle initial tab from URL hash (legacy #general → Branding tab).
            let initialHash = window.location.hash || '';
            if (initialHash === '#general') {
                initialHash = '#branding';
                if (window.history && typeof window.history.replaceState === 'function') {
                    window.history.replaceState(null, '', '#branding');
                }
            }
            if (initialHash) {
                const $targetTab = $(`.nav-tab[href="${initialHash}"]`);
                if ($targetTab.length) {
                    $targetTab.trigger('click');
                }
            }
        },

        // Setup color pickers
        setupColorPickers: function() {
            if (typeof $.fn.wpColorPicker !== 'undefined') {
                $('.color-picker').wpColorPicker({
                    change: function(event, ui) {
                        const color = ui.color.toString();
                        ZSkeletonAdmin.updateColorPreview($(this), color);
                    }
                });
            }
        },

        // Update color preview
        updateColorPreview: function($input, color) {
            const previewId = $input.data('preview');
            if (previewId) {
                $(`#${previewId}`).css('background-color', color);
            }
        },

        // Setup image uploaders
        setupImageUploaders: function() {
            $('.image-upload-button').on('click', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const $input = $button.siblings('.image-field-input');
                const $preview = $button.siblings('.image-preview');
                
                // WordPress media uploader
                if (typeof wp !== 'undefined' && wp.media) {
                    const mediaUploader = wp.media({
                        title: 'Select Image',
                        button: {
                            text: 'Use this image'
                        },
                        multiple: false
                    });

                    mediaUploader.on('select', function() {
                        const attachment = mediaUploader.state().get('selection').first().toJSON();
                        
                        $input.val(attachment.id);
                        $preview.html(`<img src="${attachment.url}" alt="" style="max-width: 200px; max-height: 100px; display: block; margin-bottom: 10px;" />`);
                        
                        $button.text('Change Image');
                        
                        // Show remove button or add it if it doesn't exist
                        let $removeBtn = $button.siblings('.image-remove-button');
                        if ($removeBtn.length === 0) {
                            $removeBtn = $(`<button type="button" class="button image-remove-button" data-field="${$button.data('field')}">Remove Image</button>`);
                            $button.after(' ').after($removeBtn);
                        }
                        $removeBtn.show();
                    });

                    mediaUploader.open();
                }
            });

            // Remove image functionality
            $(document).on('click', '.image-remove-button', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const $input = $button.siblings('.image-field-input');
                const $preview = $button.siblings('.image-preview');
                const $uploadBtn = $button.siblings('.image-upload-button');
                
                $input.val('');
                $preview.empty();
                $uploadBtn.text('Upload Image');
                $button.hide();
            });
        },

        // Setup form validation
        setupFormValidation: function() {
            // Real-time validation for settings forms
            $('.zskeleton-settings-form input, .zskeleton-settings-form textarea').on('blur', function() {
                ZSkeletonAdmin.validateField($(this));
            });

            // Form submission: sync color pickers only. Do not block save — client-side validation of every
            // input caused silent failures ($.val() can be an array for multi-select, or break on edge cases)
            // so the POST never reached admin-post.php and nothing was saved.
            $('.zskeleton-settings-form').on('submit', function() {
                const $form = $(this);
                // Iris may leave rgb()/rgba() in the input; PHP sanitize_hex_color only accepts #hex (theme-colors.php also normalizes rgb).
                $form.find('.wp-color-picker').each(function() {
                    const $input = $(this);
                    if (typeof $input.iris !== 'function') {
                        return;
                    }
                    try {
                        const raw = $input.iris('option', 'color');
                        if (raw === null || typeof raw === 'undefined' || raw === '') {
                            return;
                        }
                        if (typeof raw === 'string' && raw.indexOf('#') === 0) {
                            $input.val(raw);
                            return;
                        }
                        if (typeof raw === 'object' && raw !== null && typeof raw.toString === 'function') {
                            let hex = raw.toString('hex');
                            if (hex && hex.indexOf('#') !== 0) {
                                hex = '#' + hex;
                            }
                            if (hex) {
                                $input.val(hex);
                            }
                        }
                    } catch (ignore) {
                        /* Older Iris: server-side rgb() parsing still applies. */
                    }
                });
            });
        },

        // Validate individual field
        validateField: function($field) {
            const fieldType = $field.attr('type') || $field.prop('tagName').toLowerCase();
            const rawVal = $field.val();
            const value = Array.isArray(rawVal)
                ? rawVal.join(',')
                : (rawVal === null || typeof rawVal === 'undefined' ? '' : String(rawVal)).trim();
            const fieldName = $field.attr('name');
            let isValid = true;
            let errorMessage = '';

            // Clear previous errors
            $field.removeClass('is-invalid');
            $field.siblings('.field-error').remove();

            // Required field validation
            if ($field.prop('required') && !value) {
                isValid = false;
                errorMessage = 'This field is required.';
            }

            // Email validation
            if (fieldType === 'email' && value && !ZSkeletonAdmin.isValidEmail(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address.';
            }

            // URL validation
            if (fieldType === 'url' && value && !ZSkeletonAdmin.isValidUrl(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid URL.';
            }

            // Number validation
            if (fieldType === 'number' && value) {
                const min = parseFloat($field.attr('min'));
                const max = parseFloat($field.attr('max'));
                const numValue = parseFloat(value);
                
                if (isNaN(numValue)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid number.';
                } else if (!isNaN(min) && numValue < min) {
                    isValid = false;
                    errorMessage = `Value must be at least ${min}.`;
                } else if (!isNaN(max) && numValue > max) {
                    isValid = false;
                    errorMessage = `Value must be no more than ${max}.`;
                }
            }

            // Show error if invalid
            if (!isValid) {
                $field.addClass('is-invalid');
                $field.after(`<div class="field-error">${errorMessage}</div>`);
            }

            return isValid;
        },

        // Setup bulk actions
        setupBulkActions: function() {
            // Bulk action handlers
            $('.bulk-action-btn').on('click', function(e) {
                e.preventDefault();
                
                const action = $(this).data('action');
                const selectedItems = $('.bulk-select:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedItems.length === 0) {
                    alert('Please select items to perform bulk action.');
                    return;
                }

                if (confirm(`Perform "${action}" on ${selectedItems.length} selected items?`)) {
                    ZSkeletonAdmin.performBulkAction(action, selectedItems);
                }
            });

            // Select all checkbox
            $('.select-all').on('change', function() {
                const isChecked = $(this).prop('checked');
                $('.bulk-select').prop('checked', isChecked);
                ZSkeletonAdmin.updateBulkActionButtons();
            });

            // Individual checkboxes
            $('.bulk-select').on('change', function() {
                ZSkeletonAdmin.updateBulkActionButtons();
            });
        },

        // Update bulk action button states
        updateBulkActionButtons: function() {
            const selectedCount = $('.bulk-select:checked').length;
            const $bulkActions = $('.bulk-actions');
            
            if (selectedCount > 0) {
                $bulkActions.removeClass('disabled');
                $('.selected-count').text(`${selectedCount} selected`);
            } else {
                $bulkActions.addClass('disabled');
                $('.selected-count').text('');
            }
        },

        // Perform bulk action
        performBulkAction: function(action, items) {
            const $loadingIndicator = $('.bulk-loading');
            $loadingIndicator.show();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'zskeleton_bulk_action',
                    bulk_action: action,
                    items: items,
                    nonce: zskeletonAdminAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        ZSkeletonAdmin.showNotice(response.data, 'success');
                        
                        // Refresh the page or update the table
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        ZSkeletonAdmin.showNotice(response.data || 'Bulk action failed.', 'error');
                    }
                },
                error: function() {
                    ZSkeletonAdmin.showNotice('An error occurred during bulk action.', 'error');
                },
                complete: function() {
                    $loadingIndicator.hide();
                }
            });
        },

        // Setup live preview
        setupLivePreview: function() {
            // Live preview for theme settings
            $('.live-preview-field').on('input change', function() {
                const $field = $(this);
                const previewSelector = $field.data('preview-selector');
                const previewProperty = $field.data('preview-property');
                const value = $field.val();
                
                if (previewSelector && previewProperty) {
                    ZSkeletonAdmin.updateLivePreview(previewSelector, previewProperty, value);
                }
            });

            // Preview modal
            $('.preview-changes').on('click', function(e) {
                e.preventDefault();
                ZSkeletonAdmin.openPreviewModal();
            });
        },

        // Update live preview
        updateLivePreview: function(selector, property, value) {
            const $previewFrame = $('#preview-frame');
            
            if ($previewFrame.length) {
                const frameDoc = $previewFrame[0].contentDocument || $previewFrame[0].contentWindow.document;
                $(frameDoc).find(selector).css(property, value);
            }
        },

        // Open preview modal
        openPreviewModal: function() {
            const modalHtml = `
                <div class="preview-modal modal">
                    <div class="modal-overlay"></div>
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Theme Preview</h3>
                            <button class="modal-close">&times;</button>
                        </div>
                        <div class="modal-body">
                            <iframe id="preview-frame" src="${window.location.origin}" width="100%" height="500"></iframe>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
            $('.preview-modal').addClass('active');
        },

        // Setup import/export functionality
        setupImportExport: function() {
            // Export settings
            $('.export-settings').on('click', function(e) {
                e.preventDefault();
                ZSkeletonAdmin.exportSettings();
            });

            // Import settings
            $('.import-settings').on('click', function(e) {
                e.preventDefault();
                $('#import-file').click();
            });

            // Handle file selection
            $('#import-file').on('change', function() {
                const file = this.files[0];
                if (file) {
                    ZSkeletonAdmin.importSettings(file);
                }
            });

            // Reset to defaults
            $('.reset-defaults').on('click', function(e) {
                e.preventDefault();
                
                if (confirm('Reset all settings to default values? This cannot be undone.')) {
                    ZSkeletonAdmin.resetToDefaults();
                }
            });
        },

        // Export settings
        exportSettings: function() {
            const $loadingIndicator = $('.export-loading');
            $loadingIndicator.show();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'zskeleton_export_settings',
                    nonce: zskeletonAdminAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Create download link
                        const blob = new Blob([JSON.stringify(response.data, null, 2)], {
                            type: 'application/json'
                        });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `zskeleton-settings-${new Date().toISOString().split('T')[0]}.json`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                        
                        ZSkeletonAdmin.showNotice('Settings exported successfully.', 'success');
                    } else {
                        ZSkeletonAdmin.showNotice('Export failed.', 'error');
                    }
                },
                complete: function() {
                    $loadingIndicator.hide();
                }
            });
        },

        // Import settings
        importSettings: function(file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                try {
                    const settings = JSON.parse(e.target.result);
                    ZSkeletonAdmin.processImportedSettings(settings);
                } catch (error) {
                    ZSkeletonAdmin.showNotice('Invalid settings file format.', 'error');
                }
            };
            
            reader.readAsText(file);
        },

        // Process imported settings
        processImportedSettings: function(settings) {
            const $loadingIndicator = $('.import-loading');
            $loadingIndicator.show();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'zskeleton_import_settings',
                    settings: JSON.stringify(settings),
                    nonce: zskeletonAdminAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        ZSkeletonAdmin.showNotice('Settings imported successfully.', 'success');
                        
                        // Reload page to reflect changes
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        ZSkeletonAdmin.showNotice('Import failed: ' + response.data, 'error');
                    }
                },
                complete: function() {
                    $loadingIndicator.hide();
                }
            });
        },

        // Reset to defaults
        resetToDefaults: function() {
            const $loadingIndicator = $('.reset-loading');
            $loadingIndicator.show();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'zskeleton_reset_settings',
                    nonce: zskeletonAdminAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        ZSkeletonAdmin.showNotice('Settings reset to defaults.', 'success');
                        
                        // Reload page to reflect changes
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        ZSkeletonAdmin.showNotice('Reset failed.', 'error');
                    }
                },
                complete: function() {
                    $loadingIndicator.hide();
                }
            });
        },

        // Show admin notice
        showNotice: function(message, type = 'info') {
            const $notice = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);

            $('.wrap > h1').after($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $notice.fadeOut(() => $notice.remove());
            }, 5000);

            // Manual dismiss
            $notice.find('.notice-dismiss').on('click', () => {
                $notice.fadeOut(() => $notice.remove());
            });
        },

        // Utility functions
        isValidEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        isValidUrl: function(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        },

        // Chart rendering for statistics
        renderChart: function(canvasId, chartData, chartType = 'line') {
            const canvas = document.getElementById(canvasId);
            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            const ctx = canvas.getContext('2d');
            
            new Chart(ctx, {
                type: chartType,
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        },

        // Real-time statistics updates
        updateStatistics: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'zskeleton_get_statistics',
                    nonce: zskeletonAdminAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const stats = response.data;
                        
                        // Update stat displays
                        $('.stat-total-members').text(stats.total_members);
                        $('.stat-individual-members').text(stats.individual_members);
                        $('.stat-organizational-members').text(stats.organizational_members);
                        $('.stat-revenue').text('$' + stats.revenue.toLocaleString());
                        
                        // Update charts if available
                        if (stats.chartData) {
                            ZSkeletonAdmin.renderChart('membership-chart', stats.chartData.membership);
                            ZSkeletonAdmin.renderChart('revenue-chart', stats.chartData.revenue);
                        }
                    }
                }
            });
        }
    };

    // Initialize admin functionality when document is ready
    $(document).ready(function() {
        ZSkeletonAdmin.init();
        
        // Update statistics every 5 minutes
        setInterval(ZSkeletonAdmin.updateStatistics, 5 * 60 * 1000);
        
        // Initial statistics load
        ZSkeletonAdmin.updateStatistics();
    });

    // Handle modal close events
    $(document).on('click', '.modal-close, .modal-overlay', function() {
        $('.modal.active').removeClass('active');
    });

    // Handle keyboard events
    $(document).on('keydown', function(e) {
        // Close modal on escape
        if (e.key === 'Escape') {
            $('.modal.active').removeClass('active');
        }
        
        // Save settings on Ctrl+S — use native button click (jQuery .trigger('submit') does not POST the form).
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            const $btn = $('.zskeleton-settings-form').find('input[type="submit"], button[type="submit"]').first();
            if ($btn.length) {
                $btn[0].click();
            }
        }
    });

    // Make admin object globally available
    window.ZSkeletonAdmin = ZSkeletonAdmin;

})(jQuery);
