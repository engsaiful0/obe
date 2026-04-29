/**
 * password-toggle.js
 * Toggle password visibility on all fields
 */
'use strict';

$(document).ready(function () {
  $(document).on('click', '.toggle-password', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const $input = $btn.closest('.input-group').find('input');
    const $icon = $btn.find('i');

    if ($input.attr('type') === 'password') {
      $input.attr('type', 'text');
      $icon.removeClass('bx-hide').addClass('bx-show');
    } else {
      $input.attr('type', 'password');
      $icon.removeClass('bx-show').addClass('bx-hide');
    }
  });
});
