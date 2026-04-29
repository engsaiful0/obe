/**
 * DataTables Basic for Reward Types
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
          offCanvasElement.querySelector('#description').value = '';
          $('#form-add-new-record').removeAttr('data-id');
          // Open offCanvas with form
          offCanvasEl.show();
        });
      }
    }, 200);

    // Form validation for Add new record
    fv = FormValidation.formValidation(formAddNewRecord, {
      fields: {
        name: {
          validators: {
            notEmpty: {
              message: 'The reward type name is required'
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
        url: window.rewardTypeUrls.getData,
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
        { data: 'name' },
        { 
          data: 'description',
          render: function (data, type, row) {
            return data ? (data.length > 50 ? data.substring(0, 50) + '...' : data) : '';
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
          render: function (data, type, row) {
            return (
              '<div class="d-flex align-items-center">' +
              '<a href="javascript:;" class="dropdown-toggle hide-arrow text-primary" data-bs-toggle="dropdown">' +
              '<i class="ti ti-dots-vertical ti-sm mx-1"></i>' +
              '</a>' +
              '<div class="dropdown-menu dropdown-menu-end m-0">' +
              '<a href="javascript:;" class="dropdown-item edit-record" data-id="' +
              row.id +
              '" data-name="' +
              row.name +
              '" data-description="' +
              (row.description || '') +
              '">Edit</a>' +
              '<a href="javascript:;" class="dropdown-item delete-record" data-id="' +
              row.id +
              '">Delete</a>' +
              '</div>' +
              '</div>'
            );
          }
        }
      ],
      order: [[0, 'desc']],
      dom: '<"card-header d-flex border-top rounded-0 flex-wrap py-3"<"me-5 ms-n2 pe-5"f><"d-flex justify-content-end justify-content-md-between flex-wrap"<"text-body mb-3 mb-md-0"lB>>>t<"row mx-2"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      buttons: [
        {
          extend: 'collection',
          className: 'btn btn-label-primary dropdown-toggle me-2',
          text: '<i class="ti ti-file-export me-1 ti-xs"></i>Export',
          buttons: [
            {
              extend: 'print',
              text: '<i class="ti ti-printer me-1" ></i>Print',
              className: 'dropdown-item',
              exportOptions: { columns: [1, 2] }
            },
            {
              extend: 'csv',
              text: '<i class="ti ti-file-text me-1" ></i>Csv',
              className: 'dropdown-item',
              exportOptions: { columns: [1, 2] }
            },
            {
              extend: 'excel',
              text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
              className: 'dropdown-item',
              exportOptions: { columns: [1, 2] }
            },
            {
              extend: 'pdf',
              text: '<i class="ti ti-file me-1"></i>Pdf',
              className: 'dropdown-item',
              exportOptions: { columns: [1, 2] }
            },
            {
              extend: 'copy',
              text: '<i class="ti ti-copy me-1" ></i>Copy',
              className: 'dropdown-item',
              exportOptions: { columns: [1, 2] }
            }
          ]
        },
        {
          text: '<i class="ti ti-plus me-0 me-sm-1 ti-xs"></i><span class="d-none d-sm-inline-block">Add New Record</span>',
          className: 'create-new btn btn-primary',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#add-new-record'
          }
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Details of ' + data.name;
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = $.map(columns, function (col, i) {
              return col.columnIndex !== 3
                ? '<tr data-dt-row="' +
                    col.rowIdx +
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
      }
    });
  }

  // Add/Update Record
  fv.on('core.form.valid', function () {
    var $new_name = $('.add-new-record .dt-full-name').val();
    var $description = $('.add-new-record #description').val();
    var id = $('#form-add-new-record').attr('data-id');

    if ($new_name != '') {
      var url = window.rewardTypeUrls.store;
      var method = 'POST';
      var message = 'Reward type added successfully.';
      var data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        name: $new_name,
        description: $description,
      };

      if (id) {
        url = window.rewardTypeUrls.update + '/' + id;
        method = 'PUT';
        message = 'Reward type updated successfully.';
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
          $('.add-new-record #description').val(''); // clear textarea
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
    }
  });

  // Edit record
  $('.datatables-basic tbody').on('click', '.edit-record', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    
    // Set form data
    $('#form-add-new-record').attr('data-id', data.id);
    $('#name').val(data.name);
    $('#description').val(data.description);

    // Open offCanvas
    offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
    offCanvasEl.show();
  });

  // Delete record with confirmation and spinner
  $('.datatables-basic tbody').on('click', '.delete-record', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
      if (result.isConfirmed) {
        // Show loading state
        Swal.fire({
          title: 'Deleting...',
          text: 'Please wait while we delete the reward type.',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });

        // AJAX delete request
        $.ajax({
          url: window.rewardTypeUrls.destroy + '/' + data.id,
          type: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            Swal.fire({
              title: 'Deleted!',
              text: 'Reward type has been deleted successfully.',
              icon: 'success',
              timer: 1500,
              showConfirmButton: false
            });
            dt_basic.ajax.reload();
          },
          error: function (xhr) {
            Swal.fire({
              title: 'Error!',
              text: 'An error occurred while deleting the reward type.',
              icon: 'error'
            });
          }
        });
      }
    });
  });
});