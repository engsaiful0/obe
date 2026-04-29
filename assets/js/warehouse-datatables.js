/**
 * DataTables for Warehouse
 */

'use strict';

let fv, offCanvasEl;
document.addEventListener('DOMContentLoaded', function () {
  (function () {
    const formAddNewRecord = document.getElementById('form-add-new-record');

    setTimeout(() => {
      const newRecord = document.querySelector('.create-new'),
        offCanvasElement = document.querySelector('#add-new-record');

      if (newRecord) {
        newRecord.addEventListener('click', function () {
          offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
          offCanvasElement.querySelector('.dt-warehouse-name').value = '';
          offCanvasElement.querySelector('.dt-warehouse-number').value = '';
          offCanvasElement.querySelector('.dt-address').value = '';
          $('#form-add-new-record').removeAttr('data-id');
          offCanvasEl.show();
        });
      }
    }, 200);

    fv = FormValidation.formValidation(formAddNewRecord, {
      fields: {
        warehouse_name: {
          validators: {
            notEmpty: { message: 'The warehouse name is required' }
          }
        },
        warehouse_number: {
          validators: {
            notEmpty: { message: 'The warehouse number is required' }
          }
        },
        address: {
          validators: {
            stringLength: { max: 500, message: 'Max 500 characters allowed' }
          }
        }
      },
      plugins: {
        trigger: new FormValidation.plugins.Trigger(),
        bootstrap5: new FormValidation.plugins.Bootstrap5({ eleValidClass: '', rowSelector: '.col-sm-12' }),
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

$(function () {
  var dt_basic_table = $('.datatables-basic'), dt_basic;

  if (dt_basic_table.length) {
    dt_basic = dt_basic_table.DataTable({
      ajax: { url: window.warehouseUrls.getData, type: 'GET', dataSrc: 'data' },
      columns: [
        { data: 'id', name: 'id', render: function (data, type, row, meta) { return meta.row + 1; } },
        { data: 'warehouse_name' },
        { data: 'warehouse_number' },
        { data: 'address' },
        { data: '' }
      ],
      columnDefs: [
        { targets: -1, title: 'Actions', orderable: false, searchable: false, render: function () {
            return '<div class="d-inline-block">'
              + '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon item-edit"><i class="ti ti-pencil ti-md"></i></a>'
              + '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon delete-record"><i class="ti ti-trash ti-md"></i></a>'
              + '</div>';
          }
        }
      ],
      order: [[0, 'desc']],
      dom: '<"card-header flex-column flex-md-row"<"head-label text-center"><"dt-action-buttons text-end pt-6 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end mt-n6 mt-md-0"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
      displayLength: 7,
      lengthMenu: [7, 10, 25, 50, 75, 100],
      language: { paginate: { next: '<i class="ti ti-chevron-right ti-sm"></i>', previous: '<i class="ti ti-chevron-left ti-sm"></i>' } },
      buttons: [ { text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Record</span>', className: 'create-new btn btn-primary waves-effect waves-light' } ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({ header: function (row) { var data = row.data(); return 'Details of ' + data['warehouse_name']; } }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            var data = $.map(columns, function (col) {
              return col.title !== '' ? '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '"><td>' + col.title + ':' + '</td> <td>' + col.data + '</td></tr>' : '';
            }).join('');
            return data ? $('<table class="table"/><tbody />').append(data) : false;
          }
        }
      },
      initComplete: function () { $('.card-header').after('<hr class="my-0">'); }
    });
    $('div.head-label').html('<h5 class="card-title mb-0">Warehouses</h5>');
  }

  fv.on('core.form.valid', function () {
    var name = $('.add-new-record .dt-warehouse-name').val();
    var number = $('.add-new-record .dt-warehouse-number').val();
    var address = $('.add-new-record .dt-address').val();
    var id = $('#form-add-new-record').attr('data-id');

    if (name && number) {
      var url = window.warehouseUrls.store;
      var method = 'POST';
      var message = 'Warehouse added successfully.';
      var data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        warehouse_name: name,
        warehouse_number: number,
        address: address
      };

      if (id) {
        url = window.warehouseUrls.update + '/' + id;
        method = 'PUT';
        message = 'Warehouse updated successfully.';
      }

      var $submitBtn = $('#form-add-new-record button[type="submit"]');
      $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

      $.ajax({
        url: url,
        type: method,
        data: data,
        success: function () {
          dt_basic.ajax.reload();
          offCanvasEl.hide();
          $('#form-add-new-record').removeAttr('data-id');
          $('.add-new-record .dt-warehouse-name').val('');
          $('.add-new-record .dt-warehouse-number').val('');
          $('.add-new-record .dt-address').val('');
          toastr.success(message);
          $submitBtn.prop('disabled', false).html('Save');
        },
        error: function (error) {
          if (error.responseJSON && error.responseJSON.errors) {
            var errors = error.responseJSON.errors; var errorMessages = '';
            for (var key in errors) { if (errors.hasOwnProperty(key)) { errorMessages += errors[key][0] + '<br>'; } }
            toastr.error(errorMessages);
          } else { toastr.error('An error occurred.'); }
          $submitBtn.prop('disabled', false).html('Save');
        }
      });
    }
  });

  $('.datatables-basic tbody').on('click', '.delete-record', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete it!',
      customClass: { confirmButton: 'btn btn-primary me-3', cancelButton: 'btn btn-label-secondary' },
      buttonsStyling: false
    }).then(function (result) {
      if (result.value) {
        $.ajax({
          url: window.warehouseUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: { _token: $('meta[name="csrf-token"]').attr('content') },
          success: function () { row.remove().draw(); toastr.success('Warehouse deleted successfully.'); },
          error: function (error) {
            if (error.responseJSON && error.responseJSON.errors) {
              var errors = error.responseJSON.errors; var errorMessages = '';
              for (var key in errors) { if (errors.hasOwnProperty(key)) { errorMessages += errors[key][0] + '<br>'; } }
              toastr.error(errorMessages);
            } else { toastr.error('An error occurred.'); }
          }
        });
      }
    });
  });

  $('.datatables-basic tbody').on('click', '.item-edit', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
    document.querySelector('.dt-warehouse-name').value = data.warehouse_name || '';
    document.querySelector('.dt-warehouse-number').value = data.warehouse_number || '';
    document.querySelector('.dt-address').value = data.address || '';
    $('#form-add-new-record').attr('data-id', data.id);
    offCanvasEl.show();
  });
});


