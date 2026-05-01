/**
 * Question–CLO create wizard: mains + parts, JSON POST.
 */
(function () {
  var mainSeq = 0;
  var closList = [];

  function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function escapeHtml(s) {
    var div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
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
        return { ok: res.ok, status: res.status, data: data };
      });
    }).catch(function () {
      return { ok: false, status: 0, data: null };
    });
  }

  function suggestLabel(mainNo, part) {
    var main = String(mainNo || '').trim();
    var p = String(part || '').trim();
    if (!main && !p) return '';
    if (!p) return main;
    if (p.charAt(0) === '(') return main + p;
    return main + p;
  }

  function setBtnLoading(btn, loading) {
    if (!btn) return;
    btn.disabled = !!loading;
    var sp = btn.querySelector('.obe-btn-spinner');
    var lb = btn.querySelector('.obe-btn-label');
    if (sp) sp.classList.toggle('d-none', !loading);
    if (lb) lb.classList.toggle('opacity-50', !!loading);
  }

  function showWizardErrors(box, payload) {
    if (!box) return;
    var errors = payload && payload.errors ? payload.errors : null;
    var message = payload && payload.message ? payload.message : '';
    var parts = [];
    if (errors && typeof errors === 'object') {
      Object.keys(errors).forEach(function (key) {
        var msgs = errors[key];
        if (Array.isArray(msgs)) {
          msgs.forEach(function (t) {
            parts.push(t);
          });
        }
      });
    }
    if (parts.length) {
      box.classList.remove('d-none');
      box.innerHTML = '<ul class="mb-0">' + parts.map(function (t) {
        return '<li>' + escapeHtml(String(t)) + '</li>';
      }).join('') + '</ul>';
      return;
    }
    if (message) {
      box.classList.remove('d-none');
      box.innerHTML = '<div>' + escapeHtml(message) + '</div>';
    }
  }

  function clearWizardErrors(box) {
    if (!box) return;
    box.classList.add('d-none');
    box.innerHTML = '';
  }

  function bloomOptionsInner(wiz) {
    var h = '';
    (wiz.blooms || []).forEach(function (b) {
      h += '<option value="' + b.id + '">' + escapeHtml(b.label) + '</option>';
    });
    return h;
  }

  function statusOptionsInner(wiz, ph) {
    var h = '<option value="">' + escapeHtml(ph) + '</option>';
    (wiz.statuses || []).forEach(function (s) {
      h += '<option value="' + s.id + '">' + escapeHtml(s.status_name) + '</option>';
    });
    return h;
  }

  function cloOptionsInner() {
    var h = '<option value="">CLO</option>';
    closList.forEach(function (c) {
      var t = (c.clo_code || '') + (c.title ? ' — ' + c.title : '');
      h += '<option value="' + c.id + '" data-bloom-id="' + (c.bloom_id || '') + '">' + escapeHtml(t) + '</option>';
    });
    return h;
  }

  function setPartLettersEnabled(card, multi) {
    card.querySelectorAll('[data-part-row]').forEach(function (row) {
      var sel = row.querySelector('[data-field="question_part"]');
      if (!sel) return;
      if (!multi) {
        sel.value = '';
        sel.disabled = true;
      } else {
        sel.disabled = false;
      }
    });
  }

  function updateMainRemaining(card) {
    var capIn = card.querySelector('[data-main-cap]');
    var remEl = card.querySelector('[data-main-remaining]');
    var root = document.getElementById('qcm-wizard-root');
    if (!capIn || !remEl || !root) return;
    var cap = parseFloat(capIn.value) || 0;
    var sum = 0;
    card.querySelectorAll('[data-part-row]').forEach(function (row) {
      var mIn = row.querySelector('[data-field="marks"]');
      sum += parseFloat(mIn && mIn.value ? mIn.value : '0') || 0;
    });
    remEl.style.display = '';
    var tmpl = root.getAttribute('data-ph-remaining') || 'Remaining';
    var left = Math.round((cap - sum) * 100) / 100;
    remEl.textContent = tmpl + ': ' + left;
    remEl.classList.toggle('text-danger', left < -0.001);
    remEl.classList.toggle('text-muted', left >= -0.001 && Math.abs(left) > 0.001);
    remEl.classList.toggle('text-success', Math.abs(left) <= 0.001);
  }

  function bindPartRow(row, wiz, cascade, card) {
    var mainNoIn = card.querySelector('[data-main-no]');

    function syncBloomFromClo() {
      var cloSel = row.querySelector('[data-field="clo_id"]');
      var bloomSel = row.querySelector('[data-field="bloom_id"]');
      var opt = cloSel.selectedOptions[0];
      if (!cloSel.value) {
        bloomSel.value = '';
        return;
      }
      var bid = opt ? opt.getAttribute('data-bloom-id') : '';
      if (bid) bloomSel.value = bid;
      if (cascade.bloomByClo && cloSel.value) {
        var url = cascade.bloomByClo.split('__CLO_ID__').join(String(cloSel.value));
        fetchJson(url).then(function (res) {
          if (res.ok && res.data && res.data.bloom_id) {
            bloomSel.value = String(res.data.bloom_id);
          }
        });
      }
    }

    var cloSel = row.querySelector('[data-field="clo_id"]');
    cloSel.innerHTML = cloOptionsInner();
    var bloomSel = row.querySelector('[data-field="bloom_id"]');
    bloomSel.innerHTML = '<option value="">—</option>' + bloomOptionsInner(wiz);

    cloSel.addEventListener('change', syncBloomFromClo);

    row.querySelector('[data-field="question_part"]').addEventListener('change', function () {
      var lab = row.querySelector('[data-field="question_label"]');
      if (!lab || lab.dataset.userEdited === '1') return;
      lab.value = suggestLabel(mainNoIn.value, row.querySelector('[data-field="question_part"]').value);
    });

    row.querySelector('[data-field="question_label"]').addEventListener('input', function () {
      this.dataset.userEdited = '1';
    });

    row.querySelector('[data-field="marks"]').addEventListener('input', function () {
      updateMainRemaining(card);
    });

    row.querySelector('[data-remove-part]').addEventListener('click', function () {
      row.remove();
      updateMainRemaining(card);
    });

    syncBloomFromClo();
  }

  function syncLabelsForCard(card) {
    var mainNoIn = card.querySelector('[data-main-no]');
    if (!mainNoIn) return;
    card.querySelectorAll('[data-part-row]').forEach(function (row) {
      var lab = row.querySelector('[data-field="question_label"]');
      if (!lab || lab.dataset.userEdited === '1') return;
      var lp = row.querySelector('[data-field="question_part"]').value;
      lab.value = suggestLabel(mainNoIn.value, lp);
    });
  }

  function addPartRow(card, wiz, cascade) {
    var tpl = document.getElementById('qcm-tpl-part');
    if (!tpl) return;
    var row = tpl.content.firstElementChild.cloneNode(true);
    var tbody = card.querySelector('[data-parts-body]');
    tbody.appendChild(row);

    var root = document.getElementById('qcm-wizard-root');
    var stPh = root ? root.getAttribute('data-ph-select-status') : '…';
    row.querySelector('[data-field="status_id"]').innerHTML = statusOptionsInner(wiz, stPh);
    if (wiz.statuses && wiz.statuses[0]) {
      row.querySelector('[data-field="status_id"]').value = String(wiz.statuses[0].id);
    }

    bindPartRow(row, wiz, cascade, card);

    var multi = card.querySelector('[data-main-multi-yes]').checked;
    setPartLettersEnabled(card, multi);
    updateMainRemaining(card);
  }

  function wireMainCard(card, wiz, cascade) {
    mainSeq++;
    var gid = mainSeq;
    card.querySelector('[data-main-multi-yes]').name = 'multi_grp_' + gid;
    card.querySelector('[data-main-multi-no]').name = 'multi_grp_' + gid;

    var yes = card.querySelector('[data-main-multi-yes]');
    var no = card.querySelector('[data-main-multi-no]');
    var addBtn = card.querySelector('[data-add-part]');

    function syncUi() {
      var multi = yes.checked;
      addBtn.style.display = multi ? '' : 'none';
      var tbody = card.querySelector('[data-parts-body]');
      var n = tbody.querySelectorAll('[data-part-row]').length;
      if (!multi && n > 1) {
        while (tbody.querySelectorAll('[data-part-row]').length > 1) {
          tbody.removeChild(tbody.lastElementChild);
        }
      }
      if (!multi && n === 0) {
        addPartRow(card, wiz, cascade);
      }
      setPartLettersEnabled(card, multi);
      updateMainRemaining(card);
    }

    yes.addEventListener('change', function () {
      var tbody = card.querySelector('[data-parts-body]');
      if (yes.checked && tbody.querySelectorAll('[data-part-row]').length === 0) {
        addPartRow(card, wiz, cascade);
      }
      syncUi();
    });
    no.addEventListener('change', syncUi);
    card.querySelector('[data-main-cap]').addEventListener('input', function () {
      updateMainRemaining(card);
    });
    card.querySelector('[data-main-no]').addEventListener('input', function () {
      syncLabelsForCard(card);
    });
    addBtn.addEventListener('click', function () {
      if (card.querySelectorAll('[data-part-row]').length >= 4) return;
      addPartRow(card, wiz, cascade);
    });
    card.querySelector('[data-remove-main]').addEventListener('click', function () {
      card.remove();
    });

    addPartRow(card, wiz, cascade);
    syncUi();
  }

  function buildPayload(progVal, coursVal, acVal) {
    var list = document.getElementById('qcm-main-list');
    var mains = [];
    list.querySelectorAll('[data-main-card]').forEach(function (card) {
      var mainNo = card.querySelector('[data-main-no]').value.trim();
      var cap = parseFloat(card.querySelector('[data-main-cap]').value);
      var multi = card.querySelector('[data-main-multi-yes]').checked;
      var parts = [];
      card.querySelectorAll('[data-part-row]').forEach(function (row) {
        var pq = row.querySelector('[data-field="question_part"]').value || '';
        parts.push({
          question_part: pq === '' ? null : pq.toLowerCase(),
          question_label: row.querySelector('[data-field="question_label"]').value.trim(),
          marks: parseFloat(row.querySelector('[data-field="marks"]').value),
          clo_id: parseInt(row.querySelector('[data-field="clo_id"]').value, 10),
          bloom_id: row.querySelector('[data-field="bloom_id"]').value
            ? parseInt(row.querySelector('[data-field="bloom_id"]').value, 10)
            : null,
          status_id: parseInt(row.querySelector('[data-field="status_id"]').value, 10),
          remarks: null
        });
      });
      mains.push({
        main_question_no: mainNo,
        main_question_marks: cap,
        has_multiple_questions: multi,
        parts: parts
      });
    });
    return {
      program_id: parseInt(progVal, 10),
      course_id: parseInt(coursVal, 10),
      assessment_component_id: parseInt(acVal, 10),
      mains: mains
    };
  }

  function runWizardInit() {
    var root = document.getElementById('qcm-wizard-root');
    var saveBtn = document.getElementById('qcm-wizard-save');
    if (!root || !saveBtn) return;

    var errBox = root.querySelector('[data-qcm-wizard-errors]');
    var jsonEl = document.getElementById('qcm-wizard-json');
    var wiz;
    try {
      wiz = JSON.parse(
        jsonEl ? jsonEl.textContent.trim() : root.getAttribute('data-json')
      );
    } catch (e) {
      console.warn('question-clo-mapping-create-wizard: invalid wizard JSON');
      return;
    }
    wiz.blooms = wiz.blooms || [];
    wiz.statuses = wiz.statuses || [];
    var cascade = wiz.cascade || {};

    clearWizardErrors(errBox);

    var progSel = document.getElementById('wiz_program_id');
    var coursSel = document.getElementById('wiz_course_id');
    var acSel = document.getElementById('wiz_ac_id');
    var capHint = root.querySelector('[data-wiz-component-cap]');

    progSel.addEventListener('change', function () {
      if (!progSel.value) {
        coursSel.innerHTML =
          '<option value="">' + escapeHtml('—') + '</option>';
        coursSel.disabled = true;
        acSel.innerHTML = '<option value="">—</option>';
        acSel.disabled = true;
        closList = [];
        capHint.style.display = 'none';
        return;
      }
      var uc = cascade.courses.split('__PROGRAM_ID__').join(String(progSel.value));
      coursSel.disabled = true;
      fetchJson(uc).then(function (res) {
        coursSel.disabled = false;
        coursSel.innerHTML = '<option value="">—</option>';
        if (res.ok && Array.isArray(res.data)) {
          res.data.forEach(function (c) {
            var o = document.createElement('option');
            o.value = String(c.id);
            o.textContent = (c.course_code || '') + ' — ' + (c.course_title || '');
            coursSel.appendChild(o);
          });
        }
        acSel.innerHTML = '<option value="">—</option>';
        acSel.disabled = true;
        closList = [];
      });
    });

    coursSel.addEventListener('change', function () {
      acSel.disabled = false;
      var uAc = cascade.assessmentComponents.split('__COURSE_ID__').join(String(coursSel.value || '0'));
      fetchJson(uAc).then(function (res) {
        acSel.innerHTML = '<option value="">—</option>';
        if (res.ok && Array.isArray(res.data)) {
          res.data.forEach(function (a) {
            var o = document.createElement('option');
            o.value = String(a.id);
            o.textContent = (a.component_name || '') + ' (' + String(a.marks || '') + ')';
            acSel.appendChild(o);
          });
        }
      });
      fetchJson(cascade.clos.split('__COURSE_ID__').join(String(coursSel.value))).then(function (res) {
        closList = res.ok && Array.isArray(res.data) ? res.data : [];
        document.querySelectorAll('[data-main-card] [data-field="clo_id"]').forEach(function (sel) {
          var was = sel.value;
          sel.innerHTML = cloOptionsInner();
          sel.value = was;
        });
      });
    });

    acSel.addEventListener('change', function () {
      var opt = acSel.selectedOptions[0];
      if (!opt || !acSel.value) {
        capHint.style.display = 'none';
        return;
      }
      var m = /\(([^\)]*)\)$/.exec((opt.textContent || '').trim());
      capHint.style.display = '';
      capHint.textContent = m ? 'Cap: ' + m[1] : '';
    });

    root.querySelector('[data-qcm-add-main]').addEventListener('click', function () {
      var msgStep1 = root.getAttribute('data-err-fill-step1');
      if (!progSel.value || !coursSel.value || !acSel.value) {
        window.alert(msgStep1 || 'Complete step 1.');
        return;
      }
      if (!closList.length) {
        window.alert('CLO list not loaded.');
        return;
      }
      var tplMain = document.getElementById('qcm-tpl-main');
      var card = tplMain.content.firstElementChild.cloneNode(true);
      document.getElementById('qcm-main-list').appendChild(card);
      wireMainCard(card, wiz, cascade);
    });

    saveBtn.addEventListener('click', function () {
      clearWizardErrors(errBox);
      var msgStep1 = root.getAttribute('data-err-fill-step1');
      if (!progSel.value || !coursSel.value || !acSel.value) {
        showWizardErrors(errBox, { message: msgStep1 });
        return;
      }
      var body = buildPayload(progSel.value, coursSel.value, acSel.value);
      if (!body.mains.length) {
        showWizardErrors(errBox, { message: 'Add at least one main question.' });
        return;
      }

      setBtnLoading(saveBtn, true);
      fetch(wiz.storeUrl, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': wiz.csrf || csrfToken()
        },
        body: JSON.stringify(body),
        credentials: 'same-origin'
      })
        .then(function (res) {
          return res.json().then(function (data) {
            return { ok: res.ok, status: res.status, data: data };
          });
        })
        .then(function (result) {
          if (result.ok && result.data && result.data.redirect) {
            window.location.href = result.data.redirect;
            return;
          }
          if (result.status === 422) {
            showWizardErrors(errBox, result.data);
            if (typeof toastr !== 'undefined') {
              toastr.error('Validation failed.');
            }
          } else {
            showWizardErrors(errBox, { message: (result.data && result.data.message) || 'Save failed.' });
          }
        })
        .catch(function () {
          showWizardErrors(errBox, { message: 'Network error.' });
        })
        .finally(function () {
          setBtnLoading(saveBtn, false);
        });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runWizardInit);
  } else {
    runWizardInit();
  }
})();
