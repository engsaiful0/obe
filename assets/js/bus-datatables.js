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
        bus_name: {
          validators: {
            notEmpty: {
              message: 'The bus name is required'
            }
          }
        },
        type: {
          validators: {
            notEmpty: {
              message: 'The bus type is required'
            }
          }
        },
        bus_number: {
          validators: {
            notEmpty: {
              message: 'The bus number is required'
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

// Load Bus Sub-Types
function loadBusSubTypes() {
  $.ajax({
    url: AppUtils.buildUrl('app/settings/get-bus-sub-type'),
    method: 'GET',
    success: function (response) {
      var options = '<option value="">Select Bus Sub-Type</option>';
      response.data.forEach(function (subType) {
        options += '<option value="' + subType.id + '" data-name="' + subType.sub_type_name + '">' + subType.sub_type_name + '</option>';
      });
      $('#bus_sub_type_id').html(options);
    },
    error: function (error) {
      console.error('Error loading bus sub-types:', error);
      toastr.error('Failed to load bus sub-types');
    }
  });
}

// Handle Bus Sub-Type Change
$(document).on('change', '#bus_sub_type_id', function () {
  var selectedText = $(this).find('option:selected').data('name');
  
  // Hide all price fields first
  $('#fixed_price_field').hide();
  $('#rate_per_km_field').hide();
  $('#fixed_price').val('');
  $('#rate_per_km').val('');
  
  // Show appropriate field based on selection
  if (selectedText) {
    if (selectedText.toLowerCase().includes('hired')) {
      $('#fixed_price_field').show();
    } else if (selectedText.toLowerCase().includes('brtc') || selectedText.toLowerCase().includes('kilometer')) {
      $('#rate_per_km_field').show();
    }
    // For "Own" - no price fields shown
  }
});

// Load bus sub-types when page loads
$(document).ready(function () {
  loadBusSubTypes();
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
        url: window.busUrls.getData,
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
        { data: 'bus_name' },
        {
          data: 'type',
          render: function (data, type, row) {
            var typeClass = '';
            var typeText = '';
            switch (data) {
              case 'bus':
                typeClass = 'bg-label-primary';
                typeText = 'Bus';
                break;
              case 'minibus':
                typeClass = 'bg-label-info';
                typeText = 'Minibus';
                break;
              case 'car':
                typeClass = 'bg-label-success';
                typeText = 'Car';
                break;
              case 'truck':
                typeClass = 'bg-label-warning';
                typeText = 'Truck';
                break;
              default:
                typeClass = 'bg-label-secondary';
                typeText = data;
            }
            return '<span class="badge ' + typeClass + '">' + typeText + '</span>';
          }
        },
        { data: 'bus_number' },
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
          text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Bus</span>',
          className: 'create-new btn btn-primary waves-effect waves-light'
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['bus_name'];
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
    $('div.head-label').html('<h5 class="card-title mb-0">Buses</h5>');
  }

  // Add/Update Record
  fv.on('core.form.valid', function () {
    var $bus_name = $('.add-new-record .dt-full-name').val();
    var $type = $('.add-new-record #type').val();
    var $bus_number = $('.add-new-record #bus_number').val();
    var $bus_sub_type_id = $('.add-new-record #bus_sub_type_id').val();
    var $fixed_price = $('.add-new-record #fixed_price').val();
    var $rate_per_km = $('.add-new-record #rate_per_km').val();
    var id = $('#form-add-new-record').attr('data-id');

    if ($bus_name != '' && $type != '' && $bus_number != '') {
      var url = window.busUrls.store;
      var method = 'POST';
      var message = 'Bus added successfully.';
      var data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        bus_name: $bus_name,
        type: $type,
        bus_number: $bus_number,
        bus_sub_type_id: $bus_sub_type_id,
        fixed_price: $fixed_price,
        rate_per_km: $rate_per_km
      };

      if (id) {
        url = window.busUrls.update + '/' + id;
        method = 'PUT';
        message = 'Bus updated successfully.';
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
          url: window.busUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            row.remove().draw();
            toastr.success('Bus deleted successfully.');
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
    $('#bus_name').val(data.bus_name);
    $('#type').val(data.type);
    $('#bus_number').val(data.bus_number);
    $('#bus_sub_type_id').val(data.bus_sub_type_id);
    $('#fixed_price').val(data.fixed_price);
    $('#rate_per_km').val(data.rate_per_km);
    
    // Trigger change to show appropriate price field
    $('#bus_sub_type_id').trigger('change');
    
    $('#form-add-new-record').attr('data-id', data.id);
    offCanvasEl.show();
  });
});
