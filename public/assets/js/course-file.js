/**
 * Course File — AJAX uploads, CQI save, charts.
 */
(function () {
  'use strict';

  var app = document.getElementById('course-file-app');
  if (!app) return;

  var uploadUrl = app.dataset.uploadUrl || '';
  var cqiUrl = app.dataset.cqiUrl || '';
  var csrf = app.dataset.csrf || '';
  var canManage = app.dataset.canManage === '1';
  var deleteUrlTemplate = uploadUrl.replace(/\/documents$/, '/documents/');

  function showAlert(msg, type) {
    var el = document.getElementById('cf-status-alert');
    if (!el) return;
    el.className = 'alert alert-' + (type || 'info');
    el.textContent = msg;
    el.classList.remove('d-none');
    setTimeout(function () { el.classList.add('d-none'); }, 4000);
  }

  function updateCompletion(pct) {
    var bar = document.getElementById('cf-completion-bar');
    var label = document.getElementById('cf-completion-label');
    if (bar) bar.style.width = Math.min(100, pct) + '%';
    if (label) label.textContent = pct + '%';
  }

  function uploadFile(type, file, videoUrl) {
    var fd = new FormData();
    fd.append('document_type', type);
    fd.append('_token', csrf);
    if (videoUrl) {
      fd.append('video_url', videoUrl);
    } else if (file) {
      fd.append('file', file);
    } else {
      return;
    }

    var block = app.querySelector('.cf-doc-block[data-document-type="' + type + '"]');
    var progressWrap = block ? block.querySelector('.cf-upload-progress') : null;
    var progressBar = progressWrap ? progressWrap.querySelector('.progress-bar') : null;
    if (progressWrap) progressWrap.classList.remove('d-none');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', uploadUrl, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.upload.onprogress = function (e) {
      if (e.lengthComputable && progressBar) {
        progressBar.style.width = Math.round((e.loaded / e.total) * 100) + '%';
      }
    };
    xhr.onload = function () {
      if (progressWrap) progressWrap.classList.add('d-none');
      if (progressBar) progressBar.style.width = '0%';
      var data = {};
      try { data = JSON.parse(xhr.responseText || '{}'); } catch (err) {}
      if (xhr.status >= 200 && xhr.status < 300) {
        showAlert(data.message || 'Uploaded', 'success');
        if (data.completion_percent != null) updateCompletion(data.completion_percent);
        location.reload();
      } else {
        showAlert(data.message || 'Upload failed', 'danger');
      }
    };
    xhr.onerror = function () { showAlert('Network error', 'danger'); };
    xhr.send(fd);
  }

  if (canManage) {
    app.querySelectorAll('.cf-upload-trigger').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var type = btn.dataset.type;
        var isVideo = btn.dataset.video === '1';
        if (isVideo) {
          var url = prompt('Enter video URL:');
          if (url) uploadFile(type, null, url);
          return;
        }
        var input = app.querySelector('.cf-file-input[data-type="' + type + '"]');
        if (input) input.click();
      });
    });

    app.querySelectorAll('.cf-file-input').forEach(function (input) {
      input.addEventListener('change', function () {
        if (input.files && input.files[0]) uploadFile(input.dataset.type, input.files[0], null);
        input.value = '';
      });
    });

    app.querySelectorAll('.cf-dropzone').forEach(function (zone) {
      var type = zone.dataset.dropType;
      zone.classList.remove('d-none');
      zone.addEventListener('dragover', function (e) { e.preventDefault(); zone.classList.add('bg-light'); });
      zone.addEventListener('dragleave', function () { zone.classList.remove('bg-light'); });
      zone.addEventListener('drop', function (e) {
        e.preventDefault();
        zone.classList.remove('bg-light');
        if (e.dataTransfer.files && e.dataTransfer.files[0]) uploadFile(type, e.dataTransfer.files[0], null);
      });
    });

    app.querySelectorAll('.cf-delete-doc').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm('Delete this document?')) return;
        var id = btn.dataset.id;
        fetch(deleteUrlTemplate + id, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
        })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            showAlert(data.message || 'Deleted', 'success');
            if (data.completion_percent != null) updateCompletion(data.completion_percent);
            btn.closest('li')?.remove();
          })
          .catch(function () { showAlert('Delete failed', 'danger'); });
      });
    });

    var cqiForm = document.getElementById('cf-cqi-form');
    if (cqiForm && cqiUrl) {
      cqiForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var body = {};
        ['strengths', 'weaknesses', 'problems', 'improvements', 'recommendations'].forEach(function (k) {
          var el = cqiForm.querySelector('[name="' + k + '"]');
          if (el) body[k] = el.value;
        });
        fetch(cqiUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify(body),
        })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            showAlert(data.message || 'Saved', 'success');
            if (data.completion_percent != null) updateCompletion(data.completion_percent);
          })
          .catch(function () { showAlert('Save failed', 'danger'); });
      });
    }
  }

  function initCharts() {
    if (typeof Chart === 'undefined' || !window.__courseFileCharts) return;
    var clo = window.__courseFileCharts.clo || {};
    if (clo.labels && clo.labels.length && document.getElementById('cf-clo-bar-chart')) {
      new Chart(document.getElementById('cf-clo-bar-chart'), {
        type: 'bar',
        data: {
          labels: clo.labels,
          datasets: [
            { label: 'Achieved %', data: clo.achieved, backgroundColor: '#28a745' },
            { label: 'Target %', data: clo.target, backgroundColor: '#ffc107' },
          ],
        },
        options: { responsive: true, scales: { y: { max: 100 } } },
      });
      new Chart(document.getElementById('cf-clo-pie-chart'), {
        type: 'pie',
        data: {
          labels: clo.labels,
          datasets: [{ data: clo.achieved, backgroundColor: ['#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f'] }],
        },
      });
    }
    var plo = window.__courseFileCharts.plo || {};
    if (plo.labels && plo.labels.length && document.getElementById('cf-plo-bar-chart')) {
      new Chart(document.getElementById('cf-plo-bar-chart'), {
        type: 'bar',
        data: { labels: plo.labels, datasets: [{ label: 'Achieved %', data: plo.achieved, backgroundColor: '#007bff' }] },
        options: { responsive: true, scales: { y: { max: 100 } } },
      });
    }
    var grades = window.__courseFileCharts.grades || {};
    var gLabels = Object.keys(grades);
    if (gLabels.length && document.getElementById('cf-grade-pie-chart')) {
      new Chart(document.getElementById('cf-grade-pie-chart'), {
        type: 'pie',
        data: {
          labels: gLabels,
          datasets: [{ data: gLabels.map(function (k) { return grades[k]; }), backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545', '#6c757d'] }],
        },
      });
    }
  }

  document.addEventListener('DOMContentLoaded', initCharts);
})();
