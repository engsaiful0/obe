@extends('layouts/layoutMaster')

@section('title', __('Student List'))

@section('content')
    @include('content.teacher-courses.partials.breadcrumb', [
        'items' => [
            ['label' => __('Course'), 'url' => route('teacher-courses.assigned')],
            ['label' => __('Course Dashboard'), 'url' => route('teacher-courses.dashboard', $courseAssignment)],
            ['label' => __('Students')],
        ],
    ])

    @php $info = $dashboard['course_info'] ?? []; @endphp
    <div class="card mb-3">
        <div class="card-body py-3 d-flex flex-wrap justify-content-between gap-2">
            <div>
                <h5 class="mb-1">{{ ($info['course_code'] ?? '') . ' — ' . ($info['course_title'] ?? '') }}</h5>
                <small class="text-muted">{{ $info['program'] ?? '' }} · {{ $info['academic_session'] ?? '' }}</small>
            </div>
            <a href="{{ route('teacher-courses.dashboard', $courseAssignment) }}" class="btn btn-sm btn-outline-primary">{{ __('Course Dashboard') }}</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">{{ __('Student List') }}</h5></div>
        <div class="card-body">
            @include('content.teacher-courses.partials.tab-students', ['assignment' => $courseAssignment, 'dashboard' => $dashboard])
        </div>
    </div>
@endsection

@section('page-script')
    <script>window.__teacherCourseStudentsRoute = @json(route('teacher-courses.students.json', $courseAssignment));</script>
    <script src="{{ asset('assets/js/teacher-course-dashboard.js') }}"></script>
@endsection
