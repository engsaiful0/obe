$(document).ready(function() {
    let itemRowIndex = {{ $issue->issueItems->count() }};

    // Initialize Select2
    $('.select2').select2({
        dropdownParent: $('#issue-form')
    });

    // Add item row
    $('#add-item-btn').on('click', function() {
        $.ajax({
            url: window.issueUrls.productRow,
            method: 'GET',
            data: { row_index: itemRowIndex },
            success: function(response) {
                $('#issue-items-container tbody').append(response);
                $('.select2').select2({
                    dropdownParent: $('#issue-form')
                });
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
        $(this).closest('tr').remove();
        updateSerialNumbers();
    });

    // Update serial numbers
    function updateSerialNumbers() {
        $('#issue-items-container tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    // Form submission
    $('#issue-form').on('submit', function(e) {
        e.preventDefault();
        
        if ($('#issue-form').hasClass('form-loading')) {
            return false;
        }

        // Validate form
        if (!validateForm()) {
            return false;
        }

        // Show loading state
        $('#issue-form').addClass('form-loading');
        
        // Get submit button and store original text
        var $submitBtn = $('button[type="submit"]');
        var originalButtonText = $submitBtn.html();
        $submitBtn.data('original-text', originalButtonText);
        
        // Disable submit button to prevent double submission and show spinner
        $submitBtn.prop('disabled', true).html('<i class="ti ti-loader-2 ti-spin me-2"></i> Updating...');

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
        $('#issue-items-container tbody tr').each(function() {
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
            url: window.issueUrls.update.replace(':id', formData.get('issue_id')),
            method: 'PUT',
            data: issueData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                toastr.success(response.message);
                
                // Redirect to view page
                setTimeout(function() {
                    window.location.href = window.issueUrls.view;
                }, 1500);
            },
            error: function(xhr, status, error) {
                console.error('Error updating issue:', error);
                let errorMessage = 'Error updating issue';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                }
                
                // Restore submit button on error
                var $submitBtn = $('button[type="submit"]');
                $submitBtn.prop('disabled', false).html($submitBtn.data('original-text') || 'Update Issue');
                $('#issue-form').removeClass('form-loading');
                
                toastr.error(errorMessage);
            },
            complete: function() {
                $('#issue-form').removeClass('form-loading');
                // Restore submit button
                var $submitBtn = $('button[type="submit"]');
                $submitBtn.prop('disabled', false).html($submitBtn.data('original-text') || 'Update Issue');
            }
        });
    });

    // Form validation
    function validateForm() {
        let isValid = true;
        const errors = [];

        // Validate required fields
        if (!$('#issue_number').val()) {
            errors.push('Issue number is required');
            isValid = false;
        }

        if (!$('#employee_id').val()) {
            errors.push('Employee is required');
            isValid = false;
        }

        if (!$('#date').val()) {
            errors.push('Date is required');
            isValid = false;
        }

        // Validate items
        let hasValidItems = false;
        $('#issue-items-container tbody tr').each(function() {
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

    // Reset form
    $('button[type="reset"]').on('click', function() {
        location.reload();
    });
});
