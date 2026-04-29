@extends('layouts/layoutMaster')

@section('title', 'Issue Report')

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
    $('#issue-report-form').on('submit', function(e) {
      e.preventDefault();
      filterIssues();
    });

    // Real-time filtering on select change
    $('.form-select, .datepicker').on('change', function() {
      filterIssues();
    });

    // Reset filters
    $('#reset-filters').on('click', function() {
      $('#issue-report-form')[0].reset();
      $('.form-select').val(null).trigger('change');
      $('.datepicker').val('');
      filterIssues();
    });

    function filterIssues() {
      var formData = $('#issue-report-form').serialize();
      
      $.ajax({
        url: '{{ route("issue-report.ajax") }}',
        type: 'POST',
        data: formData,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
          $('#issue-table-container').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        },
        success: function(response) {
          $('#issue-table-container').html(response.html);
          
          // Update export links with current filters
          var queryString = new URLSearchParams(formData).toString();
          $('a[href*="issue-report.print-list"]').attr('href', '{{ route("issue-report.print-list") }}?' + queryString);
          $('a[href*="issue-report.pdf"]').attr('href', '{{ route("issue-report.pdf") }}?' + queryString);
          $('a[href*="issue-report.excel"]').attr('href', '{{ route("issue-report.excel") }}?' + queryString);
        },
        error: function(xhr) {
          console.error('Error filtering issues:', xhr.responseText);
          $('#issue-table-container').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
        }
      });
    }
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Issue Report</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('issue-report.print-list', request()->query()) }}" 
               target="_blank" 
               class="btn btn-outline-primary">
                <i class="ti ti-printer"></i> Print
            </a>
            <a href="{{ route('issue-report.pdf', request()->query()) }}" class="btn btn-danger" target="_blank">
                <i class="ti ti-file-pdf"></i> Export to PDF
            </a>
            <a href="{{ route('issue-report.excel', request()->query()) }}" class="btn btn-success">
                <i class="ti ti-file-excel"></i> Export to Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        {{-- Search Form --}}
        <form id="issue-report-form" action="{{ route('issue-report') }}" method="GET">
            <div class="row">
                <div class="col-md-2">
                    <label for="employee_id" class="form-label">Employee</label>
                    <select id="employee_id" name="employee_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->employee_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="item_id" class="form-label">Item</label>
                    <select id="item_id" name="item_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($items as $item)
                        <option value="{{ $item->id }}" {{ request('item_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->item_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="issue_number" class="form-label">Issue Number</label>
                    <input type="text" id="issue_number" name="issue_number" class="form-control"
                        value="{{ request('issue_number') }}" placeholder="Search by issue number">
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
            </div>
            <div class="row mt-3">
                <div class="col-md-3">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="text" id="to_date" name="to_date" class="form-control datepicker"
                        value="{{ request('to_date') }}">
                </div>
                <div class="col-md-9 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Search</button>
                    <button type="button" id="reset-filters" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </form>
    </div>
    <div id="issue-table-container">
        @include('content.report.issue-report-table')
    </div>
</div>
@endsection
