/**
 * DataTables Basic
 */

'use strict';

let fv, offCanvasEl;

function initializeRuleSelect(selector) {
  const ruleSelect = $(selector);
  if (ruleSelect.length) {
    $.ajax({
      url: '/app/settings/get-rules',
      type: 'GET',
      dataType: 'json',
      success: function (data) {
        const ruleData = $.map(data.data, function (rule) {
          return {
            id: rule.id,
            text: rule.name,
          };
        });

        ruleSelect.select2({
          placeholder: 'Select a rule',
          dropdownParent: ruleSelect.closest('.offcanvas'),
          data: ruleData
        });
      }
    });
  }
}

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
          $('#form-add-new-record').removeAttr('data-id');
          // Open offCanvas with form
          offCanvasEl.show();
        });
      }
    }, 200);

    // Form validation for Add new record
    fv = FormValidation.formValidation(formAddNewRecord, {
      fields: {
        academic_year_name: {
          validators: {
            notEmpty: {
              message: 'The academic year name is required'
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
  // Initialize Rule Select2
  initializeRuleSelect('.rule-select');
});

// datatable (jquery)
$(function () {
  var dt_basic_table = $('.datatables-users'),
    dt_basic;

  // DataTable with buttons
  // --------------------------------------------------------------------

  if (dt_basic_table.length) {
  dt_basic = dt_basic_table.DataTable({
    ajax: {
      url: window.userUrls.getData,
      type: 'GET',
      dataSrc: 'data'
    },
    columns: [
      { data: null },             // Id (auto index)
      { data: 'name' },           // Name
      { data: 'rule' },           // Rule
      { data: 'email' },          // Email
      { data: 'profile_picture' },// Picture
      { data: 'action' }          // Actions
    ],
    columnDefs: [
      {
        // Sr. No
        targets: 0,
        render: function (data, type, full, meta) {
          return meta.row + 1;
        }
      },
      {
        // Rule
        targets: 2,
        render: function (data, type, full, meta) {
          return full.rule ? full.rule.name : 'N/A';
        }
      },
      {
        // Profile Picture
        targets: 4,
        orderable: false,
        searchable: false,
        render: function (data, type, full, meta) {
          if (data) {
            return '<img src="/profile_pictures/' + data + '" alt="' + full.name + '" class="rounded-circle" width="40" height="40">';
          } else {
            return '<img src="/images/default-avatar.png" alt="default" class="rounded-circle" width="40" height="40">';
          }
        }
      },
      {
        // Actions
        targets: 5,
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
            return 'Details of ' + data['name'];
          }
        }),
        type: 'column',
        renderer: function (api, rowIdx, columns) {
          var data = $.map(columns, function (col, i) {
            return col.title !== ''
              ? '<tr data-dt-row="' +
              col.rowIndex +
              '" data-dt-column="' +
              col.columnIndex +
              '">' +
              '<td>' +
              col.title +
              ':</td> ' +
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
  $('div.head-label').html('<h5 class="card-title mb-0">User</h5>');
}



  // Add/Update Record
  fv.on('core.form.valid', function () {
    var form = $('#form-add-new-record')[0]; // Get the form element
    var formData = new FormData(form); // Pass complete form (with file input, CSRF, etc.)
    var id = $('#form-add-new-record').attr('data-id');

    var url = window.userUrls.store;
    var method = 'POST';
    var message = 'User added successfully.';

    if (id) {
      url = window.userUrls.update + '/' + id;
      formData.append('_method', 'PUT'); // Laravel expects this for updates
      message = 'User updated successfully.';
    }

    $.ajax({
      url: url,
      type: 'POST', // Always POST with FormData
      data: formData,
      processData: false, // important for FormData
      contentType: false, // important for FormData
      success: function (response) {
        dt_basic.ajax.reload();
        offCanvasEl.hide();
        $('#form-add-new-record').removeAttr('data-id');
        toastr.success(message);
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
  });


  // Delete Record
  $('.datatables-users tbody').on('click', '.delete-record', function () {
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
          url: window.userUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            row.remove().draw();
            toastr.success('User deleted successfully.');
          },
          error: function (error) {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: 'An error occurred while deleting the user.',
              customClass: {
                confirmButton: 'btn btn-danger'
              }
            });
          }
        });
      }
    });
  });

  // Edit Record
  $('.datatables-users tbody').on('click', '.item-edit', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
    document.querySelector('.dt-full-name').value = data.name;
    document.querySelector('.dt-email').value = data.email;
    $('#form-add-new-record').attr('data-id', data.id);
    offCanvasEl.show();
  });
});
