/**
 * Matrix page: when program changes, load courses for the dropdown (CLO–PO mapping).
 */
(function () {
  function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('cpm-matrix-form');
    var prog = document.querySelector('[data-matrix-program]');
    var cours = document.querySelector('[data-matrix-course]');
    if (!form || !prog || !cours) {
      return;
    }
    var urlTpl = form.getAttribute('data-url-courses');

    prog.addEventListener('change', function () {
      var pid = prog.value;
      cours.innerHTML = '';
      var o = document.createElement('option');
      o.value = '';
      if (!pid) {
        o.textContent = cours.getAttribute('data-ph-need-program') || '—';
        cours.appendChild(o);
        cours.disabled = true;
        return;
      }
      cours.disabled = true;
      o.textContent = cours.getAttribute('data-ph-loading') || 'Loading…';
      cours.appendChild(o);

      var url = urlTpl.split('__PROGRAM_ID__').join(String(pid));

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
          cours.innerHTML = '';
          cours.disabled = false;
          var placeholder = document.createElement('option');
          placeholder.value = '';
          placeholder.textContent = cours.getAttribute('data-ph-select') || 'Select course';
          cours.appendChild(placeholder);
          if (!result.ok || !Array.isArray(result.data)) {
            var err = cours.getAttribute('data-ph-error') || 'Error';
            if (typeof toastr !== 'undefined') {
              toastr.error(err);
            }
            return;
          }
          result.data.forEach(function (row) {
            var opt = document.createElement('option');
            opt.value = String(row.id);
            opt.textContent = (row.course_code || '') + ' — ' + (row.course_title || '');
            cours.appendChild(opt);
          });
        })
        .catch(function () {
          cours.innerHTML = '';
          cours.disabled = false;
          var e = document.createElement('option');
          e.value = '';
          e.textContent = cours.getAttribute('data-ph-error') || 'Error';
          cours.appendChild(e);
          if (typeof toastr !== 'undefined') {
            toastr.error(cours.getAttribute('data-ph-error') || 'Error');
          }
        });
    });

    if (!prog.value) {
      cours.disabled = true;
    }
  });
})();
