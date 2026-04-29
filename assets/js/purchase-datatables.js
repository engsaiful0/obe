/**
 * DataTables Basic
 */

'use strict';

let fv, offCanvasEl, itemCounter = 0;
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
          resetPurchaseItems();
          loadSuppliers();
          loadItems();
          // Open offCanvas with form
          offCanvasEl.show();
        });
      }
    }, 200);

    // Form validation for Add new record
    fv = FormValidation.formValidation(formAddNewRecord, {
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
        payment_method: {
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

// datatable (jquery)
$(function () {
  var dt_basic_table = $('.datatables-basic'),
    dt_basic;

  // DataTable with buttons
  // --------------------------------------------------------------------

  if (dt_basic_table.length) {
    dt_basic = dt_basic_table.DataTable({
      ajax: {
        url: window.purchaseUrls.getData,
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
        { data: 'purchase_number' },
        {
          data: 'supplier',
          render: function (data, type, row) {
            return data ? data.supplier_name : '-';
          }
        },
        {
          data: 'date',
          render: function (data, type, row) {
            return data ? new Date(data).toLocaleDateString() : '-';
          }
        },
        {
          data: 'net_total',
          render: function (data, type, row) {
            return '$' + parseFloat(data).toFixed(2);
          }
        },
        {
          data: 'paid',
          render: function (data, type, row) {
            return '$' + parseFloat(data).toFixed(2);
          }
        },
        {
          data: 'due',
          render: function (data, type, row) {
            return '$' + parseFloat(data).toFixed(2);
          }
        },
        {
          data: 'payment_method',
          render: function (data, type, row) {
            var methodClass = '';
            var methodText = '';
            switch (data) {
              case 'cash':
                methodClass = 'bg-label-success';
                methodText = 'Cash';
                break;
              case 'bank':
                methodClass = 'bg-label-primary';
                methodText = 'Bank';
                break;
              case 'cheque':
                methodClass = 'bg-label-info';
                methodText = 'Cheque';
                break;
              case 'card':
                methodClass = 'bg-label-warning';
                methodText = 'Card';
                break;
              default:
                methodClass = 'bg-label-secondary';
                methodText = data;
            }
            return '<span class="badge ' + methodClass + '">' + methodText + '</span>';
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
          text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Purchase</span>',
          className: 'create-new btn btn-primary waves-effect waves-light'
        }
      ],
      responsive: {
        details: {
          display: $.fn.dataTable.Responsive.display.modal({
            header: function (row) {
              var data = row.data();
              return 'Details of ' + data['purchase_number'];
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
    $('div.head-label').html('<h5 class="card-title mb-0">Purchases</h5>');
  }

  // Load suppliers for dropdown
  function loadSuppliers() {
    $.ajax({
      url: window.purchaseUrls.getData.replace('get-purchase', 'get-suppliers'),
      type: 'GET',
      success: function (response) {
        var supplierSelect = $('#supplier_id');
        supplierSelect.empty().append('<option value="">Select Supplier</option>');
        response.forEach(function (supplier) {
          supplierSelect.append('<option value="' + supplier.id + '">' + supplier.supplier_name + '</option>');
        });
      }
    });
  }

  // Load items for dropdown
  function loadItems() {
    $.ajax({
      url: window.purchaseUrls.getData.replace('get-purchase', 'get-items'),
      type: 'GET',
      success: function (response) {
        var itemSelects = $('.item-select');
        itemSelects.empty().append('<option value="">Select Item</option>');
        response.forEach(function (item) {
          itemSelects.append('<option value="' + item.id + '">' + item.item_name + '</option>');
        });
      }
    });
  }

  // Reset purchase items
  function resetPurchaseItems() {
    itemCounter = 0;
    $('#purchase-items-container').html(`
      <div class="purchase-item-row row g-2 mb-3">
        <div class="col-sm-4">
          <label class="form-label">Item *</label>
          <select class="form-select item-select" name="items[0][item_id]">
            <option value="">Select Item</option>
          </select>
        </div>
        <div class="col-sm-2">
          <label class="form-label">Quantity *</label>
          <input type="number" class="form-control quantity-input" name="items[0][quantity]" 
            step="0.01" min="0.01" placeholder="0.00" />
        </div>
        <div class="col-sm-2">
          <label class="form-label">Unit Price *</label>
          <input type="number" class="form-control unit-price-input" name="items[0][unit_price]" 
            step="0.01" min="0" placeholder="0.00" />
        </div>
        <div class="col-sm-2">
          <label class="form-label">Total Price</label>
          <input type="number" class="form-control total-price-input" name="items[0][total_price]" 
            readonly placeholder="0.00" />
        </div>
        <div class="col-sm-2">
          <label class="form-label">&nbsp;</label>
          <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-item-btn">
            <i class="ti ti-trash"></i>
          </button>
        </div>
      </div>
    `);
    loadItems();
    bindItemEvents();
  }

  // Add new item row
  function addItemRow() {
    itemCounter++;
    var newRow = `
      <div class="purchase-item-row row g-2 mb-3">
        <div class="col-sm-4">
          <label class="form-label">Item *</label>
          <select class="form-select item-select" name="items[${itemCounter}][item_id]">
            <option value="">Select Item</option>
          </select>
        </div>
        <div class="col-sm-2">
          <label class="form-label">Quantity *</label>
          <input type="number" class="form-control quantity-input" name="items[${itemCounter}][quantity]" 
            step="0.01" min="0.01" placeholder="0.00" />
        </div>
        <div class="col-sm-2">
          <label class="form-label">Unit Price *</label>
          <input type="number" class="form-control unit-price-input" name="items[${itemCounter}][unit_price]" 
            step="0.01" min="0" placeholder="0.00" />
        </div>
        <div class="col-sm-2">
          <label class="form-label">Total Price</label>
          <input type="number" class="form-control total-price-input" name="items[${itemCounter}][total_price]" 
            readonly placeholder="0.00" />
        </div>
        <div class="col-sm-2">
          <label class="form-label">&nbsp;</label>
          <button type="button" class="btn btn-outline-danger btn-sm w-100 remove-item-btn">
            <i class="ti ti-trash"></i>
          </button>
        </div>
      </div>
    `;
    $('#purchase-items-container').append(newRow);
    loadItems();
    bindItemEvents();
  }

  // Bind item events
  function bindItemEvents() {
    // Remove item button
    $('.remove-item-btn').off('click').on('click', function () {
      if ($('.purchase-item-row').length > 1) {
        $(this).closest('.purchase-item-row').remove();
        calculateNetTotal();
      }
    });

    // Quantity and unit price change
    $('.quantity-input, .unit-price-input').off('input').on('input', function () {
      var row = $(this).closest('.purchase-item-row');
      var quantity = parseFloat(row.find('.quantity-input').val()) || 0;
      var unitPrice = parseFloat(row.find('.unit-price-input').val()) || 0;
      var totalPrice = quantity * unitPrice;
      row.find('.total-price-input').val(totalPrice.toFixed(2));
      calculateNetTotal();
    });
  }

  // Calculate net total
  function calculateNetTotal() {
    var netTotal = 0;
    $('.total-price-input').each(function () {
      netTotal += parseFloat($(this).val()) || 0;
    });
    $('#net_total').val(netTotal.toFixed(2));
  }

  // Add item button
  $('#add-item-btn').on('click', function () {
    addItemRow();
  });

  // Add/Update Record
  fv.on('core.form.valid', function () {
    var formData = new FormData($('#form-add-new-record')[0]);
    var id = $('#form-add-new-record').attr('data-id');

    // Validate items
    var items = [];
    $('.purchase-item-row').each(function (index) {
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

    var url = window.purchaseUrls.store;
    var method = 'POST';
    var message = 'Purchase added successfully.';

    if (id) {
      url = window.purchaseUrls.update + '/' + id;
      method = 'PUT';
      message = 'Purchase updated successfully.';
    }

    // Get submit button
    var $submitBtn = $('#form-add-new-record button[type="submit"]');
    // Disable & show spinner
    $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.ajax({
      url: url,
      type: method,
      data: {
        _token: $('meta[name="csrf-token"]').attr('content'),
        purchase_number: $('#purchase_number').val(),
        supplier_id: $('#supplier_id').val(),
        date: $('#date').val(),
        paid: $('#paid').val(),
        due: $('#due').val(),
        net_total: $('#net_total').val(),
        payment_method_id: $('#payment_method_id').val(),
        remarks: $('#remarks').val(),
        items: items
      },
      success: function (response) {
        dt_basic.ajax.reload();
        offCanvasEl.hide();
        $('#form-add-new-record').removeAttr('data-id');
        $('#form-add-new-record')[0].reset(); // clear form
        resetPurchaseItems();
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
          url: window.purchaseUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            row.remove().draw();
            toastr.success('Purchase deleted successfully.');
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
    $('#purchase_number').val(data.purchase_number);
    $('#supplier_id').val(data.supplier_id);
    $('#date').val(data.date);
    $('#paid').val(data.paid);
    $('#due').val(data.due);
    $('#net_total').val(data.net_total);
    $('#payment_method_id').val(data.payment_method_id);
    $('#remarks').val(data.remarks);
    
    // Load purchase items
    if (data.purchase_items && data.purchase_items.length > 0) {
      resetPurchaseItems();
      data.purchase_items.forEach(function (item, index) {
        if (index > 0) {
          addItemRow();
        }
        var row = $('.purchase-item-row').eq(index);
        row.find('.item-select').val(item.item_id);
        row.find('.quantity-input').val(item.quantity);
        row.find('.unit-price-input').val(item.unit_price);
        row.find('.total-price-input').val(item.total_price);
      });
    }
    
    $('#form-add-new-record').attr('data-id', data.id);
    offCanvasEl.show();
  });
});
