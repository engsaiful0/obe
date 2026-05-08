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
    var importBtn = document.getElementById('marks-import-btn');
    var importForm = document.getElementById('my-course-import-form');
    var currentRows = [];
    var currentPage = 1;

    function renderRows(payload) {
      currentRows = payload.students || [];
      body.innerHTML = '';
      currentRows.forEach(function (student) {
        var tr = document.createElement('tr');
        var td = document.createElement('td');
        td.innerHTML = '<div class="fw-semibold small">' + student.student_code + '</div><div class="text-muted small">' + student.student_name + '</div>';
        tr.appendChild(td);

        config.columns.forEach(function (column) {
          var markTd = document.createElement('td');
          var input = document.createElement('input');
          input.type = 'number';
          input.step = '0.01';
          input.className = 'form-control form-control-sm mark-input';
          input.dataset.studentId = student.id;
          input.dataset.column = column;
          input.value = student.marks[column] || 0;
          markTd.appendChild(input);
          tr.appendChild(markTd);
        });

        body.appendChild(tr);
      });

      var page = payload.pagination || {};
      pagination.textContent = 'Page ' + (page.current_page || 1) + ' of ' + (page.last_page || 1) + ' (Total: ' + (page.total || 0) + ')';
    }

    function loadStudents(page) {
      currentPage = page || 1;
      if (loading) loading.classList.remove('d-none');
      var query = '?page=' + currentPage + '&search=' + encodeURIComponent(searchInput ? searchInput.value : '');
      fetch(config.studentsRoute + query, {
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() },
        credentials: 'same-origin'
      })
        .then(function (res) { return res.json(); })
        .then(renderRows)
        .finally(function () {
          if (loading) loading.classList.add('d-none');
        });
    }

    pagination.addEventListener('click', function () {
      loadStudents(currentPage + 1);
    });

    var timer;
    if (searchInput) {
      searchInput.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
          loadStudents(1);
        }, 300);
      });
    }

    if (saveBtn) {
      saveBtn.addEventListener('click', function () {
        var students = [];
        currentRows.forEach(function (row) {
          var marks = {};
          config.columns.forEach(function (column) {
            var input = body.querySelector('input.mark-input[data-student-id="' + row.id + '"][data-column="' + column + '"]');
            marks[column] = input ? parseFloat(input.value || 0) : 0;
          });
          students.push({ student_id: row.id, marks: marks });
        });

        setBtnLoading(saveBtn, true);
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
              return;
            }
            showFeedback(feedback, pack.data.message || 'Failed to save.', 'danger');
          })
          .finally(function () {
            setBtnLoading(saveBtn, false);
          });
      });
    }

    if (importBtn && importForm) {
      importBtn.addEventListener('click', function () {
        var fd = new FormData(importForm);
        setBtnLoading(importBtn, true);
        showFeedback(feedback, '', 'info');
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
              loadStudents(1);
              return;
            }
            showFeedback(feedback, pack.data.message || 'Import failed.', 'danger');
          })
          .finally(function () {
            setBtnLoading(importBtn, false);
          });
      });
    }

    renderRows(config.initialStudents || { students: [], pagination: {} });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initCourseListPage();
    initMarksEntryPage();
  });
})();
