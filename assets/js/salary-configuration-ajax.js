/**
 * Salary Configuration AJAX System
 * Enhanced with spinner support and comprehensive CRUD operations
 */

'use strict';

// Global variables
let salaryConfigAjax = {
    // Configuration
    config: {
        csrfToken: $('meta[name="csrf-token"]').attr('content'),
        baseUrl: '/app/settings/salary-configuration',
        ajaxUrl: '/app/settings/salary-configuration/ajax'
    },

    // Initialize the system
    init: function() {
        this.bindEvents();
        this.initializeComponents();
    },

    // Bind all event handlers
    bindEvents: function() {
        const self = this;

        // Filter form submission
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            const url = `${self.config.baseUrl}?${formData}`;
            window.location.href = url;
        });

        // Reset filters
        $('#reset-filters').on('click', function() {
            $('#filter-form')[0].reset();
            $('.select2').val(null).trigger('change');
            window.location.href = self.config.baseUrl;
        });

        // Create new setting
        $('#create-setting-btn').on('click', function() {
            self.populateCreateModal();
            $('#createModal').modal('show');
        });

        // Handle create form submission
        $('#create-form').on('submit', function(e) {
            e.preventDefault();
            self.handleCreate();
        });

        // Handle edit form submission
        $('#edit-form').on('submit', function(e) {
            e.preventDefault();
            self.handleEdit();
        });

        // Toggle status with AJAX
        $(document).on('click', '.toggle-status', function(e) {
            e.preventDefault();
            self.handleToggleStatus($(this));
        });

        // Delete setting with AJAX
        $(document).on('click', '.delete-setting', function(e) {
            e.preventDefault();
            self.handleDelete($(this));
        });

        // Edit setting with AJAX
        $(document).on('click', '.edit-setting', function(e) {
            e.preventDefault();
            self.handleEditModal($(this));
        });

        // Salary calculator
        $('#calculate-salary').on('click', function() {
            self.handleSalaryCalculation();
        });
    },

    // Initialize components
    initializeComponents: function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: "Select an option",
            allowClear: true
        });
    },

    // Spinner utility functions
    showSpinner: function(element, text = 'Loading...') {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', true);
        element.data('original-text', element.html());
        element.html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${text}`);
    },

    hideSpinner: function(element, originalText = null) {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', false);
        const text = originalText || element.data('original-text') || 'Save';
        element.html(text);
    },

    // AJAX utility function
    makeAjaxRequest: function(url, method, data, successCallback, errorCallback) {
        $.ajax({
            url: url,
            type: method,
            data: data,
            headers: {
                'X-CSRF-TOKEN': this.config.csrfToken
            },
            success: successCallback,
            error: errorCallback
        });
    },

    // Handle create operation
    handleCreate: function() {
        const formData = $('#create-form').serialize();
        const submitBtn = $('#create-submit-btn');
        
        this.showSpinner(submitBtn, 'Creating...');
        
        this.makeAjaxRequest(
            `${this.config.ajaxUrl}/store`,
            'POST',
            formData,
            (response) => {
                this.hideSpinner(submitBtn);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#createModal').modal('hide');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    this.showError('Error!', response.error || 'Failed to create setting');
                }
            },
            (xhr) => {
                this.hideSpinner(submitBtn);
                const errorMessage = this.getErrorMessage(xhr, 'Error creating setting. Please try again.');
                this.showError('Error!', errorMessage);
            }
        );
    },

    // Handle edit operation
    handleEdit: function() {
        const formData = $('#edit-form').serialize();
        const settingId = $('#edit-form').data('setting-id');
        const submitBtn = $('#edit-submit-btn');
        
        this.showSpinner(submitBtn, 'Updating...');
        
        this.makeAjaxRequest(
            `${this.config.ajaxUrl}/${settingId}`,
            'PUT',
            formData,
            (response) => {
                this.hideSpinner(submitBtn);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    $('#editModal').modal('hide');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    this.showError('Error!', response.error || 'Failed to update setting');
                }
            },
            (xhr) => {
                this.hideSpinner(submitBtn);
                const errorMessage = this.getErrorMessage(xhr, 'Error updating setting. Please try again.');
                this.showError('Error!', errorMessage);
            }
        );
    },

    // Handle toggle status
    handleToggleStatus: function(button) {
        const url = button.attr('href');
        const row = button.closest('tr');
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to toggle the status of this setting?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, toggle it!'
        }).then((result) => {
            if (result.isConfirmed) {
                this.showSpinner(button, 'Toggling...');
                
                this.makeAjaxRequest(
                    url.replace('monthly-salary-settings', 'monthly-salary-settings/ajax'),
                    'PATCH',
                    {},
                    (response) => {
                        this.hideSpinner(button);
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            // Update the status badge
                            this.updateStatusBadge(row, response.setting.is_active);
                        } else {
                            this.showError('Error!', response.error || 'Failed to toggle status');
                        }
                    },
                    (xhr) => {
                        this.hideSpinner(button);
                        const errorMessage = this.getErrorMessage(xhr, 'Error updating status. Please try again.');
                        this.showError('Error!', errorMessage);
                    }
                );
            }
        });
    },

    // Handle delete operation
    handleDelete: function(button) {
        const url = button.attr('href');
        const row = button.closest('tr');
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this! This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                this.makeAjaxRequest(
                    url.replace('monthly-salary-settings', 'monthly-salary-settings/ajax'),
                    'DELETE',
                    {},
                    (response) => {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            this.removeTableRow(row);
                        } else {
                            this.showError('Error!', response.error || 'Failed to delete setting');
                        }
                    },
                    (xhr) => {
                        const errorMessage = this.getErrorMessage(xhr, 'Error deleting setting. Please try again.');
                        this.showError('Error!', errorMessage);
                    }
                );
            }
        });
    },

    // Handle edit modal
    handleEditModal: function(button) {
        const url = button.attr('href');
        
        this.showSpinner(button, 'Loading...');
        
        this.makeAjaxRequest(
            url.replace('monthly-salary-settings', 'monthly-salary-settings/ajax'),
            'GET',
            {},
            (response) => {
                this.hideSpinner(button);
                if (response.success) {
                    this.populateEditModal(response.setting, response.months, response.years);
                    $('#editModal').modal('show');
                } else {
                    this.showError('Error!', response.error || 'Failed to load setting data');
                }
            },
            (xhr) => {
                this.hideSpinner(button);
                const errorMessage = this.getErrorMessage(xhr, 'Error loading setting data. Please try again.');
                this.showError('Error!', errorMessage);
            }
        );
    },

    // Handle salary calculation
    handleSalaryCalculation: function() {
        const formData = $('#salary-calculator-form').serialize();
        
        this.makeAjaxRequest(
            `${this.config.baseUrl}/calculate`,
            'POST',
            formData,
            (response) => {
                this.displayCalculationResult(response.calculation);
            },
            (xhr) => {
                const errorMessage = this.getErrorMessage(xhr, 'Error calculating salary. Please try again.');
                this.showError('Error!', errorMessage);
            }
        );
    },

    // Populate create modal
    populateCreateModal: function() {
        $('#create-form')[0].reset();
        $('#create-form').removeData('setting-id');
        $('#createModal .modal-title').text('Create New Salary Setting');
        $('#create-submit-btn').text('Create Setting');
    },

    // Populate edit modal
    populateEditModal: function(setting, months, years) {
        $('#edit-form').data('setting-id', setting.id);
        $('#edit-form #year').val(setting.year);
        $('#edit-form #month').val(setting.month);
        $('#edit-form #total_working_days').val(setting.total_working_days);
        $('#edit-form #official_holidays').val(setting.official_holidays);
        $('#edit-form #default_overtime_rate').val(setting.default_overtime_rate);
        $('#edit-form #notes').val(setting.notes);
        $('#edit-form #is_active').prop('checked', setting.is_active);
        
        // Update month and year options
        this.populateSelectOptions('#edit-form #year', years, setting.year);
        this.populateSelectOptions('#edit-form #month', months, setting.month);
        
        $('#editModal .modal-title').text('Edit Salary Setting');
        $('#edit-submit-btn').text('Update Setting');
    },

    // Populate select options
    populateSelectOptions: function(selector, options, selectedValue) {
        const select = $(selector);
        select.empty();
        
        if (Array.isArray(options)) {
            options.forEach(function(option) {
                select.append(`<option value="${option}">${option}</option>`);
            });
        } else {
            Object.keys(options).forEach(function(key) {
                select.append(`<option value="${key}">${options[key]}</option>`);
            });
        }
        
        select.val(selectedValue);
    },

    // Update status badge
    updateStatusBadge: function(row, isActive) {
        const statusBadge = row.find('.status-badge');
        if (isActive) {
            statusBadge.removeClass('bg-secondary').addClass('bg-success').text('Active');
        } else {
            statusBadge.removeClass('bg-success').addClass('bg-secondary').text('Inactive');
        }
    },

    // Remove table row with animation
    removeTableRow: function(row) {
        row.fadeOut(300, function() {
            $(this).remove();
            // Check if table is empty
            if ($('tbody tr').length === 0) {
                $('tbody').append('<tr><td colspan="8" class="text-center">No salary settings found.</td></tr>');
            }
        });
    },

    // Display calculation result
    displayCalculationResult: function(calculation) {
        const html = `
            <tr><td><strong>Basic Salary</strong></td><td class="text-end">${calculation.basic_salary.toFixed(2)}</td></tr>
            <tr><td><strong>Daily Rate</strong></td><td class="text-end">${calculation.daily_rate.toFixed(2)}</td></tr>
            <tr><td><strong>Actual Present Days</strong></td><td class="text-end">${calculation.actual_present_days}</td></tr>
            <tr><td><strong>Base Salary</strong></td><td class="text-end">${calculation.base_salary.toFixed(2)}</td></tr>
            <tr><td><strong>Overtime Hours</strong></td><td class="text-end">${calculation.overtime_hours}</td></tr>
            <tr><td><strong>Overtime Amount</strong></td><td class="text-end">${calculation.overtime_amount.toFixed(2)}</td></tr>
            <tr><td><strong>Deductions</strong></td><td class="text-end">${calculation.deductions.toFixed(2)}</td></tr>
            <tr class="table-primary"><td><strong>Total Salary</strong></td><td class="text-end"><strong>${calculation.total_salary.toFixed(2)}</strong></td></tr>
        `;
        $('#result-table').html(html);
        $('#calculation-result').show();
    },

    // Show error message
    showError: function(title, message) {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message
        });
    },

    // Get error message from response
    getErrorMessage: function(xhr, defaultMessage) {
        if (xhr.responseJSON && xhr.responseJSON.error) {
            return xhr.responseJSON.error;
        }
        return defaultMessage;
    }
};

// Initialize when document is ready
$(document).ready(function() {
    salaryConfigAjax.init();
});
