$(document).ready(function () {
    'use strict';

    // Initialize toastr configuration if not already set
    if (typeof toastr !== 'undefined' && !toastr.options) {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
    }

    // Auto-apply filters on change (with debounce for search input)
    let searchTimeout;
    $('#search').on('keyup', function(e) {
        clearTimeout(searchTimeout);
        // Wait 500ms after user stops typing before applying filter
        searchTimeout = setTimeout(function() {
            loadSchedules();
        }, 500);
    });

    // Enter key on search input - apply immediately
    $('#search').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            clearTimeout(searchTimeout);
            loadSchedules();
        }
    });

    // Auto-apply filters when select fields change
    $('.filter-select').on('change', function() {
        loadSchedules();
    });

    // Auto-apply filters when date field changes
    $('#effective_from').on('change', function() {
        loadSchedules();
    });

    // Manual filter button (still available)
    $('#applyFilters').on('click', function() {
        loadSchedules();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#filterForm')[0].reset();
        // Reset Select2 if used
        $('.filter-select').each(function() {
            if ($(this).data('select2')) {
                $(this).val(null).trigger('change');
            }
        });
        loadSchedules();
    });

    // Load schedules via AJAX
    function loadSchedules() {
        showFilterSpinner();

        // Get form data
        const formData = $('#filterForm').serialize();

        $.ajax({
            url: window.busScheduleUrls.index,
            type: 'GET',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                hideFilterSpinner();

                if (response.success) {
                    // Update table content
                    $('#scheduleTableContainer').html(response.html);
                    
                    // Update pagination if provided
                    if (response.pagination) {
                        // Remove old pagination wrapper
                        $('#scheduleTableContainer').find('.pagination-wrapper').remove();
                        // Append new pagination
                        $('#scheduleTableContainer').append('<div class="d-flex justify-content-center mt-3 pagination-wrapper">' + response.pagination + '</div>');
                    }
                    
                    // Update URL without reload
                    const params = new URLSearchParams(formData);
                    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                    window.history.pushState({}, '', newUrl);
                    
                    // Scroll to top of table container
                    $('html, body').animate({
                        scrollTop: $('#scheduleTableContainer').offset().top - 100
                    }, 300);
                } else {
                    // Handle case where response doesn't have success flag but has html
                    if (response.html) {
                        $('#scheduleTableContainer').html(response.html);
                    } else {
                        // Fallback: try to use response directly
                        $('#scheduleTableContainer').html(response);
                    }
                }
            },
            error: function(xhr) {
                hideFilterSpinner();
                let errorMessage = 'Failed to load schedules. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 0) {
                    errorMessage = 'Network error. Please check your connection.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please try again later.';
                }
                
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage, 'Error');
                } else {
                    alert('Error: ' + errorMessage);
                }
            }
        });
    }

    // View schedule
    $(document).on('click', '.view-schedule', function() {
        const scheduleId = $(this).data('id');
        const url = window.busScheduleUrls.view.replace(':id', scheduleId);
        window.location.href = url;
    });

    // Delete schedule
    $(document).on('click', '.delete-schedule', function() {
        const scheduleId = $(this).data('id');
        const $btn = $(this);
        
        if (typeof Swal !== 'undefined') {
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
                    deleteSchedule(scheduleId, $btn);
                }
            });
        } else {
            if (confirm('Are you sure you want to delete this schedule?')) {
                deleteSchedule(scheduleId, $btn);
            }
        }
    });

    // Delete schedule function
    function deleteSchedule(scheduleId, $btn) {
        const originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: window.busScheduleUrls.destroy.replace(':id', scheduleId),
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $btn.prop('disabled', false).html(originalHtml);
                
                if (response.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || 'Schedule deleted successfully!', 'Success');
                    } else {
                        alert('Success: ' + (response.message || 'Schedule deleted successfully!'));
                    }
                    // Reload the table
                    loadSchedules();
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(response.message || 'Failed to delete schedule.', 'Error');
                    } else {
                        alert('Error: ' + (response.message || 'Failed to delete schedule.'));
                    }
                }
            },
            error: function(xhr) {
                $btn.prop('disabled', false).html(originalHtml);
                
                let errorMessage = 'Failed to delete schedule.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage, 'Error');
                } else {
                    alert('Error: ' + errorMessage);
                }
            }
        });
    }

    // Show filter spinner
    function showFilterSpinner() {
        const $spinner = $('#filterSpinner');
        const $icon = $('#filterIcon');
        const $text = $('#filterText');
        const $btn = $('#applyFilters');
        
        $spinner.removeClass('d-none');
        $spinner.css('display', 'inline-block');
        $icon.addClass('d-none');
        $text.text('Loading...');
        $btn.prop('disabled', true);
        $btn.addClass('disabled');
        
        // Also show loading overlay on table container
        $('#scheduleTableContainer').css('opacity', '0.6');
        $('#scheduleTableContainer').css('pointer-events', 'none');
    }

    // Hide filter spinner
    function hideFilterSpinner() {
        const $spinner = $('#filterSpinner');
        const $icon = $('#filterIcon');
        const $text = $('#filterText');
        const $btn = $('#applyFilters');
        
        $spinner.addClass('d-none');
        $icon.removeClass('d-none');
        $text.text('Filter');
        $btn.prop('disabled', false);
        $btn.removeClass('disabled');
        
        // Remove loading overlay from table container
        $('#scheduleTableContainer').css('opacity', '1');
        $('#scheduleTableContainer').css('pointer-events', 'auto');
    }

    // Handle pagination links via AJAX
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            showFilterSpinner();
            
            // Use the full URL from the pagination link (it already includes filters)
            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    hideFilterSpinner();
                    
                    if (response.success) {
                        $('#scheduleTableContainer').html(response.html);
                        
                        // Update pagination if provided
                        if (response.pagination) {
                            // Remove old pagination wrapper
                            $('#scheduleTableContainer').find('.pagination-wrapper').remove();
                            // Append new pagination
                            $('#scheduleTableContainer').append('<div class="d-flex justify-content-center mt-3 pagination-wrapper">' + response.pagination + '</div>');
                        }
                        
                        // Update URL
                        window.history.pushState({}, '', url);
                        
                        // Scroll to top of table
                        $('html, body').animate({
                            scrollTop: $('#scheduleTableContainer').offset().top - 100
                        }, 300);
                    }
                },
                error: function(xhr) {
                    hideFilterSpinner();
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load page. Please try again.', 'Error');
                    } else {
                        alert('Error: Failed to load page. Please try again.');
                    }
                }
            });
        }
    });

    // Toast notification function
    function showToast(type, title, message) {
        if (typeof toastr !== 'undefined') {
            // Ensure toastr is configured
            if (!toastr.options) {
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "timeOut": "5000"
                };
            }
            toastr[type](message, title);
        } else {
            alert(title + ': ' + message);
        }
    }
});

