@extends('layouts/layoutMaster')

@section('title', 'View Employee Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Employee Attendance Details</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('employee-attendances.edit', $employeeAttendance) }}" class="btn btn-primary">
            <i class="ti ti-edit me-1"></i> Edit
        </a>
        <a href="{{ route('employee-attendances.index') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Attendance Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Employee</label>
                        <div class="d-flex align-items-center">
                            @if($employeeAttendance->employee->picture)
                                <img src="{{ asset('storage/app/public/' . $employeeAttendance->employee->picture) }}" 
                                     alt="{{ $employeeAttendance->employee->employee_name }}" 
                                     class="rounded-circle me-3" width="48" height="48">
                            @else
                                <div class="avatar-initial rounded-circle bg-label-primary me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                    {{ substr($employeeAttendance->employee->employee_name, 0, 1) }}
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $employeeAttendance->employee->employee_name }}</h6>
                                <small class="text-muted">{{ $employeeAttendance->employee->employee_unique_id }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Attendance Date</label>
                        <p class="mb-0">{{ $employeeAttendance->attendance_date->format('M d, Y') }}</p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Check In Time</label>
                        <p class="mb-0">{{ $employeeAttendance->formatted_check_in_time }}</p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Check Out Time</label>
                        <p class="mb-0">{{ $employeeAttendance->formatted_check_out_time ?? 'Not recorded' }}</p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <div>
                            @switch($employeeAttendance->status)
                                @case('present')
                                    <span class="badge bg-label-success fs-6">Present</span>
                                    @break
                                @case('absent')
                                    <span class="badge bg-label-danger fs-6">Absent</span>
                                    @break
                                @case('late')
                                    <span class="badge bg-label-warning fs-6">Late</span>
                                    @break
                                @case('early_leave')
                                    <span class="badge bg-label-info fs-6">Early Leave</span>
                                    @break
                            @endswitch
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Working Hours</label>
                        <p class="mb-0">
                            @if($employeeAttendance->working_hours)
                                {{ $employeeAttendance->working_hours }} hours
                            @else
                                Not calculated
                            @endif
                        </p>
                    </div>

                    @if($employeeAttendance->remarks)
                    <div class="col-12">
                        <label class="form-label fw-bold">Remarks</label>
                        <p class="mb-0">{{ $employeeAttendance->remarks }}</p>
                    </div>
                    @endif

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Created By</label>
                        <p class="mb-0">{{ $employeeAttendance->user->name ?? 'System' }}</p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Created At</label>
                        <p class="mb-0">{{ $employeeAttendance->created_at->format('M d, Y H:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('employee-attendances.edit', $employeeAttendance) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i> Edit Attendance
                    </a>
                    <a href="{{ route('employee-attendances.index') }}" class="btn btn-outline-primary">
                        <i class="ti ti-list me-1"></i> View All Attendance
                    </a>
                    <a href="{{ route('employee-attendances.create') }}" class="btn btn-outline-success">
                        <i class="ti ti-plus me-1"></i> Add New Attendance
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Employee Info</h5>
            </div>
            <div class="card-body">
                <p><strong>Name:</strong> {{ $employeeAttendance->employee->employee_name }}</p>
                <p><strong>ID:</strong> {{ $employeeAttendance->employee->employee_unique_id }}</p>
                <p><strong>Mobile:</strong> {{ $employeeAttendance->employee->mobile ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $employeeAttendance->employee->email ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
