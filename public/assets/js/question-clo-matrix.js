/**
 * Matrix page: Program → Courses; Course → Assessment components dropdowns (AJAX).
 */
(function () {
  function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function fetchJson(url) {
    return fetch(url, {
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
      .catch(function () {
        return { ok: false, data: null };
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('qcm-matrix-form');
    if (!form) {
      return;
    }
    var urlCourses = form.getAttribute('data-url-courses');
    var urlComponents = form.getAttribute('data-url-components');
    var prog = document.querySelector('[data-mx-program]');
    var cours = document.querySelector('[data-mx-course]');
    var comp = document.querySelector('[data-mx-component]');
    if (!urlCourses || !urlComponents || !prog || !cours || !comp) {
      return;
    }

    prog.addEventListener('change', function () {
      var pid = prog.value;
      cours.innerHTML = '';
      comp.innerHTML = '';
      var c0 = document.createElement('option');
      c0.value = '';
      if (!pid) {
        c0.textContent = cours.getAttribute('data-ph-need-program');
        cours.appendChild(c0);
        cours.disabled = true;
        var p0 = document.createElement('option');
        p0.value = '';
        p0.textContent = comp.getAttribute('data-ph-need-course');
        comp.appendChild(p0);
        comp.disabled = true;
        return;
      }
      cours.disabled = true;
      c0.textContent = cours.getAttribute('data-ph-loading');
      cours.appendChild(c0);

      fetchJson(urlCourses.split('__PROGRAM_ID__').join(String(pid))).then(function (res) {
        cours.disabled = false;
        cours.innerHTML = '';
        var ph = document.createElement('option');
        ph.value = '';
        ph.textContent = cours.getAttribute('data-ph-select');
        cours.appendChild(ph);
        if (!res.ok || !Array.isArray(res.data)) {
          if (typeof toastr !== 'undefined') {
            toastr.error(cours.getAttribute('data-ph-error'));
          }
          return;
        }
        res.data.forEach(function (row) {
          var o = document.createElement('option');
          o.value = String(row.id);
          o.textContent = (row.course_code || '') + ' — ' + (row.course_title || '');
          cours.appendChild(o);
        });
      });

      comp.innerHTML = '';
      var ox = document.createElement('option');
      ox.value = '';
      ox.textContent = comp.getAttribute('data-ph-need-course');
      comp.appendChild(ox);
      comp.disabled = true;
    });

    cours.addEventListener('change', function () {
      var cid = cours.value;
      comp.innerHTML = '';
      var o = document.createElement('option');
      o.value = '';
      if (!cid) {
        o.textContent = comp.getAttribute('data-ph-need-course');
        comp.appendChild(o);
        comp.disabled = true;
        return;
      }
      comp.disabled = true;
      o.textContent = comp.getAttribute('data-ph-loading');
      comp.appendChild(o);

      fetchJson(urlComponents.split('__COURSE_ID__').join(String(cid))).then(function (res) {
        comp.disabled = false;
        comp.innerHTML = '';
        var ph = document.createElement('option');
        ph.value = '';
        ph.textContent = comp.getAttribute('data-ph-select') || '';
        comp.appendChild(ph);
        if (!res.ok || !Array.isArray(res.data)) {
          if (typeof toastr !== 'undefined') {
            toastr.error('Could not load components');
          }
          return;
        }
        res.data.forEach(function (row) {
          var opt = document.createElement('option');
          opt.value = String(row.id);
          opt.textContent = (row.component_name || '') + ' (' + String(row.marks || '') + ')';
          comp.appendChild(opt);
        });
      });
    });

    if (!prog.value) {
      cours.disabled = true;
      comp.disabled = true;
    }
  });
})();
