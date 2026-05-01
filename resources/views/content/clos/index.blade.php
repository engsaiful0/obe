@extends('layouts/layoutMaster')

@section('title', __('Course learning outcomes'))

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('Course learning outcomes (CLO)') }}</h5>
        <a href="{{ route('clos.create') }}" class="btn btn-primary btn-sm">{{ __('Add CLO') }}</a>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-0">{{ __('Search') }}</label>
                <input class="form-control" name="q" value="{{ request('q') }}"
                    placeholder="{{ __('Code, title, description, course') }}">
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
            <div class="col-md-3">
                <label class="form-label small mb-0">{{ __('Course') }}</label>
                <select name="course_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($courses as $cr)
                        <option value="{{ $cr->id }}" @selected(request('course_id') == $cr->id)>
                            {{ $cr->course_code }} — {{ \Illuminate\Support\Str::limit($cr->course_title, 36) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __("Bloom level") }}</label>
                <select name="bloom_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($blooms as $bl)
                        <option value="{{ $bl->id }}" @selected(request('bloom_id') == $bl->id)>{{ $bl->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Status') }}</label>
                <select name="status_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($filterStatuses as $st)
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
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Program') }}</th>
                        <th>{{ __('Course') }}</th>
                        <th>{{ __("Bloom") }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clos as $row)
                        <tr>
                            <td class="fw-medium">{{ $row->clo_code }}</td>
                            <td class="text-muted small">{{ $row->title ? \Illuminate\Support\Str::limit($row->title, 48) : '—' }}</td>
                            <td>{{ $row->program->program_name ?? '—' }}</td>
                            <td>
                                <span class="d-block small fw-medium">{{ $row->course->course_code ?? '—' }}</span>
                                <span class="text-muted small">{{ \Illuminate\Support\Str::limit($row->course->course_title ?? '', 40) }}</span>
                            </td>
                            <td>{{ $row->bloom->name ?? '—' }}</td>
                            <td>
                                @php $sn = strtolower($row->status->status_name ?? ''); @endphp
                                <span class="badge {{ str_contains($sn, 'active') ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $row->status->status_name ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('clos.show', $row) }}" class="btn btn-sm btn-outline-info">{{ __('View') }}</a>
                                <a href="{{ route('clos.edit', $row) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                                <button type="button"
                                    class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1"
                                    data-async-delete-url="{{ route('clos.destroy', $row) }}"
                                    data-confirm="{{ __('Are you sure you want to delete this CLO?') }}"
                                    data-swal-title="{{ __('Delete CLO?') }}"
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
        {{ $clos->links() }}
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
@endsection
