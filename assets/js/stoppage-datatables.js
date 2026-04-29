/**
 * DataTables Basic
 */

'use strict';

let fv, offCanvasEl;
document.addEventListener('DOMContentLoaded', function (e) {
  (function () {
    const formAddNewRecord = document.getElementById('form-add-new-record');

    setTimeout(() => {
      const newRecord = document.querySelector('.create-new'),
        offCanvasElement = document.querySelector('#add-new-record');

      // To open offCanvas, to add new record
      if (newRecord) {
        newRecord.addEventListener('click', function () {
          offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
          // Empty fields on offCanvas open
          offCanvasElement.querySelector('.dt-full-name').value = '';
          offCanvasElement.querySelector('.dt-distance').value = '';
          offCanvasElement.querySelector('.dt-status').value = 'active';
          $('#form-add-new-record').removeAttr('data-id');
          // Update modal title
          document.querySelector('#exampleModalLabel').textContent = 'New Stoppage';
          // Open offCanvas with form
          offCanvasEl.show();
        });
      }
    }, 200);

    // Form validation for Add new record
    fv = FormValidation.formValidation(formAddNewRecord, {
      fields: {
        stoppage_name: {
          validators: {
            notEmpty: {
              message: 'The stoppage name is required'
            }
          }
        },
        distance: {
          validators: {
            numeric: {
              message: 'The distance must be a number'
            },
            greaterThan: {
              min: 0,
              message: 'The distance must be greater than or equal to 0'
            }
          }
        },
        status: {
          validators: {
            notEmpty: {
              message: 'The status is required'
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
          rowSelector: '.col-sm-12'
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

// datatable (jquery)
$(function () {
  var dt_basic_table = $('.datatables-basic'),
    dt_basic;

  // DataTable with buttons
  // --------------------------------------------------------------------

  if (dt_basic_table.length) {
    dt_basic = dt_basic_table.DataTable({
      ajax: {
        url: window.stoppageUrls.getData,
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
        { data: 'stoppage_name' },
        {
          data: 'distance',
          name: 'distance',
          render: function (data, type, row) {
            return data !== null && data !== '' ? parseFloat(data).toFixed(2) + ' KM' : 'N/A';
          }
        },
        {
          data: 'status',
          name: 'status',
          render: function (data, type, row) {
            if (data === 'active') {
              return '<span class="badge bg-success">Active</span>';
            } else if (data === 'inactive') {
              return '<span class="badge bg-danger">Inactive</span>';
            }
            return '<span class="badge bg-secondary">' + (data || 'N/A') + '</span>';
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
              '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon item-edit"><i class="ti ti-pencil ti-md"></i></a>' +
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
              return 'Details of ' + data['stoppage_name'];
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
    $('div.head-label').html('<h5 class="card-title mb-0">Stoppages</h5>');
  }

  // Add/Update Record
  fv.on('core.form.valid', function () {
    var $new_name = $('.add-new-record .dt-full-name').val();
    var id = $('#form-add-new-record').attr('data-id');

    if ($new_name != '') {
      var url = window.stoppageUrls.store;
      var method = 'POST';
      var message = 'Stoppage added successfully.';
      var data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        stoppage_name: $new_name,
        distance: $('.add-new-record .dt-distance').val() || null,
        status: $('.add-new-record .dt-status').val()
      };

      if (id) {
        url = window.stoppageUrls.update + '/' + id;
        method = 'PUT';
        message = 'Stoppage updated successfully.';
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
          $('.add-new-record .dt-full-name').val(''); // clear input
          $('.add-new-record .dt-distance').val(''); // clear distance
          $('.add-new-record .dt-status').val('active'); // reset status
          toastr.success(message);

          // Restore button
          $submitBtn.prop('disabled', false).html('Save');
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
          $submitBtn.prop('disabled', false).html('Save');
        }
      });
    }
  });


  // Delete Record
  $('.datatables-basic tbody').on('click', '.delete-record', function (e) {
    e.preventDefault();
    e.stopPropagation();
    
    var $deleteBtn = $(this);
    var $row = $deleteBtn.closest('tr');
    var row = dt_basic.row($row);
    var data = row.data();
    
    // Store original button HTML
    var originalHtml = $deleteBtn.html();
    
    // Prevent multiple clicks
    if ($deleteBtn.prop('disabled') || $deleteBtn.hasClass('deleting')) {
      return false;
    }
    
    // Show confirmation dialog
    Swal.fire({
      title: 'Delete Stoppage?',
      text: "Are you sure you want to delete '" + (data.stoppage_name || 'this stoppage') + "'? This action cannot be undone!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '<i class="ti ti-trash me-1"></i>Yes, Delete It!',
      cancelButtonText: '<i class="ti ti-x me-1"></i>Cancel',
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      customClass: {
        confirmButton: 'btn btn-danger me-3',
        cancelButton: 'btn btn-secondary'
      },
      buttonsStyling: false,
      reverseButtons: true
    }).then(function (result) {
      if (result.isConfirmed) {
        // Mark button as deleting to prevent multiple clicks
        $deleteBtn.addClass('deleting').prop('disabled', true);
        
        // Show spinner on delete button
        $deleteBtn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Deleting...');
        
        // Highlight row
        $row.addClass('table-danger');
        
        // Make AJAX delete request
        $.ajax({
          url: window.stoppageUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          dataType: 'json',
          timeout: 30000, // 30 second timeout
          success: function (response) {
            // Remove row from DataTable
            row.remove().draw();
            
            // Show success toast notification
            toastr.success(
              response.message || 'Stoppage deleted successfully.',
              'Deleted Successfully',
              {
                timeOut: 4000,
                progressBar: true,
                closeButton: true
              }
            );
          },
          error: function (xhr, status, error) {
            // Remove loading state from row
            $row.removeClass('table-danger');
            
            // Restore button state immediately
            $deleteBtn.removeClass('deleting')
              .prop('disabled', false)
              .html(originalHtml);
            
            // Determine error message
            var errorMessage = 'Failed to delete stoppage. Please try again.';
            
            if (xhr.responseJSON) {
              if (xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
              } else if (xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              }
            } else if (xhr.status === 404) {
              errorMessage = 'Stoppage not found.';
            } else if (xhr.status === 403) {
              errorMessage = 'You do not have permission to delete this stoppage.';
            } else if (xhr.status === 500) {
              errorMessage = 'Server error occurred. Please try again later.';
            } else if (status === 'timeout') {
              errorMessage = 'Request timed out. Please try again.';
            } else if (status === 'error' && xhr.status === 0) {
              errorMessage = 'Network error. Please check your connection.';
            }
            
            // Show error toast
            toastr.error(errorMessage, 'Delete Failed', {
              timeOut: 5000,
              progressBar: true,
              closeButton: true
            });
          },
          complete: function() {
            // Always remove deleting class and enable button if row still exists
            if ($row.length && $row.is(':visible')) {
              $deleteBtn.removeClass('deleting').prop('disabled', false);
              // Only restore HTML if button still exists and is visible
              if ($deleteBtn.length && $deleteBtn.is(':visible') && !$deleteBtn.hasClass('deleting')) {
                $deleteBtn.html(originalHtml);
              }
            }
          }
        });
      }
    });
    
    return false;
  });

  // Edit Record
  $('.datatables-basic tbody').on('click', '.item-edit', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
    document.querySelector('.dt-full-name').value = data.stoppage_name;
    document.querySelector('.dt-distance').value = data.distance || '';
    document.querySelector('.dt-status').value = data.status || 'active';
    $('#form-add-new-record').attr('data-id', data.id);
    // Update modal title
    document.querySelector('#exampleModalLabel').textContent = 'Edit Stoppage';
    offCanvasEl.show();
  });
});
