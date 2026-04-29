/**
 * Daily Deployment Plan Form AJAX Handler
 * Handles form submission, dynamic rows, and bus selection
 */

$(document).ready(function() {
    'use strict';

    let rowIndex = 0;
    const busSubTypes = window.deploymentPlanData?.busSubTypes || [];
    const busesBySubType = window.deploymentPlanData?.busesBySubType || [];
    const getBusesUrl = window.deploymentPlanData?.getBusesUrl || '';

    // Initialize date picker
    if ($('#deployment_date').length) {
        $('#deployment_date').flatpickr({
            dateFormat: 'Y-m-d',
            defaultDate: new Date()
        });
    }

    // Initialize row index from existing rows
    if ($('#stoppageRows .stoppage-row').length > 0) {
        rowIndex = $('#stoppageRows .stoppage-row').length;
    }

    // Load buses for existing rows in edit mode
    if (window.deploymentPlanData?.existingItems && window.deploymentPlanData.existingItems.length > 0) {
        // Buses are already loaded in the edit view, just need to ensure they're available
        // The edit view already has buses pre-populated
    } else {
        // For create mode, load buses for the first row
        $('#stoppageRows .stoppage-row').first().find('.bus-select').each(function() {
            const subTypeId = $(this).data('sub-type-id');
            const busSelect = $(this).closest('td').find('.bus-assignment-select');
            loadBusesForSubType(subTypeId, busSelect);
        });
    }

    // Add stoppage row
    $('#addStoppageRow').on('click', function() {
        addStoppageRow();
    });

    // Remove row
    $(document).on('click', '.remove-row', function() {
        if ($('#stoppageRows .stoppage-row').length > 1) {
            $(this).closest('.stoppage-row').remove();
            updateRowIndices();
        } else {
            showAlert('error', 'At least one stoppage row is required.');
        }
    });

    // Load buses when bus sub-type is selected (for dynamically added rows)
    $(document).on('change', '.bus-select', function() {
        const subTypeId = $(this).data('sub-type-id');
        const busSelect = $(this).closest('td').find('.bus-assignment-select');
        loadBusesForSubType(subTypeId, busSelect);
    });

    // Form submission
    $('#deploymentPlanForm').on('submit', function(e) {
        e.preventDefault();
        submitForm();
    });

    // Fix dropdown width to match button width
    $(document).on('show.bs.dropdown', '.bus-multi-dropdown', function() {
        const $dropdown = $(this);
        const $button = $dropdown.find('.dropdown-toggle');
        const buttonWidth = $button.outerWidth();
        const $menu = $dropdown.find('.dropdown-menu');
        $menu.css({
            'width': buttonWidth + 'px',
            'min-width': buttonWidth + 'px',
            'max-width': buttonWidth + 'px'
        });
    });

    /**
     * Add a new stoppage row
     */
    function addStoppageRow() {
        const template = $('#stoppageRowTemplate').html();
        const newRow = template.replace(/INDEX/g, rowIndex);
        $('#stoppageRows').append(newRow);
        
        // Load buses for all sub-types in the new row
        const newRowElement = $('#stoppageRows .stoppage-row').last();
        newRowElement.find('.bus-select').each(function() {
            const subTypeId = $(this).data('sub-type-id');
            const busSelect = $(this).closest('td').find('.bus-assignment-select');
            // Buses should already be in cache from initial load
            const cachedData = busesBySubType.find(item => item.sub_type_id == subTypeId);
            if (cachedData && cachedData.buses) {
                populateBusSelect(busSelect, cachedData.buses);
            } else {
                loadBusesForSubType(subTypeId, busSelect);
            }
        });

        rowIndex++;
        feather.replace();
    }

    /**
     * Update row indices after removal
     */
    function updateRowIndices() {
        $('#stoppageRows .stoppage-row').each(function(index) {
            $(this).find('select, input').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                }
            });
        });
    }

    /**
     * Load buses for a specific sub-type
     */
    function loadBusesForSubType(subTypeId, busSelectElement) {
        if (!subTypeId || !busSelectElement) return;

        // Show loading state
        busSelectElement.html('<option value="">Loading...</option>').prop('disabled', true);

        // Try to get from cached data first
        const cachedData = busesBySubType.find(item => item.sub_type_id == subTypeId);
        if (cachedData && cachedData.buses) {
            populateBusSelect(busSelectElement, cachedData.buses);
            return;
        }

        // If not in cache, fetch from server
        $.ajax({
            url: getBusesUrl,
            type: 'GET',
            data: { bus_sub_type_id: subTypeId },
            success: function(response) {
                if (response.success && response.buses) {
                    populateBusSelect(busSelectElement, response.buses);
                } else {
                    busSelectElement.html('<option value="">No buses available</option>');
                }
            },
            error: function() {
                busSelectElement.html('<option value="">Error loading buses</option>');
            },
            complete: function() {
                busSelectElement.prop('disabled', false);
            }
        });
    }

    /**
     * Populate bus select dropdown
     */
    function populateBusSelect(selectElement, buses, selectedBusId = null) {
        let options = '<option value="">Select Bus</option>';
        buses.forEach(function(bus) {
            const displayText = bus.model_name + ' (' + bus.registration_number + ')';
            const selected = (selectedBusId && bus.id == selectedBusId) ? 'selected' : '';
            options += `<option value="${bus.id}" ${selected}>${displayText}</option>`;
        });
        selectElement.html(options);
    }

    /**
     * Load buses for existing rows (edit mode)
     */
    function loadBusesForExistingRows() {
        $('#stoppageRows .stoppage-row').each(function(rowIndex) {
            const row = $(this);
            const stoppageId = row.find('.stoppage-select').val();
            
            row.find('.bus-assignment-select').each(function() {
                const subTypeId = $(this).data('sub-type-id');
                
                // Find the bus_id for this stoppage and sub-type combination
                const existingItem = window.deploymentPlanData.existingItems.find(function(item) {
                    return item.stoppage_id == stoppageId && item.bus_sub_type_id == subTypeId;
                });

                loadBusesForSubTypeWithSelection(subTypeId, $(this), existingItem?.bus_id);
            });
        });
    }

    /**
     * Load buses with pre-selected value
     */
    function loadBusesForSubTypeWithSelection(subTypeId, busSelectElement, selectedBusId = null) {
        if (!subTypeId || !busSelectElement) return;

        busSelectElement.html('<option value="">Loading...</option>').prop('disabled', true);

        const cachedData = busesBySubType.find(item => item.sub_type_id == subTypeId);
        if (cachedData && cachedData.buses) {
            populateBusSelectWithSelection(busSelectElement, cachedData.buses, selectedBusId);
            return;
        }

        $.ajax({
            url: getBusesUrl,
            type: 'GET',
            data: { bus_sub_type_id: subTypeId },
            success: function(response) {
                if (response.success && response.buses) {
                    populateBusSelectWithSelection(busSelectElement, response.buses, selectedBusId);
                } else {
                    busSelectElement.html('<option value="">No buses available</option>');
                }
            },
            error: function() {
                busSelectElement.html('<option value="">Error loading buses</option>');
            },
            complete: function() {
                busSelectElement.prop('disabled', false);
            }
        });
    }

    /**
     * Populate bus select with pre-selected value
     */
    function populateBusSelectWithSelection(selectElement, buses, selectedBusId = null) {
        let options = '<option value="">Select Bus</option>';
        buses.forEach(function(bus) {
            const displayText = bus.model_name + ' (' + bus.registration_number + ')';
            const selected = (selectedBusId && bus.id == selectedBusId) ? 'selected' : '';
            options += `<option value="${bus.id}" ${selected}>${displayText}</option>`;
        });
        selectElement.html(options);
    }

    /**
     * Submit form via AJAX
     */
    function submitForm() {
        const form = $('#deploymentPlanForm');
        const formData = new FormData(form[0]);
        const submitBtn = $('#submitBtn');
        const submitSpinner = $('#submitSpinner');
        const submitText = $('#submitText');

        // Validate form
        if (!validateForm()) {
            return false;
        }

        // Show loading spinner
        submitBtn.prop('disabled', true);
        submitSpinner.removeClass('d-none');
        submitText.html('Saving...');

        // Prepare data for submission
        const data = prepareFormData();

        $.ajax({
            url: form.attr('action'),
            type: form.find('input[name="_method"]').val() || 'POST',
            data: data,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                submitBtn.prop('disabled', false);
                submitSpinner.addClass('d-none');
                submitText.html('<i data-feather="save"></i> ' + (form.find('input[name="_method"]').val() ? 'Update' : 'Save') + ' Deployment Plan');

                if (response.success) {
                    toastr.success(response.message || 'Deployment plan saved successfully.');
                    setTimeout(function() {
                        var baseUrl = window.location.origin;
                        var pathName = window.location.pathname;
                        var appIndex = pathName.indexOf('/app/');
                        if (appIndex !== -1) {
                          baseUrl += pathName.substring(0, appIndex);
                        }
                        window.location.href = baseUrl + '/app/deployment-plans/view-daily-deployment-plan';
                      }, 1500); // 1.5 second delay to show success message
                    
                   
                } else {
                    toastr.error(response.message || 'An error occurred while saving.');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false);
                submitSpinner.addClass('d-none');
                submitText.html('<i data-feather="save"></i> ' + (form.find('input[name="_method"]').val() ? 'Update' : 'Save') + ' Deployment Plan');

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON?.errors || {};
                    let errorMessage = 'Validation failed. Please check the form.';
                    
                    if (Object.keys(errors).length > 0) {
                        errorMessage = Object.values(errors).flat().join('<br>');
                    }
                    
                    showAlert('error', errorMessage);
                    displayValidationErrors(errors);
                } else {
                    // Show detailed error message if available (for debugging)
                    let errorMessage = xhr.responseJSON?.message || 'An error occurred while saving the deployment plan.';
                    if (xhr.responseJSON?.error) {
                        errorMessage += '<br><small class="text-muted">Error: ' + xhr.responseJSON.error + '</small>';
                    }
                    showAlert('error', errorMessage);
                    
                    // Log to console for debugging
                    console.error('Deployment plan save error:', xhr.responseJSON);
                }
            }
        });
    }

    /**
     * Prepare form data for submission
     */
    function prepareFormData() {
        const data = new FormData();
        
        // Add common fields
        data.append('deployment_date', $('#deployment_date').val());
        data.append('trip_time_id', $('#trip_time_id').val());
        data.append('bus_user_id', $('#bus_user_id').val());
        data.append('deployment_type_id', $('#deployment_type_id').val());
        data.append('trip_type', $('#trip_type').val());
        data.append('remarks', $('#remarks').val() || '');
        data.append('_token', $('meta[name="csrf-token"]').attr('content'));
        
        if ($('#deploymentPlanForm input[name="_method"]').length) {
            data.append('_method', $('#deploymentPlanForm input[name="_method"]').val());
        }

        // Prepare items array - allow multiple buses per stoppage and sub type (via checkboxes)
        const items = [];
        $('#deploymentTable tbody tr').each(function(index) {
            const row = $(this);
            const stoppageId = row.find('input[name*="[stoppage_id]"]').val();
            
            if (!stoppageId) return;

            const busAssignments = [];

            // Include all checked buses for this stoppage (across sub types)
            row.find('input[type="checkbox"][name*="[bus_assignments]"][name*="[bus_id]"]:checked').each(function() {
                const busId = $(this).val();
                if (!busId) {
                    return;
                }

                const nameAttr = $(this).attr('name');
                const match = nameAttr.match(/\[bus_assignments\]\[(\d+)\]/);
                if (!match) {
                    return;
                }

                const subTypeId = match[1];
                const subTypeIdInput = row.find(`input[name*="[bus_assignments][${subTypeId}][bus_sub_type_id]"]`);
                const busSubTypeId = subTypeIdInput.val() || subTypeId;

                busAssignments.push({
                    bus_sub_type_id: parseInt(busSubTypeId),
                    bus_id: parseInt(busId)
                });
            });

            // Only add item if at least one bus is selected
            if (busAssignments.length > 0) {
                items.push({
                    stoppage_id: parseInt(stoppageId),
                    bus_assignments: busAssignments
                });
            }
        });

        data.append('items', JSON.stringify(items));

        return data;
    }

    /**
     * Validate form
     */
    function validateForm() {
        let isValid = true;
        
        // Clear previous errors
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        $('.bus-validation-error').remove();
        $('.border-danger').removeClass('border-danger');

        // Validate common fields
        if (!$('#deployment_date').val()) {
            showFieldError('#deployment_date', 'Deployment date is required.');
            isValid = false;
        }

        if (!$('#trip_time_id').val()) {
            showFieldError('#trip_time_id', 'Trip time is required.');
            isValid = false;
        }

        if (!$('#bus_user_id').val()) {
            showFieldError('#bus_user_id', 'Bus user is required.');
            isValid = false;
        }

        if (!$('#deployment_type_id').val()) {
            showFieldError('#deployment_type_id', 'Deployment type is required.');
            isValid = false;
        }

        if (!$('#trip_type').val()) {
            showFieldError('#trip_type', 'Trip type is required.');
            isValid = false;
        }

        // Validate at least one stoppage row
        if ($('#deploymentTable tbody tr').length === 0) {
            showAlert('error', 'At least one stoppage row is required.');
            isValid = false;
        }

        return isValid;
    }

    /**
     * Show field error
     */
    function showFieldError(field, message) {
        const fieldElement = $(field);
        fieldElement.addClass('is-invalid');
        fieldElement.after(`<div class="invalid-feedback">${message}</div>`);
    }

    /**
     * Display validation errors
     */
    function displayValidationErrors(errors) {
        $.each(errors, function(field, messages) {
            const fieldElement = $(`[name="${field}"]`);
            if (fieldElement.length) {
                fieldElement.addClass('is-invalid');
                fieldElement.after(`<div class="invalid-feedback">${messages[0]}</div>`);
            }
        });
    }

    /**
     * Show alert message
     */
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'ti ti-check-circle' : 'ti ti-alert-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${icon} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('#alertContainer').html(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }

    // Initialize: Add one row by default if no rows exist
    if ($('#stoppageRows .stoppage-row').length === 0) {
        addStoppageRow();
    }

    // Initialize feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

