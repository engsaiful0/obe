/**
 * Purchase Add Form
 */

'use strict';

let fv, itemCounter = 0, globalItems = [], selectedItems = [];
document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const purchaseForm = document.getElementById('purchase-form');

    // Form validation for Add new record
    fv = FormValidation.formValidation(purchaseForm, {
      fields: {
        purchase_number: {
          validators: {
            notEmpty: {
              message: 'The purchase number is required'
            }
          }
        },
        supplier_id: {
          validators: {
            notEmpty: {
              message: 'The supplier is required'
            }
          }
        },
        date: {
          validators: {
            notEmpty: {
              message: 'The date is required'
            }
          }
        },
        payment_method_id: {
          validators: {
            notEmpty: {
              message: 'The payment method is required'
            }
          }
        },
        paid: {
          validators: {
            notEmpty: {
              message: 'The paid amount is required'
            },
            numeric: {
              message: 'The paid amount must be a number'
            }
          }
        },
        due: {
          validators: {
            notEmpty: {
              message: 'The due amount is required'
            },
            numeric: {
              message: 'The due amount must be a number'
            }
          }
        },
        net_total: {
          validators: {
            notEmpty: {
              message: 'The net total is required'
            },
            numeric: {
              message: 'The net total must be a number'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          // Use this for enabling/changing valid/invalid class
          // eleInvalidClass: '',
          eleValidClass: '',
          rowSelector: '.col-sm-12, .col-sm-6, .col-sm-4'
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
        // defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
        autoFocus: new FormValidation.plugins.AutoFocus()
      },
      init: instance => {
        instance.on('plugins.message.placed', function (e) {
          if (e.element.parentElement.classList.contains('input-group')) {
            e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
          }
        });
      }
    });
  })();
});



// Add new item row
function addItemRow() {
  itemCounter++;
  console.log('Adding new item row, counter:', itemCounter);
  
  // Get the add item button and show spinner
  var $addBtn = $('#add-item-btn');
  var originalText = $addBtn.html();
  $addBtn.prop('disabled', true).html('<i class="ti ti-loader-2 ti-spin"></i> Adding...');

  var baseUrl = window.location.origin;
  var pathName = window.location.pathname;
  var appIndex = pathName.indexOf('/app/');
  if (appIndex !== -1) {
    baseUrl += pathName.substring(0, appIndex);
  }
  console.log('window.base_url:', window.base_url);
  console.log('Base URL:', baseUrl);

  // Load the product row template via AJAX
  $.ajax({
    url: baseUrl + '/app/purchase/product-row',
    type: 'GET',
    data: {
      row_index: itemCounter
    },
    success: function (response) {
      console.log('Product row template loaded successfully');


      // Prepend the new row to the table body (add at top)
      $('#purchase-items-container tbody').prepend(response);
      console.log('New row prepended to table');
      
      // Update serial numbers for all rows
      updateSerialNumbers();

      // Initialize select2 for the new select
      const $newSelect = $('#item_select_' + itemCounter);
      $newSelect.select2({
        dropdownParent: $('#purchase-form'),
        placeholder: 'Select Item',
        width: '100%'
      });
      // Restore button state
      $addBtn.prop('disabled', false).html(originalText);

      console.log('New item row added successfully');
    },
    error: function (xhr, status, error) {
      console.error('Error loading product row template:', error);
      console.error('Response:', xhr.responseText);

      // Restore button state on error
      $addBtn.prop('disabled', false).html(originalText);

      toastr.error('Error loading product row template. Please try again.');
    }
  });
}


// Calculate net total
function calculateNetTotal() {
  var netTotal = 0;
  $('.total-price-input').each(function () {
    var rowTotal = parseFloat($(this).val()) || 0;
    netTotal += rowTotal;
    console.log('Adding row total to net total:', rowTotal);
  });
  
  console.log('Final net total:', netTotal);
  $('#net_total').val(netTotal.toFixed(2));
  
  // Also update paid and due amounts if they exist
  updatePaidAndDue();
}

