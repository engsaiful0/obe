/**
 * Bus Route DataTables with Enhanced AJAX and Spinner Support
 */

'use strict';

let fv, offCanvasEl, stoppages = [], dt_basic;

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
        let errorMessage = 'An error occurred while processing your request.';
        
        if (xhr.responseJSON) {
            if (xhr.responseJSON.errors) {
                // Laravel validation errors
                const errors = xhr.responseJSON.errors;
                const errorMessages = Object.values(errors).flat().join('<br>');
                errorMessage = errorMessages;
            } else if (xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            } else if (xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
        } else if (xhr.status === 422) {
            errorMessage = 'Validation failed. Please check your input.';
        } else if (xhr.status === 404) {
            errorMessage = 'The requested resource was not found.';
        } else if (xhr.status === 500) {
            errorMessage = 'Internal server error. Please try again later.';
        }
        
        if (options.onError) {
            options.onError(errorMessage);
        } else {
            toastr.error(errorMessage);
        }
    }
};

// Load stoppages with enhanced error handling
function loadStoppages() {
    const stoppageUrl = window.AppUtils.buildUrl('app/settings/get-stoppages');
    console.log('Loading stoppages from:', stoppageUrl);
    
    $.ajax({
        url: stoppageUrl,
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Stoppages loaded:', response);
            stoppages = response.data || [];
            populateStoppageDropdowns();
        },
        error: function(xhr, status, error) {
            console.error('Error loading stoppages:', {xhr, status, error});
            console.error('URL attempted:', stoppageUrl);
            console.error('Response:', xhr.responseText);
            toastr.error('Failed to load stoppages. Please refresh the page.');
        }
    });
}

function populateStoppageDropdowns() {
    console.log('Populating dropdowns with stoppages:', stoppages);
    
    const startSelect = document.getElementById('start_stoppage_id');
    const endSelect = document.getElementById('end_stoppage_id');
    
    if (!startSelect || !endSelect) {
        console.error('Dropdown elements not found');
        return;
    }
    
    // Clear existing options except the first one
    startSelect.innerHTML = '<option value="">Select Start Stoppage</option>';
    endSelect.innerHTML = '<option value="">Select End Stoppage</option>';
    
    // Populate both dropdowns with stoppages
    if (stoppages && stoppages.length > 0) {
        stoppages.forEach(function(stoppage) {
            const startOption = document.createElement('option');
            startOption.value = stoppage.id;
            startOption.textContent = stoppage.stoppage_name;
            startSelect.appendChild(startOption);
            
            const endOption = document.createElement('option');
            endOption.value = stoppage.id;
            endOption.textContent = stoppage.stoppage_name;
            endSelect.appendChild(endOption);
        });
        console.log('Dropdowns populated successfully');
    } else {
        console.warn('No stoppages available to populate dropdowns');
        toastr.warning('No stoppages available. Please add stoppages first.');
    }
}

// Enhanced form submission with validation
function submitForm(isEdit = false) {
    const form = document.getElementById('form-add-new-record');
    const formData = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        route_name: $('#route_name').val(),
        description: $('#description').val(),
        start_stoppage_id: $('#start_stoppage_id').val(),
        end_stoppage_id: $('#end_stoppage_id').val(),
        distance: $('#distance').val(),
        estimated_time: $('#estimated_time').val(),
        is_active: $('#is_active').is(':checked') ? 1 : 0
    };
    
    console.log('Form data to be sent:', formData);
    console.log('Available URLs:', window.busRouteUrls);
    
    // Basic validation
    if (!formData.route_name.trim()) {
        toastr.error('Route name is required.');
        return;
    }
    
    if (!formData.start_stoppage_id) {
        toastr.error('Start stoppage is required.');
        return;
    }
    
    if (!formData.end_stoppage_id) {
        toastr.error('End stoppage is required.');
        return;
    }
    
    if (formData.start_stoppage_id === formData.end_stoppage_id) {
        toastr.error('Start and end stoppages must be different.');
        return;
    }
    
    const id = $('#form-add-new-record').attr('data-id');
    const url = isEdit ? (window.busRouteUrls.update + '/' + id) : window.busRouteUrls.store;
    const method = isEdit ? 'PUT' : 'POST';
    const message = isEdit ? 'Bus route updated successfully.' : 'Bus route created successfully.';
    
    console.log('Submitting to URL:', url, 'Method:', method);
    
    const $submitBtn = $('#form-add-new-record button[type="submit"]');
    
    // Show spinner
    SpinnerUtils.show($submitBtn, isEdit ? 'Updating...' : 'Creating...');
    
    $.ajax({
        url: url,
        type: method,
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Success response:', response);
            toastr.success(message);
            
            // Reload data table
            if (dt_basic) {
                dt_basic.ajax.reload();
            }
            
            // Close modal and reset form
            if (offCanvasEl) {
                offCanvasEl.hide();
            }
            
            resetForm();
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {xhr, status, error});
            console.error('Response:', xhr.responseText);
            
            let errorMessage = 'An error occurred while processing your request.';
            
            if (xhr.responseJSON) {
                if (xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    const errorMessages = Object.values(errors).flat().join('<br>');
                    errorMessage = errorMessages;
                } else if (xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
            }
            
            toastr.error(errorMessage);
        },
        complete: function() {
            // Hide spinner
            SpinnerUtils.hide($submitBtn);
        }
    });
}

