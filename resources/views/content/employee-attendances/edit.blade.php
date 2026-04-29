@extends('layouts/layoutMaster')

@section('title', 'Edit Employee Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Employee Attendance</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('employee-attendances.show', $employeeAttendance) }}" class="btn btn-outline-primary">
            <i class="ti ti-eye me-1"></i> View
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
                <h5 class="card-title mb-0">Edit Attendance Information</h5>
            </div>
            <div class="card-body">
                <form id="attendanceEditForm" action="{{ route('employee-attendances.update', $employeeAttendance) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                            <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ old('employee_id', $employeeAttendance->employee_id) == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->employee_name }} ({{ $employee->employee_unique_id }})
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="attendance_date" class="form-label">Attendance Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('attendance_date') is-invalid @enderror" 
                                   id="attendance_date" name="attendance_date" 
                                   value="{{ old('attendance_date', $employeeAttendance->attendance_date->format('Y-m-d')) }}" required>
                            @error('attendance_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="check_in_time" class="form-label">Check In Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('check_in_time') is-invalid @enderror" 
                                   id="check_in_time" name="check_in_time" 
                                   value="{{ old('check_in_time', $employeeAttendance->check_in_time->format('H:i')) }}" required>
                            @error('check_in_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="check_out_time" class="form-label">Check Out Time</label>
                            <input type="time" class="form-control @error('check_out_time') is-invalid @enderror" 
                                   id="check_out_time" name="check_out_time" 
                                   value="{{ old('check_out_time', $employeeAttendance->check_out_time ? $employeeAttendance->check_out_time->format('H:i') : '') }}">
                            @error('check_out_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="present" {{ old('status', $employeeAttendance->status) == 'present' ? 'selected' : '' }}>Present</option>
                                <option value="absent" {{ old('status', $employeeAttendance->status) == 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="late" {{ old('status', $employeeAttendance->status) == 'late' ? 'selected' : '' }}>Late</option>
                                <option value="early_leave" {{ old('status', $employeeAttendance->status) == 'early_leave' ? 'selected' : '' }}>Early Leave</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3" 
                                      placeholder="Enter any remarks...">{{ old('remarks', $employeeAttendance->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" id="updateBtn" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i> <span class="btn-text">Update Attendance</span>
                            </button>
                            <a href="{{ route('employee-attendances.show', $employeeAttendance) }}" class="btn btn-outline-secondary ms-2">
                                <i class="ti ti-eye me-1"></i> View
                            </a>
                            <a href="{{ route('employee-attendances.index') }}" class="btn btn-outline-secondary ms-2">
                                <i class="ti ti-x me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Current Information</h5>
            </div>
            <div class="card-body">
                <p><strong>Employee:</strong> {{ $employeeAttendance->employee->employee_name }}</p>
                <p><strong>Date:</strong> {{ $employeeAttendance->attendance_date->format('M d, Y') }}</p>
                <p><strong>Check In:</strong> {{ $employeeAttendance->formatted_check_in_time }}</p>
                <p><strong>Check Out:</strong> {{ $employeeAttendance->formatted_check_out_time ?? 'Not recorded' }}</p>
                <p><strong>Status:</strong> 
                    @switch($employeeAttendance->status)
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
                </p>
                @if($employeeAttendance->remarks)
                    <p><strong>Remarks:</strong> {{ $employeeAttendance->remarks }}</p>
                @endif
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('employee-attendances.show', $employeeAttendance) }}" class="btn btn-outline-primary">
                        <i class="ti ti-eye me-1"></i> View Details
                    </a>
                    <a href="{{ route('employee-attendances.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-list me-1"></i> View All Attendance
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // AJAX form submission
    const form = document.getElementById('attendanceEditForm');
    const submitBtn = document.getElementById('updateBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnIcon = submitBtn.querySelector('i');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        showLoadingState();
        
        // Collect form data
        const formData = new FormData(form);
        
        // Make AJAX request
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.message) {
                // Success
                toastr.success(data.message || 'Employee attendance updated successfully!');
                setTimeout(() => {
                    window.location.href = '{{ route("employee-attendances.show", $employeeAttendance) }}';
                }, 1500);
            } else {
                // Handle validation errors
                if (data.errors) {
                    displayValidationErrors(data.errors);
                } else {
                    toastr.error(data.message || 'An error occurred while updating attendance.');
                }
                hideLoadingState();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('An error occurred while updating attendance.');
            hideLoadingState();
        });
    });

    function showLoadingState() {
        submitBtn.disabled = true;
        btnIcon.className = 'ti ti-loader-2 me-1 fa-spin';
        btnText.textContent = 'Updating...';
        form.style.opacity = '0.7';
        form.style.pointerEvents = 'none';
    }

    function hideLoadingState() {
        submitBtn.disabled = false;
        btnIcon.className = 'ti ti-check me-1';
        btnText.textContent = 'Update Attendance';
        form.style.opacity = '1';
        form.style.pointerEvents = 'auto';
    }

    function displayValidationErrors(errors) {
        // Clear previous errors
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });

        // Display new errors
        Object.keys(errors).forEach(field => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = errors[field][0];
                input.parentNode.appendChild(errorDiv);
            }
        });
    }
});
</script>
@endsection
