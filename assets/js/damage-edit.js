$(document).ready(function() {
    let itemRowIndex = $('#damage-items-container tr').length;

    // Add item row
    $('#add-item-btn').on('click', function() {
        $.ajax({
            url: window.damageUrls.productRow,
            method: 'GET',
            data: { row_index: itemRowIndex },
            success: function(response) {
                $('#damage-items-container').append(response);
                itemRowIndex++;
                updateSerialNumbers();
                // Initialize Select2 for new row
                $('#damage-items-container tr:last .select2').select2({
                    dropdownParent: $('#damage-form')
                });
            },
            error: function(xhr, status, error) {
                console.error('Error adding item row:', error);
                toastr.error('Error adding item row');
            }
        });
    });

    // Remove item row
    $(document).on('click', '.remove-item-btn', function() {
        if ($('#damage-items-container tr').length > 1) {
            $(this).closest('tr').remove();
            updateSerialNumbers();
        } else {
            toastr.warning('At least one item row is required');
        }
    });

    // Update serial numbers
    function updateSerialNumbers() {
        $('#damage-items-container tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    // Initialize Select2
    $('.select2').select2({
        dropdownParent: $('#damage-form')
    });

    // Form submission
    $('#damage-form').on('submit', function(e) {
        e.preventDefault();
        
        if ($('#damage-form').hasClass('form-loading')) {
            return false;
        }

        // Validate form
        if (!validateForm()) {
            return false;
        }

        // Show loading state
        $('#damage-form').addClass('form-loading');
        
        // Disable submit button and show spinner inside it
        const $submitBtn = $('button[type="submit"]');
        const originalBtnHtml = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Updating...');

        // Prepare form data
        const formData = new FormData(this);
        const damageData = {
            warehouse_id: formData.get('warehouse_id'),
            date: formData.get('date'),
            remarks: formData.get('remarks'),
            items: []
        };

        // Collect items data
        $('#damage-items-container tr').each(function() {
            const itemId = $(this).find('.item-select').val();
            const quantity = $(this).find('.quantity-input').val();
            const reason = $(this).find('.reason-input').val();
            const approximate = $(this).find('.approximate-input').val();

            if (itemId && quantity) {
                damageData.items.push({
                    item_id: itemId,
                    quantity: parseFloat(quantity),
                    reason: reason || null,
                    approximate: approximate ? parseFloat(approximate) : null
                });
            }
        });

        // Submit data
        $.ajax({
            url: window.damageUrls.update,
            method: 'PUT',
            data: damageData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                toastr.success(response.message);
                
                // Redirect to view page
                setTimeout(function() {
                    window.location.href = window.damageUrls.view;
                }, 1500);
            },
            error: function(xhr, status, error) {
                console.error('Error updating damage:', error);
                let errorMessage = 'Error updating damage';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('<br>');
                }
                
                toastr.error(errorMessage);
            },
            complete: function() {
                $('#damage-form').removeClass('form-loading');
                $submitBtn.prop('disabled', false).html(originalBtnHtml);
            }
        });
    });

    // Form validation
    function validateForm() {
        let isValid = true;
        const errors = [];

        // Validate required fields
        if (!$('#warehouse_id').val()) {
            errors.push('Warehouse is required');
            isValid = false;
        }

        if (!$('#date').val()) {
            errors.push('Date is required');
            isValid = false;
        }

        // Validate items
        let hasValidItems = false;
        $('#damage-items-container tr').each(function() {
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
});

