/**
 * Settings — Grade CRUD (AJAX + spinners).
 */
(function () {
  var cfg = window.__gradeSettings || {};
  var listUrl = cfg.listUrl || '';
  var storeUrl = cfg.storeUrl || '';
  var updateBase = (cfg.updateUrlBase || '').replace(/\/$/, '');
  var csrf = cfg.csrf || '';
  var str = cfg.strings || {};

  function escapeHtml(s) {
    var div = document.createElement('div');
    div.textContent = s == null ? '' : String(s);
    return div.innerHTML;
  }

  function csrfMeta() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : csrf;
  }

  function setBtnLoading(btn, on) {
    if (!btn) return;
    btn.disabled = !!on;
    var spin = btn.querySelector('.obe-btn-spinner');
    var label = btn.querySelector('.obe-btn-label');
    if (spin) spin.classList.toggle('d-none', !on);
    if (label) label.classList.toggle('opacity-50', !!on);
  }

  function setTableLoading(on) {
    var el = document.getElementById('grade-table-overlay');
    if (el) el.classList.toggle('d-none', !on);
  }

  function showFormErrors(htmlOrNull) {
    var box = document.getElementById('grade-form-errors');
    if (!box) return;
    if (!htmlOrNull) {
      box.classList.add('d-none');
      box.innerHTML = '';
      return;
    }
    box.classList.remove('d-none');
    box.innerHTML = htmlOrNull;
  }

  function parseErrors(payload) {
    var errs = payload && payload.errors;
    if (!errs || typeof errs !== 'object') {
      return payload && payload.message ? '<div>' + escapeHtml(payload.message) + '</div>' : '';
    }
    var parts = [];
    Object.keys(errs).forEach(function (k) {
      var msgs = errs[k];
      if (Array.isArray(msgs)) {
        msgs.forEach(function (m) {
          parts.push('<li>' + escapeHtml(m) + '</li>');
        });
      }
    });
    return parts.length ? '<ul class="mb-0 ps-3">' + parts.join('') + '</ul>' : '';
  }

  function tbody() {
    return document.getElementById('grade-table-body');
  }

  function renderRows(rows) {
    var tb = tbody();
    if (!tb) return;
    tb.innerHTML = '';
    if (!rows || !rows.length) {
      var tr = document.createElement('tr');
      tr.id = 'grade-table-empty';
      tr.innerHTML =
        '<td colspan="5" class="text-muted text-center py-4">' + escapeHtml(str.empty || 'No grades yet.') + '</td>';
      tb.appendChild(tr);
      return;
    }
    rows.forEach(function (g) {
      var tr = document.createElement('tr');
      tr.setAttribute('data-id', String(g.id));
      tr.innerHTML =
        '<td>' +
        escapeHtml(g.from_marks) +
        '</td><td>' +
        escapeHtml(g.to_marks) +
        '</td><td>' +
        escapeHtml(g.grade_name) +
        '</td><td>' +
        escapeHtml(g.grade_point) +
        '</td><td class="text-end">' +
        '<button type="button" class="btn btn-sm btn-label-primary grade-btn-edit me-1">' +
        escapeHtml(str.edit || 'Edit') +
        '</button>' +
        '<button type="button" class="btn btn-sm btn-label-danger grade-btn-del">' +
        escapeHtml(str.delete || 'Delete') +
        '</button>' +
        '</td>';
      tb.appendChild(tr);
    });
  }

  function loadList() {
    if (!listUrl) return Promise.resolve();
    setTableLoading(true);
    return fetch(listUrl, {
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfMeta()
      }
    })
      .then(function (res) {
        return res.json().then(function (data) {
          return { ok: res.ok, data: data };
        });
      })
      .then(function (pack) {
        if (pack.ok && pack.data && Array.isArray(pack.data.data)) {
          renderRows(pack.data.data);
        } else if (typeof toastr !== 'undefined') {
          toastr.error((pack.data && pack.data.message) || str.loadFailed || 'Could not load grades.');
        }
      })
      .catch(function () {
        if (typeof toastr !== 'undefined') toastr.error(str.network || 'Network error.');
      })
      .finally(function () {
        setTableLoading(false);
      });
  }

  function openOffcanvas(isEdit, row) {
    var title = document.getElementById('grade-offcanvas-title');
    var form = document.getElementById('grade-form');
    var idInp = document.getElementById('grade_id');
    if (!form || !idInp) return;
    showFormErrors(null);
    form.reset();
    idInp.value = '';
    if (title) title.textContent = isEdit ? str.editTitle || 'Edit grade' : str.addTitle || 'Add grade';
    if (isEdit && row) {
      idInp.value = row.id;
      document.getElementById('from_marks').value = row.from_marks;
      document.getElementById('to_marks').value = row.to_marks;
      document.getElementById('grade_name').value = row.grade_name;
      document.getElementById('grade_point').value = row.grade_point;
    }
    var oc = document.getElementById('grade-offcanvas');
    if (oc && typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
      bootstrap.Offcanvas.getOrCreateInstance(oc).show();
    }
  }

  function closeOffcanvas() {
    var oc = document.getElementById('grade-offcanvas');
    if (oc && typeof bootstrap !== 'undefined' && bootstrap.Offcanvas) {
      bootstrap.Offcanvas.getInstance(oc)?.hide();
    }
  }

  function submitForm() {
    var form = document.getElementById('grade-form');
    var btn = document.getElementById('grade-form-submit');
    if (!form) return;
    showFormErrors(null);
    setBtnLoading(btn, true);

    var id = (document.getElementById('grade_id') || {}).value;
    var isEdit = id && String(id).trim() !== '';
    var url = isEdit ? updateBase + '/' + encodeURIComponent(id) : storeUrl;

    var fd = new FormData(form);
    if (isEdit) {
      fd.append('_method', 'PUT');
    }

    fetch(url, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfMeta()
      }
    })
      .then(function (res) {
        return res.json().then(function (data) {
          return { ok: res.ok, status: res.status, data: data };
        });
      })
      .then(function (pack) {
        if (pack.ok) {
          if (typeof toastr !== 'undefined' && pack.data.message) toastr.success(pack.data.message);
          closeOffcanvas();
          return loadList();
        }
        var html = parseErrors(pack.data);
        showFormErrors(html || '<div>' + escapeHtml(str.validationFailed || 'Validation failed.') + '</div>');
      })
      .catch(function () {
        if (typeof toastr !== 'undefined') toastr.error(str.network || 'Network error.');
      })
      .finally(function () {
        setBtnLoading(btn, false);
      });
  }

  function confirmDelete() {
    var msg = str.deleteConfirm || 'Delete this grade?';
    if (typeof Swal !== 'undefined' && Swal.fire) {
      return Swal.fire({
        text: msg,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        focusCancel: true,
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
          confirmButton: 'btn btn-danger me-2',
          cancelButton: 'btn btn-label-secondary'
        }
      }).then(function (r) {
        return !!r.isConfirmed;
      });
    }
    return Promise.resolve(window.confirm(msg));
  }

  function deleteRow(id) {
    confirmDelete().then(function (ok) {
      if (!ok) return;
      setTableLoading(true);
      var fd = new FormData();
      fd.append('_method', 'DELETE');
      fd.append('_token', csrfMeta());

      fetch(updateBase + '/' + encodeURIComponent(id), {
        method: 'POST',
        body: fd,
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfMeta()
        }
      })
        .then(function (res) {
          return res.json().then(function (data) {
            return { ok: res.ok, data: data };
          });
        })
        .then(function (pack) {
          if (pack.ok) {
            if (typeof toastr !== 'undefined' && pack.data.message) toastr.success(pack.data.message);
            return loadList();
          }
          if (typeof toastr !== 'undefined') {
            toastr.error((pack.data && pack.data.message) || str.deleteFailed || 'Delete failed.');
          }
        })
        .catch(function () {
          if (typeof toastr !== 'undefined') toastr.error(str.network || 'Network error.');
        })
        .finally(function () {
          setTableLoading(false);
        });
    });
  }

  function rowDataFromTr(tr) {
    var tds = tr.querySelectorAll('td');
    if (tds.length < 4) return null;
    return {
      id: tr.getAttribute('data-id'),
      from_marks: tds[0].textContent.trim(),
      to_marks: tds[1].textContent.trim(),
      grade_name: tds[2].textContent.trim(),
      grade_point: tds[3].textContent.trim()
    };
  }

  document.addEventListener('DOMContentLoaded', function () {
    var addBtn = document.getElementById('grade-btn-open-add');
    if (addBtn) {
      addBtn.addEventListener('click', function () {
        openOffcanvas(false, null);
      });
    }

    var form = document.getElementById('grade-form');
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitForm();
      });
    }

    var tb = tbody();
    if (tb) {
      tb.addEventListener('click', function (e) {
        var editBtn = e.target.closest('.grade-btn-edit');
        var delBtn = e.target.closest('.grade-btn-del');
        var tr = e.target.closest('tr[data-id]');
        if (editBtn && tr) {
          openOffcanvas(true, rowDataFromTr(tr));
        } else if (delBtn && tr) {
          deleteRow(tr.getAttribute('data-id'));
        }
      });
    }

    loadList();
  });
})();
