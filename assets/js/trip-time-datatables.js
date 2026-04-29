/**
 * DataTables Basic for Trip Time Management
 */

'use strict';

let fv, offCanvasEl;
document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const formAddNewRecord = document.getElementById('form-add-new-record');

    // Check if form exists before initializing validation
    if (!formAddNewRecord) {
      console.error('Form element not found: #form-add-new-record');
      return;
    }

    setTimeout(() => {
      const newRecord = document.querySelector('.create-new'),
        offCanvasElement = document.querySelector('#add-new-record');

      // To open offCanvas, to add new record
      if (newRecord) {
        newRecord.addEventListener('click', function () {
          offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
          // Empty fields on offCanvas open
          offCanvasElement.querySelector('#time_name').value = '';
          offCanvasElement.querySelector('#time_value').value = '';
          offCanvasElement.querySelector('#time_period').value = '';
          offCanvasElement.querySelector('#description').value = '';
          $('#form-add-new-record').removeAttr('data-id');
          // Open offCanvas with form
          offCanvasEl.show();
        });
      }
    }, 200);

    // Skip FormValidation initialization to avoid conflicts
    // We'll use direct form validation instead
    console.log('Form validation skipped - using direct validation');
  })();
});

// datatable (jquery)
$(function () {
  var dt_basic_table = $('.datatables-basic'),
    dt_basic;

  // DataTable with buttons
  if (dt_basic_table.length) {
    dt_basic = dt_basic_table.DataTable({
      ajax: {
        url: window.tripTimeUrls.getData,
        type: 'GET',
        dataSrc: 'data'
      },
      columns: [
        {
          data: 'id',
          name: 'id',
          render: function (data, type, row, meta) {
            return meta.row + 1;
          }
        },
        { data: 'time_name' },
        { 
          data: 'time_value',
          render: function (data, type, row) {
            return data ? new Date('1970-01-01T' + data).toLocaleTimeString('en-US', { 
              hour: '2-digit', 
              minute: '2-digit',
              hour12: false 
            }) : '';
          }
        },
        { data: 'time_period' },
        { 
          data: 'description',
          render: function (data, type, row) {
            return data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : '-';
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
              '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon trip-time-edit"><i class="ti ti-pencil ti-md"></i></a>' +
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
              return 'Details of ' + data['time_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col, i) {
              return col.title !== ''
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
    $('div.head-label').html('<h5 class="card-title mb-0">Trip Times</h5>');
  }

  // Form validation and submission handler
  function validateAndSubmitForm() {
    // Basic validation
    var timeName = $('#time_name').val().trim();
    var timeValue = $('#time_value').val();
    var timePeriod = $('#time_period').val();
    
    // Clear previous error messages
    $('.invalid-feedback').remove();
    $('.is-invalid').removeClass('is-invalid');
    
    var hasErrors = false;
    
    if (!timeName) {
      showFieldError('#time_name', 'Trip name is required');
      hasErrors = true;
    }
    if (!timeValue) {
      showFieldError('#time_value', 'Time is required');
      hasErrors = true;
    }
    if (!timePeriod) {
      showFieldError('#time_period', 'Time period is required');
      hasErrors = true;
    }
    
    if (hasErrors) {
      return false;
    }
    
    // Proceed with form submission
    var $time_name = $('#time_name').val();
    var $time_value = $('#time_value').val();
    var $time_period = $('#time_period').val();
    var $description = $('#description').val();
    var id = $('#form-add-new-record').attr('data-id');

    var url = window.tripTimeUrls.store;
    var method = 'POST';
    var message = 'Trip time added successfully.';
    var data = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      time_name: $time_name,
      time_value: $time_value,
      time_period: $time_period,
      description: $description
    };

    if (id) {
      url = window.tripTimeUrls.update + '/' + id;
      method = 'PUT';
      message = 'Trip time updated successfully.';
    }

    // Get submit button
    var $submitBtn = $('#form-add-new-record button[type="submit"]');
    // Disable & show spinner
    $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.ajax({
      url: url,
      type: method,
      data: data,
      success: function (response) {
        dt_basic.ajax.reload();
        offCanvasEl.hide();
        $('#form-add-new-record').removeAttr('data-id');
        $('#time_name').val(''); // clear input
        $('#time_value').val(''); // clear time input
        $('#time_period').val(''); // clear period select
        $('#description').val(''); // clear textarea
        toastr.success(message);

        // Restore button
        $submitBtn.prop('disabled', false).html('Submit');
      },
      error: function (error) {
        if (error.responseJSON && error.responseJSON.errors) {
          var errors = error.responseJSON.errors;
          var errorMessages = '';
          for (var key in errors) {
            if (errors.hasOwnProperty(key)) {
              errorMessages += errors[key][0] + '<br>';
            }
          }
          toastr.error(errorMessages);
        } else {
          toastr.error('An error occurred.');
        }

        // Restore button on error
        $submitBtn.prop('disabled', false).html('Submit');
      }
    });
    
    return true;
  }
  
  // Helper function to show field errors
  function showFieldError(fieldSelector, message) {
    var $field = $(fieldSelector);
    $field.addClass('is-invalid');
    $field.after('<div class="invalid-feedback">' + message + '</div>');
  }

  // Form submission handler
  $('#form-add-new-record').on('submit', function(e) {
    e.preventDefault();
    validateAndSubmitForm();
  });
  
  // Also handle submit button click
  $('#form-add-new-record button[type="submit"]').on('click', function(e) {
    e.preventDefault();
    validateAndSubmitForm();
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
        $deleteBtn.html('<i class="fa fa-spinner fa-spin"></i>');
        $deleteBtn.prop('disabled', true);
        
        $.ajax({
          url: window.tripTimeUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            row.remove().draw();
            toastr.success('Trip time deleted successfully.');
          },
          error: function (error) {
            if (error.responseJSON && error.responseJSON.errors) {
              var errors = error.responseJSON.errors;
              var errorMessages = '';
              for (var key in errors) {
                if (errors.hasOwnProperty(key)) {
                  errorMessages += errors[key][0] + '<br>';
                }
              }
              toastr.error(errorMessages);
            } else {
              toastr.error('An error occurred.');
            }
          },
          complete: function() {
            // Restore delete button
            $deleteBtn.html('<i class="ti ti-trash ti-md"></i>');
            $deleteBtn.prop('disabled', false);
          }
        });
      }
    });
  });

  // Edit Record
  $('.datatables-basic tbody').on('click', '.trip-time-edit', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
    document.querySelector('#time_name').value = data.time_name;
    document.querySelector('#time_value').value = data.time_value;
    document.querySelector('#time_period').value = data.time_period;
    document.querySelector('#description').value = data.description || '';
    $('#form-add-new-record').attr('data-id', data.id);
    offCanvasEl.show();
  });
});