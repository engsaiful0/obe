@extends('layouts/layoutMaster')

@section('title', 'Expense Report')

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
  $(function() {
    // Initialize datepickers
    $('.datepicker').flatpickr();

    // Initialize Select2 for all select elements
    $('.form-select').select2({
      placeholder: "Select an option",
      allowClear: true
    });

    // AJAX form submission
    $('#expense-report-form').on('submit', function(e) {
      e.preventDefault();
      filterExpenses();
    });

    // Real-time filtering on select change
    $('.form-select, .datepicker').on('change', function() {
      filterExpenses();
    });

    // Reset filters
    $('#reset-filters').on('click', function() {
      $('#expense-report-form')[0].reset();
      $('.form-select').val(null).trigger('change');
      $('.datepicker').val('');
      filterExpenses();
    });

    function filterExpenses() {
      var formData = $('#expense-report-form').serialize();

      $.ajax({
        url: '{{ route("expense-report.ajax") }}',
        type: 'POST',
        data: formData,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
          $('#expense-table-container').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        },
        success: function(response) {
          $('#expense-table-container').html(response.html);

          // Update export links with current filters
          var queryString = new URLSearchParams(formData).toString();
          $('.expense-export-print').attr('href', '{{ route("expense-report.print-list") }}?' + queryString);
          $('.expense-export-pdf').attr('href', '{{ route("expense-report.pdf") }}?' + queryString);
          $('.expense-export-excel').attr('href', '{{ route("expense-report.excel") }}?' + queryString);
        },
        error: function(xhr) {
          console.error('Error filtering expenses:', xhr.responseText);
          $('#expense-table-container').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
        }
      });
    }
  });
</script>
@endsection

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">Expense Report</h5>
    <div class="d-flex gap-2">
      <a href="{{ route('expense-report.print-list', request()->query()) }}"
        target="_blank"
        class="btn btn-outline-primary expense-export-print">
        <i class="ti ti-printer"></i> Print
      </a>
      <a href="{{ route('expense-report.pdf', request()->query()) }}" class="btn btn-danger expense-export-pdf" target="_blank">
        <i class="ti ti-file-pdf"></i> Export to PDF
      </a>
      <a href="{{ route('expense-report.excel', request()->query()) }}" class="btn btn-success expense-export-excel">
        <i class="ti ti-file-excel"></i> Export to Excel
      </a>
    </div>
  </div>
  <div class="card-body">
    {{-- Search Form --}}
    <form id="expense-report-form" action="{{ route('expense-report') }}" method="GET">
      <div class="row">
        <div class="col-md-2">
          <label for="expense_head_id" class="form-label">Expense Head</label>
          <select id="expense_head_id" name="expense_head_id" class="form-select">
            <option value="">All</option>
            @foreach ($expenseHeads as $head)
            <option value="{{ $head->id }}" {{ request('expense_head_id') == $head->id ? 'selected' : '' }}>
              {{ $head->name }}
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
          <label class="form-label" for="supplier_id">Supplier</label>
          <select class="form-select" id="supplier_id" name="supplier_id">
            <option value="">All Suppliers</option>
            @foreach($suppliers as $supplier)
            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->supplier_name }}</option>
            @endforeach
          </select>
        </div>

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
        <div class="col-md-1">
          <label for="from_date" class="form-label">From Date</label>
          <input type="text" id="from_date" name="from_date" class="form-control datepicker"
            value="{{ request('from_date') }}">
        </div>
        <div class="col-md-1">
          <label for="to_date" class="form-label">To Date</label>
          <input type="text" id="to_date" name="to_date" class="form-control datepicker"
            value="{{ request('to_date') }}">
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-md-12">
          <button type="submit" class="btn btn-primary">Search</button>
          <button type="button" id="reset-filters" class="btn btn-secondary">Reset</button>
        </div>
      </div>
    </form>
  </div>
  <div id="expense-table-container">
    @include('content.report.expense-report-table')
  </div>
</div>
@endsection