/**
 * Assistant DataTables Configuration
 * Handles AJAX operations for assistant management
 */

$(document).ready(function() {
    // Initialize DataTable if the table exists
    if ($('#assistantsTable').length) {
        initializeAssistantDataTable();
    }
    
    // Initialize form handlers
    initializeFormHandlers();
});

/**
 * Initialize DataTable for assistants
 */
function initializeAssistantDataTable() {
    const table = $('#assistantsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.assistantsDataUrl || '/app/assistants/get-data',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                console.log('Loading assistants data...');
                console.log('Using AJAX URL:', window.assistantsDataUrl || '/app/assistants/get-data');
            },
            complete: function() {
                console.log('Data loading completed');
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error,
                    thrown: thrown
                });
                
                let errorMessage = 'Error loading assistants data.';
                if (xhr.status === 500) {
                    errorMessage = 'Server error occurred. Please contact administrator.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Assistants data endpoint not found.';
                } else if (xhr.status === 422) {
                    errorMessage = 'Invalid request parameters.';
                }
                
                showAlert(errorMessage + ' Please refresh the page.', 'error');
            }
        },
        columns: [
            { data: 'assistant_id', name: 'assistant_id' },
            { 
                data: 'assistant_name', 
                name: 'assistant_name',
                render: function(data, type, row) {
                    const avatar = row.picture ? 
                        `<img src="/storage/${row.picture}" alt="${data}" class="rounded-circle me-2" width="32" height="32">` :
                        `<div class="avatar-initial rounded-circle bg-label-primary me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">${data.charAt(0).toUpperCase()}</div>`;
                    
                    return `
                        <div class="d-flex align-items-center">
                            ${avatar}
                            <div>
                                <h6 class="mb-0">${data}</h6>
                                <small class="text-muted">${row.father_name}</small>
                            </div>
                        </div>
                    `;
                }
            },
            { data: 'mobile', name: 'mobile' },
            { 
                data: 'gender.gender_name', 
                name: 'gender.gender_name',
                orderable: false,
                render: function(data) {
                    return data ? `<span class="badge bg-label-info">${data}</span>` : 'N/A';
                }
            },
            { 
                data: 'employee_type.employee_type_name', 
                name: 'employee_type.employee_type_name',
                orderable: false,
                render: function(data) {
                    return data ? `<span class="badge bg-label-secondary">${data}</span>` : 'N/A';
                }
            },
            { 
                data: 'assigned_bus', 
                name: 'assigned_bus',
                orderable: false,
                render: function(data, type, row) {
                    if (row.assigned_bus && row.assigned_bus.model_name) {
                        return `<span class="badge bg-label-success">${row.assigned_bus.model_name}</span>`;
                    }
                    return `<span class="badge bg-label-warning">Unassigned</span>`;
                }
            },
            { 
                data: 'years_of_experience', 
                name: 'years_of_experience',
                render: function(data) {
                    return `<span class="badge bg-label-primary">${data} years</span>`;
                }
            },
            { 
                data: 'gross_salary', 
                name: 'gross_salary',
                render: function(data) {
                    return `<span class="fw-semibold">৳${parseFloat(data).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>`;
                }
            },
            {
                data: 'id',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    const busAction = row.assigned_bus ? 
                        `<a class="dropdown-item text-warning" href="#" onclick="unassignBus(${data})">
                            <i class="ti ti-user-x me-1"></i> Unassign Bus
                        </a>` :
                        `<a class="dropdown-item text-success" href="#" onclick="assignBus(${data})">
                            <i class="ti ti-car me-1"></i> Assign Bus
                        </a>`;
                    
                    return `
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="/app/assistants/${data}">
                                    <i class="ti ti-eye me-1"></i> View
                                </a>
                                <a class="dropdown-item" href="/app/assistants/${data}/edit">
                                    <i class="ti ti-pencil me-1"></i> Edit
                                </a>
                                ${busAction}
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#" onclick="deleteAssistant(${data})">
                                    <i class="ti ti-trash me-1"></i> Delete
                                </a>
                            </div>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, 'asc']], // Sort by assistant name
        pageLength: 10,
        responsive: true,
        dom: 'Bfrtip', // Add buttons for export if needed
        buttons: [],
        language: {
            processing: '<div class="d-flex align-items-center"><div class="spinner-border spinner-border-sm me-2" role="status"><span class="visually-hidden">Loading...</span></div>Loading assistants...</div>',
            emptyTable: '<div class="text-center py-4"><i class="ti ti-users-off text-muted" style="font-size: 3rem;"></i><h6 class="mt-2 text-muted">No assistants found</h6><p class="text-muted">Start by adding your first assistant.</p></div>',
            zeroRecords: '<div class="text-center py-4"><i class="ti ti-search-off text-muted" style="font-size: 3rem;"></i><h6 class="mt-2 text-muted">No matching assistants found</h6><p class="text-muted">Try adjusting your search criteria.</p></div>',
            loadingRecords: "Loading...",
            search: "Search assistants:",
            lengthMenu: "Show _MENU_ assistants per page",
            info: "Showing _START_ to _END_ of _TOTAL_ assistants",
            infoEmpty: "Showing 0 to 0 of 0 assistants",
            infoFiltered: "(filtered from _MAX_ total assistants)"
        }
    });
}

/**
 * Initialize form handlers
 */
function initializeFormHandlers() {
    // Auto-calculate gross salary
    $('input[name="basic_salary"], input[name="house_rent"], input[name="medical_allowance"], input[name="other_allowance"]').on('input', function() {
        calculateGrossSalary();
    });
    
    // Form validation
    $('#assistantForm').on('submit', function(e) {
        if (!validateForm()) {
            e.preventDefault();
        }
    });
}

/**
 * Calculate gross salary automatically
 */
function calculateGrossSalary() {
    const basicSalary = parseFloat($('#basic_salary').val()) || 0;
    const houseRent = parseFloat($('#house_rent').val()) || 0;
    const medicalAllowance = parseFloat($('#medical_allowance').val()) || 0;
    const otherAllowance = parseFloat($('#other_allowance').val()) || 0;
    
    const grossSalary = basicSalary + houseRent + medicalAllowance + otherAllowance;
    $('#gross_salary').val(grossSalary.toFixed(2));
}

/**
 * Validate form before submission
 */
function validateForm() {
    const requiredFields = [
        'assistant_name', 'father_name', 'mother_name', 'mobile',
        'gender_id', 'marital_status_id', 'religion_id', 'nid_number',
        'present_address', 'permanent_address', 'academic_qualification',
        'years_of_experience', 'employee_type_id', 'basic_salary',
        'house_rent', 'medical_allowance', 'other_allowance'
    ];

    let isValid = true;
    requiredFields.forEach(fieldName => {
        const field = $(`#${fieldName}`);
        if (!field.val() || field.val().trim() === '') {
            field.addClass('is-invalid');
            isValid = false;
        } else {
            field.removeClass('is-invalid');
        }
    });

    if (!isValid) {
        showAlert('Please fill in all required fields.', 'error');
    }

    return isValid;
}

