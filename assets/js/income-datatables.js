/**
 * Income CRUD with AJAX and Spinner
 * Enhanced with proper spinner utilities and error handling
 */

'use strict';

let fv, offCanvasEl, dt_basic;

// Utility functions for spinner and loading states
const SpinnerUtils = {
    show: function(element, text = 'Loading...') {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', true);
        element.data('original-text', element.html());
        element.html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${text}`);
    },
    
    hide: function(element, originalText = null) {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', false);
        // Restore original text if stored, otherwise use provided text
        const text = originalText || element.data('original-text');
        if (text) {
            element.html(text);
        }
    },
    
    showTable: function() {
        if (dt_basic && typeof dt_basic.processing === 'function') {
            dt_basic.processing(true);
        }
    },
    
    hideTable: function() {
        if (dt_basic && typeof dt_basic.processing === 'function') {
            dt_basic.processing(false);
        }
    }
};

// Enhanced AJAX utility with error handling
const AjaxUtils = {
    request: function(options) {
        const defaults = {
            type: 'GET',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                if (options.showSpinner && options.spinnerElement) {
                    SpinnerUtils.show(options.spinnerElement, options.spinnerText);
                }
                if (options.showTableSpinner) {
                    SpinnerUtils.showTable();
                }
            },
            complete: function() {
                if (options.showSpinner && options.spinnerElement) {
                    SpinnerUtils.hide(options.spinnerElement);
                }
                if (options.showTableSpinner) {
                    SpinnerUtils.hideTable();
                }
            },
            success: function(response) {
                if (options.onSuccess) {
                    options.onSuccess(response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {xhr, status, error});
                AjaxUtils.handleError(xhr, options);
            }
        };
        
        return $.ajax($.extend(defaults, options));
    },
    
    handleError: function(xhr, options = {}) {
        let message = 'An error occurred. Please try again.';
        
        if (xhr.status === 422) {
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                message = Object.values(errors).flat().join('<br>');
                // Show field-specific errors
                AjaxUtils.showFieldErrors(errors);
            }
        } else if (xhr.status === 500) {
            message = 'Server error. Please contact support.';
        } else if (xhr.status === 404) {
            message = 'Resource not found.';
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }
        
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert('Error: ' + message);
        }
        
        if (options.onError) {
            options.onError(xhr, message);
        }
    },
    
    showFieldErrors: function(errors) {
        // Clear previous errors
        $('.form-control, .form-select, textarea').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        // Show new errors
        $.each(errors, function(field, messages) {
            const fieldElement = $(`[name="${field}"]`);
            if (fieldElement.length) {
                fieldElement.addClass('is-invalid');
                const errorMessage = Array.isArray(messages) ? messages[0] : messages;
                fieldElement.closest('.col-sm-12').append(`<div class="invalid-feedback">${errorMessage}</div>`);
            }
        });
    }
};

// Load Income Heads dropdown
function loadIncomeHeads() {
    AjaxUtils.request({
        url: window.incomeUrls.getIncomeHeads,
        type: 'GET',
        showSpinner: false,
        onSuccess: function(response) {
            const select = $('#income_head_id');
            const currentValue = select.val();
            select.empty().append('<option value="">Select Income Head</option>');
            
            if (response.data) {
                response.data.forEach(function(item) {
                    const selected = currentValue == item.id ? 'selected' : '';
                    select.append(`<option value="${item.id}" ${selected}>${item.name}</option>`);
                });
            }
        },
        onError: function(xhr, errorMessage) {
            console.error('Error loading income heads:', xhr);
        }
    });
}

// Load Employees dropdown
function loadEmployees() {
    AjaxUtils.request({
        url: window.incomeUrls.getEmployees,
        type: 'GET',
        showSpinner: false,
        onSuccess: function(response) {
            const select = $('#employee_id');
            const currentValue = select.val();
            select.empty().append('<option value="">Select Employee</option>');
            
            if (response.data) {
                response.data.forEach(function(item) {
                    const selected = currentValue == item.id ? 'selected' : '';
                    const displayName = item.employee_name + (item.employee_unique_id ? ' (' + item.employee_unique_id + ')' : '');
                    select.append(`<option value="${item.id}" ${selected}>${displayName}</option>`);
                });
            }
        },
        onError: function(xhr, errorMessage) {
            console.error('Error loading employees:', xhr);
        }
    });
}

document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const formAddNewRecord = document.getElementById('form-add-new-record');

    // Load dropdowns on page load
    loadIncomeHeads();
    loadEmployees();

    setTimeout(() => {
      const newRecord = document.querySelector('.create-new'),
        offCanvasElement = document.querySelector('#add-new-record');

      // To open offCanvas, to add new record
      if (newRecord) {
        newRecord.addEventListener('click', function () {
          offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
          // Empty fields on offCanvas open
          $('#income_head_id').val('').trigger('change');
          $('#amount').val('');
          $('#income_date').val('');
          $('#employee_id').val('').trigger('change');
          $('#remarks').val('');
          $('#form-add-new-record').removeAttr('data-id');
          // Clear validation errors
          $('.form-control, .form-select, textarea').removeClass('is-invalid');
          $('.invalid-feedback').remove();
          // Update modal title
          document.querySelector('#exampleModalLabel').textContent = 'New Income';
          // Reload dropdowns
          loadIncomeHeads();
          loadEmployees();
          // Open offCanvas with form
          offCanvasEl.show();
        });
      }
    }, 200);

    // Form validation for Add new record
    fv = FormValidation.formValidation(formAddNewRecord, {
      fields: {
        income_head_id: {
          validators: {
            notEmpty: {
              message: 'The income head is required'
            }
          }
        },
        amount: {
          validators: {
            notEmpty: {
              message: 'The amount is required'
            },
            numeric: {
              message: 'The amount must be a number',
              decimalSeparator: '.'
            },
            greaterThan: {
              min: 0,
              message: 'The amount must be greater than or equal to 0'
            }
          }
        },
        income_date: {
          validators: {
            notEmpty: {
              message: 'The date is required'
            },
            date: {
              format: 'YYYY-MM-DD',
              message: 'The date is not valid'
            }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({
          eleValidClass: '',
          rowSelector: '.col-sm-12'
        }),
        submitButton: new FormValidation.plugins.SubmitButton(),
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

// datatable (jquery)
$(function () {
  var dt_basic_table = $('.datatables-basic');

  // DataTable with buttons
  // --------------------------------------------------------------------

  if (dt_basic_table.length) {
    dt_basic = dt_basic_table.DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: window.incomeUrls.getData,
        type: 'GET',
        dataSrc: 'data',
        error: function(xhr, error, thrown) {
          console.error('DataTable AJAX Error:', {xhr, error, thrown});
          if (typeof toastr !== 'undefined') {
            toastr.error('Failed to load incomes. Please try again.');
          }
        }
      },
      columns: [
        {
          data: 'id',
          name: 'id',
          render: function (data, type, row, meta) {
            return meta.row + 1;
          }
        },
        { 
          data: 'income_head_name',
          name: 'income_head_name'
        },
        { 
          data: 'amount',
          name: 'amount',
          render: function (data, type, row) {
            return parseFloat(data).toFixed(2);
          }
        },
        { 
          data: 'income_date',
          name: 'income_date',
          render: function (data, type, row) {
            if (data) {
              const date = new Date(data);
              return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            }
            return '';
          }
        },
        { 
          data: 'employee_name',
          name: 'employee_name',
          render: function (data, type, row) {
            return data || '-';
          }
        },
        { 
          data: 'remarks',
          name: 'remarks',
          render: function (data, type, row) {
            if (data && data.length > 50) {
              return '<span title="' + data + '">' + data.substring(0, 50) + '...</span>';
            }
            return data || '-';
          }
        },
        { data: '' }
      ],
      columnDefs: [
        {
          // Actions
          targets: -1,
          title: 'Actions',
          orderable: false,
          searchable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-block">' +
              '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon income-edit"><i class="ti ti-pencil ti-md"></i></a>' +
              '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon delete-record"><i class="ti ti-trash ti-md"></i></a>' +
              '</div>'
            );
          }
        }
      ],
      order: [[0, 'desc']],
      dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-6 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end mt-n6 mt-md-0"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      language: {
        paginate: {
          next: '<i class="ti ti-chevron-right ti-sm"></i>',
          previous: '<i class="ti ti-chevron-left ti-sm"></i>'
        }
      },
      buttons: [
        {
          text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Record</span>',
          className: 'create-new btn btn-primary waves-effect waves-light'
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of Income #' + data['id'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                ? '<tr data-dt-row="' +
                col.rowIndex +
                '" data-dt-column="' +
                col.columnIndex +
                '">' +
                '<td>' +
                col.title +
                ':' +
                '</td> ' +
                '<td>' +
                col.data +
                '</td>' +
                '</tr>'
                : '';
            }).join('');

            return data ? $('<table class="table"/><tbody />').append(data) : false;
          }
        }
      },
      initComplete: function (settings, json) {
        $('.card-header').after('<hr class="my-0">');
      }
    });
    $('div.head-label').html('<h5 class="card-title mb-0">Incomes</h5>');
  }

  // Add/Update Record
  fv.on('core.form.valid', function () {
    var $income_head_id = $('#income_head_id').val();
    var $amount = $('#amount').val();
    var $income_date = $('#income_date').val();
    var $employee_id = $('#employee_id').val();
    var $remarks = $('#remarks').val();
    var id = $('#form-add-new-record').attr('data-id');

    if ($income_head_id != '' && $amount != '' && $income_date != '') {
      var url = window.incomeUrls.store;
      var method = 'POST';
      var message = 'Income added successfully.';
      var data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        income_head_id: $income_head_id,
        amount: $amount,
        income_date: $income_date,
        employee_id: $employee_id || null,
        remarks: $remarks || null
      };

      if (id) {
        url = window.incomeUrls.update + '/' + id;
        method = 'PUT';
        message = 'Income updated successfully.';
      }

      // Get submit button
      var $submitBtn = $('#form-add-new-record button[type="submit"]');
      
      // Use AjaxUtils with spinner
      AjaxUtils.request({
        url: url,
        type: method,
        data: data,
        showSpinner: true,
        spinnerElement: $submitBtn,
        spinnerText: id ? 'Updating...' : 'Saving...',
        showTableSpinner: false, // Don't show table spinner for form submissions
        onSuccess: function (response) {
          if (dt_basic && typeof dt_basic.ajax !== 'undefined' && typeof dt_basic.ajax.reload === 'function') {
            dt_basic.ajax.reload(null, false); // Reload without resetting pagination
          }
          if (offCanvasEl) {
            offCanvasEl.hide();
          }
          $('#form-add-new-record').removeAttr('data-id');
          // Clear form fields
          $('#income_head_id').val('').trigger('change');
          $('#amount').val('');
          $('#income_date').val('');
          $('#employee_id').val('').trigger('change');
          $('#remarks').val('');
          // Clear validation errors
          $('.form-control, .form-select, textarea').removeClass('is-invalid');
          $('.invalid-feedback').remove();
          
          if (typeof toastr !== 'undefined') {
            toastr.success(message);
          }
        },
        onError: function(xhr, errorMessage) {
          // Error already handled by AjaxUtils.handleError
        }
      });
    }
  });


  // Delete Record
  $('.datatables-basic tbody').on('click', '.delete-record', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    var $deleteBtn = $(this);
    
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
        // Show spinner on delete button
        SpinnerUtils.show($deleteBtn, 'Deleting...');
        
        AjaxUtils.request({
          url: window.incomeUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          showSpinner: false, // We're handling spinner manually
          showTableSpinner: false, // Don't show table spinner for delete
          onSuccess: function (response) {
            if (dt_basic && typeof dt_basic.row === 'function') {
              dt_basic.row($deleteBtn.closest('tr')).remove().draw();
            }
            if (typeof toastr !== 'undefined') {
              toastr.success('Income deleted successfully.');
            }
            // Hide spinner on success
            SpinnerUtils.hide($deleteBtn);
          },
          onError: function(xhr, errorMessage) {
            // Error already handled by AjaxUtils.handleError
            // Hide spinner on error
            SpinnerUtils.hide($deleteBtn);
          }
        });
      }
    });
  });

  // Edit Record
  $('.datatables-basic tbody').on('click', '.income-edit', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    var $editBtn = $(this);
    
    // Show spinner on edit button
    SpinnerUtils.show($editBtn, 'Loading...');
    
    // Small delay to show spinner, then load data
    setTimeout(function() {
      offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
      
      // Set form values
      $('#income_head_id').val(data.income_head_id).trigger('change');
      $('#amount').val(data.amount);
      $('#income_date').val(data.income_date);
      $('#employee_id').val(data.employee_id || '').trigger('change');
      $('#remarks').val(data.remarks || '');
      $('#form-add-new-record').attr('data-id', data.id);
      
      // Clear validation errors
      $('.form-control, .form-select').removeClass('is-invalid');
      $('.invalid-feedback').remove();
      
      // Update modal title
      document.querySelector('#exampleModalLabel').textContent = 'Edit Income';
      
      // Reload dropdowns to ensure options are available
      loadIncomeHeads();
      loadEmployees();
      
      // Set values again after dropdowns are loaded
      setTimeout(function() {
        $('#income_head_id').val(data.income_head_id).trigger('change');
        $('#employee_id').val(data.employee_id || '').trigger('change');
      }, 300);
      
      offCanvasEl.show();
      
      // Hide spinner
      SpinnerUtils.hide($editBtn);
    }, 100);
  });
});

