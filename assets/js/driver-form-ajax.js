$(document).ready(function () {
    'use strict';

    // Initialize form handlers
    initializeFormSubmission();
    initializeFormValidation();
    initializeSalaryCalculation();

    function initializeFormSubmission() {
        // Handle form submission with AJAX
        $('#driverEditForm').on('submit', function (e) {
            e.preventDefault();

            const form = $(this);
            const formData = new FormData(this);
            const url = form.attr('action');
            const isEdit = url.includes('update');
            // Show spinner
            showSpinner('#submitSpinner', '#submitText');

            // Add CSRF token
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
            formData.append('_method', 'PUT');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    hideSpinner('#submitSpinner', '#submitText');

                    if (response.success) {
                        toastr.success(response.message);

                        // Redirect after successful creation/update
                        setTimeout(() => {
                            if (response.redirect_url) {
                                window.location.href = response.redirect_url;
                            } else {
                                // Only reset form for create operations, not edit
                                if (!isEdit) {
                                    $('#driverForm')[0].reset();
                                }
                            }
                        }, 1500);
                    }
                },
                error: function (xhr) {
                    hideSpinner('#submitSpinner', '#submitText');

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        displayValidationErrors(xhr.responseJSON.errors);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        showAlert('error', xhr.responseJSON.message);
                    } else {
                        showAlert('error', 'An error occurred while saving the driver.');
                    }
                }
            });
        });
    }

    function initializeFormValidation() {
        // Initialize jQuery Validation
        $('#driverForm').validate({
            // Validation rules
            rules: {
                full_name: {
                    required: true,
                    minlength: 2,
                    maxlength: 100
                },
                father_name: {
                    maxlength: 100
                },
                contact_number: {
                    required: true,
                    phoneNumber: true
                },
              
           
             
                driver_type_id: {
                    required: true
                },
              
               
               
              
            },
            
            // Custom error messages
            messages: {
                full_name: {
                    required: "Please enter the driver's full name",
                    minlength: "Full name must be at least 2 characters",
                    maxlength: "Full name cannot exceed 100 characters"
                },
                father_name: {
                    maxlength: "Father's name cannot exceed 100 characters"
                },
               
                contact_number: {
                    required: "Please enter a contact number",
                    phoneNumber: "Please enter a valid contact number (10-15 digits)"
                },
               
              
                driver_type_id: {
                    required: "Please select a driver type"
                },
                
            },
            
            // Error placement
            errorPlacement: function(error, element) {
                if (element.is(':radio') || element.is(':checkbox')) {
                    error.appendTo(element.closest('.form-check'));
                } else {
                    error.insertAfter(element);
                }
            },
            
            // Highlight and unhighlight
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid').removeClass('is-valid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid').addClass('is-valid');
            },
            
            // Error class
            errorClass: 'invalid-feedback',
            validClass: 'is-valid',
            
            // Submit handler
            submitHandler: function(form) {
                // Show spinner
                showSpinner('#submitSpinner', '#submitText');
                
                const formData = new FormData(form);
                const url = $(form).attr('action');
                const isEdit = url.includes('update');

                // Add CSRF token
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                if (isEdit) {
                    formData.append('_method', 'PUT');
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        hideSpinner('#submitSpinner', '#submitText');

                        if (response.success) {
                            // Reset the form
                            form.reset();

                            // Show toast message
                            toastr.success(response.message || 'Driver saved successfully!');
                            
                            // Redirect after success
                            setTimeout(() => {
                                if (response.redirect_url) {
                                    window.location.href = response.redirect_url;
                                }
                            }, 1000);
                        }
                    },
                    error: function (xhr) {
                        hideSpinner('#submitSpinner', '#submitText');

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            displayValidationErrors(xhr.responseJSON.errors);
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            showAlert('error', xhr.responseJSON.message);
                        } else {
                            showAlert('error', 'An error occurred while saving the driver.');
                        }
                    }
                });
            }
        });

        // Add custom validation methods
        addCustomValidationMethods();
    }

    function addCustomValidationMethods() {
        // Phone number validation
        $.validator.addMethod("phoneNumber", function(value, element) {
            if (!value) return true; // Allow empty for optional fields
            const cleanNumber = value.replace(/\s/g, '');
            return /^[0-9+\-()]{10,15}$/.test(cleanNumber);
        }, "Please enter a valid phone number (10-15 digits)");

        // Past date validation
        $.validator.addMethod("pastDate", function(value, element) {
            if (!value) return true;
            const date = new Date(value);
            const today = new Date();
            return date < today;
        }, "Date must be in the past");

        // Future date validation
        $.validator.addMethod("futureDate", function(value, element) {
            if (!value) return true;
            const date = new Date(value);
            const today = new Date();
            return date > today;
        }, "Date must be in the future");

        // Not future date validation
        $.validator.addMethod("notFuture", function(value, element) {
            if (!value) return true;
            const date = new Date(value);
            const today = new Date();
            return date <= today;
        }, "Date cannot be in the future");

        // Age validation (minimum)
        $.validator.addMethod("minAge", function(value, element, param) {
            if (!value) return true;
            const birthDate = new Date(value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            return age >= param;
        }, "Age must be at least {0} years");

        // Age validation (maximum)
        $.validator.addMethod("maxAge", function(value, element, param) {
            if (!value) return true;
            const birthDate = new Date(value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            return age <= param;
        }, "Age cannot exceed {0} years");

        // After issue date validation
        $.validator.addMethod("afterIssueDate", function(value, element) {
            if (!value) return true;
            const issueDate = $('#license_issue_date').val();
            if (!issueDate) return true;
            
            const expiryDate = new Date(value);
            const issueDateObj = new Date(issueDate);
            return expiryDate > issueDateObj;
        }, "Expiry date must be after issue date");

        // Gross salary match validation by driver type
        $.validator.addMethod("grossSalaryMatch", function(value, element) {
            const typeText = getDriverTypeText();
            if (!value && typeText === 'daily') {
                // Daily driver does not use gross salary
                return true;
            }

            const grossSalary = parseFloat(value) || 0;

            if (typeText === 'contractual') {
                const basic = parseFloat($('#basic_salary').val()) || 0;
                const food = parseFloat($('#food_allowance').val()) || 0;
                const calculated = basic + food;
                return Math.abs(calculated - grossSalary) <= 0.01;
            }

            // Regular & others: basic + house + other
            const basic = parseFloat($('#basic_salary').val()) || 0;
            const house = parseFloat($('#house_rent').val()) || 0;
            const other = parseFloat($('#other_allowance').val()) || 0;
            const calculated = basic + house + other;

            if (basic > 0 || house > 0 || other > 0) {
                return Math.abs(calculated - grossSalary) <= 0.01;
            }

            return true;
        }, "Gross salary must equal the sum of salary components for this driver type");
    }

    function getDriverTypeText() {
        return $('#driver_type_id option:selected').text().trim().toLowerCase();
    }

    function initializeSalaryCalculation() {
        const salaryFields = [
            '#basic_salary',
            '#house_rent',
            '#medical_allowance',
            '#other_allowance',
            '#daily_salary',
            '#food_allowance'
        ];

        salaryFields.forEach(function (fieldSelector) {
            $(fieldSelector).on('input change', function () {
                calculateGrossSalaryByDriverType();
            });
        });

        $('#driver_type_id').on('change', function () {
            toggleSalaryFieldsByDriverType();
        });

        toggleSalaryFieldsByDriverType();
    }

    function calculateGrossSalaryByDriverType() {
        const typeText = getDriverTypeText();

        if (!typeText) {
            $('#gross_salary').val('');
            return;
        }

        let gross = 0;

        if (typeText === 'daily') {
            // Daily driver: only daily_salary, no gross salary
            $('#gross_salary').val('');
        } else if (typeText === 'contractual') {
            const basic = parseFloat($('#basic_salary').val()) || 0;
            const food = parseFloat($('#food_allowance').val()) || 0;
            gross = basic + food;
            $('#gross_salary').val(gross.toFixed(2));
        } else {
            // Regular and any other: basic + house + other
            const basic = parseFloat($('#basic_salary').val()) || 0;
            const house = parseFloat($('#house_rent').val()) || 0;
            const other = parseFloat($('#other_allowance').val()) || 0;
            gross = basic + house + other;
            $('#gross_salary').val(gross.toFixed(2));
        }

        $('#gross_salary').valid();
    }

    function toggleSalaryFieldsByDriverType() {
        const typeText = getDriverTypeText();
        const isDaily = typeText === 'daily';
        const isContractual = typeText === 'contractual';

        const dailyControl = $('#daily_salary_control');
        const basicControl = $('#basic_salary_control');
        const foodControl = $('#food_allowance_control');
        const houseControl = $('#house_rent_control');
        const medicalControl = $('#medical_allowance_control');
        const otherControl = $('#other_allowance_control');
        const grossControl = $('#gross_salary_control');

        // Reset visibility
        dailyControl.show();
        basicControl.show();
        foodControl.show();
        houseControl.show();
        medicalControl.show();
        otherControl.show();
        grossControl.show();

        if (isDaily) {
            dailyControl.show();
            basicControl.hide();
            foodControl.hide();
            houseControl.hide();
            medicalControl.hide();
            otherControl.hide();
            grossControl.hide();

            $('#daily_salary').prop('required', true);
            $('#basic_salary').prop('required', false).val('');
            $('#food_allowance').prop('required', false).val('');
            $('#house_rent').prop('required', false).val('');
            $('#medical_allowance').prop('required', false).val('');
            $('#other_allowance').prop('required', false).val('');
            $('#gross_salary').val('');
        } else if (isContractual) {
            dailyControl.hide();
            basicControl.show();
            foodControl.show();
            houseControl.hide();
            medicalControl.hide();
            otherControl.hide();
            grossControl.show();

            $('#daily_salary').prop('required', false).val('');
            $('#basic_salary').prop('required', true);
            $('#food_allowance').prop('required', true);
            $('#house_rent').prop('required', false).val('');
            $('#medical_allowance').prop('required', false).val('');
            $('#other_allowance').prop('required', false).val('');
        } else {
            // Regular driver and others
            dailyControl.hide();
            basicControl.show();
            foodControl.hide();
            houseControl.show();
            medicalControl.hide();
            otherControl.show();
            grossControl.show();

            $('#daily_salary').prop('required', false).val('');
            $('#basic_salary').prop('required', true);
            $('#food_allowance').prop('required', false).val('');
            $('#house_rent').prop('required', true);
            $('#medical_allowance').prop('required', false).val('');
            $('#other_allowance').prop('required', true);
        }

        calculateGrossSalaryByDriverType();
    }

    function displayValidationErrors(errors) {
        // Clear existing error messages
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        // Display new error messages
        $.each(errors, function (field, messages) {
            const fieldElement = $(`[name="${field}"]`);
            fieldElement.addClass('is-invalid');

            if (messages.length > 0) {
                fieldElement.after(`<div class="invalid-feedback">${messages[0]}</div>`);
            }
        });
    }

    function getFieldLabel(fieldName) {
        const labels = {
            'full_name': 'Full Name',
            'father_name': 'Father\'s Name',
            'date_of_birth': 'Date of Birth',
            'national_id_passport': 'National ID/Passport',
            'contact_number': 'Contact Number',
            'email': 'Email',
            'license_number': 'License Number',
            'license_type_id': 'License Type',
            'driver_type_id': 'Driver Type',
            'religion_id': 'Religion',
            'educational_qualification_id': 'Educational Qualification',
            'status_id': 'Status',
            'experience_year_id': 'Experience Year'
        };

        return labels[fieldName] || fieldName;
    }

    function showSpinner(spinnerSelector, textSelector) {
        $(spinnerSelector).removeClass('d-none');
        $(textSelector).html('Processing...');
    }

    function hideSpinner(spinnerSelector, textSelector) {
        $(spinnerSelector).addClass('d-none');
        const originalText = $(textSelector).data('original-text') || 'Save Driver';
        $(textSelector).html(originalText);
    }

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        // Remove existing alerts
        $('.alert').remove();

        // Add new alert at the top of the form
        $('.card-body').prepend(alertHtml);

        // Auto-hide after 5 seconds
        setTimeout(function () {
            $('.alert').fadeOut();
        }, 5000);
    }

    // Initialize Select2 for better dropdown experience
    if ($.fn.select2) {
        $('select').select2({
            placeholder: 'Select an option',
            allowClear: true
        });
    }

    // Add CSS for validation feedback
    const validationCSS = `
        <style>
            .form-control.is-valid {
                border-color: #198754;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='m2.3 6.73.94-.94 1.88 1.88 3.75-3.75.94.94-4.69 4.69z'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }
            
            .form-control.is-invalid {
                border-color: #dc3545;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 1.4 1.4 1.4-1.4'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right calc(0.375em + 0.1875rem) center;
                background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            }
            
            .invalid-feedback {
                display: block;
                width: 100%;
                margin-top: 0.25rem;
                font-size: 0.875em;
                color: #dc3545;
            }
            
            .is-valid {
                border-color: #198754;
            }
            
            .is-invalid {
                border-color: #dc3545;
            }
        </style>
    `;
    
    // Inject CSS
    $('head').append(validationCSS);
});