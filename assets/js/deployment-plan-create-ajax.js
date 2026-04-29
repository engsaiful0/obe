/**
 * Daily Deployment Plan Create Form AJAX System
 * Enhanced with spinner support
 */

'use strict';

// Utility functions for spinner and loading states
const SpinnerUtils = {
    show: function(element, text = 'Saving...') {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', true);
        element.data('original-text', element.html());
        const spinnerHtml = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${text}`;
        element.html(spinnerHtml);
    },
    
    hide: function(element, originalText = null) {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', false);
        const text = originalText || element.data('original-text') || '<i data-feather="save"></i> <span class="d-none d-sm-inline">Save Deployment Plan</span><span class="d-inline d-sm-none">Save</span>';
        element.html(text);
        // Re-initialize feather icons
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }
};

// Enhanced AJAX utility with error handling
const AjaxUtils = {
    request: function(options) {
        const defaults = {
            type: 'POST',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                if (options.showSpinner && options.spinnerElement) {
                    SpinnerUtils.show(options.spinnerElement, options.spinnerText);
                }
            },
            complete: function() {
                if (options.showSpinner && options.spinnerElement) {
                    SpinnerUtils.hide(options.spinnerElement);
                }
            },
            success: function(response) {
                if (options.onSuccess) {
                    options.onSuccess(response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                AjaxUtils.handleError(xhr, options);
            }
        };
        
        return $.ajax($.extend(defaults, options));
    },
    
    handleError: function(xhr, options = {}) {
        let message = 'An error occurred. Please try again.';
        
        if (xhr.status === 422) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                message = Object.values(errors).flat().join('<br>');
                // Show field-specific errors
                AjaxUtils.showFieldErrors(errors);
            } else if (xhr.responseJSON?.message) {
                message = xhr.responseJSON.message;
            }
        } else if (xhr.status === 500) {
            message = 'Server error. Please contact support.';
        } else if (xhr.status === 404) {
            message = 'Resource not found.';
        } else if (xhr.responseJSON?.message) {
            message = xhr.responseJSON.message;
        }
        
        NotificationUtils.showAlert('error', message);
        
        if (options.onError) {
            options.onError(xhr, message);
        }
    },
    
    showFieldErrors: function(errors) {
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Show new errors
        Object.keys(errors).forEach(field => {
            const fieldElement = $(`[name="${field}"]`);
            fieldElement.addClass('is-invalid');
            const errorMessage = errors[field][0];
            fieldElement.after(`<div class="invalid-feedback">${errorMessage}</div>`);
        });
    }
};

// Notification utility
const NotificationUtils = {
    showAlert: function(type, message, duration = 5000) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        const icon = type === 'success' ? 'ti ti-check-circle' : 
                    type === 'error' ? 'ti ti-alert-circle' : 
                    type === 'warning' ? 'ti ti-alert-triangle' : 'ti ti-info-circle';
        
        // Create alert container if it doesn't exist
        let alertContainer = $('#alertContainer');
        if (alertContainer.length === 0) {
            alertContainer = $('<div id="alertContainer"></div>');
            $('#deploymentPlanForm').before(alertContainer);
        }
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        alertContainer.html(alertHtml);
        
        // Auto-hide after duration
        if (type === 'success' || type === 'info') {
            setTimeout(() => {
                $('.alert').fadeOut();
            }, duration);
        }
    }
};

// Initialize the page
$(document).ready(function() {
    initializeForm();
    initializeSelect2();
    
    // Re-initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

// Initialize form
function initializeForm() {
    const form = $('#deploymentPlanForm');
    if (form.length) {
        // Clear validation errors on input
        form.find('input, select, textarea').on('input change', function() {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        });
        
        // Handle form submission
        form.on('submit', function(e) {
            e.preventDefault();
            handleFormSubmit();
        });
    }
}

// Initialize Select2
function initializeSelect2() {
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }
}

// Handle form submission
function handleFormSubmit() {
    const form = $('#deploymentPlanForm');
    const submitBtn = $('#submitBtn');
    const formUrl = form.attr('action');
    
    // Check if at least one bus is selected
    let hasBusSelected = false;
    form.find('select[name*="[bus_id]"]').each(function() {
        if ($(this).val() && $(this).val() !== '') {
            hasBusSelected = true;
            return false; // break loop
        }
    });
    
    if (!hasBusSelected) {
        NotificationUtils.showAlert('error', 'Please select at least one bus for deployment.');
        return;
    }
    
    // Filter out items without bus_id before submission
    const filteredData = filterFormData(form);
    
    AjaxUtils.request({
        url: formUrl,
        type: 'POST',
        data: filteredData,
        processData: false,
        contentType: false,
        showSpinner: true,
        spinnerElement: submitBtn,
        spinnerText: 'Saving...',
        onSuccess: function(response) {
            if (response.success) {
                toastr.success(response.message || 'Daily deployment plan created successfully.');
                
                // Redirect after a short delay
                setTimeout(function() {
                    const redirectUrl = $('#deploymentPlanForm').data('redirect-url') || '/app/deployment-plans/view-daily-deployment-plan';
                    window.location.href = redirectUrl;
                }, 1500);
            } else {
                toastr.error(response.message || 'Failed to create deployment plan.');
            }
        },
        onError: function(xhr, message) {
            // Error is already handled in handleError
        }
    });
}

// Filter form data to only include items with bus_id
function filterFormData(form) {
    const formData = new FormData();
    
    // Add all non-item fields
    form.find('input, select, textarea').not('[name^="items"]').each(function() {
        const $field = $(this);
        const name = $field.attr('name');
        const value = $field.val();
        
        if (name && name !== '_token') {
            formData.append(name, value);
        }
    });
    
    // Add CSRF token
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
    // Collect items data
    const itemsData = {};
    
    // Get all stoppage rows
    form.find('tbody tr').each(function() {
        const $row = $(this);
        const stoppageInput = $row.find('input[name*="[stoppage_id]"]');
        
        if (stoppageInput.length) {
            const stoppageId = stoppageInput.val();
            const nameMatch = stoppageInput.attr('name').match(/items\[(\d+)\]/);
            
            if (nameMatch && stoppageId) {
                const originalIndex = nameMatch[1];
                
                if (!itemsData[originalIndex]) {
                    itemsData[originalIndex] = {
                        stoppage_id: stoppageId,
                        bus_assignments: {}
                    };
                }
                
                // Get bus assignments for this row
                $row.find('select[name*="[bus_id]"]').each(function() {
                    const $busSelect = $(this);
                    const busId = $busSelect.val();
                    const busNameMatch = $busSelect.attr('name').match(/items\[(\d+)\]\[bus_assignments\]\[(\d+)\]/);
                    
                    if (busNameMatch && busId && busId !== '') {
                        const busSubTypeId = busNameMatch[2];
                        const busSubTypeInput = $row.find(`input[name*="[bus_assignments][${busSubTypeId}][bus_sub_type_id]"]`);
                        
                        itemsData[originalIndex].bus_assignments[busSubTypeId] = {
                            bus_sub_type_id: busSubTypeInput.val() || busSubTypeId,
                            bus_id: busId
                        };
                    }
                });
            }
        }
    });
    
    // Filter items that have at least one bus_id and add to FormData
    let newIndex = 0;
    Object.keys(itemsData).forEach(function(oldIndex) {
        const item = itemsData[oldIndex];
        const hasBusId = Object.keys(item.bus_assignments).length > 0;
        
        if (hasBusId && item.stoppage_id) {
            formData.append(`items[${newIndex}][stoppage_id]`, item.stoppage_id);
            
            Object.keys(item.bus_assignments).forEach(function(subTypeId) {
                const assignment = item.bus_assignments[subTypeId];
                formData.append(`items[${newIndex}][bus_assignments][${subTypeId}][bus_sub_type_id]`, assignment.bus_sub_type_id);
                formData.append(`items[${newIndex}][bus_assignments][${subTypeId}][bus_id]`, assignment.bus_id);
            });
            
            newIndex++;
        }
    });
    
    return formData;
}

