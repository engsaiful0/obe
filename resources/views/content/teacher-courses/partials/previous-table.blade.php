<div class="table-responsive">
    <table class="table table-sm table-striped table-hover align-middle mb-0 teacher-course-datatable" id="teacher-course-table">
        <thead class="table-light">
            <tr>
                <th>{{ __('Serial No.') }}</th>
                <th data-sort="session">{{ __('Academic Session') }}</th>
                <th data-sort="program">{{ __('Program') }}</th>
                <th data-sort="course_code">{{ __('Course Code') }}</th>
                <th data-sort="course_title">{{ __('Course Title') }}</th>
                <th>{{ __('Batch') }}</th>
                <th>{{ __('Section') }}</th>
                <th class="text-center">{{ __('Students') }}</th>
                <th>{{ __('Grade Submission Date') }}</th>
                <th class="text-end no-print">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($courses as $assignment)
                @php
                    $sectionLabel = trim(($assignment->section?->section_code ?? '') . ' ' . ($assignment->section?->section_name ?? ''));
                    $batchLabel = implode(', ', $assignment->batch_labels ?? []);
                    $serial = $loop->iteration + ($courses->currentPage() - 1) * $courses->perPage();
                    $gradeDate = $assignment->grade_submission_date ?? null;
                @endphp
                <tr>
                    <td>{{ $serial }}</td>
                    <td>{{ $assignment->academicSession?->session_name ?? '-' }}</td>
                    <td>{{ $assignment->program?->program_name ?? '-' }}</td>
                    <td>{{ $assignment->course?->course_code ?? '-' }}</td>
                    <td>{{ $assignment->course?->course_title ?? '-' }}</td>
                    <td>{{ $batchLabel !== '' ? $batchLabel : '-' }}</td>
                    <td>{{ $sectionLabel !== '' ? $sectionLabel : '-' }}</td>
                    <td class="text-center">{{ (int) ($assignment->total_students ?? 0) }}</td>
                    <td>{{ $gradeDate ? \Carbon\Carbon::parse($gradeDate)->format('d M Y') : '-' }}</td>
                    <td class="text-end no-print">
                        @include('content.teacher-courses.partials.actions-dropdown', [
                            'assignment' => $assignment,
                            'readonly' => true,
                            'compact' => true,
                        ])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">{{ __('No previous semester courses found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3 teacher-course-pagination">
    {{ $courses->links() }}
</div>
