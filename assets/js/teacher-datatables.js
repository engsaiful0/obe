/**
 * Teacher CRUD with AJAX and Spinner
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

function loadDropdowns() {
  if (typeof window.teacherMetaUrls === 'undefined') {
    return;
  }
  const $dept = $('.dt-department-id');
  const $des = $('.dt-designation-id');
  $dept.prop('disabled', true);
  $des.prop('disabled', true);

  $.getJSON(window.teacherMetaUrls.departments)
    .done(function (res) {
      const list = res.data || [];
      $dept.find('option:not(:first)').remove();
      list.forEach(function (d) {
        const label = d.name + (d.faculty ? ' — ' + d.faculty.faculty_name : '');
        $dept.append($('<option></option>').attr('value', d.id).text(label));
      });
    })
    .always(function () {
      $dept.prop('disabled', false);
    });

  $.getJSON(window.teacherMetaUrls.designations)
    .done(function (res) {
      const list = res.data || [];
      $des.find('option:not(:first)').remove();
      list.forEach(function (d) {
        $des.append(
          $('<option></option>').attr('value', d.id).text(d.designation_name)
        );
      });
    })
    .always(function () {
      $des.prop('disabled', false);
    });
}

document.addEventListener('DOMContentLoaded', function () {
  const formAddNewRecord = document.getElementById('form-add-new-record');
  loadDropdowns();

  setTimeout(() => {
    const newRecord = document.querySelector('.create-new');
    const offCanvasElement = document.querySelector('#add-new-record');

    if (newRecord) {
      newRecord.addEventListener('click', function () {
        offCanvasEl = new bootstrap.Offcanvas(offCanvasElement);
        formAddNewRecord.reset();
        $('#form-add-new-record').removeAttr('data-id');
        $('.dt-password').val('');
        document.querySelector('#exampleModalLabel').textContent = 'New Teacher';
        offCanvasEl.show();
      });
    }
  }, 200);

  fv = FormValidation.formValidation(formAddNewRecord, {
    fields: {
      department_id: {
        validators: {
          notEmpty: {
            message: 'Department is required'
          }
        }
      },
      teacher_name: {
        validators: {
          notEmpty: {
            message: 'Teacher name is required'
          }
        }
      },
      designation_id: {
        validators: {
          notEmpty: {
            message: 'Designation is required'
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
      employee_id: {
        validators: {
          notEmpty: {
            message: 'Employee ID is required'
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
  if (typeof window.teacherUrls === 'undefined') {
    return;
  }

  const dt_basic_table = $('.datatables-basic');

  if (dt_basic_table.length) {
    dt_basic = dt_basic_table.DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: window.teacherUrls.getData,
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
        {
          data: 'department',
          render: function (data) {
            return data && data.name ? data.name : '—';
          }
        },
        { data: 'teacher_name' },
        {
          data: 'designation',
          render: function (data) {
            return data && data.designation_name ? data.designation_name : '—';
          }
        },
        { data: 'email' },
        { data: 'phone' },
        { data: 'employee_id' },
        {
          data: 'login_email',
          render: function (data, type, row) {
            const v = row.login_email || (row.user && row.user.email);
            return v ? v : '—';
          }
        },
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
              '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon teacher-edit"><i class="ti ti-pencil ti-md"></i></a>' +
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

    $('div.head-label').html('<h5 class="card-title mb-0">Teachers</h5>');
  }

  fv.on('core.form.valid', function () {
    const id = $('#form-add-new-record').attr('data-id');
    let url = window.teacherUrls.store;
    let method = 'POST';
    let successMessage = 'Teacher added successfully.';

    const data = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      department_id: $('.dt-department-id').val(),
      teacher_name: $('.dt-teacher-name').val(),
      designation_id: $('.dt-designation-id').val(),
      email: $('.dt-email').val(),
      phone: $('.dt-phone').val(),
      employee_id: $('.dt-employee-id').val(),
      login_email: $('.dt-login-email').val() || '',
      password: $('.dt-password').val() || '',
      status: $('.dt-status').val()
    };

    if (id) {
      url = window.teacherUrls.update + '/' + id;
      method = 'PUT';
      successMessage = 'Teacher updated successfully.';
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
        $('.dt-password').val('');
        if (typeof toastr !== 'undefined') {
          toastr.success(successMessage);
        }
      }
    });
  });

  $('.datatables-basic tbody').on('click', '.teacher-edit', function () {
    const row = dt_basic.row($(this).parents('tr'));
    const data = row.data();
    const $editBtn = $(this);
    SpinnerUtils.show($editBtn, 'Loading...');

    setTimeout(function () {
      offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
      $('.dt-department-id').val(data.department_id);
      $('.dt-teacher-name').val(data.teacher_name);
      $('.dt-designation-id').val(data.designation_id);
      $('.dt-email').val(data.email);
      $('.dt-phone').val(data.phone);
      $('.dt-employee-id').val(data.employee_id);
      const login = data.login_email || (data.user && data.user.email) || '';
      $('.dt-login-email').val(login);
      $('.dt-password').val('');
      $('.dt-status').val(data.status);
      $('#form-add-new-record').attr('data-id', data.id);
      document.querySelector('#exampleModalLabel').textContent = 'Edit Teacher';
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
          url: window.teacherUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          onSuccess: function () {
            dt_basic.row($deleteBtn.closest('tr')).remove().draw();
            SpinnerUtils.hide($deleteBtn);
            if (typeof toastr !== 'undefined') {
              toastr.success('Teacher deleted successfully.');
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
