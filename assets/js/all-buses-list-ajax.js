$(document).ready(function() {
    console.log('All Buses List AJAX script loaded');
    console.log('Available URLs:', window.allBusesListUrls);
    
    let currentFilters = {};
    
    // Initialize Select2 for dropdown filters

    
    // Apply filters button
    $('#applyFiltersBtn').on('click', function() {
        applyFilters();
    });
    
    // Clear filters button
    $('#clearFiltersBtn').on('click', function() {
        clearFilters();
    });
    
    // Export PDF button
    $('#exportPdfBtn').on('click', function() {
        exportPdf();
    });
    
    // Export Excel button
    $('#exportExcelBtn').on('click', function() {
        exportExcel();
    });
    
     // Auto-apply filters on input change (with debounce)
     let filterTimeout;
     $('#filterDateFrom, #filterDateTo, #filterBus, #filterBusSubType, #filterDriver, #filterAssistant, #filterSearch').on('change input', function() {
         clearTimeout(filterTimeout);
         filterTimeout = setTimeout(function() {
             applyFilters();
         }, 500); // 500ms delay
     });
     
     // Handle Select2 change events
     $('.select2').on('change', function() {
         clearTimeout(filterTimeout);
         filterTimeout = setTimeout(function() {
             applyFilters();
         }, 500);
     });
    
    // Pagination click handler
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            const page = url.split('page=')[1];
            applyFilters(page);
        }
    });
    
    
    
     function applyFilters(page = 1) {
         console.log('Applying filters, page:', page);
         
         // Collect filter values
         currentFilters = {
             date_from: $('#filterDateFrom').val(),
             date_to: $('#filterDateTo').val(),
             bus_id: $('#filterBus').val(),
             bus_sub_type_id: $('#filterBusSubType').val(),
             driver_id: $('#filterDriver').val(),
             assistant_id: $('#filterAssistant').val(),
             search: $('#filterSearch').val(),
             page: page
         };
         
         console.log('Current filters:', currentFilters);
         
         // Show loading spinner
         showLoadingSpinner();
         
         // Disable filter button
         const filterBtn = $('#applyFiltersBtn');
         const filterSpinner = $('#filterSpinner');
         const filterText = $('#filterText');
         
         filterBtn.prop('disabled', true);
         filterSpinner.removeClass('d-none');
         filterText.html('Loading...');
         
         console.log('Filter button disabled, spinner shown');
        
        // Make AJAX request
        $.ajax({
            url: window.allBusesListUrls.getFilteredData,
            method: 'GET',
            data: currentFilters,
            beforeSend: function() {
                console.log('Sending AJAX request to:', window.allBusesListUrls.getFilteredData);
                console.log('Request data:', currentFilters);
            },
            success: function(response) {
                console.log('AJAX success response:', response);
                
                try {
                    // Check if response has the expected structure
                    if (response && response.success !== false) {
                        console.log('Response structure:', {
                            hasHtml: !!response.html,
                            hasPagination: !!response.pagination,
                            hasSummary: !!response.summary,
                            total: response.total
                        });
                        
                        // Update table body
                        if (response.html) {
                            const tableBody = $('#dataTableBody');
                            if (tableBody.length) {
                                tableBody.html(response.html);
                                console.log('Table body updated with HTML length:', response.html.length);
                            } else {
                                console.error('Table body element not found');
                            }
                        } else {
                            console.warn('No HTML content in response');
                        }
                        
                        // Update pagination
                        if (response.pagination) {
                            const paginationContainer = $('#paginationContainer');
                            if (paginationContainer.length) {
                                paginationContainer.html(response.pagination);
                                console.log('Pagination updated with HTML length:', response.pagination.length);
                            } else {
                                console.error('Pagination container element not found');
                            }
                        } else {
                            console.warn('No pagination content in response');
                        }
                        
                        // Update results summary
                        if (response.summary) {
                            const resultsSummary = $('#resultsSummary');
                            if (resultsSummary.length) {
                                resultsSummary.html(response.summary);
                                console.log('Summary updated with HTML length:', response.summary.length);
                            } else {
                                console.error('Results summary element not found');
                            }
                        } else {
                            console.warn('No summary content in response');
                        }
                        
                
                        
                        // Show success message if filters were applied
                        if (Object.values(currentFilters).some(val => val && val !== '1' && val !== '')) {
                            showAlert('success', `Found ${response.total || 'data'} records matching your criteria.`);
                        }
                    } else {
                        console.error('Response indicates failure:', response);
                        showAlert('error', response.message || 'Error loading data.');
                    }
                } catch (error) {
                    console.error('Error processing response:', error);
                    showAlert('error', 'Error processing server response.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', xhr, status, error);
                console.error('Response text:', xhr.responseText);
                
                let errorMessage = 'Error loading data. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showAlert('error', errorMessage);
            },
             complete: function() {
                 console.log('AJAX request completed');
                 
                 // Hide loading spinner
                 hideLoadingSpinner();
                 
                 // Re-enable filter button
                 filterBtn.prop('disabled', false);
                 filterSpinner.addClass('d-none');
                 filterText.html('<i data-feather="search"></i> Apply Filters');
                 
         
                 
                 console.log('Filter button re-enabled and Select2 reinitialized');
             }
        });
    }
    
     function clearFilters() {
         // Clear all filter inputs
         $('#filterDateFrom').val('');
         $('#filterDateTo').val('');
         $('#filterBus').val('').trigger('change');
         $('#filterBusSubType').val('').trigger('change');
         $('#filterDriver').val('').trigger('change');
         $('#filterAssistant').val('').trigger('change');
         $('#filterSearch').val('');
         
         // Clear current filters
         currentFilters = {};
         
         // Apply filters to show all data
         applyFilters();
         
         showAlert('info', 'Filters cleared. Showing all records.');
     }
    
    function exportPdf() {
        if (Object.keys(currentFilters).length === 0) {
            showAlert('warning', 'Please apply filters first to export data.');
            return;
        }
        
        // Build export URL with current filters
        const exportUrl = window.allBusesListUrls.exportPdf + '?' + $.param(currentFilters);
        
        // Open in new window
        window.open(exportUrl, '_blank');
        
        showAlert('success', 'PDF export started. Check your downloads.');
    }
    
     function exportExcel() {
         if (Object.keys(currentFilters).length === 0) {
             showAlert('warning', 'Please apply filters first to export data.');
             return;
         }
         
         // Build export URL with current filters
         const exportUrl = window.allBusesListUrls.exportExcel + '?' + $.param(currentFilters);
         
         // Open in new window
         window.open(exportUrl, '_blank');
         
         showAlert('success', 'Excel export started. Check your downloads.');
     }
     
     function deleteEntry(deleteUrl, deleteId, csrfToken) {
         console.log('Deleting entry with ID:', deleteId);
         console.log('Delete URL:', deleteUrl);
         console.log('CSRF Token:', csrfToken);
         
        // Show loading state
        const loadingSwal = Swal.fire({
            title: 'Deleting...',
            text: 'Please wait while we delete the entry.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
         
         // Make AJAX delete request
         $.ajax({
             url: deleteUrl,
             method: 'DELETE',
             data: {
                 _token: csrfToken,
                 _method: 'DELETE'
             },
             beforeSend: function(xhr) {
                 console.log('Sending delete request to:', deleteUrl);
                 console.log('Request data:', {
                     _token: csrfToken,
                     _method: 'DELETE'
                 });
                 
                 // Set CSRF token in header as well
                 xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
             },
            success: function(response) {
                console.log('Delete success response:', response);
                
                // Close loading alert explicitly
                Swal.close();
                // Also try to dismiss any remaining alerts
                Swal.stopLoading();
                
                // Force close any remaining dialogs after a short delay
                setTimeout(function() {
                    if (Swal.isLoading()) {
                        Swal.stopLoading();
                    }
                    if (Swal.isVisible()) {
                        Swal.close();
                    }
                }, 100);
                
                // Show success toast only
                if (typeof toastr !== 'undefined') {
                    console.log('Showing toastr success message');
                    toastr.success('The entry has been deleted successfully.');
                } else {
                    console.log('toastr not available, using SweetAlert fallback');
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The entry has been deleted successfully.',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        timer: 3000,
                        timerProgressBar: true
                    });
                }
                
                // Refresh the data immediately
                console.log('Refreshing data after delete...');
                applyFilters();
            },
             error: function(xhr, status, error) {
                 console.error('Delete error:', xhr, status, error);
                 console.error('Response text:', xhr.responseText);
                 console.error('Status code:', xhr.status);
                 
                 // Close loading alert explicitly
                 Swal.close();
                 Swal.stopLoading();
                 
                 // Force close any remaining dialogs after a short delay
                 setTimeout(function() {
                     if (Swal.isLoading()) {
                         Swal.stopLoading();
                     }
                     if (Swal.isVisible()) {
                         Swal.close();
                     }
                 }, 100);
                 
                 let errorMessage = 'Error deleting entry. Please try again.';
                 
                 if (xhr.responseJSON && xhr.responseJSON.message) {
                     errorMessage = xhr.responseJSON.message;
                 } else if (xhr.status === 419) {
                     errorMessage = 'Session expired. Please refresh the page and try again.';
                 } else if (xhr.status === 404) {
                     errorMessage = 'Entry not found. It may have already been deleted.';
                 } else if (xhr.status === 403) {
                     errorMessage = 'You do not have permission to delete this entry.';
                 }
                 
                // Show error toast
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage);
                } else {
                    // Fallback to SweetAlert if toastr is not available
                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
             }
         });
     }
    
     function showLoadingSpinner() {
         console.log('Showing loading spinner');
         $('#loadingSpinner').removeClass('d-none');
         $('#dataTableContainer').addClass('d-none');
         $('#paginationContainer').addClass('d-none');
     }
     
     function hideLoadingSpinner() {
         console.log('Hiding loading spinner');
         $('#loadingSpinner').addClass('d-none');
         $('#dataTableContainer').removeClass('d-none');
         $('#paginationContainer').removeClass('d-none');
     }
    
    function showAlert(type, message) {
        let alertClass = type === 'success' ? 'alert-success' : 
                        type === 'error' ? 'alert-danger' : 
                        type === 'warning' ? 'alert-warning' : 'alert-info';
        
        let alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        $('#alertContainer').html(alertHtml);
        
        // Auto-hide success and info alerts after 3 seconds
        if (type === 'success' || type === 'info') {
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 3000);
        }
    }
    
    // Test delete functionality
    window.testDelete = function() {
        console.log('Testing delete functionality...');
        const deleteBtn = $('.delete-btn').first();
        if (deleteBtn.length) {
            console.log('Found delete button, triggering click...');
            deleteBtn.trigger('click');
        } else {
            console.log('No delete buttons found');
        }
    };
    
    // Test toastr functionality
    window.testToastr = function() {
        console.log('Testing toastr functionality...');
        console.log('toastr available:', typeof toastr !== 'undefined');
        
        if (typeof toastr !== 'undefined') {
            console.log('Showing test toast...');
            toastr.success('Test toast message - this should appear!');
        } else {
            console.error('toastr is not available!');
        }
    };
    
    // Test data refresh
    window.testRefresh = function() {
        console.log('Testing data refresh...');
        applyFilters();
    };
     
     // Initialize on page load
     console.log('All Buses List AJAX initialized');
     console.log('Delete buttons found:', $('.delete-btn').length);
     console.log('Forms found:', $('form').length);
});
// Delete using AJAX with SweetAlert
$(document).on('click', '.delete-btn', function (e) {
    e.preventDefault();

    let form = $(this).closest('form');
    let deleteUrl = form.attr('action');
    let id = form.find('input[name="id"]').val();

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
            $.ajax({
                url: deleteUrl,
                method: 'POST',
                data: form.serialize(),
                beforeSend: function() {
                    Swal.fire({
                        title: "Deleting...",
                        text: "Please wait a moment.",
                        didOpen: () => {
                            Swal.showLoading();
                        },
                        allowOutsideClick: false
                    });
                },
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        toastr.success(response.message || "Deleted successfully!");
                        // Remove the row visually without reloading
                        form.closest('tr').fadeOut(500, function() { $(this).remove(); });
                    } else {
                        toastr.error(response.message || "Something went wrong!");
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    toastr.error("Error occurred while deleting.");
                }
            });
        }
    });
});