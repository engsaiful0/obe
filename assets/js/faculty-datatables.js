/**
 * Faculty CRUD with AJAX and Spinner
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
        document.querySelector('#exampleModalLabel').textContent = 'New Faculty';
        offCanvasEl.show();
      });
    }
  }, 200);

  fv = FormValidation.formValidation(formAddNewRecord, {
    fields: {
      faculty_name: {
        validators: {
          notEmpty: {
            message: 'Faculty name is required'
          }
        }
      },
      faculty_code: {
        validators: {
          notEmpty: {
            message: 'Faculty code is required'
          }
        }
      },
      dean_name: {
        validators: {
          notEmpty: {
            message: 'Dean name is required'
          }
        }
      },
      email: {
        validators: {
          notEmpty: {
            message: 'Email is required'
          },
          emailAddress: {
            message: 'Please enter a valid email'
          }
        }
      },
      phone: {
        validators: {
          notEmpty: {
            message: 'Phone is required'
          }
        }
      },
      status: {
        validators: {
          notEmpty: {
            message: 'Status is required'
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
    }
  });
});

$(function () {
  if (typeof window.facultyUrls === 'undefined') {
    return;
  }

  const dt_basic_table = $('.datatables-basic');

  if (dt_basic_table.length) {
    dt_basic = dt_basic_table.DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: window.facultyUrls.getData,
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
        { data: 'faculty_name' },
        { data: 'faculty_code' },
        { data: 'dean_name' },
        { data: 'email' },
        { data: 'phone' },
        {
          data: 'status',
          render: function (data) {
            const badgeClass = data === 'Active' ? 'bg-label-success' : 'bg-label-secondary';
            return `<span class="badge ${badgeClass}">${data}</span>`;
          }
        },
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
              '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon faculty-edit"><i class="ti ti-pencil ti-md"></i></a>' +
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
      buttons: [
        {
          text: '<i class="ti ti-plus me-sm-1"></i> <span class="d-none d-sm-inline-block">Add New Record</span>',
          className: 'create-new btn btn-primary waves-effect waves-light'
        }
      ],
      initComplete: function () {
        $('.card-header').after('<hr class="my-0">');
      }
    });

    $('div.head-label').html('<h5 class="card-title mb-0">Faculties</h5>');
  }

  fv.on('core.form.valid', function () {
    const id = $('#form-add-new-record').attr('data-id');
    let url = window.facultyUrls.store;
    let method = 'POST';
    let successMessage = 'Faculty added successfully.';

    const data = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      faculty_name: $('.dt-faculty-name').val(),
      faculty_code: $('.dt-faculty-code').val(),
      dean_name: $('.dt-dean-name').val(),
      email: $('.dt-email').val(),
      phone: $('.dt-phone').val(),
      status: $('.dt-status').val()
    };

    if (id) {
      url = window.facultyUrls.update + '/' + id;
      method = 'PUT';
      successMessage = 'Faculty updated successfully.';
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

  $('.datatables-basic tbody').on('click', '.faculty-edit', function () {
    const row = dt_basic.row($(this).parents('tr'));
    const data = row.data();
    const $editBtn = $(this);
    SpinnerUtils.show($editBtn, 'Loading...');

    setTimeout(function () {
      offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
      $('.dt-faculty-name').val(data.faculty_name);
      $('.dt-faculty-code').val(data.faculty_code);
      $('.dt-dean-name').val(data.dean_name);
      $('.dt-email').val(data.email);
      $('.dt-phone').val(data.phone);
      $('.dt-status').val(data.status);
      $('#form-add-new-record').attr('data-id', data.id);
      document.querySelector('#exampleModalLabel').textContent = 'Edit Faculty';
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
          url: window.facultyUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          onSuccess: function () {
            dt_basic.row($deleteBtn.closest('tr')).remove().draw();
            SpinnerUtils.hide($deleteBtn);
            if (typeof toastr !== 'undefined') {
              toastr.success('Faculty deleted successfully.');
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
