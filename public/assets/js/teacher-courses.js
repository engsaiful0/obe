(function () {
  function initTeacherCourseList() {
    var routes = window.__teacherCoursesRoutes || {};
    var search = document.getElementById('teacher-course-search');
    var sort = document.getElementById('teacher-course-sort');
    var direction = document.getElementById('teacher-course-direction');
    var container = document.getElementById('teacher-course-table-container');
    var loading = document.getElementById('teacher-course-loading');
    if (!routes.list || !container) return;

    function buildUrl(extra) {
      var params = new URLSearchParams();
      if (search && search.value) params.set('search', search.value);
      if (sort && sort.value) params.set('sort', sort.value);
      if (direction && direction.value) params.set('direction', direction.value);
      if (extra) {
        Object.keys(extra).forEach(function (k) {
          params.set(k, extra[k]);
        });
      }
      var qs = params.toString();
      return routes.list + (qs ? '?' + qs : '');
    }

    function loadPage(url) {
      if (loading) loading.classList.remove('d-none');
      fetch(url, {
        headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
      })
        .then(function (res) { return res.text(); })
        .then(function (html) {
          container.innerHTML = html;
        })
        .finally(function () {
          if (loading) loading.classList.add('d-none');
        });
    }

    var timer;
    if (search) {
      search.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { loadPage(buildUrl()); }, 300);
      });
    }
    if (sort) sort.addEventListener('change', function () { loadPage(buildUrl()); });
    if (direction) direction.addEventListener('change', function () { loadPage(buildUrl()); });

    container.addEventListener('click', function (e) {
      var link = e.target.closest('.teacher-course-pagination a');
      if (!link) return;
      e.preventDefault();
      loadPage(link.href);
    });
  }

  document.addEventListener('DOMContentLoaded', initTeacherCourseList);
})();
