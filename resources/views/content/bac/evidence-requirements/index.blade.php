@extends('layouts/layoutMaster')

@section('title', __('BAC Evidence Requirements'))

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('BAC evidence requirements') }}</h5>
        @permission('bac-manage')
        <a href="{{ route('bac-evidence-requirements.create', request()->only('bac_criterion_id')) }}" class="btn btn-primary btn-sm">{{ __('Add requirement') }}</a>
        @endpermission
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-0">{{ __('Search') }}</label>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Title, type, description') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-0">{{ __('Criterion') }}</label>
                <select name="bac_criterion_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($criteria as $criterion)
                        <option value="{{ $criterion->id }}" @selected(request('bac_criterion_id') == $criterion->id)>
                            {{ $criterion->standard?->standard_no }} / {{ $criterion->criterion_no }} - {{ \Illuminate\Support\Str::limit($criterion->title ?: $criterion->description, 70) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Required') }}</label>
                <select name="is_required" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    <option value="1" @selected(request('is_required') === '1')>{{ __('Required') }}</option>
                    <option value="0" @selected(request('is_required') === '0')>{{ __('Optional') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Status') }}</label>
                <select name="is_active" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    <option value="1" @selected(request('is_active') === '1')>{{ __('Active') }}</option>
                    <option value="0" @selected(request('is_active') === '0')>{{ __('Inactive') }}</option>
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
                        <th>{{ __('Criterion') }}</th>
                        <th>{{ __('Requirement') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Required') }}</th>
                        <th>{{ __('Evidence links') }}</th>
                        <th>{{ __('Sort') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requirements as $requirement)
                        <tr>
                            <td class="small">
                                <span class="fw-medium">{{ $requirement->criterion?->standard?->standard_no }} / {{ $requirement->criterion?->criterion_no }}</span>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $requirement->title }}</div>
                                @if ($requirement->description)
                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($requirement->description, 90) }}</div>
                                @endif
                            </td>
                            <td>{{ $requirement->evidence_type ?: '-' }}</td>
                            <td>
                                <span class="badge {{ $requirement->is_required ? 'bg-primary' : 'bg-secondary' }}">
                                    {{ $requirement->is_required ? __('Required') : __('Optional') }}
                                </span>
                            </td>
                            <td>{{ $requirement->evidence_links_count }}</td>
                            <td>{{ $requirement->sort_order ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $requirement->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $requirement->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                            <td class="text-end">
                                @permission('bac-manage')
                                <a href="{{ route('bac-evidence-requirements.edit', $requirement) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1"
                                    data-async-delete-url="{{ route('bac-evidence-requirements.destroy', $requirement) }}"
                                    data-swal-title="{{ __('Delete or deactivate requirement?') }}"
                                    data-confirm="{{ __('Unused requirements will be deleted. Requirements with evidence links will be deactivated.') }}"
                                    data-confirm-yes="{{ __('Continue') }}"
                                    data-confirm-no="{{ __('Cancel') }}"
                                    aria-label="{{ __('Delete or deactivate') }}">
                                    <span class="obe-btn-label">{{ __('Delete') }}</span>
                                    <span class="spinner-border spinner-border-sm d-none obe-btn-spinner" role="status" aria-hidden="true"></span>
                                </button>
                                @endpermission
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">{{ __('No records.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $requirements->links() }}
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
@endsection
