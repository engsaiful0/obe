(function () {
  function showFeedback(el, message, type) {
    if (!el) return;
    if (!message) {
      el.className = 'alert d-none';
      el.textContent = '';
      return;
    }
    el.className = 'alert alert-' + (type || 'info');
    el.textContent = message;
  }

  function statusOptions(selected) {
    var statuses = [
      { v: 'present', l: 'Present' },
      { v: 'absent', l: 'Absent' },
      { v: 'late', l: 'Late' },
      { v: 'excused', l: 'Excused' }
    ];
    return statuses.map(function (s) {
      return '<option value="' + s.v + '"' + (selected === s.v ? ' selected' : '') + '>' + s.l + '</option>';
    }).join('');
  }

  function initAttendance() {
    var cfg = window.__studentAttendanceConfig || {};
    var dateInput = document.getElementById('attendance-date');
    var tbody = document.querySelector('#attendance-roster-table tbody');
    var historyBody = document.querySelector('#attendance-history-table tbody');
    var saveBtn = document.getElementById('attendance-save-btn');
    var markAllBtn = document.getElementById('attendance-mark-all-present');
    var feedback = document.getElementById('attendance-feedback');
    var spinner = document.getElementById('attendance-save-spinner');
    if (!cfg.rosterRoute || !tbody) return;

    var currentRows = [];

    function renderRoster(students) {
      currentRows = students || [];
      tbody.innerHTML = '';
      currentRows.forEach(function (s) {
        var tr = document.createElement('tr');
        tr.dataset.studentId = s.student_id;
        tr.innerHTML =
          '<td>' + s.serial + '</td>' +
          '<td>' + (s.student_code || '') + '</td>' +
          '<td>' + (s.student_name || '') + '</td>' +
          '<td>' + (s.batch_name || '') + '</td>' +
          '<td><select class="form-select form-select-sm att-status" ' + (cfg.readonly ? 'disabled' : '') + '>' +
          statusOptions(s.status) + '</select></td>' +
          '<td><input type="text" class="form-control form-control-sm att-remarks" value="' + (s.remarks || '') + '" ' + (cfg.readonly ? 'readonly' : '') + '></td>';
        tbody.appendChild(tr);
      });
    }

    function loadRoster() {
      var date = dateInput ? dateInput.value : '';
      fetch(cfg.rosterRoute + '?date=' + encodeURIComponent(date), {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin'
      })
        .then(function (res) { return res.json(); })
        .then(function (data) { renderRoster(data.students || []); });
    }

    function loadHistory() {
      if (!historyBody || !cfg.historyRoute) return;
      fetch(cfg.historyRoute, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin'
      })
        .then(function (res) { return res.json(); })
        .then(function (data) {
          historyBody.innerHTML = '';
          (data.dates || []).forEach(function (row) {
            var tr = document.createElement('tr');
            tr.innerHTML =
              '<td>' + row.date + '</td>' +
              '<td class="text-center">' + row.total + '</td>' +
              '<td class="text-center">' + row.present + '</td>' +
              '<td class="text-center">' + row.absent + '</td>' +
              '<td class="text-end">' + row.percentage + '%</td>';
            historyBody.appendChild(tr);
          });
        });
    }

    if (dateInput) {
      dateInput.addEventListener('change', loadRoster);
    }

    if (markAllBtn) {
      markAllBtn.addEventListener('click', function () {
        tbody.querySelectorAll('.att-status').forEach(function (sel) {
          sel.value = 'present';
        });
      });
    }

    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        var rows = [];
        tbody.querySelectorAll('tr').forEach(function (tr) {
          rows.push({
            student_id: parseInt(tr.dataset.studentId, 10),
            status: tr.querySelector('.att-status').value,
            remarks: tr.querySelector('.att-remarks').value || null
          });
        });
        saveBtn.disabled = true;
        if (spinner) spinner.classList.remove('d-none');
        showFeedback(feedback, '', 'info');
        fetch(cfg.saveRoute, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': cfg.csrf,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            attendance_date: dateInput ? dateInput.value : '',
            rows: rows
          })
        })
          .then(function (res) { return res.json().then(function (d) { return { ok: res.ok, data: d }; }); })
          .then(function (pack) {
            if (pack.ok) {
              showFeedback(feedback, pack.data.message || 'Saved.', 'success');
              loadHistory();
              return;
            }
            showFeedback(feedback, pack.data.message || 'Failed to save.', 'danger');
          })
          .finally(function () {
            saveBtn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
          });
      });
    }

    document.querySelectorAll('#attendance-tabs button[data-bs-target="#att-history"]').forEach(function (btn) {
      btn.addEventListener('shown.bs.tab', loadHistory);
    });

    loadRoster();
    loadHistory();
  }

  document.addEventListener('DOMContentLoaded', initAttendance);
})();
