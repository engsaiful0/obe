<div class="table-responsive">
    <table class="table table-sm table-striped align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>{{ __('Course Code') }}</th>
                <th>{{ __('Course Name') }}</th>
                <th>{{ __('Academic Session') }}</th>
                <th>{{ __('Program') }}</th>
                <th>{{ __('Batch') }}</th>
                <th>{{ __('Section') }}</th>
                <th class="text-center">{{ __('Students') }}</th>
                <th class="text-end">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($courses as $assignment)
                @php
                    $sectionLabel = trim(
                        ($assignment->section?->section_code ?? '') . ' ' . ($assignment->section?->section_name ?? '')
                    );
                    $batchLabel = implode(', ', $assignment->batch_labels ?? []);
                @endphp
                <tr>
                    <td>{{ $loop->iteration + ($courses->currentPage() - 1) * $courses->perPage() }}</td>
                    <td>{{ $assignment->course?->course_code ?? '-' }}</td>
                    <td>{{ $assignment->course?->course_title ?? '-' }}</td>
                    <td>
                        {{ $assignment->academicSession?->session_name ?? '-' }}
                        @if ($assignment->academicSession?->academic_year)
                            <span class="text-muted">({{ $assignment->academicSession->academic_year }})</span>
                        @endif
                    </td>
                    <td>{{ $assignment->program?->program_name ?? '-' }}</td>
                    <td>{{ $batchLabel !== '' ? $batchLabel : '-' }}</td>
                    <td>{{ $sectionLabel !== '' ? $sectionLabel : '-' }}</td>
                    <td class="text-center">{{ (int) ($assignment->total_students ?? 0) }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('my-courses.marks-entry', $assignment) }}" class="btn btn-primary">
                                {{ __('Marks Entry') }}
                            </a>
                            <a href="{{ route('my-courses.import', $assignment) }}" class="btn btn-success">
                                {{ __('Excel Import') }}
                            </a>
                            <a href="{{ route('my-courses.download-template', $assignment) }}" class="btn btn-outline-primary">
                                {{ __('Template') }}
                            </a>
                            <a href="{{ route('my-courses.grade-sheet', $assignment) }}" class="btn btn-info">
                                {{ __('Grade Sheet') }}
                            </a>
                            <a href="{{ route('my-courses.grade-sheet.pdf', $assignment) }}" class="btn btn-outline-danger">
                                {{ __('PDF') }}
                            </a>
                            <a href="{{ route('my-courses.grade-sheet.excel', $assignment) }}" class="btn btn-outline-success">
                                {{ __('Excel') }}
                            </a>
                            <a href="{{ route('my-courses.grade-sheet.print', $assignment) }}" class="btn btn-outline-secondary" target="_blank">
                                {{ __('Print') }}
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-3">{{ __('No assigned courses found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3 my-course-pagination">
    {{ $courses->links() }}
</div>
