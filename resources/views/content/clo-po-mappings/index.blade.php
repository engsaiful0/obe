@extends('layouts/layoutMaster')

@section('title', __('CLO–PO mappings'))

@php
    $levelBadges = [
        1 => ['secondary', __('Low')],
        2 => ['info', __('Medium')],
        3 => ['success', __('High')],
    ];
@endphp

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('CLO–PO / PLO mappings') }}</h5>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('clo-po-mappings.matrix') }}" class="btn btn-outline-primary btn-sm">{{ __('Mapping matrix') }}</a>
            <a href="{{ route('clo-po-mappings.create') }}" class="btn btn-primary btn-sm">{{ __('Add mapping') }}</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-0">{{ __('Search') }}</label>
                <input class="form-control" name="q" value="{{ request('q') }}"
                    placeholder="{{ __('CLO, PO, course') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Program') }}</label>
                <select name="program_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($programs as $p)
                        <option value="{{ $p->id }}" @selected(request('program_id') == $p->id)>
                            {{ $p->program_code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Course') }}</label>
                <select name="course_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($courses as $cr)
                        <option value="{{ $cr->id }}" @selected(request('course_id') == $cr->id)>
                            {{ $cr->course_code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('CLO') }}</label>
                <select name="clo_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($clos as $clo)
                        <option value="{{ $clo->id }}" @selected(request('clo_id') == $clo->id)>{{ $clo->clo_code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('PO/PLO') }}</label>
                <select name="program_outcome_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($programOutcomes as $po)
                        <option value="{{ $po->id }}" @selected(request('program_outcome_id') == $po->id)>
                            {{ $po->outcome_code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-0">{{ __('Level') }}</label>
                <select name="mapping_level" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ([1 => __('Low'), 2 => __('Medium'), 3 => __('High')] as $k => $label)
                        <option value="{{ $k }}" @selected(request('mapping_level') == $k)>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Status') }}</label>
                <select name="status_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($obeStatuses as $st)
                        <option value="{{ $st->id }}" @selected(request('status_id') == $st->id)>{{ $st->status_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-outline-primary">{{ __('Filter') }}</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Program') }}</th>
                        <th>{{ __('Course') }}</th>
                        <th>{{ __('CLO') }}</th>
                        <th>{{ __('PO/PLO') }}</th>
                        <th>{{ __('Level') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mappings as $row)
                        @php
                            $lvl = (int) $row->mapping_level;
                            $lb = $levelBadges[$lvl] ?? ['secondary', '?'];
                        @endphp
                        <tr>
                            <td class="small">{{ $row->program->program_name ?? '—' }}</td>
                            <td class="small">
                                <span class="fw-medium">{{ $row->course->course_code ?? '—' }}</span><br>
                                <span class="text-muted">{{ \Illuminate\Support\Str::limit($row->course->course_title ?? '', 36) }}</span>
                            </td>
                            <td class="small">
                                <span class="fw-medium">{{ $row->clo->clo_code ?? '—' }}</span><br>
                                <span class="text-muted">{{ $row->clo->title ? \Illuminate\Support\Str::limit($row->clo->title, 36) : '—' }}</span>
                            </td>
                            <td class="small">
                                <span class="fw-medium">{{ $row->programOutcome->outcome_code ?? '—' }}</span>
                                <span class="text-muted">{{ $row->programOutcome && $row->programOutcome->outcome_type ? ' ('.$row->programOutcome->outcome_type.')' : '' }}</span><br>
                                <span class="text-muted">{{ $row->programOutcome && $row->programOutcome->title ? \Illuminate\Support\Str::limit($row->programOutcome->title, 36) : '—' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $lb[0] }}">{{ $lvl }} {{ $lb[1] }}</span>
                            </td>
                            <td>
                                @php $sn = strtolower($row->status->status_name ?? ''); @endphp
                                <span class="badge {{ str_contains($sn, 'active') ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $row->status->status_name ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('clo-po-mappings.show', $row) }}" class="btn btn-sm btn-outline-info">{{ __('View') }}</a>
                                <a href="{{ route('clo-po-mappings.edit', $row) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                                <button type="button"
                                    class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1"
                                    data-async-delete-url="{{ route('clo-po-mappings.destroy', $row) }}"
                                    data-confirm="{{ __('Are you sure you want to delete this CLO-PO mapping?') }}"
                                    data-swal-title="{{ __('Delete CLO-PO Mapping?') }}"
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
                            <td colspan="7" class="text-center text-muted py-4">{{ __('No records.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $mappings->links() }}
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
@endsection
