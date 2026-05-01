@extends('layouts/layoutMaster')

@section('title', __('Assessment setup'))

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('Assessment components') }}</h5>
        <a href="{{ route('assessment-components.create') }}" class="btn btn-primary btn-sm">{{ __('Add component') }}</a>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-0">{{ __('Search') }}</label>
                <input class="form-control" name="q" value="{{ request('q') }}"
                    placeholder="{{ __('Name, type, course') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Program') }}</label>
                <select name="program_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($programs as $p)
                        <option value="{{ $p->id }}" @selected(request('program_id') == $p->id)>{{ $p->program_code }}</option>
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
                <label class="form-label small mb-0">{{ __('Type') }}</label>
                <select name="component_type" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($componentTypes as $tk => $tv)
                        <option value="{{ $tk }}" @selected(request('component_type') === $tk)>{{ $tv }}</option>
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

        @if (request()->filled('course_id') && $totalActiveMarksForCourse !== null)
            <div class="alert alert-info py-2 small mb-3">
                {{ __('Total marks (active components) for filtered course') }}:
                <strong>{{ rtrim(rtrim(number_format((float) $totalActiveMarksForCourse, 2, '.', ''), '0'), '.') }}</strong>
                / 100
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Program') }}</th>
                        <th>{{ __('Course') }}</th>
                        <th>{{ __('Component') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Multiple Q') }}</th>
                        <th>{{ __('Marks') }}</th>
                        <th>{{ __('Weight %') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($components as $row)
                        <tr>
                            <td class="small">{{ $row->program->program_name ?? '—' }}</td>
                            <td class="small">
                                <span class="fw-medium">{{ $row->course->course_code ?? '—' }}</span><br>
                                <span class="text-muted">{{ \Illuminate\Support\Str::limit($row->course->course_title ?? '', 34) }}</span>
                            </td>
                            <td class="fw-medium">{{ $row->component_name }}</td>
                            <td><span class="badge bg-secondary">{{ $row->component_type }}</span></td>
                            <td>{{ $row->has_multiple_questions == 1 ? 'Yes' : 'No' }}</td>
                            <td>{{ $row->marks }}</td>
                            <td>{{ $row->weight_percentage ?? '—' }}</td>
                            <td>
                                @php $sn = strtolower($row->status->status_name ?? ''); @endphp
                                <span class="badge {{ str_contains($sn, 'active') ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $row->status->status_name ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('assessment-components.show', $row) }}" class="btn btn-sm btn-outline-info">{{ __('View') }}</a>
                                <a href="{{ route('assessment-components.edit', $row) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                                <button type="button"
                                    class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1"
                                    data-async-delete-url="{{ route('assessment-components.destroy', $row) }}"
                                    data-confirm="{{ __('Are you sure you want to delete this assessment component?') }}"
                                    data-swal-title="{{ __('Delete Assessment Component?') }}"
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
                            <td colspan="9" class="text-center text-muted py-4">{{ __('No records.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $components->links() }}
    </div>
</div>
@endsection

@section('page-script')
<script src="{{ asset('assets/js/obe-ajax-crud.js') }}"></script>
@endsection
