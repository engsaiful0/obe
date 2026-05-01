<div class="qcm-table-fragment">
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('Program') }}</th>
                    <th>{{ __('Course') }}</th>
                    <th>{{ __('Component') }}</th>
                    <th>{{ __('Main Q no') }}</th>
                    <th>{{ __('Part') }}</th>
                    <th>{{ __('Question label') }}</th>
                    <th>{{ __('Marks') }}</th>
                    <th>{{ __('CLO') }}</th>
                    <th>{{ __("Bloom") }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($mappings as $row)
                    <tr>
                        <td class="small">{{ $row->program->program_name ?? '—' }}</td>
                        <td class="small">
                            <span class="fw-medium">{{ $row->course->course_code ?? '—' }}</span><br>
                            <span class="text-muted">{{ \Illuminate\Support\Str::limit($row->course->course_title ?? '', 34) }}</span>
                        </td>
                        <td class="small">
                            <span class="fw-medium">{{ $row->assessmentComponent->component_name ?? '—' }}</span>
                            <span class="text-muted">({{ __('max') }} {{ $row->assessmentComponent->marks ?? '—' }})</span>
                        </td>
                        <td class="small">{{ $row->main_question_no ?? '—' }}</td>
                        <td class="small">{{ $row->question_part ?? '—' }}</td>
                        <td class="fw-medium">{{ $row->question_label }}</td>
                        <td>{{ $row->marks }}</td>
                        <td class="small">
                            <span class="fw-medium">{{ $row->clo->clo_code ?? '—' }}</span><br>
                            <span class="text-muted">{{ $row->clo->title ? \Illuminate\Support\Str::limit($row->clo->title, 36) : '—' }}</span>
                        </td>
                        <td class="small">{{ $row->bloom->name ?? '—' }}</td>
                        <td>
                            @php $sn = strtolower($row->status->status_name ?? ''); @endphp
                            <span class="badge {{ str_contains($sn, 'active') ? 'bg-success' : 'bg-secondary' }}">
                                {{ $row->status->status_name ?? '—' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('question-clo-mappings.show', $row) }}" class="btn btn-sm btn-outline-info">{{ __('View') }}</a>
                            <a href="{{ route('question-clo-mappings.edit', $row) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                            <button type="button"
                                class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1"
                                data-async-delete-url="{{ route('question-clo-mappings.destroy', $row) }}"
                                data-confirm="{{ __('Are you sure you want to delete this Question-CLO mapping?') }}"
                                data-swal-title="{{ __('Delete Question-CLO Mapping?') }}"
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
                        <td colspan="11" class="text-center text-muted py-4">{{ __('No records.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3 d-flex justify-content-center qcm-pagination">
        {{ $mappings->links() }}
    </div>
</div>
