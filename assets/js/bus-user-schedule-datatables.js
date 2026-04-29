$(document).ready(function() {
    'use strict';

    // Initialize filter handlers
    initializeFilterHandlers();

    function initializeFilterHandlers() {
        // Filter form submission
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });

        // Clear filters
        $('#clearFilters').on('click', function() {
            $('#filterForm')[0].reset();
            applyFilters();
        });

        // Export PDF
        $('#exportPdfBtn').on('click', function() {
            const formData = $('#filterForm').serialize();
            const currentUrl = window.location.pathname;
            window.open(currentUrl + '/export-pdf?' + formData, '_blank');
        });

        // Print
        $('#printBtn').on('click', function() {
            const formData = $('#filterForm').serialize();
            const currentUrl = window.location.pathname;
            window.open(currentUrl + '/print?' + formData, '_blank');
        });
    }

    function applyFilters() {
        const formData = $('#filterForm').serialize();
        
        $.ajax({
            url: window.location.pathname,
            type: 'GET',
            data: formData,
            success: function(response) {
                if (response.html) {
                    $('#scheduleTableContainer').html(response.html);
                    if (response.pagination) {
                        // Update pagination if needed
                    }
                }
            },
            error: function() {
                showAlert('error', 'Error applying filters.');
            }
        });
    }

    // View schedule
    window.viewSchedule = function(id) {
        $.ajax({
            url: `/app/bus-schedules/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.schedule) {
                    showScheduleDetails(response.schedule);
                }
            },
            error: function() {
                showAlert('error', 'Error loading schedule details.');
            }
        });
    };

    // Edit schedule
    window.editSchedule = function(id) {
        window.location.href = `/app/bus-schedules/${id}/edit`;
    };

    // Delete schedule
    window.deleteSchedule = function(id) {
        if (confirm('Are you sure you want to delete this schedule? This action cannot be undone.')) {
            $.ajax({
                url: `/app/bus-schedules/${id}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        applyFilters(); // Reload table
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        showAlert('error', xhr.responseJSON.message);
                    } else {
                        showAlert('error', 'Error deleting schedule.');
                    }
                }
            });
        }
    };

    function showScheduleDetails(schedule) {
        // Create and show a modal with schedule details
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
                                    <strong>Route:</strong> ${schedule.bus_route?.route_name || 'N/A'}<br>
                                    <strong>Start Time:</strong> ${schedule.start_time || 'N/A'}<br>
                                </div>
                                <div class="col-md-6">
                                    <strong>Bus:</strong> ${schedule.bus?.model_name || 'N/A'} (${schedule.vehicle?.registration_number || 'N/A'})<br>
                                    <strong>Driver:</strong> ${schedule.driver?.full_name || 'N/A'}<br>
                                    <strong>Assistant:</strong> ${schedule.assistant?.assistant_name || 'N/A'}<br>
                                    <strong>Bus User:</strong> ${schedule.bus_user?.bus_user_name || 'N/A'}<br>
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

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
});
