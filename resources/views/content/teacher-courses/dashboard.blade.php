@extends('layouts/layoutMaster')

@section('title', __('Course Dashboard'))

@php
    $info = $dashboard['course_info'] ?? [];
    $readonly = (bool) ($dashboard['is_readonly'] ?? false);
    $courseTitle = ($info['course_code'] ?? '') . ' - ' . ($info['course_title'] ?? '');
@endphp

@section('content')
    @include('content.teacher-courses.partials.breadcrumb', [
        'items' => [
            ['label' => __('Course'), 'url' => route('teacher-courses.assigned')],
            ['label' => $readonly ? __('Previous Semester Courses') : __('Current Semester Courses'), 'url' => $readonly ? route('teacher-courses.previous') : route('teacher-courses.current')],
            ['label' => __('Course Dashboard')],
        ],
    ])

    @if ($readonly)
        <div class="alert alert-warning py-2">
            <i class="ti ti-lock"></i> {{ __('This is a previous semester course. All data is read-only.') }}
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <h4 class="mb-1">{{ $courseTitle }}</h4>
                    <p class="text-muted mb-0">
                        {{ $info['program'] ?? '' }} &middot; {{ $info['academic_session'] ?? '' }}
                        @if (!empty($info['academic_year'])) ({{ $info['academic_year'] }}) @endif
                        &middot; {{ __('Section') }}: {{ $info['section'] ?? '-' }}
                    </p>
                </div>
                <span class="badge bg-label-primary">{{ $info['teacher_name'] ?? '' }}</span>
            </div>
        </div>
    </div>

    @include('content.teacher-courses.partials.dashboard-stats', ['dashboard' => $dashboard])
    @include('content.teacher-courses.partials.dashboard-quick-actions', ['dashboard' => $dashboard, 'assignment' => $assignment])

    <div class="card">
        <div class="card-header border-bottom">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                @foreach ([
                    'overview' => __('Overview'),
                    'students' => __('Students'),
                    'attendance' => __('Attendance'),
                    'assessments' => __('Assessments'),
                    'grade-sheet' => __('Grade Sheet'),
                    'clo' => __('CLO Assessment'),
                    'plo' => __('PLO Assessment'),
                    'reports' => __('Reports'),
                    'course-file' => __('Course File'),
                ] as $key => $label)
                    <li class="nav-item">
                        <a class="nav-link {{ ($tab ?? 'overview') === $key ? 'active' : '' }}"
                            href="{{ route('teacher-courses.dashboard', ['courseAssignment' => $assignment, 'tab' => $key]) }}">
                            {{ $label }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body">
            @switch($tab ?? 'overview')
                @case('students')
                    @include('content.teacher-courses.partials.tab-students', ['assignment' => $assignment, 'dashboard' => $dashboard])
                    @break
                @case('attendance')
                    @include('content.teacher-courses.partials.tab-attendance', ['assignment' => $assignment, 'dashboard' => $dashboard, 'readonly' => $readonly])
                    @break
                @case('assessments')
                    @include('content.teacher-courses.partials.tab-assessments', ['assignment' => $assignment, 'readonly' => $readonly])
                    @break
                @case('grade-sheet')
                    <div class="text-center py-3">
                        <a href="{{ route('my-courses.grade-sheet', $assignment) }}" class="btn btn-primary">
                            <i class="ti ti-table"></i> {{ __('Open Grade Sheet') }}
                        </a>
                    </div>
                    @break
                @case('clo')
                    @include('content.teacher-courses.partials.tab-clo', ['assignment' => $assignment, 'readonly' => $readonly])
                    @break
                @case('plo')
                    @include('content.teacher-courses.partials.tab-plo', ['assignment' => $assignment, 'readonly' => $readonly])
                    @break
                @case('reports')
                    @include('content.teacher-courses.partials.tab-reports', ['assignment' => $assignment, 'readonly' => $readonly])
                    @break
                @case('course-file')
                    <div class="text-center py-3">
                        <a href="{{ route('my-courses.course-file', $assignment) }}" class="btn btn-warning">
                            <i class="ti ti-folder"></i> {{ __('Open Course File') }}
                        </a>
                    </div>
                    @break
                @default
                    @include('content.teacher-courses.partials.tab-overview', ['dashboard' => $dashboard])
            @endswitch
        </div>
    </div>
@endsection

@section('page-script')
    @if (($tab ?? 'overview') === 'students')
        <script>
            window.__teacherCourseStudentsRoute = @json(route('teacher-courses.students.json', $assignment));
        </script>
        <script src="{{ asset('assets/js/teacher-course-dashboard.js') }}"></script>
    @endif
@endsection
