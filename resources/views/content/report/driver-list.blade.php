@extends('layouts/layoutMaster')

@section('title', 'Driver List')

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
    $('#driver-list-form').on('submit', function(e) {
      e.preventDefault();
      filterDrivers();
    });

    // Real-time filtering on select change (with debounce for better performance)
    let filterTimeout;
    $('.form-select, .datepicker').on('change', function() {
      clearTimeout(filterTimeout);
      filterTimeout = setTimeout(function() {
        filterDrivers();
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
        $('#driver_type_id').val(params.get('driver_type_id') || '').trigger('change');
        $('#status_id').val(params.get('status_id') || '').trigger('change');
        $('#license_type_id').val(params.get('license_type_id') || '').trigger('change');
        $('#experience_year_id').val(params.get('experience_year_id') || '').trigger('change');
        $('#min_salary').val(params.get('min_salary') || '');
        $('#max_salary').val(params.get('max_salary') || '');
        $('#date_range').val(params.get('date_range') || '').trigger('change');
        $('#from_date').val(params.get('from_date') || '');
        $('#to_date').val(params.get('to_date') || '');
        
        // Load the page
        filterDrivers();
      }
    });

    // Real-time filtering on input change (with debounce)
    let searchTimeout;
    $('#search').on('input', function() {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(function() {
        filterDrivers();
      }, 500);
    });

    // Reset filters
    $('#reset-filters').on('click', function() {
      $('#driver-list-form')[0].reset();
      $('.form-select').val(null).trigger('change');
      $('.datepicker').val('');
      filterDrivers();
    });

    function filterDrivers() {
      var formData = $('#driver-list-form').serialize();
      
      $.ajax({
        url: '{{ route("driver-list.ajax") }}',
        type: 'POST',
        data: formData,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
          $('#driver-list-table-container').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        },
        success: function(response) {
          if (response.success) {
            $('#driver-list-table-container').html(response.html);
            
            // Update pagination if exists
            if (response.pagination) {
              $('#driver-list-table-container').find('.card-footer').remove();
              $('#driver-list-table-container').append(response.pagination);
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
            $('a[href*="driver-list/pdf"]').attr('href', '{{ route("driver-list.pdf") }}?' + queryString);
            $('a[href*="driver-list/print"]').attr('href', '{{ route("driver-list.print") }}?' + queryString);
          }
        },
        error: function(xhr) {
          console.error('Error filtering drivers:', xhr.responseText);
          $('#driver-list-table-container').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
        }
      });
    }
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Driver List</h5>
        <div>
            <a href="{{ route('driver-list.print', request()->query()) }}" class="btn btn-info" target="_blank">
                <i class="ti ti-printer me-1"></i> Print
            </a>
            <a href="{{ route('driver-list.pdf', request()->query()) }}" class="btn btn-danger" target="_blank">
                <i class="ti ti-file-pdf me-1"></i> Export to PDF
            </a>
            <!-- <a href="{{ route('driver-list.excel', request()->query()) }}" class="btn btn-success">
                <i class="ti ti-file-excel me-1"></i> Export to Excel
            </a> -->
        </div>
    </div>
    <div class="card-body">
        {{-- Summary Cards --}}
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-label-primary">
                    <div class="card-body">
                        <h6 class="card-title text-primary">Total Drivers</h6>
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
        <form id="driver-list-form" action="{{ route('driver-list') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Name, ID, Mobile, License..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="driver_type_id" class="form-label">Driver Type</label>
                    <select id="driver_type_id" name="driver_type_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($driverTypes as $type)
                        <option value="{{ $type->id }}" {{ request('driver_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->driver_type_name }}
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
                    <label for="license_type_id" class="form-label">License Type</label>
                    <select id="license_type_id" name="license_type_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($licenseTypes as $type)
                        <option value="{{ $type->id }}" {{ request('license_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->items_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                     <label for="experience_year_id" class="form-label">Experience</label>
                     <select id="experience_year_id" name="experience_year_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($experienceOptions as $exp)
                        <option value="{{ $exp->id }}" {{ request('experience_year_id') == $exp->id ? 'selected' : '' }}>
                            {{ $exp->items_name }}
                        </option>
                        @endforeach
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
                    <label for="date_range" class="form-label">Date Range (Joining)</label>
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
    <div id="driver-list-table-container">
        @include('content.report.driver-list-table', ['drivers' => $drivers, 'totalCount' => $totalCount, 'totalSalary' => $totalSalary, 'avgSalary' => $avgSalary])
    </div>
</div>
@endsection
