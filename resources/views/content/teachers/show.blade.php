@extends('layouts/layoutMaster')

@section('title', 'Teacher Details')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Teacher Details</h5>
        <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><strong>Name:</strong> {{ $teacher->teacher_name }}</div>
            <div class="col-md-4"><strong>Employee ID:</strong> {{ $teacher->employee_id }}</div>
            <div class="col-md-4"><strong>Department:</strong> {{ $teacher->department->name ?? 'N/A' }}</div>
            <div class="col-md-4"><strong>Designation:</strong> {{ $teacher->designation->designation_name ?? 'N/A' }}</div>
            <div class="col-md-4"><strong>Email:</strong> {{ $teacher->email }}</div>
            <div class="col-md-4"><strong>Status:</strong> {{ $teacher->teacherStatus->status_name ?? 'N/A' }}</div>
        </div>
    </div>
</div>
@endsection
