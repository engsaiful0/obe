/**
 * AJAX Form Handler Utility
 * A reusable JavaScript utility for handling form submissions with AJAX
 * Provides loading states, validation, and user feedback
 */

class AjaxFormHandler {
    constructor(options) {
        this.form = document.getElementById(options.formId);
        this.submitBtn = document.getElementById(options.submitBtnId);
        this.loadingSpinner = document.getElementById(options.loadingSpinnerId);
        this.alertContainer = document.getElementById(options.alertContainerId);
        this.alertMessage = document.getElementById(options.alertMessageId);
        this.alertText = document.getElementById(options.alertTextId);
        this.alertIcon = document.getElementById(options.alertIconId);
        
        this.options = {
            requiredFields: options.requiredFields || [],
            successMessage: options.successMessage || 'Data saved successfully!',
            redirectUrl: options.redirectUrl || null,
            redirectDelay: options.redirectDelay || 2000,
            resetFormOnSuccess: options.resetFormOnSuccess !== false,
            submitButtonText: options.submitButtonText || 'Submit',
            submitButtonLoadingText: options.submitButtonLoadingText || 'Saving...',
            onSuccess: options.onSuccess || null,
            onError: options.onError || null,
            onValidationError: options.onValidationError || null,
            customValidation: options.customValidation || null,
            ...options
        };

        this.init();
    }

    init() {
        if (!this.form) {
            console.error('Form not found');
            return;
        }

        this.bindEvents();
    }

    bindEvents() {
        // Form submission
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));

        // Clear validation errors on input
        const allInputs = this.form.querySelectorAll('input, select, textarea');
        allInputs.forEach(input => {
            input.addEventListener('input', () => {
                if (input.classList.contains('is-invalid')) {
                    input.classList.remove('is-invalid');
                }
            });
        });
    }

    handleSubmit(e) {
        e.preventDefault();
        this.hideAlert();

        // Validate form
        if (!this.validateForm()) {
            return;
        }

        // Custom validation if provided
        if (this.options.customValidation && !this.options.customValidation()) {
            return;
        }

        // Disable form and show loading state
        this.toggleFormState(true);

        // Create FormData object
        const formData = new FormData(this.form);

        // Submit via AJAX
        this.submitForm(formData);
    }

    validateForm() {
        let isValid = true;
        let firstInvalidField = null;

        this.options.requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && !field.value.trim()) {
                field.classList.add('is-invalid');
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
                isValid = false;
            } else if (field) {
                field.classList.remove('is-invalid');
            }
        });

        if (!isValid && firstInvalidField) {
            firstInvalidField.focus();
            this.showAlert('danger', 'Please fill in all required fields.');
        }

        return isValid;
    }

    submitForm(formData) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', this.form.action, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        // Add CSRF token if available
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken.getAttribute('content'));
        }

        xhr.onreadystatechange = () => this.handleResponse(xhr);
        xhr.onerror = () => this.handleNetworkError();
        xhr.send(formData);
    }

    handleResponse(xhr) {
        if (xhr.readyState !== 4) return;

        // Re-enable form
        this.toggleFormState(false);

        if (xhr.status === 200) {
            this.handleSuccess(xhr);
        } else if (xhr.status === 422) {
            this.handleValidationErrors(xhr);
        } else {
            this.handleError(xhr);
        }
    }

    handleSuccess(xhr) {
        try {
            const response = JSON.parse(xhr.responseText);
            
            if (response.success) {
                const message = response.message || this.options.successMessage;
                this.showAlert('success', message);

                // Custom success callback
                if (this.options.onSuccess) {
                    this.options.onSuccess(response);
                }

                // Reset form if enabled
                if (this.options.resetFormOnSuccess) {
                    setTimeout(() => {
                        this.form.reset();
                        this.hideAlert();
                        
                        // Redirect if URL provided
                        if (this.options.redirectUrl) {
                            setTimeout(() => {
                                window.location.href = this.options.redirectUrl;
                            }, this.options.redirectDelay);
                        }
                    }, 2000);
                }
            } else {
                this.showAlert('danger', response.message || 'Operation failed. Please try again.');
            }
        } catch (error) {
            this.showAlert('danger', 'An error occurred while processing the response.');
        }
    }

    handleValidationErrors(xhr) {
        try {
            const response = JSON.parse(xhr.responseText);
            let errorMessage = 'Validation failed:';
            
            if (response.errors) {
                Object.keys(response.errors).forEach(field => {
                    const fieldElement = document.getElementById(field);
                    if (fieldElement) {
                        fieldElement.classList.add('is-invalid');
                    }
                    errorMessage += `\n- ${response.errors[field][0]}`;
                });
            }
            
            this.showAlert('danger', errorMessage);

            // Custom validation error callback
            if (this.options.onValidationError) {
                this.options.onValidationError(response);
            }
        } catch (error) {
            this.showAlert('danger', 'Validation failed. Please check your input and try again.');
        }
    }

    handleError(xhr) {
        const message = 'An error occurred. Please try again.';
        this.showAlert('danger', message);

        // Custom error callback
        if (this.options.onError) {
            this.options.onError(xhr);
        }
    }

    handleNetworkError() {
        this.toggleFormState(false);
        this.showAlert('danger', 'Network error. Please check your connection and try again.');
    }

    showAlert(type, message) {
        if (!this.alertContainer) return;

        this.alertContainer.className = 'd-block';
        this.alertMessage.className = `alert alert-${type} alert-dismissible fade show`;
        this.alertText.textContent = message;
        
        if (this.alertIcon) {
            if (type === 'success') {
                this.alertIcon.className = 'ti ti-check-circle me-2';
            } else {
                this.alertIcon.className = 'ti ti-alert-circle me-2';
            }
        }

        // Scroll to show the alert
        const card = document.querySelector('.card');
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    hideAlert() {
        if (this.alertContainer) {
            this.alertContainer.className = 'd-none';
        }
    }

    toggleFormState(disabled) {
        const formElements = this.form.querySelectorAll('input, select, textarea, button');
        formElements.forEach(element => {
            element.disabled = disabled;
        });

        if (disabled) {
            if (this.loadingSpinner) {
                this.loadingSpinner.classList.remove('d-none');
            }
            if (this.submitBtn) {
                this.submitBtn.innerHTML = `<i class="ti ti-check me-1"></i>${this.options.submitButtonLoadingText} <span class="spinner-border spinner-border-sm ms-2" role="status"><span class="visually-hidden">Loading...</span></span>`;
            }
        } else {
            if (this.loadingSpinner) {
                this.loadingSpinner.classList.add('d-none');
            }
            if (this.submitBtn) {
                this.submitBtn.innerHTML = `<i class="ti ti-check me-1"></i>${this.options.submitButtonText}`;
            }
        }
    }

    // Public methods
    reset() {
        this.form.reset();
        this.hideAlert();
        this.toggleFormState(false);
    }

    disable() {
        this.toggleFormState(true);
    }

    enable() {
        this.toggleFormState(false);
    }
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = AjaxFormHandler;
} else {
    window.AjaxFormHandler = AjaxFormHandler;
}

// Utility function for quick setup
function initAjaxForm(options) {
    return new AjaxFormHandler(options);
}

if (typeof window !== 'undefined') {
    window.initAjaxForm = initAjaxForm;
}
