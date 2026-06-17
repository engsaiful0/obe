@extends('layouts/layoutMaster')

@section('title', __('BAC Standards'))

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('BAC standards') }}</h5>
        @permission('bac-manage')
        <a href="{{ route('bac-standards.create') }}" class="btn btn-primary btn-sm">{{ __('Add standard') }}</a>
        @endpermission
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small mb-0">{{ __('Search') }}</label>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Standard no, title, description') }}">
            </div>
            <div class="col-md-3">
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
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Criteria') }}</th>
                        <th>{{ __('Sort') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($standards as $standard)
                        <tr>
                            <td class="fw-medium">{{ $standard->standard_no }}</td>
                            <td>
                                <div class="fw-medium">{{ $standard->title }}</div>
                                @if ($standard->description)
                                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($standard->description, 90) }}</div>
                                @endif
                            </td>
                            <td>{{ $standard->criteria_count }}</td>
                            <td>{{ $standard->sort_order ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $standard->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $standard->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('bac-criteria.index', ['bac_standard_id' => $standard->id]) }}" class="btn btn-sm btn-outline-info">{{ __('Criteria') }}</a>
                                @permission('bac-manage')
                                <a href="{{ route('bac-standards.edit', $standard) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1"
                                    data-async-delete-url="{{ route('bac-standards.destroy', $standard) }}"
                                    data-swal-title="{{ __('Delete or deactivate standard?') }}"
                                    data-confirm="{{ __('Unused standards will be deleted. Standards with linked criteria will be deactivated.') }}"
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
                            <td colspan="6" class="text-center text-muted py-4">{{ __('No records.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $standards->links() }}
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
@endsection
