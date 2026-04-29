$(document).ready(function () {
    'use strict';

    // Global variables
    let isSubmitting = false;

    // Toast notification function
    function showToast(type, title, message) {
        // Check if toastr is available
        if (typeof toastr !== 'undefined') {
            toastr[type](message, title);
        } else {
            // Fallback to alert if toastr is not available
            alert(title + ': ' + message);
        }
    }

    // Initialize form handlers
    initializeFormHandlers();
    initializeSpinners();

    function initializeFormHandlers() {
        // Bus type and sub-type change handlers for both create and edit forms
        $('#bus_type_id, #bus_sub_type_id').on('change', function() {
            let typeId = $('#bus_type_id').val();
            let subTypeId = $('#bus_sub_type_id').val();

            // Call function to load buses
            loadBuses(typeId, subTypeId);
        });

        // Enhanced form submission with better validation and AJAX
        $('#scheduleForm').on('submit', function (e) {
            var baseUrl = window.location.origin;
            var pathName = window.location.pathname;
            var appIndex = pathName.indexOf('/app/');
            if (appIndex !== -1) {
                baseUrl += pathName.substring(0, appIndex);
            }
            e.preventDefault();

            if (isSubmitting) {
                return false;
            }

            const form = $(this);
            const formData = new FormData(this);
            const url = form.attr('action');
            const isEdit = form.attr('data-form-type') === 'edit';

            // Validate form before submission
            if (!validateForm(form)) {
                return false;
            }

            // Show loading state and disable all form fields
            showFormLoading(true, isEdit);
            isSubmitting = true;

            // Add CSRF token and method
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            if (isEdit) {
                formData.append('_method', 'PUT');
            }

            // Log form data for debugging
            console.log('Form Data:', Object.fromEntries(formData));
            console.log('URL:', url);
            console.log('Is Edit:', isEdit);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    hideFormLoading();
                    isSubmitting = false;

                    console.log('Success Response:', response);

                    if (response.success) {
                        showNotification('success', response.message);

                        // For edit forms, just show success message and stay on page
                        // For create forms, redirect to index
                        const isEdit = $('#scheduleForm').attr('data-form-type') === 'edit';
                        if (!isEdit) {
                            setTimeout(function () {
                                window.location.href = baseUrl + '/app/bus-schedules';
                            }, 1500);
                        } else {
                            // For edit forms, reset the form state after successful update
                            setTimeout(function () {
                                // Clear any validation states
                                $('#scheduleForm .is-invalid').removeClass('is-invalid');
                                $('#scheduleForm .invalid-feedback').remove();
                                $('#scheduleForm .is-valid').removeClass('is-valid');

                                // Show a subtle success indicator on the form
                                $('#scheduleForm').addClass('was-validated');
                                setTimeout(function () {
                                    $('#scheduleForm').removeClass('was-validated');
                                }, 3000);
                            }, 2000);
                        }
                    } else {
                        showNotification('error', response.message || 'An error occurred');
                    }
                },
                error: function (xhr) {
                    hideFormLoading();
                    isSubmitting = false;

                    console.error('AJAX Error:', xhr);
                    console.error('Response Text:', xhr.responseText);

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        displayValidationErrors(xhr.responseJSON.errors);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        showNotification('error', xhr.responseJSON.message);
                    } else {
                        let errorMessage = 'An error occurred while saving the schedule.';
                        if (xhr.status === 422) {
                            errorMessage = 'Validation failed. Please check your input.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Server error. Please try again later.';
                        }
                        showNotification('error', errorMessage);
                    }
                }
            });
        });
      

        // Enhanced form submission with better validation and AJAX
        $('#busScheduleEditForm').on('submit', function (e) {
            e.preventDefault();

            if (isSubmitting) {
                return false;
            }

            const form = $(this);
            const formData = new FormData(this);
            const url = form.attr('action');
            const isEdit = form.attr('data-form-type') === 'edit';

            // Validate form before submission
            if (!validateForm(form)) {
                return false;
            }

            // Show loading state and disable all form fields
            showFormLoading(true, isEdit);
            isSubmitting = true;

            // Add CSRF token and method
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            if (isEdit) {
                formData.append('_method', 'PUT');
            }

            // Log form data for debugging
            console.log('Form Data:', Object.fromEntries(formData));
            console.log('URL:', url);
            console.log('Is Edit:', isEdit);
            var baseUrl = window.location.origin;
            var pathName = window.location.pathname;
            var appIndex = pathName.indexOf('/app/');
            if (appIndex !== -1) {
                baseUrl += pathName.substring(0, appIndex);
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    hideFormLoading();
                    isSubmitting = false;

                    console.log('Success Response:', response);

                    if (response.success) {
                        toastr.success(response.message);

                        // For edit forms, just show success message and stay on page
                        // For create forms, redirect to index
                        const isEdit = $('#scheduleForm').attr('data-form-type') === 'edit';
                        if (!isEdit) {
                            setTimeout(function () {
                                window.location.href = baseUrl + '/app/bus-schedules';
                            }, 1500);
                        } else {
                            // For edit forms, reset the form state after successful update
                            setTimeout(function () {
                                // Clear any validation states
                                $('#scheduleForm .is-invalid').removeClass('is-invalid');
                                $('#scheduleForm .invalid-feedback').remove();
                                $('#scheduleForm .is-valid').removeClass('is-valid');

                                // Show a subtle success indicator on the form
                                $('#scheduleForm').addClass('was-validated');
                                setTimeout(function () {
                                    $('#scheduleForm').removeClass('was-validated');
                                }, 3000);
                            }, 2000);
                        }
                    } else {
                        toastr.error(response.message || 'An error occurred');
                    }
                },
                error: function (xhr) {
                    hideFormLoading();
                    isSubmitting = false;

                    console.error('AJAX Error:', xhr);
                    console.error('Response Text:', xhr.responseText);

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        displayValidationErrors(xhr.responseJSON.errors);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        let errorMessage = 'An error occurred while saving the schedule.';
                        if (xhr.status === 422) {
                            errorMessage = 'Validation failed. Please check your input.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Server error. Please try again later.';
                        }
                        toastr.error(errorMessage);
                    }
                }
            });
        });

        // Real-time validation
        $('#scheduleForm input, #scheduleForm select').on('blur change', function () {
            validateField($(this));
        });

        // Real-time assignment conflict checking
        $('#scheduleForm select[name="driver_id"]').on('change', function () {
            const driverId = $(this).val();
            const busId = $('#scheduleForm select[name="bus_id"]').val();
            const startTime = $('#scheduleForm input[name="start_time"]').val();

            if (driverId && busId && startTime) {
                checkDriverAssignmentConflicts(driverId, busId, startTime);
            }
        });

        $('#scheduleForm select[name="assistant_id"]').on('change', function () {
            const assistantId = $(this).val();
            const busId = $('#scheduleForm select[name="bus_id"]').val();
            const startTime = $('#scheduleForm input[name="start_time"]').val();

            if (assistantId && busId && startTime) {
                checkAssistantAssignmentConflicts(assistantId, busId, startTime);
            }
        });

        $('#scheduleForm select[name="bus_id"]').on('change', function () {
            // Re-check conflicts when bus changes
            const driverId = $('#scheduleForm select[name="driver_id"]').val();
            const assistantId = $('#scheduleForm select[name="assistant_id"]').val();
            const busId = $(this).val();
            const startTime = $('#scheduleForm input[name="start_time"]').val();

            if (driverId && busId && startTime) {
                checkDriverAssignmentConflicts(driverId, busId, startTime);
            }
            if (assistantId && busId && startTime) {
                checkAssistantAssignmentConflicts(assistantId, busId, startTime);
            }
        });

        $('#scheduleForm input[name="start_time"]').on('change', function () {
            // Re-check conflicts when time changes
            const driverId = $('#scheduleForm select[name="driver_id"]').val();
            const assistantId = $('#scheduleForm select[name="assistant_id"]').val();
            const busId = $('#scheduleForm select[name="bus_id"]').val();
            const startTime = $(this).val();

            if (driverId && busId && startTime) {
                checkDriverAssignmentConflicts(driverId, busId, startTime);
            }
            if (assistantId && busId && startTime) {
                checkAssistantAssignmentConflicts(assistantId, busId, startTime);
            }
        });
    }

    function initializeSpinners() {
        // Initialize loading states for various elements
        $('.btn').each(function () {
            if (!$(this).find('.spinner-border').length) {
                $(this).prepend('<span class="spinner-border spinner-border-sm d-none" style="margin-right: 5px;"></span>');
            }
        });
    }

    function validateForm(form) {
        let isValid = true;

        // Clear previous validation
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').remove();

        // Validate required fields
        form.find('[required]').each(function () {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        // Check for assignment conflicts
        if (form.find('select[name="driver_id"]').hasClass('is-invalid')) {
            isValid = false;
        }

        if (form.find('select[name="assistant_id"]').hasClass('is-invalid')) {
            isValid = false;
        }

        // Additional business logic validation - only for create, not edit
        const startTime = form.find('input[name="start_time"]').val();
        const isEdit = form.attr('data-form-type') === 'edit';

        if (startTime && !isEdit) {
            const startDateTime = new Date(startTime);
            const now = new Date();

            if (startDateTime <= now) {
                const field = form.find('input[name="start_time"]');
                field.addClass('is-invalid');
                field.after('<div class="invalid-feedback">Start time must be in the future.</div>');
                isValid = false;
            }
        }

        return isValid;
    }

    function validateField(field) {
        const value = field.val();
        const isRequired = field.prop('required');
        const fieldName = field.attr('name');

        // Remove existing validation classes
        field.removeClass('is-valid is-invalid');
        field.siblings('.invalid-feedback').remove();

        // Check if required field is empty
        if (isRequired && (!value || value.trim() === '')) {
            field.addClass('is-invalid');
            field.after(`<div class="invalid-feedback">${getFieldLabel(fieldName)} is required.</div>`);
            return false;
        }

        // If we get here, field is valid
        field.addClass('is-valid');
        return true;
    }

    function displayValidationErrors(errors) {
        // Clear existing error messages
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        console.log('Validation Errors:', errors);

        // Display new error messages
        $.each(errors, function (field, messages) {
            const fieldElement = $(`[name="${field}"]`);
            fieldElement.addClass('is-invalid');

            if (messages.length > 0) {
                fieldElement.after(`<div class="invalid-feedback">${messages[0]}</div>`);
            }
        });
    }

    // Enhanced spinner functions
    function showFormLoading(show, isEdit = false) {
        if (show) {
            $('#submitSpinner').removeClass('d-none');
            $('#submitText').text(isEdit ? 'Updating Schedule...' : 'Creating Schedule...');
            $('#submitBtn').prop('disabled', true);

            // Disable all form fields
            $('#scheduleForm input, #scheduleForm select, #scheduleForm textarea').prop('disabled', true);

            // Add loading overlay to form
            if ($('#scheduleForm').find('.loading-overlay').length === 0) {
                $('#scheduleForm').css('position', 'relative');
                $('#scheduleForm').append('<div class="loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-light bg-opacity-75" style="z-index: 10;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            }
        } else {
            $('#submitSpinner').addClass('d-none');
            $('#submitText').text(isEdit ? 'Update Schedule' : 'Create Schedule');
            $('#submitBtn').prop('disabled', false);

            // Enable all form fields
            $('#scheduleForm input, #scheduleForm select, #scheduleForm textarea').prop('disabled', false);

            // Remove loading overlay
            $('#scheduleForm').find('.loading-overlay').remove();
        }
    }

    function hideFormLoading() {
        const isEdit = $('#scheduleForm').attr('data-form-type') === 'edit';
        showFormLoading(false, isEdit);
    }

    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'ti ti-check-circle' : 'ti ti-alert-circle';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                <i class="${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Remove existing notifications
        $('.alert.position-fixed').remove();

        // Add new notification
        $('body').append(alertHtml);

        // Auto-hide after 5 seconds for success, 8 seconds for errors
        const hideDelay = type === 'success' ? 5000 : 8000;
        setTimeout(function () {
            $('.alert.position-fixed').fadeOut();
        }, hideDelay);
    }

    function getFieldLabel(fieldName) {
        const labels = {
            'start_stoppage_id': 'Start Stoppage',
            'end_stoppage_id': 'End Stoppage',
            'bus_route_id': 'Bus Route',
            'start_time': 'Start Time',
            'bus_id': 'Bus',
            'driver_id': 'Driver',
            'assistant_id': 'Assistant',
            'bus_user_id': 'Bus User',
            'status': 'Status'
        };

        return labels[fieldName] || fieldName;
    }

    // Check for driver assignment conflicts
    function checkDriverAssignmentConflicts(driverId, busId, startTime) {
        var baseUrl = window.location.origin;
        var pathName = window.location.pathname;
        var appIndex = pathName.indexOf('/app/');
        if (appIndex !== -1) {
            baseUrl += pathName.substring(0, appIndex);
        }
        if (!driverId || !busId || !startTime) return;

        const isEdit = $('#scheduleForm').attr('data-form-type') === 'edit';
        const scheduleId = isEdit ? window.location.pathname.split('/').pop() : null;

        $.ajax({
            url: baseUrl + '/app/bus-schedules/check-driver-conflicts',
            type: 'POST',
            data: {
                driver_id: driverId,
                bus_id: busId,
                start_time: startTime,
                schedule_id: scheduleId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                console.log('Driver conflict response:', response);
                const field = $('select[name="driver_id"]');
                if (response.has_conflicts) {
                    field.addClass('is-invalid');
                    field.siblings('.invalid-feedback').remove();
                    field.after(`<div class="invalid-feedback">${response.message}</div>`);

                    // Show toast notification
                    showToast('error', 'Driver Conflict', response.message);
                } else {
                    field.removeClass('is-invalid');
                    field.siblings('.invalid-feedback').remove();
                }
            },
            error: function (xhr) {
                console.error('Error checking driver conflicts:', xhr.responseText);
                showToast('error', 'Error', 'Failed to check driver conflicts');
            }
        });
    }

    // Check for assistant assignment conflicts
    function checkAssistantAssignmentConflicts(assistantId, busId, startTime) {
        var baseUrl = window.location.origin;
        var pathName = window.location.pathname;
        var appIndex = pathName.indexOf('/app/');
        if (appIndex !== -1) {
            baseUrl += pathName.substring(0, appIndex);
        }
        if (!assistantId) {
            const field = $('select[name="assistant_id"]');
            field.removeClass('is-invalid');
            field.siblings('.invalid-feedback').remove();
            return;
        }

        if (!assistantId) {
            const field = $('select[name="assistant_id"]');
            field.removeClass('is-invalid');
            field.siblings('.invalid-feedback').remove();
            return;
        }

        if (!busId || !startTime) return;

        const isEdit = $('#scheduleForm').attr('data-form-type') === 'edit';
        const scheduleId = isEdit ? window.location.pathname.split('/').pop() : null;

        $.ajax({
            url: baseUrl + '/app/bus-schedules/check-assistant-conflicts',
            type: 'POST',
            data: {
                assistant_id: assistantId,
                bus_id: busId,
                start_time: startTime,
                schedule_id: scheduleId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                console.log('Assistant conflict response:', response);
                const field = $('select[name="assistant_id"]');
                if (response.has_conflicts) {
                    field.addClass('is-invalid');
                    field.siblings('.invalid-feedback').remove();
                    field.after(`<div class="invalid-feedback">${response.message}</div>`);

                    // Show toast notification
                    showToast('error', 'Assistant Conflict', response.message);
                } else {
                    field.removeClass('is-invalid');
                    field.siblings('.invalid-feedback').remove();
                }
            },
            error: function (xhr) {
                console.error('Error checking assistant conflicts:', xhr.responseText);
                showToast('error', 'Error', 'Failed to check assistant conflicts');
            }
        });
    }

    // Function to load bus list based on type and sub-type
    function loadBuses(typeId, subTypeId) {
        if (!typeId || !subTypeId) {
            $('#bus_id').html('<option value="">Select Bus</option>');
            return;
        }

        var baseUrl = window.location.origin;
        var pathName = window.location.pathname;
        var appIndex = pathName.indexOf('/app/');
        if (appIndex !== -1) {
            baseUrl += pathName.substring(0, appIndex);
        }

        var busUrlForTypeAndSubType = baseUrl + '/app/buses/get-buses-names-by-type-and-subtype';

        $.ajax({
            url: busUrlForTypeAndSubType,
            type: 'GET',
            data: {
                bus_type_id: typeId,
                bus_sub_type_id: subTypeId
            },
            beforeSend: function() {
                $('#bus_id').html('<option>Loading...</option>');
            },
            success: function(response) {
                $('#bus_id').empty().append('<option value="">Select Bus</option>');

                if (response && response.success && response.buses && response.buses.length > 0) {
                    $.each(response.buses, function(index, bus) {
                        $('#bus_id').append(
                            $('<option>', {
                                value: bus.id,
                                text: bus.model_name + ' (' + bus.registration_number + ')'
                            })
                        );
                    });
                } else {
                    $('#bus_id').append('<option value="">No buses found</option>');
                }
            },
            error: function() {
                $('#bus_id').html('<option value="">Error loading buses</option>');
            }
        });
    }

});
$(document).ready(function () {
    let isSubmitting = false;

    $('#addBusScheduleForm').on('submit', function (e) {
        e.preventDefault();

        if (isSubmitting) return false;

        const form = $(this);
        const formData = new FormData(this);
        const url = form.attr('action');

        // Validate before submit
        if (typeof validateForm === 'function' && !validateForm(form)) {
            return false;
        }

        showFormLoading(true);
        isSubmitting = true;

        // Append CSRF token
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        console.log('%c[Form Submit] Debug Info', 'color: #0dcaf0; font-weight: bold;');
        console.log('➡️ URL:', url);
        console.log('🧾 Data:', Object.fromEntries(formData));

        const baseUrl = window.location.origin + (window.location.pathname.split('/app/')[0] || '');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                hideFormLoading();
                isSubmitting = false;

                console.log('%c✅ Success Response:', 'color: green;', response);

                if (response.success) {
                    notify('success', response.message || 'Schedule saved successfully!');

                    // Redirect to index page
                    setTimeout(() => {
                        window.location.href = `${baseUrl}/app/bus-schedules`;
                    }, 1200);
                } else {
                    notify('error', response.message || 'Unexpected error occurred.');
                }
            },
            error: function (xhr) {
                hideFormLoading();
                isSubmitting = false;

                console.error('%c❌ AJAX Error:', 'color: red;', xhr);

                let errorMessage = 'An unexpected error occurred. Please try again.';

                if (xhr.status === 422) {
                    errorMessage = 'Validation failed. Please check your input.';
                    if (xhr.responseJSON?.errors) displayValidationErrors(xhr.responseJSON.errors);
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please try again later.';
                } else if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                notify('error', errorMessage);
            }
        });
    });

    /** 🔔 Helper for showing notifications */
    function notify(type, message) {
        if (typeof toastr !== 'undefined') {
            if (type === 'success') toastr.success(message);
            else if (type === 'error') toastr.error(message);
            else toastr.info(message);
        } else if (typeof showNotification === 'function') {
            showNotification(type, message);
        } else {
            alert(message);
        }
    }

    /** 🌀 Default form loading handler (optional) */
    function showFormLoading(show = true) {
        const btn = $('#submitBtn');
        const spinner = $('#submitSpinner');
        const text = $('#submitText');
        if (show) {
            btn.prop('disabled', true);
            spinner.removeClass('d-none');
            text.text('Saving...');
        } else {
            btn.prop('disabled', false);
            spinner.addClass('d-none');
            text.text('Create Schedule');
        }
    }

    function hideFormLoading() {
        showFormLoading(false);
    }
});