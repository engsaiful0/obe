$(document).ready(function() {
    // Initialize the page
    initializePage();
    
    // Load filter data
    loadFilterData();
    
    // Event listeners for bus filtering
    $('#bus_sub_type_id').on('change', function() {
        let subTypeId = $(this).val();
        // Clear bus selection when sub type changes
        $('#bus_id').val('');
        // Load buses filtered by sub type, then apply filters
        loadBusesBySubType(subTypeId, function() {
            // Callback after buses are loaded
            applyFilters();
        });
    });
    
    // Form submission - now with AJAX
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        applyFilters();
    });
    
    // Real-time filtering on input change (with debounce)
    let filterTimeout;
    $('#search').on('input', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            applyFilters();
        }, 500); // 500ms delay
    });
    
    // Real-time filtering on select change
    $('#expense_head_id, #bus_id, #employee_id, #date_from, #date_to, #per_page, #supplier_id').on('change', function() {
        applyFilters();
    });
});

function initializePage() {
    // Show success/error messages if any
    if (typeof window.successMessage !== 'undefined') {
        showAlert(window.successMessage, 'success');
    }
    if (typeof window.errorMessage !== 'undefined') {
        showAlert(window.errorMessage, 'error');
    }
    
    // If bus_sub_type_id is selected on page load, filter buses
    var selectedSubTypeId = $('#bus_sub_type_id').val();
    if (selectedSubTypeId) {
        loadBusesBySubType(selectedSubTypeId);
    }
}

function loadFilterData() {
    loadVehicleTypes();
    loadDrivers();
    loadEmployees();
    loadAssistants();
}

