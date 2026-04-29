$(document).ready(function() {
    let itemRowIndex = 1;

    // Debug: Check if URLs are available
    console.log('Issue URLs:', window.issueUrls);
    console.log('Base URL:', AppUtils.getBaseUrl());
    
    // Fallback: If productRow URL is not available, create it manually
    if (!window.issueUrls || !window.issueUrls.productRow) {
        console.log('ProductRow URL not found, creating manually...');
        if (!window.issueUrls) {
            window.issueUrls = {};
        }
        window.issueUrls.productRow = AppUtils.buildUrl('app/issue/product-row');
        console.log('Manual productRow URL:', window.issueUrls.productRow);
    }

    // Initialize Select2
    $('.select2').select2({
        dropdownParent: $('#issue-form')
    });

    // Initialize remove button states
    updateRemoveButtonStates();
    
    // Initialize total quantity and individual displays
    console.log('Initializing quantity displays...');
    updateTotalQuantity();
    updateIndividualQuantityDisplays();
    
    // Also update when page is fully loaded
    $(window).on('load', function() {
        console.log('Page loaded, updating quantity displays...');
        updateTotalQuantity();
        updateIndividualQuantityDisplays();
    });
    
    // Force update after a short delay to ensure DOM is ready
    setTimeout(function() {
        console.log('Delayed update of quantity displays...');
        updateTotalQuantity();
        updateIndividualQuantityDisplays();
    }, 500);

    // Add event listeners for item selection and quantity changes
    $(document).on('change', '.item-select', function() {
        checkForDuplicates();
        updateTotalQuantity();
        updateIndividualQuantityDisplays();
    });

    // Also listen for when select2 is cleared
    $(document).on('select2:clear', '.item-select', function() {
        updateTotalQuantity();
        updateIndividualQuantityDisplays();
    });

    // Debounced quantity update to prevent excessive calculations
    let quantityUpdateTimeout;
    
    // Handle input events for quantity fields
    $(document).on('input keyup change', '.quantity-input', function() {
        console.log('Quantity input changed:', $(this).val());
        
        // Update individual display immediately
        const quantity = parseFloat($(this).val()) || 0;
        $(this).closest('tr').find('.quantity-display').text(quantity.toFixed(2));
        
        // Update total with debounce
        clearTimeout(quantityUpdateTimeout);
        quantityUpdateTimeout = setTimeout(function() {
            console.log('Updating total quantity...');
            updateTotalQuantity();
        }, 100);
    });
    
    // Also handle paste events
    $(document).on('paste', '.quantity-input', function() {
        setTimeout(() => {
            console.log('Quantity pasted:', $(this).val());
            const quantity = parseFloat($(this).val()) || 0;
            $(this).closest('tr').find('.quantity-display').text(quantity.toFixed(2));
            updateTotalQuantity();
        }, 10);
    });
    
    // Additional event listener for any changes to quantity inputs
    $(document).on('blur', '.quantity-input', function() {
        console.log('Quantity input blurred:', $(this).val());
        const quantity = parseFloat($(this).val()) || 0;
        $(this).closest('tr').find('.quantity-display').text(quantity.toFixed(2));
        updateTotalQuantity();
    });


    // Add item row
    $('#add-item-btn').on('click', function() {
        console.log('Add item button clicked');
        console.log('Product row URL:', window.issueUrls.productRow);
        console.log('Row index:', itemRowIndex);
        
        // Check if button exists
        if ($('#add-item-btn').length === 0) {
            console.error('Add item button not found!');
            return;
        }
        
        // Check if URLs are defined
        if (!window.issueUrls || !window.issueUrls.productRow) {
            console.error('Issue URLs not properly defined!');
            console.error('Available URLs:', window.issueUrls);
            return;
        }
        
        $.ajax({
            url: window.issueUrls.productRow,
            method: 'GET',
            data: { row_index: itemRowIndex },
            success: function(response) {
                console.log('Product row response:', response);
                $('#issue-items-container tbody').append(response);
                $('.select2').select2({
                    dropdownParent: $('#issue-form')
                });
                itemRowIndex++;
                updateSerialNumbers();
                updateRemoveButtonStates();
                updateTotalQuantity();
                updateIndividualQuantityDisplays();
            },
            error: function(xhr, status, error) {
                console.error('Error adding item row:', error);
                console.error('XHR:', xhr);
                console.error('Status:', status);
                toastr.error('Error adding item row');
            }
        });
    });

    // Remove item row
    $(document).on('click', '.remove-item-btn', function() {
        // Check if this is the only row remaining
        const totalRows = $('#issue-items-container tbody tr').length;
        
        if (totalRows <= 1) {
            toastr.warning('At least one item row must remain. Cannot delete the last row.');
            return;
        }
        
        $(this).closest('tr').remove();
        updateSerialNumbers();
        updateRemoveButtonStates();
        updateTotalQuantity();
        updateIndividualQuantityDisplays();
    });

    // Update serial numbers
    function updateSerialNumbers() {
        $('#issue-items-container tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    // Update remove button states based on number of rows
    function updateRemoveButtonStates() {
        const totalRows = $('#issue-items-container tbody tr').length;
        
        $('.remove-item-btn').each(function() {
            if (totalRows <= 1) {
                $(this).prop('disabled', true)
                       .addClass('disabled')
                       .attr('title', 'Cannot delete the last row');
            } else {
                $(this).prop('disabled', false)
                       .removeClass('disabled')
                       .attr('title', 'Remove this item');
            }
        });
    }

    // Check for duplicate items
    function checkForDuplicates() {
        const selectedItems = [];
        let hasDuplicates = false;
        
        $('#issue-items-container tbody tr').each(function() {
            const itemId = $(this).find('.item-select').val();
            if (itemId) {
                if (selectedItems.includes(itemId)) {
                    hasDuplicates = true;
                    $(this).find('.item-select').addClass('is-invalid');
                } else {
                    selectedItems.push(itemId);
                    $(this).find('.item-select').removeClass('is-invalid');
                }
            } else {
                $(this).find('.item-select').removeClass('is-invalid');
            }
        });
        
        return hasDuplicates;
    }

    // Calculate total quantity
    function calculateTotalQuantity() {
        let total = 0;
        $('#issue-items-container tbody tr').each(function() {
            const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
            total += quantity;
        });
        return total;
    }

    // Update total quantity display
    function updateTotalQuantity() {
        const total = calculateTotalQuantity();
        console.log('Total quantity calculated:', total);
        $('#total-quantity-display').text(total.toFixed(2));
        console.log('Total quantity display updated to:', total.toFixed(2));
    }

    // Update individual quantity displays
    function updateIndividualQuantityDisplays() {
        $('#issue-items-container tbody tr').each(function() {
            const quantity = parseFloat($(this).find('.quantity-input').val()) || 0;
            $(this).find('.quantity-display').text(quantity.toFixed(2));
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
        $submitBtn.prop('disabled', true).html('<i class="ti ti-loader-2 ti-spin me-2"></i> Saving...');

        // Prepare form data
        const formData = new FormData(this);
        const issueData = {
            issue_number: formData.get('issue_number'),
            employee_id: formData.get('employee_id'),
            date: formData.get('date'),
            remarks: formData.get('remarks'),
            serial: formData.get('serial'),
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
            url: window.issueUrls.store,
            method: 'POST',
            data: issueData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                toastr.success(response.message);
                
                // Reset form and items
                $('#issue-form')[0].reset();
                $('#issue-items-container tbody tr:not(:first)').remove();
                itemRowIndex = 1;
                updateSerialNumbers();
                $('.select2').val(null).trigger('change');
                
                // Redirect to view page
                setTimeout(function() {
                    window.location.href = window.issueUrls.view;
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
                
                // Restore submit button on error
                var $submitBtn = $('button[type="submit"]');
                $submitBtn.prop('disabled', false).html($submitBtn.data('original-text') || 'Save Issue');
                $('#issue-form').removeClass('form-loading');
                
                toastr.error(errorMessage);
            },
            complete: function() {
                $('#issue-form').removeClass('form-loading');
                // Restore submit button
                var $submitBtn = $('button[type="submit"]');
                $submitBtn.prop('disabled', false).html($submitBtn.data('original-text') || 'Save Issue');
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

        // Validate items - ensure at least one item is added
        let hasValidItems = false;
        let itemCount = 0;
        let emptyItemRows = 0;
        
        $('#issue-items-container tbody tr').each(function() {
            const itemId = $(this).find('.item-select').val();
            const quantity = $(this).find('.quantity-input').val();
            itemCount++;

            if (itemId && quantity && parseFloat(quantity) > 0) {
                hasValidItems = true;
            } else if (!itemId && !quantity) {
                emptyItemRows++;
            }
        });

        // Check if no items are added at all
        if (itemCount === 0) {
            errors.push('Please add at least one item to the issue list');
            isValid = false;
        } else if (!hasValidItems) {
            errors.push('At least one item must be selected with a valid quantity');
            isValid = false;
        }
        
        // Additional check: ensure the first row has valid data
        const firstRow = $('#issue-items-container tbody tr:first');
        const firstItemId = firstRow.find('.item-select').val();
        const firstQuantity = firstRow.find('.quantity-input').val();
        
        if (!firstItemId || !firstQuantity || parseFloat(firstQuantity) <= 0) {
            errors.push('The first item row must have a selected item and valid quantity');
            isValid = false;
        }

        // Check for duplicate items
        if (checkForDuplicates()) {
            errors.push('Duplicate items are not allowed. Please select different items.');
            isValid = false;
        }

        if (!isValid) {
            toastr.error(errors.join('<br>'));
        }

        return isValid;
    }

    // Reset form
    $('button[type="reset"]').on('click', function() {
        $('#issue-form')[0].reset();
        $('#issue-items-container tbody tr:not(:first)').remove();
        itemRowIndex = 1;
        updateSerialNumbers();
        updateRemoveButtonStates();
        updateTotalQuantity();
        updateIndividualQuantityDisplays();
        $('.select2').val(null).trigger('change');
    });
});
