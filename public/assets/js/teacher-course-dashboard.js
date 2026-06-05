(function () {
  function initDashboardStudents() {
    var route = window.__teacherCourseStudentsRoute;
    var tbody = document.querySelector('#dashboard-students-table tbody');
    var search = document.getElementById('dashboard-student-search');
    if (!route || !tbody) return;

    function render(students) {
      tbody.innerHTML = '';
      (students || []).forEach(function (s, idx) {
        var tr = document.createElement('tr');
        tr.innerHTML =
          '<td>' + (idx + 1) + '</td>' +
          '<td>' + (s.student_code || '') + '</td>' +
          '<td>' + (s.student_name || '') + '</td>' +
          '<td>' + (s.batch_name || '') + '</td>' +
          '<td class="text-end">' + (s.attendance_percentage != null ? Number(s.attendance_percentage).toFixed(1) : '-') + '</td>' +
          '<td class="text-end">' + (s.total_marks != null ? Number(s.total_marks).toFixed(2) : '-') + '</td>' +
          '<td>' + (s.grade || '-') + '</td>';
        tbody.appendChild(tr);
      });
    }

    function load() {
      var q = search ? '?search=' + encodeURIComponent(search.value || '') : '';
      fetch(route + q, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin'
      })
        .then(function (res) { return res.json(); })
        .then(function (data) { render(data.students || []); });
    }

    var timer;
    if (search) {
      search.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(load, 300);
      });
    }
    load();
  }

  document.addEventListener('DOMContentLoaded', initDashboardStudents);
})();