function resetForm() {
    $('#form-add-new-record').removeAttr('data-id');
    $('#route_name').val('');
    $('#description').val('');
    $('#start_stoppage_id').val('');
    $('#end_stoppage_id').val('');
    $('#distance').val('');
    $('#estimated_time').val('');
    $('#is_active').prop('checked', true);
    document.querySelector('#modal-title').textContent = 'Add New Bus Route';
}

// Enhanced delete function with confirmation
function deleteRoute(routeId, routeName) {
    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete the route "${routeName}". This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then(function (result) {
        if (result.value) {
            AjaxUtils.request({
                url: window.busRouteUrls.destroy + '/' + routeId,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                showTableSpinner: true,
                onSuccess: function(response) {
                    toastr.success('Bus route deleted successfully.');
                    if (dt_basic) {
                        dt_basic.ajax.reload();
                    }
                }
            });
        }
    });
}

// Enhanced edit function
function editRoute(routeData) {
    offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
    
    // Store the route data for later use
    window.currentEditRouteData = routeData;
    
    // Reload stoppages when opening edit modal
    loadStoppagesForEdit();
    
    // Populate non-dropdown form fields immediately
    document.querySelector('#route_name').value = routeData.route_name || '';
    document.querySelector('#description').value = routeData.description || '';
    document.querySelector('#distance').value = routeData.distance || '';
    document.querySelector('#estimated_time').value = routeData.estimated_time || '';
    document.querySelector('#is_active').checked = routeData.is_active;
    
    $('#form-add-new-record').attr('data-id', routeData.id);
    document.querySelector('#modal-title').textContent = 'Edit Bus Route';
    offCanvasEl.show();
}

// Load stoppages specifically for edit mode
function loadStoppagesForEdit() {
    const stoppageUrl = window.AppUtils.buildUrl('app/settings/get-stoppages');
    console.log('Loading stoppages for edit from:', stoppageUrl);
    
    $.ajax({
        url: stoppageUrl,
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Stoppages loaded for edit:', response);
            stoppages = response.data || [];
            populateStoppageDropdownsForEdit();
        },
        error: function(xhr, status, error) {
            console.error('Error loading stoppages for edit:', {xhr, status, error});
            console.error('URL attempted:', stoppageUrl);
            console.error('Response:', xhr.responseText);
            toastr.error('Failed to load stoppages. Please refresh the page.');
        }
    });
}

// Populate dropdowns for edit mode with pre-selection
function populateStoppageDropdownsForEdit() {
    console.log('Populating dropdowns for edit with stoppages:', stoppages);
    
    const startSelect = document.getElementById('start_stoppage_id');
    const endSelect = document.getElementById('end_stoppage_id');
    
    if (!startSelect || !endSelect) {
        console.error('Dropdown elements not found');
        return;
    }
    
    // Clear existing options except the first one
    startSelect.innerHTML = '<option value="">Select Start Stoppage</option>';
    endSelect.innerHTML = '<option value="">Select End Stoppage</option>';
    
    // Populate both dropdowns with stoppages
    if (stoppages && stoppages.length > 0) {
        stoppages.forEach(function(stoppage) {
            const startOption = document.createElement('option');
            startOption.value = stoppage.id;
            startOption.textContent = stoppage.stoppage_name;
            startSelect.appendChild(startOption);
            
            const endOption = document.createElement('option');
            endOption.value = stoppage.id;
            endOption.textContent = stoppage.stoppage_name;
            endSelect.appendChild(endOption);
        });
        console.log('Dropdowns populated for edit successfully');
        
        // Now set the selected values after dropdowns are populated
        if (window.currentEditRouteData) {
            const routeData = window.currentEditRouteData;
            console.log('Setting selected values for edit:', {
                start_stoppage_id: routeData.start_stoppage_id,
                end_stoppage_id: routeData.end_stoppage_id
            });
            
            // Add a small delay to ensure DOM is updated
            setTimeout(function() {
                // Set the selected values
                startSelect.value = routeData.start_stoppage_id || '';
                endSelect.value = routeData.end_stoppage_id || '';
                
                // Verify the values were set
                console.log('Values set - Start:', startSelect.value, 'End:', endSelect.value);
                
                // If values didn't set properly, try again with a longer delay
                if (!startSelect.value || !endSelect.value) {
                    console.log('Values not set properly, retrying...');
                    setTimeout(function() {
                        startSelect.value = routeData.start_stoppage_id || '';
                        endSelect.value = routeData.end_stoppage_id || '';
                        console.log('Retry - Values set - Start:', startSelect.value, 'End:', endSelect.value);
                    }, 200);
                }
                
                // Clear the stored data
                window.currentEditRouteData = null;
            }, 100);
        }
    } else {
        console.warn('No stoppages available to populate dropdowns for edit');
        toastr.warning('No stoppages available. Please add stoppages first.');
    }
}

