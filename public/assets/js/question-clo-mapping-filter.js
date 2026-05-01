/**
 * AJAX index filters + pagination for Question–CLO mappings (no full reload).
 */
(function () {
  function csrfToken() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  function buildQuery(form) {
    var fd = new FormData(form);
    var params = [];
    fd.forEach(function (val, key) {
      if (key === 'page') {
        return;
      }
      if (val === null || String(val).trim() === '') {
        return;
      }
      params.push(encodeURIComponent(key) + '=' + encodeURIComponent(val));
    });
    return params.join('&');
  }

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('question-clo-filter-form');
    var wrapper = document.getElementById('question-clo-mapping-table-wrapper');
    var inner = document.getElementById('question-clo-mapping-table-inner');
    var overlay = document.getElementById('qcm-loading-overlay');

    if (!form || !inner || !wrapper) {
      return;
    }

    var baseUrl = form.getAttribute('action').split('?')[0];

    function setOverlay(on) {
      if (!overlay) {
        return;
      }
      overlay.classList.toggle('d-none', !on);
    }

    function loadTable(urlStr) {
      setOverlay(true);
      fetch(urlStr, {
        method: 'GET',
        headers: {
          Accept: 'text/html, application/xhtml+xml',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': csrfToken()
        },
        credentials: 'same-origin'
      })
        .then(function (res) {
          if (!res.ok) {
            throw new Error('bad status');
          }
          return res.text();
        })
        .then(function (html) {
          inner.innerHTML = html;
          if (history && history.pushState) {
            try {
              var u = new URL(urlStr, window.location.href);
              history.pushState({}, '', u.pathname + u.search);
            } catch (e) {}
          }
        })
        .catch(function () {
          if (typeof toastr !== 'undefined') {
            toastr.error('Failed to load filtered results.');
          }
        })
        .finally(function () {
          setOverlay(false);
        });
    }

    function loadFromFilters(resetPage) {
      var qs = buildQuery(form);
      if (resetPage) {
        qs = qs.replace(/(^|&)page=\d+/g, '');
      }
      var url = qs ? baseUrl + '?' + qs : baseUrl;
      loadTable(url);
    }

    var debTimer;
    function debouncedLoadFromFilters() {
      clearTimeout(debTimer);
      debTimer = setTimeout(function () {
        loadFromFilters(true);
      }, 500);
    }

    form.querySelectorAll('.question-clo-filter').forEach(function (el) {
      el.addEventListener('change', function () {
        loadFromFilters(true);
      });
    });

    var qInput = document.getElementById('q');
    if (qInput) {
      ['input', 'keyup', 'search'].forEach(function (ev) {
        qInput.addEventListener(ev, debouncedLoadFromFilters);
      });
    }

    inner.addEventListener('click', function (e) {
      var a = e.target.closest('.pagination a[href]');
      if (!a || !inner.contains(a)) {
        return;
      }
      e.preventDefault();
      loadTable(a.href);
    });

    window.addEventListener('popstate', function () {
      loadTable(location.pathname + (location.search || ''));
    });
  });
})();
