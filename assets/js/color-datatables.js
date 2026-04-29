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
          offCanvasElement.querySelector('#color_name').value = '';
          offCanvasElement.querySelector('#color_code').value = '';
          $('#form-add-new-record').removeAttr('data-id');
          // Initialize color picker functionality
          initializeColorPicker();
          // Open offCanvas with form
          offCanvasEl.show();
        });
      }
    }, 200);

    // Form validation for Add new record
    if (typeof FormValidation === 'undefined') {
      console.error('FormValidation library not loaded');
    }
    fv = FormValidation.formValidation(formAddNewRecord, {
      fields: {
        color_name: {
          validators: {
            notEmpty: {
              message: 'The color name is required'
            }
          }
        },
        color_code: {
          validators: {
            notEmpty: {
              message: 'The color code is required'
            },
            regexp: {
              regexp: /^#[0-9A-Fa-f]{6}$/,
              message: 'Color code must be a valid hex color (e.g., #FF5733)'
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

// Color picker functionality
function initializeColorPicker() {
  const colorCodeInput = document.getElementById('color_code');
  const colorPicker = document.getElementById('color_picker');
  const colorPreview = document.getElementById('color_preview');
  const previewText = document.getElementById('preview_text');
  const previewCode = document.getElementById('preview_code');
  
  // Function to update preview
  function updatePreview() {
    const color = colorCodeInput.value || '#000000';
    colorPreview.style.backgroundColor = color;
    previewCode.textContent = color.toUpperCase();
    previewText.textContent = colorCodeInput.value ? 'Selected Color' : 'Select a color';
  }
  
  // Color picker to text input
  if (colorPicker) {
    colorPicker.addEventListener('input', function() {
      colorCodeInput.value = this.value;
      updatePreview();
    });
  }
  
  // Text input to color picker
  if (colorCodeInput) {
    colorCodeInput.addEventListener('input', function() {
      if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
        if (colorPicker) colorPicker.value = this.value;
        updatePreview();
      }
    });
  }
  
  // Click on color preview to open color picker
  if (colorPreview) {
    colorPreview.addEventListener('click', function() {
      if (colorPicker) {
        colorPicker.click();
      }
    });
    colorPreview.style.cursor = 'pointer';
    colorPreview.title = 'Click to open color picker';
  }
  
  // Initial preview
  updatePreview();
}

// datatable (jquery)
$(function () {
  // Wait for URLs to be defined
  if (typeof window.colorUrls === 'undefined') {
    console.error('Color URLs not defined');
    return;
  }
  
  var dt_basic_table = $('.datatables-basic'),
    dt_basic;

  // DataTable with buttons
  // --------------------------------------------------------------------

  if (dt_basic_table.length) {
    dt_basic = dt_basic_table.DataTable({
      ajax: {
        url: window.colorUrls.getData,
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
        { 
          data: 'color_code', 
          name: 'color_code',
          render: function(data, type, row) {
            return '<div class="d-flex align-items-center">' +
                   '<div style="width: 30px; height: 30px; background-color: ' + data + '; border: 1px solid #ccc; border-radius: 5px; margin-right: 10px;"></div>' +
                   '<span class="badge bg-secondary">' + data + '</span>' +
                   '</div>';
          }
        },
        { data: 'color_name' },
        { 
          data: 'color_code', 
          name: 'color_code',
          render: function(data, type, row) {
            return '<code>' + data.toUpperCase() + '</code>';
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
              '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon color-edit"><i class="ti ti-pencil ti-md"></i></a>' +
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
              return 'Details of ' + data['color_name'];
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
    $('div.head-label').html('<h5 class="card-title mb-0">Colors</h5>');
  }

  // Add/Update Record
  fv.on('core.form.valid', function () {
    var $color_name = $('.add-new-record .dt-full-name').val();
    var $color_code = $('#color_code').val();
    var $description = $('#description').val();
    var id = $('#form-add-new-record').attr('data-id');

    if ($color_name != '' && $color_code != '') {
      var url = window.colorUrls.store;
      var method = 'POST';
      var message = 'Color added successfully.';
      var data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        color_name: $color_name,
        color_code: $color_code,
        description: $description
      };

      if (id) {
        url = window.colorUrls.update + '/' + id;
        method = 'PUT';
        message = 'Color updated successfully.';
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
          $('#color_code').val(''); // clear input
          $('#description').val(''); // clear input
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
          url: window.colorUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            row.remove().draw();
            toastr.success('Color deleted successfully.');
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
  $('.datatables-basic tbody').on('click', '.color-edit', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    offCanvasEl = new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
    document.querySelector('.dt-full-name').value = data.color_name;
    document.querySelector('#color_code').value = data.color_code;
    document.querySelector('#description').value = data.description || '';
    $('#form-add-new-record').attr('data-id', data.id);
    
    // Initialize color picker functionality for edit mode
    initializeColorPicker();
    
    offCanvasEl.show();
  });
});