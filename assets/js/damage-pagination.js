$(document).ready(function() {
    let ajaxRequest = null;
    let searchTimeout = null;

    // Function to load damages via AJAX
    function loadDamages(page = 1) {
        // Cancel previous ajax request if exists
        if (ajaxRequest) {
            ajaxRequest.abort();
        }

        const form = $('#search-form');
        const formData = form.serializeArray();
        
        // Add page to form data
        formData.push({ name: 'page', value: page });

        // Show spinner, hide table container
        const spinner = $('#damages-spinner');
        const container = $('#damages-table-container');
        
        spinner.removeClass('d-none');
        container.hide();

        // Disable filter form during loading
        form.find('input, select, button').prop('disabled', true);

        // Make AJAX request
        ajaxRequest = $.ajax({
            url: form.attr('data-ajax-url') || form.attr('action'),
            method: 'GET',
            data: $.param(formData),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    // Update table content
                    container.html(response.html + response.pagination);
                    
                    // Update URL without page reload
                    const params = new URLSearchParams($.param(formData));
                    const newUrl = window.location.pathname + '?' + params.toString();
                    window.history.pushState({}, '', newUrl);
                    
                    // Update export links
                    updateExportLinks();
                } else {
                    toastr.error('Error loading damages. Please try again.');
                }
            },
            error: function(xhr) {
                if (xhr.statusText !== 'abort') {
                    console.error('Error loading damages:', xhr);
                    toastr.error('Error loading damages. Please try again.');
                }
            },
            complete: function() {
                // Hide spinner, show table container
                spinner.addClass('d-none');
                container.show();

                // Re-enable filter form
                form.find('input, select, button').prop('disabled', false);
                
                ajaxRequest = null;
            }
        });
    }

    // Function to update export links with current filters
    function updateExportLinks() {
        const formData = $('#search-form').serialize();
        const queryString = formData ? '?' + formData : '';
        
        $('#export-excel-btn').attr('href', window.damageUrls.exportExcel + queryString);
        $('#export-pdf-btn').attr('href', window.damageUrls.exportPdf + queryString);
    }

    // Handle filter changes
    $('#warehouse_id, #item_id, #date_from, #date_to').on('change', function() {
        loadDamages(1);
    });

    // Search with debounce
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            loadDamages(1);
        }, 500);
    });

    // Form submission (prevent default and use AJAX)
    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        loadDamages(1);
    });

    // Handle pagination clicks
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        if (url) {
            const page = new URL(url).searchParams.get('page') || 1;
            loadDamages(page);
        }
    });

    // Delete damage
    $(document).on('click', '.delete-damage', function(e) {
        e.preventDefault();
        const damageId = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel',
            customClass: {
                confirmButton: 'btn btn-primary me-3',
                cancelButton: 'btn btn-label-secondary'
            },
            buttonsStyling: false
        }).then(function(result) {
            if (result.value) {
                // Show spinner
                const spinner = $('<span class="spinner-border spinner-border-sm me-2" role="status"><span class="visually-hidden">Loading...</span></span>');
                $('.delete-damage[data-id="' + damageId + '"]').prepend(spinner);
                
                $.ajax({
                    url: window.damageUrls.destroy.replace(':id', damageId),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message || 'Damage deleted successfully.');
                        
                        // Reload data after a short delay
                        setTimeout(function() {
                            const currentPage = new URLSearchParams(window.location.search).get('page') || 1;
                            loadDamages(currentPage);
                        }, 1000);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error deleting damage:', error);
                        let errorMessage = 'Error deleting damage';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join('<br>');
                        }

                        toastr.error(errorMessage);
                        spinner.remove();
                    }
                });
            }
        });
    });

    // Export buttons with spinner
    $('#export-excel-btn, #export-pdf-btn').on('click', function(e) {
        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Exporting...').prop('disabled', true);
        
        // Re-enable after 10 seconds in case of timeout
        setTimeout(function() {
            $btn.html(originalHtml).prop('disabled', false);
        }, 10000);
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Initialize popovers
    $('[data-bs-toggle="popover"]').popover();
});
