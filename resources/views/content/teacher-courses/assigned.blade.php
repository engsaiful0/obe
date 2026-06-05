@extends('layouts/layoutMaster')

@section('title', __('Assigned Courses'))

@section('content')
    @include('content.teacher-courses.partials.list-shell', [
        'title' => __('Assigned Courses'),
        'tablePartial' => 'content.teacher-courses.partials.assigned-table',
        'exportRoute' => route('teacher-courses.export-assigned'),
        'scope' => 'assigned',
        'breadcrumbItems' => [
            ['label' => __('Course')],
            ['label' => __('Assigned Courses')],
        ],
    ])
@endsection

@section('page-script')
    <script>
        window.__teacherCoursesRoutes = {
            list: @json(route('teacher-courses.assigned'))
        };
    </script>
    <script src="{{ asset('assets/js/teacher-courses.js') }}"></script>
@endsection
