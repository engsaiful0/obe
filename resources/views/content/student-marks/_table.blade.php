<div class="sm-table-fragment">
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Session') }}</th>
                    <th>{{ __('Program') }}</th>
                    <th>{{ __('Course') }}</th>
                    <th>{{ __('Batch') }}</th>
                    <th>{{ __('Section') }}</th>
                    <th>{{ __('Component') }}</th>
                    <th>{{ __('Student') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($marks as $row)
                    <tr>
                        <td class="small">{{ $row->academicSession?->session_name }} · {{ $row->academicSession?->academic_year }}</td>
                        <td class="small">{{ $row->program?->program_code }}</td>
                        <td class="small">{{ $row->course?->course_code }}</td>
                        <td class="small">{{ $row->batch?->batch_name }}</td>
                        <td class="small">{{ $row->section?->section_code ?? '—' }}</td>
                        <td class="small">{{ $row->assessmentComponent?->component_name }}</td>
                        <td>
                            <span class="fw-medium">{{ $row->student?->student_code }}</span>
                            <br>
                            <span class="text-muted small">{{ \Illuminate\Support\Str::limit($row->student?->student_name, 42) }}</span>
                        </td>
                        <td>{{ $row->total_marks }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $row->status?->status_name }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('student-marks.show', $row) }}" class="btn btn-sm btn-outline-info">{{ __('View') }}</a>
                            <a href="{{ route('student-marks.edit', $row) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                            <button type="button"
                                class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1"
                                data-async-delete-url="{{ route('student-marks.destroy', $row) }}"
                                data-confirm="{{ __('Delete this marks sheet permanently?') }}"
                                data-swal-title="{{ __('Delete marks?') }}"
                                data-confirm-yes="{{ __('Yes, delete') }}"
                                data-confirm-no="{{ __('Cancel') }}"
                                aria-label="{{ __('Delete') }}">
                                <span class="obe-btn-label">{{ __('Delete') }}</span>
                                <span class="spinner-border spinner-border-sm d-none obe-btn-spinner" role="status" aria-hidden="true"></span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">{{ __('No records.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3 d-flex justify-content-center sm-pagination">
        {{ $marks->links() }}
    </div>
</div>
