@extends('layouts/layoutMaster')

@section('title', 'Student Details')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Student Details</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('student.edit', $student->id) }}" class="btn btn-sm btn-primary">
                    <i class="ti ti-pencil me-1"></i>Edit
                </a>
                <a href="{{ route('student.view-student') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><strong>Name:</strong><br>{{ $student->student_name }}</div>
                <div class="col-md-3"><strong>Student ID:</strong><br>{{ $student->student_code }}</div>
                <div class="col-md-3"><strong>Status:</strong><br>{{ $student->status->status_name ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Admission Date:</strong><br>{{ $student->admission_date?->format('Y-m-d') ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Program:</strong><br>{{ $student->program->program_name ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Batch:</strong><br>{{ $student->batch->batch_name ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Session:</strong><br>{{ $student->academicSession->session_name ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Gender:</strong><br>{{ $student->gender->gender_name ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Religion:</strong><br>{{ $student->religion->religion_name ?? 'N/A' }}</div>
                <div class="col-md-3"><strong>Phone:</strong><br>{{ $student->phone ?: 'N/A' }}</div>
                <div class="col-md-3"><strong>Email:</strong><br>{{ $student->email ?: 'N/A' }}</div>
                <div class="col-md-3"><strong>Shift / Type:</strong><br>{{ $student->shift }} / {{ $student->student_type }}</div>
                <div class="col-md-6"><strong>Father Name:</strong><br>{{ $student->father_name ?: 'N/A' }}</div>
                <div class="col-md-6"><strong>Mother Name:</strong><br>{{ $student->mother_name ?: 'N/A' }}</div>
                <div class="col-md-12"><strong>Present Address:</strong><br>{{ $student->present_address ?: 'N/A' }}</div>
                <div class="col-md-12"><strong>Permanent Address:</strong><br>{{ $student->permanent_address ?: 'N/A' }}</div>
            </div>
        </div>
    </div>
@endsection
