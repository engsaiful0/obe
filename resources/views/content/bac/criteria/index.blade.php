@extends('layouts/layoutMaster')

@section('title', __('BAC Criteria'))

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('BAC criteria') }}</h5>
        @permission('bac-manage')
        <a href="{{ route('bac-criteria.create', request()->only('bac_standard_id')) }}" class="btn btn-primary btn-sm">{{ __('Add criterion') }}</a>
        @endpermission
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-0">{{ __('Search') }}</label>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Criterion no, title, evidence') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">{{ __('Standard') }}</label>
                <select name="bac_standard_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($standards as $standard)
                        <option value="{{ $standard->id }}" @selected(request('bac_standard_id') == $standard->id)>
                            {{ $standard->standard_no }} - {{ $standard->title }}
                        </option>
                    @endforeach
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
                        <th>{{ __('Standard') }}</th>
                        <th>{{ __('Criterion') }}</th>
                        <th>{{ __('Title / description') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Requirements') }}</th>
                        <th>{{ __('Evidence') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($criteria as $criterion)
                        <tr>
                            <td class="small">{{ $criterion->standard?->standard_no }}</td>
                            <td class="fw-medium">{{ $criterion->criterion_no }}</td>
                            <td>
                                <div class="fw-medium">{{ $criterion->title ?: '-' }}</div>
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($criterion->description, 90) }}</div>
                            </td>
                            <td>{{ $criterion->responsible_role ?: '-' }}</td>
                            <td>{{ $criterion->evidence_requirements_count }}</td>
                            <td>{{ $criterion->evidence_links_count }}</td>
                            <td>
                                <span class="badge {{ $criterion->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $criterion->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('bac-evidence-requirements.index', ['bac_criterion_id' => $criterion->id]) }}" class="btn btn-sm btn-outline-info">{{ __('Requirements') }}</a>
                                @permission('bac-manage')
                                <a href="{{ route('bac-criteria.edit', $criterion) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1"
                                    data-async-delete-url="{{ route('bac-criteria.destroy', $criterion) }}"
                                    data-swal-title="{{ __('Delete or deactivate criterion?') }}"
                                    data-confirm="{{ __('Unused criteria will be deleted. Criteria with linked records will be deactivated.') }}"
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
        {{ $criteria->links() }}
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
@endsection
