$(document).ready(function() {
    'use strict';

    // Global variables
    let allBuses = [];
    let filteredBuses = [];
    let isSubmitting = false;

    // Initialize
    initializeEventHandlers();
    initializeSelect2();
    loadBuses();

    function initializeEventHandlers() {
        // Filter handlers
        $('#applyFilter').on('click', function() {
            applyFilter();
        });

        $('#clearFilter').on('click', function() {
            clearFilter();
        });

        $('#busSearch').on('input', function() {
            debounce(applyFilter, 500)();
        });

        $('#busSubTypeFilter').on('change', function() {
            applyFilter();
        });

        // Form handlers
        $('#allAttendanceForm').on('submit', function(e) {
            e.preventDefault();
            submitAttendance();
        });

        // Select all handlers
        $('#selectAll').on('click', function() {
            selectAllBuses();
        });

        $('#deselectAll').on('click', function() {
            deselectAllBuses();
        });

        $('#selectAllCheckbox').on('change', function() {
            if ($(this).is(':checked')) {
                selectAllBuses();
            } else {
                deselectAllBuses();
            }
        });

        // Reset form
        $('#resetForm').on('click', function() {
            resetForm();
        });

        // Trip type change handler
        $('#trip_type').on('change', function() {
            updateTimeLabels();
        });

        // Stoppage change handler
        $('#start_stoppage_id, #end_stoppage_id').on('change', function() {
            validateStoppages();
        });

        // View attendance button
        $('#viewAttendance').on('click', function() {
            window.location.href = '/app/bus-attendance';
        });
    }

    function initializeSelect2() {
 
    }

    function loadBuses() {
        // Store all buses data
        allBuses = [];
        $('#busesTableBody tr').each(function() {
            const $row = $(this);
            const busId = $row.data('bus-id');
            const subType = $row.data('sub-type');
            const modelName = $row.find('td:nth-child(2) strong').text();
            const registrationNumber = $row.find('td:nth-child(2) small').text();
            
            allBuses.push({
                id: busId,
                subType: subType,
                modelName: modelName,
                registrationNumber: registrationNumber,
                element: $row
            });
        });
        
        filteredBuses = [...allBuses];
    }

    function applyFilter() {
        const subTypeFilter = $('#busSubTypeFilter').val();
        const searchTerm = $('#busSearch').val().toLowerCase();

        filteredBuses = allBuses.filter(bus => {
            const matchesSubType = !subTypeFilter || bus.subType === getSubTypeName(subTypeFilter);
            const matchesSearch = !searchTerm || 
                bus.modelName.toLowerCase().includes(searchTerm) ||
                bus.registrationNumber.toLowerCase().includes(searchTerm);
            
            return matchesSubType && matchesSearch;
        });

        renderFilteredBuses();
    }

    function getSubTypeName(subTypeId) {
        const $option = $('#busSubTypeFilter option[value="' + subTypeId + '"]');
        return $option.text();
    }

    function renderFilteredBuses() {
        // Hide all rows first
        $('#busesTableBody tr').hide();
        
        // Show filtered rows
        filteredBuses.forEach(bus => {
            bus.element.show();
        });

        // Update select all checkbox
        updateSelectAllCheckbox();
    }

    function clearFilter() {
        $('#busSubTypeFilter').val('').trigger('change');
        $('#busSearch').val('');
        filteredBuses = [...allBuses];
        renderFilteredBuses();
    }

    function selectAllBuses() {
        filteredBuses.forEach(bus => {
            bus.element.find('.bus-checkbox').prop('checked', true);
        });
        updateSelectAllCheckbox();
    }

    function deselectAllBuses() {
        filteredBuses.forEach(bus => {
            bus.element.find('.bus-checkbox').prop('checked', false);
        });
        updateSelectAllCheckbox();
    }

    function updateSelectAllCheckbox() {
        const totalVisible = filteredBuses.length;
        const selectedVisible = filteredBuses.filter(bus => 
            bus.element.find('.bus-checkbox').is(':checked')
        ).length;

        if (selectedVisible === 0) {
            $('#selectAllCheckbox').prop('indeterminate', false).prop('checked', false);
        } else if (selectedVisible === totalVisible) {
            $('#selectAllCheckbox').prop('indeterminate', false).prop('checked', true);
        } else {
            $('#selectAllCheckbox').prop('indeterminate', true);
        }
    }

    function updateTimeLabels() {
        const tripType = $('#trip_type').val();
        const timeLabel = tripType === 'in' ? 'In Time' : 'Out Time';
        
        // Update table header if needed
        $('.time-input').attr('placeholder', timeLabel);
    }

    function validateStoppages() {
        const startStoppage = $('#start_stoppage_id').val();
        const endStoppage = $('#end_stoppage_id').val();
        
        if (startStoppage && endStoppage && startStoppage === endStoppage) {
            $('#end_stoppage_id').addClass('is-invalid');
            showNotification('error', 'Start and End Stoppages must be different.');
        } else {
            $('#end_stoppage_id').removeClass('is-invalid');
        }
    }

    function submitAttendance() {
        if (isSubmitting) return;

        // Validate form
        if (!validateForm()) {
            return;
        }

        // Get selected buses
        const selectedBuses = getSelectedBuses();
        if (selectedBuses.length === 0) {
            showNotification('error', 'Please select at least one bus.');
            return;
        }

        // Show loading state
        showLoadingState(true);
        isSubmitting = true;

        // Prepare form data
        const formData = new FormData();
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('attendance_date', $('#attendance_date').val());
        formData.append('trip_type', $('#trip_type').val());
        formData.append('start_stoppage_id', $('#start_stoppage_id').val());
        formData.append('end_stoppage_id', $('#end_stoppage_id').val());

        // Add selected buses data
        selectedBuses.forEach((bus, index) => {
            formData.append(`buses[${index}][bus_id]`, bus.busId);
            formData.append(`buses[${index}][driver_id]`, bus.driverId || '');
            formData.append(`buses[${index}][assistant_id]`, bus.assistantId || '');
            formData.append(`buses[${index}][time]`, bus.time);
            if (bus.distance) {
                formData.append(`buses[${index}][total_distance]`, bus.distance);
            }
            if (bus.remarks) {
                formData.append(`buses[${index}][remarks]`, bus.remarks);
            }
        });

        // Submit via AJAX
        $.ajax({
            url: '/app/bus-attendance/submit-all-attendance',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showLoadingState(false);
                isSubmitting = false;
                
                if (response.success) {
                    showResultModal(response, 'success');
                } else {
                    showResultModal(response, 'error');
                }
            },
            error: function(xhr) {
                showLoadingState(false);
                isSubmitting = false;
                
                let errorMessage = 'An error occurred while submitting attendance.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showNotification('error', errorMessage);
            }
        });
    }

    function validateForm() {
        let isValid = true;

        // Clear previous validation
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        // Validate required fields
        const requiredFields = ['attendance_date', 'trip_type', 'start_stoppage_id', 'end_stoppage_id'];
        requiredFields.forEach(field => {
            if (!$(`#${field}`).val()) {
                $(`#${field}`).addClass('is-invalid');
                $(`#${field}`).after(`<div class="invalid-feedback">This field is required.</div>`);
                isValid = false;
            }
        });

        // Validate stoppages
        const startStoppage = $('#start_stoppage_id').val();
        const endStoppage = $('#end_stoppage_id').val();
        if (startStoppage && endStoppage && startStoppage === endStoppage) {
            $('#end_stoppage_id').addClass('is-invalid');
            $('#end_stoppage_id').after(`<div class="invalid-feedback">Start and End Stoppages must be different.</div>`);
            isValid = false;
        }

        // Validate selected buses
        const selectedBuses = getSelectedBuses();
        if (selectedBuses.length === 0) {
            showNotification('error', 'Please select at least one bus.');
            isValid = false;
        }

        // Validate time inputs for selected buses
        selectedBuses.forEach(bus => {
            if (!bus.time) {
                showNotification('error', 'Please fill in time for all selected buses.');
                isValid = false;
            }
        });

        return isValid;
    }

    function getSelectedBuses() {
        const selectedBuses = [];
        
        $('#busesTableBody tr:visible').each(function() {
            const $row = $(this);
            const $checkbox = $row.find('.bus-checkbox');
            
            if ($checkbox.is(':checked')) {
                const busId = $row.data('bus-id');
                const driverId = $row.find('.driver-select').val();
                const assistantId = $row.find('.assistant-select').val();
                const time = $row.find('.time-input').val();
                const distance = $row.find('.distance-input').val();
                const remarks = $row.find('.remarks-input').val();
                
                selectedBuses.push({
                    busId: busId,
                    driverId: driverId,
                    assistantId: assistantId,
                    time: time,
                    distance: distance,
                    remarks: remarks
                });
            }
        });
        
        return selectedBuses;
    }

    function showLoadingState(show) {
        if (show) {
            $('#submitSpinner').removeClass('d-none');
            $('#submitIcon').addClass('d-none');
            $('#submitText').text('Submitting...');
            $('#submitAttendance').prop('disabled', true);
            
            // Add loading overlay
            if ($('#allAttendanceForm').find('.loading-overlay').length === 0) {
                $('#allAttendanceForm').css('position', 'relative');
                $('#allAttendanceForm').append('<div class="loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-light bg-opacity-75" style="z-index: 10;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            }
        } else {
            $('#submitSpinner').addClass('d-none');
            $('#submitIcon').removeClass('d-none');
            $('#submitText').text('Submit Attendance');
            $('#submitAttendance').prop('disabled', false);
            
            // Remove loading overlay
            $('#allAttendanceForm').find('.loading-overlay').remove();
        }
    }

    function showResultModal(response, type) {
        const title = type === 'success' ? 'Success' : 'Partial Success';
        const icon = type === 'success' ? 'ti ti-check-circle text-success' : 'ti ti-alert-triangle text-warning';
        
        let content = `
            <div class="text-center mb-3">
                <i class="${icon}" style="font-size: 3rem;"></i>
            </div>
            <p class="text-center mb-3">${response.message}</p>
        `;
        
        if (response.success_count !== undefined) {
            content += `
                <div class="row text-center">
                    <div class="col-6">
                        <div class="card bg-success bg-opacity-10">
                            <div class="card-body">
                                <h5 class="text-success">${response.success_count}</h5>
                                <small>Successful</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-danger bg-opacity-10">
                            <div class="card-body">
                                <h5 class="text-danger">${response.error_count || 0}</h5>
                                <small>Failed</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        if (response.errors && response.errors.length > 0) {
            content += `
                <div class="mt-3">
                    <h6>Errors:</h6>
                    <ul class="list-unstyled">
                        ${response.errors.map(error => `<li class="text-danger">• ${error}</li>`).join('')}
                    </ul>
                </div>
            `;
        }
        
        $('#resultModalTitle').text(title);
        $('#resultModalBody').html(content);
        $('#resultModal').modal('show');
    }

    function resetForm() {
        $('#allAttendanceForm')[0].reset();
        $('#attendance_date').val(new Date().toISOString().split('T')[0]);
        deselectAllBuses();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
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
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert.position-fixed').fadeOut();
        }, 5000);
    }

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Initialize bus checkboxes change handler
    $(document).on('change', '.bus-checkbox', function() {
        updateSelectAllCheckbox();
    });
});
