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
          $('#form-add-new-record')[0].reset();
          $('#form-add-new-record').removeAttr('data-id');
          // Open offCanvas with form
          offCanvasEl.show();
        });
      }
    }, 200);

    // Form validation for Add new record
    fv = FormValidation.formValidation(formAddNewRecord, {
      fields: {
        supplier_name: {
          validators: {
            notEmpty: {
              message: 'The supplier name is required'
            }
          }
        },
        status: {
          validators: {
            notEmpty: {
              message: 'The status is required'
            }
          }
        },
        email: {
          validators: {
            emailAddress: {
              message: 'The value is not a valid email address'
            }
          }
        },
        contact_person_email: {
          validators: {
            emailAddress: {
              message: 'The value is not a valid email address'
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
          rowSelector: '.col-sm-12, .col-sm-6'
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
        url: window.supplierUrls.getData,
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
        { data: 'supplier_name' },
        { data: 'contact_person_name' },
        { data: 'mobile' },
        { data: 'email' },
        {
          data: 'status',
          render: function (data, type, row) {
            var statusClass = '';
            var statusText = '';
            switch (data) {
              case 'active':
                statusClass = 'bg-label-success';
                statusText = 'Active';
                break;
              case 'inactive':
                statusClass = 'bg-label-secondary';
                statusText = 'Inactive';
                break;
              case 'suspended':
                statusClass = 'bg-label-danger';
                statusText = 'Suspended';
                break;
              default:
                statusClass = 'bg-label-secondary';
                statusText = data;
            }
            return '<span class="badge ' + statusClass + '">' + statusText + '</span>';
          }
        },
        {
          data: 'joining_date',
          render: function (data, type, row) {
            return data ? new Date(data).toLocaleDateString() : '-';
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
          text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Supplier</span>',
          className: 'create-new btn btn-primary waves-effect waves-light'
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['supplier_name'];
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
    $('div.head-label').html('<h5 class="card-title mb-0">Suppliers</h5>');
  }

  // Add/Update Record
  fv.on('core.form.valid', function () {
    var formData = new FormData($('#form-add-new-record')[0]);
    var id = $('#form-add-new-record').attr('data-id');

    var url = window.supplierUrls.store;
    var method = 'POST';
    var message = 'Supplier added successfully.';

    if (id) {
      url = window.supplierUrls.update + '/' + id;
      method = 'PUT';
      message = 'Supplier updated successfully.';
    }

    // Get submit button
    var $submitBtn = $('#form-add-new-record button[type="submit"]');
    // Disable & show spinner
    $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.ajax({
      url: url,
      type: method,
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        dt_basic.ajax.reload();
        offCanvasEl.hide();
        $('#form-add-new-record').removeAttr('data-id');
        $('#form-add-new-record')[0].reset(); // clear form
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
  });

  // Delete Record
  $('.datatables-basic tbody').on('click', '.delete-record', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
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
          url: window.supplierUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            row.remove().draw();
            toastr.success('Supplier deleted successfully.');
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
          }
        });
      }
    });
  });

  // Edit Record
  $('.datatables-basic tbody').on('click', '.item-edit', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
    
    // Populate form fields
    $('#supplier_name').val(data.supplier_name);
    $('#address').val(data.address);
    $('#mobile').val(data.mobile);
    $('#email').val(data.email);
    $('#contact_person_name').val(data.contact_person_name);
    $('#contact_person_mobile').val(data.contact_person_mobile);
    $('#contact_person_email').val(data.contact_person_email);
    $('#working_experience').val(data.working_experience);
    $('#joining_date').val(data.joining_date);
    $('#trade_license_number').val(data.trade_license_number);
    $('#status').val(data.status);
    
    $('#form-add-new-record').attr('data-id', data.id);
    offCanvasEl.show();
  });
});
