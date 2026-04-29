/**
 * Bus Form AJAX Handler
 * Handles AJAX submission for bus add/edit forms with spinner and form state management
 */
'use strict';

class BusFormAjax {
    constructor() {
        this.form = null;
        this.submitButton = null;
        this.spinner = null;
        this.isSubmitting = false;
        this.init();
    }

    /**
     * Initialize the bus form AJAX functionality
     */
    init() {
        this.bindEvents();
        this.initializeForms();
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Handle form submission
        $(document).on('submit', 'form[data-ajax-bus]', this.handleFormSubmit.bind(this));
        
        // Handle form reset
        $(document).on('click', '[data-reset-form]', this.resetForm.bind(this));
        
        // Handle file preview
        $(document).on('change', 'input[type="file"]', this.handleFilePreview.bind(this));
    }

    /**
     * Initialize existing forms
     */
    initializeForms() {
        $('form[data-ajax-bus]').each((index, form) => {
            this.setupForm($(form));
        });
    }

    /**
     * Setup a form for AJAX submission
     */
    setupForm($form) {
        // Add AJAX attributes if not present
        if (!$form.attr('data-ajax-bus')) {
            $form.attr('data-ajax-bus', 'true');
        }

        // Find or create submit button
        this.submitButton = $form.find('button[type="submit"], input[type="submit"]');
        if (this.submitButton.length === 0) {
            this.submitButton = $form.find('button').first();
        }

        // Create spinner if it doesn't exist
        if (this.submitButton.find('.spinner-border').length === 0) {
            this.submitButton.prepend('<span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>');
        }

        this.spinner = this.submitButton.find('.spinner-border');
        
        // Add form ID if not present
        if (!$form.attr('id')) {
            $form.attr('id', 'bus-form-' + Date.now());
        }
    }

    /**
     * Handle form submission
     */
    handleFormSubmit(event) {
        event.preventDefault();
        
        if (this.isSubmitting) {
            return false;
        }

        this.form = $(event.currentTarget);
        this.setupForm(this.form);

        // Validate form
        if (!this.validateForm()) {
            return false;
        }

        this.submitForm();
    }

    /**
     * Validate form before submission
     */
    validateForm() {
        let isValid = true;
        const requiredFields = this.form.find('[required]');
        
        // Clear previous errors
        this.clearFormErrors();
        
        // Validate required fields
        requiredFields.each((index, field) => {
            const $field = $(field);
            if (!$field.val() || $field.val().trim() === '') {
                this.showFieldError($field, 'This field is required');
                isValid = false;
            }
        });

        // Validate unique fields
        this.validateUniqueFields();
        
        // Validate file uploads
        this.validateFileUploads();

        return isValid;
    }

    /**
     * Validate unique fields
     */
    validateUniqueFields() {
        const chassisNumber = this.form.find('input[name="chassis_number"]');
        const registrationNumber = this.form.find('input[name="registration_number"]');
        
        if (chassisNumber.length && chassisNumber.val().trim()) {
            if (!this.validateChassisNumber(chassisNumber.val())) {
                this.showFieldError(chassisNumber, 'Invalid chassis number format');
            }
        }
        
        if (registrationNumber.length && registrationNumber.val().trim()) {
            if (!this.validateRegistrationNumber(registrationNumber.val())) {
                this.showFieldError(registrationNumber, 'Invalid registration number format');
            }
        }
    }

    /**
     * Validate file uploads
     */
    validateFileUploads() {
        const fileInputs = this.form.find('input[type="file"]');
        
        fileInputs.each((index, input) => {
            const $input = $(input);
            const files = input.files;
            
            if (files.length > 0) {
                const maxSize = this.getMaxFileSize($input);
                const allowedTypes = this.getAllowedFileTypes($input);
                
                for (let file of files) {
                    if (file.size > maxSize) {
                        this.showFieldError($input, `File size must be less than ${this.formatFileSize(maxSize)}`);
                        return;
                    }
                    
                    if (!this.isFileTypeAllowed(file, allowedTypes)) {
                        this.showFieldError($input, `File type not allowed. Allowed types: ${allowedTypes.join(', ')}`);
                        return;
                    }
                }
            }
        });
    }

