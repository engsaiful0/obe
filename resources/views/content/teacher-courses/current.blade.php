@extends('layouts/layoutMaster')

@section('title', __('Current Semester Courses'))

@section('content')
    @include('content.teacher-courses.partials.list-shell', [
        'title' => __('Current Semester Courses'),
        'tablePartial' => 'content.teacher-courses.partials.current-table',
        'scope' => 'current',
        'breadcrumbItems' => [
            ['label' => __('Course')],
            ['label' => __('Current Semester Courses')],
        ],
    ])
@endsection

@section('page-script')
    <script>
        window.__teacherCoursesRoutes = {
            list: @json(route('teacher-courses.current'))
        };
    </script>
    <script src="{{ asset('assets/js/teacher-courses.js') }}"></script>
@endsection
