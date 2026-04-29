@extends('layouts/layoutMaster')

@section('title', 'Monthly Salary Settings')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}"></script>
<script src="{{asset('assets/js/salary-configuration-ajax.js')}}"></script>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: "Select an option",
        allowClear: true
    });

    // Filter form submission
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        var url = '{{ route("monthly-salary-settings.index") }}?' + formData;
        window.location.href = url;
    });

    // Reset filters
    $('#reset-filters').on('click', function() {
        $('#filter-form')[0].reset();
        $('.select2').val(null).trigger('change');
        window.location.href = '{{ route("monthly-salary-settings.index") }}';
    });

    // Delete confirmation
    $('.delete-setting').on('click', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        
        if (confirm('Are you sure you want to delete this setting? This action cannot be undone.')) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert('Error deleting setting. Please try again.');
                }
            });
        }
    });
});
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Monthly Salary Settings</h5>
        <div>
            <button id="create-setting-btn" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add Setting
            </button>
            <a href="{{ route('monthly-salary-settings.yearly.create') }}" class="btn btn-success">
                <i class="ti ti-calendar-plus me-1"></i> Create Yearly Settings
            </a>
            <a href="{{ route('monthly-salary-settings.yearly.management') }}" class="btn btn-info">
                <i class="ti ti-settings me-1"></i> Manage Yearly Settings
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filter Form -->
        <form id="filter-form" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="year" class="form-label">Year</label>
                    <select id="year" name="year" class="form-select select2">
                        <option value="">All Years</option>
                        @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="month" class="form-label">Month</label>
                    <select id="month" name="month" class="form-select select2">
                        <option value="">All Months</option>
                        @for($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(null, $i, 1)->format('F') }}
                        </option>
                        @endfor
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <button type="button" id="reset-filters" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </form>

        <!-- Settings Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Month</th>
                        <th>Working Days</th>
                        <th>Holidays</th>
                        <th>Overtime Rate</th>
                        
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($settings as $setting)
                    <tr>
                        <td>{{ $setting->year }}</td>
                        <td>{{ $setting->month_name }}</td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $setting->total_working_days }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-warning">{{ $setting->official_holidays }}</span>
                        </td>
                        <td class="text-end">{{ number_format($setting->default_overtime_rate, 2) }}</td>
                        
                        <td>
                            @if($setting->notes)
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($setting->notes, 50) }}</small>
                            @else
                                <small class="text-muted">No notes</small>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('monthly-salary-settings.show', $setting) }}">
                                        <i class="ti ti-eye me-1"></i> View
                                    </a>
                                    <a class="dropdown-item edit-setting" href="{{ route('monthly-salary-settings.edit', $setting) }}">
                                        <i class="ti ti-pencil me-1"></i> Edit
                                    </a>
                                    
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger delete-setting" href="{{ route('monthly-salary-settings.destroy', $setting) }}">
                                        <i class="ti ti-trash me-1"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No salary settings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $settings->links() }}
        </div>
    </div>
</div>

