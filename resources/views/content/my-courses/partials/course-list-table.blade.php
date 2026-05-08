<div class="table-responsive">
    <table class="table table-sm table-striped align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>{{ __('Course Name') }}</th>
                
                <th>{{ __('Semester / Session') }}</th>
                <th>{{ __('Section') }}</th>
                <th class="text-center">{{ __('Total Students') }}</th>
                <th class="text-end">{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($courses as $assignment)
                <tr>
                    <td>{{ $loop->iteration }}</td>

                    <td>{{ $assignment->course?->course_code ?? '-' }}</td>
                    <td>
                        {{ $assignment->semester?->semester_name ?? '-' }}
                        <span class="text-muted">/</span>
                        {{ $assignment->academicSession?->session_name ?? '-' }}
                    </td>
                    <td>{{ $assignment->Section?->section_code ?? '-' }}</td>
                    <td class="text-center">{{ (int) ($assignment->total_students ?? 0) }}</td>
                    <td class="text-end">
                        <a href="{{ route('my-courses.marks-entry', $assignment) }}" class="btn btn-primary btn-sm">
                            {{ __('Input Marks') }}
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-3">{{ __('No assigned courses found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3 my-course-pagination">
    {{ $courses->links() }}
</div>