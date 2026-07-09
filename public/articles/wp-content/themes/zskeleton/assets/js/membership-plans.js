/**
 * ZSkeleton Membership Plans Admin JavaScript
 */
/* global zskeletonPlans, zskeletonPlansData */

// Fires without jQuery: if this never appears, the file is not loading or has a parse error above this line.
console.log('[ZS membership-plans.js] parsed');

// Capture-phase listener: logs even if jQuery.ready throws; handles clicks on inner nodes of the button.
document.addEventListener(
    'click',
    function (e) {
        var el = e.target;
        if (!el || !el.closest) {
            return;
        }
        var btn = el.closest('#zskeleton-import-plans-btn');
        if (!btn) {
            return;
        }
        console.log('[ZS plans import] native capture: Import button clicked');
    },
    true
);

jQuery(document).ready(function ($) {
    // Import first so a failure elsewhere cannot block binding.
    $(document).on('click', '#zskeleton-import-plans-btn', function (e) {
        var dbg = function () {
            console.log.apply(console, ['[ZS plans import]'].concat([].slice.call(arguments)));
        };
        dbg('jQuery delegated click', e.target);

        if (typeof zskeletonPlans === 'undefined' || !zskeletonPlans.import_nonce) {
            console.error('[ZS plans import] zskeletonPlans missing or import_nonce empty', typeof zskeletonPlans);
            alert(
                typeof zskeletonPlans !== 'undefined' && zskeletonPlans.strings && zskeletonPlans.strings.script_not_loaded
                    ? zskeletonPlans.strings.script_not_loaded
                    : 'Plans script not loaded. Refresh the page.'
            );
            return;
        }

        var fileInput = document.getElementById('zskeleton-plans-json-file');
        if (!fileInput || !fileInput.files.length) {
            dbg('no_file_chosen — pick a JSON file first');
            alert(zskeletonPlans.strings.import_pick_file);
            return;
        }
        var mode = $('#zskeleton-plans-import-mode').val();
        dbg('file:', fileInput.files[0].name, 'size:', fileInput.files[0].size, 'mode:', mode);

        if (mode === 'replace' && !confirm(zskeletonPlans.strings.import_confirm_replace)) {
            dbg('replace_cancelled_by_user');
            return;
        }

        var fd = new FormData();
        fd.append('action', 'zskeleton_import_membership_plans');
        fd.append('nonce', zskeletonPlans.import_nonce);
        fd.append('zskeleton_plans_json', fileInput.files[0]);
        fd.append('import_mode', mode);

        dbg('sending POST to', zskeletonPlans.ajax_url, 'action=zskeleton_import_membership_plans');

        var $btn = $(this);
        var orig = $btn.text();
        $btn.prop('disabled', true).text('…');

        $.ajax({
            url: zskeletonPlans.ajax_url,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                console.log('[ZS plans import] response', response);
                if (response && response.success && response.data && response.data.message) {
                    var msg = response.data.message;
                    if (response.data.errors && response.data.errors.length) {
                        msg += '\n\n' + response.data.errors.join('\n');
                    }
                    alert(msg);
                    location.reload();
                } else {
                    var err =
                        response && typeof response.data === 'string'
                            ? response.data
                            : response && response.data && response.data.message
                              ? response.data.message
                              : zskeletonPlans.strings.error;
                    console.warn('[ZS plans import] not success', response);
                    alert(err);
                }
            },
            error: function (xhr, status, errThrown) {
                console.error('[ZS plans import] ajax error', status, errThrown, xhr.status, xhr.responseText);
                var detail = '';
                try {
                    if (xhr.responseJSON && xhr.responseJSON.data) {
                        detail = typeof xhr.responseJSON.data === 'string' ? xhr.responseJSON.data : '';
                    } else if (xhr.responseText) {
                        detail = xhr.responseText.substring(0, 200);
                    }
                } catch (ignore) {
                    /* ignore */
                }
                alert(zskeletonPlans.strings.error + (detail ? '\n\n' + detail : ''));
            },
            complete: function () {
                dbg('ajax complete');
                $btn.prop('disabled', false).text(orig);
            }
        });
    });

    var currentPlan = null;

    try {
    // Add new plan
    $('#add-new-plan').on('click', function() {
        currentPlan = null;
        resetForm();
        $('#modal-title').text('Add New Membership Plan');
        $('#plan-editor-modal').show();
    });
    
    // Edit plan
    $(document).on('click', '.edit-plan', function() {
        const planCard = $(this).closest('.plan-card');
        const planId = planCard.data('plan-id');
        loadPlanData(planId);
        $('#modal-title').text('Edit Membership Plan');
        $('#plan-editor-modal').show();
    });
    
    // Delete plan
    $(document).on('click', '.delete-plan', function() {
        if (!confirm(zskeletonPlans.strings.confirm_delete)) {
            return;
        }
        
        const planCard = $(this).closest('.plan-card');
        const planId = planCard.data('plan-id');
        
        $.ajax({
            url: zskeletonPlans.ajax_url,
            type: 'POST',
            data: {
                action: 'zskeleton_delete_plan',
                plan_id: planId,
                nonce: zskeletonPlans.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data || (zskeletonPlans.strings && zskeletonPlans.strings.error_deleting_plan) || '');
                }
            },
            error: function() {
                alert((zskeletonPlans.strings && zskeletonPlans.strings.error_deleting_plan) || '');
            }
        });
    });
    
    // Close modal
    $('.modal-close').on('click', function() {
        $('#plan-editor-modal').hide();
    });
    
    // Close modal on outside click
    $('#plan-editor-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).hide();
        }
    });
    
    // Add feature
    $('#add-feature').on('click', function() {
        addFeatureInput();
    });
    
    // Remove feature
    $(document).on('click', '.remove-feature', function() {
        $(this).closest('.feature-input').remove();
    });
    
    // Save plan
    $('#plan-editor-form').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.text(zskeletonPlans.strings.saving).prop('disabled', true);
        
        // Collect features
        const features = [];
        $('.feature-input input').each(function() {
            const value = $(this).val().trim();
            if (value) {
                features.push(value);
            }
        });
        
        const formData = {
            action: 'zskeleton_save_plan',
            nonce: zskeletonPlans.nonce,
            plan_id: $('#plan_id').val(),
            plan_name: $('#plan_name').val(),
            plan_price: $('#plan_price').val(),
            plan_currency: $('#plan_currency').val(),
            plan_type: $('#plan_type').val(),
            plan_description: $('#plan_description').val(),
            plan_button_text: $('#plan_button_text').val(),
            plan_external_url: $('#plan_external_url').val(),
            plan_period_value: $('#plan_period_value').val(),
            plan_period_unit: $('#plan_period_unit').val(),
            plan_popular: $('#plan_popular').is(':checked') ? 1 : 0,
            plan_active: $('#plan_active').is(':checked') ? 1 : 0,
            plan_features: features
        };
        
        $.ajax({
            url: zskeletonPlans.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
                submitBtn.text(originalText).prop('disabled', false);
                
                if (response && response.success === true) {
                    $('#plan-editor-modal').hide();
                    location.reload();
                } else {
                    alert(response && response.data ? response.data : zskeletonPlans.strings.error);
                }
            },
            error: function(xhr, status, error) {
                submitBtn.text(originalText).prop('disabled', false);
                console.error('AJAX Error:', status, error);
                console.error('Response Text:', xhr.responseText);
                alert(zskeletonPlans.strings.error + '\n\nDetails: ' + error + '\n\nCheck console for more info.');
            }
        });
        
        return false; // Prevent any form submission
    });
    
    // Functions
    function resetForm() {
        $('#plan-editor-form')[0].reset();
        $('#features-container').empty();
        addFeatureInput(); // Add one empty feature input
    }
    
    function addFeatureInput(value) {
        if (typeof value === 'undefined') {
            value = '';
        }
        var featureHtml =
            '<div class="feature-input">' +
            '<input type="text" placeholder="Enter feature description" value="' +
            String(value).replace(/"/g, '&quot;') +
            '">' +
            '<button type="button" class="remove-feature">Remove</button>' +
            '</div>';
        $('#features-container').append(featureHtml);
    }
    
    function loadPlanData(planId) {
        // Get plan data from global variable passed from PHP
        if (typeof zskeletonPlansData !== 'undefined' && zskeletonPlansData[planId]) {
            const plan = zskeletonPlansData[planId];
            
            // Set form values from plan object
            $('#plan_id').val(plan.id || planId);
            $('#plan_name').val(plan.name || '');
            $('#plan_price').val(plan.price || '');
            $('#plan_currency').val(plan.currency || 'USD');
            $('#plan_type').val(plan.type || 'individual');
            $('#plan_description').val(plan.description || '');
            $('#plan_button_text').val(plan.button_text || '');
            $('#plan_external_url').val(plan.external_url || '');
            $('#plan_period_value').val(plan.period_value || '');
            $('#plan_period_unit').val(plan.period_unit || 'months');
            $('#plan_popular').prop('checked', plan.popular || false);
            $('#plan_active').prop('checked', plan.active || false);
            
            // Populate features
            $('#features-container').empty();
            if (plan.features && plan.features.length > 0) {
                plan.features.forEach(function(feature) {
                    addFeatureInput(feature);
                });
            } else {
                addFeatureInput();
            }
            
            // Add one empty feature input for new features
            addFeatureInput();
        } else {
            console.error('Plan data not found for ID:', planId);
        }
        
        currentPlan = planId;
    }
    } catch (err) {
        console.error('[ZS membership-plans.js] ready() error (import handler already bound):', err);
    }
});
