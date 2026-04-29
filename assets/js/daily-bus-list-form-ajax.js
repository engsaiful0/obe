/**
 * Daily Bus List Form AJAX System
 * Enhanced with spinner support and auto-loading of last saved data
 */

'use strict';

// Utility functions for spinner and loading states
const SpinnerUtils = {
    show: function(element, text = 'Loading...') {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', true);
        element.data('original-text', element.html());
        element.html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${text}`);
    },
    
    hide: function(element, originalText = null) {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', false);
        const text = originalText || element.data('original-text') || 'Save';
        element.html(text);
    }
};

// Enhanced AJAX utility with error handling
const AjaxUtils = {
    request: function(options) {
        const defaults = {
            type: 'GET',
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
            }
        } else if (xhr.status === 500) {
            message = 'Server error. Please contact support.';
        } else if (xhr.status === 404) {
            message = 'Resource not found.';
        }
        
        NotificationUtils.showAlert('error', message);
        
        if (options.onError) {
            options.onError(xhr, message);
        }
    },
    
    showFieldErrors: function(errors) {
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Show new errors
        Object.keys(errors).forEach(field => {
            const fieldElement = $(`[name="${field}"]`);
            fieldElement.addClass('is-invalid');
            fieldElement.siblings('.invalid-feedback').text(errors[field][0]);
        });
    }
};

// Notification utility
const NotificationUtils = {
    showAlert: function(type, message, duration = 5000) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'ti ti-check-circle' : 'ti ti-alert-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        $('#alertContainer').html(alertHtml);
        
        // Auto-hide after duration
        setTimeout(() => {
            $('.alert').fadeOut();
        }, duration);
    }
};

// Initialize the page
$(document).ready(function() {
    initializeForm();
    initializeEventHandlers();
    initializeDatePicker();
});

// Initialize form
function initializeForm() {
    const form = $('#dailyBusListForm');
    if (form.length) {
        // Clear validation errors on input
        form.find('input, select, textarea').on('input change', function() {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('');
        });
    }
}

// Initialize date picker
function initializeDatePicker() {
    $('#list_date').flatpickr({
        dateFormat: 'Y-m-d',
        allowInput: true,
        defaultDate: new Date()
    });
}

// Initialize event handlers
function initializeEventHandlers() {
    // Form submission
    $('#dailyBusListForm').on('submit', function(e) {
        e.preventDefault();
        handleFormSubmit();
    });
    
    // Bus change handler for auto-loading last saved data
    $('#bus_id').on('change', function() {
        const busId = $(this).val();
        const date = $('#list_date').val();
        
        if (busId && date) {
            loadLastSavedData(busId, date);
        }
    });
    
    // Date change handler for auto-loading last saved data
    $('#list_date').on('change', function() {
        const busId = $('#bus_id').val();
        const date = $(this).val();
        
        if (busId && date) {
            loadLastSavedData(busId, date);
        }
    });
}

// Handle form submission
function handleFormSubmit() {
    const form = $('#dailyBusListForm');
    const formData = new FormData(form[0]);
    const isEdit = form.data('id');
    const url = isEdit ? `/app/daily-bus-lists/${isEdit}` : '/app/daily-bus-lists';
    const method = isEdit ? 'PUT' : 'POST';
    
    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    AjaxUtils.request({
        url: url,
        type: method,
        data: formData,
        processData: false,
        contentType: false,
        showSpinner: true,
        spinnerElement: '#saveDailyBusListBtn',
        spinnerText: isEdit ? 'Updating...' : 'Saving...',
        onSuccess: function(response) {
            NotificationUtils.showAlert('success', response.message);
            
            if (!isEdit) {
                // Reset form for new entries
                form[0].reset();
                $('#list_date').flatpickr().setDate(new Date());
            }
            
            // Redirect to index after a delay
            setTimeout(() => {
                window.location.href = '/app/daily-bus-lists';
            }, 2000);
        }
    });
}

// Load last saved data for a bus on a specific date
function loadLastSavedData(busId, date) {
    AjaxUtils.request({
        url: '/app/daily-bus-lists/last-saved-data',
        data: { bus_id: busId, date: date },
        showSpinner: true,
        spinnerElement: '#saveDailyBusListBtn',
        spinnerText: 'Loading...',
        onSuccess: function(response) {
            if (response.data) {
                // Populate form with last saved data
                populateFormWithData(response.data);
                
                // Show notification
                NotificationUtils.showAlert('success', 'Last saved data loaded for this bus and date.');
            }
        },
        onError: function() {
            // Don't show error for this operation as it's optional
        }
    });
}

// Populate form with data
function populateFormWithData(data) {
    $('#start_stoppage_id').val(data.start_stoppage_id).trigger('change');
    $('#end_stoppage_id').val(data.end_stoppage_id).trigger('change');
    $('#start_time').val(data.start_time);
    $('#driver_id').val(data.driver_id).trigger('change');
    $('#assistant_id').val(data.assistant_id).trigger('change');
    $('#remarks').val(data.remarks);
}

// Check if bus has data for the selected date
function checkBusData(busId, date) {
    AjaxUtils.request({
        url: '/app/daily-bus-lists/check-bus-data',
        data: { bus_id: busId, date: date },
        onSuccess: function(response) {
            if (response.has_data) {
                NotificationUtils.showAlert('error', 'This bus already has data for the selected date. Please update the existing record instead.');
            }
        }
    });
}

// Global functions for external access
window.loadLastSavedData = loadLastSavedData;
window.checkBusData = checkBusData;
