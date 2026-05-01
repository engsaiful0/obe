/**
 * CLO–PO mapping: Program → Courses + PO/PLO; Course → CLOs.
 * URLs use __PROGRAM_ID__ and __COURSE_ID__ placeholders.
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

  function populateOptions(selectEl, rows, placeholder, formatter, preservedId) {
    var keep = preservedId !== undefined && preservedId !== null ? String(preservedId) : '';
    selectEl.innerHTML = '';
    var o0 = document.createElement('option');
    o0.value = '';
    o0.textContent = placeholder || '—';
    selectEl.appendChild(o0);
    rows.forEach(function (r) {
      var o = document.createElement('option');
      o.value = String(r.id);
      o.textContent = formatter(r);
      if (keep && keep === String(r.id)) {
        o.selected = true;
      }
      selectEl.appendChild(o);
    });
  }

  function setLoading(selectEl, loadingText) {
    selectEl.disabled = true;
    selectEl.innerHTML = '';
    var o = document.createElement('option');
    o.value = '';
    o.textContent = loadingText || 'Loading…';
    selectEl.appendChild(o);
  }

  function bindForm(form, grid, urls) {
    var prog = form.querySelector('[data-cpm-program]');
    var cours = form.querySelector('[data-cpm-course]');
    var clo = form.querySelector('[data-cpm-clo]');
    var outcome = form.querySelector('[data-cpm-outcome]');
    var urlCourses = urls.courses;
    var urlOutcomes = urls.outcomes;
    var urlClos = urls.clos;
    var errMsg = cours.getAttribute('data-ph-error') || 'Request failed.';
    var loadMsg = cours.getAttribute('data-ph-loading') || 'Loading…';

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

    function resetClo(reason) {
      clo.innerHTML = '';
      var o = document.createElement('option');
      o.value = '';
      o.textContent = reason || clo.getAttribute('data-ph-need-course') || '—';
      clo.appendChild(o);
      clo.disabled = true;
    }

    function onProgramChanged() {
      var pid = prog.value;
      if (!pid) {
        cours.innerHTML = '';
        var c0 = document.createElement('option');
        c0.value = '';
        c0.textContent = cours.getAttribute('data-ph-need-program') || '—';
        cours.appendChild(c0);
        cours.disabled = true;
        outcome.innerHTML = '';
        var o0b = document.createElement('option');
        o0b.value = '';
        o0b.textContent = cours.getAttribute('data-ph-need-program') || '—';
        outcome.appendChild(o0b);
        outcome.disabled = true;
        resetClo();
        return;
      }

      setLoading(cours, loadMsg);
      setLoading(outcome, outcome.getAttribute('data-ph-loading') || loadMsg);
      resetClo();

      var uc = urlCourses.split('__PROGRAM_ID__').join(String(pid));
      var uo = urlOutcomes.split('__PROGRAM_ID__').join(String(pid));

      Promise.all([fetchJson(uc), fetchJson(uo)]).then(function (pairs) {
        var cRes = pairs[0];
        var oRes = pairs[1];
        cours.disabled = false;
        outcome.disabled = false;

        if (cRes.ok && Array.isArray(cRes.data)) {
          populateOptions(
            cours,
            cRes.data,
            cours.getAttribute('data-ph-select'),
            function (x) {
              return (x.course_code || '') + ' — ' + (x.course_title || '');
            },
            ''
          );
        } else {
          cours.innerHTML = '';
          var ce = document.createElement('option');
          ce.value = '';
          ce.textContent = errMsg;
          cours.appendChild(ce);
          if (typeof toastr !== 'undefined') {
            toastr.error(errMsg);
          }
        }

        if (oRes.ok && Array.isArray(oRes.data)) {
          populateOptions(
            outcome,
            oRes.data,
            outcome.getAttribute('data-ph-select'),
            function (x) {
              var t = x.title ? ' — ' + x.title : '';
              return (x.outcome_code || '') + t;
            },
            ''
          );
        } else {
          outcome.innerHTML = '';
          var oe = document.createElement('option');
          oe.value = '';
          oe.textContent = errMsg;
          outcome.appendChild(oe);
          if (typeof toastr !== 'undefined') {
            toastr.error(errMsg);
          }
        }
      });
    }

    function onCourseChanged() {
      var cid = cours.value;
      if (!cid) {
        resetClo();
        return;
      }
      setLoading(clo, clo.getAttribute('data-ph-loading') || loadMsg);
      var u = urlClos.split('__COURSE_ID__').join(String(cid));
      fetchJson(u).then(function (res) {
        clo.disabled = false;
        if (res.ok && Array.isArray(res.data)) {
          populateOptions(
            clo,
            res.data,
            'Select CLO',
            function (x) {
              var t = x.title ? ' — ' + x.title : '';
              return (x.clo_code || '') + t;
            },
            ''
          );
          return;
        }
        clo.innerHTML = '';
        var e = document.createElement('option');
        e.value = '';
        e.textContent = errMsg;
        clo.appendChild(e);
        if (typeof toastr !== 'undefined') {
          toastr.error(errMsg);
        }
      });
    }

    prog.addEventListener('change', onProgramChanged);
    cours.addEventListener('change', onCourseChanged);

    if (!prog.value) {
      cours.disabled = true;
      outcome.disabled = true;
      resetClo();
      return;
    }

    cours.disabled = false;
    outcome.disabled = false;

    var hasCourses = hasValueOptions(cours);
    var hasOutcomes = hasValueOptions(outcome);
    var hasClosOpts = hasValueOptions(clo);

    if (!hasCourses || !hasOutcomes) {
      onProgramChanged();
    } else if (cours.value && !hasClosOpts) {
      onCourseChanged();
    } else if (cours.value) {
      clo.disabled = false;
    } else {
      resetClo();
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    var ids = ['cpm-create-form', 'cpm-edit-form'];
    ids.forEach(function (fid) {
      var form = document.getElementById(fid);
      if (!form || !form.hasAttribute('data-async-no-submit')) {
        return;
      }
      var grid = form.querySelector('.cpm-form-grid');
      if (!grid) {
        return;
      }
      var urls = {
        courses: grid.getAttribute('data-url-courses'),
        outcomes: grid.getAttribute('data-url-outcomes'),
        clos: grid.getAttribute('data-url-clos')
      };
      if (urls.courses && urls.outcomes && urls.clos) {
        bindForm(form, grid, urls);
      }
    });
  });
})();
