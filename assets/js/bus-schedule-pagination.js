$(document).ready(function() {
    'use strict';

    // Global variables
    let filterTimeout = null;
    let isFiltering = false;

    // Initialize all handlers
    initializeActionHandlers();
    initializeFilterHandlers();
    initializeSpinners();

    function initializeFilterHandlers() {
        var baseUrl = window.location.origin;
        var pathName = window.location.pathname;
        var appIndex = pathName.indexOf('/app/');
        if (appIndex !== -1) {
            baseUrl += pathName.substring(0, appIndex);
        }

        // Apply filters button
        $('#applyFilters').on('click', function() {
            applyFilters();
        });

        // Clear filters button
        $('#clearFilters').on('click', function() {
            clearFilters();
        });

        // Auto-filter on select change (with debounce)
        $('.filter-select').on('change', function() {
            // Cancel previous request if still pending
            cancelPreviousRequest();
            
            // Show immediate feedback
            showFilterLoading(true);
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                applyFilters();
            }, 300); // Reduced delay for better responsiveness
        });

        // Auto-filter on input change (with debounce)
        $('.filter-input').on('input', function() {
            // Cancel previous request if still pending
            cancelPreviousRequest();
            
            // Show immediate feedback
            showFilterLoading(true);
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                applyFilters();
            }, 800); // Reduced delay for better responsiveness
        });

        // Handle browser back/forward navigation
        $(window).on('popstate', function() {
            applyFilters();
        });

        // Enter key on search input
        $('#search').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                applyFilters();
            }
        });
    }

    function initializeActionHandlers() {
        var baseUrl = window.location.origin;
        var pathName = window.location.pathname;
        var appIndex = pathName.indexOf('/app/');
        if (appIndex !== -1) {
            baseUrl += pathName.substring(0, appIndex);
        }
        // View schedule details
        window.viewSchedule = function(id) {
            showLoadingSpinner($('body'));
            
            $.ajax({
                url: baseUrl + `/app/bus-schedules/${id}`,
                type: 'GET',
                success: function(response) {
                    hideLoadingSpinner($('body'));
                    if (response.schedule) {
                        showScheduleDetails(response.schedule);
                    }
                },
                error: function() {
                    hideLoadingSpinner($('body'));
                    showNotification('error', 'Error loading schedule details.');
                }
            });
        };

        // Edit schedule
        window.editSchedule = function(id) {
            showLoadingSpinner($('body'));
            window.location.href = baseUrl + `/app/bus-schedules/${id}/edit`;
        };

      

        // Export PDF
        $('#exportPdfBtn').on('click', function() {
            showLoadingSpinner($(this));
            const formData = $('form').serialize();
            window.open(baseUrl + '/app/bus-schedules/export-pdf?' + formData, '_blank');
            hideLoadingSpinner($(this));
        });

        // Print
        $('#printBtn').on('click', function() {
            showLoadingSpinner($(this));
            const formData = $('form').serialize();
            window.open(baseUrl + '/app/bus-schedules/print?' + formData, '_blank');
            hideLoadingSpinner($(this));
        });
    }

    function initializeSpinners() {
        // Initialize loading states for various elements
        $('.btn').each(function() {
            if (!$(this).find('.spinner-border').length) {
                $(this).prepend('<span class="spinner-border spinner-border-sm d-none" style="margin-right: 5px;"></span>');
            }
        });
    }

    function showScheduleDetails(schedule) {
        const modalHtml = `
            <div class="modal fade" id="viewModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Schedule Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Start Stoppage:</strong> ${schedule.start_stoppage?.stoppage_name || 'N/A'}<br>
                                    <strong>End Stoppage:</strong> ${schedule.end_stoppage?.stoppage_name || 'N/A'}<br>
                                    <strong>Route:</strong> ${schedule.vehicle_route?.route_name || 'N/A'}<br>
                                    <strong>Start Time:</strong> ${schedule.start_time || 'N/A'}<br>
                                </div>
                                <div class="col-md-6">
                                    <strong>Vehicle:</strong> ${schedule.vehicle?.model_name || 'N/A'} (${schedule.vehicle?.registration_number || 'N/A'})<br>
                                    <strong>Driver:</strong> ${schedule.driver?.full_name || 'N/A'}<br>
                                    <strong>Assistant:</strong> ${schedule.assistant?.assistant_name || 'N/A'}<br>
                                    <strong>Vehicle User:</strong> ${schedule.vehicle_user?.vehicle_user_name || 'N/A'}<br>
                                    <strong>Keyword:</strong> ${schedule.keyword ? `<span class="badge bg-info">${schedule.keyword.keyword_name}</span>` : '<span class="text-muted">No Keyword</span>'}<br>
                                    <strong>Status:</strong> <span class="badge bg-${schedule.status === 'active' ? 'success' : (schedule.status === 'inactive' ? 'warning' : 'danger')}">${schedule.status}</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#viewModal').remove();
        
        // Add new modal
        $('body').append(modalHtml);
        $('#viewModal').modal('show');
    }

    // Enhanced spinner functions
    function showButtonSpinner(spinnerSelector, textSelector, loadingText) {
        $(spinnerSelector).removeClass('d-none');
        $(textSelector).text(loadingText);
        $(spinnerSelector).closest('button').prop('disabled', true);
    }

    function hideButtonSpinner(spinnerSelector, textSelector, originalText) {
        $(spinnerSelector).addClass('d-none');
        $(textSelector).text(originalText);
        $(spinnerSelector).closest('button').prop('disabled', false);
    }

    function showLoadingSpinner(element) {
        if (element.find('.loading-spinner').length === 0) {
            element.append('<div class="loading-spinner text-center p-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        }
    }

    function hideLoadingSpinner(element) {
        element.find('.loading-spinner').remove();
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

    // Helper function to cancel previous requests
    function cancelPreviousRequest() {
        if (window.currentFilterRequest && window.currentFilterRequest.readyState !== 4) {
            window.currentFilterRequest.abort();
        }
    }

    // Filter functions
    function applyFilters() {
        // Prevent multiple simultaneous requests
        if (isFiltering) {
            return;
        }
        
        isFiltering = true;
        var baseUrl = window.location.origin;
        var pathName = window.location.pathname;
        var appIndex = pathName.indexOf('/app/');
        if (appIndex !== -1) {
            baseUrl += pathName.substring(0, appIndex);
        }

        // Show loading spinner
        showFilterLoading(true);

        // Get form data
        const formData = $('#filterForm').serialize();

        // Update URL with filter parameters
        const url = new URL(window.location);
        const params = new URLSearchParams(formData);
        url.search = params.toString();
        window.history.pushState({}, '', url);

        // Add request timeout
        const ajaxRequest = $.ajax({
            url: baseUrl + '/app/bus-schedules',
            type: 'GET',
            data: formData,
            timeout: 10000, // 10 second timeout
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                hideFilterLoading();
                isFiltering = false;
                
                if (response.html) {
                    // Add fade effect for smooth transition
                    $('#scheduleTableContainer').fadeOut(200, function() {
                        $(this).html(response.html).fadeIn(300);
                        
                        // Re-initialize Select2 for new content
                        if ($.fn.select2) {
                            $('#scheduleTableContainer select').select2({
                                placeholder: 'Select an option',
                                allowClear: true,
                                width: '100%'
                            });
                        }
                    });
                }
                
                if (response.pagination) {
                    // Update pagination if needed
                    $('.pagination').html(response.pagination);
                }

                // Show success notification for manual filter application
                if ($('#applyFilters').is(':focus')) {
                    showNotification('success', 'Filters applied successfully.');
                }
            },
            error: function(xhr, status, error) {
                hideFilterLoading();
                isFiltering = false;
                console.error('Filter Error:', xhr);
                
                // Don't show error for aborted requests
                if (status === 'abort') {
                    return;
                }
                
                let errorMessage = 'Error applying filters. Please try again.';
                
                if (status === 'timeout') {
                    errorMessage = 'Request timed out. Please try again.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please try again later.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Page not found. Please refresh the page.';
                }
                
                showNotification('error', errorMessage);
            }
        });

        // Store the current request for potential cancellation
        window.currentFilterRequest = ajaxRequest;
    }

    function clearFilters() {
        // Clear all filter inputs
        $('#filterForm')[0].reset();
        
        // Clear Select2 selections
        if ($.fn.select2) {
            $('.filter-select').val(null).trigger('change');
        }
        
        // Clear URL parameters
        const url = new URL(window.location);
        url.search = '';
        window.history.pushState({}, '', url);
        
        // Apply filters with cleared values
        applyFilters();
    }

    function showFilterLoading(show) {
        if (show) {
            // Show button spinner
            $('#filterSpinner').removeClass('d-none');
            $('#filterIcon').addClass('d-none');
            $('#filterText').text('Filtering...');
            $('#applyFilters').prop('disabled', true);
            
            // Add loading overlay to table container with enhanced styling
            if ($('#scheduleTableContainer').find('.loading-overlay').length === 0) {
                $('#scheduleTableContainer').css('position', 'relative');
                $('#scheduleTableContainer').append(`
                    <div class="loading-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
                         style="z-index: 10; background-color: rgba(255, 255, 255, 0.9); backdrop-filter: blur(2px);">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="text-muted">Loading schedules...</div>
                        </div>
                    </div>
                `);
            }
            
            // Add loading state to filter form
            $('#filterForm').addClass('loading');
            
        } else {
            // Hide button spinner
            $('#filterSpinner').addClass('d-none');
            $('#filterIcon').removeClass('d-none');
            $('#filterText').text('Filter');
            $('#applyFilters').prop('disabled', false);
            
            // Remove loading overlay with fade effect
            $('#scheduleTableContainer').find('.loading-overlay').fadeOut(300, function() {
                $(this).remove();
            });
            
            // Remove loading state from filter form
            $('#filterForm').removeClass('loading');
        }
    }

    function hideFilterLoading() {
        showFilterLoading(false);
    }

    // Initialize Select2 for better dropdown experience
    if ($.fn.select2) {
        $('select').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%'
        });
    }
});

