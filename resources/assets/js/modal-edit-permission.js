/**
 * Edit Permission Modal JS
 */

'use strict';

$(function () {
  var editPermissionForm = $('#editPermissionForm');

  $('.datatables-permissions tbody').on('click', '.edit-record', function () {
    var id = $(this).data('id');
    var editUrl = $('.datatables-permissions').data('edit-url').replace(':id', id);
    var updateUrl = $('.datatables-permissions').data('update-url').replace(':id', id);
    $.get(editUrl, function (data) {
      $('#editPermissionName').val(data.permission.name);
      editPermissionForm.attr('action', updateUrl);
    });
  });

  if (editPermissionForm.length) {
    editPermissionForm.on('submit', function (e) {
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
            $('#editPermissionModal').modal('hide');
            $('.datatables-permissions').DataTable().ajax.reload();
          }
        }
      });
    });
  }
});
