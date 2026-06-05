@php
    $readonly = (bool) ($dashboard['is_readonly'] ?? false);
    $a = $assignment;
@endphp
<div class="row g-2 mb-4">
    @if (!$readonly)
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <a href="{{ route('teacher-courses.attendance', $a) }}" class="btn btn-outline-primary w-100">
                <i class="ti ti-calendar-plus d-block mb-1"></i> {{ __('Take Attendance') }}
            </a>
        </div>
    @endif
    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
        <a href="{{ route('teacher-courses.attendance', $a) }}" class="btn btn-outline-info w-100">
            <i class="ti ti-report d-block mb-1"></i> {{ __('Attendance Report') }}
        </a>
    </div>
    @if (!$readonly)
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <a href="{{ route('my-courses.marks-entry', $a) }}" class="btn btn-primary w-100">
                <i class="ti ti-edit d-block mb-1"></i> {{ __('Marks Entry') }}
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
            <a href="{{ route('my-courses.import', $a) }}" class="btn btn-success w-100">
                <i class="ti ti-upload d-block mb-1"></i> {{ __('Bulk Import') }}
            </a>
        </div>
    @endif
    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
        <a href="{{ route('my-courses.grade-sheet', $a) }}" class="btn btn-info w-100">
            <i class="ti ti-table d-block mb-1"></i> {{ __('Grade Sheet') }}
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
        <a href="{{ route('my-courses.course-file', $a) }}" class="btn btn-warning w-100">
            <i class="ti ti-folder d-block mb-1"></i> {{ __('Course File') }}
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
        <a href="{{ route('teacher-courses.students', $a) }}" class="btn btn-outline-secondary w-100">
            <i class="ti ti-users d-block mb-1"></i> {{ __('Student List') }}
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
        <a href="{{ route('teacher-courses.clo', $a) }}" class="btn btn-outline-dark w-100">
            <i class="ti ti-chart-bar d-block mb-1"></i> {{ __('CLO Assessment') }}
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
        <a href="{{ route('teacher-courses.plo', $a) }}" class="btn btn-outline-dark w-100">
            <i class="ti ti-chart-pie d-block mb-1"></i> {{ __('PLO Assessment') }}
        </a>
    </div>
    <div class="col-6 col-md-4 col-lg-3 col-xl-2">
        <a href="{{ route('teacher-courses.reports', $a) }}" class="btn btn-outline-danger w-100">
            <i class="ti ti-file-analytics d-block mb-1"></i> {{ __('Reports') }}
        </a>
    </div>
</div>
