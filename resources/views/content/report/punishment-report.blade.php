@extends('layouts/layoutMaster')

@section('title', 'Punishment Report')

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
    $('#punishment-report-form').on('submit', function(e) {
      e.preventDefault();
      filterPunishments();
    });

    // Real-time filtering on select change
    $('.form-select, .datepicker').on('change', function() {
      filterPunishments();
    });

    // Reset filters
    $('#reset-filters').on('click', function() {
      $('#punishment-report-form')[0].reset();
      $('.form-select').val(null).trigger('change');
      $('.datepicker').val('');
      filterPunishments();
    });

    function filterPunishments() {
      var formData = $('#punishment-report-form').serialize();
      
      $.ajax({
        url: '{{ route("punishment-report.ajax") }}',
        type: 'POST',
        data: formData,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
          $('#punishment-table-container').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        },
        success: function(response) {
          $('#punishment-table-container').html(response.html);
          
          // Update export links with current filters
          var queryString = new URLSearchParams(formData).toString();
          $('a[href*="punishment-report.print-list"]').attr('href', '{{ route("punishment-report.print-list") }}?' + queryString);
          $('a[href*="punishment-report.pdf"]').attr('href', '{{ route("punishment-report.pdf") }}?' + queryString);
          $('a[href*="punishment-report.excel"]').attr('href', '{{ route("punishment-report.excel") }}?' + queryString);
        },
        error: function(xhr) {
          console.error('Error filtering punishments:', xhr.responseText);
          $('#punishment-table-container').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
        }
      });
    }
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Punishment Report</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('punishment-report.print-list', request()->query()) }}" 
               target="_blank" 
               class="btn btn-outline-primary">
                <i class="ti ti-printer"></i> Print
            </a>
            <a href="{{ route('punishment-report.pdf', request()->query()) }}" class="btn btn-danger" target="_blank">
                <i class="ti ti-file-pdf"></i> Export to PDF
            </a>
            <a href="{{ route('punishment-report.excel', request()->query()) }}" class="btn btn-success">
                <i class="ti ti-file-excel"></i> Export to Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        {{-- Search Form --}}
        <form id="punishment-report-form" action="{{ route('punishment-report') }}" method="GET">
            <div class="row">
                <div class="col-md-2">
                    <label for="punishment_type_id" class="form-label">Punishment Type</label>
                    <select id="punishment_type_id" name="punishment_type_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($punishmentTypes as $type)
                        <option value="{{ $type->id }}" {{ request('punishment_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="violation_type_id" class="form-label">Violation Type</label>
                    <select id="violation_type_id" name="violation_type_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($violationTypes as $type)
                        <option value="{{ $type->id }}" {{ request('violation_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="bus_sub_type_id" class="form-label">Bus Sub Type</label>
                    <select id="bus_sub_type_id" name="bus_sub_type_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($busSubTypes as $subType)
                        <option value="{{ $subType->id }}" {{ request('bus_sub_type_id') == $subType->id ? 'selected' : '' }}>
                            {{ $subType->sub_type_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="bus_id" class="form-label">Bus</label>
                    <select id="bus_id" name="bus_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($buses as $bus)
                        <option value="{{ $bus->id }}" {{ request('bus_id') == $bus->id ? 'selected' : '' }}>
                            {{ $bus->bus_number }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="driver_id" class="form-label">Driver</label>
                    <select id="driver_id" name="driver_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->full_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="bus_helper_id" class="form-label">Bus Helper</label>
                    <select id="bus_helper_id" name="bus_helper_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($busHelpers as $busHelper)
                        <option value="{{ $busHelper->id }}" {{ request('bus_helper_id') == $busHelper->id ? 'selected' : '' }}>
                            {{ $busHelper->bus_helper_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="per_page" class="form-label">Rows per page</label>
                    <select id="per_page" name="per_page" class="form-select">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
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
                        value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="text" id="to_date" name="to_date" class="form-control datepicker"
                        value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Search</button>
                    <button type="button" id="reset-filters" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </form>
    </div>
    <div id="punishment-table-container">
        @include('content.report.punishment-report-table')
    </div>
</div>
@endsection
