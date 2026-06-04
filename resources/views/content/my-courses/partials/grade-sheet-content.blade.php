@php
    $assignment = $report['assignment'] ?? null;
    $summary = $report['summary'] ?? [];
    $rows = $report['rows'] ?? [];
    $gradeScale = $report['grade_scale'] ?? collect();
    $sectionLabel = trim(($assignment?->section?->section_code ?? '') . ' ' . ($assignment?->section?->section_name ?? ''));
    $batchText = implode(', ', $report['batch_labels'] ?? []);
@endphp
<div class="grade-sheet-report">
    <div class="text-center mb-3">
        <h4 class="mb-1">{{ $appSettings?->university_name ?? config('app.name') }}</h4>
        <p class="mb-0">{{ $assignment?->program?->department?->name ?? __('Department') }}</p>
    </div>
    <table class="table table-sm table-borderless mb-3">
        <tr>
            <td><strong>{{ __('Course Code') }}:</strong> {{ $assignment?->course?->course_code }}</td>
            <td><strong>{{ __('Course Name') }}:</strong> {{ $assignment?->course?->course_title }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Academic Session') }}:</strong> {{ $assignment?->academicSession?->session_name }}</td>
            <td><strong>{{ __('Program') }}:</strong> {{ $assignment?->program?->program_name }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Batch') }}:</strong> {{ $batchText !== '' ? $batchText : '-' }}</td>
            <td><strong>{{ __('Section') }}:</strong> {{ $sectionLabel !== '' ? $sectionLabel : '-' }}</td>
        </tr>
        <tr>
            <td><strong>{{ __('Teacher') }}:</strong> {{ $assignment?->teacher?->teacher_name }}</td>
            <td><strong>{{ __('Generated') }}:</strong> {{ ($generatedAt ?? now())->format('d M Y H:i') }}</td>
        </tr>
    </table>

    <h6>{{ __('Grade Table') }}</h6>
    <table class="table table-sm table-bordered w-auto mb-3">
        <thead class="table-light">
            <tr><th>{{ __('Grade') }}</th><th>{{ __('Min') }}</th><th>{{ __('Max') }}</th><th>{{ __('Point') }}</th></tr>
        </thead>
        <tbody>
            @foreach ($gradeScale as $grade)
                <tr>
                    <td>{{ $grade->grade_name }}</td>
                    <td>{{ $grade->from_marks }}</td>
                    <td>{{ $grade->to_marks }}</td>
                    <td>{{ $grade->grade_point }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h6>{{ __('Grade Sheet') }}</h6>
    <table class="table table-sm table-bordered mb-3">
        <thead class="table-light">
            <tr>
                <th>{{ __('Student ID') }}</th>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Reg. No') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Total') }}</th>
                <th>{{ __('%') }}</th>
                <th>{{ __('Grade') }}</th>
                <th>{{ __('GPA') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['student_id'] }}</td>
                    <td>{{ $row['student_code'] }}</td>
                    <td>{{ $row['registration_no'] ?: '-' }}</td>
                    <td>{{ $row['student_name'] }}</td>
                    <td>{{ number_format((float) $row['total_marks'], 2) }}</td>
                    <td>{{ number_format((float) $row['total_marks_percentage'], 2) }}</td>
                    <td>{{ $row['total_marks_grade_name'] ?? '-' }}</td>
                    <td>{{ $row['total_marks_grade_points'] !== null ? number_format((float) $row['total_marks_grade_points'], 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="row">
        <div class="col-md-6">
            <h6>{{ __('Summary') }}</h6>
            <ul class="list-unstyled">
                <li>{{ __('Total Students') }}: {{ $summary['total_students'] ?? 0 }}</li>
                <li>{{ __('Passed') }}: {{ $summary['passed_students'] ?? 0 }}</li>
                <li>{{ __('Failed') }}: {{ $summary['failed_students'] ?? 0 }}</li>
                <li>{{ __('Highest') }}: {{ number_format((float) ($summary['highest_marks'] ?? 0), 2) }}</li>
                <li>{{ __('Lowest') }}: {{ number_format((float) ($summary['lowest_marks'] ?? 0), 2) }}</li>
                <li>{{ __('Average Marks') }}: {{ number_format((float) ($summary['average_marks'] ?? 0), 2) }}</li>
                <li>{{ __('Average GPA') }}: {{ number_format((float) ($summary['average_gpa'] ?? 0), 2) }}</li>
            </ul>
        </div>
        <div class="col-md-6">
            <h6>{{ __('Grade Distribution') }}</h6>
            <table class="table table-sm table-bordered w-auto">
                <thead><tr><th>{{ __('Grade') }}</th><th>{{ __('Count') }}</th></tr></thead>
                <tbody>
                    @foreach ($summary['grade_distribution'] ?? [] as $gradeName => $count)
                        <tr><td>{{ $gradeName }}</td><td>{{ $count }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