/**
 * Bus assignment functions
 */
function assignBus(assistantId) {
    $('#assistantId').val(assistantId);
    
    // Load available buses
    $.ajax({
        url: '/app/buses',
        type: 'GET',
        data: { status: 'active' },
        success: function(response) {
            const select = $('#busSelect');
            select.html('<option value="">Choose a bus...</option>');
            
            if (response.buses) {
                response.buses.forEach(bus => {
                    select.append(`<option value="${bus.id}">${bus.model_name} (${bus.registration_number})</option>`);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading buses:', error);
            showAlert('Error loading buses', 'error');
        }
    });
    
    $('#assignBusModal').modal('show');
}

function confirmAssignBus() {
    const formData = new FormData($('#assignBusForm')[0]);
    const assistantId = $('#assistantId').val();
    
    $.ajax({
        url: `/app/assistants/${assistantId}/assign-bus`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showAlert('Bus assigned successfully', 'success');
                $('#assignBusModal').modal('hide');
                location.reload();
            } else {
                showAlert('Error assigning bus: ' + response.message, 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            showAlert('Error assigning bus', 'error');
        }
    });
}

function unassignBus(assistantId) {
    if (confirm('Are you sure you want to unassign the bus from this assistant?')) {
        $.ajax({
            url: `/app/assistants/${assistantId}/unassign-bus`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Bus assignment removed successfully', 'success');
                    location.reload();
                } else {
                    showAlert('Error unassigning bus: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                showAlert('Error unassigning bus', 'error');
            }
        });
    }
}

/**
 * Delete assistant
 */
function deleteAssistant(assistantId) {
    if (confirm('Are you sure you want to delete this assistant? This action cannot be undone.')) {
        $.ajax({
            url: `/app/assistants/${assistantId}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('Assistant deleted successfully', 'success');
                    location.reload();
                } else {
                    showAlert('Error deleting assistant: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                showAlert('Error deleting assistant', 'error');
            }
        });
    }
}

/**
 * Get unassigned assistants
 */
function loadUnassignedAssistants() {
    $.ajax({
        url: '/app/assistants/unassigned',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                displayUnassignedAssistants(response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading unassigned assistants:', error);
            showAlert('Error loading unassigned assistants', 'error');
        }
    });
}

/**
 * Display unassigned assistants
 */
function displayUnassignedAssistants(assistants) {
    const container = $('#unassignedAssistantsContainer');
    if (assistants.length === 0) {
        container.html(`
            <div class="text-center py-4">
                <i class="ti ti-check-circle text-success" style="font-size: 3rem;"></i>
                <h6 class="mt-2 text-success">All assistants have buses assigned</h6>
            </div>
        `);
    } else {
        let html = '<div class="row">';
        assistants.forEach(assistant => {
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar-initial rounded-circle bg-label-primary me-3" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    ${assistant.assistant_name.charAt(0).toUpperCase()}
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${assistant.assistant_name}</h6>
                                    <small class="text-muted">${assistant.assistant_id}</small>
                                </div>
                                <button class="btn btn-primary btn-sm" onclick="assignBus(${assistant.id})">
                                    <i class="ti ti-car me-1"></i>Assign Bus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.html(html);
    }
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="ti ti-${type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert
    $('.card-body').prepend(alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

/**
 * Initialize unassigned assistants page
 */
function initializeUnassignedAssistantsPage() {
    if ($('#unassignedAssistantsContainer').length) {
        loadUnassignedAssistants();
    }
}

// Initialize unassigned assistants page when document is ready
$(document).ready(function() {
    initializeUnassignedAssistantsPage();
});