// Update paid and due amounts based on net total
function updatePaidAndDue() {
  var netTotal = parseFloat($('#net_total').val()) || 0;
  var paidAmount = parseFloat($('#paid').val()) || 0;
  
  if(paidAmount > netTotal){
    $('#paid').val('');
    $('#paid').focus();
    $('#due').val(netTotal.toFixed(2));
    toastr.error('Paid amount cannot be greater than net total.');
    return;
  }
  // Calculate due amount
  var dueAmount = netTotal - paidAmount;
  
  // Update due field
  $('#due').val(dueAmount.toFixed(2));
  
  console.log('Updated amounts:', {
    netTotal: netTotal,
    paidAmount: paidAmount,
    dueAmount: dueAmount
  });
}

// Duplicate check function with automatic removal
function checkForDuplicateItems() {
  var selectedItems = [];
  var duplicateFound = false;
  var duplicateRow = null;
  var duplicateItemName = '';
  
  // Collect all selected items and find duplicates
    $('.item-select').each(function() {
    var selectedValue = $(this).val();
    if (selectedValue) {
      if (selectedItems.includes(selectedValue)) {
        duplicateFound = true;
        duplicateRow = $(this);
        duplicateItemName = $(this).find('option:selected').text();
        return false; // Break the loop
      }
      selectedItems.push(selectedValue);
    }
  });
  
  // Automatically clear the duplicate item selection
  if (duplicateFound && duplicateRow) {
    duplicateRow.val(''); // Clear the selection
    toastr.warning('Duplicate item "' + duplicateItemName + '" selection. Please select a different item.');
  }
  
  return {
    hasDuplicates: false, // Always return false since we auto-remove
    selectedItems: selectedItems
  };
}

// Update serial numbers for all rows
function updateSerialNumbers() {
  $('#purchase-items-container tbody tr').each(function(index) {
    $(this).find('td:first').text(index + 1);
  });
}

// Separate purchase store function with debugging
function storePurchase(purchaseData, callbacks) {
  console.log('=== PURCHASE STORE FUNCTION START ===');
  console.log('Purchase Data:', purchaseData);
  
  // Validate required fields
  var requiredFields = ['purchase_number', 'supplier_id', 'date', 'paid', 'due', 'net_total', 'payment_method_id', 'serial'];
  var missingFields = [];
  
  requiredFields.forEach(function(field) {
    if (!purchaseData[field] || purchaseData[field] === '') {
      missingFields.push(field);
    }
  });
  
  if (missingFields.length > 0) {
    console.error('Missing required fields:', missingFields);
    if (callbacks && callbacks.error) {
      callbacks.error('Missing required fields: ' + missingFields.join(', '));
    }
    return false;
  }
  
  // Validate items
  if (!purchaseData.items || purchaseData.items.length === 0) {
    console.error('No items provided');
    if (callbacks && callbacks.error) {
      callbacks.error('No items provided');
    }
    return false;
  }
  
  console.log('Validation passed. Items count:', purchaseData.items.length);
  
  // Prepare AJAX data
  var ajaxData = {
    _token: $('meta[name="csrf-token"]').attr('content'),
    purchase_number: purchaseData.purchase_number,
    supplier_id: purchaseData.supplier_id,
    date: purchaseData.date,
    paid: parseFloat(purchaseData.paid),
    due: parseFloat(purchaseData.due),
    net_total: parseFloat(purchaseData.net_total),
    payment_method_id: purchaseData.payment_method_id,
    serial: purchaseData.serial,
    remarks: purchaseData.remarks,
    items: purchaseData.items
  };
  
  console.log('AJAX Data prepared:', ajaxData);

  var baseUrl = window.location.origin;
  var pathName = window.location.pathname;
  var appIndex = pathName.indexOf('/app/');
  if (appIndex !== -1) {
    baseUrl += pathName.substring(0, appIndex);
  }
  
  // Check if this is an edit form
  var isEditForm = $('#purchase-form').data('purchase-id');
  var url = isEditForm ? baseUrl + '/app/purchase/' + isEditForm : baseUrl + '/app/purchase';
  var method = isEditForm ? 'PUT' : 'POST';
  
  // Make AJAX request
  $.ajax({
    url: url,
    type: method,
    data: ajaxData,
    beforeSend: function(xhr) {
      console.log('AJAX request starting...');
      if (callbacks && callbacks.beforeSend) {
        callbacks.beforeSend();
      }
    },
    success: function(response) {
      console.log('=== PURCHASE STORE SUCCESS ===');
      console.log('Response:', response);
      
      if (callbacks && callbacks.success) {
        callbacks.success(response);
      }
    },
    error: function(xhr, status, error) {
      console.error('=== PURCHASE STORE ERROR ===');
      console.error('Status:', status);
      console.error('Error:', error);
      console.error('Response Text:', xhr.responseText);
      console.error('Response JSON:', xhr.responseJSON);
      
      var errorMessage = 'An error occurred while saving the purchase.';
      
      if (xhr.responseJSON && xhr.responseJSON.errors) {
        var errors = xhr.responseJSON.errors;
        var errorMessages = [];
        for (var key in errors) {
          if (errors.hasOwnProperty(key)) {
            errorMessages.push(errors[key][0]);
          }
        }
        errorMessage = errorMessages.join('<br>');
        console.error('Validation errors:', errors);
      } else if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
        console.error('Server message:', xhr.responseJSON.message);
      }
      
      if (callbacks && callbacks.error) {
        callbacks.error(errorMessage, xhr);
      }
    },
    complete: function() {
      console.log('=== PURCHASE STORE COMPLETE ===');
      if (callbacks && callbacks.complete) {
        callbacks.complete();
      }
      }
    });
  }

// Add new purchase function
function addPurchase(purchaseData, callbacks) {
  console.log('=== ADD PURCHASE FUNCTION START ===');
  console.log('Purchase Data:', purchaseData);
  
  // Validate required fields
  var requiredFields = ['purchase_number', 'supplier_id', 'date', 'paid', 'due', 'net_total', 'payment_method_id', 'serial'];
  var missingFields = [];
  
  requiredFields.forEach(function(field) {
    if (!purchaseData[field] || purchaseData[field] === '') {
      missingFields.push(field);
    }
  });
  
  if (missingFields.length > 0) {
    console.error('Missing required fields:', missingFields);
    if (callbacks && callbacks.error) {
      callbacks.error('Missing required fields: ' + missingFields.join(', '));
    }
    return false;
  }
  
  // Validate items
  if (!purchaseData.items || purchaseData.items.length === 0) {
    console.error('No items provided');
    if (callbacks && callbacks.error) {
      callbacks.error('No items provided');
    }
    return false;
  }
  
  console.log('Validation passed. Items count:', purchaseData.items.length);
  
  // Prepare AJAX data for ADD
  var ajaxData = {
    _token: $('meta[name="csrf-token"]').attr('content'),
    purchase_number: purchaseData.purchase_number,
    supplier_id: purchaseData.supplier_id,
    date: purchaseData.date,
    paid: parseFloat(purchaseData.paid),
    due: parseFloat(purchaseData.due),
    net_total: parseFloat(purchaseData.net_total),
    payment_method_id: purchaseData.payment_method_id,
    serial: purchaseData.serial,
    remarks: purchaseData.remarks,
    items: purchaseData.items
  };
  
  console.log('AJAX Data prepared for ADD:', ajaxData);

  var baseUrl = window.location.origin;
  var pathName = window.location.pathname;
  var appIndex = pathName.indexOf('/app/');
  if (appIndex !== -1) {
    baseUrl += pathName.substring(0, appIndex);
  }
  
  var url = baseUrl + '/app/purchase';
  
  // Make AJAX request for ADD
  $.ajax({
    url: url,
    type: 'POST',
    data: ajaxData,
    beforeSend: function(xhr) {
      console.log('ADD AJAX request starting...');
      if (callbacks && callbacks.beforeSend) {
        callbacks.beforeSend(xhr);
      }
    },
    success: function(response) {
      console.log('=== ADD PURCHASE SUCCESS ===');
      console.log('Response:', response);
      
      if (callbacks && callbacks.success) {
        callbacks.success(response);
      }
    },
    error: function(xhr, status, error) {
      console.error('=== ADD PURCHASE ERROR ===');
      console.error('Status:', status);
      console.error('Error:', error);
      console.error('Response Text:', xhr.responseText);
      console.error('Response JSON:', xhr.responseJSON);
      
      var errorMessage = 'An error occurred while adding the purchase.';
      
      if (xhr.responseJSON && xhr.responseJSON.errors) {
        var errors = xhr.responseJSON.errors;
        var errorMessages = [];
        for (var key in errors) {
          if (errors.hasOwnProperty(key)) {
            errorMessages.push(errors[key][0]);
          }
        }
        errorMessage = errorMessages.join(', ');
      } else if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
      }
      
      if (callbacks && callbacks.error) {
        callbacks.error(errorMessage);
      }
    },
    complete: function() {
      console.log('=== ADD PURCHASE COMPLETE ===');
      if (callbacks && callbacks.complete) {
        callbacks.complete();
      }
    }
  });
}

// Update existing purchase function
function updatePurchase(purchaseData, callbacks) {
  console.log('=== UPDATE PURCHASE FUNCTION START ===');
  console.log('Purchase Data:', purchaseData);
  
  // Validate required fields
  var requiredFields = ['purchase_number', 'supplier_id', 'date', 'paid', 'due', 'net_total', 'payment_method_id', 'serial'];
  var missingFields = [];
  
  requiredFields.forEach(function(field) {
    if (!purchaseData[field] || purchaseData[field] === '') {
      missingFields.push(field);
    }
  });
  
  if (missingFields.length > 0) {
    console.error('Missing required fields:', missingFields);
    if (callbacks && callbacks.error) {
      callbacks.error('Missing required fields: ' + missingFields.join(', '));
    }
    return false;
  }
  
  // Validate items
  if (!purchaseData.items || purchaseData.items.length === 0) {
    console.error('No items provided');
    if (callbacks && callbacks.error) {
      callbacks.error('No items provided');
    }
    return false;
  }
  
  console.log('Validation passed. Items count:', purchaseData.items.length);
  
  // Get purchase ID from form
  var purchaseId = $('#purchase-form').data('purchase-id');
  if (!purchaseId) {
    console.error('Purchase ID not found');
    if (callbacks && callbacks.error) {
      callbacks.error('Purchase ID not found');
    }
    return false;
  }
  
  // Prepare AJAX data for UPDATE
  var ajaxData = {
    _token: $('meta[name="csrf-token"]').attr('content'),
    _method: 'PUT',
    purchase_number: purchaseData.purchase_number,
    supplier_id: purchaseData.supplier_id,
    date: purchaseData.date,
    paid: parseFloat(purchaseData.paid),
    due: parseFloat(purchaseData.due),
    net_total: parseFloat(purchaseData.net_total),
    payment_method_id: purchaseData.payment_method_id,
    serial: purchaseData.serial,
    remarks: purchaseData.remarks,
    items: purchaseData.items
  };
  
  console.log('AJAX Data prepared for UPDATE:', ajaxData);

  var baseUrl = window.location.origin;
  var pathName = window.location.pathname;
  var appIndex = pathName.indexOf('/app/');
  if (appIndex !== -1) {
    baseUrl += pathName.substring(0, appIndex);
  }
  
  var url = baseUrl + '/app/purchase/' + purchaseId;
  
  // Make AJAX request for UPDATE
  $.ajax({
    url: url,
    type: 'POST', // Laravel uses POST with _method: 'PUT'
    data: ajaxData,
    beforeSend: function(xhr) {
      console.log('UPDATE AJAX request starting...');
      if (callbacks && callbacks.beforeSend) {
        callbacks.beforeSend(xhr);
      }
    },
    success: function(response) {
      console.log('=== UPDATE PURCHASE SUCCESS ===');
      console.log('Response:', response);
      
      if (callbacks && callbacks.success) {
        callbacks.success(response);
      }
    },
    error: function(xhr, status, error) {
      console.error('=== UPDATE PURCHASE ERROR ===');
      console.error('Status:', status);
      console.error('Error:', error);
      console.error('Response Text:', xhr.responseText);
      console.error('Response JSON:', xhr.responseJSON);
      
      var errorMessage = 'An error occurred while updating the purchase.';
      
      if (xhr.responseJSON && xhr.responseJSON.errors) {
        var errors = xhr.responseJSON.errors;
        var errorMessages = [];
        for (var key in errors) {
          if (errors.hasOwnProperty(key)) {
            errorMessages.push(errors[key][0]);
          }
        }
        errorMessage = errorMessages.join(', ');
      } else if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
      }
      
      if (callbacks && callbacks.error) {
        callbacks.error(errorMessage);
      }
    },
    complete: function() {
      console.log('=== UPDATE PURCHASE COMPLETE ===');
      if (callbacks && callbacks.complete) {
        callbacks.complete();
      }
    }
  });
}
  
