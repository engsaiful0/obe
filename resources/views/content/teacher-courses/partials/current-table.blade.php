<div class="table-responsive">
    <table class="table table-sm table-striped table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>{{ __('Serial No.') }}</th>
                <th data-sort="session">{{ __('Academic Session') }}</th>
                <th data-sort="course_code">{{ __('Course Code') }}</th>
                <th data-sort="course_title">{{ __('Course Title') }}</th>
                <th>{{ __('Batch') }}</th>
                <th>{{ __('Section') }}</th>
                <th class="text-center">{{ __('Total Students') }}</th>
                <th class="text-center">{{ __('Classes Taken') }}</th>
                <th>{{ __('Attendance Status') }}</th>
                <th>{{ __('Marks Entry Status') }}</th>
                <th>{{ __('Grade Submission') }}</th>
                <th class="text-end no-print">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($courses as $assignment)
                @php
                    $sectionLabel = trim(($assignment->section?->section_code ?? '') . ' ' . ($assignment->section?->section_name ?? ''));
                    $batchLabel = implode(', ', $assignment->batch_labels ?? []);
                    $serial = $loop->iteration + ($courses->currentPage() - 1) * $courses->perPage();
                    $statusBadge = fn ($s) => match ($s) {
                        'Complete', 'Submitted' => 'success',
                        'In Progress' => 'warning',
                        default => 'secondary',
                    };
                @endphp
                <tr>
                    <td>{{ $serial }}</td>
                    <td>{{ $assignment->academicSession?->session_name ?? '-' }}</td>
                    <td>{{ $assignment->course?->course_code ?? '-' }}</td>
                    <td>
                        <a href="{{ route('teacher-courses.dashboard', $assignment) }}" class="fw-medium">
                            {{ $assignment->course?->course_title ?? '-' }}
                        </a>
                    </td>
                    <td>{{ $batchLabel !== '' ? $batchLabel : '-' }}</td>
                    <td>{{ $sectionLabel !== '' ? $sectionLabel : '-' }}</td>
                    <td class="text-center">{{ (int) ($assignment->total_students ?? 0) }}</td>
                    <td class="text-center">{{ (int) ($assignment->classes_taken ?? 0) }}</td>
                    <td><span class="badge bg-{{ $statusBadge($assignment->attendance_status ?? 'Pending') }}">{{ __($assignment->attendance_status ?? 'Pending') }}</span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height: 6px; min-width: 60px;">
                                <div class="progress-bar" style="width: {{ min(100, (float) ($assignment->marks_entry_progress ?? 0)) }}%"></div>
                            </div>
                            <small>{{ $assignment->marks_entry_status ?? 'Pending' }}</small>
                        </div>
                    </td>
                    <td><span class="badge bg-{{ $statusBadge($assignment->grade_submission_status ?? 'Pending') }}">{{ __($assignment->grade_submission_status ?? 'Pending') }}</span></td>
                    <td class="text-end no-print">
                        <a href="{{ route('teacher-courses.dashboard', $assignment) }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-layout-dashboard"></i> {{ __('Dashboard') }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center text-muted py-4">{{ __('No current semester courses found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3 teacher-course-pagination">
    {{ $courses->links() }}
</div>
