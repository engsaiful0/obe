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
  function wireCascade(scope, cascade) {
    if (!scope || !cascade) return {};

    var elProg = scope.querySelector('#sm_prog');
    var elCourse = scope.querySelector('#sm_course');
    var elBatch = scope.querySelector('#sm_batch');
    var elSection = scope.querySelector('#sm_section');
    var elComp = scope.querySelector('#sm_comp');

    function onProgramChange() {
      var pid = elProg ? elProg.value : '';
      if (!pid) {
        clearSelect(elCourse, 'Select');
        clearSelect(elBatch, 'Select');
        clearSelect(elSection, elSection && elSection.id === 'sm_section' ? 'All / not specified' : 'Optional');
        clearSelect(elComp, 'Select');
        [elCourse, elBatch, elSection, elComp].forEach(function (x) {
          if (x) x.disabled = true;
        });
        return Promise.resolve(null);
      }
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

      clearSelect(elSection, elSection && elSection.options && elSection.options.length ? '' : '');
      clearSelect(elComp, 'Select');
      if (elComp) elComp.disabled = true;
      elSection.disabled = true;

      return Promise.all(promises);
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
      });
    }

    function onCourseChange() {
      var cid = elCourse ? elCourse.value : '';
      if (!cid) {
        clearSelect(elComp, 'Select');
        if (elComp) elComp.disabled = true;
        return Promise.resolve();
      }
      return fetchJson(
        replaceUrlTpl(cascade.assessmentComponents || '', { '__COURSE_ID__': cid }),
        { headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }, credentials: 'same-origin' },
        false
      ).then(function (pack) {
        if (!pack || !pack.ok || !pack.data) return;
        fillSelect(elComp, pack.data, function (ac) {
          var m = typeof ac.marks !== 'undefined' ? ' (max ' + ac.marks + ')' : '';
          return (ac.component_name || ac.label || '') + m;
        });
        elComp.disabled = false;
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

    var c = wireCascade(form, cascade);

    var btnLoad = document.getElementById('sm-load-grid');
    var btnSave = document.getElementById('sm-bulk-save');
    var btnTemplate = document.getElementById('sm-download-template');
    var btnReset = document.getElementById('sm-reset');
    var wrap = document.getElementById('sm-matrix-wrap');
    var tbl = document.getElementById('sm-matrix-table');
    var capDisplay = document.getElementById('sm-comp-cap-display');
    var feedback = document.getElementById('sm-bulk-feedback');
    var btnImport = document.getElementById('sm-import-submit');
    var importErrors = document.getElementById('sm-import-errors');
    var formImport = document.getElementById('sm-import-form');

    /** @type {Array<{questions: Array} & Record<string, mixed>>} */
    var lastStudents = [];
    /** @type {Array<{id:number, question_label:string, marks:number}>} */
    var lastQuestions = [];
    var componentCap = null;

    function readFilters() {
      var fd = new FormData(form);
      function pick(name) {
        var v = fd.get(name);
        return v === null || String(v).trim() === '' ? '' : String(v).trim();
      }
      return {
        academic_session_id: pick('academic_session_id'),
        program_id: pick('program_id'),
        course_id: pick('course_id'),
        batch_id: pick('batch_id'),
        section_id: pick('section_id'),
        assessment_component_id: pick('assessment_component_id'),
        status_id: pick('status_id')
      };
    }

    function buildParams(f) {
      var p = new URLSearchParams();
      Object.keys(f).forEach(function (k) {
        if (!f[k]) return;
        p.set(k, f[k]);
      });
      return p;
    }

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
      var capTotal = typeof componentCap === 'number' && isFinite(componentCap) ? componentCap : Infinity;
      var sum = 0;
      tr.querySelectorAll('input.sm-qpart').forEach(function (inp) {
        var max = parseFloat(inp.getAttribute('data-max'));
        var v = parseFloat(String(inp.value).replace(',', '.'));
        if (!isFinite(v)) v = 0;
        var cl = inp.classList;
        v = clampNum(v, isFinite(max) ? max : Infinity);
        if (isFinite(max) && v >= max - 1e-6) inp.classList.add('border-danger');
        else inp.classList.remove('border-danger');
        sum += v;
      });
      sum = Math.round(sum * 100) / 100;
      var tot = tr.querySelector('input.sm-total');
      if (tot) {
        tot.value = String(sum);
        if (sum > capTotal + 1e-4) tot.classList.add('is-invalid');
        else tot.classList.remove('is-invalid');
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

    function buildMatrix(students, questions, compMarks) {
      lastStudents = students;
      lastQuestions = questions;
      componentCap =
        typeof compMarks === 'number' && isFinite(compMarks)
          ? compMarks
          : compMarks !== null && compMarks !== undefined
            ? parseFloat(compMarks)
            : null;
      if (capDisplay) {
        capDisplay.textContent =
          componentCap !== null && isFinite(componentCap) ? String(componentCap) : '—';
      }

      var thead = tbl.querySelector('thead');
      var tbody = tbl.querySelector('tbody');
      thead.innerHTML = '';
      tbody.innerHTML = '';

      var trh = document.createElement('tr');
      trh.appendChild(document.createElement('th')).textContent = 'Student';
      questions.forEach(function (q) {
        var th = document.createElement('th');
        th.textContent = q.question_label + ' (' + q.marks + ')';
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

        questions.forEach(function (q) {
          var td = document.createElement('td');
          var inp = document.createElement('input');
          inp.type = 'text';
          inp.inputMode = 'decimal';
          inp.className = 'form-control form-control-sm sm-qpart';
          inp.dataset.studentId = String(st.id);
          inp.dataset.mapId = String(q.id);
          inp.setAttribute('data-max', String(q.marks));
          var ex = st.existing && st.existing.question_marks ? st.existing.question_marks[String(q.id)] : undefined;
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
        var exTot = st.existing ? st.existing.total_marks : null;
        if (exTot !== null && typeof exTot !== 'undefined') inpT.value = String(exTot);
        tdT.appendChild(inpT);
        tr.appendChild(tdT);

        tbody.appendChild(tr);
        recalcRow(tr);
      });

      wrap.classList.remove('d-none');
      btnSave.disabled = !students.length || !questions.length;
    }

    function escapeHtml(s) {
      return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    if (btnLoad) {
      btnLoad.addEventListener('click', function () {
        var f = readFilters();
        if (!f.academic_session_id || !f.program_id || !f.course_id || !f.batch_id || !f.assessment_component_id) {
          showFeedback('Fill session, program, course, batch, and component.');
          return;
        }
        showFeedback(null);
        setBtnLoading(btnLoad, true);

        var p1 = routes.questionsApi + '?' + buildParams(f).toString();
        var p2 =
          routes.studentsApi + '?' + buildParams(Object.assign({}, f, { with_marks: '1' })).toString();

        Promise.all([
          fetchJson(
            p1,
            {
              credentials: 'same-origin',
              headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }
            },
            false
          ),
          fetchJson(
            p2,
            {
              credentials: 'same-origin',
              headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }
            },
            false
          )
        ])
          .then(function (pairs) {
            var qPack = pairs[0];
            var stPack = pairs[1];
            if (!qPack.ok || !stPack.ok) return;
            var questions = qPack.data.questions || [];
            var compatCap =
              qPack.data.component && typeof qPack.data.component.marks !== 'undefined'
                ? qPack.data.component.marks
                : null;
            if (!questions.length) {
              showFeedback('No question parts mapped for this session and component.');
              wrap.classList.add('d-none');
              btnSave.disabled = true;
              return;
            }
            var students = stPack.data.students || [];
            buildMatrix(
              students,
              questions,
              compatCap !== null ? compatCap : (stPack.data.component ? stPack.data.component.marks : null)
            );
          })
          .finally(function () {
            setBtnLoading(btnLoad, false);
          });
      });
    }

    if (btnSave) {
      btnSave.addEventListener('click', function () {
        var f = readFilters();
        if (!f.status_id) {
          if (typeof toastr !== 'undefined') toastr.error('Choose OBE status.');
          return;
        }
        if (!lastQuestions.length || !lastStudents.length) {
          return;
        }
        var rowsPayload = [];

        tbl.querySelector('tbody').querySelectorAll('tr').forEach(function (tr) {
          var firstCell = tr.querySelector('input.sm-qpart');
          if (!firstCell) return;
          var sid = parseInt(firstCell.getAttribute('data-student-id'), 10);
          var questions = [];
          tr.querySelectorAll('input.sm-qpart').forEach(function (inp) {
            questions.push({
              question_clo_mapping_id: parseInt(inp.getAttribute('data-map-id'), 10),
              obtained_marks: parseFloat(String(inp.value).replace(',', '.')) || 0
            });
          });
          var totEl = tr.querySelector('input.sm-total');
          var total = parseFloat(String(totEl.value).replace(',', '.')) || 0;
          rowsPayload.push({ student_id: sid, total_marks: total, questions: questions });
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
            batch_id: f.batch_id,
            section_id: f.section_id || null,
            assessment_component_id: f.assessment_component_id,
            status_id: f.status_id,
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
        if (!f.academic_session_id || !f.program_id || !f.course_id || !f.batch_id || !f.assessment_component_id) {
          if (typeof toastr !== 'undefined') toastr.error('Select filters first.');
          return;
        }
        try {
          var u = new URL(routes.template, window.location.origin);
          Object.keys(f).forEach(function (k) {
            if (f[k]) u.searchParams.set(k, f[k]);
          });
          window.location.href = u.pathname + u.search;
        } catch (eURL) {
          var qs = buildParams(f).toString();
          window.location.href =
            routes.template + (String(routes.template).indexOf('?') === -1 ? '?' : '&') + qs;
        }
      });
    }

    if (btnReset && routes.reset) {
      btnReset.addEventListener('click', function () {
        var f = readFilters();
        if (!f.academic_session_id || !f.program_id || !f.course_id || !f.batch_id || !f.assessment_component_id) {
          if (typeof toastr !== 'undefined') toastr.error('Select filters first.');
          return;
        }

        confirmSwal(
          'Delete all marks for this session/program/course/batch/component' +
            (f.section_id ? ' and section?' : '?'),
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
            body: JSON.stringify(Object.assign({ _token: csrfToken() }, f))
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

        ['academic_session_id', 'program_id', 'course_id', 'batch_id', 'assessment_component_id', 'status_id'].forEach(
          function (k) {
            if (filt[k]) fd.append(k, filt[k]);
          }
        );
        if (!filt.section_id) {
          /* omit */
        } else {
          fd.append('section_id', filt.section_id);
        }

        var fileInp = document.getElementById('sm-import-file');
        if (!fileInp || !fileInp.files || !fileInp.files.length) {
          if (typeof toastr !== 'undefined') toastr.error('Choose a file.');
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
    if (!root || !saveBox || !routes.studentsApi || !routes.questionsApi) return;

    var btnLoad = document.getElementById('sm-load-context');
    wireCascade(root, cascade);

    btnLoad.disabled = false;

    /** @type {Array<{id: number, existing?: Record<string, unknown>}>} */
    var loadedStudentsSnapshot = [];

    function escapeBare(txt) {
      return String(txt)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/"/g, '&quot;');
    }

    btnLoad.addEventListener('click', function () {
      var form = root;
      var fd = new FormData(form);
      function pick(name) {
        var v = fd.get(name);
        return v !== null && String(v).trim() !== '' ? String(v).trim() : '';
      }
      var f = {
        academic_session_id: pick('academic_session_id'),
        program_id: pick('program_id'),
        course_id: pick('course_id'),
        batch_id: pick('batch_id'),
        section_id: pick('section_id'),
        assessment_component_id: pick('assessment_component_id')
      };

      if (!f.academic_session_id || !f.program_id || !f.course_id || !f.batch_id || !f.assessment_component_id) {
        if (typeof toastr !== 'undefined') toastr.error('Complete all required filters.');
        return;
      }

      var pParams = new URLSearchParams();
      Object.keys(f).forEach(function (k) {
        if (f[k]) pParams.append(k, f[k]);
      });
      pParams.set('with_marks', '1');

      setBtnLoading(btnLoad, true);

      Promise.all([
        fetchJson(routes.questionsApi + '?' + pParams.toString(), {
          credentials: 'same-origin',
          headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }
        }, false),
        fetchJson(routes.studentsApi + '?' + pParams.toString(), {
          credentials: 'same-origin',
          headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken() }
        }, false)
      ])
        .then(function (pairs) {
          var qPack = pairs[0];
          var stPack = pairs[1];
          if (!qPack.ok || !stPack.ok) return;

          document.getElementById('hf_sess').value = f.academic_session_id;
          document.getElementById('hf_prog').value = f.program_id;
          document.getElementById('hf_course').value = f.course_id;
          document.getElementById('hf_batch').value = f.batch_id;
          document.getElementById('hf_section').value = f.section_id || '';
          document.getElementById('hf_comp').value = f.assessment_component_id;

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

          var questions = qPack.data.questions || [];
          var qWrap = document.getElementById('sm-single-questions');
          qWrap.innerHTML = '';

          questions.forEach(function (q, idx) {
            var wrap = document.createElement('div');
            wrap.className = 'col-md-4';
            wrap.innerHTML =
              '<label class="form-label small">' +
              escapeBare(q.question_label) +
              ' <span class="text-muted">(max ' +
              escapeBare(q.marks) +
              ')</span></label>' +
              '<input type="hidden" name="questions[' +
              idx +
              '][question_clo_mapping_id]" value="' +
              q.id +
              '">' +
              '<input type="text" inputmode="decimal" name="questions[' +
              idx +
              '][obtained_marks]" class="form-control form-control-sm sm-single-part" data-max="' +
              q.marks +
              '" value="0">';
            qWrap.appendChild(wrap);
          });

          var totalEl = document.getElementById('sm_single_total');
          qWrap.parentElement.style.display = 'block';

          function recalcSingle() {
            var sum = 0;
            qWrap.querySelectorAll('.sm-single-part').forEach(function (inp) {
              var max = parseFloat(inp.getAttribute('data-max'));
              var v = parseFloat(String(inp.value).replace(',', '.'));
              if (!isFinite(v)) v = 0;
              var nv = clampNum(v, isFinite(max) ? max : Infinity);
              if (nv !== v) inp.value = String(nv);
              sum += nv;
            });
            sum = Math.round(sum * 100) / 100;
            totalEl.value = String(sum);
          }

          qWrap.querySelectorAll('.sm-single-part').forEach(function (inp) {
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
            if (!st || !st.existing) {
              return;
            }
            var qm = st.existing.question_marks || {};
            var stBox = document.getElementById('sm_status_single');
            if (stBox) stBox.value = String(st.existing.status_id || '');

            questions.forEach(function (q, idx) {
              var inp = qWrap.querySelector('input[name="questions[' + idx + '][obtained_marks]"]');
              if (!inp) return;
              var v = qm[String(q.id)];
              if (typeof v !== 'undefined') inp.value = String(v);
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