// Initialize the application
document.addEventListener('DOMContentLoaded', function (e) {
    // Load stoppages on page load
    setTimeout(function() {
        loadStoppages();
    }, 500);
    
    // Initialize form and modal
    (function () {
        const formAddNewRecord = document.getElementById('form-add-new-record');

        setTimeout(() => {
            const newRecord = document.querySelector('.create-new'),
                offCanvasElement = document.querySelector('#add-new-record');

            // To open offCanvas, to add new record
            if (newRecord) {
                newRecord.addEventListener('click', function () {
                    offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
                    resetForm();
                    loadStoppages();
                    offCanvasEl.show();
                });
            }
        }, 200);

        // Add direct form submit handler as fallback
        if (formAddNewRecord) {
            formAddNewRecord.addEventListener('submit', function(e) {
                console.log('Direct form submit event triggered');
                e.preventDefault();
                
                // Manual validation
                const routeName = $('#route_name').val();
                const startStoppage = $('#start_stoppage_id').val();
                const endStoppage = $('#end_stoppage_id').val();
                
                console.log('Manual validation check:', {
                    route_name: routeName,
                    start_stoppage_id: startStoppage,
                    end_stoppage_id: endStoppage,
                    hasRouteName: !!routeName,
                    hasStartStoppage: !!startStoppage,
                    hasEndStoppage: !!endStoppage,
                    differentStoppages: startStoppage !== endStoppage
                });
                
                if (!routeName || !startStoppage || !endStoppage) {
                    console.error('Missing required fields');
                    toastr.error('Please fill in all required fields');
                    return;
                }
                
                if (startStoppage === endStoppage) {
                    console.error('Start and end stoppages are the same');
                    toastr.error('Start and end stoppages must be different');
                    return;
                }
                
                // If validation passes, trigger the save
                console.log('Manual validation passed, triggering save...');
                const id = $('#form-add-new-record').attr('data-id');
                const isEdit = !!id;
                submitForm(isEdit);
            });
        }

        // Enhanced form validation
        console.log('Initializing form validation...');
        fv = FormValidation.formValidation(formAddNewRecord, {
            fields: {
                route_name: {
                    validators: {
                        notEmpty: {
                            message: 'The route name is required'
                        },
                        stringLength: {
                            min: 2,
                            max: 255,
                            message: 'Route name must be between 2 and 255 characters'
                        }
                    }
                },
                start_stoppage_id: {
                    validators: {
                        notEmpty: {
                            message: 'The start stoppage is required'
                        }
                    }
                },
                end_stoppage_id: {
                    validators: {
                        notEmpty: {
                            message: 'The end stoppage is required'
                        },
                        callback: {
                            message: 'Start and end stoppages must be different',
                            callback: function(value, validator, $field) {
                                const startStoppageId = document.getElementById('start_stoppage_id').value;
                                return value !== startStoppageId;
                            }
                        }
                    }
                },
                distance: {
                    validators: {
                        numeric: {
                            message: 'Distance must be a valid number'
                        },
                        greaterThan: {
                            min: 0,
                            message: 'Distance must be greater than or equal to 0'
                        }
                    }
                },
                estimated_time: {
                    validators: {
                        integer: {
                            message: 'Estimated time must be a valid integer'
                        },
                        greaterThan: {
                            min: 0,
                            message: 'Estimated time must be greater than or equal to 0'
                        }
                    }
                }
            },
            plugins: {
                trigger: new FormValidation.plugins.Trigger(),
                bootstrap5: new FormValidation.plugins.Bootstrap5({
                    eleValidClass: '',
                    rowSelector: '.mb-3'
                }),
                submitButton: new FormValidation.plugins.SubmitButton(),
                autoFocus: new FormValidation.plugins.AutoFocus()
            },
            init: instance => {
                console.log('Form validation initialized');
                instance.on('plugins.message.placed', function (e) {
                    if (e.element.parentElement.classList.contains('input-group')) {
                        e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
                    }
                });
                
                instance.on('core.form.valid', function() {
                    console.log('Form validation passed');
                    const id = $('#form-add-new-record').attr('data-id');
                    const isEdit = !!id;
                    console.log('Is edit mode:', isEdit);
                    submitForm(isEdit);
                });
            }
        });
    })();
});

