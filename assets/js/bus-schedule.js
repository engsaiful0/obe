$(document).ready(function () {
    'use strict';

    // Initialize toastr configuration if not already set
    if (typeof toastr !== 'undefined' && !toastr.options) {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
    }

    // Initialize rowCount based on existing rows or start from 1
    const existingRows = $('#scheduleTable tbody').find('tr.data-row').length;
    let rowCount = window.initialRowCount || existingRows || 1;

    // Convert 12-hour format to 24-hour format (HH:mm)
    function convertTo24Hour(hours, minutes, amPm) {
        if (!hours || !minutes || !amPm) {
            return null;
        }
        
        let hour24 = parseInt(hours);
        const minute = minutes.padStart(2, '0');
        
        if (amPm.toLowerCase() === 'pm' && hour24 !== 12) {
            hour24 += 12;
        } else if (amPm.toLowerCase() === 'am' && hour24 === 12) {
            hour24 = 0;
        }
        
        return hour24.toString().padStart(2, '0') + ':' + minute;
    }

    // Add new row
    function addNewRow() {
        const tbody = $('#scheduleTable tbody');
        const firstRow = tbody.find('tr.data-row').first();
        
        // Clone the row
        const newRow = firstRow.clone(false);

        newRow.find('input, select').each(function () {
            const $element = $(this);
            const originalName = $element.attr('name');

            if (originalName) {
                const newName = originalName.replace(/\[\d+]/, `[${rowCount}]`);
                $element.attr('name', newName);
            }

            $element.val('');
        });

        // Remove duplicate blank options (keep only the first one)
        newRow.find("select").each(function () {
            const $select = $(this);
            const blankOptions = $select.find('option[value=""]');
            if (blankOptions.length > 1) {
                // Keep the first one, remove the rest
                blankOptions.slice(1).remove();
            }
            // Also check for duplicate option values
            const seenValues = {};
            $select.find('option').each(function() {
                const value = $(this).attr('value');
                if (value && seenValues[value]) {
                    $(this).remove();
                } else if (value) {
                    seenValues[value] = true;
                }
            });
        });

        newRow.attr('data-row-index', rowCount);
        
        newRow.find('.action-btn').html(`
            <button type="button" class="btn btn-danger btn-sm remove-row">
                <i class="ti ti-trash"></i>
            </button>
        `);

        tbody.append(newRow);
        
       
        
        rowCount++;
        updateButtons();
    }


    // Update add/remove button visibility
    function updateButtons() {
        const rows = $('#scheduleTable tbody').find('tr.data-row');
        
        rows.each(function (index) {
            const btnCell = $(this).find('.action-btn');
            
            if (index === 0) {
                btnCell.html(`
                    <button type="button" class="btn btn-primary btn-sm add-row">
                        <i class="ti ti-plus"></i>
                    </button>
                `);
            } else {
                btnCell.html(`
                    <button type="button" class="btn btn-danger btn-sm remove-row">
                        <i class="ti ti-trash"></i>
                    </button>
                `);
            }
        });
    }

    // Add Row Click
    $(document).on('click', '.add-row', function () {
        addNewRow();
    });

    // Remove Row Click
    $(document).on('click', '.remove-row', function () {
        $(this).closest('tr').remove();
    updateButtons();
    });

    // Form submission handler
    $('#scheduleForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const url = form.attr('action');
        const method = form.find('input[name="_method"]').val() || 'POST';
        const isAjax = form.attr('data-ajax') === 'true';
        
        // Validate required fields before processing
        const effectiveFrom = $('#effective_from').val();
        const keywordId = $('#keyword_id').val();
        const status = $('#status').val();
        const busUserId = $('#bus_user_id').val();
        
        if (!effectiveFrom) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Please select Effective From date.', 'Validation Error');
            } else {
                alert('Please select Effective From date.');
            }
            return false;
        }
        if (!keywordId) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Please select Schedule Category.', 'Validation Error');
            } else {
                alert('Please select Schedule Category.');
            }
            return false;
        }
        if (!status) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Please select Status.', 'Validation Error');
            } else {
                alert('Please select Status.');
            }
            return false;
        }
        if (!busUserId) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Please select Bus User.', 'Validation Error');
            } else {
                alert('Please select Bus User.');
            }
            return false;
        }

        // Check if at least one schedule row exists
        const scheduleRows = $('#scheduleTable tbody').find('tr.data-row');
        if (scheduleRows.length === 0) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Please add at least one schedule entry.', 'Validation Error');
            } else {
                alert('Please add at least one schedule entry.');
            }
            return false;
        }
        
        // Remove any existing hidden start_time inputs
        form.find('input[name*="[start_time]"]').remove();
        
        // Convert 12-hour format to 24-hour format and add as hidden inputs BEFORE building FormData
        form.find('tr.data-row').each(function() {
            const row = $(this);
            const hours = row.find('select[name*="[hours]"]').val();
            const minutes = row.find('select[name*="[minutes]"]').val();
            const amPm = row.find('select[name*="[am_pm]"]').val();
            
            if (hours && minutes && amPm) {
                const time24 = convertTo24Hour(hours, minutes, amPm);
                if (time24) {
                    // Get the index from the hours select name
                    const nameMatch = row.find('select[name*="[hours]"]').attr('name').match(/schedules\[(\d+)\]/);
                    if (nameMatch) {
                        const index = nameMatch[1];
                        // Add hidden input with 24-hour format directly to the form
                        form.append(`<input type="hidden" name="schedules[${index}][start_time]" value="${time24}">`);
                    }
                }
            }
        });
        
        // Build FormData manually to ensure all fields are included, especially Select2 values
        const formData = new FormData();
        
        // Add CSRF token
        const csrfToken = form.find('input[name="_token"]').val();
        if (csrfToken) {
            formData.append('_token', csrfToken);
        }
        
        // Add method override for PUT
        if (method === 'PUT') {
            formData.append('_method', 'PUT');
        }
        
        // Add all input fields (text, date, hidden, etc.)
        form.find('input').each(function() {
            const $input = $(this);
            const name = $input.attr('name');
            const type = $input.attr('type');
            
            if (!name) return;
            
            if (type === 'checkbox') {
                if ($input.is(':checked')) {
                    formData.append(name, $input.val() || '1');
                }
            } else if (type === 'radio') {
                if ($input.is(':checked')) {
                    formData.append(name, $input.val());
                }
            } else {
                const value = $input.val();
                if (value !== null && value !== undefined) {
                    formData.append(name, value);
                }
            }
        });
        
        // Add all select fields (including Select2 - get value directly)
        form.find('select').each(function() {
            const $select = $(this);
            const name = $select.attr('name');
            if (!name) return;
            
            // Get value - Select2 stores it in the select element
            const value = $select.val();
            if (value !== null && value !== undefined && value !== '') {
                formData.append(name, value);
            }
        });
        
        // Add all textarea fields
        form.find('textarea').each(function() {
            const $textarea = $(this);
            const name = $textarea.attr('name');
            if (!name) return;
            
            const value = $textarea.val();
            if (value !== null && value !== undefined) {
                formData.append(name, value);
            }
        });
        
        // Explicitly ensure required fields are present (double-check)
        formData.set('effective_from', effectiveFrom);
        formData.set('keyword_id', keywordId);
        formData.set('status', status);
        formData.set('bus_user_id', busUserId);
        
        // Validate form
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return false;
        }

        // Validate each row
        let isValid = true;
        scheduleRows.each(function() {
            const row = $(this);
            const hours = row.find('select[name*="[hours]"]').val();
            const minutes = row.find('select[name*="[minutes]"]').val();
            const amPm = row.find('select[name*="[am_pm]"]').val();
            const startingPoint = row.find('select[name*="[starting_point_id]"]').val();
            const busRoute = row.find('select[name*="[bus_route_id]"]').val();

            if (!hours || !minutes || !amPm || !startingPoint || !busRoute) {
                isValid = false;
                return false;
            }
        });

        if (!isValid) {
            if (typeof toastr !== 'undefined') {
                toastr.error('Please fill all required fields in schedule rows.', 'Validation Error');
            } else {
                alert('Please fill all required fields in schedule rows.');
            }
            return false;
        }

        if (isAjax) {
            showSpinner();

            $.ajax({
                url: url,
                type: 'POST', // Always use POST, Laravel handles PUT via _method
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    hideSpinner();

                    if (response.success) {
                        const isUpdate = method === 'PUT';
                        const successMessage = isUpdate 
                            ? (response.message || 'Bus schedule updated successfully!')
                            : (response.message || 'Bus schedule created successfully!');
                        
                        // Show success toast message
                        if (typeof toastr !== 'undefined') {
                            toastr.success(successMessage, 'Success', {
                                timeOut: 5000,
                                closeButton: true,
                                progressBar: true
                            });
                        } else {
                            alert('Success: ' + successMessage);
                        }
                        
                        if (response.redirect_url) {
                            // Wait a bit longer to ensure user sees the toast message
                            setTimeout(function() {
                                window.location.href = response.redirect_url;
                            }, 2000);
                        } else {
                            if (isUpdate) {
                                // For update, redirect to index after showing success message
                                // Wait longer to ensure toast is visible
                                setTimeout(function() {
                                    if (window.busScheduleUrls && window.busScheduleUrls.index) {
                                        window.location.href = window.busScheduleUrls.index;
                                    } else {
                                        // Fallback - try to get from route helper if available
                                        window.location.href = window.location.pathname.replace(/\/edit$/, '') || '/app/bus-schedules/schedule-index';
                                    }
                                }, 2000);
                            } else {
                                // Reset form for create
                                form[0].reset();
                                // Remove all rows except first data row
                                const tbody = $('#scheduleTable tbody');
                                tbody.find('tr.data-row').not(':first').remove();
                                rowCount = 1;
                                updateButtons();
                            }
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message || 'An error occurred while saving the schedule.', 'Error', {
                                timeOut: 5000,
                                closeButton: true,
                                progressBar: true
                            });
                        } else {
                            alert('Error: ' + (response.message || 'An error occurred while saving the schedule.'));
                        }
                    }
                },
                error: function(xhr) {
                    hideSpinner();

                    const isUpdate = method === 'PUT';
                    let errorMessage = isUpdate 
                        ? 'An error occurred while updating the schedule.'
                        : 'An error occurred while creating the schedule.';
                    
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            // Get first error message
                            const firstErrorKey = Object.keys(errors)[0];
                            const firstError = errors[firstErrorKey];
                            errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                            
                            // Show all validation errors
                            if (Object.keys(errors).length > 1) {
                                let allErrors = [];
                                Object.keys(errors).forEach(function(key) {
                                    const errorMessages = Array.isArray(errors[key]) ? errors[key] : [errors[key]];
                                    allErrors = allErrors.concat(errorMessages);
                                });
                                errorMessage = allErrors.join(', ');
                            }
                        }
                    } else if (xhr.status === 0) {
                        errorMessage = 'Network error. Please check your connection.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Please try again later.';
                    }

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
            });
        } else {
            form[0].submit();
        }

        return false;
    });

    // Show spinner
    function showSpinner() {
        const $spinner = $('#submitSpinner');
        const $text = $('#submitText');
        const $btn = $('#submitBtn');
        const isUpdate = $('#scheduleForm').find('input[name="_method"]').val() === 'PUT';
        
        $spinner.removeClass('d-none');
        $spinner.css('display', 'inline-block');
        $text.text(isUpdate ? 'Updating...' : 'Creating...');
        $btn.prop('disabled', true);
        $btn.addClass('disabled');
    }

    // Hide spinner
    function hideSpinner() {
        const $spinner = $('#submitSpinner');
        const $text = $('#submitText');
        const $btn = $('#submitBtn');
        const isUpdate = $('#scheduleForm').find('input[name="_method"]').val() === 'PUT';
        
        $spinner.addClass('d-none');
        $text.text(isUpdate ? 'Update Schedule' : 'Create Schedule');
        $btn.prop('disabled', false);
        $btn.removeClass('disabled');
    }

    // Toast notification function
    function showToast(type, title, message) {
        if (typeof toastr !== 'undefined') {
            // Configure toastr options for better visibility
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
                timeOut: '5000', // Show for 5 seconds
                extendedTimeOut: '1000',
                showEasing: 'swing',
                hideEasing: 'linear',
                showMethod: 'fadeIn',
                hideMethod: 'fadeOut'
            };
            
            // Show the toast
            toastr[type](message, title, {
                timeOut: 5000, // 5 seconds for success messages
                extendedTimeOut: 2000
            });
        } else if (typeof Swal !== 'undefined') {
            // Fallback to SweetAlert if toastr is not available
            Swal.fire({
                icon: type === 'success' ? 'success' : 'error',
                title: title,
                text: message,
                timer: type === 'success' ? 3000 : 5000,
                showConfirmButton: true
            });
        } else {
            // Final fallback to alert
            alert(title + ': ' + message);
        }
    }

   

    updateButtons();
});
