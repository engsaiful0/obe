@php
    $assignment = $courseAssignment ?? $report['assignment'] ?? null;
    $batchText = is_array($batchLabels ?? null)
        ? implode(', ', $batchLabels)
        : (is_string($batchLabels ?? null) ? $batchLabels : implode(', ', $report['batch_labels'] ?? []));
    $sectionLabel = trim(
        ($assignment?->section?->section_code ?? '').' '.($assignment?->section?->section_name ?? '')
    );
    $totalStudents = $report['summary']['total_students'] ?? count($report['rows'] ?? []);
@endphp
<table class="table table-sm table-bordered mb-0">
    <tbody>
        <tr>
            <th class="bg-light" style="width: 38%">{{ __('Academic Session') }}</th>
            <td>{{ $assignment?->academicSession?->session_name ?? '-' }}</td>
        </tr>
        <tr>
            <th class="bg-light">{{ __('Program') }}</th>
            <td>{{ $assignment?->program?->program_name ?? '-' }}</td>
        </tr>
        <tr>
            <th class="bg-light">{{ __('Course Code') }}</th>
            <td>{{ $assignment?->course?->course_code ?? '-' }}</td>
        </tr>
        <tr>
            <th class="bg-light">{{ __('Course Name') }}</th>
            <td>{{ $assignment?->course?->course_title ?? '-' }}</td>
        </tr>
        <tr>
            <th class="bg-light">{{ __('Batch') }}</th>
            <td>{{ $batchText !== '' ? $batchText : '-' }}</td>
        </tr>
        <tr>
            <th class="bg-light">{{ __('Section') }}</th>
            <td>{{ $sectionLabel !== '' ? $sectionLabel : '-' }}</td>
        </tr>
        <tr>
            <th class="bg-light">{{ __('Semester') }}</th>
            <td>{{ $assignment?->semester?->semester_name ?? '-' }}</td>
        </tr>
        <tr>
            <th class="bg-light">{{ __('Teacher') }}</th>
            <td>{{ $assignment?->teacher?->teacher_name ?? '-' }}</td>
        </tr>
        <tr>
            <th class="bg-light">{{ __('Total Students') }}</th>
            <td>{{ $totalStudents }}</td>
        </tr>
    </tbody>
</table>
