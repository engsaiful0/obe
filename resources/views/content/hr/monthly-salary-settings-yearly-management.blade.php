@extends('layouts/layoutMaster')

@section('title', 'Yearly Salary Settings Management')

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
    let currentYear = null;
    let isLoading = false;

    // Initialize Select2
    $('.select2').select2({
        placeholder: "Select an option",
        allowClear: true
    });

    // Year selection change
    $('#year').on('change', function() {
        const year = $(this).val();
        if (year) {
            loadYearlySettings(year);
        } else {
            $('#months-container').html('');
            $('#create-yearly-section').hide();
        }
    });

    // Load yearly settings
    function loadYearlySettings(year) {
        if (isLoading) return;
        
        isLoading = true;
        currentYear = year;
        showSpinner();
        
        $.ajax({
            url: '{{ route("monthly-salary-settings.yearly.get-settings") }}',
            type: 'GET',
            data: { year: year },
            success: function(response) {
                displayMonths(response.months);
                $('#create-yearly-section').show();
                hideSpinner();
                isLoading = false;
            },
            error: function(xhr) {
                console.error('Error loading settings:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load yearly settings. Please try again.'
                });
                hideSpinner();
                isLoading = false;
            }
        });
    }

    // Display months in grid
    function displayMonths(months) {
        let html = '<div class="row g-3">';
        
        months.forEach(function(month) {
            const hasSetting = month.setting !== null;
            const statusClass = hasSetting ? 'success' : 'light';
            const statusText = hasSetting ? 'Configured' : 'Not Set';
            
            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="card border-${statusClass} h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">${month.month_name}</h6>
                            <span class="badge bg-${statusClass}">${statusText}</span>
                        </div>
                        <div class="card-body">
                            ${hasSetting ? `
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Working Days</small>
                                        <div class="fw-bold">${month.setting.total_working_days}</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Holidays</small>
                                        <div class="fw-bold">${month.setting.official_holidays}</div>
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Overtime Rate</small>
                                        <div class="fw-bold">${parseFloat(month.setting.default_overtime_rate).toFixed(2)}</div>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Total Days</small>
                                        <div class="fw-bold">${month.total_days}</div>
                                    </div>
                                </div>
                                ${month.setting.notes ? `
                                    <div class="mb-3">
                                        <small class="text-muted">Notes</small>
                                        <div class="small">${month.setting.notes.substring(0, 50)}${month.setting.notes.length > 50 ? '...' : ''}</div>
                                    </div>
                                ` : ''}
                            ` : `
                                <div class="text-center text-muted">
                                    <i class="ti ti-calendar-x ti-2x mb-2"></i>
                                    <div>No settings configured</div>
                                </div>
                            `}
                        </div>
                        <div class="card-footer">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-${hasSetting ? 'primary' : 'success'} btn-sm" 
                                        onclick="editMonth(${month.month}, '${month.month_name}', ${month.total_days}, ${hasSetting ? JSON.stringify(month.setting).replace(/"/g, '&quot;') : 'null'})">
                                    <i class="ti ti-${hasSetting ? 'edit' : 'plus'} me-1"></i>
                                    ${hasSetting ? 'Edit' : 'Create'}
                                </button>
                                ${hasSetting ? `
                                    <button type="button" class="btn btn-danger btn-sm" 
                                            onclick="deleteMonth(${month.month}, '${month.month_name}')">
                                        <i class="ti ti-trash me-1"></i>
                                        Delete
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        $('#months-container').html(html);
    }

    // Edit/Create month modal
    window.editMonth = function(month, monthName, totalDays, setting) {
        const isEdit = setting !== null;
        
        // Populate form
        $('#month-modal-title').text(isEdit ? `Edit ${monthName}` : `Create ${monthName}`);
        $('#month-modal-month').val(month);
        $('#month-modal-month-name').text(monthName);
        $('#month-modal-total-days').text(totalDays);
        $('#month-modal-working-days').val(isEdit ? setting.total_working_days : '');
        $('#month-modal-holidays').val(isEdit ? setting.official_holidays : '');
        $('#month-modal-overtime-rate').val(isEdit ? setting.default_overtime_rate : '');
        $('#month-modal-notes').val(isEdit ? setting.notes : '');
        // removed active toggle
        
        // Show modal
        $('#monthModal').modal('show');
    };

    // Delete month
    window.deleteMonth = function(month, monthName) {
        Swal.fire({
            title: 'Delete Setting',
            text: `Are you sure you want to delete the setting for ${monthName}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteMonthSetting(month);
            }
        });
    };

    // Delete month setting
    function deleteMonthSetting(month) {
        showSpinner();
        
        $.ajax({
            url: '{{ route("monthly-salary-settings.yearly.delete-monthly") }}',
            type: 'DELETE',
            data: {
                year: currentYear,
                month: month,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message
                });
                loadYearlySettings(currentYear);
            },
            error: function(xhr) {
                console.error('Error deleting setting:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.error || 'Failed to delete setting. Please try again.'
                });
                hideSpinner();
            }
        });
    }

    // Save month setting
    $('#save-month-btn').on('click', function() {
        const formData = {
            year: currentYear,
            month: $('#month-modal-month').val(),
            total_working_days: $('#month-modal-working-days').val(),
            official_holidays: $('#month-modal-holidays').val(),
            default_overtime_rate: $('#month-modal-overtime-rate').val(),
            notes: $('#month-modal-notes').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // Validation
        if (!formData.total_working_days) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Working days is required.'
            });
            return;
        }

        showSpinner();
        
        $.ajax({
            url: '{{ route("monthly-salary-settings.yearly.update-monthly") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message
                });
                $('#monthModal').modal('hide');
                loadYearlySettings(currentYear);
            },
            error: function(xhr) {
                console.error('Error saving setting:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to save setting. Please try again.'
                });
                hideSpinner();
            }
        });
    });

    // Create yearly settings
    $('#create-yearly-btn').on('click', function() {
        const year = $('#year').val();
        const workingDays = $('#default-working-days').val();
        const holidays = $('#default-holidays').val();
        const overtimeRate = $('#default-overtime-rate').val();

        if (!year || !workingDays) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Year and working days are required.'
            });
            return;
        }

        Swal.fire({
            title: 'Create Yearly Settings',
            text: `This will create settings for all 12 months of ${year}. Are you sure?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, create them!'
        }).then((result) => {
            if (result.isConfirmed) {
                createYearlySettings(year, workingDays, holidays, overtimeRate);
            }
        });
    });

    // Create yearly settings function
    function createYearlySettings(year, workingDays, holidays, overtimeRate) {
        showSpinner();
        
        $.ajax({
            url: '{{ route("monthly-salary-settings.yearly.create-yearly-ajax") }}',
            type: 'POST',
            data: {
                year: year,
                default_working_days: workingDays,
                default_holidays: holidays,
                overtime_rate: overtimeRate,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message
                });
                loadYearlySettings(year);
            },
            error: function(xhr) {
                console.error('Error creating yearly settings:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.error || 'Failed to create yearly settings. Please try again.'
                });
                hideSpinner();
            }
        });
    }

    // Spinner functions
    function showSpinner() {
        $('#loading-spinner').show();
        $('.btn').prop('disabled', true);
    }

    function hideSpinner() {
        $('#loading-spinner').hide();
        $('.btn').prop('disabled', false);
    }

    // Initialize with current year if available
    if ($('#year').val()) {
        loadYearlySettings($('#year').val());
    }
});
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Yearly Salary Settings Management</h5>
        <div>
            <a href="{{ route('monthly-salary-settings.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Year Selection -->
        <div class="row mb-4">
            <div class="col-md-6">
                <label for="year" class="form-label">Select Year <span class="text-danger">*</span></label>
                <select id="year" name="year" class="form-select select2" required>
                    <option value="">Select Year</option>
                    @foreach($years as $year)
                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div id="loading-spinner" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2">Loading settings...</div>
        </div>

        <!-- Create Yearly Settings Section -->
        <div id="create-yearly-section" class="card mb-4" style="display: none;">
            <div class="card-header">
                <h6 class="card-title mb-0">Create All 12 Months</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="default-working-days" class="form-label">Default Working Days <span class="text-danger">*</span></label>
                        <input type="number" id="default-working-days" class="form-control" min="1" max="31" value="22" required>
                    </div>
                    <div class="col-md-3">
                        <label for="default-holidays" class="form-label">Default Holidays</label>
                        <input type="number" id="default-holidays" class="form-control" min="0" max="31" value="0">
                    </div>
                    <div class="col-md-3">
                        <label for="default-overtime-rate" class="form-label">Default Overtime Rate</label>
                        <input type="number" id="default-overtime-rate" class="form-control" step="0.01" min="0" value="0">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" id="create-yearly-btn" class="btn btn-success">
                            <i class="ti ti-calendar-plus me-1"></i> Create All Months
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Months Container -->
        <div id="months-container">
            <div class="text-center text-muted py-5">
                <i class="ti ti-calendar ti-3x mb-3"></i>
                <h5>Select a year to view monthly settings</h5>
                <p>Choose a year from the dropdown above to load and manage monthly salary settings.</p>
            </div>
        </div>
    </div>
</div>

<!-- Month Edit/Create Modal -->
<div class="modal fade" id="monthModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="month-modal-title">Edit Month</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="month-form">
                    <input type="hidden" id="month-modal-month">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Month</label>
                            <div class="form-control-plaintext" id="month-modal-month-name"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total Days in Month</label>
                            <div class="form-control-plaintext" id="month-modal-total-days"></div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="month-modal-working-days" class="form-label">Working Days <span class="text-danger">*</span></label>
                            <input type="number" id="month-modal-working-days" class="form-control" min="1" max="31" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="month-modal-holidays" class="form-label">Official Holidays</label>
                            <input type="number" id="month-modal-holidays" class="form-control" min="0" max="31" value="0">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="month-modal-overtime-rate" class="form-label">Overtime Rate</label>
                            <input type="number" id="month-modal-overtime-rate" class="form-control" step="0.01" min="0" value="0">
                        </div>
                        
                        
                        
                        <div class="col-12">
                            <label for="month-modal-notes" class="form-label">Notes</label>
                            <textarea id="month-modal-notes" class="form-control" rows="3" placeholder="Special adjustments or notes for this month..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="save-month-btn" class="btn btn-primary">
                    <i class="ti ti-check me-1"></i> Save Setting
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
