@extends('layouts/layoutMaster')

@section('title', __('Attendance'))

@section('content')
    @include('content.teacher-courses.partials.breadcrumb', [
        'items' => [
            ['label' => __('Course'), 'url' => route('teacher-courses.assigned')],
            ['label' => __('Course Dashboard'), 'url' => route('teacher-courses.dashboard', $courseAssignment)],
            ['label' => __('Attendance')],
        ],
    ])

    @php $info = $dashboard['course_info'] ?? []; @endphp
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('Attendance') }} — {{ $info['course_code'] ?? '' }}</h5>
            <a href="{{ route('teacher-courses.dashboard', $courseAssignment) }}" class="btn btn-sm btn-outline-primary">{{ __('Dashboard') }}</a>
        </div>
        <div class="card-body">
            @include('content.teacher-courses.partials.tab-attendance', [
                'assignment' => $courseAssignment,
                'dashboard' => $dashboard,
                'readonly' => $readonly ?? false,
            ])
        </div>
    </div>
@endsection
