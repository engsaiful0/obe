/**
 * Daily Deployment Plan Index - AJAX Filter Handler
 */

$(document).ready(function() {
    'use strict';

    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded!');
        return;
    }

    const INDEX_URL = window.deploymentPlanIndexUrl || '/app/deployment-plans/view-daily-deployment-plan';
    let ajaxRequest = null;

    console.log('Deployment Plan AJAX Handler initialized');
    console.log('Index URL:', INDEX_URL);
    
    // Note: Auto-filter on change is enabled for all filters
    // Filters will automatically reload data when changed (with 500ms debounce)

    // Apply filters button
    $(document).on('click', '#applyFiltersBtn', function() {
        console.log('Apply filters button clicked');
        loadData(1);
    });

    // Clear filters button
    $(document).on('click', '#clearFiltersBtn', function() {
        console.log('Clear filters button clicked');
        clearFilters();
    });

    // Auto-apply on filter change (with debounce)
    let filterTimeout;
    
    // Handle select dropdowns and date inputs
    $(document).on('change', '#filterDateFrom, #filterDateTo, #filterTripTime, #filterBusUser, #deployment_type_id, #trip_type', function() {
        console.log('Filter changed:', $(this).attr('id'), $(this).val());
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            loadData(1);
        }, 500);
    });
    
    // Also handle input events for date fields (for better responsiveness)
    $(document).on('input', '#filterDateFrom, #filterDateTo', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            loadData(1);
        }, 800); // Slightly longer delay for date inputs to allow user to finish typing
    });

    // Pagination click handler
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            try {
                const urlObj = new URL(url, window.location.origin);
                const page = urlObj.searchParams.get('page') || 1;
                loadData(page);
            } catch (e) {
                const urlParams = new URLSearchParams(url.split('?')[1] || '');
                const page = urlParams.get('page') || 1;
                loadData(page);
            }
        }
    });

    // Delete button handler
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        handleDelete($(this));
    });

    /**
     * Load data with filters
     */
    function loadData(page = 1) {
        // Cancel previous request
        if (ajaxRequest) {
            ajaxRequest.abort();
        }

        // Get filter values
        const dateFrom = $('#filterDateFrom').val() || '';
        const dateTo = $('#filterDateTo').val() || '';
        const tripTimeId = $('#filterTripTime').val() || '';
        const busUserId = $('#filterBusUser').val() || '';
        const deploymentTypeId = $('#deployment_type_id').val() || '';
        const tripType = $('#trip_type').val() || '';
        
        const filters = {
            page: page
        };
        
        // Only add non-empty filter values
        if (dateFrom.trim() !== '') {
            filters.date_from = dateFrom;
        }
        if (dateTo.trim() !== '') {
            filters.date_to = dateTo;
        }
        if (tripTimeId.trim() !== '') {
            filters.trip_time_id = tripTimeId;
        }
        if (busUserId.trim() !== '') {
            filters.bus_user_id = busUserId;
        }
        if (deploymentTypeId.trim() !== '') {
            filters.deployment_type_id = deploymentTypeId;
        }
        if (tripType.trim() !== '') {
            filters.trip_type = tripType;
        }

        console.log('Loading data with filters:', filters);
        console.log('Filter values:', {
            dateFrom: dateFrom,
            dateTo: dateTo,
            tripTimeId: tripTimeId,
            busUserId: busUserId,
            deploymentTypeId: deploymentTypeId,
            tripType: tripType
        });
        console.log('AJAX URL:', INDEX_URL);

        showLoading();

        // Make AJAX request
        ajaxRequest = $.ajax({
            url: INDEX_URL,
            type: 'GET',
            data: filters,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                console.log('AJAX Success Response:', response);
                hideLoading();
                
                // Handle JSON response
                if (typeof response === 'object' && response !== null) {
                    if (response.success !== false) {
                        // Update table body
                        if (response.html) {
                            $('#dataTableBody').html(response.html);
                        }
                        
                        // Update pagination
                        if (response.pagination) {
                            $('#paginationContainer').html(response.pagination);
                        }
                    } else {
                        console.error('Response error:', response);
                        showAlert('error', response.message || 'Error loading data.');
                    }
                } else {
                    // Handle HTML response (fallback)
                    console.warn('Received HTML response instead of JSON');
                    $('#dataTableContainer').html(response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                hideLoading();
                
                let errorMessage = 'Error loading data. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMessage = 'Page not found.';
                } else if (xhr.responseText) {
                    console.error('Response text:', xhr.responseText);
                }
                
                showAlert('error', errorMessage);
            },
            complete: function() {
                ajaxRequest = null;
            }
        });
    }

    /**
     * Clear all filters
     */
    function clearFilters() {
        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');
        $('#filterTripTime').val('').trigger('change');
        $('#filterBusUser').val('').trigger('change');
        $('#deployment_type_id').val('').trigger('change');
        $('#trip_type').val('').trigger('change');
        
        loadData(1);
    }

    /**
     * Handle delete action
     */
    function handleDelete($button) {
        const form = $button.closest('form');
        const deleteUrl = form.attr('action');
        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this deletion!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Deleting...",
                    text: "Please wait.",
                    didOpen: () => Swal.showLoading(),
                    allowOutsideClick: false
                });

                $.ajax({
                    url: deleteUrl,
                    method: 'DELETE',
                    data: {
                        _token: csrfToken,
                        _method: 'DELETE'
                    },
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        Swal.close();
                        
                        if (response.success) {
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message || 'Deleted successfully.');
                            } else {
                                Swal.fire('Deleted!', response.message || 'Deleted successfully.', 'success');
                            }
                            
                            // Reload data
                            loadData();
                        } else {
                            showError(response.message || 'Error deleting.');
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        let errorMessage = 'Error deleting. Please try again.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            errorMessage = 'Record not found.';
                        } else if (xhr.status === 403) {
                            errorMessage = 'Permission denied.';
                        }
                        
                        showError(errorMessage);
                    }
                });
            }
        });
    }

    /**
     * Show loading spinner
     */
    function showLoading() {
        $('#loadingSpinner').removeClass('d-none').css('display', 'flex');
        $('#dataTableContainer').addClass('d-none');
    }

    /**
     * Hide loading spinner
     */
    function hideLoading() {
        $('#loadingSpinner').addClass('d-none').css('display', 'none');
        $('#dataTableContainer').removeClass('d-none');
    }

    /**
     * Show alert message
     */
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'error' ? 'alert-danger' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Create alert container if it doesn't exist
        if ($('#alertContainer').length === 0) {
            $('#dataTableContainer').before('<div id="alertContainer"></div>');
        }
        
        $('#alertContainer').html(alertHtml);
        
        // Auto-hide success/info alerts
        if (type === 'success' || type === 'info') {
            setTimeout(function() {
                $('#alertContainer .alert').fadeOut();
            }, 3000);
        }
    }

    /**
     * Show error message
     */
    function showError(message) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            Swal.fire('Error!', message, 'error');
        }
    }
});