// DataTable initialization
$(function () {
    var dt_basic_table = $('.datatables-basic');

    if (dt_basic_table.length) {
        dt_basic = dt_basic_table.DataTable({
            ajax: {
                url: window.busRouteUrls.getData,
                type: 'GET',
                dataSrc: 'data',
                error: function(xhr, error, thrown) {
                    console.error('DataTable AJAX Error:', {xhr, error, thrown});
                    console.error('URL attempted:', window.busRouteUrls.getData);
                    toastr.error('Failed to load bus routes. Please refresh the page.');
                }
            },
            columns: [
                {
                    data: 'id',
                    name: 'id',
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'route_name' },
                { 
                    data: 'start_stoppage',
                    render: function (data, type, row) {
                        return data ? data.stoppage_name : '-';
                    }
                },
                { 
                    data: 'end_stoppage',
                    render: function (data, type, row) {
                        return data ? data.stoppage_name : '-';
                    }
                },
                { 
                    data: 'distance',
                    render: function (data, type, row) {
                        return data ? data + ' km' : '-';
                    }
                },
                { 
                    data: 'estimated_time',
                    render: function (data, type, row) {
                        return data ? data + ' min' : '-';
                    }
                },
                { 
                    data: 'is_active',
                    render: function (data, type, row) {
                        return data ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                    }
                },
                { data: '' }
            ],
            columnDefs: [
                {
                    // Actions
                    targets: -1,
                    title: 'Actions',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        return (
                            '<div class="d-inline-block">' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon bus-route-edit" title="Edit"><i class="ti ti-pencil ti-md"></i></a>' +
                            '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon delete-record" title="Delete"><i class="ti ti-trash ti-md"></i></a>' +
                            '</div>'
                        );
                    }
                }
            ],
            order: [[0, 'desc']],
            dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-6 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end mt-n6 mt-md-0"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 7,
            lengthMenu: [7, 10, 25, 50, 75, 100],
            language: {
                paginate: {
                    next: '<i class="ti ti-chevron-right ti-sm"></i>',
                    previous: '<i class="ti ti-chevron-left ti-sm"></i>'
                }
            },
            buttons: [
                {
                    text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Route</span>',
                    className: 'create-new btn btn-primary waves-effect waves-light'
                }
            ],
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function (row) {
                            var data = row.data();
                            return 'Details of ' + data['route_name'];
                        }
                    }),
                    type: 'column',
                    renderer: function (api, rowIdx, columns) {
                        var data = $.map(columns, function (col, i) {
                            return col.title !== '' 
                                ? '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '">' +
                                  '<td>' + col.title + ':</td> ' +
                                  '<td>' + col.data + '</td>' +
                                  '</tr>'
                                : '';
                        }).join('');

                        return data ? $('<table class="table"/><tbody />').append(data) : false;
                    }
                }
            },
            initComplete: function (settings, json) {
                $('.card-header').after('<hr class="my-0">');
            }
        });
        
        $('div.head-label').html('<h5 class="card-title mb-0">Bus Routes</h5>');
    }

    // Delete Record Event
    $('.datatables-basic tbody').on('click', '.delete-record', function () {
        var row = dt_basic.row($(this).parents('tr'));
        var data = row.data();
        deleteRoute(data.id, data.route_name);
    });

    // Edit Record Event
    $('.datatables-basic tbody').on('click', '.bus-route-edit', function () {
        var row = dt_basic.row($(this).parents('tr'));
        var data = row.data();
        console.log('Edit clicked, route data:', data);
        editRoute(data);
    });
});