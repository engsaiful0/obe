<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Course Summary') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
    </style>
</head>
<body>
    @php $info = $dashboard['course_info'] ?? []; @endphp
    <h2>{{ $info['course_code'] ?? '' }} — {{ $info['course_title'] ?? '' }}</h2>
    <p>{{ $info['program'] ?? '' }} | {{ $info['academic_session'] ?? '' }} | {{ __('Section') }}: {{ $info['section'] ?? '' }}</p>
    <table>
        <tr><th>{{ __('Teacher') }}</th><td>{{ $info['teacher_name'] ?? '' }}</td></tr>
        <tr><th>{{ __('Batch') }}</th><td>{{ $info['batch'] ?? '' }}</td></tr>
        <tr><th>{{ __('Credit Hours') }}</th><td>{{ $info['credit_hours'] ?? '' }}</td></tr>
        <tr><th>{{ __('Total Students') }}</th><td>{{ (int) ($dashboard['quick_stats']['total_students'] ?? 0) }}</td></tr>
        <tr><th>{{ __('Marks Entry Progress') }}</th><td>{{ number_format((float) ($dashboard['quick_stats']['marks_entry_progress'] ?? 0), 1) }}%</td></tr>
        <tr><th>{{ __('Grade Submission') }}</th><td>{{ $dashboard['status']['grade_submission'] ?? '' }}</td></tr>
    </table>
</body>
</html>
