/**
 * App access-rules list
 */

'use strict';

// Datatable (jquery)
$(function () {
  var dtUserTable = $('.datatables-rules');

  // Users List datatable
  if (dtUserTable.length) {
    var dtUser = dtUserTable.DataTable({
      columnDefs: [
        {
          // Actions
          targets: -1,
          title: 'Actions',
          searchable: false,
          orderable: false,
          render: function (data, type, full, meta) {
            return (
              '<a href="javascript:;" class="btn btn-sm btn-icon edit-rule" data-id="' + full[2] + '"><i class="ti ti-edit"></i></a>' +
              '<a href="javascript:;" class="btn btn-sm btn-icon delete-rule" data-id="' + full[2] + '"><i class="ti ti-trash"></i></a>'
            );
          }
        }
      ],
      order: [[0, 'asc']],
      dom:
        '<"row mx-2"' +
        '<"col-sm-12 col-md-4 col-lg-6" l>' +
        '<"col-sm-12 col-md-8 col-lg-6"<"dt-action-buttons text-xl-end text-lg-start text-md-end text-start d-flex align-items-center justify-content-md-end justify-content-center align-items-center flex-sm-nowrap flex-wrap me-1"<"me-3"f>>>' +
        '>t' +
        '<"row mx-2"' +
        '<"col-sm-12 col-md-6"i>' +
        '<"col-sm-12 col-md-6"p>' +
        '>',
      language: {
        sLengthMenu: 'Show _MENU_',
        search: 'Search',
        searchPlaceholder: 'Search..'
      },
    });
  }

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    $('.dataTables_filter .form-control').removeClass('form-control-sm');
    $('.dataTables_length .form-select').removeClass('form-select-sm');
  }, 300);

  // Add New Rule
  $('#addRuleForm').on('submit', function (e) {
    e.preventDefault();
    var form = $(this);
    var url = form.attr('action');
    var method = form.attr('method');
    var data = form.serialize();

    $.ajax({
      url: url,
      method: method,
      data: data,
      success: function (response) {
        if (response.success) {
          $('#addRuleModal').modal('hide');
          toastr.success(response.message);
          // Redraw the table
          var permissions = response.rule.permissions.map(function (p) {
            return '<span class="badge bg-label-primary">' + p.name + '</span>';
          }).join(' ');
          dtUser.row.add([
            response.rule.name,
            permissions,
            response.rule.id
          ]).draw();
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong!',
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Something went wrong!',
        });
      }
    });
  });

  // Edit Rule
  dtUserTable.on('click', '.edit-rule', function () {
    var id = $(this).closest('tr').data('id');
    var editUrl = dtUserTable.data('edit-url').replace(':id', id);
    $.ajax({
      url: editUrl,
      method: 'GET',
      success: function (response) {
        var updateUrl = dtUserTable.data('update-url').replace(':id', response.rule.id);
        $('#editRuleModal #editRuleId').val(response.rule.id);
        $('#editRuleModal #modalRuleName').val(response.rule.name);
        $('#editRuleModal #editRuleForm').attr('action', updateUrl);
        $('#editRuleModal .permission-checkbox').prop('checked', false);
        response.rule.permissions.forEach(function (p) {
          $('#editRuleModal #editPermission' + p.id).prop('checked', true);
        });
        $('#editRuleModal').modal('show');
      }
    });
  });

  // Update Rule
  $('#editRuleForm').on('submit', function (e) {
    e.preventDefault();
    var form = $(this);
    var url = form.attr('action');
    var method = 'PUT';
    var data = form.serialize();

    $.ajax({
      url: url,
      method: method,
      data: data,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        if (response.success) {
          $('#editRuleModal').modal('hide');
          toastr.success(response.message);
          // Redraw the table
          var row = dtUser.row(function (idx, data, node) {
            return data[2] === response.rule.id;
          });
          var permissions = response.rule.permissions.map(function (p) {
            return '<span class="badge bg-label-primary">' + p.name + '</span>';
          }).join(' ');
          row.data([
            response.rule.name,
            permissions,
            response.rule.id
          ]).draw();
        } else {
          toastr.error('Something went wrong!');
        }
      },
      error: function () {
        toastr.error('Something went wrong!');
      }
    });
  });

  // Delete Rule
  dtUserTable.on('click', '.delete-rule', function () {
    var id = $(this).closest('tr').data('id');
    var deleteUrl = dtUserTable.data('delete-url').replace(':id', id);
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
          url: deleteUrl,
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function (response) {
            if (response.success) {
              Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: response.message,
                customClass: {
                  confirmButton: 'btn btn-success'
                }
              });
              // Remove the row from the datatable
              dtUser.row(function (idx, data, node) {
                return data[2] === id;
              }).remove().draw();
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Something went wrong!',
              });
            }
          },
          error: function () {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Something went wrong!',
            });
          }
        });
      }
    });
  });

});

$(function () {
  // Handle select all for Add Rule modal
  $('#addSelectAll').on('change', function () {
    var isChecked = $(this).is(':checked');
    $('#addRuleForm .permission-checkbox').prop('checked', isChecked);
  });

  // Handle select all for Edit Rule modal
  $('#editSelectAll').on('change', function () {
    var isChecked = $(this).is(':checked');
    $('#editRuleForm .permission-checkbox').prop('checked', isChecked);
  });

  // Deselect all checkboxes on modal close
  $('#addRuleModal, #editRuleModal').on('hidden.bs.modal', function () {
    $(this).find('input[type="checkbox"]').prop('checked', false);
  });
});
