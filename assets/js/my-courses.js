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

  function humanizeErrorText(text) {
    if (!text) return text;
    var lower = String(text).toLowerCase();
    if (lower.indexOf('duplicate entry') !== -1 || lower.indexOf('unique constraint') !== -1) {
      if (lower.indexOf('student_marks') !== -1) {
        return 'Marks for this student are already saved for this course. Save again to update existing marks.';
      }
      return 'This record already exists. Please update the existing entry instead of creating a duplicate.';
    }
    return text;
  }

  function flattenErrors(errors) {
    if (!errors) return '';
    if (typeof errors === 'string') return humanizeErrorText(errors);
    if (Array.isArray(errors)) return humanizeErrorText(errors.join(' '));
    return humanizeErrorText(
      Object.keys(errors)
        .map(function (k) {
          var v = errors[k];
          return Array.isArray(v) ? v.join(' ') : String(v);
        })
        .join(' ')
    );
  }

  function parseMarkValue(raw) {
    var v = parseFloat(String(raw || '').replace(',', '.'));
    return isFinite(v) ? v : 0;
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
    var table = document.getElementById('marks-excel-table');
    var loading = document.getElementById('marks-loading');
    var feedback = document.getElementById('my-course-feedback');
    var pagination = document.getElementById('marks-pagination');
    var searchInput = document.getElementById('marks-student-search');
    var saveBtn = document.getElementById('marks-save-btn');

    var currentRows = [];
    var currentPage = 1;

    function columnMax(column) {
      if (config.maxByColumn && config.maxByColumn[column] !== undefined) {
        return config.maxByColumn[column];
      }
      return config.maxMarks || 100;
    }

    function resolveGrade(percentage) {
      var scale = config.gradeScale || [];
      for (var i = 0; i < scale.length; i++) {
        var g = scale[i];
        if (percentage >= g.from_marks && percentage <= g.to_marks) {
          return g.grade_name || '';
        }
      }
      return '';
    }

    function recalcRow(tr) {
      if (!tr) return;
      var studentId = tr.dataset.studentId;
      var total = 0;
      tr.querySelectorAll('input.mark-input').forEach(function (inp) {
        var max = parseFloat(inp.getAttribute('data-max') || '0');
        var v = parseMarkValue(inp.value);
        if (v < 0) v = 0;
        if (isFinite(max) && max > 0 && v > max) {
          inp.classList.add('is-over-max');
        } else {
          inp.classList.remove('is-over-max');
        }
        total += v;
      });
      total = Math.round(total * 100) / 100;
      var maxMarks = parseFloat(config.maxMarks || 100);
      var pct = maxMarks > 0 ? Math.round((total / maxMarks) * 10000) / 100 : 0;
      var grade = resolveGrade(pct);

      var totalCell = tr.querySelector('td[data-field="total_marks"][data-student-id="' + studentId + '"]');
      var pctCell = tr.querySelector('td[data-field="total_marks_percentage"][data-student-id="' + studentId + '"]');
      var gradeCell = tr.querySelector('td[data-field="total_marks_grade_name"][data-student-id="' + studentId + '"]');
      if (totalCell) totalCell.textContent = total.toFixed(2);
      if (pctCell) pctCell.textContent = pct.toFixed(2);
      if (gradeCell) gradeCell.textContent = grade;
    }

    function collectMarksForStudent(studentId) {
      var marks = {};
      config.columns.forEach(function (column) {
        var input = body.querySelector(
          'input.mark-input[data-student-id="' + studentId + '"][data-column="' + column + '"]'
        );
        marks[column] = input ? parseMarkValue(input.value) : 0;
      });
      return marks;
    }

    function appendIdentityCells(tr, student, rowIndex) {
      var tdSl = document.createElement('td');
      tdSl.className = 'col-sl';
      tdSl.textContent = String(rowIndex);
      tr.appendChild(tdSl);

      var tdId = document.createElement('td');
      tdId.className = 'col-id text-nowrap';
      tdId.textContent = student.student_code || '';
      tr.appendChild(tdId);

      var tdName = document.createElement('td');
      tdName.className = 'col-name';
      tdName.textContent = student.student_name || '';
      tr.appendChild(tdName);
    }

    function appendCalcCells(tr, student) {
      var fields = [
        { key: 'total_marks', fmt: function (v) { return (v || 0).toFixed(2); } },
        { key: 'total_marks_percentage', fmt: function (v) { return (v || 0).toFixed(2); } },
        { key: 'total_marks_grade_name', fmt: function (v) { return v || ''; } }
      ];
      fields.forEach(function (f) {
        var td = document.createElement('td');
        td.className = 'col-calc';
        td.dataset.field = f.key;
        td.dataset.studentId = student.id;
        td.textContent = f.fmt(student[f.key]);
        tr.appendChild(td);
      });
    }

    function renderRows(payload) {
      currentRows = payload.students || [];
      currentPage = (payload.pagination && payload.pagination.current_page) || 1;
      body.innerHTML = '';

      currentRows.forEach(function (student, idx) {
        var tr = document.createElement('tr');
        tr.dataset.studentId = student.id;
        appendIdentityCells(tr, student, idx + 1);

        var studentMarks = student.marks || {};
        config.columns.forEach(function (column) {
          var markTd = document.createElement('td');
          markTd.className = 'col-mark';
          var input = document.createElement('input');
          input.type = 'text';
          input.inputMode = 'decimal';
          input.className = 'mark-input excel-cell-input';
          input.dataset.studentId = student.id;
          input.dataset.column = column;
          input.setAttribute('data-max', String(columnMax(column)));
          input.autocomplete = 'off';
          input.placeholder = '0';
          var val = studentMarks[column];
          input.value = val === 0 || val === '0' ? '' : (val !== undefined && val !== null ? String(val) : '');
          if (config.readonly) {
            input.readOnly = true;
            input.classList.add('bg-light');
          }
          markTd.appendChild(input);
          tr.appendChild(markTd);
        });

        appendCalcCells(tr, student);
        body.appendChild(tr);
        recalcRow(tr);
      });

      if (pagination) {
        var total = (payload.pagination && payload.pagination.total) || currentRows.length;
        pagination.textContent = total > 0 ? total + ' student(s) loaded' : '';
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

    if (table && !table.__marksExcelBound) {
      table.__marksExcelBound = true;
      table.addEventListener('input', function (e) {
        var inp = e.target.closest('input.mark-input');
        if (!inp) return;
        recalcRow(inp.closest('tr'));
      });
      table.addEventListener('keydown', function (e) {
        var inp = e.target.closest('input.mark-input');
        if (!inp || e.key !== 'Enter') return;
        e.preventDefault();
        var inputs = Array.prototype.slice.call(table.querySelectorAll('input.mark-input'));
        var idx = inputs.indexOf(inp);
        if (idx >= 0 && idx < inputs.length - 1) {
          inputs[idx + 1].focus();
          inputs[idx + 1].select();
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
      body.querySelectorAll('tr').forEach(recalcRow);
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
            showFeedback(
              feedback,
              flattenErrors(pack.data.errors) || humanizeErrorText(pack.data.message) || 'Failed to save.',
              'danger'
            );
          })
          .finally(function () {
            saveBtn.disabled = false;
          });
      });
    }

    var initial = config.initialStudents || { students: [], pagination: {} };
    if (!initial.students || initial.students.length === 0) {
      renderRows(initial);
    } else {
      currentRows = initial.students || [];
      if (pagination) {
        var total = (initial.pagination && initial.pagination.total) || currentRows.length;
        pagination.textContent = total > 0 ? total + ' student(s) loaded' : '';
      }
      if (body) {
        body.querySelectorAll('tr').forEach(recalcRow);
      }
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    initCourseListPage();
    initMarksEntryPage();
  });
})();
