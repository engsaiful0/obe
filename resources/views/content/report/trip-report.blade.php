@extends('layouts/layoutMaster')

@section('title', 'Trip Report')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
@endsection

@section('page-script')
<script>
  $(function () {
    // Initialize datepickers
    $('.datepicker').flatpickr({
      dateFormat: 'Y-m-d',
      allowInput: true
    });

    // AJAX form submission
    $('#trip-report-form').on('submit', function(e) {
      e.preventDefault();
      filterTrips();
    });

    // Real-time filtering on date change
    $('.datepicker').on('change', function() {
      filterTrips();
    });

    // Reset filters
    $('#reset-filters').on('click', function() {
      $('#trip-report-form')[0].reset();
      $('.datepicker').val('');
      // Set default to current month
      var now = new Date();
      var firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
      var lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
      $('#from_date').val(firstDay.toISOString().split('T')[0]);
      $('#to_date').val(lastDay.toISOString().split('T')[0]);
      filterTrips();
    });

    function filterTrips() {
      var formData = $('#trip-report-form').serialize();
      
      $.ajax({
        url: '{{ route("trip-report.ajax") }}',
        type: 'POST',
        data: formData,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
          $('#trip-table-container').html('<div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        },
        success: function(response) {
          if (response.success) {
            $('#trip-table-container').html(response.html);
            // Update print and PDF links with current filters
            updateExportLinks();
          } else {
            $('#trip-table-container').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
          }
        },
        error: function(xhr) {
          console.error('Error filtering trips:', xhr.responseText);
          $('#trip-table-container').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
        }
      });
    }

    // Update print and PDF links with current form data
    function updateExportLinks() {
      var formData = $('#trip-report-form').serialize();
      var printUrl = '{{ route("trip-report.print-list") }}?' + formData;
      var pdfUrl = '{{ route("trip-report.pdf") }}?' + formData;
      $('a[href*="trip-report.print-list"]').attr('href', printUrl);
      $('a[href*="trip-report.pdf"]').attr('href', pdfUrl);
    }

    // Update links on form change
    $('#trip-report-form').on('change input', function() {
      updateExportLinks();
    });

    // Initial update
    updateExportLinks();
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="ti ti-report me-2"></i>Trip Report
        </h5>
        <div class="d-flex gap-2">
            <a href="{{ route('trip-report.print-list', request()->query()) }}" 
               target="_blank" 
               class="btn btn-outline-primary">
                <i class="ti ti-printer me-1"></i> Print
            </a>
            <a href="{{ route('trip-report.pdf', request()->query()) }}" 
               class="btn btn-outline-danger">
                <i class="ti ti-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>
    <div class="card-body">
        {{-- Filter Form --}}
        <form id="trip-report-form" action="{{ route('trip-report') }}" method="GET">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="from_date" class="form-label">From Date <span class="text-danger">*</span></label>
                    <input type="text" id="from_date" name="from_date" class="form-control datepicker"
                        value="{{ $fromDate ?? '' }}" required>
                </div>
                <div class="col-md-4">
                    <label for="to_date" class="form-label">To Date <span class="text-danger">*</span></label>
                    <input type="text" id="to_date" name="to_date" class="form-control datepicker"
                        value="{{ $toDate ?? '' }}" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="ti ti-search me-1"></i>Generate Report
                    </button>
                    <button type="button" id="reset-filters" class="btn btn-secondary">
                        <i class="ti ti-refresh me-1"></i>Reset
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div id="trip-table-container">
        @include('content.report.trip-report-table')
    </div>
</div>
@endsection

