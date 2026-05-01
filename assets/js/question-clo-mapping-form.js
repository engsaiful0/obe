/**
 * Question–CLO create/edit: Program→Course; Course→components+CLOs; CLO→Bloom.
 */
(function () {
  function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function hasRealOptions(sel) {
    var opts = sel.querySelectorAll('option[value]');
    for (var i = 0; i < opts.length; i++) {
      if (opts[i].value !== '') {
        return true;
      }
    }
    return false;
  }

  function setPlaceholder(sel, text, disabled) {
    sel.innerHTML = '';
    var o = document.createElement('option');
    o.value = '';
    o.textContent = text;
    sel.appendChild(o);
    sel.disabled = !!disabled;
  }

  function fetchJson(url) {
    return fetch(url, {
      headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken()
      },
      credentials: 'same-origin'
    }).then(function (res) {
      return res.json().then(function (data) {
        return { ok: res.ok, data: data };
      });
    }).catch(function () {
      return { ok: false, data: null };
    });
  }

  function populateSelect(sel, rows, formatter, preserveId, placeholder) {
    var keep = preserveId ? String(preserveId) : '';
    sel.innerHTML = '';
    var o0 = document.createElement('option');
    o0.value = '';
    o0.textContent = placeholder || '—';
    sel.appendChild(o0);
    rows.forEach(function (r) {
      var o = document.createElement('option');
      o.value = String(r.id);
      if (typeof r.bloom_id !== 'undefined' && r.bloom_id !== null) {
        o.setAttribute('data-bloom-id', String(r.bloom_id));
      }
      o.textContent = formatter(r);
      if (keep && keep === String(r.id)) {
        o.selected = true;
      }
      sel.appendChild(o);
    });
    sel.disabled = false;
  }

  function applyBloomFromClo(selClo, selBloom, urlTplBloom) {
    var opt = selClo.selectedOptions[0];
    if (!opt || !selClo.value) {
      selBloom.value = '';
      return;
    }
    var bid = opt.getAttribute('data-bloom-id');
    if (!bid || bid === '') {
      selBloom.value = '';
      return;
    }
    selBloom.value = bid;
    if (urlTplBloom && selClo.value) {
      var url = urlTplBloom.split('__CLO_ID__').join(String(selClo.value));
      fetchJson(url).then(function (res) {
        if (res.ok && res.data && res.data.bloom_id) {
          selBloom.value = String(res.data.bloom_id);
        }
      });
    }
  }

  function bindForm(form) {
    var grid = form.querySelector('.qcm-form-grid');
    if (!grid) {
      return;
    }

    var urlCourses = grid.getAttribute('data-url-courses');
    var urlAc = grid.getAttribute('data-url-assessment-components');
    var urlClos = grid.getAttribute('data-url-clos');
    var urlBloom = grid.getAttribute('data-url-bloom-clo');

    var prog = form.querySelector('[data-qcm-program]');
    var cours = form.querySelector('[data-qcm-course]');
    var comp = form.querySelector('[data-qcm-component]');
    var cloSel = form.querySelector('[data-qcm-clo]');
    var bloomSel = form.querySelector('[data-qcm-bloom]');

    if (!prog || !cours || !comp || !cloSel || !bloomSel) {
      return;
    }

    function onProgramChanged() {
      var pid = prog.value;
      if (!pid) {
        setPlaceholder(cours, cours.getAttribute('data-ph-need-program'), true);
        setPlaceholder(comp, comp.getAttribute('data-ph-need-course'), true);
        setPlaceholder(cloSel, cloSel.getAttribute('data-ph-need-course'), true);
        return;
      }
      cours.disabled = true;
      setPlaceholder(cours, cours.getAttribute('data-ph-loading'), true);
      setPlaceholder(comp, comp.getAttribute('data-ph-loading'), true);
      setPlaceholder(cloSel, cloSel.getAttribute('data-ph-loading'), true);

      fetchJson(urlCourses.split('__PROGRAM_ID__').join(String(pid))).then(function (res) {
        cours.disabled = false;
        if (res.ok && Array.isArray(res.data)) {
          populateSelect(cours, res.data, function (x) {
            return (x.course_code || '') + ' — ' + (x.course_title || '');
          }, '', cours.getAttribute('data-ph-select'));
        } else {
          setPlaceholder(cours, cours.getAttribute('data-ph-error'), false);
          if (typeof toastr !== 'undefined') {
            toastr.error(cours.getAttribute('data-ph-error'));
          }
        }
        setPlaceholder(comp, comp.getAttribute('data-ph-need-course'), true);
        setPlaceholder(cloSel, cloSel.getAttribute('data-ph-need-course'), true);
      });
    }

    function onCourseChanged() {
      var cid = cours.value;
      if (!cid) {
        setPlaceholder(comp, comp.getAttribute('data-ph-need-course'), true);
        setPlaceholder(cloSel, cloSel.getAttribute('data-ph-need-course'), true);
        return;
      }

      comp.disabled = true;
      cloSel.disabled = true;
      setPlaceholder(comp, comp.getAttribute('data-ph-loading'), true);
      setPlaceholder(cloSel, cloSel.getAttribute('data-ph-loading'), true);

      var uac = urlAc.split('__COURSE_ID__').join(String(cid));
      var ucl = urlClos.split('__COURSE_ID__').join(String(cid));

      Promise.all([fetchJson(uac), fetchJson(ucl)]).then(function (pairs) {
        var aRes = pairs[0];
        var cRes = pairs[1];
        comp.disabled = false;
        cloSel.disabled = false;

        if (aRes.ok && Array.isArray(aRes.data)) {
          populateSelect(comp, aRes.data, function (x) {
            return (x.component_name || '') + ' (' + String(x.marks || '') + ')';
          }, '', comp.getAttribute('data-ph-select'));
        } else {
          setPlaceholder(comp, comp.getAttribute('data-ph-error'), false);
          if (typeof toastr !== 'undefined') {
            toastr.error(comp.getAttribute('data-ph-error'));
          }
        }

        if (cRes.ok && Array.isArray(cRes.data)) {
          populateSelect(cloSel, cRes.data, function (x) {
            var t = x.title ? ' — ' + x.title : '';
            return (x.clo_code || '') + t;
          }, '', cloSel.getAttribute('data-ph-select'));
          if (cloSel.value) {
            applyBloomFromClo(cloSel, bloomSel, urlBloom);
          }
        } else {
          setPlaceholder(cloSel, cloSel.getAttribute('data-ph-error'), false);
          if (typeof toastr !== 'undefined') {
            toastr.error(cloSel.getAttribute('data-ph-error'));
          }
        }
      });
    }

    prog.addEventListener('change', onProgramChanged);
    cours.addEventListener('change', onCourseChanged);
    cloSel.addEventListener('change', function () {
      applyBloomFromClo(cloSel, bloomSel, urlBloom);
    });

    if (!prog.value) {
      cours.disabled = true;
      comp.disabled = true;
      cloSel.disabled = true;
      return;
    }

    cours.disabled = false;
    var needCourseFetch = !hasRealOptions(cours);
    if (needCourseFetch) {
      onProgramChanged();
    }

    function maybeLoadCourseDeps() {
      if (!cours.value) {
        comp.disabled = true;
        cloSel.disabled = true;
        return;
      }
      if (!hasRealOptions(comp) || !hasRealOptions(cloSel)) {
        onCourseChanged();
      } else {
        comp.disabled = false;
        cloSel.disabled = false;
      }
    }

    setTimeout(function () {
      maybeLoadCourseDeps();
      if (cloSel.value) {
        applyBloomFromClo(cloSel, bloomSel, urlBloom);
      }
    }, 0);

    var mainIn = form.querySelector('[data-qcm-main]');
    var partIn = form.querySelector('[data-qcm-part]');
    var labelIn = form.querySelector('[data-qcm-label]');

    function suggestQuestionLabel(mainVal, partVal) {
      var main = String(mainVal || '').trim();
      var part = String(partVal || '').trim();
      if (!main && !part) {
        return '';
      }
      if (!part) {
        return main;
      }
      if (part.charAt(0) === '(') {
        return main + part;
      }
      return main + part;
    }

    function applySuggestedLabelFromParts() {
      if (!labelIn || !mainIn || !partIn) {
        return;
      }
      if (!labelIn.classList.contains('qcm-label-auto')) {
        return;
      }
      var sug = suggestQuestionLabel(mainIn.value, partIn.value);
      if (sug !== '') {
        labelIn.value = sug;
      }
    }

    if (mainIn && partIn && labelIn) {
      ['input', 'change'].forEach(function (ev) {
        mainIn.addEventListener(ev, applySuggestedLabelFromParts);
        partIn.addEventListener(ev, applySuggestedLabelFromParts);
      });

      labelIn.addEventListener('keydown', function () {
        labelIn.classList.remove('qcm-label-auto');
      });

      labelIn.addEventListener('input', function () {
        if (labelIn.value.trim() !== '') {
          labelIn.classList.remove('qcm-label-auto');
        }
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    ['qcm-create-form', 'qcm-edit-form'].forEach(function (id) {
      var f = document.getElementById(id);
      if (f && f.hasAttribute('data-async-no-submit')) {
        bindForm(f);
      }
    });
  });
})();
