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
          offCanvasElement.querySelector('.dt-opening-stock').value = '';
          offCanvasElement.querySelector('#related_to').value = '';
          $('#form-add-new-record').removeAttr('data-id');
          // Open offCanvas with form
          offCanvasEl.show();
        });
      }
    }, 200);

    // Form validation for Add new record
    fv = FormValidation.formValidation(formAddNewRecord, {
      fields: {
        item_name: {
          validators: {
            notEmpty: {
              message: 'The item name is required'
            }
          }
        },
        opening_stock: {
          validators: {
            numeric: {
              message: 'The opening stock must be a number'
            },
            between: {
              min: 0,
              max: 999999.99,
              message: 'The opening stock must be between 0 and 999999.99'
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
        url: window.itemUrls.getData,
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
        { data: 'item_name' },
        { 
          data: 'opening_stock',
          render: function (data, type, row) {
            return parseFloat(data || 0).toFixed(2);
          }
        },
        { data: 'related_to' },
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
              return 'Details of ' + data['item_name'];
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
    $('div.head-label').html('<h5 class="card-title mb-0">Items</h5>');
  }

  // Add/Update Record
  fv.on('core.form.valid', function () {
    var $new_name = $('.add-new-record .dt-full-name').val();
    var $opening_stock = $('.add-new-record .dt-opening-stock').val();
    var id = $('#form-add-new-record').attr('data-id');

    if ($new_name != '') {
      var url = window.itemUrls.store;
      var method = 'POST';
      var message = 'Item added successfully.';
      var data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        item_name: $new_name,
        opening_stock: $opening_stock || 0,
        related_to: $('#related_to').val()
      };

      if (id) {
        url = window.itemUrls.update + '/' + id;
        method = 'PUT';
        message = 'Item updated successfully.';
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
          $('.add-new-record .dt-opening-stock').val(''); // clear input
          $('#related_to').val(''); // clear related_to
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
          url: window.itemUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            row.remove().draw();
            toastr.success('Item deleted successfully.');
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
    document.querySelector('.dt-full-name').value = data.item_name;
    document.querySelector('.dt-opening-stock').value = data.opening_stock || 0;
    document.querySelector('#related_to').value = data.related_to || '';
    $('#form-add-new-record').attr('data-id', data.id);
    offCanvasEl.show();
  });
});
