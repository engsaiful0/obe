/**
 * Add Student — load dropdowns + submit via AJAX with spinner (multipart).
 */
'use strict';

const StudentCreateSpinner = {
  show: function ($btn, text) {
    $btn.prop('disabled', true);
    if (!$btn.data('original-html')) {
      $btn.data('original-html', $btn.html());
    }
    $btn.html(
      '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
        (text || 'Saving...')
    );
  },
  hide: function ($btn) {
    $btn.prop('disabled', false);
    const orig = $btn.data('original-html');
    if (orig) {
      $btn.html(orig);
    }
  }
};

function showErrors(xhr) {
  let message = 'Could not save student.';
  if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
    message = Object.values(xhr.responseJSON.errors)
      .flat()
      .join('<br>');
  } else if (xhr.responseJSON && xhr.responseJSON.message) {
    message = xhr.responseJSON.message;
  }
  if (typeof toastr !== 'undefined') {
    toastr.error(message);
  } else {
    alert(message.replace(/<br>/g, '\n'));
  }
}

function fillSelect($select, items, valueKey, labelFn, placeholder) {
  const v = $select.val();
  $select.empty();
  $select.append($('<option></option>').val('').text(placeholder));
  items.forEach(function (item) {
    $select.append(
      $('<option></option>')
        .val(item[valueKey])
        .text(labelFn(item))
    );
  });
  if (v) {
    $select.val(v);
  }
}

function applyFormMeta(data) {
  fillSelect(
    $('#program_id'),
    data.programs || [],
    'id',
    function (p) {
      return (p.program_name || '') + (p.program_code ? ' (' + p.program_code + ')' : '');
    },
    '— Select program —'
  );

  fillSelect(
    $('#academic_session_id'),
    data.academic_sessions || [],
    'id',
    function (s) {
      let t = s.session_name || '';
      if (s.academic_year) {
        t += ' (' + s.academic_year + ')';
      }
      return t;
    },
    '— Select session —'
  );

  fillSelect(
    $('#gender_id'),
    data.genders || [],
    'id',
    function (g) {
      return g.gender_name || '';
    },
    '— Select gender —'
  );

  fillSelect(
    $('#religion_id'),
    data.religions || [],
    'id',
    function (r) {
      return r.religion_name || '';
    },
    '— Select religion —'
  );

  fillSelect(
    $('#nationality_id'),
    data.nationalities || [],
    'id',
    function (n) {
      return n.nationality_name || '';
    },
    '— Select nationality —'
  );

  fillSelect(
    $('#blood_group_id'),
    data.blood_groups || [],
    'id',
    function (b) {
      return b.blood_group_name || '';
    },
    '— Select blood group —'
  );

  fillSelect(
    $('#marital_status_id'),
    data.marital_statuses || [],
    'id',
    function (m) {
      return m.marital_status_name || '';
    },
    '— Select marital status —'
  );
}

function loadFormMeta() {
  return $.ajax({
    url: window.studentCreateUrls.meta,
    type: 'GET',
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }).done(applyFormMeta);
}

function loadBatches(programId) {
  const $batch = $('#batch_id');
  if (!programId) {
    $batch.prop('disabled', true);
    fillSelect($batch, [], 'id', function () {
      return '';
    }, '— Select program first —');
    return;
  }
  $batch.prop('disabled', true);
  $.ajax({
    url: window.studentCreateUrls.batches,
    type: 'GET',
    data: { program_id: programId },
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  })
    .done(function (res) {
      const rows = res.data || [];
      $batch.empty();
      $batch.append($('<option></option>').val('').text('— Select batch —'));
      rows.forEach(function (b) {
        let label = b.batch_name || '';
        if (b.batch_code) {
          label += ' (' + b.batch_code + ')';
        }
        if (b.academic_session && b.academic_session.session_name) {
          label += ' · ' + b.academic_session.session_name;
        }
        $batch.append($('<option></option>').val(b.id).text(label));
      });
      $batch.prop('disabled', false);
    })
    .fail(function () {
      if (typeof toastr !== 'undefined') {
        toastr.error('Could not load batches.');
      }
      $batch.prop('disabled', false);
    });
}

$(function () {
  if (typeof window.studentCreateUrls === 'undefined') {
    return;
  }

  const $form = $('#studentCreateForm');
  const $submit = $('#studentSubmitBtn');

  loadFormMeta().fail(function () {
    if (typeof toastr !== 'undefined') {
      toastr.error('Could not load form options.');
    }
  });

  $('#program_id').on('change', function () {
    loadBatches($(this).val());
  });

  $form.on('submit', function (e) {
    e.preventDefault();

    const fd = new FormData(this);
    const token = $('meta[name="csrf-token"]').attr('content');

    StudentCreateSpinner.show($submit, 'Saving...');

    $.ajax({
      url: window.studentCreateUrls.store,
      type: 'POST',
      data: fd,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .done(function (res) {
        if (typeof toastr !== 'undefined') {
          toastr.success(res.message || 'Student created successfully.');
        }
        $form[0].reset();
        $('#status').val('Active');
        $('#shift').val('Morning');
        $('#student_type').val('Regular');
        loadFormMeta();
        $('#batch_id')
          .empty()
          .append($('<option></option>').val('').text('— Select program first —'))
          .prop('disabled', true);
        loadBatches('');
      })
      .fail(function (xhr) {
        showErrors(xhr);
      })
      .always(function () {
        StudentCreateSpinner.hide($submit);
      });
  });
});
