<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Grade Sheet') }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; margin: 0; padding: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: left; }
        th { background: #f0f0f0; }
        h4, h6 { margin: 8px 0 4px; }
    </style>
</head>
<body>
    @include('content.my-courses.partials.grade-sheet-content')
</body>
</html>