<!-- Salary Calculator Modal -->
<div class="modal fade" id="salaryCalculatorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Salary Calculator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="salary-calculator-form">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="calc_year" class="form-label">Year</label>
                            <select id="calc_year" name="year" class="form-select select2">
                                @foreach($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="calc_month" class="form-label">Month</label>
                            <select id="calc_month" name="month" class="form-select select2">
                                @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">{{ \Carbon\Carbon::createFromDate(null, $i, 1)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="basic_salary" class="form-label">Basic Salary</label>
                            <input type="number" id="basic_salary" name="basic_salary" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="actual_present_days" class="form-label">Actual Present Days</label>
                            <input type="number" id="actual_present_days" name="actual_present_days" class="form-control" step="0.01" min="0">
                        </div>
                        <div class="col-md-6">
                            <label for="overtime_hours" class="form-label">Overtime Hours</label>
                            <input type="number" id="overtime_hours" name="overtime_hours" class="form-control" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-md-6">
                            <label for="deductions" class="form-label">Deductions</label>
                            <input type="number" id="deductions" name="deductions" class="form-control" step="0.01" min="0" value="0">
                        </div>
                    </div>
                </form>
                
                <div id="calculation-result" class="mt-4" style="display: none;">
                    <h6>Calculation Result:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tbody id="result-table">
                                <!-- Results will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="calculate-salary" class="btn btn-primary">Calculate</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Salary Calculator
    $('#calculate-salary').on('click', function() {
        var formData = $('#salary-calculator-form').serialize();
        
        $.ajax({
            url: '{{ route("monthly-salary-settings.calculate") }}',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                var calc = response.calculation;
                var html = `
                    <tr><td><strong>Basic Salary</strong></td><td class="text-end">${calc.basic_salary.toFixed(2)}</td></tr>
                    <tr><td><strong>Daily Rate</strong></td><td class="text-end">${calc.daily_rate.toFixed(2)}</td></tr>
                    <tr><td><strong>Actual Present Days</strong></td><td class="text-end">${calc.actual_present_days}</td></tr>
                    <tr><td><strong>Base Salary</strong></td><td class="text-end">${calc.base_salary.toFixed(2)}</td></tr>
                    <tr><td><strong>Overtime Hours</strong></td><td class="text-end">${calc.overtime_hours}</td></tr>
                    <tr><td><strong>Overtime Amount</strong></td><td class="text-end">${calc.overtime_amount.toFixed(2)}</td></tr>
                    <tr><td><strong>Deductions</strong></td><td class="text-end">${calc.deductions.toFixed(2)}</td></tr>
                    <tr class="table-primary"><td><strong>Total Salary</strong></td><td class="text-end"><strong>${calc.total_salary.toFixed(2)}</strong></td></tr>
                `;
                $('#result-table').html(html);
                $('#calculation-result').show();
            },
            error: function(xhr) {
                alert('Error calculating salary: ' + (xhr.responseJSON?.error || 'Please try again.'));
            }
        });
    });
});
</script>
@endsection

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Salary Setting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="create-form">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                            <select id="year" name="year" class="form-select" required>
                                <option value="">Select Year</option>
                                @foreach(range(date('Y') - 5, date('Y') + 5) as $year)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="month" class="form-label">Month <span class="text-danger">*</span></label>
                            <select id="month" name="month" class="form-select" required>
                                <option value="">Select Month</option>
                                @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">{{ \Carbon\Carbon::createFromDate(null, $i, 1)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="total_working_days" class="form-label">Total Working Days <span class="text-danger">*</span></label>
                            <input type="number" id="total_working_days" name="total_working_days" class="form-control" 
                                   min="1" max="31" value="22" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="official_holidays" class="form-label">Official Holidays</label>
                            <input type="number" id="official_holidays" name="official_holidays" class="form-control" 
                                   min="0" max="31" value="0">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="default_overtime_rate" class="form-label">Default Overtime Rate</label>
                            <input type="number" id="default_overtime_rate" name="default_overtime_rate" class="form-control" 
                                   step="0.01" min="0" value="0">
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes & Special Adjustments</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3" 
                                      placeholder="Enter any special notes or adjustments for this month..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" id="create-submit-btn" form="create-form" class="btn btn-primary">
                    <i class="ti ti-check me-1"></i> Create Setting
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Salary Setting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-form">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                            <select id="year" name="year" class="form-select" required>
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="month" class="form-label">Month <span class="text-danger">*</span></label>
                            <select id="month" name="month" class="form-select" required>
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="total_working_days" class="form-label">Total Working Days <span class="text-danger">*</span></label>
                            <input type="number" id="total_working_days" name="total_working_days" class="form-control" 
                                   min="1" max="31" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="official_holidays" class="form-label">Official Holidays</label>
                            <input type="number" id="official_holidays" name="official_holidays" class="form-control" 
                                   min="0" max="31">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="default_overtime_rate" class="form-label">Default Overtime Rate</label>
                            <input type="number" id="default_overtime_rate" name="default_overtime_rate" class="form-control" 
                                   step="0.01" min="0">
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active">
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes & Special Adjustments</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3" 
                                      placeholder="Enter any special notes or adjustments for this month..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" id="edit-submit-btn" form="edit-form" class="btn btn-primary">
                    <i class="ti ti-check me-1"></i> Update Setting
                </button>
            </div>
        </div>
    </div>
</div>
