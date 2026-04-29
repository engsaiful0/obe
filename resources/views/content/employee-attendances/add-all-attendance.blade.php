@extends('layouts/layoutMaster')

@section('title', 'Add All Employee Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Add All Employee Attendance</h4>
    <a href="{{ route('employee-attendances.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Bulk Attendance Entry</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('employee-attendances.submit-all-attendance') }}" method="POST" id="bulkAttendanceForm">
                    @csrf
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="attendance_date" class="form-label">Attendance Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('attendance_date') is-invalid @enderror" 
                                   id="attendance_date" name="attendance_date" 
                                   value="{{ old('attendance_date', date('Y-m-d')) }}" required>
                            @error('attendance_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary mt-4" onclick="setAllPresent()">
                                <i class="ti ti-check me-1"></i> Set All Present
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-warning mt-4" onclick="setAllAbsent()">
                                <i class="ti ti-x me-1"></i> Set All Absent
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Employee</th>
                                    <th width="15%">Check In Time</th>
                                    <th width="15%">Check Out Time</th>
                                    <th width="15%">Status</th>
                                    <th width="25%">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($employees as $index => $employee)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($employee->picture)
                                                <img src="{{ asset('storage/app/public/' . $employee->picture) }}" 
                                                     alt="{{ $employee->employee_name }}" 
                                                     class="rounded-circle me-2" width="32" height="32">
                                            @else
                                                <div class="avatar-initial rounded-circle bg-label-primary me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                    {{ substr($employee->employee_name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="mb-0">{{ $employee->employee_name }}</h6>
                                                <small class="text-muted">{{ $employee->employee_unique_id }}</small>
                                            </div>
                                        </div>
                                        <input type="hidden" name="attendances[{{ $index }}][employee_id]" value="{{ $employee->id }}">
                                    </td>
                                    <td>
                                        <input type="time" class="form-control" 
                                               name="attendances[{{ $index }}][check_in_time]" 
                                               value="{{ old("attendances.$index.check_in_time", '09:00') }}">
                                    </td>
                                    <td>
                                        <input type="time" class="form-control" 
                                               name="attendances[{{ $index }}][check_out_time]" 
                                               value="{{ old("attendances.$index.check_out_time", '16:30') }}">
                                    </td>
                                    <td>
                                        <select class="form-select" name="attendances[{{ $index }}][status]">
                                            <option value="present" {{ old("attendances.$index.status") == 'present' ? 'selected' : '' }}>Present</option>
                                            <option value="absent" {{ old("attendances.$index.status") == 'absent' ? 'selected' : '' }}>Absent</option>
                                            <option value="late" {{ old("attendances.$index.status") == 'late' ? 'selected' : '' }}>Late</option>
                                            <option value="early_leave" {{ old("attendances.$index.status") == 'early_leave' ? 'selected' : '' }}>Early Leave</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" 
                                               name="attendances[{{ $index }}][remarks]" 
                                               value="{{ old("attendances.$index.remarks") }}" 
                                               placeholder="Enter remarks...">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($employees->count() == 0)
                        <div class="text-center py-5">
                            <i class="ti ti-users text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No employees found</h5>
                            <p class="text-muted">Please add employees first before managing attendance.</p>
                            <a href="{{ route('employees.add-employee') }}" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i> Add Employee
                            </a>
                        </div>
                    @else
                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" id="bulkSubmitBtn" class="btn btn-success">
                                    <i class="ti ti-check me-1"></i> <span class="btn-text">Save All Attendance</span>
                                </button>
                                <a href="{{ route('employee-attendances.index') }}" class="btn btn-outline-secondary ms-2">
                                    <i class="ti ti-x me-1"></i> Cancel
                                </a>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
function setAllPresent() {
    const statusSelects = document.querySelectorAll('select[name*="[status]"]');
    const checkInInputs = document.querySelectorAll('input[name*="[check_in_time]"]');
    const checkOutInputs = document.querySelectorAll('input[name*="[check_out_time]"]');
    
    statusSelects.forEach(select => select.value = 'present');
    checkInInputs.forEach(input => input.value = '09:00');
    checkOutInputs.forEach(input => input.value = '16:30');
    
    toastr.success('All employees set to Present');
}

function setAllAbsent() {
    const statusSelects = document.querySelectorAll('select[name*="[status]"]');
    const checkInInputs = document.querySelectorAll('input[name*="[check_in_time]"]');
    const checkOutInputs = document.querySelectorAll('input[name*="[check_out_time]"]');
    
    statusSelects.forEach(select => select.value = 'absent');
    checkInInputs.forEach(input => input.value = '');
    checkOutInputs.forEach(input => input.value = '');
    
    toastr.warning('All employees set to Absent');
}

document.addEventListener('DOMContentLoaded', function() {
    // Set default values
    const checkInInputs = document.querySelectorAll('input[name*="[check_in_time]"]');
    const checkOutInputs = document.querySelectorAll('input[name*="[check_out_time]"]');
    checkInInputs.forEach(input => { if(!input.value) input.value = '09:00'; });
    checkOutInputs.forEach(input => { if(!input.value) input.value = '16:30'; });

    // AJAX submission
    const form = document.getElementById('bulkAttendanceForm');
    const submitBtn = document.getElementById('bulkSubmitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnIcon = submitBtn.querySelector('i');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        showBulkLoadingState();
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.success || data.message) {
                toastr.success(data.message || 'All employee attendance saved successfully!');
                setTimeout(() => { window.location.href = '{{ route("employee-attendances.index") }}'; }, 1500);
            } else if(data.errors) {
                displayBulkValidationErrors(data.errors);
                hideBulkLoadingState();
            } else {
                toastr.error(data.message || 'An error occurred.');
                hideBulkLoadingState();
            }
        })
        .catch(err => {
            console.error(err);
            toastr.error('An error occurred while saving attendance.');
            hideBulkLoadingState();
        });

        function showBulkLoadingState() {
            submitBtn.disabled = true;
            btnIcon.className = 'ti ti-loader-2 me-1 fa-spin';
            btnText.textContent = 'Saving All...';
            form.style.opacity = '0.7';
            form.style.pointerEvents = 'none';
        }

        function hideBulkLoadingState() {
            submitBtn.disabled = false;
            btnIcon.className = 'ti ti-check me-1';
            btnText.textContent = 'Save All Attendance';
            form.style.opacity = '1';
            form.style.pointerEvents = 'auto';
        }

        function displayBulkValidationErrors(errors) {
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            Object.keys(errors).forEach(field => {
                const input = document.querySelector(`[name="${field}"]`);
                if(input){
                    input.classList.add('is-invalid');
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = errors[field][0];
                    input.parentNode.appendChild(errorDiv);
                }
            });
        }
    });
});
</script>
@endsection
