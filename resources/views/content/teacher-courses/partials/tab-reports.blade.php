<div class="row g-2">
    @php
        $reports = [
            ['label' => __('Attendance Report'), 'route' => route('teacher-courses.attendance', $assignment)],
            ['label' => __('Marks Report'), 'route' => route('my-courses.grade-sheet', $assignment)],
            ['label' => __('Grade Distribution'), 'route' => route('my-courses.grade-sheet', $assignment)],
            ['label' => __('CLO Report'), 'route' => route('teacher-courses.clo', $assignment)],
            ['label' => __('PLO Report'), 'route' => route('teacher-courses.plo', $assignment)],
            ['label' => __('Attainment Report'), 'route' => route('teacher-courses.reports', $assignment)],
            ['label' => __('Course File PDF'), 'route' => route('my-courses.course-file.pdf', $assignment)],
            ['label' => __('Grade Sheet Excel'), 'route' => route('my-courses.grade-sheet.excel', $assignment)],
        ];
        if ($readonly ?? false) {
            $reports[] = ['label' => __('CQI Report'), 'route' => route('my-courses.course-file', $assignment)];
            $reports[] = ['label' => __('Export PDF'), 'route' => route('teacher-courses.export-previous-pdf', $assignment)];
        }
    @endphp
    @foreach ($reports as $report)
        <div class="col-md-6 col-lg-4">
            <a href="{{ $report['route'] }}" class="btn btn-outline-primary w-100 text-start">
                <i class="ti ti-file-report me-1"></i> {{ $report['label'] }}
            </a>
        </div>
    @endforeach
</div>
