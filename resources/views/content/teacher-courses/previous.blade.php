@extends('layouts/layoutMaster')

@section('title', __('Previous Semester Courses'))

@section('content')
    <div class="alert alert-info no-print mb-3">
        <i class="ti ti-lock"></i> {{ __('Previous semester courses are read-only. Marks and attendance cannot be modified.') }}
    </div>
    @include('content.teacher-courses.partials.list-shell', [
        'title' => __('Previous Semester Courses'),
        'tablePartial' => 'content.teacher-courses.partials.previous-table',
        'scope' => 'previous',
        'breadcrumbItems' => [
            ['label' => __('Course')],
            ['label' => __('Previous Semester Courses')],
        ],
    ])
@endsection

@section('page-script')
    <script>
        window.__teacherCoursesRoutes = {
            list: @json(route('teacher-courses.previous'))
        };
    </script>
    <script src="{{ asset('assets/js/teacher-courses.js') }}"></script>
@endsection
