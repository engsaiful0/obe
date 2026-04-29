@extends('layouts/layoutMaster')

@section('title', 'Monthly Salary Setting Details')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Salary Calculator
    $('#calculate-salary-btn').on('click', function() {
        const basicSalary = parseFloat($('#basic-salary').val()) || 0;
        const presentDays = parseFloat($('#present-days').val()) || 0;
        const overtimeHours = parseFloat($('#overtime-hours').val()) || 0;
        const deductions = parseFloat($('#deductions').val()) || 0;

        if (!basicSalary || !presentDays) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Basic salary and present days are required for calculation.'
            });
            return;
        }

        // Calculate using the setting's method
        const workingDays = {{ $monthlySalarySetting->total_working_days }};
        const dailyRate = basicSalary / workingDays;
        const baseSalary = dailyRate * presentDays;
        
        let overtimeAmount = 0;
        const overtimeRules = @json($monthlySalarySetting->overtime_rules);
        if (overtimeHours > 0 && overtimeRules && overtimeRules.enabled) {
            const overtimeRate = dailyRate * (overtimeRules.rate_multiplier || 1.5);
            overtimeAmount = overtimeRate * overtimeHours;
        }
        
        const totalSalary = baseSalary + overtimeAmount - deductions;

        // Display results
        $('#calculation-results').html(`
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6 class="card-title">Basic Information</h6>
                            <div class="row g-2">
                                <div class="col-6"><small class="text-muted">Basic Salary:</small><br><strong>${basicSalary.toFixed(2)}</strong></div>
                                <div class="col-6"><small class="text-muted">Daily Rate:</small><br><strong>${dailyRate.toFixed(2)}</strong></div>
                                <div class="col-6"><small class="text-muted">Present Days:</small><br><strong>${presentDays}</strong></div>
                                <div class="col-6"><small class="text-muted">Working Days:</small><br><strong>${workingDays}</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="card-title">Calculation</h6>
                            <div class="row g-2">
                                <div class="col-6"><small class="text-muted">Base Salary:</small><br><strong>${baseSalary.toFixed(2)}</strong></div>
                                <div class="col-6"><small class="text-muted">Overtime Amount:</small><br><strong>${overtimeAmount.toFixed(2)}</strong></div>
                                <div class="col-6"><small class="text-muted">Deductions:</small><br><strong>${deductions.toFixed(2)}</strong></div>
                                <div class="col-6"><small class="text-muted">Total Salary:</small><br><strong class="text-success">${totalSalary.toFixed(2)}</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    });
});
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Monthly Salary Setting Details</h5>
        <div>
            <a href="{{ route('monthly-salary-settings.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </a>
            <a href="{{ route('monthly-salary-settings.edit', $monthlySalarySetting) }}" class="btn btn-primary">
                <i class="ti ti-edit me-1"></i> Edit
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Basic Information -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="card-title mb-0">Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label text-muted">Year</label>
                                <div class="fw-bold">{{ $monthlySalarySetting->year }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted">Month</label>
                                <div class="fw-bold">{{ $monthlySalarySetting->month_name }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted">Working Days</label>
                                <div class="fw-bold text-primary">{{ $monthlySalarySetting->total_working_days }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted">Official Holidays</label>
                                <div class="fw-bold text-warning">{{ $monthlySalarySetting->official_holidays }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted">Overtime Rate</label>
                                <div class="fw-bold">{{ number_format($monthlySalarySetting->default_overtime_rate, 2) }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted">Status</label>
                                <div>
                                    @if($monthlySalarySetting->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="card-title mb-0">Month Statistics</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label text-muted">Total Days in Month</label>
                                <div class="fw-bold">{{ \Carbon\Carbon::createFromDate($monthlySalarySetting->year, $monthlySalarySetting->month, 1)->daysInMonth }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted">Working Days</label>
                                <div class="fw-bold text-primary">{{ $monthlySalarySetting->total_working_days }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted">Holidays</label>
                                <div class="fw-bold text-warning">{{ $monthlySalarySetting->official_holidays }}</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted">Other Days</label>
                                <div class="fw-bold text-muted">
                                    {{ \Carbon\Carbon::createFromDate($monthlySalarySetting->year, $monthlySalarySetting->month, 1)->daysInMonth - $monthlySalarySetting->total_working_days - $monthlySalarySetting->official_holidays }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Rules -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title mb-0">Attendance Rules</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($monthlySalarySetting->attendance_rules as $rule => $config)
                            <div class="col-md-3">
                                <div class="card border-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">{{ $config['label'] }}</h6>
                                        <div class="display-6 text-primary">{{ $config['value'] }}</div>
                                        <small class="text-muted">{{ $config['description'] }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overtime Rules -->
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="card-title mb-0">Overtime Rules</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="card border-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Enabled</h6>
                                        <div class="display-6 text-{{ $monthlySalarySetting->overtime_rules['enabled'] ? 'success' : 'danger' }}">
                                            {{ $monthlySalarySetting->overtime_rules['enabled'] ? 'Yes' : 'No' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Rate Multiplier</h6>
                                        <div class="display-6 text-primary">{{ $monthlySalarySetting->overtime_rules['rate_multiplier'] }}x</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Minimum Hours</h6>
                                        <div class="display-6 text-info">{{ $monthlySalarySetting->overtime_rules['minimum_hours'] }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Max Hours/Day</h6>
                                        <div class="display-6 text-warning">{{ $monthlySalarySetting->overtime_rules['maximum_hours_per_day'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes -->
        @if($monthlySalarySetting->notes)
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="card-title mb-0">Notes & Special Adjustments</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $monthlySalarySetting->notes }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Salary Calculator -->
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-secondary">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="card-title mb-0">Salary Calculator</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="basic-salary" class="form-label">Basic Salary</label>
                                <input type="number" id="basic-salary" class="form-control" step="0.01" min="0" placeholder="Enter basic salary">
                            </div>
                            <div class="col-md-3">
                                <label for="present-days" class="form-label">Actual Present Days</label>
                                <input type="number" id="present-days" class="form-control" step="0.01" min="0" placeholder="Enter present days">
                            </div>
                            <div class="col-md-3">
                                <label for="overtime-hours" class="form-label">Overtime Hours</label>
                                <input type="number" id="overtime-hours" class="form-control" step="0.01" min="0" value="0" placeholder="Enter overtime hours">
                            </div>
                            <div class="col-md-3">
                                <label for="deductions" class="form-label">Deductions</label>
                                <input type="number" id="deductions" class="form-control" step="0.01" min="0" value="0" placeholder="Enter deductions">
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="button" id="calculate-salary-btn" class="btn btn-primary">
                                <i class="ti ti-calculator me-1"></i> Calculate Salary
                            </button>
                        </div>
                        
                        <div id="calculation-results" class="mt-4" style="display: none;">
                            <!-- Results will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
