'use strict';

$(function () {
  try {
    var cfgEl = document.getElementById('damage-urls');
    if (cfgEl) {
      window.damageUrls = JSON.parse(cfgEl.textContent);
    }
  } catch (e) { /* ignore */ }

  let rowIndex = 1;

  function updateSerials() {
    $('#damage-items-container tr').each(function (idx) {
      $(this).find('.serial').text(idx + 1);
    });
  }

  $('#add-row-btn').on('click', function () {
    $.ajax({
      url: window.damageUrls.productRow,
      method: 'GET',
      data: { row_index: rowIndex },
      success: function (html) {
        $('#damage-items-container').append(html);
        rowIndex += 1;
        updateSerials();
      },
      error: function () { toastr.error('Failed to add row'); }
    });
  });

  $('#damage-items-container').on('click', '.remove-row-btn', function () {
    if ($('#damage-items-container tr').length > 1) {
      $(this).closest('tr').remove();
      updateSerials();
    }
  });

  function validateForm() {
    let valid = true;
    const warehouseId = $('#warehouse_id').val();
    const date = $('#date').val();
    if (!warehouseId) { toastr.error('Warehouse is required'); valid = false; }
    if (!date) { toastr.error('Date is required'); valid = false; }

    let hasItem = false;
    $('#damage-items-container tr').each(function () {
      const itemId = $(this).find('.item-select').val();
      const qty = parseFloat($(this).find('.quantity-input').val());
      if (itemId && qty > 0) { hasItem = true; }
    });
    if (!hasItem) { toastr.error('Add at least one valid item'); valid = false; }
    return valid;
  }

  $('#damage-form').on('submit', function (e) {
    e.preventDefault();
    if (!validateForm()) return;

    const formData = new FormData(this);
    const payload = {
      warehouse_id: formData.get('warehouse_id'),
      date: formData.get('date'),
      remarks: formData.get('remarks'),
      items: []
    };

    $('#damage-items-container tr').each(function () {
      const itemId = $(this).find('.item-select').val();
      const qtyVal = $(this).find('.quantity-input').val();
      const reasonVal = $(this).find('.reason-input').val();
      const approximateVal = $(this).find('.approximate-input').val();
      if (itemId && qtyVal) {
        payload.items.push({ 
          item_id: itemId, 
          quantity: parseFloat(qtyVal), 
          reason: reasonVal || null,
          approximate: approximateVal ? parseFloat(approximateVal) : null
        });
      }
    });

    $('#damage-form').addClass('form-loading');
    const $btn = $('#save-damage-btn');
    const originalBtnHtml = $btn.html();
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...');

    $.ajax({
      url: window.damageUrls.store,
      method: 'POST',
      data: payload,
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      success: function (res) {
        toastr.success(res.message || 'Saved');
        // Redirect to view page after success
        setTimeout(function() {
          if (window.damageUrls && window.damageUrls.view) {
            window.location.href = window.damageUrls.view;
          } else {
            window.location.href = '/app/damage/view-damage';
          }
        }, 1500);
      },
      error: function (xhr) {
        let msg = 'Error saving damage';
        if (xhr.responseJSON && xhr.responseJSON.message) { msg = xhr.responseJSON.message; }
        else if (xhr.responseJSON && xhr.responseJSON.errors) { msg = Object.values(xhr.responseJSON.errors).flat().join('<br>'); }
        toastr.error(msg);
      },
      complete: function () {
        $('#damage-form').removeClass('form-loading');
        $btn.prop('disabled', false).html(originalBtnHtml);
      }
    });
  });
});


