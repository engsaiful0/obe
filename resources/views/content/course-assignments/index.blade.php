@extends('layouts/layoutMaster')

@section('title', __('Course assignments'))

@section('content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">{{ __('Teacher course assignments') }}</h5>
        <a href="{{ route('course-assignment.create') }}" class="btn btn-primary btn-sm">{{ __('Add assignment') }}</a>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-0">{{ __('Search') }}</label>
                <input class="form-control" name="q" value="{{ request('q') }}"
                    placeholder="{{ __('Course code, title, teacher, batch, section…') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Session') }}</label>
                <select name="academic_session_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($sessions as $session)
                        <option value="{{ $session->id }}" @selected(request('academic_session_id') == $session->id)>
                            {{ $session->session_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Program') }}</label>
                <select name="program_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" @selected(request('program_id') == $program->id)>
                            {{ $program->program_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Batch') }}</label>
                <select name="batch_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($batches as $batch)
                        <option value="{{ $batch->id }}" @selected(request('batch_id') == $batch->id)>
                            {{ $batch->batch_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Semester') }}</label>
                <select name="semester_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($semesters as $semester)
                        <option value="{{ $semester->id }}" @selected(request('semester_id') == $semester->id)>
                            {{ $semester->semester_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Section') }}</label>
                <select name="section_id" class="form-select">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected(request('section_id') == $section->id)>
                            {{ $section->section_name }}
                        </option>
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
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">{{ __('Filter') }}</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead>
                    <tr>
                        <th>{{ __('Session') }}</th>
                        <th>{{ __('Program') }}</th>
                        <th>{{ __('Batch') }}</th>
                        <th>{{ __('Semester') }}</th>
                        <th>{{ __('Course') }}</th>
                        <th>{{ __('Section') }}</th>
                        <th>{{ __('Teacher') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $row)
                        <tr>
                            <td>{{ $row->academicSession->session_name ?? '—' }}</td>
                            <td>{{ $row->program->program_name ?? '—' }}</td>
                            <td>{{ $row->batch->batch_name ?? '—' }}</td>
                            <td>{{ $row->semester->semester_name ?? '—' }}</td>
                            <td>
                                <span class="fw-medium">{{ $row->course->course_code ?? '' }}</span>
                                <div class="small text-muted">{{ $row->course->course_title ?? '' }}</div>
                            </td>
                            <td>{{ $row->section->section_name ?? '—' }}</td>
                            <td>{{ $row->teacher->teacher_name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $row->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $row->status }}
                                </span>
                            </td>
                            <td class="text-end d-flex gap-1 justify-content-end flex-wrap">
                                <a href="{{ route('course-assignment.show', $row) }}" class="btn btn-sm btn-outline-info">{{ __('View') }}</a>
                                <a href="{{ route('course-assignment.edit', $row) }}" class="btn btn-sm btn-outline-warning">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('course-assignment.destroy', $row) }}"
                                    onsubmit="return confirm(@json(__('Delete this assignment?')));" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">{{ __('No assignments found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $assignments->links() }}
    </div>
</div>
@endsection
