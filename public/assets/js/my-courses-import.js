(function () {
  'use strict';

  function getConfig() {
    return window.__myCourseImportConfig || {};
  }

  function csrfToken() {
    var config = getConfig();
    if (config.csrfToken) {
      return config.csrfToken;
    }
    var meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  function columnLabel(column) {
    var config = getConfig();
    if (config.markColumnLabels && config.markColumnLabels[column]) {
      return config.markColumnLabels[column];
    }
    return String(column).replace(/_/g, ' ').replace(/\b\w/g, function (c) {
      return c.toUpperCase();
    });
  }

  function extractErrorMessage(err) {
    if (!err) return 'Request failed.';
    if (err.data && err.data.errors) {
      var flat = flattenErrors(err.data.errors);
      if (flat) return flat;
    }
    if (err.data && err.data.message) return err.data.message;
    return err.message || 'Request failed.';
  }

  function flattenErrors(errors) {
    if (!errors) return '';
    if (typeof errors === 'string') return errors;
    if (Array.isArray(errors)) return errors.join('\n');
    return Object.keys(errors)
      .map(function (k) {
        var v = errors[k];
        return Array.isArray(v) ? v.join(' ') : String(v);
      })
      .join('\n');
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

  function notifySuccess(title, htmlOrText) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'success', title: title, html: htmlOrText || undefined, text: htmlOrText ? undefined : title });
      return;
    }
    showStatus(title + (htmlOrText ? ' ' + String(htmlOrText).replace(/<[^>]+>/g, ' ') : ''), 'success');
  }

  function notifyError(title, text) {
    if (typeof Swal !== 'undefined') {
      Swal.fire({ icon: 'error', title: title, text: text || title });
      return;
    }
    showStatus((title ? title + ': ' : '') + (text || ''), 'danger');
  }

  function uploadWithProgress(url, formData, onProgress) {
    return new Promise(function (resolve, reject) {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', url, true);
      xhr.setRequestHeader('Accept', 'application/json');
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken());

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
            reject({ message: 'Session expired. Please refresh the page and log in again.' });
            return;
          }
          if (text.indexOf('<html') !== -1 || text.indexOf('<!DOCTYPE') !== -1) {
            reject({ message: 'Server returned an error page instead of JSON. Check PHP error log or enable zip extension for Excel.' });
            return;
          }
          reject({ message: 'Invalid server response.' });
          return;
        }

        if (xhr.status >= 200 && xhr.status < 300) {
          resolve(data);
          return;
        }

        reject({
          message: data.message || flattenErrors(data.errors) || ('Request failed (' + xhr.status + ')'),
          data: data,
          status: xhr.status,
        });
      };

      xhr.onerror = function () {
        reject({ message: 'Network error while uploading the file.' });
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
    var hint = 'Excel (.xlsx) needs the PHP zip extension in the Apache PHP that serves this site (not only CLI). ';
    if (diagnostics && diagnostics.php_ini) {
      hint += 'Loaded php.ini: ' + diagnostics.php_ini + '. ';
    }
    hint += 'Restart Apache in XAMPP after enabling extension=zip, or upload a .csv file.';
    el.textContent = hint;
    el.classList.remove('d-none');
  }

  function initImportPage() {
    var config = getConfig();
    if (!config.previewRoute || !config.bulkSaveRoute) {
      showStatus('Import routes are not configured on this page.', 'danger');
      return;
    }

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
        .catch(function () {
          /* keep server-rendered flag */
        });
    }

    var fileInput = document.getElementById('marks-import-file');
    var uploadBtn = document.getElementById('import-upload-preview-btn');
    var bulkSaveBtn = document.getElementById('import-bulk-save-btn');
    var previewSection = document.getElementById('import-preview-section');
    var previewTable = document.getElementById('import-preview-table');
    var previewSummary = document.getElementById('import-preview-summary');
    var failedSection = document.getElementById('import-failed-section');
    var failedList = document.getElementById('import-failed-list');
    var resultSummary = document.getElementById('import-result-summary');

    var pendingSaveRows = [];
    var markColumns = config.markColumns || [];

    function renderPreview(preview, summary) {
      if (!previewTable) return;
      var thead = previewTable.querySelector('thead');
      var tbody = previewTable.querySelector('tbody');
      if (!thead || !tbody) return;

      thead.innerHTML = '';
      tbody.innerHTML = '';

      var headers = ['row', 'student_code', 'status'].concat(markColumns);
      var trh = document.createElement('tr');
      headers.forEach(function (h) {
        var th = document.createElement('th');
        th.className = 'text-nowrap';
        th.textContent = columnLabel(h);
        trh.appendChild(th);
      });
      thead.appendChild(trh);

      (preview || []).forEach(function (row) {
        var tr = document.createElement('tr');
        if (row.status === 'failed') {
          tr.classList.add('table-danger');
        } else if (row.status === 'ok') {
          tr.classList.add('table-success');
        }
        headers.forEach(function (h) {
          var td = document.createElement('td');
          var val = row[h];
          if (h === 'status') {
            td.textContent = val === 'ok' ? 'OK' : 'Failed';
          } else {
            td.textContent = val != null && val !== '' ? val : (h === 'row' ? '' : '0');
          }
          tr.appendChild(td);
        });
        tbody.appendChild(tr);
      });

      if (previewSummary && summary) {
        previewSummary.classList.remove('d-none');
        previewSummary.textContent =
          'Total rows: ' +
          (summary.total_rows || 0) +
          ' | Valid: ' +
          (summary.valid_rows || 0) +
          ' | Failed: ' +
          (summary.failed_rows || 0);
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

    if (uploadBtn) {
      uploadBtn.addEventListener('click', function () {
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
          notifyError('No file', 'Please select an Excel or CSV file first.');
          return;
        }

        var file = fileInput.files[0];

        var fd = new FormData();
        fd.append('file', file);
        fd.append('_token', csrfToken());

        setBtnLoading(uploadBtn, true);
        showStatus('Uploading and reading file...', 'info');
        if (resultSummary) resultSummary.classList.add('d-none');

        uploadWithProgress(config.previewRoute, fd, function (pct) {
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
              previewSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            renderPreview(data.preview || [], data.summary || {});

            if (bulkSaveBtn) {
              bulkSaveBtn.disabled = pendingSaveRows.length < 1;
            }

            var summary = data.summary || {};
            var statusMsg = data.message || 'Preview loaded.';
            if ((summary.valid_rows || 0) < 1 && (summary.total_rows || 0) > 0) {
              statusMsg =
                'No rows passed validation. Check failed rows below (wrong student code, non-numeric marks, or total exceeds course maximum ' +
                (config.maxMarks || '') +
                ').';
              showStatus(statusMsg, 'warning');
              notifyError('Preview: no valid rows', statusMsg);
            } else if (pendingSaveRows.length > 0) {
              showStatus(statusMsg + ' Click Bulk Save All to store ' + pendingSaveRows.length + ' row(s).', 'success');
              notifySuccess('Preview ready', statusMsg);
            } else {
              showStatus(statusMsg, 'info');
            }
          })
          .catch(function (err) {
            hideProgress('import-upload-progress-wrap', 'import-upload-progress-bar');
            var msg = extractErrorMessage(err);
            showStatus(msg, 'danger');
            notifyError('Preview failed', msg);
          })
          .finally(function () {
            setBtnLoading(uploadBtn, false);
            setTimeout(function () {
              hideProgress('import-upload-progress-wrap', 'import-upload-progress-bar');
            }, 800);
          });
      });
    } else {
      showStatus('Upload button not found on page.', 'danger');
    }

    if (bulkSaveBtn) {
      bulkSaveBtn.addEventListener('click', function () {
        if (!pendingSaveRows.length) {
          notifyError('Nothing to save', 'No valid rows in preview. Fix failed rows or upload again.');
          return;
        }

        setBtnLoading(bulkSaveBtn, true);
        showStatus('Saving marks to database...', 'info');
        setProgress(
          'import-save-progress-wrap',
          'import-save-progress-bar',
          'import-save-progress-pct',
          'import-save-progress-label',
          20,
          'Saving marks...'
        );

        var progressTimer = setInterval(function () {
          var bar = document.getElementById('import-save-progress-bar');
          if (!bar) return;
          var w = parseFloat(bar.style.width) || 20;
          if (w < 90) bar.style.width = w + 5 + '%';
        }, 200);

        fetch(config.bulkSaveRoute, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken(),
          },
          body: JSON.stringify({ rows: pendingSaveRows }),
        })
          .then(function (res) {
            return res.text().then(function (text) {
              var data = {};
              try {
                data = text ? JSON.parse(text) : {};
              } catch (e) {
                throw new Error('Invalid save response from server.');
              }
              return { ok: res.ok, data: data };
            });
          })
          .then(function (pack) {
            clearInterval(progressTimer);
            setProgress(
              'import-save-progress-wrap',
              'import-save-progress-bar',
              'import-save-progress-pct',
              'import-save-progress-label',
              100,
              'Save complete'
            );

            if (!pack.ok) {
              var errMsg = extractErrorMessage({ data: pack.data, message: pack.data.message });
              showStatus(errMsg, 'danger');
              notifyError('Save failed', errMsg);
              return;
            }

            var s = pack.data.summary || {};
            var html =
              '<ul class="text-start mb-0">' +
              '<li>Processed: <strong>' +
              (s.processed || 0) +
              '</strong></li>' +
              '<li>Inserted: <strong>' +
              (s.inserted || 0) +
              '</strong></li>' +
              '<li>Updated: <strong>' +
              (s.updated || 0) +
              '</strong></li>' +
              '<li>Failed: <strong>' +
              (s.failed || 0) +
              '</strong></li>' +
              '</ul>';

            if (resultSummary) {
              resultSummary.className = 'alert alert-success mt-3';
              resultSummary.innerHTML = html;
              resultSummary.classList.remove('d-none');
            }

            showStatus('Import saved successfully.', 'success');
            notifySuccess('Import saved', html);
          })
          .catch(function (err) {
            clearInterval(progressTimer);
            hideProgress('import-save-progress-wrap', 'import-save-progress-bar');
            var msg = err.message || 'Network error while saving.';
            showStatus(msg, 'danger');
            notifyError('Save failed', msg);
          })
          .finally(function () {
            setBtnLoading(bulkSaveBtn, false);
            setTimeout(function () {
              hideProgress('import-save-progress-wrap', 'import-save-progress-bar');
            }, 800);
          });
      });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initImportPage);
  } else {
    initImportPage();
  }
})();
