@extends('layouts/layoutMaster')

@section('title', 'Bus List Report')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/select2/select2.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
@endsection


@section('page-script')
<script>
  $(function() {
    // Initialize Select2 for all select elements
    $('.form-select').select2({
      placeholder: "Select an option",
      allowClear: true
    });

    // AJAX form submission
    $('#bus-list-form').on('submit', function(e) {
      e.preventDefault();
      filterBuses();
    });

    // Real-time filtering on select change
    $('.form-select').on('change', function() {
      filterBuses();
    });

    // Reset filters
    $('#reset-filters').on('click', function() {
      $('#bus-list-form')[0].reset();
      $('.form-select').val(null).trigger('change');
      filterBuses();
    });

    function filterBuses() {
      var formData = $('#bus-list-form').serialize();

      $.ajax({
        url: '{{ route("bus-list.ajax") }}',
        type: 'POST',
        data: formData,
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
          $('#bus-table-container').html('<div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        },
        success: function(response) {
          if (response.success) {
            $('#bus-table-container').html(response.html);
            // Update PDF export link with current filters
            updatePdfExportLink();
            // Update print link with current filters
            updatePrintLink();
          } else {
            $('#bus-table-container').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
          }
        },
        error: function(xhr) {
          console.error('Error filtering buses:', xhr.responseText);
          $('#bus-table-container').html('<div class="alert alert-danger">Error loading data. Please try again.</div>');
        }
      });
    }

    // Update PDF export link with current form data
    function updatePdfExportLink() {
      var formData = $('#bus-list-form').serialize();
      var pdfUrl = '{{ route("bus-list.pdf") }}?' + formData;
      $('#pdf-export-btn').attr('href', pdfUrl);
    }

    // Update print link with current form data
    function updatePrintLink() {
      var formData = $('#bus-list-form').serialize();
      var printUrl = '{{ route("bus-list.print-list") }}?' + formData;
      $('a[href*="bus-list.print-list"]').attr('href', printUrl);
    }

    // Update PDF and print links on form change
    $('#bus-list-form').on('change input', function() {
      updatePdfExportLink();
      updatePrintLink();
    });

    // Initial update
    updatePdfExportLink();
    updatePrintLink();
  });
</script>
@endsection

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="card-title mb-0">
      <i class="ti ti-bus me-2"></i>Bus List Report
    </h5>
    <div class="d-flex gap-2">
      <a href="{{ route('bus-list.print-list', request()->query()) }}" 
         target="_blank" 
         class="btn btn-outline-primary">
        <i class="ti ti-printer me-1"></i> Print
      </a>
      <a href="{{ route('bus-list.pdf', request()->query()) }}" class="btn btn-outline-danger" id="pdf-export-btn" target="_blank">
        <i class="ti ti-file-pdf me-1"></i>Export PDF
      </a>
    </div>
  </div>
  <div class="card-body">
    {{-- Filter Form --}}
    <form id="bus-list-form" action="{{ route('bus-list') }}" method="GET">
      <div class="row g-3">
        <div class="col-md-4">
          <label for="bus_sub_type_id" class="form-label">Bus Sub Type</label>
          <select id="bus_sub_type_id" name="bus_sub_type_id" class="form-select">
            <option value="all" {{ request('bus_sub_type_id') == 'all' || !request('bus_sub_type_id') ? 'selected' : '' }}>All</option>
            @foreach ($busSubTypes as $subType)
            <option value="{{ $subType->id }}" {{ request('bus_sub_type_id') == $subType->id ? 'selected' : '' }}>
              {{ $subType->sub_type_name }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label for="status_id" class="form-label">Status</label>
          <select id="status_id" name="status_id" class="form-select">
            <option value="all" {{ request('status_id') == 'all' || !request('status_id') ? 'selected' : '' }}>All</option>
            @foreach($busStatus as $status)
            <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
              {{ ucfirst($status->status_name) }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-4">
          <label for="search" class="form-label">Search</label>
          <input type="text" id="search" name="search" class="form-control"
            placeholder="Search by bus number, registration, model..."
            value="{{ request('search') }}">
        </div>
      </div>
      <div class="row mt-3">
        <div class="col-md-12">
          <button type="submit" class="btn btn-primary">
            <i class="ti ti-search me-1"></i>Search
          </button>
          <button type="button" id="reset-filters" class="btn btn-secondary">
            <i class="ti ti-refresh me-1"></i>Reset
          </button>
        </div>
      </div>
    </form>
  </div>
  <div id="bus-table-container">
    @include('content.report.bus-list-table')
  </div>
</div>
@endsection