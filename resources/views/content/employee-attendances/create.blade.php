@extends('layouts/layoutMaster')

@section('title', 'Add Employee Attendance')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Add Employee Attendance</h4>
    <a href="{{ route('employee-attendances.index') }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Attendance Information</h5>
            </div>
            <div class="card-body">
                <form id="attendanceForm" action="{{ route('employee-attendances.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                            <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
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
                                value="{{ old('attendance_date', date('Y-m-d')) }}" required>
                            @error('attendance_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="check_in_time" class="form-label">Check In Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('check_in_time') is-invalid @enderror"
                                id="check_in_time" name="check_in_time"
                                value="{{ old('check_in_time', '09:00') }}" required>
                            @error('check_in_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="check_out_time" class="form-label">Check Out Time</label>
                            <input type="time" class="form-control @error('check_out_time') is-invalid @enderror"
                                id="check_out_time" name="check_out_time"
                                value="{{ old('check_out_time', '16:30') }}">
                            @error('check_out_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>Present</option>
                                <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Late</option>
                                <option value="early_leave" {{ old('status') == 'early_leave' ? 'selected' : '' }}>Early Leave</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror"
                                id="remarks" name="remarks" rows="3"
                                placeholder="Enter any remarks...">{{ old('remarks') }}</textarea>
                            @error('remarks')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" id="submitBtn" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i> <span class="btn-text">Save Attendance</span>
                            </button>
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
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('employee-attendances.add-all-attendance') }}" class="btn btn-success">
                        <i class="ti ti-users me-1"></i> Add All Attendance
                    </a>
                    <a href="{{ route('employee-attendances.index') }}" class="btn btn-outline-primary">
                        <i class="ti ti-list me-1"></i> View All Attendance
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Help</h5>
            </div>
            <div class="card-body">
                <h6>Status Types:</h6>
                <ul class="list-unstyled">
                    <li><span class="badge bg-label-success me-1">Present</span> - Employee attended normally</li>
                    <li><span class="badge bg-label-danger me-1">Absent</span> - Employee did not attend</li>
                    <li><span class="badge bg-label-warning me-1">Late</span> - Employee came late</li>
                    <li><span class="badge bg-label-info me-1">Early Leave</span> - Employee left early</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Set default status if empty
    if (!$('#status').val()) {
        $('#status').val('present');
    }

    // AJAX form submission
    $('#attendanceForm').on('submit', function(e) {
        e.preventDefault();

        const form = $(this);
        const submitBtn = $('#submitBtn');
        const btnText = submitBtn.find('.btn-text');
        const btnIcon = submitBtn.find('i');

        // Show spinner & disable form
        showSpinner();

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success) {
                    toastr.success(data.message || 'Employee attendance saved successfully!');
                    setTimeout(function() {
                        window.location.href = "{{ route('employee-attendances.index') }}";
                    }, 1200);
                } else if (data.errors) {
                    displayValidationErrors(data.errors);
                    toastr.warning('Please correct the highlighted fields.');
                    hideSpinner();
                } else {
                    toastr.error(data.message || 'An unexpected error occurred.');
                    hideSpinner();
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    displayValidationErrors(xhr.responseJSON.errors);
                    toastr.warning('Please correct the highlighted fields.');
                } else {
                    toastr.error('Something went wrong while saving attendance.');
                }
                hideSpinner();
            }
        });

        function showSpinner() {
            submitBtn.prop('disabled', true);
            btnIcon.removeClass().addClass('ti ti-loader-2 me-1 fa-spin');
            btnText.text('Saving...');
            form.css({
                'opacity': '0.7',
                'pointer-events': 'none'
            });
        }

        function hideSpinner() {
            submitBtn.prop('disabled', false);
            btnIcon.removeClass().addClass('ti ti-check me-1');
            btnText.text('Save Attendance');
            form.css({
                'opacity': '1',
                'pointer-events': 'auto'
            });
        }

        function displayValidationErrors(errors) {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            $.each(errors, function(field, messages) {
                const input = $('[name="' + field + '"]');
                if (input.length) {
                    input.addClass('is-invalid');
                    const errorDiv = $('<div class="invalid-feedback"></div>').text(messages[0]);
                    input.parent().append(errorDiv);
                }
            });
        }
    });
});
</script>
@endsection