    /**
     * Submit form via AJAX
     */
    submitForm() {
        this.isSubmitting = true;
        this.showSpinner();
        this.disableForm();

        const formData = new FormData(this.form[0]);
        const url = this.form.attr('action');
        const method = this.form.find('input[name="_method"]').val() || 'POST';

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: (response) => {
                this.handleSuccess(response);
            },
            error: (xhr) => {
                this.handleError(xhr);
            },
            complete: () => {
                this.isSubmitting = false;
                this.hideSpinner();
                this.enableForm();
            }
        });
    }

    /**
     * Handle successful response
     */
    handleSuccess(response) {
        // Show success message
        this.showAlert('success', response.message || 'Bus saved successfully!');
        
        // Clear form if it's a create form
        if (this.form.attr('action').includes('store')) {
            this.resetForm();
        }
        
        // Redirect if specified
        if (response.redirect_url) {
            setTimeout(() => {
                window.location.href = response.redirect_url;
            }, 1500);
        }
        
        // Trigger custom event
        $(document).trigger('bus:saved', [response.data]);
    }

    /**
     * Handle error response
     */
    handleError(xhr) {
        let message = 'An error occurred while saving the bus.';
        
        if (xhr.responseJSON) {
            if (xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else if (xhr.responseJSON.errors) {
                this.handleValidationErrors(xhr.responseJSON.errors);
                return;
            }
        }
        
        this.showAlert('danger', message);
    }

    /**
     * Handle validation errors
     */
    handleValidationErrors(errors) {
        this.clearFormErrors();
        
        Object.keys(errors).forEach(field => {
            const $field = this.form.find(`[name="${field}"]`);
            if ($field.length) {
                this.showFieldError($field, errors[field][0]);
            }
        });
        
        this.showAlert('danger', 'Please correct the errors below.');
    }

    /**
     * Show spinner
     */
    showSpinner() {
        this.spinner.removeClass('d-none');
        this.submitButton.prop('disabled', true);
    }

    /**
     * Hide spinner
     */
    hideSpinner() {
        this.spinner.addClass('d-none');
        this.submitButton.prop('disabled', false);
    }

    /**
     * Disable form
     */
    disableForm() {
        this.form.find('input, select, textarea, button').prop('disabled', true);
        this.form.addClass('form-disabled');
    }

    /**
     * Enable form
     */
    enableForm() {
        this.form.find('input, select, textarea, button').prop('disabled', false);
        this.form.removeClass('form-disabled');
    }

    /**
     * Reset form
     */
    resetForm() {
        if (this.form) {
            this.form[0].reset();
            this.clearFormErrors();
            this.clearFilePreviews();
        }
    }

    /**
     * Clear form errors
     */
    clearFormErrors() {
        this.form.find('.is-invalid').removeClass('is-invalid');
        this.form.find('.invalid-feedback').remove();
        this.form.find('.alert').remove();
    }

    /**
     * Show field error
     */
    showFieldError($field, message) {
        $field.addClass('is-invalid');
        $field.after(`<div class="invalid-feedback">${message}</div>`);
    }

    /**
     * Show alert message
     */
    showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="ti ti-${type === 'success' ? 'check' : 'alert-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Remove existing alerts
        this.form.find('.alert').remove();
        
        // Add new alert at the top of the form
        this.form.prepend(alertHtml);
        
        // Auto-hide success alerts
        if (type === 'success') {
            setTimeout(() => {
                this.form.find('.alert-success').fadeOut();
            }, 3000);
        }
    }

    /**
     * Handle file preview
     */
    handleFilePreview(event) {
        const input = event.target;
        const $input = $(input);
        const files = input.files;
        
        if (files.length > 0) {
            const file = files[0];
            const previewContainer = $input.siblings('.file-preview');
            
            if (previewContainer.length === 0) {
                $input.after('<div class="file-preview mt-2"></div>');
            }
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    $input.siblings('.file-preview').html(`
                        <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        <div class="mt-1">
                            <small class="text-muted">${file.name} (${this.formatFileSize(file.size)})</small>
                        </div>
                    `);
                };
                reader.readAsDataURL(file);
            } else {
                $input.siblings('.file-preview').html(`
                    <div class="alert alert-info">
                        <i class="ti ti-file me-2"></i>
                        ${file.name} (${this.formatFileSize(file.size)})
                    </div>
                `);
            }
        }
    }

    /**
     * Clear file previews
     */
    clearFilePreviews() {
        this.form.find('.file-preview').remove();
    }

    /**
     * Validate chassis number
     */
    validateChassisNumber(chassisNumber) {
        // Basic chassis number validation (17 characters, alphanumeric)
        return /^[A-HJ-NPR-Z0-9]{17}$/i.test(chassisNumber);
    }

    /**
     * Validate registration number
     */
    validateRegistrationNumber(registrationNumber) {
        // Basic registration number validation
        return registrationNumber.length >= 5 && registrationNumber.length <= 20;
    }

    /**
     * Get max file size for input
     */
    getMaxFileSize($input) {
        const maxSize = $input.attr('data-max-size');
        if (maxSize) {
            return parseInt(maxSize);
        }
        
        // Default max sizes based on input name
        const name = $input.attr('name');
        if (name === 'bus_photo') return 2 * 1024 * 1024; // 2MB
        return 5 * 1024 * 1024; // 5MB
    }

    /**
     * Get allowed file types for input
     */
    getAllowedFileTypes($input) {
        const accept = $input.attr('accept');
        if (accept) {
            return accept.split(',').map(type => type.trim());
        }
        
        // Default types based on input name
        const name = $input.attr('name');
        if (name === 'bus_photo') return ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        return ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    }

    /**
     * Check if file type is allowed
     */
    isFileTypeAllowed(file, allowedTypes) {
        return allowedTypes.some(type => {
            if (type.includes('*')) {
                return file.type.startsWith(type.replace('*', ''));
            }
            return file.type === type;
        });
    }

    /**
     * Format file size
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

// Initialize when document is ready
$(document).ready(function() {
    window.busFormAjax = new BusFormAjax();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BusFormAjax;
}
