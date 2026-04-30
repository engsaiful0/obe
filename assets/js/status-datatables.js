/**
 * Status CRUD with AJAX and Spinner
 * Enhanced with proper spinner utilities and error handling
 */

'use strict';

let fv, offCanvasEl, dt_basic;

// Utility functions for spinner and loading states
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
    // Restore original text if stored, otherwise use provided text
    const text = originalText || element.data('original-text');
    if (text) {
      element.html(text);
    }
  },

  showTable: function () {
    if (dt_basic && typeof dt_basic.processing === 'function') {
      dt_basic.processing(true);
    }
  },

  hideTable: function () {
    if (dt_basic && typeof dt_basic.processing === 'function') {
      dt_basic.processing(false);
    }
  }
};

// Enhanced AJAX utility with error handling
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
        if (options.showTableSpinner) {
          SpinnerUtils.showTable();
        }
      },
      complete: function () {
        if (options.showSpinner && options.spinnerElement) {
          SpinnerUtils.hide(options.spinnerElement);
        }
        if (options.showTableSpinner) {
          SpinnerUtils.hideTable();
        }
      },
      success: function (response) {
        if (options.onSuccess) {
          options.onSuccess(response);
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', { xhr, status, error });
        AjaxUtils.handleError(xhr, options);
      }
    };

    return $.ajax($.extend(defaults, options));
  },

  handleError: function (xhr, options = {}) {
    let message = 'An error occurred. Please try again.';

    if (xhr.status === 422) {
      const errors = xhr.responseJSON?.errors;
      if (errors) {
        message = Object.values(errors).flat().join('<br>');
      }
    } else if (xhr.status === 500) {
      message = 'Server error. Please contact support.';
    } else if (xhr.status === 404) {
      message = 'Resource not found.';
    } else if (xhr.responseJSON && xhr.responseJSON.message) {
      message = xhr.responseJSON.message;
    }

    if (typeof toastr !== 'undefined') {
      toastr.error(message);
    } else {
      alert('Error: ' + message);
    }

    if (options.onError) {
      options.onError(xhr, message);
    }
  }
};

