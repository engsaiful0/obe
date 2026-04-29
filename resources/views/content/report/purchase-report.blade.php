@extends('layouts/layoutMaster')

@section('title', 'Purchase Report')

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
    $('#purchase-report-form').on('submit', function(e) {
      e.preventDefault();
      filterPurchases();
    });

    // Real-time filtering on select change
    $('.form-select, .datepicker').on('change', function() {
      filterPurchases();
    });

    // Reset filters
    $('#reset-filters').on('click', function() {
      $('#purchase-report-form')[0].reset();
      $('.form-select').val(null).trigger('change');
      $('.datepicker').val('');
      filterPurchases();
    });

    function filterPurchases() {
      var formData = $('#purchase-report-form').serialize();
      
      $.ajax({
        url: '{{ route("purchase-report.ajax") }}',
        type: 'POST',
        data: formData,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
          $('#purchase-table-container').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        },
        success: function(response) {
          $('#purchase-table-container').html(response.html);
          
          // Update export links with current filters
          var queryString = new URLSearchParams(formData).toString();
          $('a[href*="purchase-report.print-list"]').attr('href', '{{ route("purchase-report.print-list") }}?' + queryString);
          $('a[href*="purchase-report.pdf"]').attr('href', '{{ route("purchase-report.pdf") }}?' + queryString);
          $('a[href*="purchase-report.excel"]').attr('href', '{{ route("purchase-report.excel") }}?' + queryString);
        },
        error: function(xhr) {
          console.error('Error filtering purchases:', xhr.responseText);
          $('#purchase-table-container').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
        }
      });
    }
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Purchase Report</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('purchase-report.print-list', request()->query()) }}" 
               target="_blank" 
               class="btn btn-outline-primary">
                <i class="ti ti-printer"></i> Print
            </a>
            <a href="{{ route('purchase-report.pdf', request()->query()) }}" class="btn btn-danger" target="_blank">
                <i class="ti ti-file-pdf"></i> Export to PDF
            </a>
            <a href="{{ route('purchase-report.excel', request()->query()) }}" class="btn btn-success">
                <i class="ti ti-file-excel"></i> Export to Excel
            </a>
        </div>
    </div>
    <div class="card-body">
        {{-- Search Form --}}
        <form id="purchase-report-form" action="{{ route('purchase-report') }}" method="GET">
            <div class="row">
                <div class="col-md-2">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select id="supplier_id" name="supplier_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->supplier_name }}
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
                    <label for="payment_method_id" class="form-label">Payment Method</label>
                    <select id="payment_method_id" name="payment_method_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($paymentMethods as $method)
                        <option value="{{ $method->id }}" {{ request('payment_method_id') == $method->id ? 'selected' : '' }}>
                            {{ $method->payment_method_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="purchase_number" class="form-label">Purchase Number</label>
                    <input type="text" id="purchase_number" name="purchase_number" class="form-control"
                        value="{{ request('purchase_number') }}" placeholder="Search by purchase number">
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
            </div>
            <div class="row mt-3">
                <div class="col-md-3">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="text" id="from_date" name="from_date" class="form-control datepicker"
                        value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="text" id="to_date" name="to_date" class="form-control datepicker"
                        value="{{ request('to_date') }}">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Search</button>
                    <button type="button" id="reset-filters" class="btn btn-secondary">Reset</button>
                </div>
            </div>
        </form>
    </div>
    <div id="purchase-table-container">
        @include('content.report.purchase-report-table')
    </div>
</div>
@endsection
