<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Course File') }} — {{ $courseInfo['course_code'] ?? '' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin-top: 18px; border-bottom: 1px solid #ccc; padding-bottom: 4px; page-break-after: avoid; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f0f0f0; }
        .toc { margin: 12px 0; }
        .toc li { margin: 4px 0; }
        .page-break { page-break-before: always; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #666; }
    </style>
</head>
<body>
    <h1>{{ __('Course File') }}</h1>
    <p>{{ $courseInfo['course_code'] }} — {{ $courseInfo['course_title'] }} | {{ $courseInfo['instructor'] }}</p>

    <h2>{{ __('Table of Contents') }}</h2>
    <ol class="toc">
        @foreach (range(1, 12) as $n)
            <li>{{ __('Section') }} {{ $n }}</li>
        @endforeach
    </ol>

    <h2>1. {{ __('Course Information') }}</h2>
    @include('content.my-courses.course-file.sections.info', ['courseInfo' => $courseInfo, 'courseAssignment' => $courseAssignment])

    <h2 class="page-break">3. {{ __('CLO Management') }}</h2>
    @include('content.my-courses.course-file.sections.clos', ['clos' => $clos])

    <h2>4. {{ __('Assessment Plan') }}</h2>
    @include('content.my-courses.course-file.sections.assessments', ['assessments' => $assessments])

    <h2 class="page-break">6. {{ __('Mark Distribution') }}</h2>
    @include('content.my-courses.course-file.sections.mark-distribution', ['markDistribution' => $markDistribution])

    <h2>7. {{ __('Student Marks') }}</h2>
    @include('content.my-courses.course-file.sections.student-marks', [
        'marksRows' => array_slice($marksRows, 0, 50),
        'markDistribution' => $markDistribution,
        'courseAssignment' => $courseAssignment,
    ])

    <h2 class="page-break">9. {{ __('CLO Attainment') }}</h2>
    @include('content.my-courses.course-file.sections.clo-attainment', ['cloAttainment' => $cloAttainment])

    <h2>10. {{ __('PLO Attainment') }}</h2>
    @include('content.my-courses.course-file.sections.plo-attainment', ['ploAttainment' => $ploAttainment])

    <h2>15. {{ __('CQI') }}</h2>
    @if ($courseFile->cqi)
        <p><strong>{{ __('Strengths') }}:</strong> {{ $courseFile->cqi->strengths }}</p>
        <p><strong>{{ __('Weaknesses') }}:</strong> {{ $courseFile->cqi->weaknesses }}</p>
        <p><strong>{{ __('Improvements') }}:</strong> {{ $courseFile->cqi->improvements }}</p>
    @endif

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script('if ($PAGE_COUNT > 0) { $font = $fontMetrics->get_font("DejaVu Sans", "normal"); $pdf->text(520, 820, "Page " . $PAGE_NUM . " of " . $PAGE_COUNT, $font, 8); }');
        }
    </script>
</body>
</html>
