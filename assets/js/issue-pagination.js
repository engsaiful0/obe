$(document).ready(function() {
    let itemRowIndex = 1;

    // Add item row
    $('#add-item-btn').on('click', function() {
        $.ajax({
            url: window.issueUrls.productRow,
            method: 'GET',
            data: { row_index: itemRowIndex },
            success: function(response) {
                $('#issue-items-container').append(response);
                itemRowIndex++;
                updateSerialNumbers();
            },
            error: function(xhr, status, error) {
                console.error('Error adding item row:', error);
                toastr.error('Error adding item row');
            }
        });
    });

    // Remove item row
    $(document).on('click', '.remove-item-btn', function() {
        $(this).closest('.issue-item-row').remove();
        updateSerialNumbers();
    });

    // Update serial numbers
    function updateSerialNumbers() {
        $('#issue-items-container .issue-item-row').each(function(index) {
            $(this).find('label').first().text(`Item ${index + 1} *`);
        });
    }

    // Form submission for add new record
    $('#form-add-new-record').on('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return false;
        }

        // Prepare form data
        const formData = new FormData(this);
        const issueData = {
            issue_number: formData.get('issue_number'),
            employee_id: formData.get('employee_id'),
            date: formData.get('date'),
            remarks: formData.get('remarks'),
            items: []
        };

        // Collect items data
        $('#issue-items-container .issue-item-row').each(function() {
            const itemId = $(this).find('.item-select').val();
            const unitId = $(this).find('.unit-select').val();
            const quantity = $(this).find('.quantity-input').val();

            if (itemId && quantity) {
                issueData.items.push({
                    item_id: itemId,
                    unit_id: unitId || null,
                    quantity: parseFloat(quantity)
                });
            }
        });

        // Submit data
        $.ajax({
            url: window.issueUrls.store,
            method: 'POST',
            data: issueData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                toastr.success(response.message);
                $('#form-add-new-record')[0].reset();
                $('#issue-items-container .issue-item-row:not(:first)').remove();
                itemRowIndex = 1;
                updateSerialNumbers();
                $('#add-new-record').offcanvas('hide');
                
                // Reload page to show new data
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            },
            error: function(xhr, status, error) {
                console.error('Error saving issue:', error);
                let errorMessage = 'Error saving issue';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                }
                
                toastr.error(errorMessage);
            }
        });
    });

    // Form validation
    function validateForm() {
        let isValid = true;
        const errors = [];

        // Validate required fields
        if (!$('#form-add-new-record #issue_number').val()) {
            errors.push('Issue number is required');
            isValid = false;
        }

        if (!$('#form-add-new-record #employee_id').val()) {
            errors.push('Employee is required');
            isValid = false;
        }

        if (!$('#form-add-new-record #date').val()) {
            errors.push('Date is required');
            isValid = false;
        }

        // Validate items
        let hasValidItems = false;
        $('#issue-items-container .issue-item-row').each(function() {
            const itemId = $(this).find('.item-select').val();
            const quantity = $(this).find('.quantity-input').val();

            if (itemId && quantity && parseFloat(quantity) > 0) {
                hasValidItems = true;
            }
        });

        if (!hasValidItems) {
            errors.push('At least one valid item is required');
            isValid = false;
        }

        if (!isValid) {
            toastr.error(errors.join('<br>'));
        }

        return isValid;
    }

    // Delete issue
    $(document).on('click', '.delete-issue', function(e) {
        e.preventDefault();
        const issueId = $(this).data('id');
        
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
                $.ajax({
                    url: window.issueUrls.destroy.replace(':id', issueId),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message || 'Issue deleted successfully.');
                        
                        // Reload page to refresh the list after a short delay
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error deleting issue:', error);
                        let errorMessage = 'Error deleting issue';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join('<br>');
                        }
                        
                        toastr.error(errorMessage);
                    }
                });
            }
        });
    });

    // Auto-submit search form on Enter key
    $('#search').on('keypress', function(e) {
        if (e.which === 13) {
            $('#search-form').submit();
        }
    });

    

    // Show loading state for search form
    $('#search-form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="ti ti-loader-2 spin"></i> Searching...');
        submitBtn.prop('disabled', true);
        
        // Re-enable button after a delay (in case of errors)
        setTimeout(function() {
            submitBtn.html(originalText);
            submitBtn.prop('disabled', false);
        }, 5000);
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Initialize popovers
    $('[data-bs-toggle="popover"]').popover();
});
