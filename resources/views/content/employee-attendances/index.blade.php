@extends('layouts/layoutMaster')

@section('title', 'Employee Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Employee Attendance</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('employee-attendances.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Attendance
        </a>
        <a href="{{ route('employee-attendances.add-all-attendance') }}" class="btn btn-success">
            <i class="ti ti-users me-1"></i> Add All Attendance
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('employee-attendances.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="employee_id" class="form-label">Employee</label>
                    <select class="form-select" id="employee_id" name="employee_id">
                        <option value="">All Employees</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->employee_name }} ({{ $employee->employee_unique_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                        <option value="early_leave" {{ request('status') == 'early_leave' ? 'selected' : '' }}>Early Leave</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="ti ti-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('employee-attendances.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-x me-1"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Attendance List -->
<div class="card">
    <div class="card-body">
        @if($attendances->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                            <th>Working Hours</th>
                            <th>Remarks</th>
                            @if(auth()->user()->hasPermissionTo('employee-attendance-view') || auth()->user()->hasPermissionTo('employee-attendance-edit') || auth()->user()->hasPermissionTo('employee-attendance-delete'))
                            <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $index => $attendance)
                        <tr>
                            <td>{{ $attendances->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($attendance->employee->picture)
                                        <img src="{{ asset('storage/app/public/' . $attendance->employee->picture) }}" 
                                             alt="{{ $attendance->employee->employee_name }}" 
                                             class="rounded-circle me-2" width="32" height="32">
                                    @else
                                        <div class="avatar-initial rounded-circle bg-label-primary me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            {{ substr($attendance->employee->employee_name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <h6 class="mb-0">{{ $attendance->employee->employee_name }}</h6>
                                        <small class="text-muted">{{ $attendance->employee->employee_unique_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $attendance->attendance_date->format('M d, Y') }}</td>
                            <td>{{ $attendance->formatted_check_in_time }}</td>
                            <td>{{ $attendance->formatted_check_out_time ?? '-' }}</td>
                            <td>
                                @switch($attendance->status)
                                    @case('present')
                                        <span class="badge bg-label-success">Present</span>
                                        @break
                                    @case('absent')
                                        <span class="badge bg-label-danger">Absent</span>
                                        @break
                                    @case('late')
                                        <span class="badge bg-label-warning">Late</span>
                                        @break
                                    @case('early_leave')
                                        <span class="badge bg-label-info">Early Leave</span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                @if($attendance->working_hours)
                                    {{ $attendance->working_hours }}h
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ str($attendance->remarks)->limit(30) }}</td>

                            @if(auth()->user()->hasPermissionTo('employee-attendance-view') || auth()->user()->hasPermissionTo('employee-attendance-edit') || auth()->user()->hasPermissionTo('employee-attendance-delete'))
                            <td>
                                <div class="dropdown">
                                    @if(auth()->user()->hasPermissionTo('employee-attendance-view'))
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    @endif
                                    <div class="dropdown-menu">
                                        @if(auth()->user()->hasPermissionTo('employee-attendance-view'))
                                        <a class="dropdown-item" href="{{ route('employee-attendances.show', $attendance) }}">
                                            <i class="ti ti-eye me-1"></i> View
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermissionTo('employee-attendance-edit'))
                                        <a class="dropdown-item" href="{{ route('employee-attendances.edit', $attendance) }}">
                                            <i class="ti ti-edit me-1"></i> Edit
                                        </a>
                                        @endif
                                        @if(auth()->user()->hasPermissionTo('employee-attendance-delete'))
                                        <a class="dropdown-item text-danger" href="#" onclick="deleteAttendance({{ $attendance->id }})">
                                            <i class="ti ti-trash me-1"></i> Delete
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    Showing {{ $attendances->firstItem() }} to {{ $attendances->lastItem() }} of {{ $attendances->total() }} entries
                </div>
                <div>
                    {{ $attendances->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="ti ti-calendar-x text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">No attendance records found</h5>
                <p class="text-muted">Start by adding employee attendance records.</p>
                <a href="{{ route('employee-attendances.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Add First Attendance
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
function deleteAttendance(attendanceId) {
    if (confirm('Are you sure you want to delete this attendance record?')) {
        fetch(`/app/employee-attendance/${attendanceId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                location.reload();
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('An error occurred while deleting the attendance record.');
        });
    }
}
</script>
@endsection
