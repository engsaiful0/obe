/**
 * Designation CRUD with AJAX and Spinner
 */
'use strict';

let fv, offCanvasEl, dt_basic;

const SpinnerUtils = {
  show: function (element, text = 'Loading...') {
    if (typeof element === 'string') {
      element = $(element);
    }
    element.prop('disabled', true);
    element.data('original-text', element.html());
    element.html(`<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${text}`);
  },
  hide: function (element, originalText = null) {
    if (typeof element === 'string') {
      element = $(element);
    }
    element.prop('disabled', false);
    const text = originalText || element.data('original-text');
    if (text) {
      element.html(text);
    }
  }
};

const AjaxUtils = {
  request: function (options) {
    const defaults = {
      type: 'GET',
      dataType: 'json',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      beforeSend: function () {
        if (options.showSpinner && options.spinnerElement) {
          SpinnerUtils.show(options.spinnerElement, options.spinnerText);
        }
      },
      complete: function () {
        if (options.showSpinner && options.spinnerElement) {
          SpinnerUtils.hide(options.spinnerElement);
        }
      },
      success: function (response) {
        if (options.onSuccess) {
          options.onSuccess(response);
        }
      },
      error: function (xhr) {
        AjaxUtils.handleError(xhr);
        if (options.onError) {
          options.onError(xhr);
        }
      }
    };

    return $.ajax($.extend(defaults, options));
  },
  handleError: function (xhr) {
    let message = 'An error occurred. Please try again.';
    if (xhr.status === 422 && xhr.responseJSON?.errors) {
      message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
    } else if (xhr.responseJSON?.message) {
      message = xhr.responseJSON.message;
    }

    if (typeof toastr !== 'undefined') {
      toastr.error(message);
    } else {
      alert(message);
    }
  }
};

document.addEventListener('DOMContentLoaded', function () {
  const formAddNewRecord = document.getElementById('form-add-new-record');

  setTimeout(() => {
    const newRecord = document.querySelector('.create-new');
    const offCanvasElement = document.querySelector('#add-new-record');

    if (newRecord) {
      newRecord.addEventListener('click', function () {
        offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
        formAddNewRecord.reset();
        $('#form-add-new-record').removeAttr('data-id');
        document.querySelector('#exampleModalLabel').textContent = 'New Designation';
        offCanvasEl.show();
      });
    }
  }, 200);

  fv = FormValidation.formValidation(formAddNewRecord, {
    fields: {
      designation_name: {
        validators: {
          notEmpty: {
            message: 'Designation name is required'
          }
        }
      },
      designation_type: {
        validators: {
          notEmpty: {
            message: 'Designation type is required'
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
});

$(function () {
  if (typeof window.designationUrls === 'undefined') {
    return;
  }

  const dt_basic_table = $('.datatables-basic');

  if (dt_basic_table.length) {
    dt_basic = dt_basic_table.DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: window.designationUrls.getData,
        type: 'GET',
        dataSrc: 'data'
      },
      columns: [
        {
          data: 'id',
          render: function (data, type, row, meta) {
            return meta.row + 1;
          }
        },
        { data: 'designation_name' },
        { data: 'designation_type' },
        { data: null }
      ],
      columnDefs: [
        {
          targets: -1,
          title: 'Actions',
          orderable: false,
          searchable: false,
          render: function () {
            return (
              '<div class="d-inline-block">' +
              '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon designation-edit"><i class="ti ti-pencil ti-md"></i></a>' +
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
              const data = row.data();
              return 'Details of ' + data.designation_name + ' — ' + data.designation_type;
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = $.map(columns, function (col) {
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
      initComplete: function () {
        $('.card-header').after('<hr class="my-0">');
      }
    });

    $('div.head-label').html('<h5 class="card-title mb-0">Designations</h5>');
  }

  fv.on('core.form.valid', function () {
    const id = $('#form-add-new-record').attr('data-id');
    let url = window.designationUrls.store;
    let method = 'POST';
    let successMessage = 'Designation added successfully.';

    const data = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      designation_name: $('.dt-full-name').val(),
      designation_type: $('.dt-designation-type').val()
    };

    if (id) {
      url = window.designationUrls.update + '/' + id;
      method = 'PUT';
      successMessage = 'Designation updated successfully.';
    }

    const $submitBtn = $('#form-add-new-record button[type="submit"]');
    AjaxUtils.request({
      url: url,
      type: method,
      data: data,
      showSpinner: true,
      spinnerElement: $submitBtn,
      spinnerText: id ? 'Updating...' : 'Saving...',
      onSuccess: function () {
        dt_basic.ajax.reload(null, false);
        if (offCanvasEl) {
          offCanvasEl.hide();
        }
        $('#form-add-new-record').removeAttr('data-id');
        document.getElementById('form-add-new-record').reset();
        if (typeof toastr !== 'undefined') {
          toastr.success(successMessage);
        }
      }
    });
  });

  $('.datatables-basic tbody').on('click', '.designation-edit', function () {
    const row = dt_basic.row($(this).parents('tr'));
    const data = row.data();
    const $editBtn = $(this);
    SpinnerUtils.show($editBtn, 'Loading...');

    setTimeout(function () {
      offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
      $('.dt-full-name').val(data.designation_name);
      $('.dt-designation-type').val(data.designation_type);
      $('#form-add-new-record').attr('data-id', data.id);
      document.querySelector('#exampleModalLabel').textContent = 'Edit Designation';
      offCanvasEl.show();
      SpinnerUtils.hide($editBtn);
    }, 100);
  });

  $('.datatables-basic tbody').on('click', '.delete-record', function () {
    const row = dt_basic.row($(this).parents('tr'));
    const data = row.data();
    const $deleteBtn = $(this);

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
        SpinnerUtils.show($deleteBtn, 'Deleting...');
        AjaxUtils.request({
          url: window.designationUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          onSuccess: function () {
            dt_basic.row($deleteBtn.closest('tr')).remove().draw();
            SpinnerUtils.hide($deleteBtn);
            if (typeof toastr !== 'undefined') {
              toastr.success('Designation deleted successfully.');
            }
          },
          onError: function () {
            SpinnerUtils.hide($deleteBtn);
          }
        });
      }
    });
  });
});
