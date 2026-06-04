@php $ca = $courseAttainment ?? []; $dist = $ca['grade_distribution'] ?? []; @endphp
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="text-muted small">{{ __('Course Average %') }}</div><div class="fs-4 fw-bold">{{ $ca['course_average'] ?? 0 }}</div></div></div>
    <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="text-muted small">{{ __('GPA Average') }}</div><div class="fs-4 fw-bold">{{ $ca['gpa_average'] ?? 0 }}</div></div></div>
    <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="text-muted small">{{ __('Pass Rate %') }}</div><div class="fs-4 fw-bold">{{ $ca['pass_rate'] ?? 0 }}</div></div></div>
    <div class="col-md-3"><div class="border rounded p-3 text-center"><div class="text-muted small">{{ __('Students Passed') }}</div><div class="fs-4 fw-bold">{{ $ca['passed_students'] ?? 0 }}/{{ $ca['total_students'] ?? 0 }}</div></div></div>
</div>
<canvas id="cf-grade-pie-chart" height="200"></canvas>
