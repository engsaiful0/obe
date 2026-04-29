/**
 * Daily Deployment Plan Edit Form Handler
 * Handles form submission and validation for edit mode
 * Preserves pre-selected bus values
 */

$(document).ready(function() {
    'use strict';

    // Initialize toastr options if available
    if (typeof toastr !== 'undefined') {
        // Configure toastr for better visibility
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            preventDuplicates: false,
            onclick: null,
            showDuration: '300',
            hideDuration: '1000',
            timeOut: '5000',
            extendedTimeOut: '1000',
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };
    }

    // Form submission
    $('#deploymentPlanForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });

    // Fix dropdown width to match button width
    $(document).on('show.bs.dropdown', '.bus-multi-dropdown', function() {
        const $dropdown = $(this);
        const $button = $dropdown.find('.dropdown-toggle');
        const buttonWidth = $button.outerWidth();
        const $menu = $dropdown.find('.dropdown-menu');
        $menu.css({
            'width': buttonWidth + 'px',
            'min-width': buttonWidth + 'px',
            'max-width': buttonWidth + 'px'
        });
    });

    /**
     * Submit form via AJAX
     */
    function submitForm() {
        const form = $('#deploymentPlanForm');
        const submitBtn = $('#submitBtn');
        const submitSpinner = $('#submitSpinner');
        const submitText = $('#submitText');

        // Validate form
        if (!validateForm()) {
            return false;
        }

        // Show loading spinner
        submitBtn.prop('disabled', true);
        submitSpinner.removeClass('d-none');
        submitText.html('Updating...');

        // Prepare data for submission
        const formData = prepareFormData();

        // Debug: Log form data
        console.log('Form Data:', {
            deployment_date: $('#deployment_date').val(),
            trip_time_id: $('#trip_time_id').val(),
            bus_user_id: $('#bus_user_id').val(),
            deployment_type_id: $('#deployment_type_id').val(),
            trip_type: $('#trip_type').val(),
        });

        $.ajax({
            url: form.attr('action'),
            type: 'POST', // Use POST with _method spoofing for better compatibility
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                submitBtn.prop('disabled', false);
                submitSpinner.addClass('d-none');
                submitText.html('<i data-feather="save"></i> <span class="d-none d-sm-inline">Update Deployment Plan</span><span class="d-inline d-sm-none">Update</span>');

                // Debug: Log response
                console.log('Update response:', response);

                if (response && response.success) {
                    const successMessage = response.message || 'Deployment plan updated successfully.';
                    
                    // Show success toast message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(successMessage, 'Success', {
                            timeOut: 3000,
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-top-right'
                        });
                    } else {
                        // Fallback if toastr is not available
                        alert('Success: ' + successMessage);
                    }
                    
                    // Redirect after showing message
                    setTimeout(function() {
                        var baseUrl = window.location.origin;
                        var pathName = window.location.pathname;
                        var appIndex = pathName.indexOf('/app/');
                        if (appIndex !== -1) {
                            baseUrl += pathName.substring(0, appIndex);
                        }
                        window.location.href = baseUrl + '/app/deployment-plans/view-daily-deployment-plan';
                    }, 2000); // Increased delay to ensure toast is visible
                } else {
                    const errorMessage = (response && response.message) || 'An error occurred while updating.';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMessage, 'Error', {
                            timeOut: 5000,
                            closeButton: true,
                            progressBar: true
                        });
                    } else {
                        alert('Error: ' + errorMessage);
                    }
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false);
                submitSpinner.addClass('d-none');
                submitText.html('<i data-feather="save"></i> <span class="d-none d-sm-inline">Update Deployment Plan</span><span class="d-inline d-sm-none">Update</span>');

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors || {};
                    let errorMessage = 'Validation failed. Please check the form.';
                    
                    if (Object.keys(errors).length > 0) {
                        errorMessage = Object.values(errors).flat().join('<br>');
                    }
                    
                    showAlert('error', errorMessage);
                    displayValidationErrors(errors);
                } else {
                    let errorMessage = xhr.responseJSON?.message || 'An error occurred while updating the deployment plan.';
                    if (xhr.responseJSON?.error) {
                        errorMessage += '<br><small class="text-muted">Error: ' + xhr.responseJSON.error + '</small>';
                    }
                    showAlert('error', errorMessage);
                    console.error('Deployment plan update error:', xhr.responseJSON);
                }
            }
        });
    }

    /**
     * Prepare form data for submission
     */
    function prepareFormData() {
        const data = new FormData();
        
        // Add common fields - ensure values are not null/undefined
        const deploymentDate = $('#deployment_date').val();
        const tripTimeId = $('#trip_time_id').val();
        const busUserId = $('#bus_user_id').val();
        const deploymentTypeId = $('#deployment_type_id').val();
        const tripType = $('#trip_type').val();
        const remarks = $('#remarks').val() || '';
        
        if (deploymentDate) data.append('deployment_date', deploymentDate);
        if (tripTimeId) data.append('trip_time_id', tripTimeId);
        if (busUserId) data.append('bus_user_id', busUserId);
        if (deploymentTypeId) data.append('deployment_type_id', deploymentTypeId);
        if (tripType) data.append('trip_type', tripType);
        if (remarks) data.append('remarks', remarks);
        
        data.append('_token', $('meta[name="csrf-token"]').attr('content'));
        data.append('_method', 'PUT');

        // Prepare items array - allow multiple buses per stoppage and sub type (via checkboxes)
        const items = [];
        $('#deploymentTable tbody tr').each(function(index) {
            const row = $(this);
            const stoppageId = row.find('input[name*="[stoppage_id]"]').val();
            
            if (!stoppageId) return;

            const busAssignments = [];

            // Include all checked buses for this stoppage (across sub types)
            row.find('input[type="checkbox"][name*="[bus_assignments]"][name*="[bus_id]"]:checked').each(function() {
                const busId = $(this).val();
                if (!busId) {
                    return;
                }

                const nameAttr = $(this).attr('name');
                const match = nameAttr.match(/\[bus_assignments\]\[(\d+)\]/);
                if (!match) {
                    return;
                }

                const subTypeId = match[1];
                const subTypeIdInput = row.find(`input[name*="[bus_assignments][${subTypeId}][bus_sub_type_id]"]`);
                const busSubTypeId = subTypeIdInput.val() || subTypeId;

                busAssignments.push({
                    bus_sub_type_id: parseInt(busSubTypeId),
                    bus_id: parseInt(busId)
                });
            });

            // Only add item if at least one bus is selected
            if (busAssignments.length > 0) {
                items.push({
                    stoppage_id: parseInt(stoppageId),
                    bus_assignments: busAssignments
                });
            }
        });

        data.append('items', JSON.stringify(items));

        return data;
    }

    /**
     * Validate form
     */
    function validateForm() {
        let isValid = true;
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('.bus-validation-error').remove();
        $('.border-danger').removeClass('border-danger');

        // Validate common fields
        if (!$('#deployment_date').val()) {
            showFieldError('#deployment_date', 'Deployment date is required.');
            isValid = false;
        }

        if (!$('#trip_time_id').val()) {
            showFieldError('#trip_time_id', 'Trip time is required.');
            isValid = false;
        }

        if (!$('#bus_user_id').val()) {
            showFieldError('#bus_user_id', 'Bus user is required.');
            isValid = false;
        }

        if (!$('#deployment_type_id').val()) {
            showFieldError('#deployment_type_id', 'Deployment type is required.');
            isValid = false;
        }

        if (!$('#trip_type').val()) {
            showFieldError('#trip_type', 'Trip type is required.');
            isValid = false;
        }

        // Validate at least one stoppage row
        if ($('#deploymentTable tbody tr').length === 0) {
            showAlert('error', 'At least one stoppage row is required.');
            isValid = false;
        }

        return isValid;
    }

    /**
     * Show field error
     */
    function showFieldError(field, message) {
        const fieldElement = $(field);
        fieldElement.addClass('is-invalid');
        fieldElement.after(`<div class="invalid-feedback">${message}</div>`);
    }

    /**
     * Display validation errors
     */
    function displayValidationErrors(errors) {
        $.each(errors, function(field, messages) {
            const fieldElement = $(`[name="${field}"]`);
            if (fieldElement.length) {
                fieldElement.addClass('is-invalid');
                fieldElement.after(`<div class="invalid-feedback">${messages[0]}</div>`);
            }
        });
    }

    /**
     * Show alert message
     */
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'ti ti-check-circle' : 'ti ti-alert-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#alertContainer').html(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }

    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

