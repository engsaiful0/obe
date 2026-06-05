@include('content.teacher-courses.partials.plo-attainment-live', [
    'ploAttainment' => $dashboard['plo_attainment'] ?? ['rows' => [], 'chart' => []],
])

<div class="row g-3 mt-2">
    <div class="col-md-6">
        <a href="{{ route('teacher-courses.reports', $assignment) }}" class="btn btn-sm btn-outline-primary">{{ __('Attainment Report') }}</a>
        <a href="{{ route('my-courses.course-file', $assignment) }}" class="btn btn-sm btn-outline-warning">{{ __('Course File PLO Section') }}</a>
    </div>
</div>
