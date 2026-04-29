/**
 * Income Management with AJAX, Filters, and Pagination
 */

'use strict';

let isFiltering = false;

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
        const text = originalText || element.data('original-text');
        if (text) {
            element.html(text);
        }
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
                AjaxUtils.showFieldErrors(errors);
            }
        } else if (xhr.status === 500) {
            message = 'Server error. Please contact support.';
        } else if (xhr.status === 404) {
            message = 'Resource not found.';
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }
        
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert('Error: ' + message);
        }
        
        if (options.onError) {
            options.onError(xhr, message);
        }
    },
    
    showFieldErrors: function(errors) {
        $('.form-control, .form-select, textarea').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        $.each(errors, function(field, messages) {
            const fieldElement = $(`[name="${field}"]`);
            if (fieldElement.length) {
                fieldElement.addClass('is-invalid');
                const errorMessage = Array.isArray(messages) ? messages[0] : messages;
                fieldElement.closest('.col-sm-12').append(`<div class="invalid-feedback">${errorMessage}</div>`);
            }
        });
    }
};

$(document).ready(function() {
    // Event listeners
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        applyFilters();
    });
    
    // Real-time filtering on input change (with debounce)
    let filterTimeout;
    $('#search').on('input', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            applyFilters();
        }, 500);
    });
    
    // Real-time filtering on select change
    $('#income_head_id, #employee_id, #date_from, #date_to').on('change', function() {
        applyFilters();
    });
    
    // Export buttons
    $('#export-excel-btn').on('click', function() {
        exportToExcel();
    });
    
    $('#export-pdf-btn').on('click', function() {
        exportToPdf();
    });
    
    // Handle pagination clicks
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            window.location.href = url;
        }
    });
    
    // Delete income
    $(document).on('click', '.delete-record', function() {
        const incomeId = $(this).data('id');
        deleteIncome(incomeId);
    });
});


function applyFilters() {
    if (isFiltering) {
        return;
    }
    
    isFiltering = true;
    const form = $('#filter-form');
    const formData = form.serialize();
    const spinner = $('#incomes-spinner');
    const container = $('#incomes-table-container');
    
    // Show spinner
    spinner.removeClass('d-none');
    
    // Disable filter form during loading
    form.find('input, select, button').prop('disabled', true);
    
    $.ajax({
        url: form.attr('action'),
        method: 'GET',
        data: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                // Update the table content
                container.html(response.html);
                
                // Update URL without page reload
                const newUrl = new URL(window.location);
                const params = new URLSearchParams(formData);
                newUrl.search = params.toString();
                window.history.pushState({}, '', newUrl);
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Error loading incomes. Please try again.');
                }
            }
        },
        error: function(xhr) {
            console.error('Error applying filters:', xhr);
            if (typeof toastr !== 'undefined') {
                toastr.error('Error loading incomes. Please try again.');
            }
        },
        complete: function() {
            // Hide spinner
            spinner.addClass('d-none');
            
            // Re-enable filter form
            form.find('input, select, button').prop('disabled', false);
            isFiltering = false;
        }
    });
}

function exportToExcel() {
    const form = $('#filter-form');
    const formData = form.serialize();
    const url = window.incomeUrls.exportExcel + '?' + formData;
    window.open(url, '_blank');
}

function exportToPdf() {
    const form = $('#filter-form');
    const formData = form.serialize();
    const url = window.incomeUrls.exportPdf + '?' + formData;
    window.open(url, '_blank');
}

function deleteIncome(incomeId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        customClass: {
            confirmButton: 'btn btn-primary me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then(function (result) {
        if (result.value) {
            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: window.incomeUrls.destroy + incomeId,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    Swal.close();
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Income deleted successfully.');
                    }
                    // Reload the table
                    applyFilters();
                },
                error: function(xhr, errorMessage) {
                    Swal.close();
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Error deleting income.');
                    }
                }
            });
        }
    });
}


