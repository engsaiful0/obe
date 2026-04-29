'use strict';

$(function () {
  // Auto-submit form when per_page changes
  $('#per_page').on('change', function() {
    $(this).closest('form').submit();
  });

  // Initialize Select2 for modal elements when modal is shown
  $('#add-new-record').on('shown.bs.offcanvas', function () {
    $('#add_expense_head_id').select2({
      placeholder: "Select Expense Head",
      allowClear: true,
      dropdownParent: $('#add-new-record'),
      width: '100%'
    });
    
    // Move the select2 container inside the input group to preserve the icon
    var $select2Container = $('#add_expense_head_id').next('.select2-container');
    var $inputGroup = $('#add_expense_head_id').closest('.input-group');
    $inputGroup.append($select2Container);
  });

  // Initialize Select2 for edit modal elements when modal is shown
  $('#edit-record').on('shown.bs.offcanvas', function () {
    $('#edit_expense_head_id').select2({
      placeholder: "Select Expense Head",
      allowClear: true,
      dropdownParent: $('#edit-record'),
      width: '100%'
    });
    
    // Move the select2 container inside the input group to preserve the icon
    var $select2Container = $('#edit_expense_head_id').next('.select2-container');
    var $inputGroup = $('#edit_expense_head_id').closest('.input-group');
    $inputGroup.append($select2Container);
  });

  // Destroy Select2 when modals are hidden to prevent conflicts
  $('#add-new-record').on('hidden.bs.offcanvas', function () {
    if ($('#add_expense_head_id').hasClass('select2-hidden-accessible')) {
      $('#add_expense_head_id').select2('destroy');
    }
  });

  $('#edit-record').on('hidden.bs.offcanvas', function () {
    if ($('#edit_expense_head_id').hasClass('select2-hidden-accessible')) {
      $('#edit_expense_head_id').select2('destroy');
    }
  });

  // Add Expense Form
  $('#form-add-new-record').on('submit', function (e) {
    e.preventDefault();
    var form = $(this);
    var url = window.expenseStoreUrl || '/app/expenses';
    var data = new FormData(this);
    var submitBtn = form.find('button[type="submit"]');

    submitBtn.prop('disabled', true);
    submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');

    $.ajax({
      url: url,
      method: 'POST',
      data: data,
      processData: false,
      contentType: false,
      success: function (response) {
        submitBtn.prop('disabled', false);
        submitBtn.html('Submit');
        toastr.success('Expense added successfully!');
        $('#add-new-record').offcanvas('hide');
        form[0].reset();
        window.location.reload();
      },
      error: function (xhr) {
        submitBtn.prop('disabled', false);
        submitBtn.html('Submit');
        var errors = xhr.responseJSON.errors;
        var errorMessages = '';
        $.each(errors, function (key, value) {
          errorMessages += value[0] + '\n';
        });
        toastr.error(errorMessages, 'Error');
      }
    });
  });

  // Edit Expense Button Click
  $(document).on('click', '.edit-expense', function () {
    var expenseId = $(this).data('id');
    var expenseHeadId = $(this).data('expense-head-id');
    var expenseDate = $(this).data('expense-date');
    var amount = $(this).data('amount');
    var remarks = $(this).data('remarks');

    $('#edit_expense_id').val(expenseId);
    $('#edit_expense_head_id').val(expenseHeadId);
    $('#edit_expense_date').val(expenseDate);
    $('#edit_amount').val(amount);
    $('#edit_remarks').val(remarks);

    $('#edit-record').offcanvas('show');
  });

  // Edit Expense Form
  $('#form-edit-record').on('submit', function (e) {
    e.preventDefault();
    var form = $(this);
    var expenseId = $('#edit_expense_id').val();
    var url = (window.expenseUpdateUrl || '/app/expenses/:id').replace(':id', expenseId);
    var data = new FormData(this);
    var submitBtn = form.find('button[type="submit"]');

    submitBtn.prop('disabled', true);
    submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

    $.ajax({
      url: url,
      method: 'POST',
      data: data,
      processData: false,
      contentType: false,
      headers: {
        'X-HTTP-Method-Override': 'PUT'
      },
      success: function (response) {
        submitBtn.prop('disabled', false);
        submitBtn.html('Update');
        toastr.success('Expense updated successfully!');
        $('#edit-record').offcanvas('hide');
        window.location.reload();
      },
      error: function (xhr) {
        submitBtn.prop('disabled', false);
        submitBtn.html('Update');
        var errors = xhr.responseJSON.errors;
        var errorMessages = '';
        $.each(errors, function (key, value) {
          errorMessages += value[0] + '\n';
        });
        toastr.error(errorMessages, 'Error');
      }
    });
  });

  // Delete Expense
  $(document).on('click', '.delete-expense', function () {
    var expenseId = $(this).data('id');
    var deleteUrl = $(this).data('url');
    var row = $(this).closest('tr');
    
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: {
        confirmButton: 'btn btn-primary me-3',
        cancelButton: 'btn btn-label-secondary'
      },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        $.ajax({
          url: deleteUrl,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            row.fadeOut(300, function() {
              $(this).remove();
            });
            toastr.success('Expense deleted successfully!');
            // Reload page after 1 second to refresh pagination
            setTimeout(function() {
              window.location.reload();
            }, 1000);
          },
          error: function (error) {
            toastr.error('Error deleting expense!');
            console.log(error);
          }
        });
      }
    });
  });

  // ajax setup
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });
});