function refillStatusRelatedToSelect(preserveVal) {
  var url = typeof window.statusRelatedToListUrl !== 'undefined' ? window.statusRelatedToListUrl : '';
  if (!url) {
    return $.Deferred().resolve().promise();
  }
  var $sel = $('.add-new-record .dt-related-to');
  return $.ajax({
    url: url,
    type: 'GET',
    dataType: 'json',
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  })
    .done(function (res) {
      var keep = preserveVal !== undefined ? preserveVal : '';
      $sel.find('option:not(:first)').remove();
      var rows = (res.data || []).slice().sort(function (a, b) {
        return (a.name || '').localeCompare(b.name || '', undefined, {
          sensitivity: 'base'
        });
      });
      rows.forEach(function (row) {
        if (row.id === null || row.id === undefined || row.id === '') {
          return;
        }
        $sel.append(
          $('<option></option>')
            .attr('value', String(row.id))
            .text(row.name || '#' + row.id)
        );
      });
      var keepStr = keep === '' || keep === null ? '' : String(keep);
      $sel.val(keepStr);
    })
    .fail(function () {
      if (typeof toastr !== 'undefined') {
        toastr.error('Could not load Related To options.');
      }
    });
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
          offCanvasEl = offCanvasEl || new bootstrap.Offcanvas(offCanvasElement);
          refillStatusRelatedToSelect('')
            .done(function () {
              offCanvasElement.querySelector('.dt-full-name').value = '';
              $('.add-new-record .dt-related-to').val('');
              $('#form-add-new-record').removeAttr('data-id');
              document.querySelector('#exampleModalLabel').textContent = 'New Status';
              offCanvasEl.show();
            });
        });
      }
    }, 200);

    // Form validation for Add new record
    fv = FormValidation.formValidation(formAddNewRecord, {
      fields: {
        status_name: {
          validators: {
            notEmpty: {
              message: 'The status name is required'
            }
          }
        },
        related_to_id: {
          validators: {
            notEmpty: {
              message: 'The related to field is required'
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
  if (typeof window.statusUrls === 'undefined') {
    return;
  }

  var dt_basic_table = $('.datatables-basic');

  // DataTable with buttons
  // --------------------------------------------------------------------

  if (dt_basic_table.length) {
    dt_basic = dt_basic_table.DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: window.statusUrls.getData,
        type: 'GET',
        dataSrc: 'data',
        error: function (xhr, error, thrown) {
          console.error('DataTable AJAX Error:', { xhr, error, thrown });
          if (typeof toastr !== 'undefined') {
            toastr.error('Failed to load statuses. Please try again.');
          }
        }
      },
      columns: [
        {
          data: 'id',
          name: 'id',
          render: function (data, type, row, meta) {
            return meta.row + 1;
          }
        },
        { data: 'status_name' },
        {
          data: null,
          render: function (data, type, row) {
            var name =
              row.related_to && row.related_to.name
                ? row.related_to.name
                : null;
            var label =
              name ||
              (row.related_to_id != null && row.related_to_id !== ''
                ? '#' + row.related_to_id
                : '');
            if (!label) {
              return '—';
            }
            var escaped = $('<div/>').text(label).html();
            return '<span class="badge bg-label-primary">' + escaped + '</span>';
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
              '<a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon status-edit"><i class="ti ti-pencil ti-md"></i></a>' +
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
              return 'Details of ' + data['status_name'];
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
    $('div.head-label').html('<h5 class="card-title mb-0">Statuses</h5>');
  }

  // Add/Update Record
  fv.on('core.form.valid', function () {
    var $new_name = $('.add-new-record .dt-full-name').val();
    var $related_to_id = $('.add-new-record .dt-related-to').val();
    var id = $('#form-add-new-record').attr('data-id');

    if (
      $new_name != '' &&
      $related_to_id != '' &&
      !isNaN(parseInt($related_to_id, 10))
    ) {
      var parsedRelatedId = parseInt($related_to_id, 10);
      var url = window.statusUrls.store;
      var method = 'POST';
      var message = 'Status added successfully.';
      var data = {
        _token: $('meta[name="csrf-token"]').attr('content'),
        status_name: $new_name,
        related_to_id: parsedRelatedId
      };

      if (id) {
        url = window.statusUrls.update + '/' + id;
        method = 'PUT';
        message = 'Status updated successfully.';
      }

      // Get submit button
      var $submitBtn = $('#form-add-new-record button[type="submit"]');

      // Use AjaxUtils with spinner
      AjaxUtils.request({
        url: url,
        type: method,
        data: data,
        showSpinner: true,
        spinnerElement: $submitBtn,
        spinnerText: id ? 'Updating...' : 'Saving...',
        showTableSpinner: false, // Don't show table spinner for form submissions
        onSuccess: function (response) {
          if (dt_basic && typeof dt_basic.ajax !== 'undefined' && typeof dt_basic.ajax.reload === 'function') {
            dt_basic.ajax.reload(null, false); // Reload without resetting pagination
          }
          if (offCanvasEl) {
            offCanvasEl.hide();
          }
          $('#form-add-new-record').removeAttr('data-id');
          $('.add-new-record .dt-full-name').val(''); // clear input
          $('.add-new-record .dt-related-to').val(''); // clear select
          refillStatusRelatedToSelect('');

          if (typeof toastr !== 'undefined') {
            toastr.success(message);
          }
        },
        onError: function (xhr, errorMessage) {
          // Error already handled by AjaxUtils.handleError
        }
      });
    }
  });


  // Delete Record
  $('.datatables-basic tbody').on('click', '.delete-record', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    var $deleteBtn = $(this);

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
        // Show spinner on delete button
        SpinnerUtils.show($deleteBtn, 'Deleting...');

        AjaxUtils.request({
          url: window.statusUrls.destroy + '/' + data.id,
          type: 'DELETE',
          data: {
            _token: $('meta[name="csrf-token"]').attr('content')
          },
          showSpinner: false, // We're handling spinner manually
          showTableSpinner: false, // Don't show table spinner for delete
          onSuccess: function (response) {
            if (dt_basic && typeof dt_basic.row === 'function') {
              dt_basic.row($deleteBtn.closest('tr')).remove().draw();
            }
            if (typeof toastr !== 'undefined') {
              toastr.success('Status deleted successfully.');
            }
            // Hide spinner on success
            SpinnerUtils.hide($deleteBtn);
          },
          onError: function (xhr, errorMessage) {
            // Error already handled by AjaxUtils.handleError
            // Hide spinner on error
            SpinnerUtils.hide($deleteBtn);
          }
        });
      }
    });
  });

  // Edit Record
  $('.datatables-basic tbody').on('click', '.status-edit', function () {
    var row = dt_basic.row($(this).parents('tr'));
    var data = row.data();
    var $editBtn = $(this);

    SpinnerUtils.show($editBtn, 'Loading...');

    refillStatusRelatedToSelect(data.related_to_id || '')
      .always(function () {
        SpinnerUtils.hide($editBtn);
      })
      .done(function () {
        offCanvasEl =
          offCanvasEl ||
          new bootstrap.Offcanvas(document.querySelector('#add-new-record'));
        document.querySelector('.dt-full-name').value = data.status_name || '';
        document.querySelector('.dt-related-to').value = data.related_to_id || '';
        $('#form-add-new-record').attr('data-id', data.id);
        document.querySelector('#exampleModalLabel').textContent = 'Edit Status';
        offCanvasEl.show();
      });
  });
});
