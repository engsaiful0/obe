<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('Grade Sheet') }} — {{ __('Print') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
        h4, h6 { margin: 10px 0 6px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 12px;">
        <button type="button" onclick="window.print()">{{ __('Print') }}</button>
    </div>
    @include('content.my-courses.partials.grade-sheet-content')
</body>
</html>
