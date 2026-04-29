/**
 * Bus Requisition Index Page with AJAX Filtering and Delete
 * Enhanced with real-time AJAX filtering for all filters
 */

'use strict';

$(document).ready(function() {
    const filterForm = $('#filter-form');
    const tableContainer = $('#bus-requisitions-table-container');
    const spinner = $('#bus-requisitions-spinner');
    let ajaxRequest = null;
    let searchTimeout = null;
    let isFiltering = false;

    // Handle filter form submission via AJAX
    filterForm.on('submit', function(e) {
        e.preventDefault();
        applyFilters();
    });

    // Real-time search with debounce (500ms delay)
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 500);
    });

    // Auto-apply filters on change for select dropdowns
    $('#department_id, #status').on('change', function() {
        applyFilters();
    });

    // Auto-apply filters on change for date inputs
    $('#date_from, #date_to, #required_bus_date_from, #required_bus_date_to').on('change', function() {
        applyFilters();
    });

    // Clear filters button
    $('#clear-filters-btn').on('click', function(e) {
        e.preventDefault();
        clearFilters();
    });

    // Handle pagination links
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            loadTable(url);
        }
    });

    // Handle delete button click
    $(document).on('click', '.delete-record', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        deleteBusRequisition(id);
    });

    // Handle status update
    $(document).on('change', '.status-update', function(e) {
        e.preventDefault();
        const $select = $(this);
        const id = $select.data('id');
        const status = $select.val();
        const originalStatus = $select.data('original-status') || $select.find('option:selected').val();
        
        // Store original value in case of error
        if (!$select.data('original-status')) {
            $select.data('original-status', $select.find('option[selected]').val() || $select.val());
        }
        
        updateStatus(id, status, $select);
    });

    /**
     * Clear all filters and reload table
     */
    function clearFilters() {
        // Clear all input fields
        filterForm.find('input[type="text"]').val('');
        filterForm.find('input[type="date"]').val('');
        filterForm.find('select').val('');
        
        // Apply filters to show all data
        applyFilters();
    }

    /**
     * Apply filters via AJAX
     */
    function applyFilters() {
        // Prevent multiple simultaneous requests
        if (isFiltering) {
            return;
        }

        // Cancel any pending AJAX request
        if (ajaxRequest && ajaxRequest.readyState !== 4) {
            ajaxRequest.abort();
        }

        isFiltering = true;
        const formData = filterForm.serialize();
        const url = filterForm.attr('action') + '?' + formData;
        
        loadTable(url);
    }

    /**
     * Load table data via AJAX
     */
    function loadTable(url) {
        // Show spinner and disable form
        spinner.removeClass('d-none');
        tableContainer.addClass('opacity-50');
        filterForm.find('input, select, button').prop('disabled', true);

        // Cancel any pending request
        if (ajaxRequest && ajaxRequest.readyState !== 4) {
            ajaxRequest.abort();
        }

        ajaxRequest = $.ajax({
            url: url,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success && response.html) {
                    // Update table content (includes pagination)
                    tableContainer.html(response.html);
                    
                    // Update URL without reload
                    if (window.history && window.history.pushState) {
                        try {
                            const urlObj = new URL(url, window.location.origin);
                            window.history.pushState({}, '', urlObj.pathname + urlObj.search);
                        } catch (e) {
                            // Fallback for older browsers
                        window.history.pushState({}, '', url);
                    }
                }

                    // Show success message if needed
                    if (response.showing !== undefined && response.total !== undefined) {
                        console.log(`Showing ${response.showing} of ${response.total} bus requisitions`);
                    }
                } else {
                    // Handle non-AJAX response (fallback)
                    if (typeof response === 'string') {
                        tableContainer.html(response);
                    } else {
                        showError('Error loading bus requisitions. Please try again.');
                    }
                }
            },
            error: function(xhr, status, error) {
                // Don't show error if request was aborted
                if (status !== 'abort') {
                    console.error('Error loading table:', {xhr, status, error});
                    showError('Error loading bus requisitions. Please try again.');
                }
            },
            complete: function() {
                // Hide spinner and re-enable form
                spinner.addClass('d-none');
                tableContainer.removeClass('opacity-50');
                filterForm.find('input, select, button').prop('disabled', false);
                isFiltering = false;
            }
        });
    }

    /**
     * Delete bus requisition
     */
    function deleteBusRequisition(id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-primary me-3',
                    cancelButton: 'btn btn-label-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    performDelete(id);
                }
            });
        } else {
            if (confirm('Are you sure you want to delete this bus requisition?')) {
                performDelete(id);
            }
        }
    }

    /**
     * Perform delete operation
     */
    function performDelete(id) {
        const deleteBtn = $(`.delete-record[data-id="${id}"]`);
        const originalHtml = deleteBtn.html();
        
        // Show spinner on delete button
        deleteBtn.prop('disabled', true);
        deleteBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Deleting...');

        $.ajax({
            url: window.busRequisitionUrls.destroy + id,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Bus requisition deleted successfully.');
                    }
                    // Reload table with current filters
                    applyFilters();
                } else {
                    showError(response.message || 'An error occurred while deleting. Please try again.');
                    deleteBtn.html(originalHtml);
                    deleteBtn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'An error occurred while deleting. Please try again.';
                showError(message);
                deleteBtn.html(originalHtml);
                deleteBtn.prop('disabled', false);
            }
        });
    }

    /**
     * Update status via AJAX
     */
    function updateStatus(id, status, $select) {
        const originalValue = $select.data('original-status') || $select.find('option[selected]').val() || status;
        const originalHtml = $select.html();
        
        // Store original value if not stored
        if (!$select.data('original-status')) {
            $select.data('original-status', originalValue);
        }
        
        // Show spinner - disable select and add loading class
        $select.prop('disabled', true);
        $select.addClass('opacity-50');
        
        // Add a small spinner icon next to the select
        const $spinner = $('<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>');
        $select.after($spinner);

        $.ajax({
            url: window.busRequisitionUrls.updateStatus.replace(':id', id),
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            data: JSON.stringify({ status: status }),
            success: function(response) {
                if (response.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Status updated successfully.');
                    }
                    
                    // Update the select with new value
                    $select.val(status);
                    $select.data('original-status', status);
                } else {
                    showError(response.message || 'An error occurred while updating status.');
                    // Revert to original value
                    $select.val(originalValue);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'An error occurred while updating status. Please try again.';
                showError(message);
                
                // Revert to original value
                $select.val(originalValue);
            },
            complete: function() {
                // Remove spinner and re-enable select
                $spinner.remove();
                $select.prop('disabled', false);
                $select.removeClass('opacity-50');
            }
        });
    }

    /**
     * Show error message
     */
    function showError(message) {
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert('Error: ' + message);
                }
            }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
        // Reload table with URL parameters
        const url = window.location.href;
        loadTable(url);
        });
});
