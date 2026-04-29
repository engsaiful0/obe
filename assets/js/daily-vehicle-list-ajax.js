/**
 * Daily Vehicle List AJAX System
 * Enhanced with spinner support and comprehensive filtering
 */

'use strict';

let dt_basic, filterOptions = {};

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
    },
    
    showTable: function() {
        if (dt_basic) {
            dt_basic.processing(true);
        }
    },
    
    hideTable: function() {
        if (dt_basic) {
            dt_basic.processing(false);
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
                if (options.showTableSpinner) {
                    SpinnerUtils.showTable();
                }
            },
            complete: function() {
                if (options.showSpinner && options.spinnerElement) {
                    SpinnerUtils.hide(options.spinnerElement);
                }
                if (options.showTableSpinner) {
                    SpinnerUtils.hideTable();
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
    initializeDataTable();
    initializeFilters();
    initializeEventHandlers();
    loadFilterOptions();
});

// Initialize DataTable
function initializeDataTable() {
    dt_basic = $('#dailyVehicleListTable').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ajax: {
            url: '/app/daily-vehicle-lists/data',
            type: 'GET',
            data: function(d) {
                return $.extend({}, d, getFilterData());
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', xhr);
                NotificationUtils.showAlert('error', 'Failed to load data. Please try again.');
            }
        },
        columns: [
            { data: 'list_date', name: 'list_date' },
            { data: 'vehicle', name: 'vehicle' },
            { data: 'bus_type', name: 'bus_type' },
            { data: 'start_stoppage', name: 'start_stoppage' },
            { data: 'end_stoppage', name: 'end_stoppage' },
            { data: 'start_time', name: 'start_time' },
            { data: 'driver', name: 'driver' },
            { data: 'assistant', name: 'assistant' },
            { data: 'remarks', name: 'remarks' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 0,
                render: function(data, type, row) {
                    return new Date(row.list_date).toLocaleDateString();
                }
            },
            {
                targets: 1,
                render: function(data, type, row) {
                    return `${row.vehicle.registration_number} - ${row.vehicle.model_name}`;
                }
            },
            {
                targets: 2,
                render: function(data, type, row) {
                    return row.vehicle.vehicle_sub_type?.vehicle_sub_type_name || 'N/A';
                }
            },
            {
                targets: 3,
                render: function(data, type, row) {
                    return row.start_stoppage?.stoppage_name || 'N/A';
                }
            },
            {
                targets: 4,
                render: function(data, type, row) {
                    return row.end_stoppage?.stoppage_name || 'N/A';
                }
            },
            {
                targets: 5,
                render: function(data, type, row) {
                    return new Date('1970-01-01T' + row.start_time).toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    });
                }
            },
            {
                targets: 6,
                render: function(data, type, row) {
                    return row.driver?.full_name || 'N/A';
                }
            },
            {
                targets: 7,
                render: function(data, type, row) {
                    return row.assistant?.assistant_name || 'N/A';
                }
            },
            {
                targets: 8,
                render: function(data, type, row) {
                    return row.remarks || 'N/A';
                }
            },
            {
                targets: 9,
                render: function(data, type, row) {
                    return `
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="/app/daily-vehicle-lists/${row.id}/edit">
                                    <i class="ti ti-pencil me-1"></i>Edit
                                </a>
                                <a class="dropdown-item text-danger" href="#" onclick="deleteDailyVehicleList(${row.id})">
                                    <i class="ti ti-trash me-1"></i>Delete
                                </a>
                            </div>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        }
    });
}

// Initialize filters
function initializeFilters() {
    // Initialize date pickers
    $('#filterDate').flatpickr({
        dateFormat: 'Y-m-d',
        allowInput: true
    });
    
    $('#filterDateFrom').flatpickr({
        dateFormat: 'Y-m-d',
        allowInput: true
    });
    
    $('#filterDateTo').flatpickr({
        dateFormat: 'Y-m-d',
        allowInput: true
    });
    
    // Initialize Select2 for dropdowns
    $('.form-select').select2({
        placeholder: 'Select an option',
        allowClear: true
    });
}

// Load filter options
function loadFilterOptions() {
    AjaxUtils.request({
        url: '/app/daily-vehicle-lists/filter-options',
        showSpinner: true,
        spinnerElement: '#applyFiltersBtn',
        spinnerText: 'Loading...',
        onSuccess: function(response) {
            populateFilterDropdowns(response);
        }
    });
}

// Populate filter dropdowns
function populateFilterDropdowns(data) {
    // Vehicles
    const vehicleSelect = $('#filterVehicle');
    vehicleSelect.empty().append('<option value="">All Vehicles</option>');
    data.vehicles.forEach(vehicle => {
        vehicleSelect.append(`<option value="${vehicle.id}">${vehicle.registration_number} - ${vehicle.model_name}</option>`);
    });
    
    // Vehicle Sub Types
    const vehicleSubTypeSelect = $('#filterVehicleSubType');
    vehicleSubTypeSelect.empty().append('<option value="">All Types</option>');
    data.vehicle_sub_types.forEach(subType => {
        vehicleSubTypeSelect.append(`<option value="${subType.id}">${subType.vehicle_sub_type_name}</option>`);
    });
    
    // Drivers
    const driverSelect = $('#filterDriver');
    driverSelect.empty().append('<option value="">All Drivers</option>');
    data.drivers.forEach(driver => {
        driverSelect.append(`<option value="${driver.id}">${driver.full_name} (${driver.driver_unique_id})</option>`);
    });
    
    // Assistants
    const assistantSelect = $('#filterAssistant');
    assistantSelect.empty().append('<option value="">All Assistants</option>');
    data.assistants.forEach(assistant => {
        assistantSelect.append(`<option value="${assistant.id}">${assistant.assistant_name} (${assistant.assistant_id})</option>`);
    });
    
    // Stoppages
    const startStoppageSelect = $('#filterStartStoppage');
    const endStoppageSelect = $('#filterEndStoppage');
    startStoppageSelect.empty().append('<option value="">All Stoppages</option>');
    endStoppageSelect.empty().append('<option value="">All Stoppages</option>');
    data.stoppages.forEach(stoppage => {
        startStoppageSelect.append(`<option value="${stoppage.id}">${stoppage.stoppage_name}</option>`);
        endStoppageSelect.append(`<option value="${stoppage.id}">${stoppage.stoppage_name}</option>`);
    });
}

// Get filter data
function getFilterData() {
    return {
        date: $('#filterDate').val(),
        date_from: $('#filterDateFrom').val(),
        date_to: $('#filterDateTo').val(),
        vehicle_id: $('#filterVehicle').val(),
        vehicle_sub_type_id: $('#filterVehicleSubType').val(),
        driver_id: $('#filterDriver').val(),
        assistant_id: $('#filterAssistant').val(),
        start_stoppage_id: $('#filterStartStoppage').val(),
        end_stoppage_id: $('#filterEndStoppage').val()
    };
}

// Initialize event handlers
function initializeEventHandlers() {
    // Add button
    $('#addDailyVehicleListBtn').on('click', function() {
        window.location.href = '/app/daily-vehicle-lists/create';
    });
    
    // Apply filters
    $('#applyFiltersBtn').on('click', function() {
        dt_basic.ajax.reload();
    });
    
    // Clear filters
    $('#clearFiltersBtn').on('click', function() {
        $('#filterDate, #filterDateFrom, #filterDateTo, #filterVehicle, #filterVehicleSubType, #filterDriver, #filterAssistant, #filterStartStoppage, #filterEndStoppage').val('').trigger('change');
        dt_basic.ajax.reload();
    });
    
    // Export PDF
    $('#exportPdfBtn').on('click', function() {
        const filterData = getFilterData();
        const queryString = $.param(filterData);
        window.open(`/app/daily-vehicle-lists/export-pdf?${queryString}`, '_blank');
    });
}

// Delete daily vehicle list
function deleteDailyVehicleList(id) {
    if (confirm('Are you sure you want to delete this daily vehicle list?')) {
        AjaxUtils.request({
            url: `/app/daily-vehicle-lists/${id}`,
            type: 'DELETE',
            showSpinner: true,
            spinnerElement: '#exportPdfBtn',
            spinnerText: 'Deleting...',
            onSuccess: function(response) {
                NotificationUtils.showAlert('success', response.message);
                dt_basic.ajax.reload();
            }
        });
    }
}

// Global functions for external access
window.deleteDailyVehicleList = deleteDailyVehicleList;
