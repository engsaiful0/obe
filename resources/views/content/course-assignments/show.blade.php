@extends('layouts/layoutMaster')

@section('title', __('Course assignment'))

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('Assignment details') }}</h5>
        <div class="d-flex gap-1">
            <a href="{{ route('course-assignment.edit', $assignment) }}" class="btn btn-sm btn-warning">{{ __('Edit') }}</a>
            <a href="{{ route('course-assignment.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><strong>{{ __('Academic session') }}:</strong> {{ $assignment->academicSession->session_name ?? '—' }}</div>
            <div class="col-md-6"><strong>{{ __('Program') }}:</strong> {{ $assignment->program->program_name ?? '—' }}</div>
            <div class="col-md-6"><strong>{{ __('Semester') }}:</strong> {{ $assignment->semester->semester_name ?? '—' }}</div>
            <div class="col-md-6"><strong>{{ __('Course') }}:</strong> {{ $assignment->course->course_code ?? '' }} — {{ $assignment->course->course_title ?? '' }}</div>
            <div class="col-md-6"><strong>{{ __('Section') }}:</strong> {{ $assignment->section->section_name ?? '—' }}</div>
            <div class="col-md-6"><strong>{{ __('Teacher') }}:</strong> {{ $assignment->teacher->teacher_name ?? '—' }}</div>
            <div class="col-md-6">
                <strong>{{ __('Status') }}:</strong>
                <span class="badge {{ str_contains($assignment->status->status_name, 'Active') ? 'bg-success' : 'bg-secondary' }}">{{ $assignment->status->status_name }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
