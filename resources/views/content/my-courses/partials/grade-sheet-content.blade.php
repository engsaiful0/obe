@php
    $assignment = $report['assignment'] ?? null;
    $summary = $report['summary'] ?? [];
    $rows = $report['rows'] ?? [];
    $gradeScale = $report['grade_scale'] ?? collect();
    $groupedColumns = $report['grouped_columns'] ?? [];
@endphp
<div class="grade-sheet-report">
    <div class="text-center mb-3">
        <h4 class="mb-1">{{ $appSettings?->university_name ?? config('app.name') }}</h4>
        <p class="mb-0">{{ $assignment?->program?->department?->name ?? __('Department') }}</p>
    </div>

    <table style="width:100%; margin-bottom:12px;">
        <tr>
            <td style="width:50%; vertical-align:top; padding-right:8px;">
                <h6 style="margin:0 0 6px;">{{ __('Student Information Table') }}</h6>
                @include('content.my-courses.partials.grade-sheet-student-info', [
                    'courseAssignment' => $assignment,
                    'report' => $report,
                    'batchLabels' => $report['batch_labels'] ?? [],
                ])
            </td>
            <td style="width:50%; vertical-align:top; padding-left:8px;">
                <h6 style="margin:0 0 6px;">{{ __('Grade Table') }}</h6>
                <table class="table table-sm table-bordered w-100 mb-0">
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
            </td>
        </tr>
    </table>

    <h6>{{ __('Grade Sheet') }}</h6>
    <table class="table table-sm table-bordered mb-3">
        <thead class="table-light">
            <tr>
                <th>{{ __('Serial No.') }}</th>
                <th>{{ __('Student Name') }}</th>
                <th>{{ __('Student Code') }}</th>
                @foreach ($groupedColumns as $group)
                    <th>{{ __($group['label']) }}</th>
                @endforeach
                <th>{{ __('Total') }}</th>
                <th>{{ __('%') }}</th>
                <th>{{ __('Grade') }}</th>
                <th>{{ __('GPA') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['serial'] ?? '' }}</td>
                    <td>{{ $row['student_name'] }}</td>
                    <td>{{ $row['student_code'] ?? '-' }}</td>
                    @foreach ($groupedColumns as $group)
                        <td>{{ number_format((float) ($row[$group['key']] ?? 0), 2) }}</td>
                    @endforeach
                    <td>{{ number_format((float) $row['total_marks'], 2) }}</td>
                    <td>{{ number_format((float) $row['total_marks_percentage'], 2) }}</td>
                    <td>{{ $row['total_marks_grade_name'] ?? '-' }}</td>
                    <td>{{ $row['total_marks_grade_points'] !== null ? number_format((float) $row['total_marks_grade_points'], 2) : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width:100%;">
        <tr>
            <td style="width:50%; vertical-align:top; padding-right:8px;">
                <h6>{{ __('Summary') }}</h6>
                <ul class="list-unstyled mb-0">
                    <li>{{ __('Total Students') }}: {{ $summary['total_students'] ?? 0 }}</li>
                    <li>{{ __('Passed') }}: {{ $summary['passed_students'] ?? 0 }}</li>
                    <li>{{ __('Failed') }}: {{ $summary['failed_students'] ?? 0 }}</li>
                    <li>{{ __('Highest') }}: {{ number_format((float) ($summary['highest_marks'] ?? 0), 2) }}</li>
                    <li>{{ __('Lowest') }}: {{ number_format((float) ($summary['lowest_marks'] ?? 0), 2) }}</li>
                    <li>{{ __('Average Marks') }}: {{ number_format((float) ($summary['average_marks'] ?? 0), 2) }}</li>
                    <li>{{ __('Average GPA') }}: {{ number_format((float) ($summary['average_gpa'] ?? 0), 2) }}</li>
                </ul>
            </td>
            <td style="width:50%; vertical-align:top; padding-left:8px;">
                <h6>{{ __('Grade Distribution') }}</h6>
                <table class="table table-sm table-bordered w-100 mb-0">
                    <thead><tr><th>{{ __('Grade') }}</th><th>{{ __('Count') }}</th></tr></thead>
                    <tbody>
                        @foreach ($summary['grade_distribution'] ?? [] as $gradeName => $count)
                            <tr><td>{{ $gradeName }}</td><td>{{ $count }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</div>
