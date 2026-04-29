/**
 * Add Permission Modal JS
 */

'use strict';

$(function () {
  var addPermissionForm = $('#addPermissionForm');

  if (addPermissionForm.length) {
    addPermissionForm.on('submit', function (e) {
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
            $('#addPermissionModal').modal('hide');
            $('.datatables-permissions').DataTable().ajax.reload();
          }
        }
      });
    });
  }
});
