/**
 * Teacher course marks Excel/CSV import (preview + bulk save).
 */
(function () {
  'use strict';

  var initialized = false;

  function readConfig() {
    var el = document.getElementById('marks-import-app');
    var jsonEl = document.getElementById('my-course-import-config-json');
    var config = {};

    if (jsonEl && jsonEl.textContent) {
      try {
        config = JSON.parse(jsonEl.textContent);
      } catch (e) {
        console.error('Import config JSON parse failed', e);
      }
    }

    if (el) {
      if (el.dataset.previewRoute) config.previewRoute = el.dataset.previewRoute;
      if (el.dataset.bulkSaveRoute) config.bulkSaveRoute = el.dataset.bulkSaveRoute;
      if (el.dataset.capabilitiesRoute) config.capabilitiesRoute = el.dataset.capabilitiesRoute;
      if (el.dataset.csrfToken) config.csrfToken = el.dataset.csrfToken;
      if (el.dataset.maxMarks) config.maxMarks = parseFloat(el.dataset.maxMarks) || 100;
      if (el.dataset.excelImportReady === '1') config.excelImportReady = true;
      if (el.dataset.excelImportReady === '0') config.excelImportReady = false;
    }

    if (window.__myCourseImportConfig) {
      config = Object.assign({}, window.__myCourseImportConfig, config);
    }

    return config;
  }

  function csrfToken(config) {
    if (config && config.csrfToken) return config.csrfToken;
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function columnLabel(column, config) {
    if (config.markColumnLabels && config.markColumnLabels[column]) {
      return config.markColumnLabels[column];
    }
    return String(column).replace(/_/g, ' ').replace(/\b\w/g, function (c) {
      return c.toUpperCase();
    });
  }

  function flattenErrors(errors) {
    if (!errors) return '';
    if (typeof errors === 'string') return errors;
    if (Array.isArray(errors)) return errors.join('\n');
    return Object.keys(errors).map(function (k) {
      var v = errors[k];
      return Array.isArray(v) ? v.join(' ') : String(v);
    }).join('\n');
  }

  function showStatus(message, type) {
    var el = document.getElementById('import-status-alert');
    if (!el) return;
    if (!message) {
      el.className = 'alert d-none';
      el.textContent = '';
      return;
    }
    el.className = 'alert alert-' + (type || 'info');
    el.textContent = message;
    el.classList.remove('d-none');
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function setProgress(wrapId, barId, pctId, labelId, pct, label) {
    var wrap = document.getElementById(wrapId);
    var bar = document.getElementById(barId);
    var pctEl = document.getElementById(pctId);
    var labelEl = document.getElementById(labelId);
    if (!wrap || !bar) return;
    wrap.classList.remove('d-none');
    bar.style.width = Math.min(100, Math.max(0, pct)) + '%';
    if (pctEl) pctEl.textContent = Math.round(pct) + '%';
    if (labelEl && label) labelEl.textContent = label;
  }

  function hideProgress(wrapId, barId) {
    var wrap = document.getElementById(wrapId);
    var bar = document.getElementById(barId);
    if (wrap) wrap.classList.add('d-none');
    if (bar) bar.style.width = '0%';
  }

  function setBtnLoading(btn, loading) {
    if (!btn) return;
    btn.disabled = !!loading;
    var spin = btn.querySelector('.import-btn-spinner');
    var label = btn.querySelector('.import-btn-label');
    if (spin) spin.classList.toggle('d-none', !loading);
    if (label) label.classList.toggle('opacity-50', !!loading);
  }

  function notify(title, text, icon) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: icon || 'info', title: title, text: text || title });
      return;
    }
    showStatus((title ? title + ': ' : '') + (text || ''), icon === 'error' ? 'danger' : icon === 'success' ? 'success' : 'info');
  }

  function humanizeErrorText(text) {
    if (!text) return text;
    var lower = String(text).toLowerCase();
    if (lower.indexOf('duplicate entry') !== -1 || lower.indexOf('unique constraint') !== -1) {
      if (lower.indexOf('student_marks') !== -1) {
        return 'Marks for this student are already saved for this course. Save again to update existing marks, or remove duplicate rows from your file.';
      }
      return 'This record already exists. Please update the existing entry instead of creating a duplicate.';
    }
    return text;
  }

  function extractErrorMessage(err) {
    if (!err) return 'Request failed.';
    if (err.data && err.data.errors) {
      var flat = humanizeErrorText(flattenErrors(err.data.errors));
      if (flat) return flat;
    }
    if (err.data && err.data.message) return humanizeErrorText(err.data.message);
    return humanizeErrorText(err.message || 'Request failed.');
  }

  function uploadWithProgress(url, formData, token, onProgress) {
    return new Promise(function (resolve, reject) {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', url, true);
      xhr.setRequestHeader('Accept', 'application/json');
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token);

      xhr.upload.addEventListener('progress', function (e) {
        if (e.lengthComputable && typeof onProgress === 'function') {
          onProgress(Math.round((e.loaded / e.total) * 90));
        }
      });

      xhr.onload = function () {
        var text = xhr.responseText || '';
        var data = {};
        try {
          data = text ? JSON.parse(text) : {};
        } catch (err) {
          if (xhr.status === 419) {
            reject({ message: 'Session expired. Refresh the page and log in again.' });
            return;
          }
          if (text.indexOf('<html') !== -1 || text.indexOf('<!DOCTYPE') !== -1) {
            reject({ message: 'Server returned HTML instead of JSON (status ' + xhr.status + '). Check login or server error log.' });
            return;
          }
          reject({ message: 'Invalid server response (status ' + xhr.status + ').' });
          return;
        }

        if (xhr.status >= 200 && xhr.status < 300) {
          resolve(data);
          return;
        }

        reject({
          message: data.message || flattenErrors(data.errors) || ('Request failed (' + xhr.status + ')'),
          data: data,
        });
      };

      xhr.onerror = function () {
        reject({ message: 'Network error while uploading. Check the URL: ' + url });
      };

      xhr.send(formData);
    });
  }

  function updateZipWarning(ready, diagnostics) {
    var el = document.getElementById('import-zip-warning');
    if (!el) return;
    if (ready) {
      el.classList.add('d-none');
      el.textContent = '';
      return;
    }
    var hint = 'Excel (.xlsx) needs the PHP zip extension in Apache. Restart XAMPP after enabling extension=zip, or upload a .csv file.';
    if (diagnostics && diagnostics.php_ini) {
      hint += ' (php.ini: ' + diagnostics.php_ini + ')';
    }
    el.textContent = hint;
    el.classList.remove('d-none');
  }

  function initImportPage() {
    if (initialized) return;

    var root = document.getElementById('marks-import-app');
    if (!root) return;

    var config = readConfig();
    if (!config.previewRoute || !config.bulkSaveRoute) {
      showStatus('Import is not configured (missing routes). Refresh the page.', 'danger');
      return;
    }

    initialized = true;

    var pendingSaveRows = [];
    var markColumns = config.markColumns || [];
    var token = csrfToken(config);

    updateZipWarning(config.excelImportReady !== false, null);

    if (config.capabilitiesRoute) {
      fetch(config.capabilitiesRoute, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      })
        .then(function (res) {
          return res.json();
        })
        .then(function (data) {
          config.excelImportReady = !!data.excel_ready;
          updateZipWarning(config.excelImportReady, data);
        })
        .catch(function () {});
    }

    function renderPreview(preview, summary) {
      var previewTable = document.getElementById('import-preview-table');
      if (!previewTable) return;

      var thead = previewTable.querySelector('thead');
      var tbody = previewTable.querySelector('tbody');
      var previewSummary = document.getElementById('import-preview-summary');
      var failedSection = document.getElementById('import-failed-section');
      var failedList = document.getElementById('import-failed-list');

      if (!thead || !tbody) return;

      thead.innerHTML = '';
      tbody.innerHTML = '';

      var displayMarkCols = markColumns.length > 8 ? markColumns.slice(0, 8) : markColumns;
      var headers = ['row', 'student_code', 'status'].concat(displayMarkCols);
      if (markColumns.length > 8) headers.push('…');

      var trh = document.createElement('tr');
      headers.forEach(function (h) {
        var th = document.createElement('th');
        th.className = 'text-nowrap small';
        th.textContent = h === '…' ? '…' : columnLabel(h, config);
        trh.appendChild(th);
      });
      thead.appendChild(trh);

      (preview || []).forEach(function (row) {
        var tr = document.createElement('tr');
        if (row.status === 'failed') tr.classList.add('table-danger');
        else if (row.status === 'ok') tr.classList.add('table-success');

        headers.forEach(function (h) {
          var td = document.createElement('td');
          td.className = 'small';
          if (h === '…') {
            td.textContent = '…';
          } else if (h === 'status') {
            td.textContent = row.status === 'ok' ? 'OK' : 'Failed';
          } else {
            var val = row[h];
            td.textContent = val != null && val !== '' ? val : (h === 'row' ? '' : '');
          }
          tr.appendChild(td);
        });
        tbody.appendChild(tr);
      });

      if (previewSummary && summary) {
        previewSummary.classList.remove('d-none');
        var extra = markColumns.length > 8 ? ' (showing first 8 mark columns)' : '';
        previewSummary.textContent =
          'Total rows: ' + (summary.total_rows || 0) +
          ' | Valid: ' + (summary.valid_rows || 0) +
          ' | Failed: ' + (summary.failed_rows || 0) + extra;
      }

      if (failedSection && failedList) {
        var failedRows = (preview || []).filter(function (r) {
          return r.status === 'failed';
        });
        if (failedRows.length) {
          failedSection.classList.remove('d-none');
          failedList.innerHTML = '';
          failedRows.forEach(function (r) {
            var li = document.createElement('li');
            li.textContent = 'Row ' + r.row + ' (' + r.student_code + '): ' + (r.error || 'Unknown error');
            failedList.appendChild(li);
          });
        } else {
          failedSection.classList.add('d-none');
          failedList.innerHTML = '';
        }
      }
    }

    function handleUploadClick() {
      var fileInput = document.getElementById('marks-import-file');
      var uploadBtn = document.getElementById('import-upload-preview-btn');
      var previewSection = document.getElementById('import-preview-section');
      var bulkSaveBtn = document.getElementById('import-bulk-save-btn');
      var resultSummary = document.getElementById('import-result-summary');

      if (!fileInput || !fileInput.files || !fileInput.files.length) {
        notify('No file selected', 'Choose a .csv or .xlsx file first.', 'warning');
        return;
      }

      var file = fileInput.files[0];
      var fd = new FormData();
      fd.append('file', file);
      fd.append('_token', token);

      setBtnLoading(uploadBtn, true);
      showStatus('Uploading and reading file...', 'info');
      if (resultSummary) resultSummary.classList.add('d-none');

      uploadWithProgress(config.previewRoute, fd, token, function (pct) {
        setProgress(
          'import-upload-progress-wrap',
          'import-upload-progress-bar',
          'import-upload-progress-pct',
          'import-upload-progress-label',
          pct,
          pct < 90 ? 'Uploading file...' : 'Processing preview...'
        );
      })
        .then(function (data) {
          setProgress(
            'import-upload-progress-wrap',
            'import-upload-progress-bar',
            'import-upload-progress-pct',
            'import-upload-progress-label',
            100,
            'Preview ready'
          );

          pendingSaveRows = data.rows || [];
          markColumns = data.mark_columns || markColumns;
          if (data.mark_column_labels) {
            config.markColumnLabels = data.mark_column_labels;
          }

          if (previewSection) {
            previewSection.classList.remove('d-none');
          }

          try {
            renderPreview(data.preview || [], data.summary || {});
          } catch (renderErr) {
            console.error(renderErr);
            showStatus('Preview loaded but table render failed: ' + renderErr.message, 'warning');
          }

          if (bulkSaveBtn) {
            bulkSaveBtn.disabled = pendingSaveRows.length < 1;
          }

          var summary = data.summary || {};
          var statusMsg = data.message || 'Preview loaded.';
          if ((summary.valid_rows || 0) < 1 && (summary.total_rows || 0) > 0) {
            statusMsg =
              'No valid rows. Check failed rows (wrong Student Code, non-numeric marks, or total exceeds course max ' +
              (config.maxMarks || '') + ').';
            showStatus(statusMsg, 'warning');
            notify('No valid rows', statusMsg, 'warning');
          } else if (pendingSaveRows.length > 0) {
            showStatus(statusMsg + ' Click Bulk Save All for ' + pendingSaveRows.length + ' row(s).', 'success');
          } else {
            showStatus(statusMsg, 'info');
          }
        })
        .catch(function (err) {
          hideProgress('import-upload-progress-wrap', 'import-upload-progress-bar');
          var msg = extractErrorMessage(err);
          showStatus(msg, 'danger');
          notify('Preview failed', msg, 'error');
        })
        .finally(function () {
          setBtnLoading(uploadBtn, false);
          setTimeout(function () {
            hideProgress('import-upload-progress-wrap', 'import-upload-progress-bar');
          }, 800);
        });
    }

    function handleBulkSaveClick() {
      var bulkSaveBtn = document.getElementById('import-bulk-save-btn');
      var resultSummary = document.getElementById('import-result-summary');

      if (!pendingSaveRows.length) {
        notify('Nothing to save', 'No valid rows in preview.', 'warning');
        return;
      }

      setBtnLoading(bulkSaveBtn, true);
      showStatus('Saving marks...', 'info');
      setProgress('import-save-progress-wrap', 'import-save-progress-bar', 'import-save-progress-pct', 'import-save-progress-label', 20, 'Saving...');

      fetch(config.bulkSaveRoute, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ rows: pendingSaveRows }),
      })
        .then(function (res) {
          return res.text().then(function (text) {
            var data = {};
            try {
              data = text ? JSON.parse(text) : {};
            } catch (e) {
              throw new Error('Invalid save response (status ' + res.status + ').');
            }
            return { ok: res.ok, data: data };
          });
        })
        .then(function (pack) {
          setProgress('import-save-progress-wrap', 'import-save-progress-bar', 'import-save-progress-pct', 'import-save-progress-label', 100, 'Done');

          if (!pack.ok) {
            var errMsg = extractErrorMessage({ data: pack.data, message: pack.data.message });
            showStatus(errMsg, 'danger');
            notify('Save failed', errMsg, 'error');
            return;
          }

          var s = pack.data.summary || {};
          var msg = 'Processed: ' + (s.processed || 0) + ', inserted: ' + (s.inserted || 0) + ', updated: ' + (s.updated || 0);
          if (resultSummary) {
            resultSummary.className = 'alert alert-success mt-3';
            resultSummary.textContent = msg;
            resultSummary.classList.remove('d-none');
          }
          showStatus('Import saved successfully. ' + msg, 'success');
          notify('Saved', msg, 'success');
        })
        .catch(function (err) {
          hideProgress('import-save-progress-wrap', 'import-save-progress-bar');
          showStatus(err.message || 'Save failed.', 'danger');
          notify('Save failed', err.message || 'Save failed.', 'error');
        })
        .finally(function () {
          setBtnLoading(bulkSaveBtn, false);
        });
    }

    window.__myCourseImportUploadClick = function (e) {
      if (e && e.preventDefault) e.preventDefault();
      handleUploadClick();
    };
    window.__myCourseImportBulkSaveClick = function (e) {
      if (e && e.preventDefault) e.preventDefault();
      handleBulkSaveClick();
    };
    window.__myCourseImportBooted = true;

    root.addEventListener('click', function (e) {
      if (e.target.closest('#import-upload-preview-btn')) {
        e.preventDefault();
        e.stopPropagation();
        handleUploadClick();
      }
      if (e.target.closest('#import-bulk-save-btn')) {
        e.preventDefault();
        e.stopPropagation();
        handleBulkSaveClick();
      }
    });

    showStatus('Ready. Select a file and click Upload & Preview.', 'info');
    setTimeout(function () {
      showStatus('', 'info');
    }, 2500);
  }

  function boot() {
    try {
      initImportPage();
    } catch (err) {
      console.error(err);
      showStatus('Import script error: ' + err.message, 'danger');
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
