'use strict';

let addOffCanvasEl, editOffCanvasEl, dtFeeHeads, fvAdd, fvEdit;

document.addEventListener('DOMContentLoaded', function () {
  const addForm = document.getElementById('addNewFeeHeadForm');
  const editForm = document.querySelector('.edit-fee-head-form');

  // Initialize Add Form Validation
  fvAdd = FormValidation.formValidation(addForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Fee Head name is required'
          }
        }
      },
      semester_id: {
        validators: {
          notEmpty: {
            message: 'Semester is required'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: '.mb-3'
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });

  // Initialize Edit Form Validation
  fvEdit = FormValidation.formValidation(editForm, {
    fields: {
      name: {
        validators: {
          notEmpty: {
            message: 'Fee Head name is required'
          }
        }
      },
      semester_id: {
        validators: {
          notEmpty: {
            message: 'Semester is required'
          }
        }
      }
    },
    plugins: {
      trigger: new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({
        eleValidClass: '',
        rowSelector: '.mb-3'
      }),
      submitButton: new FormValidation.plugins.SubmitButton(),
      autoFocus: new FormValidation.plugins.AutoFocus()
    }
  });

  // Handle Create Button
  document.querySelector('.create-new')?.addEventListener('click', function () {
    addOffCanvasEl = new bootstrap.Offcanvas('#offcanvasAddFeeHead');
    addForm.reset();
    fvAdd.resetForm();
    $('.semester-select').val('').trigger('change');
    addOffCanvasEl.show();
  });

  // Initialize Select2 for semesters
  $('.semester-select').select2({
    dropdownParent: $('.offcanvas'),
    placeholder: 'Select Semester',
    ajax: {
      url: '/api/semesters', // ✅ Replace with actual route
      dataType: 'json',
      processResults: function (data) {
        return {
          results: data.map(item => ({ id: item.id, text: item.name }))
        };
      }
    }
  });

  // Initialize DataTable
  const table = $('.datatables-fee-heads');

  if (table.length) {
    dtFeeHeads = table.DataTable({
      ajax: '/app/settings/get-fee-head',
      columns: [
        { data: 'id' },
        { data: 'name' },
        { data: 'semester.name' },
        { data: null }
      ],
      columnDefs: [
        {
          targets: 0,
          searchable: false,
          visible: false
        },
        {
          targets: -1,
          title: 'Actions',
          orderable: false,
          searchable: false,
          render: function (data, type, full, meta) {
            return `
              <div class="d-inline-block">
                <button class="btn btn-sm btn-icon item-edit" data-id="${full.id}"><i class="ti ti-pencil"></i></button>
                <button class="btn btn-sm btn-icon text-danger delete-record" data-id="${full.id}"><i class="ti ti-trash"></i></button>
              </div>`;
          }
        }
      ],
      order: [[0, 'desc']],
      responsive: true,
      dom: '<"row mx-2"<"col-md-6"l><"col-md-6 text-end"B>>t<"row mx-2"<"col-md-6"i><"col-md-6"p>>',
      buttons: [],
      initComplete: function () {
        $('.card-header').after('<hr class="my-0" />');
      }
    });
  }

  // Handle Add Form Submit
  fvAdd.on('core.form.valid', function () {
    const data = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      name: $('#name').val(),
      semester_id: $('#semester_id').val()
    };

    $.post('/app/settings/fee-head', data)
      .done(function () {
        dtFeeHeads.ajax.reload();
        addOffCanvasEl.hide();
        toastr.success('Fee Head added successfully.');
      })
      .fail(function () {
        toastr.error('An error occurred while adding fee head.');
      });
  });

  // Handle Edit Button Click
  table.on('click', '.item-edit', function () {
    const rowData = dtFeeHeads.row($(this).parents('tr')).data();

    $('#edit_name').val(rowData.name);
    $('#edit_semester_id').html(`<option value="${rowData.semester.id}" selected>${rowData.semester.name}</option>`).trigger('change');
    $('#fee_head_id').val(rowData.id);

    editOffCanvasEl = new bootstrap.Offcanvas('#offcanvasEditFeeHead');
    fvEdit.resetForm();
    editOffCanvasEl.show();
  });

  // Handle Edit Form Submit
  fvEdit.on('core.form.valid', function () {
    const id = $('#fee_head_id').val();
    const data = {
      _token: $('meta[name="csrf-token"]').attr('content'),
      name: $('#edit_name').val(),
      semester_id: $('#edit_semester_id').val(),
      _method: 'PUT'
    };

    $.ajax({
      url: '/app/settings/fee-head/' + id,
      type: 'POST',
      data: data,
      success: function () {
        dtFeeHeads.ajax.reload();
        editOffCanvasEl.hide();
        toastr.success('Fee Head updated successfully.');
      },
      error: function () {
        toastr.error('An error occurred while updating fee head.');
      }
    });
  });

  // Handle Delete
  table.on('click', '.delete-record', function () {
    const id = $(this).data('id');

    if (confirm('Are you sure you want to delete this fee head?')) {
      $.ajax({
        url: '/app/settings/fee-head/' + id,
        type: 'DELETE',
        data: {
          _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function () {
          dtFeeHeads.ajax.reload();
          toastr.success('Fee Head deleted successfully.');
        },
        error: function () {
          toastr.error('An error occurred while deleting.');
        }
      });
    }
  });
});
