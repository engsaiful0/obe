<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Course File Print') }}</title>
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
    <style>@media print { .no-print { display: none; } }</style>
</head>
<body class="p-4">
    <div class="no-print mb-3">
        <button onclick="window.print()" class="btn btn-primary btn-sm">{{ __('Print') }}</button>
    </div>
    @php $section = $printSection ?? 'info'; @endphp
    @if ($section === 'info')
        @include('content.my-courses.course-file.sections.info', ['courseInfo' => $courseInfo, 'courseAssignment' => $courseAssignment])
    @else
        <p>{{ __('Open the full Course File for this section.') }}</p>
    @endif
</body>
</html>
