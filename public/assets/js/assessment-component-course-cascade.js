/**
 * Assessment setup: Program → courses (GET /ajax/assessment-components/program/{id}/courses).
 */
(function () {
  function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function hasValueOptions(selectEl) {
    var opts = selectEl.querySelectorAll('option[value]');
    for (var i = 0; i < opts.length; i++) {
      if (opts[i].value !== '') {
        return true;
      }
    }
    return false;
  }

  function populateCourses(selectEl, rows, placeholder, selectedId) {
    var keep = selectedId !== undefined && selectedId !== null ? String(selectedId) : '';
    selectEl.innerHTML = '';
    var o0 = document.createElement('option');
    o0.value = '';
    o0.textContent = placeholder || '—';
    selectEl.appendChild(o0);
    rows.forEach(function (r) {
      var o = document.createElement('option');
      o.value = String(r.id);
      o.textContent = (r.course_code || '') + ' — ' + (r.course_title || '');
      if (keep && keep === String(r.id)) {
        o.selected = true;
      }
      selectEl.appendChild(o);
    });
  }

  function bindForm(form, grid) {
    var urlTpl = grid.getAttribute('data-url-courses');
    var prog = form.querySelector('[data-ac-program]');
    var cours = form.querySelector('[data-ac-course]');
    if (!urlTpl || !prog || !cours) {
      return;
    }

    var errMsg = cours.getAttribute('data-ph-error') || 'Error';
    var loadMsg = cours.getAttribute('data-ph-loading') || 'Loading…';

    function fetchCourses(programId, preserveCourseId) {
      if (!programId) {
        cours.innerHTML = '';
        var o = document.createElement('option');
        o.value = '';
        o.textContent = cours.getAttribute('data-ph-need-program') || '—';
        cours.appendChild(o);
        cours.disabled = true;
        return;
      }

      cours.disabled = true;
      cours.innerHTML = '';
      var lo = document.createElement('option');
      lo.value = '';
      lo.textContent = loadMsg;
      cours.appendChild(lo);

      var url = urlTpl.split('__PROGRAM_ID__').join(String(programId));

      fetch(url, {
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken()
        },
        credentials: 'same-origin'
      })
        .then(function (res) {
          return res.json().then(function (data) {
            return { ok: res.ok, data: data };
          });
        })
        .then(function (result) {
          cours.disabled = false;
          if (!result.ok || !Array.isArray(result.data)) {
            cours.innerHTML = '';
            var e = document.createElement('option');
            e.value = '';
            e.textContent = errMsg;
            cours.appendChild(e);
            if (typeof toastr !== 'undefined') {
              toastr.error(errMsg);
            }
            return;
          }
          populateCourses(
            cours,
            result.data,
            cours.getAttribute('data-ph-select'),
            preserveCourseId || ''
          );
        })
        .catch(function () {
          cours.disabled = false;
          cours.innerHTML = '';
          var e = document.createElement('option');
          e.value = '';
          e.textContent = errMsg;
          cours.appendChild(e);
          if (typeof toastr !== 'undefined') {
            toastr.error(errMsg);
          }
        });
    }

    prog.addEventListener('change', function () {
      fetchCourses(prog.value, '');
    });

    var checkedOpt = cours.querySelector('option:checked');
    var initialCourse =
      cours.getAttribute('data-initial-course-id') ||
      (checkedOpt ? checkedOpt.value : '') ||
      '';

    if (!prog.value) {
      cours.disabled = true;
    } else if (!hasValueOptions(cours)) {
      fetchCourses(prog.value, initialCourse);
    } else {
      cours.disabled = false;
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    ['ac-create-form', 'ac-edit-form'].forEach(function (fid) {
      var form = document.getElementById(fid);
      if (!form || !form.hasAttribute('data-async-no-submit')) {
        return;
      }
      var grid = form.querySelector('.ac-course-grid');
      if (!grid || !grid.getAttribute('data-url-courses')) {
        return;
      }
      bindForm(form, grid);
    });
  });
})();