// Helper function to collect purchase data
function collectPurchaseData() {
  console.log('=== COLLECTING PURCHASE DATA ===');
  
  // Collect form data
  var purchaseData = {
    purchase_number: $('#purchase_number').val(),
    supplier_id: $('#supplier_id').val(),
    date: $('#date').val(),
    paid: $('#paid').val(),
    due: $('#due').val(),
    net_total: $('#net_total').val(),
    payment_method_id: $('#payment_method_id').val(),
    serial: $('input[name="serial"]').val(),
    remarks: $('#remarks').val(),
    items: []
  };
  
  console.log('Form data collected:', purchaseData);
  
  // Collect items data
  $('#purchase-items-container tbody tr').each(function(index) {
    var itemId = $(this).find('.item-select').val();
    var unitId = $(this).find('.unit-select').val();
    var quantity = $(this).find('.quantity-input').val();
    var unitPrice = $(this).find('.unit-price-input').val();
    var totalPrice = $(this).find('.total-price-input').val();

    if (itemId && quantity && unitPrice) {
      var itemData = {
        item_id: itemId,
        unit_id: unitId || null,
        quantity: parseFloat(quantity),
        unit_price: parseFloat(unitPrice),
        total_price: parseFloat(totalPrice) || (parseFloat(quantity) * parseFloat(unitPrice))
      };
      
      purchaseData.items.push(itemData);
      console.log('Item ' + (index + 1) + ':', itemData);
    }
  });
  
  console.log('Total items collected:', purchaseData.items.length);
  console.log('Complete purchase data:', purchaseData);
  
  return purchaseData;
}

// Update all item dropdowns to show selected items
function updateAllItemDropdowns() {
  var duplicateCheck = checkForDuplicateItems();
  var selectedItems = duplicateCheck.selectedItems;
  
  $('.item-select').each(function() {
    var currentSelect = $(this);
    var currentValue = currentSelect.val();
    
    // Get all options and update their disabled state
    currentSelect.find('option').each(function() {
      var optionValue = $(this).val();
      var option = $(this);
      
      if (optionValue && optionValue !== currentValue) {
        // Disable if this item is selected in another dropdown
        if (selectedItems.includes(optionValue)) {
          option.prop('disabled', true);
          option.text(option.text().replace(' (Already Selected)', '') + ' (Already Selected)');
        } else {
          option.prop('disabled', false);
          option.text(option.text().replace(' (Already Selected)', ''));
        }
      }
    });
    
    // Refresh select2 to show changes
    currentSelect.trigger('change.select2');
  });
}

// Handle item selection change with duplicate check
$(document).on('change', '.item-select', function() {
  var currentSelect = $(this);
  var selectedValue = currentSelect.val();
  var previousValue = currentSelect.data('previous-value') || '';
  
  console.log('Item selection changed:', {
    selectedValue: selectedValue,
    previousValue: previousValue
  });
  
  // Check for duplicates and automatically remove if found
  var duplicateCheck = checkForDuplicateItems();
  
  // Store current value as previous for next change
  currentSelect.data('previous-value', selectedValue);
  
  // Update all dropdowns to show current selections
  updateAllItemDropdowns();
  
  console.log('Item selection updated successfully');
});

// Handle remove item button click
$(document).on('click', '.remove-item-btn', function() {
  if ($('#purchase-items-container tbody tr').length > 1) {
    var row = $(this).closest('tr');
    var selectedItemId = row.find('.item-select').val();
    
    console.log('Removing item row, selected item:', selectedItemId);
    
    // Remove the row
    row.remove();
    
    // Update serial numbers after removal
    updateSerialNumbers();
    
    // Update all dropdowns to show available items
  updateAllItemDropdowns();
    
    // Recalculate totals
    calculateNetTotal();
    
    console.log('Item row removed successfully');
  } else {
    toastr.warning('At least one item row is required.');
  }
});

