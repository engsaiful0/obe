(function () {
  function csrfToken() {
    var token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
  }

  function setBtnLoading(btn, loading) {
    if (!btn) return;
    btn.disabled = !!loading;
    var spin = btn.querySelector('.obe-btn-spinner');
    var label = btn.querySelector('.obe-btn-label');
    if (spin) spin.classList.toggle('d-none', !loading);
    if (label) label.classList.toggle('opacity-50', !!loading);
  }

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

  function flattenErrors(errors) {
    if (!errors) return '';
    if (typeof errors === 'string') return errors;
    if (Array.isArray(errors)) return errors.join(' ');
    return Object.keys(errors).map(function (k) {
      var v = errors[k];
      return Array.isArray(v) ? v.join(' ') : String(v);
    }).join(' ');
  }

  function initCourseListPage() {
    var routes = window.__myCoursesRoutes || {};
    var search = document.getElementById('my-course-search');
    var container = document.getElementById('my-course-table-container');
    var loading = document.getElementById('my-course-loading');
    if (!routes.list || !container || !search) return;

    function loadPage(url) {
      if (loading) loading.classList.remove('d-none');
      fetch(url, {
        headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      })
        .then(function (res) { return res.text(); })
        .then(function (html) { container.innerHTML = html; })
        .finally(function () {
          if (loading) loading.classList.add('d-none');
        });
    }

    var timer;
    search.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(function () {
        loadPage(routes.list + '?search=' + encodeURIComponent(search.value || ''));
      }, 300);
    });

    container.addEventListener('click', function (e) {
      var link = e.target.closest('.my-course-pagination a');
      if (!link) return;
      e.preventDefault();
      loadPage(link.href);
    });
  }

  function initMarksEntryPage() {
    var config = window.__myCourseMarksConfig || {};
    if (!config.studentsRoute || !Array.isArray(config.columns)) return;

    var body = document.getElementById('marks-student-body');
    var loading = document.getElementById('marks-loading');
    var feedback = document.getElementById('my-course-feedback');
    var pagination = document.getElementById('marks-pagination');
    var searchInput = document.getElementById('marks-student-search');
    var saveBtn = document.getElementById('marks-save-btn');
    var previewBtn = document.getElementById('marks-preview-btn');
    var importBtn = document.getElementById('marks-import-btn');
    var importConfirmBtn = document.getElementById('marks-import-confirm-btn');
    var importForm = document.getElementById('my-course-import-form');
    var previewModalEl = document.getElementById('importPreviewModal');
    var previewModal = previewModalEl && window.bootstrap ? new window.bootstrap.Modal(previewModalEl) : null;

    var currentRows = [];
    var currentPage = 1;
    var lastPage = 1;
    var pendingImportRows = null;

    function columnLabel(column) {
      return column.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    function collectMarksForStudent(studentId) {
      var marks = {};
      config.columns.forEach(function (column) {
        var input = body.querySelector('input.mark-input[data-student-id="' + studentId + '"][data-column="' + column + '"]');
        marks[column] = input ? parseFloat(input.value || 0) : 0;
      });
      return marks;
    }

    function renderRows(payload) {
      currentRows = payload.students || [];
      lastPage = (payload.pagination && payload.pagination.last_page) || 1;
      currentPage = (payload.pagination && payload.pagination.current_page) || 1;
      body.innerHTML = '';

      currentRows.forEach(function (student) {
        var tr = document.createElement('tr');
        tr.dataset.studentId = student.id;

        var td = document.createElement('td');
        td.className = 'sticky-col';
        td.innerHTML =
          '<div class="fw-semibold small">' + (student.student_code || '') + '</div>' +
          '<div class="text-muted small">' + (student.registration_no || '') + '</div>' +
          '<div class="small">' + (student.student_name || '') + '</div>';
        tr.appendChild(td);

        config.columns.forEach(function (column) {
          var markTd = document.createElement('td');
          var input = document.createElement('input');
          input.type = 'number';
          input.min = '0';
          input.max = String(config.maxMarks || 100);
          input.step = '0.01';
          input.className = 'form-control form-control-sm mark-input';
          input.dataset.studentId = student.id;
          input.dataset.column = column;
          input.value = student.marks[column] || 0;
          markTd.appendChild(input);
          tr.appendChild(markTd);
        });

        ['total_marks', 'total_marks_percentage', 'total_marks_grade_name'].forEach(function (field, idx) {
          var calcTd = document.createElement('td');
          calcTd.className = 'calc-cell text-nowrap small';
          calcTd.dataset.field = field;
          calcTd.dataset.studentId = student.id;
          if (field === 'total_marks') {
            calcTd.textContent = (student.total_marks || 0).toFixed(2);
          } else if (field === 'total_marks_percentage') {
            calcTd.textContent = (student.total_marks_percentage || 0).toFixed(2);
          } else {
            calcTd.textContent = student.total_marks_grade_name || '-';
          }
          tr.appendChild(calcTd);
        });

        var actionTd = document.createElement('td');
        actionTd.className = 'sticky-col-end';
        var saveOne = document.createElement('button');
        saveOne.type = 'button';
        saveOne.className = 'btn btn-outline-primary btn-sm save-one-btn';
        saveOne.dataset.studentId = student.id;
        saveOne.textContent = 'Save';
        actionTd.appendChild(saveOne);
        tr.appendChild(actionTd);

        body.appendChild(tr);
      });

      if (pagination) {
        pagination.innerHTML =
          '<button type="button" class="btn btn-outline-secondary btn-sm me-2" id="marks-prev-page" ' +
          (currentPage <= 1 ? 'disabled' : '') + '>Prev</button>' +
          '<span>Page ' + currentPage + ' of ' + lastPage + ' (Total: ' +
          ((payload.pagination && payload.pagination.total) || 0) + ')</span>' +
          '<button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="marks-next-page" ' +
          (currentPage >= lastPage ? 'disabled' : '') + '>Next</button>';
      }
    }

    function loadStudents(page) {
      currentPage = page || 1;
      if (loading) loading.classList.remove('d-none');
      var query = '?page=' + currentPage + '&search=' + encodeURIComponent(searchInput ? searchInput.value : '');
      fetch(config.studentsRoute + query, {
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        credentials: 'same-origin'
      })
        .then(function (res) {
          return res.json().then(function (data) {
            return { ok: res.ok, data: data };
          });
        })
        .then(function (pack) {
          if (!pack.ok) {
            showFeedback(feedback, (pack.data && pack.data.message) || 'Could not load students.', 'danger');
            renderRows({ students: [], pagination: { current_page: 1, last_page: 1, total: 0 } });
            return;
          }
          renderRows(pack.data || {});
        })
        .finally(function () {
          if (loading) loading.classList.add('d-none');
        });
    }

    if (pagination) {
      pagination.addEventListener('click', function (e) {
        if (e.target.id === 'marks-next-page' && currentPage < lastPage) {
          loadStudents(currentPage + 1);
        }
        if (e.target.id === 'marks-prev-page' && currentPage > 1) {
          loadStudents(currentPage - 1);
        }
      });
    }

    var timer;
    if (searchInput) {
      searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
          loadStudents(1);
        }, 300);
      });
    }

    if (body) {
      body.addEventListener('click', function (e) {
        var btn = e.target.closest('.save-one-btn');
        if (!btn) return;
        var studentId = parseInt(btn.dataset.studentId, 10);
        if (!studentId) return;

        btn.disabled = true;
        fetch(config.saveSingleRoute, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken()
          },
          body: JSON.stringify({
            student_id: studentId,
            marks: collectMarksForStudent(studentId)
          })
        })
          .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
          .then(function (pack) {
            if (pack.ok) {
              showFeedback(feedback, pack.data.message || 'Saved.', 'success');
              loadStudents(currentPage);
              return;
            }
            showFeedback(feedback, flattenErrors(pack.data.errors) || pack.data.message || 'Save failed.', 'danger');
          })
          .finally(function () {
            btn.disabled = false;
          });
      });
    }

    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        var students = currentRows.map(function (row) {
          return { student_id: row.id, marks: collectMarksForStudent(row.id) };
        });

        saveBtn.disabled = true;
        showFeedback(feedback, '', 'info');
        fetch(config.saveRoute, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken()
          },
          body: JSON.stringify({ students: students })
        })
          .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
          .then(function (pack) {
            if (pack.ok) {
              showFeedback(feedback, pack.data.message || 'Saved successfully.', 'success');
              loadStudents(currentPage);
              return;
            }
            showFeedback(feedback, flattenErrors(pack.data.errors) || pack.data.message || 'Failed to save.', 'danger');
          })
          .finally(function () {
            saveBtn.disabled = false;
          });
      });
    }

    function renderPreviewTable(preview) {
      var table = document.getElementById('import-preview-table');
      if (!table) return;
      var thead = table.querySelector('thead');
      var tbody = table.querySelector('tbody');
      thead.innerHTML = '';
      tbody.innerHTML = '';
      if (!preview || !preview.length) return;

      var headers = ['student_code', 'student_name', 'total_marks', 'total_marks_percentage', 'total_marks_grade_name'];
      var trh = document.createElement('tr');
      headers.forEach(function (h) {
        var th = document.createElement('th');
        th.textContent = columnLabel(h);
        trh.appendChild(th);
      });
      thead.appendChild(trh);

      preview.forEach(function (row) {
        var tr = document.createElement('tr');
        headers.forEach(function (h) {
          var td = document.createElement('td');
          td.textContent = row[h] != null ? row[h] : '';
          tr.appendChild(td);
        });
        tbody.appendChild(tr);
      });
    }

    if (previewBtn && importForm) {
      previewBtn.addEventListener('click', function () {
        var fileInput = document.getElementById('marks-import-file');
        if (!fileInput || !fileInput.files.length) {
          showFeedback(feedback, 'Please choose an Excel file first.', 'warning');
          return;
        }
        var fd = new FormData(importForm);
        previewBtn.disabled = true;
        fetch(config.importPreviewRoute, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken()
          },
          body: fd
        })
          .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
          .then(function (pack) {
            if (!pack.ok) {
              showFeedback(feedback, flattenErrors(pack.data.errors) || pack.data.message || 'Preview failed.', 'danger');
              return;
            }
            pendingImportRows = pack.data.rows || [];
            renderPreviewTable(pack.data.preview || []);
            if (previewModal) previewModal.show();
            if (importBtn) importBtn.classList.remove('d-none');
          })
          .finally(function () {
            previewBtn.disabled = false;
          });
      });
    }

    function confirmImport() {
      if (!pendingImportRows || !pendingImportRows.length) {
        showFeedback(feedback, 'No preview data to import.', 'warning');
        return;
      }
      var fd = new FormData();
      fd.append('confirmed_rows', JSON.stringify(pendingImportRows));
      fd.append('_token', csrfToken());

      var activeBtn = importConfirmBtn || importBtn;
      if (activeBtn) activeBtn.disabled = true;

      fetch(config.importRoute, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken()
        },
        body: fd
      })
        .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
        .then(function (pack) {
          if (pack.ok) {
            showFeedback(feedback, pack.data.message || 'Imported successfully.', 'success');
            pendingImportRows = null;
            if (previewModal) previewModal.hide();
            loadStudents(1);
            return;
          }
          showFeedback(feedback, flattenErrors(pack.data.errors) || pack.data.message || 'Import failed.', 'danger');
        })
        .finally(function () {
          if (activeBtn) activeBtn.disabled = false;
        });
    }

    if (importConfirmBtn) {
      importConfirmBtn.addEventListener('click', confirmImport);
    }
    if (importBtn) {
      importBtn.addEventListener('click', confirmImport);
    }

    renderRows(config.initialStudents || { students: [], pagination: {} });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initCourseListPage();
    initMarksEntryPage();
  });
})();
