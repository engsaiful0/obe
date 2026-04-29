@extends('layouts/layoutMaster')

@section('title', 'Create Yearly Salary Settings')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: "Select an option",
        allowClear: true
    });

    // Spinner handlers
    function showSpinner() {
        const $btn = $('#yearly-submit-btn');
        $btn.prop('disabled', true);
        $('#yearly-submit-spinner').removeClass('d-none');
        $('#yearly-submit-text').text('Saving...');
        $('#yearly-settings-form .btn').not('#yearly-submit-btn').prop('disabled', true);
    }

    function hideSpinner() {
        const $btn = $('#yearly-submit-btn');
        $btn.prop('disabled', false);
        $('#yearly-submit-spinner').addClass('d-none');
        $('#yearly-submit-text').text('Create Yearly Settings');
        $('#yearly-settings-form .btn').prop('disabled', false);
    }

    // AJAX submit
    $('#yearly-settings-form').on('submit', function(e) {
        e.preventDefault();

        showSpinner();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            headers: { 'Accept': 'application/json' },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message || 'Yearly salary settings created successfully.'
                }).then(function() {
                    window.location.href = "{{ route('monthly-salary-settings.index') }}";
                });
            },
            error: function(xhr) {
                let msg = 'Failed to create yearly settings. Please try again.';
                if (xhr.responseJSON && (xhr.responseJSON.error || xhr.responseJSON.message)) {
                    msg = xhr.responseJSON.error || xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
                hideSpinner();
            }
        });
    });

    // Update month preview
    function updateMonthPreview() {
        const year = $('#year').val();
        const workingDays = $('#default_working_days').val();
        const holidays = $('#default_holidays').val();
        
        if (year && workingDays) {
            let previewHtml = '<h6>Month Preview:</h6><div class="row g-2">';
            
            for (let month = 1; month <= 12; month++) {
                const monthName = new Date(year, month - 1, 1).toLocaleString('default', { month: 'long' });
                const totalDays = new Date(year, month, 0).getDate();
                
                previewHtml += `
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-body p-2">
                                <h6 class="card-title mb-1">${monthName}</h6>
                                <small class="text-muted">
                                    Working: ${workingDays} | Holidays: ${holidays || 0}<br>
                                    Total Days: ${totalDays}
                                </small>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            previewHtml += '</div>';
            $('#month-preview').html(previewHtml);
        }
    }

    // Update preview on change
    $('#year, #default_working_days, #default_holidays').on('change', updateMonthPreview);
    
    // Initial preview
    updateMonthPreview();
});
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Create Yearly Salary Settings</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            This will create salary settings for all 12 months of the selected year with the same default values. 
            You can edit individual months later if needed.
        </div>

        <form id="yearly-settings-form" action="{{ route('monthly-salary-settings.yearly.store') }}" method="POST">
            @csrf
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                    <select id="year" name="year" class="form-select select2" required>
                        <option value="">Select Year</option>
                        @foreach($years as $year)
                        <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                        @endforeach
                    </select>
                    @error('year')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="default_working_days" class="form-label">Default Working Days <span class="text-danger">*</span></label>
                    <input type="number" id="default_working_days" name="default_working_days" class="form-control" 
                           min="1" max="31" value="22" required>
                    <small class="text-muted">This will be applied to all months. You can edit individual months later.</small>
                    @error('default_working_days')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="default_holidays" class="form-label">Default Holidays</label>
                    <input type="number" id="default_holidays" name="default_holidays" class="form-control" 
                           min="0" max="31" value="0">
                    <small class="text-muted">Default number of holidays per month.</small>
                    @error('default_holidays')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="overtime_rate" class="form-label">Default Overtime Rate</label>
                    <input type="number" id="overtime_rate" name="overtime_rate" class="form-control" 
                           step="0.01" min="0" value="0">
                    <small class="text-muted">Default overtime rate per hour.</small>
                    @error('overtime_rate')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Month Preview -->
            <div class="mt-4" id="month-preview">
                <!-- Preview will be generated here -->
            </div>

            <!-- Attendance Rules Section -->
            <div class="mt-4">
                <h6>Default Attendance Rules</h6>
                <p class="text-muted">These rules will be applied to all months. You can customize individual months later.</p>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-body p-3">
                                <h6 class="card-title">Full Day</h6>
                                <p class="card-text small text-muted">Value: 1.0</p>
                                <p class="card-text small">Complete working day</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-body p-3">
                                <h6 class="card-title">Half Day</h6>
                                <p class="card-text small text-muted">Value: 0.5</p>
                                <p class="card-text small">Half working day</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-body p-3">
                                <h6 class="card-title">Leave</h6>
                                <p class="card-text small text-muted">Value: 0.0</p>
                                <p class="card-text small">Authorized leave</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-body p-3">
                                <h6 class="card-title">Absence</h6>
                                <p class="card-text small text-muted">Value: 0.0</p>
                                <p class="card-text small">Unauthorized absence</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overtime Rules Section -->
            <div class="mt-4">
                <h6>Default Overtime Rules</h6>
                <p class="text-muted">These rules will be applied to all months. You can customize individual months later.</p>
                
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-body p-3">
                                <h6 class="card-title">Rate Multiplier</h6>
                                <p class="card-text small">1.5x</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-body p-3">
                                <h6 class="card-title">Minimum Hours</h6>
                                <p class="card-text small">1 hour</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-body p-3">
                                <h6 class="card-title">Maximum Hours/Day</h6>
                                <p class="card-text small">4 hours</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border">
                            <div class="card-body p-3">
                                <h6 class="card-title">Calculation</h6>
                                <p class="card-text small">Hourly</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex align-items-center gap-2">
                <button id="yearly-submit-btn" type="submit" class="btn btn-primary">
                    <span id="yearly-submit-spinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <i class="ti ti-calendar-plus me-1"></i> <span id="yearly-submit-text">Create Yearly Settings</span>
                </button>
                <a href="{{ route('monthly-salary-settings.index') }}" class="btn btn-secondary">
                    <i class="ti ti-x me-1"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