function loadVehicleTypes() {
    $.ajax({
        url: window.expenseUrls.getVehicleTypes,
        method: 'GET',
        success: function(response) {
            const select = $('#vehicle_type_id');
            const currentValue = select.val();
            select.empty().append('<option value="">All Vehicle Types</option>');
            
            if (response.data) {
                response.data.forEach(function(item) {
                    const selected = currentValue == item.id ? 'selected' : '';
                    select.append(`<option value="${item.id}" ${selected}>${item.vehicle_type_name}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading vehicle types:', xhr);
        }
    });
}

function loadVehicleSubTypes(vehicleTypeId) {
    if (!vehicleTypeId) {
        $('#vehicle_sub_type_id').empty().append('<option value="">All Vehicle Sub Types</option>');
        return;
    }
    
    $.ajax({
        url: window.expenseUrls.getVehicleSubTypes,
        method: 'GET',
        data: { vehicle_type_id: vehicleTypeId },
        success: function(response) {
            const select = $('#vehicle_sub_type_id');
            const currentValue = select.val();
            select.empty().append('<option value="">All Vehicle Sub Types</option>');
            
            if (response.data) {
                response.data.forEach(function(item) {
                    const selected = currentValue == item.id ? 'selected' : '';
                    select.append(`<option value="${item.id}" ${selected}>${item.sub_type_name}</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading vehicle sub types:', xhr);
        }
    });
}

function loadVehicles(vehicleTypeId, vehicleSubTypeId) {
    // Use the new function for loading vehicles by type and subtype
    loadVehiclesByTypeAndSubType(vehicleTypeId, vehicleSubTypeId);
}

function loadDrivers() {
    $.ajax({
        url: window.expenseUrls.getDrivers,
        method: 'GET',
        success: function(response) {
            const select = $('#driver_id');
            const currentValue = select.val();
            select.empty().append('<option value="">All Drivers</option>');
            
            if (response.data) {
                response.data.forEach(function(item) {
                    const selected = currentValue == item.id ? 'selected' : '';
                    select.append(`<option value="${item.id}" ${selected}>${item.full_name} (${item.driver_unique_id})</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading drivers:', xhr);
        }
    });
}

function loadEmployees() {
    $.ajax({
        url: window.expenseUrls.getEmployees,
        method: 'GET',
        success: function(response) {
            const select = $('#employee_id');
            const currentValue = select.val();
            select.empty().append('<option value="">All Employees</option>');
            
            if (response.data) {
                response.data.forEach(function(item) {
                    const selected = currentValue == item.id ? 'selected' : '';
                    select.append(`<option value="${item.id}" ${selected}>${item.employee_name} (${item.employee_unique_id})</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading employees:', xhr);
        }
    });
}

function loadAssistants() {
    $.ajax({
        url: window.expenseUrls.getAssistants,
        method: 'GET',
        success: function(response) {
            const select = $('#assistant_id');
            const currentValue = select.val();
            select.empty().append('<option value="">All Assistants</option>');
            
            if (response.data) {
                response.data.forEach(function(item) {
                    const selected = currentValue == item.id ? 'selected' : '';
                    select.append(`<option value="${item.id}" ${selected}>${item.assistant_name} (${item.assistant_id})</option>`);
                });
            }
        },
        error: function(xhr) {
            console.error('Error loading assistants:', xhr);
        }
    });
}

function deleteExpense(expenseId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we delete the expense.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: window.expenseUrls.destroy.replace(':id', expenseId),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    toastr.success('Expense deleted successfully!');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                },
                error: function(xhr) {
                    toastr.error('Something went wrong while deleting the expense.');
                }
            });
        }
    });
}

function exportToExcel() {
    const form = $('#filter-form');
    const formData = form.serialize();
    const url = window.expenseUrls.exportExcel + '?' + formData;
    window.open(url, '_blank');
}

function exportToPdf() {
    const form = $('#filter-form');
    const formData = form.serialize();
    const url = window.expenseUrls.exportPdf + '?' + formData;
    window.open(url, '_blank');
}

function clearFilters() {
    // Clear all form fields
    $('#filter-form')[0].reset();
    
    // Clear dependent dropdowns
    $('#vehicle_sub_type_id').empty().append('<option value="">All Vehicle Sub Types</option>');
    $('#vehicle_id').empty().append('<option value="">All Vehicles</option>');
    $('#supplier_id').empty().append('<option value="">All Suppliers</option>');
    
    // Apply filters to show all data
    applyFilters();
}

function applyFilters() {
    const form = $('#filter-form');
    const formData = form.serialize();
    const spinner = $('#expenses-spinner');
    const container = $('#expenses-table-container');
    
    // Show spinner
    spinner.removeClass('d-none');
    
    // Disable filter form during loading
    form.find('input, select, button').prop('disabled', true);
    
    $.ajax({
        url: form.attr('action'),
        method: 'GET',
        data: formData,
        success: function(response) {
            if (response.success) {
                // Update the table content
                container.html(response.html);
                
                // Update pagination
                if (response.pagination) {
                    // Remove existing pagination and add new one
                    container.find('.d-flex.justify-content-between').remove();
                    container.append(response.pagination);
                }
                
                // Update URL without page reload
                const newUrl = new URL(window.location);
                const params = new URLSearchParams(formData);
                newUrl.search = params.toString();
                window.history.pushState({}, '', newUrl);
                
                // Show success message if needed
                if (response.showing > 0) {
                    console.log(`Showing ${response.showing} of ${response.total} expenses`);
                }
            } else {
                showAlert('Error loading expenses. Please try again.', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error applying filters:', xhr);
            showAlert('Error loading expenses. Please try again.', 'error');
        },
        complete: function() {
            // Hide spinner
            spinner.addClass('d-none');
            
            // Re-enable filter form
            form.find('input, select, button').prop('disabled', false);
        }
    });
}

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = $(`
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('.container-xxl').prepend(alert);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        alert.alert('close');
    }, 5000);
}

    // Bus loading functionality by sub type
    function loadBusesBySubType(subTypeId, callback) {
        if (!window.expenseUrls || !window.expenseUrls.getBusesBySubType) {
            console.error('getBusesBySubType URL not defined');
            if (callback) callback();
            return;
        }
        
        var busUrlForSubType = window.expenseUrls.getBusesBySubType;
        var currentBusId = $('#bus_id').val(); // Preserve current selection if it matches

        if (!subTypeId) {
            // If no sub type selected, keep current options but don't filter
            if (callback) callback();
            return;
        }

        $.ajax({
            url: busUrlForSubType,
            type: 'GET',
            data: {
                bus_sub_type_id: subTypeId
            },
            beforeSend: function() {
                $('#bus_id').html('<option>Loading...</option>');
            },
            success: function(response) {
                $('#bus_id').empty().append('<option value="">All Buses</option>');

                if (response && response.success && response.buses && response.buses.length > 0) {
                    $.each(response.buses, function(index, bus) {
                        var busText = bus.bus_number || (bus.model_name + ' (' + bus.registration_number + ')');
                        var selected = (currentBusId == bus.id) ? 'selected' : '';
                        $('#bus_id').append(
                            $('<option>', {
                                value: bus.id,
                                text: busText,
                                selected: selected
                            })
                        );
                    });
                } else {
                    $('#bus_id').append('<option value="">No buses found</option>');
                }
                if (callback) callback();
            },
            error: function() {
                $('#bus_id').html('<option value="">Error loading buses</option>');
                if (callback) callback();
            }
        });
    }