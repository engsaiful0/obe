@extends('layouts/layoutMaster')

@section('title', 'Bus Helper Report')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(function () {
    // Initialize datepickers
    $('.datepicker').flatpickr();

    // Initialize Select2 for all select elements
    $('.form-select').select2({
      placeholder: "Select an option",
      allowClear: true
    });

    // AJAX form submission
    $('#bus-helper-report-form').on('submit', function(e) {
      e.preventDefault();
      filterBusHelpers();
    });

    // Real-time filtering on select change (with debounce for better performance)
    let filterTimeout;
    $('.form-select, .datepicker').on('change', function() {
      clearTimeout(filterTimeout);
      filterTimeout = setTimeout(function() {
        filterBusHelpers();
      }, 300);
    });
    
    // Handle pagination clicks
    $(document).on('click', '.pagination a', function(e) {
      e.preventDefault();
      const url = $(this).attr('href');
      if (url) {
        // Extract query params from URL and update form
        const urlObj = new URL(url);
        const params = new URLSearchParams(urlObj.search);
        
        // Update form fields
        $('#search').val(params.get('search') || '');
        $('#gender_id').val(params.get('gender_id') || '').trigger('change');
        $('#status_id').val(params.get('status_id') || '').trigger('change');
        $('#employee_type_id').val(params.get('employee_type_id') || '').trigger('change');
        $('#assigned_bus_id').val(params.get('assigned_bus_id') || '').trigger('change');
        $('#experience_filter').val(params.get('experience_filter') || '').trigger('change');
        $('#min_salary').val(params.get('min_salary') || '');
        $('#max_salary').val(params.get('max_salary') || '');
        $('#date_range').val(params.get('date_range') || '').trigger('change');
        $('#from_date').val(params.get('from_date') || '');
        $('#to_date').val(params.get('to_date') || '');
        
        // Load the page
        filterBusHelpers();
      }
    });

    // Real-time filtering on input change (with debounce)
    let searchTimeout;
    $('#search').on('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function() {
        filterBusHelpers();
      }, 500);
    });

    // Reset filters
    $('#reset-filters').on('click', function() {
      $('#bus-helper-report-form')[0].reset();
      $('.form-select').val(null).trigger('change');
      $('.datepicker').val('');
      filterBusHelpers();
    });

    function filterBusHelpers() {
      var formData = $('#bus-helper-report-form').serialize();
      
      $.ajax({
        url: '{{ route("bus-helper-report.ajax") }}',
        type: 'POST',
        data: formData,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
          $('#bus-helper-table-container').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        },
        success: function(response) {
          if (response.success) {
            $('#bus-helper-table-container').html(response.html);
            
            // Update pagination if exists
            if (response.pagination) {
              $('#bus-helper-table-container').find('.card-footer').remove();
              $('#bus-helper-table-container').append(response.pagination);
            }
            
            // Update summary cards
            if (response.totalCount !== undefined) {
              $('.card.bg-label-primary h3').text(response.totalCount);
            }
            if (response.totalSalary !== undefined) {
              $('.card.bg-label-success h3').text('৳' + parseFloat(response.totalSalary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            }
            if (response.avgSalary !== undefined) {
              $('.card.bg-label-info h3').text('৳' + parseFloat(response.avgSalary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            }
            
            // Update export links with current filters
            var queryString = new URLSearchParams(formData).toString();
            $('a[href*="bus-helper-report.pdf"]').attr('href', '{{ route("bus-helper-report.pdf") }}?' + queryString);
            $('a[href*="bus-helper-report.excel"]').attr('href', '{{ route("bus-helper-report.excel") }}?' + queryString);
            $('a[href*="bus-helper-report.print"]').attr('href', '{{ route("bus-helper-report.print") }}?' + queryString);
          }
        },
        error: function(xhr) {
          console.error('Error filtering bus helpers:', xhr.responseText);
          $('#bus-helper-table-container').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
        }
      });
    }
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Bus Helper Report</h5>
        <div>
            <a href="{{ route('bus-helper-report.print', request()->query()) }}" class="btn btn-info" target="_blank">
                <i class="ti ti-printer me-1"></i> Print
            </a>
            <a href="{{ route('bus-helper-report.pdf', request()->query()) }}" class="btn btn-danger" target="_blank">
                <i class="ti ti-file-pdf me-1"></i> Export to PDF
            </a>
            <a href="{{ route('bus-helper-report.excel', request()->query()) }}" class="btn btn-success">
                <i class="ti ti-file-excel me-1"></i> Export to Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-label-primary">
                    <div class="card-body">
                        <h6 class="card-title text-primary">Total Bus Helpers</h6>
                        <h3 class="mb-0">{{ $totalCount }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-label-success">
                    <div class="card-body">
                        <h6 class="card-title text-success">Total Salary</h6>
                        <h3 class="mb-0">৳{{ number_format($totalSalary, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-label-info">
                    <div class="card-body">
                        <h6 class="card-title text-info">Average Salary</h6>
                        <h3 class="mb-0">৳{{ number_format($avgSalary, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search Form --}}
        <form id="bus-helper-report-form" action="{{ route('bus-helper-report') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Name, ID, Mobile, NID..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="gender_id" class="form-label">Gender</label>
                    <select id="gender_id" name="gender_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($genders as $gender)
                        <option value="{{ $gender->id }}" {{ request('gender_id') == $gender->id ? 'selected' : '' }}>
                            {{ $gender->gender_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="status_id" class="form-label">Status</label>
                    <select id="status_id" name="status_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($statuses as $status)
                        <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                            {{ $status->status_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="employee_type_id" class="form-label">Employee Type</label>
                    <select id="employee_type_id" name="employee_type_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($employeeTypes as $type)
                        <option value="{{ $type->id }}" {{ request('employee_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->employee_type_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="assigned_bus_id" class="form-label">Assigned Bus</label>
                    <select id="assigned_bus_id" name="assigned_bus_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($buses as $bus)
                        <option value="{{ $bus->id }}" {{ request('assigned_bus_id') == $bus->id ? 'selected' : '' }}>
                            {{ $bus->bus_number }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="experience_filter" class="form-label">Experience</label>
                    <select id="experience_filter" name="experience_filter" class="form-select">
                        <option value="">All</option>
                        <option value="beginner" {{ request('experience_filter') == 'beginner' ? 'selected' : '' }}>Beginner (≤1 year)</option>
                        <option value="intermediate" {{ request('experience_filter') == 'intermediate' ? 'selected' : '' }}>Intermediate (2-3 years)</option>
                        <option value="experienced" {{ request('experience_filter') == 'experienced' ? 'selected' : '' }}>Experienced (4-5 years)</option>
                        <option value="senior" {{ request('experience_filter') == 'senior' ? 'selected' : '' }}>Senior (>5 years)</option>
                    </select>
                </div>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-md-2">
                    <label for="min_salary" class="form-label">Min Salary</label>
                    <input type="number" id="min_salary" name="min_salary" class="form-control" 
                           step="0.01" min="0" value="{{ request('min_salary') }}" placeholder="0.00">
                </div>
                <div class="col-md-2">
                    <label for="max_salary" class="form-label">Max Salary</label>
                    <input type="number" id="max_salary" name="max_salary" class="form-control" 
                           step="0.01" min="0" value="{{ request('max_salary') }}" placeholder="0.00">
                </div>
                <div class="col-md-2">
                    <label for="date_range" class="form-label">Date Range</label>
                    <select id="date_range" name="date_range" class="form-select">
                        <option value="">Custom</option>
                        <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="last_six_months" {{ request('date_range') == 'last_six_months' ? 'selected' : '' }}>Last 6 Months</option>
                        <option value="this_year" {{ request('date_range') == 'this_year' ? 'selected' : '' }}>This Year</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="text" id="from_date" name="from_date" class="form-control datepicker"
                        value="{{ request('from_date') }}" placeholder="Select date">
                </div>
                <div class="col-md-2">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="text" id="to_date" name="to_date" class="form-control datepicker"
                        value="{{ request('to_date') }}" placeholder="Select date">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Search</button>
                    <button type="button" id="reset-filters" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </form>
    </div>
    <div id="bus-helper-table-container">
        @include('content.report.bus-helper-report-table', ['busHelpers' => $busHelpers, 'totalCount' => $totalCount, 'totalSalary' => $totalSalary, 'avgSalary' => $avgSalary])
    </div>
</div>
@endsection

