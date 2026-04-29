/**
 * App Setting Datatables
 */

'use strict';

// Datatable (jquery)
$(function () {
  let borderColor, bodyBg, headingColor;

  if (isDarkStyle) {
    borderColor = config.colors_dark.borderColor;
    bodyBg = config.colors_dark.bodyBg;
    headingColor = config.colors_dark.headingColor;
  } else {
    borderColor = config.colors.borderColor;
    bodyBg = config.colors.bodyBg;
    headingColor = config.colors.headingColor;
  }

  // Variable declaration for table
  var dt_app_setting_table = $('.datatables-app-setting');

  // App Setting datatable
  if (dt_app_setting_table.length) {
    var dt_app_setting = dt_app_setting_table.DataTable({
      ajax: {
        url: '/app/settings/get-app-setting',
        type: 'GET',
        dataSrc: 'data'
    },
      columns: [
        // columns according to JSON
        { data: '' },
        { data: 'id' },
        { data: 'name' },
        { data: 'value' },
        { data: 'action' }
      ],
      columnDefs: [
        {
          // For Responsive
          className: 'control',
          searchable: false,
          orderable: false,
          responsivePriority: 2,
          targets: 0,
          render: function (data, type, full, meta) {
            return '';
          }
        },
        {
          // For Id
          targets: 1,
          responsivePriority: 1,

        },
        {
          // App Setting name
          targets: 2,
          responsivePriority: 1,
          render: function (data, type, full, meta) {
            var $name = full['name'];
            return '<span class="text-nowrap">' + $name + '</span>';
          }
        },
        {
            // App Setting value
            targets: 3,
            render: function (data, type, full, meta) {
              var $value = full['value'];
              return '<span class="text-nowrap">' + $value + '</span>';
            }
        },
        {
          // Actions
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<div class="d-inline-block text-nowrap">' +
              '<button class="btn btn-sm btn-icon"><i class="bx bx-edit"></i></button>' +
              '</div>'
            );
          }
        }
      ],
      order: [[1, 'desc']],
      dom:
        '<"row mx-2"' +
        '<"col-md-2"<"me-3"l>>' +
        '<"col-md-10"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-end flex-md-row flex-column mb-3 mb-md-0"fB>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      language: {
        sLengthMenu: '_MENU_',
        search: '',
        searchPlaceholder: 'Search..'
      },
      // Buttons with Dropdown
      buttons: [
        {
          text: '<i class="bx bx-plus me-0 me-sm-1"></i><span class="d-none d-sm-inline-block">Add App Setting</span>',
          className: 'add-new btn btn-primary mx-3',
          attr: {
            'data-bs-toggle': 'offcanvas',
            'data-bs-target': '#offcanvasAddAppSetting'
          }
        }
      ],
      // For responsive popup
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
      }
    });
  }

  // Add new app setting form validation
  var addNewAppSettingForm = document.getElementById('addNewAppSettingForm');
    var offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#offcanvasAddAppSetting'));

  // app setting form validation
  const fv = FormValidation.formValidation(addNewAppSettingForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Please enter app setting name'
          }
        }
      },
      value: {
        validators: {
            notEmpty: {
                message: 'Please enter app setting value'
            }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        // Use this for enabling/changing valid/invalid class
        eleValidClass: '',
        rowSelector: function (field, ele) {
          // field is the field name & ele is the field element
          return '.mb-3';
        }
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      // Submit the form when all fields are valid
      defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });

  fv.on('core.form.valid', function () {
    var $name = $('#name').val();
    var $value = $('#value').val();
    var id = $('#addNewAppSettingForm').attr('data-id');

    if ($name != '' && $value != '') {
      var url = '/app/settings/app-setting';
      var method = 'POST';
      var message = 'App Setting added successfully.';
      var data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        name: $name,
        value: $value
      };

      if (id) {
        url = '/app/settings/app-setting/' + id;
        method = 'PUT';
        message = 'App Setting updated successfully.';
      }

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function (response) {
                dt_app_setting.ajax.reload();
                offCanvasEl.hide();
                $('#addNewAppSettingForm').removeAttr('data-id');
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: message,
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            },
            error: function (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred.',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
                console.log(error);
            }
        });
    }
    });

  // Flatpicker
  var flatpickrDate = document.querySelector('[name="flatpickr-date"]');

  if (flatpickrDate) {
    flatpickrDate.flatpickr({
      monthSelectorType: 'static'
    });
  }

  // To remove ordering when modal opens
  $('.dt-buttons > .btn-group > .btn').on('click', function () {
    $('.modal .dt-search').addClass('d-none');
    $('.modal .dt-column-search').addClass('d-none');
  });

    // Edit Record
    $('.datatables-app-setting tbody').on('click', '.btn-icon', function () {
        var row = dt_app_setting.row($(this).parents('tr'));
        var data = row.data();
        offCanvasEl.show();
        $('#name').val(data.name);
        $('#value').val(data.value);
        $('#addNewAppSettingForm').attr('data-id', data.id);
    });
  $('.dataTables_filter .form-control').on('keyup', function () {
    if ($('.modal .dt-search').length > 0) {
      // $('.modal .dt-search').addClass('d-none');
    }
  });

  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);
});
