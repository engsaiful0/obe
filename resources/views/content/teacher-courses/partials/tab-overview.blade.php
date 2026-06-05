@php $info = $dashboard['course_info'] ?? []; @endphp
<div class="row g-4">
    <div class="col-md-4">
        <h6 class="text-primary">{{ __('Course Information') }}</h6>
        <table class="table table-sm table-borderless">
            <tr><th class="w-50">{{ __('Course Code') }}</th><td>{{ $info['course_code'] ?? '-' }}</td></tr>
            <tr><th>{{ __('Course Title') }}</th><td>{{ $info['course_title'] ?? '-' }}</td></tr>
            <tr><th>{{ __('Credit Hours') }}</th><td>{{ $info['credit_hours'] ?? '-' }}</td></tr>
            <tr><th>{{ __('Program') }}</th><td>{{ $info['program'] ?? '-' }}</td></tr>
        </table>
    </div>
    <div class="col-md-4">
        <h6 class="text-primary">{{ __('Teacher Information') }}</h6>
        <table class="table table-sm table-borderless">
            <tr><th class="w-50">{{ __('Teacher Name') }}</th><td>{{ $info['teacher_name'] ?? '-' }}</td></tr>
        </table>
        <h6 class="text-primary mt-3">{{ __('Batch & Section') }}</h6>
        <table class="table table-sm table-borderless">
            <tr><th class="w-50">{{ __('Batch') }}</th><td>{{ $info['batch'] ?? '-' }}</td></tr>
            <tr><th>{{ __('Section') }}</th><td>{{ $info['section'] ?? '-' }}</td></tr>
            <tr><th>{{ __('Semester') }}</th><td>{{ $info['semester'] ?? '-' }}</td></tr>
        </table>
    </div>
    <div class="col-md-4">
        <h6 class="text-primary">{{ __('Academic Session') }}</h6>
        <table class="table table-sm table-borderless">
            <tr><th class="w-50">{{ __('Session') }}</th><td>{{ $info['academic_session'] ?? '-' }}</td></tr>
            <tr><th>{{ __('Academic Year') }}</th><td>{{ $info['academic_year'] ?? '-' }}</td></tr>
        </table>
        <h6 class="text-primary mt-3">{{ __('Status') }}</h6>
        <table class="table table-sm table-borderless">
            <tr><th class="w-50">{{ __('Attendance') }}</th><td>{{ $dashboard['status']['attendance'] ?? '-' }}</td></tr>
            <tr><th>{{ __('Marks Entry') }}</th><td>{{ $dashboard['status']['marks_entry'] ?? '-' }}</td></tr>
            <tr><th>{{ __('Grade Submission') }}</th><td>{{ $dashboard['status']['grade_submission'] ?? '-' }}</td></tr>
        </table>
    </div>
</div>
