@extends('layouts/layoutMaster')

@section('title', 'Create Monthly Salary Setting')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: "Select an option",
        allowClear: true
    });

    // AJAX submit with spinner
    function showSpinner() {
        // Disable submit and show spinner inside the button
        const $btn = $('#create-submit-btn');
        $btn.prop('disabled', true);
        $('#create-submit-spinner').removeClass('d-none');
        $('#create-submit-text').text('Saving...');
        // Disable other buttons to prevent interaction
        $('#create-form .btn').not('#create-submit-btn').prop('disabled', true);
    }

    function hideSpinner() {
        const $btn = $('#create-submit-btn');
        $btn.prop('disabled', false);
        $('#create-submit-spinner').addClass('d-none');
        $('#create-submit-text').text('Create Setting');
        $('#create-form .btn').prop('disabled', false);
    }

    $('#create-form').on('submit', function(e) {
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
                    text: response.message || 'Monthly salary settings created successfully.'
                }).then(function() {
                    window.location.href = "{{ route('monthly-salary-settings.index') }}";
                });
            },
            error: function(xhr) {
                let msg = 'Failed to save. Please check the form and try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
                hideSpinner();
            }
        });
    });

    // Update month preview
    function updateMonthPreview() {
        const year = $('#year').val();
        const month = $('#month').val();
        const workingDays = $('#total_working_days').val();
        const holidays = $('#official_holidays').val();
        
        if (year && month && workingDays) {
            const monthName = new Date(year, month - 1, 1).toLocaleString('default', { month: 'long' });
            const totalDays = new Date(year, month, 0).getDate();
            
            $('#month-preview').html(`
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="card-title mb-0">Month Preview: ${monthName} ${year}</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="display-6 text-primary">${totalDays}</div>
                                    <small class="text-muted">Total Days</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="display-6 text-success">${workingDays}</div>
                                    <small class="text-muted">Working Days</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="display-6 text-warning">${holidays || 0}</div>
                                    <small class="text-muted">Holidays</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="display-6 text-muted">${totalDays - workingDays - (holidays || 0)}</div>
                                    <small class="text-muted">Other Days</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
    }

    // Update preview on change
    $('#year, #month, #total_working_days, #official_holidays').on('change', updateMonthPreview);
    
    // Initial preview
    updateMonthPreview();

    // Attendance rules management
    function updateAttendanceRules() {
        const rules = {
            'full_day': { label: 'Full Day', value: 1.0, description: 'Complete working day' },
            'half_day': { label: 'Half Day', value: 0.5, description: 'Half working day' },
            'leave': { label: 'Leave', value: 0.0, description: 'Authorized leave' },
            'absence': { label: 'Absence', value: 0.0, description: 'Unauthorized absence' }
        };

        let html = '';
        Object.keys(rules).forEach(rule => {
            const config = rules[rule];
            html += `
                <div class="col-md-3">
                    <div class="card border-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">${config.label}</h6>
                            <div class="display-6 text-primary">${config.value}</div>
                            <small class="text-muted">${config.description}</small>
                        </div>
                    </div>
                </div>
            `;
        });
        
        $('#attendance-rules-preview').html(html);
    }

    // Overtime rules management
    function updateOvertimeRules() {
        const enabled = $('#overtime_enabled').is(':checked');
        const rateMultiplier = $('#overtime_rate_multiplier').val();
        const minHours = $('#overtime_minimum_hours').val();
        const maxHours = $('#overtime_maximum_hours').val();
        const calculationMethod = $('#overtime_calculation_method').val();

        $('#overtime-rules-preview').html(`
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card border-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Enabled</h6>
                            <div class="display-6 text-${enabled ? 'success' : 'danger'}">${enabled ? 'Yes' : 'No'}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Rate Multiplier</h6>
                            <div class="display-6 text-primary">${rateMultiplier}x</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Min Hours</h6>
                            <div class="display-6 text-info">${minHours}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-light">
                        <div class="card-body text-center">
                            <h6 class="card-title">Max Hours/Day</h6>
                            <div class="display-6 text-warning">${maxHours}</div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    // Update previews on change
    $('#overtime_enabled, #overtime_rate_multiplier, #overtime_minimum_hours, #overtime_maximum_hours, #overtime_calculation_method').on('change', updateOvertimeRules);
    
    // Initial previews
    updateAttendanceRules();
    updateOvertimeRules();
});
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Create Monthly Salary Setting</h5>
    </div>
    <div class="card-body">
        <form id="create-form" action="{{ route('monthly-salary-settings.store') }}" method="POST">
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
                    <label for="month" class="form-label">Month <span class="text-danger">*</span></label>
                    <select id="month" name="month" class="form-select select2" required>
                        <option value="">Select Month</option>
                        @foreach($months as $monthNum => $monthName)
                        <option value="{{ $monthNum }}">{{ $monthName }}</option>
                        @endforeach
                    </select>
                    @error('month')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="total_working_days" class="form-label">Total Working Days <span class="text-danger">*</span></label>
                    <input type="number" id="total_working_days" name="total_working_days" class="form-control" 
                           min="1" max="31" value="22" required>
                    @error('total_working_days')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="official_holidays" class="form-label">Official Holidays</label>
                    <input type="number" id="official_holidays" name="official_holidays" class="form-control" 
                           min="0" max="31" value="0">
                    @error('official_holidays')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="default_overtime_rate" class="form-label">Default Overtime Rate</label>
                    <input type="number" id="default_overtime_rate" name="default_overtime_rate" class="form-control" 
                           step="0.01" min="0" value="0">
                    @error('default_overtime_rate')
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
                <h6>Attendance Rules</h6>
                <p class="text-muted">These rules define how different attendance types are calculated.</p>
                
                <div class="row g-3" id="attendance-rules-preview">
                    <!-- Preview will be generated here -->
                </div>
            </div>

            <!-- Overtime Rules Section -->
            <div class="mt-4">
                <h6>Overtime Rules</h6>
                <p class="text-muted">Configure overtime calculation rules for this month.</p>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="overtime_enabled" checked>
                            <label class="form-check-label" for="overtime_enabled">
                                Enable Overtime
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="overtime_rate_multiplier" class="form-label">Rate Multiplier</label>
                        <input type="number" id="overtime_rate_multiplier" class="form-control" 
                               step="0.1" min="0" value="1.5">
                    </div>
                    <div class="col-md-6">
                        <label for="overtime_minimum_hours" class="form-label">Minimum Hours</label>
                        <input type="number" id="overtime_minimum_hours" class="form-control" 
                               min="0" value="1">
                    </div>
                    <div class="col-md-6">
                        <label for="overtime_maximum_hours" class="form-label">Maximum Hours per Day</label>
                        <input type="number" id="overtime_maximum_hours" class="form-control" 
                               min="0" value="4">
                    </div>
                </div>
                
                <div class="mt-3" id="overtime-rules-preview">
                    <!-- Preview will be generated here -->
                </div>
            </div>

            <!-- Notes Section -->
            <div class="mt-4">
                <label for="notes" class="form-label">Notes & Special Adjustments</label>
                <textarea id="notes" name="notes" class="form-control" rows="4" 
                          placeholder="Enter any special notes or adjustments for this month..."></textarea>
                @error('notes')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-4 d-flex align-items-center gap-2">
                <button id="create-submit-btn" type="submit" class="btn btn-primary">
                    <span id="create-submit-spinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                    <i class="ti ti-check me-1"></i> <span id="create-submit-text">Create Setting</span>
                </button>
                <a href="{{ route('monthly-salary-settings.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-x me-1"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection