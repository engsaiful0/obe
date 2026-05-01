/**
 * CLO form: when program changes, load courses via AJAX (GET /ajax/program/{id}/courses-for-clo).
 */
(function () {
  function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function populateCourses(selectEl, rows, selectedId) {
    var keep = selectedId ? String(selectedId) : '';
    selectEl.innerHTML = '';
    var emptyPh = selectEl.getAttribute('data-placeholder-empty') || 'Select course';
    var opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = emptyPh;
    selectEl.appendChild(opt0);
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

  function hasPreservedCourseOptions(selectEl) {
    var opts = selectEl.querySelectorAll('option[value]');
    for (var i = 0; i < opts.length; i++) {
      if (opts[i].value !== '') {
        return true;
      }
    }
    return false;
  }

  document.addEventListener('DOMContentLoaded', function () {
    var forms = document.querySelectorAll('form[data-async-no-submit][data-clo-courses-url-pattern]');
    if (!forms.length) {
      return;
    }

    forms.forEach(function (form) {
      var prog = form.querySelector('[data-clo-program-select]');
      var cours = form.querySelector('[data-clo-course-select]');
      var pattern = form.getAttribute('data-clo-courses-url-pattern');
      if (!prog || !cours || !pattern) {
        return;
      }

      var initialProg = prog.value || '';
      var checkedOpt = cours.querySelector('option:checked');
      var initialCourse =
        cours.getAttribute('data-initial-course-id') ||
        (checkedOpt ? checkedOpt.value : '') ||
        '';

      function blankNeedProgram() {
        cours.innerHTML = '';
        var o = document.createElement('option');
        o.value = '';
        o.textContent = cours.getAttribute('data-placeholder-need-program') || 'Select program first';
        cours.appendChild(o);
        cours.disabled = true;
      }

      function fetchCourses(programId, preserveCourseId) {
        if (!programId) {
          blankNeedProgram();
          return;
        }

        cours.disabled = true;
        cours.innerHTML = '';
        var loadOpt = document.createElement('option');
        loadOpt.value = '';
        loadOpt.textContent = cours.getAttribute('data-placeholder-loading') || 'Loading courses…';
        cours.appendChild(loadOpt);

        var url = pattern.split('__PROGRAM_ID__').join(String(programId));

        fetch(url, {
          method: 'GET',
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
            if (!result.ok || !Array.isArray(result.data)) {
              throw new Error('bad');
            }
            populateCourses(cours, result.data, preserveCourseId || '');
            cours.disabled = false;
          })
          .catch(function () {
            cours.innerHTML = '';
            var o = document.createElement('option');
            o.value = '';
            o.textContent = cours.getAttribute('data-placeholder-error') || 'Could not load courses';
            cours.appendChild(o);
            cours.disabled = false;
            if (typeof toastr !== 'undefined') {
              toastr.error('Could not load courses for this program.');
            }
          });
      }

      prog.addEventListener('change', function () {
        fetchCourses(prog.value, '');
      });

      if (!initialProg) {
        blankNeedProgram();
      } else if (hasPreservedCourseOptions(cours)) {
        cours.disabled = false;
      } else {
        fetchCourses(initialProg, initialCourse);
      }
    });
  });
})();
