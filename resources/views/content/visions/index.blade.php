@extends('layouts/layoutMaster')

@section('title', __('Visions'))

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('University & department vision') }}</h5>
        <a href="{{ route('visions.create') }}" class="btn btn-primary btn-sm">{{ __('Add vision') }}</a>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-0">{{ __('Search') }}</label>
                <input class="form-control" name="q" value="{{ request('q') }}" placeholder="{{ __('Title or description') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Type') }}</label>
                <select name="type" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach (['University', 'Department'] as $t)
                        <option value="{{ $t }}" @selected(request('type') === $t)>{{ __($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('University') }}</label>
                <select name="university_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($universities as $u)
                        <option value="{{ $u->id }}" @selected(request('university_id') == $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Department') }}</label>
                <select name="department_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($departments as $d)
                        <option value="{{ $d->id }}" @selected(request('department_id') == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Status') }}</label>
                <select name="status_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($statuses as $st)
                        <option value="{{ $st->id }}" @selected(request('status_id') == $st->id)>{{ $st->status_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-outline-primary w-100">{{ __('Filter') }}</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('University') }}</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visions as $row)
                        <tr>
                            <td>{{ $row->type }}</td>
                            <td>{{ $row->university->name ?? '—' }}</td>
                            <td>{{ $row->department->name ?? '—' }}</td>
                            <td>
                                <div class="fw-medium">{{ $row->title ?: '—' }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 280px;">{{ \Illuminate\Support\Str::limit($row->description, 80) }}</div>
                            </td>
                            <td>
                                @php $sn = strtolower($row->status->status_name ?? ''); @endphp
                                <span class="badge {{ str_contains($sn, 'active') ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $row->status->status_name ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('visions.show', $row) }}" class="btn btn-sm btn-outline-info">{{ __('View') }}</a>
                                <a href="{{ route('visions.edit', $row) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1 obe-vision-delete-btn"
                                    data-async-delete-url="{{ route('visions.destroy', $row) }}"
                                    data-swal-title="{{ __('Delete vision') }}"
                                    data-confirm="{{ __('Are you sure you want to delete this vision?') }}"
                                    data-confirm-yes="{{ __('Yes, delete') }}"
                                    data-confirm-no="{{ __('Cancel') }}"
                                    aria-label="{{ __('Delete') }}">
                                    <span class="obe-btn-label">{{ __('Delete') }}</span>
                                    <span class="spinner-border spinner-border-sm d-none obe-btn-spinner" role="status" aria-hidden="true"></span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No records.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $visions->links() }}
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
@endsection
