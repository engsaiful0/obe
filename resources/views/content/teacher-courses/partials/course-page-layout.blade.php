@php
    $title = $title ?? __('Course');
    $info = $dashboard['course_info'] ?? [];
@endphp
@include('content.teacher-courses.partials.breadcrumb', ['items' => $breadcrumbItems ?? []])

<div class="card mb-3">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ ($info['course_code'] ?? '') . ' — ' . ($info['course_title'] ?? '') }}</h5>
                <small class="text-muted">{{ $info['program'] ?? '' }} · {{ $info['academic_session'] ?? '' }}</small>
            </div>
            <a href="{{ route('teacher-courses.dashboard', $courseAssignment) }}" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-layout-dashboard"></i> {{ __('Course Dashboard') }}
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">{{ $title }}</h5></div>
    <div class="card-body">@yield('course-page-body')</div>
</div>
