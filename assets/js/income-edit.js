/**
 * Income Edit Form with AJAX and Spinner
 */

'use strict';

$(document).ready(function() {
    const form = $('#income-form');
    const submitBtn = $('#submit-btn');
    const spinner = submitBtn.find('.spinner-border');

    form.on('submit', function(e) {
        e.preventDefault();
        
        // Show spinner
        spinner.removeClass('d-none');
        submitBtn.prop('disabled', true);
        
        // Submit form via AJAX
        $.ajax({
            url: form.attr('action'),
            method: 'PUT',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (typeof toastr !== 'undefined') {
                    toastr.success('Income updated successfully.');
                }
                // Redirect to index page
                setTimeout(function() {
                    window.location.href = window.incomeUrls.index;
                }, 1000);
            },
            error: function(xhr) {
                spinner.addClass('d-none');
                submitBtn.prop('disabled', false);
                
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    // Clear previous errors
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                    
                    // Show new errors
                    $.each(errors, function(field, messages) {
                        const fieldElement = $(`[name="${field}"]`);
                        if (fieldElement.length) {
                            fieldElement.addClass('is-invalid');
                            const errorMessage = Array.isArray(messages) ? messages[0] : messages;
                            fieldElement.closest('.col-md-6, .col-md-12').append(`<div class="invalid-feedback d-block">${errorMessage}</div>`);
                        }
                    });
                } else {
                    const message = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(message);
                    } else {
                        alert('Error: ' + message);
                    }
                }
            }
        });
    });
});