// Add item button
$('#add-item-btn').on('click', function () {
  addItemRow();
});
// Form submission
if (typeof fv !== 'undefined' && fv && fv.on) {
  fv.on('core.form.valid', function () {
    // Duplicates are automatically handled, no need to check here

    // Validate items
    var items = [];
    $('#purchase-items-container tbody tr').each(function () {
      var itemId = $(this).find('.item-select').val();
      var quantity = $(this).find('.quantity-input').val();
      var unitPrice = $(this).find('.unit-price-input').val();

      if (itemId && quantity && unitPrice) {
        items.push({
          item_id: itemId,
          quantity: quantity,
          unit_price: unitPrice
        });
      }
    });

    if (items.length === 0) {
      toastr.error('Please add at least one item.');
      return;
    }

    // Get submit button and form elements
    var $submitBtn = $('#purchase-form button[type="submit"]');
    var $form = $('#purchase-form');
    var $formInputs = $form.find('input, select, textarea, button');
    
    // Store original button text
    var originalButtonText = $submitBtn.html();

    // Collect purchase data
    var purchaseData = collectPurchaseData();
    
    // Determine if this is an edit form and use appropriate function
    var isEditForm = $('#purchase-form').data('purchase-id');
    var purchaseFunction = isEditForm ? updatePurchase : addPurchase;
    
    // Use the appropriate function
    purchaseFunction(purchaseData, {
      beforeSend: function() {
        // Disable form and show spinner
        $formInputs.prop('disabled', true);
        $submitBtn.prop('disabled', true);
        $submitBtn.html('<i class="ti ti-loader-2 ti-spin me-2"></i> Saving...');
        $form.addClass('form-loading');
        
        // Add visual feedback
        console.log('Form loading state activated');
      },
      success: function(response) {
        var successMessage = isEditForm ? 'Purchase updated successfully.' : 'Purchase created successfully.';
        toastr.success(successMessage);
        
        // Get the purchase ID from response
        var purchaseId = response.data ? response.data.id : null;
        
        if (purchaseId) {
          // Redirect to print page after a short delay
          setTimeout(function() {
            var baseUrl = window.location.origin;
            var pathName = window.location.pathname;
            var appIndex = pathName.indexOf('/app/');
            if (appIndex !== -1) {
              baseUrl += pathName.substring(0, appIndex);
            }
            window.location.href = baseUrl + '/app/purchase/print/' + purchaseId;
          }, 1500); // 1.5 second delay to show success message
        } else {
          // Fallback: redirect to view page
          setTimeout(function() {
            var baseUrl = window.location.origin;
            var pathName = window.location.pathname;
            var appIndex = pathName.indexOf('/app/');
            if (appIndex !== -1) {
              baseUrl += pathName.substring(0, appIndex);
            }
            window.location.href = baseUrl + '/app/purchase/view-purchase';
          }, 1500);
        }
      },
      error: function(errorMessage, xhr) {
        // Re-enable form on error
        $formInputs.prop('disabled', false);
        $submitBtn.prop('disabled', false);
        $submitBtn.html(originalButtonText);
        $form.removeClass('form-loading');
        
        toastr.error(errorMessage);
      },
      complete: function() {
        console.log('Purchase store operation completed');
      }
    });
  }); // ✅ properly close fv.on('core.form.valid', function ...)
} else {
  console.error('FormValidation (fv) is not available. Form submission will not work properly.');

  // Fallback: Direct form submission without validation
  $('#purchase-form button[type="submit"]').on('click', function (e) {
    e.preventDefault();
    console.log('Using fallback form submission');

    var items = [];
    $('#purchase-items-container tbody tr').each(function () {
      var itemId = $(this).find('.item-select').val();
      var unitId = $(this).find('.unit-select').val();
      var quantity = $(this).find('.quantity-input').val();
      var unitPrice = $(this).find('.unit-price-input').val();

      if (itemId && quantity && unitPrice) {
        items.push({
          item_id: itemId,
          unit_id: unitId || null,
          quantity: quantity,
          unit_price: unitPrice
        });
      }
    });

    if (items.length === 0) {
      toastr.error('Please add at least one item.');
      return;
    }

    // Get form elements for disabling
    var $submitBtn = $(this);
    var $form = $('#purchase-form');
    var $formInputs = $form.find('input, select, textarea, button');
    
    // Store original button text
    var originalButtonText = $submitBtn.html();

    // Collect purchase data
    var purchaseData = collectPurchaseData();
    
    // Determine if this is an edit form and use appropriate function
    var isEditForm = $('#purchase-form').data('purchase-id');
    var purchaseFunction = isEditForm ? updatePurchase : addPurchase;
    
    // Use the appropriate function
    purchaseFunction(purchaseData, {
      beforeSend: function() {
        // Disable form and show spinner
        $formInputs.prop('disabled', true);
        $submitBtn.prop('disabled', true);
        $submitBtn.html('<i class="ti ti-loader-2 ti-spin me-2"></i> Saving...');
        $form.addClass('form-loading');
        
        // Add visual feedback
        console.log('Form loading state activated');
      },
      success: function(response) {
        var successMessage = isEditForm ? 'Purchase updated successfully.' : 'Purchase created successfully.';
        toastr.success(successMessage);
        
        // Get the purchase ID from response
        var purchaseId = response.data ? response.data.id : null;
        
        if (purchaseId) {
          // Redirect to print page after a short delay
          setTimeout(function() {
            var baseUrl = window.location.origin;
            var pathName = window.location.pathname;
            var appIndex = pathName.indexOf('/app/');
            if (appIndex !== -1) {
              baseUrl += pathName.substring(0, appIndex);
            }
            window.location.href = baseUrl + '/app/purchase/print/' + purchaseId;
          }, 1500); // 1.5 second delay to show success message
        } else {
          // Fallback: redirect to view page
          setTimeout(function() {
            var baseUrl = window.location.origin;
            var pathName = window.location.pathname;
            var appIndex = pathName.indexOf('/app/');
            if (appIndex !== -1) {
              baseUrl += pathName.substring(0, appIndex);
            }
            window.location.href = baseUrl + '/app/purchase/view-purchase';
          }, 1500);
        }
      },
      error: function(errorMessage, xhr) {
        // Re-enable form on error
        $formInputs.prop('disabled', false);
        $submitBtn.prop('disabled', false);
        $submitBtn.html(originalButtonText);
        $form.removeClass('form-loading');
        
        toastr.error(errorMessage);
      },
      complete: function() {
        console.log('Purchase store operation completed (fallback)');
      }
    });
  });
}


