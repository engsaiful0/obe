@extends('layouts/layoutMaster')

@section('title', __('PO / PLO'))

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('Program outcomes (PO / PLO)') }}</h5>
        <a href="{{ route('program-outcomes.create') }}" class="btn btn-primary btn-sm">{{ __('Add outcome') }}</a>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Search') }}</label>
                <input class="form-control" name="q" value="{{ request('q') }}"
                    placeholder="{{ __('Code, title, description') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">{{ __('Program') }}</label>
                <select name="program_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($programs as $pr)
                        <option value="{{ $pr->id }}" @selected(request('program_id') == $pr->id)>{{ $pr->program_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Type') }}</label>
                <select name="outcome_type" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach (['PO', 'PLO'] as $t)
                        <option value="{{ $t }}" @selected(request('outcome_type') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Category') }}</label>
                <select name="category" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach (\App\Http\Requests\StoreProgramOutcomeRequest::categoryValues() as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ __($cat) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Status') }}</label>
                <select name="status" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach (['Active', 'Inactive'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ __($st) }}</option>
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
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Program') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($outcomes as $row)
                        <tr>
                            <td class="fw-medium">{{ $row->outcome_code }}</td>
                            <td>{{ $row->outcome_type }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($row->title ?: '—', 40) }}</td>
                            <td>{{ $row->program->program_name ?? '—' }}</td>
                            <td>{{ $row->category ?: '—' }}</td>
                            <td>
                                @php $active = strtolower($row->status ?? '') === 'active'; @endphp
                                <span class="badge {{ $active ? 'bg-success' : 'bg-secondary' }}">{{ __($row->status) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('program-outcomes.show', $row) }}" class="btn btn-sm btn-outline-info">{{ __('View') }}</a>
                                <a href="{{ route('program-outcomes.edit', $row) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1"
                                    data-async-delete-url="{{ route('program-outcomes.destroy', $row) }}"
                                    data-swal-title="{{ __('Delete PO / PLO?') }}"
                                    data-confirm="{{ __('Are you sure you want to delete this program outcome?') }}"
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
        {{ $outcomes->links() }}
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
@endsection
