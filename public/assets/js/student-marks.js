/**
 * Student marks: index AJAX filters, bulk grid + save/import/reset, single-student wizard.
 */
(function () {
  function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function setBtnLoading(btn, on) {
    if (!btn) return;
    btn.disabled = !!on;
    var spin = btn.querySelector('.obe-btn-spinner');
    var label = btn.querySelector('.obe-btn-label');
    if (spin) spin.classList.toggle('d-none', !on);
    if (label) label.classList.toggle('opacity-50', !!on);
  }

  function buildQuery(form) {
    var fd = new FormData(form);
    var params = [];
    fd.forEach(function (val, key) {
      if (key === 'page') return;
      if (val === null || String(val).trim() === '') return;
      params.push(encodeURIComponent(key) + '=' + encodeURIComponent(val));
    });
    return params.join('&');
  }

  function replaceUrlTpl(tpl, map) {
    var s = String(tpl || '');
    Object.keys(map).forEach(function (k) {
      s = s.split(k).join(String(map[k]));
    });
    return s;
  }

  /** @param {boolean|string|null|false} toastIfError — pass false to skip toastr */
  function fetchJson(url, opts, toastIfError) {
    return fetch(url, opts)
      .then(function (res) {
        return res.json().then(function (data) {
          return { ok: res.ok, status: res.status, data: data };
        }).catch(function () {
          return { ok: false, status: res.status || 0, data: { message: 'Invalid JSON.' } };
        });
      })
      .then(function (pack) {
        if (!pack.ok && toastIfError !== false && typeof toastr !== 'undefined') {
          var msg =
            typeof toastIfError === 'string' && toastIfError
              ? toastIfError
              : (pack.data && pack.data.message) || 'Request failed.';
          toastr.error(msg);
        }
        return pack;
      });
  }

  function confirmSwal(message, opts) {
    opts = opts || {};
    if (typeof Swal !== 'undefined' && Swal.fire) {
      return Swal.fire({
        title: opts.title || '',
        text: message,
        icon: opts.icon || 'warning',
        showCancelButton: true,
        confirmButtonText: opts.confirmText || 'Yes',
        cancelButtonText: opts.cancelText || 'Cancel',
        focusCancel: true,
        reverseButtons: true,
        buttonsStyling: false,
        customClass: {
          confirmButton: 'btn btn-danger me-2',
          cancelButton: 'btn btn-label-secondary'
        }
      }).then(function (r) {
        return !!r.isConfirmed;
      });
    }
    return Promise.resolve(window.confirm(message));
  }

  function clearSelect(sel, placeholder) {
    if (!sel) return;
    sel.innerHTML = '';
    var o = document.createElement('option');
    o.value = '';
    o.textContent = placeholder;
    sel.appendChild(o);
  }

  function fillSelect(sel, items, labelFn, extraOpt) {
    if (!sel) return;
    var ph = sel.getAttribute('data-placeholder') || 'Select';
    clearSelect(sel, ph);
    (items || []).forEach(function (it) {
      var o = document.createElement('option');
      o.value = it.id;
      o.textContent = labelFn(it);
      if (extraOpt) extraOpt(o, it);
      sel.appendChild(o);
    });
    sel.disabled = !(items || []).length;
  }

  function clampNum(v, max) {
    if (!isFinite(v)) return 0;
    if (!isFinite(max)) return v;
    return Math.min(v, max);
  }

  /** --- INDEX --- */
  function initIndex() {
    var form = document.getElementById('sm-index-filter-form');
    var inner = document.getElementById('student-marks-table-inner');
    var overlay = document.getElementById('sm-loading-overlay');
    if (!form || !inner) return;

    var baseUrl = form.getAttribute('action').split('?')[0];

    function overlayOn(on) {
      if (overlay) overlay.classList.toggle('d-none', !on);
    }

    function loadTable(urlStr) {
      overlayOn(true);
      fetch(urlStr, {
        method: 'GET',
        headers: {
          Accept: 'text/html',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken()
        },
        credentials: 'same-origin'
      })
        .then(function (res) {
          if (!res.ok) throw new Error('bad status');
          return res.text();
        })
        .then(function (html) {
          inner.innerHTML = html;
          if (history && history.pushState) {
            try {
              var u = new URL(urlStr, window.location.href);
              history.pushState({}, '', u.pathname + u.search);
            } catch (e2) {}
          }
        })
        .catch(function () {
          if (typeof toastr !== 'undefined') toastr.error('Failed to load filtered results.');
        })
        .finally(function () {
          overlayOn(false);
        });
    }

    function loadFromFilters(resetPage) {
      var qs = buildQuery(form);
      if (resetPage) qs = qs.replace(/(^|&)page=\d+/g, '');
      var url = qs ? baseUrl + '?' + qs : baseUrl;
      loadTable(url);
    }

    var debTimer;
    function debounced() {
      clearTimeout(debTimer);
      debTimer = setTimeout(function () {
        loadFromFilters(true);
      }, 420);
    }

    form.querySelectorAll('.sm-index-filter').forEach(function (el) {
      el.addEventListener('change', function () {
        loadFromFilters(true);
      });
    });
    form.querySelectorAll('input[name="q"]').forEach(function (el) {
      el.addEventListener('input', debounced);
      el.addEventListener('change', debounced);
    });

    inner.addEventListener('click', function (e) {
      var link = e.target.closest('.sm-pagination a');
      if (link && inner.contains(link)) {
        e.preventDefault();
        loadTable(link.href);
      }
    });
  }

  /** --- SHARED CASCADE --- */
  function wireCascade(scope, cascade, opts) {
    opts = opts || {};
    var skipAssessmentComponents = !!opts.skipAssessmentComponents;
    var skipBatchAndSection = !!opts.skipBatchAndSection;
    if (!scope || !cascade) return {};

    var elProg = scope.querySelector('#sm_prog');
    var elCourse = scope.querySelector('#sm_course');
    var elBatch = scope.querySelector('#sm_batch');
    var elSection = scope.querySelector('#sm_section');
    var elComp = scope.querySelector('#sm_comp');

    function onProgramChange() {
      var pid = elProg ? elProg.value : '';
      var sectionPlaceholder =
        elSection && elSection.id === 'sm_section' ? 'All / not specified' : 'Optional';
      if (!pid) {
        clearSelect(elCourse, 'Select');
        clearSelect(elBatch, 'Select');
        clearSelect(elSection, sectionPlaceholder);
        clearSelect(elComp, 'Select');
        [elCourse, elBatch, elSection, elComp].forEach(function (x) {
          if (x) x.disabled = true;
        });
        return Promise.resolve(null);
      }
      /* Reset dependents immediately so FormData/stale selections cannot mismatch the program. */
      clearSelect(elCourse, 'Select');
      clearSelect(elBatch, 'Select');
      clearSelect(elSection, sectionPlaceholder);
      if (elCourse) elCourse.disabled = true;
      if (elBatch) elBatch.disabled = true;
      if (elSection) elSection.disabled = true;
      clearSelect(elComp, 'Select');
      if (elComp) elComp.disabled = true;

      var promises = [];
      promises.push(
        fetchJson(
          replaceUrlTpl(cascade.courses || '', { '__PROGRAM_ID__': pid }),
          { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }, credentials: 'same-origin' },
          false
        ).then(function (pack) {
          if (!pack || !pack.ok || !pack.data) return [];
          var items = pack.data.items || pack.data;
          fillSelect(elCourse, items, function (c) {
            return c.course_code ? c.course_code + ' — ' + (c.course_title || '') : c.label || '';
          });
        })
      );
      promises.push(
        fetchJson(
          replaceUrlTpl(cascade.batches || '', { '__PROGRAM_ID__': pid }),
          { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }, credentials: 'same-origin' },
          ''
        ).then(function (pack) {
          if (!pack || !pack.ok || !pack.data) return [];
          var items = pack.data.items || pack.data;
          fillSelect(elBatch, items, function (b) {
            return b.label || b.batch_name || '';
          });
        })
      );

      return Promise.all(promises).then(function () {
        if (typeof window.__studentMarksSyncCreateLoadBtn === 'function') {
          window.__studentMarksSyncCreateLoadBtn();
        }
        if (typeof window.__studentMarksSyncBulkLoadBtn === 'function') {
          window.__studentMarksSyncBulkLoadBtn();
        }
      });
    }

    function onBatchChange() {
      var pid = elProg ? elProg.value : '';
      var bid = elBatch ? elBatch.value : '';
      if (!pid || !bid) {
        if (elSection) {
          clearSelect(elSection, 'All / not specified');
          elSection.disabled = true;
        }
        return Promise.resolve();
      }
      return fetchJson(
        replaceUrlTpl(cascade.sections || '', { '__BATCH_ID__': bid }),
        { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }, credentials: 'same-origin' },
        false
      ).then(function (pack) {
        if (!pack || !pack.ok || !pack.data) return;
        var items = pack.data.items || pack.data;
        if (elSection) {
          elSection.disabled = false;
          clearSelect(elSection, elSection.id === 'sm_section' ? 'All / not specified' : 'Optional');
          items.forEach(function (s) {
            var o = document.createElement('option');
            o.value = s.id;
            o.textContent = s.label || s.section_name || '';
            elSection.appendChild(o);
          });
        }
        if (typeof window.__studentMarksSyncBulkLoadBtn === 'function') {
          window.__studentMarksSyncBulkLoadBtn();
        }
      });
    }

    function onCourseChange() {
      if (skipAssessmentComponents) {
        if (typeof window.__studentMarksSyncBulkLoadBtn === 'function') {
          window.__studentMarksSyncBulkLoadBtn();
        }
        return Promise.resolve();
      }
      var cid = elCourse ? elCourse.value : '';
      if (!cid) {
        clearSelect(elComp, 'Select');
        if (elComp) elComp.disabled = true;
        if (typeof window.__studentMarksSyncBulkLoadBtn === 'function') {
          window.__studentMarksSyncBulkLoadBtn();
        }
        return Promise.resolve();
      }
      return fetchJson(
        replaceUrlTpl(cascade.assessmentComponents || '', { '__COURSE_ID__': cid }),
        { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }, credentials: 'same-origin' },
        false
      ).then(function (pack) {
        if (!pack || !pack.ok || !pack.data) return;
        if (!elComp) return;
        fillSelect(elComp, pack.data, function (ac) {
          var m = typeof ac.marks !== 'undefined' ? ' (max ' + ac.marks + ')' : '';
          return (ac.component_name || ac.label || '') + m;
        });
        elComp.disabled = false;
        if (typeof window.__studentMarksSyncBulkLoadBtn === 'function') {
          window.__studentMarksSyncBulkLoadBtn();
        }
      });
    }

    if (elProg) {
      elProg.addEventListener('change', function () {
        onProgramChange();
      });
    }
    if (elBatch) {
      elBatch.addEventListener('change', function () {
        onBatchChange();
      });
    }
    if (elCourse) {
      elCourse.addEventListener('change', function () {
        onCourseChange();
      });
    }

    return {
      reloadProgram: function () {
        return onProgramChange();
      },
      reloadBatchSections: function () {
        return onBatchChange();
      },
      reloadComponents: function () {
        return onCourseChange();
      }
    };
  }

  /** --- BULK --- */
  function initBulk(routes) {
    routes = routes || {};
    var cascade = window.__studentMarksCascade || {};

    var form = document.getElementById('sm-bulk-filters');
    if (!form || !routes.bulkSave) return;

    wireCascade(form, cascade, {
      skipAssessmentComponents: true,
      skipBatchAndSection: true
    });

    var qCourseUrl = routes.questionsByCourseApi || routes.questionsApi;

    var btnLoad = document.getElementById('sm-load-grid');
    var btnSave = document.getElementById('sm-bulk-save');
    var btnTemplate = document.getElementById('sm-download-template');
    var btnReset = document.getElementById('sm-reset');
    var wrap = document.getElementById('sm-matrix-wrap');
    var tbl = document.getElementById('sm-matrix-table');
    var feedback = document.getElementById('sm-bulk-feedback');
    var btnImport = document.getElementById('sm-import-submit');
    var importErrors = document.getElementById('sm-import-errors');
    var formImport = document.getElementById('sm-import-form');

    /** @type {Array<Record<string, mixed>>} */
    var lastStudents = [];
    /** @type {Array<{ id: number, question_label: string, marks: number, assessment_component_id: number }>} */
    var lastQuestions = [];

    function readFilters() {
      var fd = new FormData(form);
      function pick(name) {
        var v = fd.get(name);
        return v === null || String(v).trim() === '' ? '' : String(v).trim();
      }
      return {
        academic_session_id: pick('academic_session_id'),
        program_id: pick('program_id'),
        course_id: pick('course_id')
      };
    }

    /** @param {Record<string, string>} f */
    function setContextSearchParams(params, f) {
      params.set('academic_session_id', f.academic_session_id);
      params.set('program_id', f.program_id);
      params.set('course_id', f.course_id);
    }

    function flattenQuestionsFromComponents(components) {
      /** @type {Array<{ id: number, question_label: string, marks: number, assessment_component_id: number }>} */
      var out = [];
      (components || []).forEach(function (c) {
        (c.questions || []).forEach(function (q) {
          var cname = String(c.component_name || '');
          out.push({
            id: q.id,
            question_label: cname ? cname + ' · ' + q.question_label : q.question_label,
            marks: q.marks,
            assessment_component_id: c.id
          });
        });
      });
      return out;
    }

    function escapeHtml(s) {
      return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function syncBulkFilterButtons() {
      var f = readFilters();
      var hasSessionProgramCourse = !!(
        f.academic_session_id &&
        f.program_id &&
        f.course_id
      );
      if (btnLoad) {
        btnLoad.disabled = !(hasSessionProgramCourse);
      }
      if (btnTemplate) {
        btnTemplate.disabled = !hasSessionProgramCourse;
      }
    }

    window.__studentMarksSyncBulkLoadBtn = syncBulkFilterButtons;
    form.querySelectorAll('select').forEach(function (sel) {
      sel.addEventListener('change', syncBulkFilterButtons);
    });
    syncBulkFilterButtons();

    function showFeedback(msgOrNull) {
      if (!feedback) return;
      if (!msgOrNull) {
        feedback.classList.add('d-none');
        feedback.textContent = '';
        return;
      }
      feedback.classList.remove('d-none');
      feedback.innerHTML =
        typeof msgOrNull === 'string' ? '<div>' + msgOrNull + '</div>' : JSON.stringify(msgOrNull);
    }

    function recalcRow(tr) {
      var sum = 0;
      tr.querySelectorAll('input.sm-qpart').forEach(function (inp) {
        var max = parseFloat(inp.getAttribute('data-max'));
        var v = parseFloat(String(inp.value).replace(',', '.'));
        if (!isFinite(v)) v = 0;
        v = clampNum(v, isFinite(max) ? max : Infinity);
        if (isFinite(max) && v >= max - 1e-6) inp.classList.add('border-danger');
        else inp.classList.remove('border-danger');
        sum += v;
      });
      sum = Math.round(sum * 100) / 100;
      var tot = tr.querySelector('input.sm-total');
      if (tot) {
        tot.value = String(sum);
        tot.classList.remove('is-invalid');
      }
    }

    if (tbl && !tbl.__smMarkDelegation) {
      tbl.__smMarkDelegation = true;
      tbl.addEventListener('input', function (e) {
        var inp = e.target.closest('input.sm-qpart');
        if (!inp || !tbl.contains(inp)) return;
        recalcRow(inp.closest('tr'));
      });
      tbl.addEventListener('change', function (e) {
        var inp = e.target.closest('input.sm-qpart');
        if (!inp || !tbl.contains(inp)) return;
        recalcRow(inp.closest('tr'));
      });
    }

    function existingQuestionMark(st, q) {
      var byComp = st.existing_by_component;
      if (byComp) {
        var block = byComp[String(q.assessment_component_id)];
        if (block && block.question_marks) {
          var v = block.question_marks[String(q.id)];
          if (typeof v !== 'undefined' && v !== null) return v;
        }
      }
      if (st.existing && st.existing.question_marks) {
        var v2 = st.existing.question_marks[String(q.id)];
        if (typeof v2 !== 'undefined' && v2 !== null) return v2;
      }
      return undefined;
    }

    function existingGrandTotal(st) {
      var byComp = st.existing_by_component;
      if (byComp) {
        var sum = 0;
        var any = false;
        Object.keys(byComp).forEach(function (k) {
          var eb = byComp[k];
          if (eb && typeof eb.total_marks !== 'undefined' && eb.total_marks !== null) {
            sum += eb.total_marks;
            any = true;
          }
        });
        if (any) return sum;
      }
      if (st.existing && typeof st.existing.total_marks !== 'undefined' && st.existing.total_marks !== null) {
        return st.existing.total_marks;
      }
      return null;
    }

    function buildMatrix(students, questions) {
      lastStudents = students;
      lastQuestions = questions;

      var thead = tbl.querySelector('thead');
      var tbody = tbl.querySelector('tbody');
      thead.innerHTML = '';
      tbody.innerHTML = '';

      var trh = document.createElement('tr');
      trh.appendChild(document.createElement('th')).textContent = 'Student';
      var thAtt = document.createElement('th');
      thAtt.textContent = 'Attendance marks';
      trh.appendChild(thAtt);
      questions.forEach(function (q) {
        var th = document.createElement('th');
        th.textContent = q.question_label + ' (' + q.marks + ')';
        th.setAttribute('data-component-id', String(q.assessment_component_id));
        trh.appendChild(th);
      });
      trh.appendChild(document.createElement('th')).textContent = 'Total';
      thead.appendChild(trh);

      students.forEach(function (st) {
        var tr = document.createElement('tr');
        var nm = document.createElement('td');
        nm.innerHTML =
          '<div class="small fw-semibold">' +
          escapeHtml(st.student_code || '') +
          '</div><div class="text-muted small">' +
          escapeHtml(st.student_name || '') +
          '</div>';
        tr.appendChild(nm);

        var tdAtt = document.createElement('td');
        var inpAtt = document.createElement('input');
        inpAtt.type = 'text';
        inpAtt.inputMode = 'decimal';
        inpAtt.className = 'form-control form-control-sm sm-attendance';
        inpAtt.setAttribute('data-student-id', String(st.id));
        if (typeof st.attendance_marks !== 'undefined' && st.attendance_marks !== null && st.attendance_marks !== '') {
          inpAtt.value = String(st.attendance_marks);
        }
        tdAtt.appendChild(inpAtt);
        tr.appendChild(tdAtt);

        questions.forEach(function (q) {
          var td = document.createElement('td');
          var inp = document.createElement('input');
          inp.type = 'text';
          inp.inputMode = 'decimal';
          inp.className = 'form-control form-control-sm sm-qpart';
          inp.dataset.studentId = String(st.id);
          inp.dataset.mapId = String(q.id);
          inp.dataset.componentId = String(q.assessment_component_id);
          inp.setAttribute('data-max', String(q.marks));
          var ex = existingQuestionMark(st, q);
          if (typeof ex !== 'undefined' && ex !== null) inp.value = String(ex);
          else inp.value = '0';
          td.appendChild(inp);
          tr.appendChild(td);
        });

        var tdT = document.createElement('td');
        var inpT = document.createElement('input');
        inpT.type = 'text';
        inpT.readOnly = true;
        inpT.className = 'form-control form-control-sm sm-total';
        inpT.dataset.studentId = String(st.id);
        var exTot = existingGrandTotal(st);
        if (exTot !== null && typeof exTot !== 'undefined') inpT.value = String(exTot);
        tdT.appendChild(inpT);
        tr.appendChild(tdT);

        tbody.appendChild(tr);
        recalcRow(tr);
      });

      wrap.classList.remove('d-none');
      btnSave.disabled = !students.length || !questions.length;
    }

    if (btnLoad && qCourseUrl) {
      btnLoad.addEventListener('click', function () {
        var f = readFilters();
        if (!f.academic_session_id || !f.program_id || !f.course_id) {
          showFeedback('Fill session, program, course.');
          return;
        }
        showFeedback(null);
        setBtnLoading(btnLoad, true);

        var pQs = new URLSearchParams();
        setContextSearchParams(pQs, f);
        var stQs = new URLSearchParams(pQs.toString());
        stQs.set('with_marks', '1');
        stQs.set('all_components', '1');

        Promise.all([
          fetchJson(qCourseUrl + '?' + pQs.toString(), {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }
          }, false),
          fetchJson(routes.studentsApi + '?' + stQs.toString(), {
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }
          }, false)
        ])
          .then(function (pairs) {
            var qPack = pairs[0];
            var stPack = pairs[1];
            if (!qPack.ok || !stPack.ok) return;
            var components = qPack.data.components || [];
            var questions = flattenQuestionsFromComponents(components);
            if (!questions.length) {
              showFeedback('No question parts mapped for any component in this session and section.');
              wrap.classList.add('d-none');
              btnSave.disabled = true;
              return;
            }
            buildMatrix(stPack.data.students || [], questions);
          })
          .finally(function () {
            setBtnLoading(btnLoad, false);
          });
      });
    }

    if (btnSave) {
      btnSave.addEventListener('click', function () {
        var f = readFilters();
        if (!lastQuestions.length || !lastStudents.length) {
          return;
        }
        var rowsPayload = [];

        tbl.querySelector('tbody').querySelectorAll('tr').forEach(function (tr) {
          var sidEl = tr.querySelector('input.sm-attendance') || tr.querySelector('input.sm-qpart');
          if (!sidEl) return;
          var sid = parseInt(sidEl.getAttribute('data-student-id'), 10);

          /** @type {Record<number, { assessment_component_id: number, questions: Array<{ question_clo_mapping_id: number, obtained_marks: number }> }>} */
          var byComp = {};
          tr.querySelectorAll('input.sm-qpart').forEach(function (inp) {
            var cid = parseInt(inp.getAttribute('data-component-id'), 10);
            var mapId = parseInt(inp.getAttribute('data-map-id'), 10);
            var obt = parseFloat(String(inp.value).replace(',', '.'));
            if (!isFinite(obt)) obt = 0;
            if (!byComp[cid]) {
              byComp[cid] = { assessment_component_id: cid, questions: [] };
            }
            byComp[cid].questions.push({
              question_clo_mapping_id: mapId,
              obtained_marks: obt
            });
          });

          var blocks = Object.keys(byComp).map(function (k) {
            var qsArr = byComp[k].questions;
            var tm = qsArr.reduce(function (a, q) {
              return a + q.obtained_marks;
            }, 0);
            tm = Math.round(tm * 100) / 100;
            return {
              assessment_component_id: parseInt(k, 10),
              total_marks: tm,
              questions: qsArr
            };
          });
          var attInp = tr.querySelector('input.sm-attendance');
          var rowObj = { student_id: sid, component_marks: blocks };
          if (attInp) {
            var attStr = String(attInp.value).replace(',', '.').trim();
            rowObj.attendance_marks =
              attStr === '' || !isFinite(parseFloat(attStr)) ? null : Math.round(parseFloat(attStr) * 100) / 100;
          }
          rowsPayload.push(rowObj);
        });

        showFeedback(null);
        setBtnLoading(btnSave, true);

        fetch(routes.bulkSave, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            _token: csrfToken(),
            academic_session_id: f.academic_session_id,
            program_id: f.program_id,
            course_id: f.course_id,
            rows: rowsPayload
          })
        })
          .then(function (res) {
            return res.json().then(function (data) {
              return { ok: res.ok, data: data, status: res.status };
            });
          })
          .then(function (res) {
            if (res.ok) {
              if (typeof toastr !== 'undefined' && res.data.message) {
                toastr.success(res.data.message);
              }
              if (btnLoad) btnLoad.click();

              return;
            }
            if (res.status === 422 && res.data.errors && typeof res.data.errors === 'object') {
              var parts = [];
              Object.keys(res.data.errors).forEach(function (key) {
                var msgs = res.data.errors[key];
                if (Array.isArray(msgs)) {
                  msgs.forEach(function (m) {
                    parts.push(m);
                  });
                }
              });
              showFeedback(parts.length ? '<ul class="mb-0">' + parts.map(function (p) {
                return '<li>' + escapeHtml(p) + '</li>';
              }).join('') + '</ul>' : res.data.message || 'Validation failed.');
            } else if (typeof toastr !== 'undefined') {
              toastr.error((res.data && res.data.message) || 'Save failed.');
            }
          })
          .catch(function () {
            if (typeof toastr !== 'undefined') toastr.error('Network error.');
          })
          .finally(function () {
            setBtnLoading(btnSave, false);
          });
      });
    }

    if (btnTemplate) {
      btnTemplate.addEventListener('click', function () {
        var f = readFilters();
        if (!f.academic_session_id || !f.program_id || !f.course_id) {
          if (typeof toastr !== 'undefined') toastr.error('Select academic session, program, and course first.');
          return;
        }
        try {
          var u = new URL(routes.template, window.location.origin);
          setContextSearchParams(u.searchParams, f);
          u.searchParams.set('bulk_all', '1');
          window.location.href = u.pathname + u.search;
        } catch (eURL) {
          var p = new URLSearchParams();
          setContextSearchParams(p, f);
          p.set('bulk_all', '1');
          var sep = String(routes.template).indexOf('?') === -1 ? '?' : '&';
          window.location.href = routes.template + sep + p.toString();
        }
      });
    }

    if (btnReset && routes.reset) {
      btnReset.addEventListener('click', function () {
        var f = readFilters();
        if (!f.academic_session_id || !f.program_id || !f.course_id) {
          if (typeof toastr !== 'undefined') toastr.error('Select filters first.');
          return;
        }

        confirmSwal(
          'Delete all marks for every assessment component under this academic session, program, and course?',
          { confirmText: 'Yes, reset', cancelText: 'Cancel', icon: 'warning' }
        ).then(function (ok) {
          if (!ok) return;
          setBtnLoading(btnReset, true);

          fetch(routes.reset, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
              Accept: 'application/json',
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken(),
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
              _token: csrfToken(),
              bulk_all: true,
              academic_session_id: f.academic_session_id,
              program_id: f.program_id,
              course_id: f.course_id
            })
          })
            .then(function (res) {
              return res.json().then(function (data) {
                return { ok: res.ok, data: data };
              });
            })
            .then(function (pack) {
              if (pack.ok && typeof toastr !== 'undefined' && pack.data.message) {
                toastr.success(pack.data.message);
              }
              if (!pack.ok && typeof toastr !== 'undefined') {
                toastr.error((pack.data && pack.data.message) || 'Reset failed.');
              }
            })
            .catch(function () {
              if (typeof toastr !== 'undefined') toastr.error('Network error.');
            })
            .finally(function () {
              setBtnLoading(btnReset, false);
            });
        });
      });
    }

    if (btnImport && formImport && routes.import) {
      btnImport.addEventListener('click', function () {
        var fd = new FormData(formImport);
        var filt = readFilters();

        fd.set('_token', csrfToken());
        fd.set('bulk_all', '1');
        fd.append('academic_session_id', filt.academic_session_id);
        fd.append('program_id', filt.program_id);
        fd.append('course_id', filt.course_id);

        var fileInp = document.getElementById('sm-import-file');
        if (!fileInp || !fileInp.files || !fileInp.files.length) {
          if (typeof toastr !== 'undefined') toastr.error('Choose a file.');
          return;
        }
        if (!filt.academic_session_id || !filt.program_id || !filt.course_id) {
          if (typeof toastr !== 'undefined') toastr.error('Select academic session, program, and course first.');
          return;
        }

        if (importErrors) {
          importErrors.classList.add('d-none');
          importErrors.innerHTML = '';
        }

        setBtnLoading(btnImport, true);

        fetch(routes.import, {
          method: 'POST',
          credentials: 'same-origin',
          body: fd,
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken()
          }
        })
          .then(function (res) {
            return res.json().then(function (data) {
              return { ok: res.ok, data: data, status: res.status };
            });
          })
          .then(function (pack) {
            if (pack.ok) {
              if (typeof toastr !== 'undefined' && pack.data.message) toastr.success(pack.data.message);
              if (pack.data.redirect) window.location.href = pack.data.redirect;
              return;
            }

            var rowErr = pack.data.row_errors || pack.data.errors;
            if (
              importErrors &&
              rowErr &&
              (Array.isArray(rowErr)
                ? rowErr.length
                : typeof rowErr === 'object' && Object.keys(rowErr).length)
            ) {
              importErrors.classList.remove('d-none');
              if (Array.isArray(rowErr)) {
                importErrors.innerHTML =
                  '<strong>Issues</strong><ul class="mt-2 mb-0 small">' +
                  rowErr
                    .map(function (line) {
                      return '<li>' + escapeHtml(String(line)) + '</li>';
                    })
                    .join('') +
                  '</ul>';
              } else {
                importErrors.textContent = pack.data.message || 'Import failed.';
              }
            }

            if (typeof toastr !== 'undefined') {
              toastr.error((pack.data && pack.data.message) || 'Import failed.');
            }
          })
          .catch(function () {
            if (typeof toastr !== 'undefined') toastr.error('Network error.');
          })
          .finally(function () {
            setBtnLoading(btnImport, false);
          });
      });
    }
  }

  /** --- CREATE SINGLE --- */
  function initCreate(routes) {
    var cascade = window.__studentMarksCascade || {};
    routes = routes || {};
    var root = document.getElementById('sm-single-setup');
    var saveBox = document.getElementById('sm-single-save');
    var qCourseUrl = routes.questionsByCourseApi || routes.questionsApi;
    if (!root || !saveBox || !routes.studentsApi || !qCourseUrl) return;

    var btnLoad = document.getElementById('sm-load-context');
    wireCascade(root, cascade, { skipAssessmentComponents: true });

    /** @type {Array<Record<string, unknown>>} */
    var loadedStudentsSnapshot = [];

    /** @type {Array<{ id: number, component_name: string, marks: number, questions: Array<{id:number,question_label:string,marks:number}> }>} */
    var lastComponentsSnapshot = [];

    function escapeBare(txt) {
      return String(txt)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/"/g, '&quot;');
    }

    /** Disabled <select>s are omitted from FormData — read .value explicitly. */
    function readSetupFilterVals() {
      function pick(sel) {
        if (!sel || sel.disabled) return '';
        var v = sel.value;
        return v !== null && String(v).trim() !== '' ? String(v).trim() : '';
      }
      return {
        academic_session_id: pick(root.querySelector('#sm_sess')),
        program_id: pick(root.querySelector('#sm_prog')),
        course_id: pick(root.querySelector('#sm_course')),
        // batch_id: pick(root.querySelector('#sm_batch')),
        // section_id: pick(root.querySelector('#sm_section'))
      };
    }

    function syncCreateLoadButton() {
      if (!btnLoad) return;
      var f = readSetupFilterVals();
      btnLoad.disabled = !(
        f.academic_session_id &&
        f.program_id &&
        f.course_id 
        // f.batch_id
      );
    }

    syncCreateLoadButton();
    window.__studentMarksSyncCreateLoadBtn = syncCreateLoadButton;
    root.querySelectorAll('select').forEach(function (sel) {
      sel.addEventListener('change', syncCreateLoadButton);
    });

    btnLoad.addEventListener('click', function () {
      var f = readSetupFilterVals();

      if (!f.academic_session_id || !f.program_id || !f.course_id) {
        if (typeof toastr !== 'undefined') toastr.error('Complete all required filters.');
        return;
      }

      var pParamsQs = new URLSearchParams();
      pParamsQs.set('academic_session_id', f.academic_session_id);
      pParamsQs.set('program_id', f.program_id);
      pParamsQs.set('course_id', f.course_id);
      // pParamsQs.set('batch_id', f.batch_id);
      // if (f.section_id) pParamsQs.set('section_id', f.section_id);

      var pStudent = new URLSearchParams(pParamsQs);
      pStudent.set('with_marks', '1');
      pStudent.set('all_components', '1');

      setBtnLoading(btnLoad, true);

      Promise.all([
        fetchJson(qCourseUrl + '?' + pParamsQs.toString(), {
          credentials: 'same-origin',
          headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }
        }, false),
        fetchJson(routes.studentsApi + '?' + pStudent.toString(), {
          credentials: 'same-origin',
          headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }
        }, false)
      ])
        .then(function (pairs) {
          var qPack = pairs[0];
          var stPack = pairs[1];
          if (!qPack.ok || !stPack.ok) return;

          var components = (qPack.data && qPack.data.components) || [];

          document.getElementById('hf_sess').value = f.academic_session_id;
          document.getElementById('hf_prog').value = f.program_id;
          document.getElementById('hf_course').value = f.course_id;
          // document.getElementById('hf_batch').value = f.batch_id;
          // document.getElementById('hf_section').value = f.section_id || '';

          loadedStudentsSnapshot = stPack.data.students || [];

          var stSel = document.getElementById('sm_student');
          clearSelect(stSel, 'Choose student');
          stSel.disabled = false;
          loadedStudentsSnapshot.forEach(function (s) {
            var o = document.createElement('option');
            o.value = String(s.id);
            o.textContent = (s.student_code || '') + ' — ' + (s.student_name || '');
            stSel.appendChild(o);
          });

          var qWrap = document.getElementById('sm-single-questions');
          qWrap.innerHTML = '';

          if (!components.length) {
            saveBox.classList.add('d-none');
            if (typeof toastr !== 'undefined') {
              toastr.error('No question parts are mapped for this course in the selected session/section.');
            }
            lastComponentsSnapshot = [];
            return;
          }

          lastComponentsSnapshot = components;

          components.forEach(function (comp, ci) {
            var block = document.createElement('div');
            block.className = 'border rounded mb-3 p-2 sm-multi-comp-block';
            block.dataset.componentId = String(comp.id);
            var cap =
              typeof comp.marks !== 'undefined' && isFinite(Number(comp.marks))
                ? ' <span class="text-muted fw-normal">(cap ' + escapeBare(comp.marks) + ')</span>'
                : '';
            var head = document.createElement('div');
            head.className = 'fw-semibold small mb-2';
            head.innerHTML =
              escapeBare(comp.component_name || 'Component') + cap +
              '<div class="text-muted fst-italic fw-normal mt-1">Subtotal: <span class="sm-multi-comp-sub-display">0</span></div>';
            block.appendChild(head);

            var row = document.createElement('div');
            row.className = 'row g-2 mb-2 sm-multi-q-row';

            (comp.questions || []).forEach(function (q, qi) {
              var col = document.createElement('div');
              col.className = 'col-md-4';
              col.innerHTML =
                '<label class="form-label small">' +
                escapeBare(q.question_label) +
                ' <span class="text-muted">(max ' +
                escapeBare(q.marks) +
                ')</span></label>' +
                '<input type="hidden" name="component_marks[' +
                ci +
                '][questions][' +
                qi +
                '][question_clo_mapping_id]" value="' +
                q.id +
                '">' +
                '<input type="text" inputmode="decimal" name="component_marks[' +
                ci +
                '][questions][' +
                qi +
                '][obtained_marks]" class="form-control form-control-sm sm-multi-part sm-single-part" data-max="' +
                q.marks +
                '" value="0" data-ci="' +
                ci +
                '">';
              row.appendChild(col);
            });
            block.appendChild(row);

            var hidComp = document.createElement('input');
            hidComp.type = 'hidden';
            hidComp.name = 'component_marks[' + ci + '][assessment_component_id]';
            hidComp.value = String(comp.id);
            block.appendChild(hidComp);

            var hidTotal = document.createElement('input');
            hidTotal.type = 'hidden';
            hidTotal.name = 'component_marks[' + ci + '][total_marks]';
            hidTotal.className = 'sm-multi-comp-total';
            hidTotal.value = '0';
            hidTotal.dataset.ci = String(ci);
            block.appendChild(hidTotal);

            qWrap.appendChild(block);
          });

          var totalEl = document.getElementById('sm_single_total');

          function recalcSingle() {
            var grand = 0;
            qWrap.querySelectorAll('.sm-multi-comp-block').forEach(function (blk) {
              var sub = 0;
              blk.querySelectorAll('.sm-multi-part').forEach(function (inp) {
                var max = parseFloat(inp.getAttribute('data-max'));
                var v = parseFloat(String(inp.value).replace(',', '.'));
                if (!isFinite(v)) v = 0;
                var nv = clampNum(v, isFinite(max) ? max : Infinity);
                if (nv !== v) inp.value = String(nv);
                sub += nv;
              });
              sub = Math.round(sub * 100) / 100;
              grand += sub;
              var totInp = blk.querySelector('.sm-multi-comp-total');
              if (totInp) totInp.value = String(sub);
              var disp = blk.querySelector('.sm-multi-comp-sub-display');
              if (disp) disp.textContent = String(sub);
            });
            grand = Math.round(grand * 100) / 100;
            totalEl.value = String(grand);
          }

          qWrap.parentElement.style.display = 'block';

          qWrap.querySelectorAll('.sm-multi-part').forEach(function (inp) {
            inp.addEventListener('input', recalcSingle);
            inp.addEventListener('change', recalcSingle);
          });

          recalcSingle();
          saveBox.classList.remove('d-none');

          stSel.onchange = function () {
            var sid = this.value;
            var st = null;
            for (var si = 0; si < loadedStudentsSnapshot.length; si++) {
              if (String(loadedStudentsSnapshot[si].id) === String(sid)) {
                st = loadedStudentsSnapshot[si];
                break;
              }
            }
            if (!st) return;

            var stBox = document.getElementById('sm_status_single');
            var byComp = st.existing_by_component || {};
            var statusPicked = false;

            lastComponentsSnapshot.forEach(function (comp, ci) {
              var bucket = byComp[String(comp.id)] !== undefined ? byComp[String(comp.id)] : byComp[comp.id];
              if (!statusPicked && bucket && stBox && bucket.status_id != null && bucket.status_id !== '') {
                stBox.value = String(bucket.status_id);
                statusPicked = true;
              }
              (comp.questions || []).forEach(function (q, qi) {
                var inp = qWrap.querySelector(
                  'input[name="component_marks[' + ci + '][questions][' + qi + '][obtained_marks]"]'
                );
                if (!inp) return;
                if (!bucket) {
                  inp.value = '0';
                  return;
                }
                var qm = bucket.question_marks || {};
                var vv = qm[String(q.id)] !== undefined ? qm[String(q.id)] : qm[q.id];
                if (typeof vv !== 'undefined') inp.value = String(vv);
                else inp.value = '0';
              });
            });
            qWrap.dispatchEvent(new Event('input'));
          };
        })
        .finally(function () {
          setBtnLoading(btnLoad, false);
        });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var page = window.__studentMarksPage;
    var routes = window.__studentMarksRoutes || {};

    if (page === 'index') initIndex();
    if (page === 'bulk') initBulk(routes);
    if (page === 'create') initCreate(routes);
  });
})();