// Initialize on page load
$(document).ready(function () {
  console.log('Document ready - starting initialization');
  // Use event delegation for better reliability (this should work for all rows)
  $(document).on('input', '.quantity-input, .unit-price-input', function () {
    console.log('Event delegation triggered on:', this);
    var row = $(this).closest('tr');
    var quantity = parseFloat(row.find('.quantity-input').val()) || 0;
    var unitPrice = parseFloat(row.find('.unit-price-input').val()) || 0;
    var totalPrice = quantity * unitPrice;
    
    console.log('Event delegation calculation:', {
      quantity: quantity,
      unitPrice: unitPrice,
      totalPrice: totalPrice
    });
    
    row.find('.total-price-input').val(totalPrice.toFixed(2));
    calculateNetTotal();
  });
  
  // Add event listener for paid amount changes
  $('#paid').on('input', function () {
    updatePaidAndDue();
  });
  
  
  console.log('Initial page load - events bound for first row');
  
  // Test if events are working
  console.log('Quantity inputs found:', $('.quantity-input').length);
  console.log('Unit price inputs found:', $('.unit-price-input').length);
  console.log('Total price inputs found:', $('.total-price-input').length);
  
  // Test manual calculation after a delay
  setTimeout(function () {
    console.log('Testing manual calculation...');
    var firstQuantity = $('.quantity-input').first();
    var firstUnitPrice = $('.unit-price-input').first();
    var firstTotal = $('.total-price-input').first();
    
    console.log('First quantity input:', firstQuantity.length, firstQuantity);
    console.log('First unit price input:', firstUnitPrice.length, firstUnitPrice);
    console.log('First total input:', firstTotal.length, firstTotal);
    
    // Test if we can manually set values and calculate
    if (firstQuantity.length > 0 && firstUnitPrice.length > 0 && firstTotal.length > 0) {
      console.log('All elements found, testing calculation...');
      // firstQuantity.val('5');
      // firstUnitPrice.val('10');
      
      // Trigger the calculation manually
      var row = firstQuantity.closest('.purchase-item-row');
      var quantity = parseFloat(row.find('.quantity-input').val()) || 0;
      var unitPrice = parseFloat(row.find('.unit-price-input').val()) || 0;
      var totalPrice = quantity * unitPrice;
      
      console.log('Manual calculation test:', {
        quantity: quantity,
        unitPrice: unitPrice,
        totalPrice: totalPrice
      });
      
      row.find('.total-price-input').val(totalPrice.toFixed(2));
      calculateNetTotal();
    }
  }, 1000);
});
