@extends('layouts/layoutMaster')

@section('title', 'Daywise Trip Report')

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
    $('#from_date, #to_date').flatpickr({
      dateFormat: 'Y-m-d',
      maxDate: 'today'
    });

    // Initialize Select2
    $('#bus_sub_type_id, #bus_id').select2({
      placeholder: "Select an option",
      allowClear: true
    });

    // Load buses when bus sub type changes
    $('#bus_sub_type_id').on('change', function() {
      const subTypeId = $(this).val();
      const $busSelect = $('#bus_id');
      const $select2Container = $busSelect.next('.select2-container');
      
      // Clear bus selection
      $busSelect.val(null).trigger('change');
      
      if (!subTypeId) {
        $busSelect.empty().append('<option value="">Select Bus</option>');
        // Remove spinner if exists
        $select2Container.find('.spinner-border-sm').remove();
        return;
      }

      // Show spinner inside the Select2 container
      const $spinner = $('<span class="spinner-border spinner-border-sm position-absolute" style="right: 25px; top: 50%; transform: translateY(-50%); z-index: 10; color: #6c757d;" role="status"><span class="visually-hidden">Loading...</span></span>');
      $select2Container.css('position', 'relative');
      $select2Container.find('.select2-selection').append($spinner);
      
      // Disable select and show loading option
      $busSelect.prop('disabled', true).html('<option value="">Loading buses...</option>').trigger('change');

      // AJAX request to get buses by sub type
      $.ajax({
        url: '{{ route("daywise-trip-report.get-buses-by-sub-type") }}',
        type: 'GET',
        data: {
          bus_sub_type_id: subTypeId
        },
        success: function(response) {
          $busSelect.empty().append('<option value="">Select Bus</option>');
          
          if (response.success && response.buses && response.buses.length > 0) {
            $.each(response.buses, function(index, bus) {
              const busText = bus.bus_number
              $busSelect.append(
                $('<option>', {
                  value: bus.id,
                  text: busText
                })
              );
            });
          } else {
            $busSelect.append('<option value="">No buses found</option>');
          }
        },
        error: function(xhr) {
          console.error('Error loading buses:', xhr);
          $busSelect.empty().append('<option value="">Error loading buses</option>');
        },
        complete: function() {
          // Remove spinner
          $select2Container.find('.spinner-border-sm').remove();
          $busSelect.prop('disabled', false).trigger('change');
        }
      });
    });

    // Auto-submit on change
    $('#from_date, #to_date, #bus_id').on('change', function() {
      if ($('#from_date').val() && $('#to_date').val() && $('#bus_id').val()) {
        $('#daywise-trip-form').submit();
      }
    });

    // Update print and export links when form is submitted
    $('#daywise-trip-form').on('submit', function() {
      const queryString = $(this).serialize();
      $('a[href*="daywise-trip-report.print-list"]').attr('href', '{{ route("daywise-trip-report.print-list") }}?' + queryString);
      $('a[href*="daywise-trip-report.pdf"]').attr('href', '{{ route("daywise-trip-report.pdf") }}?' + queryString);
      $('a[href*="daywise-trip-report.excel"]').attr('href', '{{ route("daywise-trip-report.excel") }}?' + queryString);
    });
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="ti ti-calendar me-2"></i>Daywise Trip Report
        </h5>
        @if($busId && $trips->count() > 0)
        <div class="d-flex gap-2">
            <a href="{{ route('daywise-trip-report.print-list', request()->query()) }}" 
               target="_blank" 
               class="btn btn-outline-primary">
                <i class="ti ti-printer"></i> Print
            </a>
            <a href="{{ route('daywise-trip-report.pdf', request()->query()) }}" class="btn btn-danger" target="_blank">
                <i class="ti ti-file-pdf"></i> Export to PDF
            </a>
            <a href="{{ route('daywise-trip-report.excel', request()->query()) }}" class="btn btn-success">
                <i class="ti ti-file-excel"></i> Export to Excel
            </a>
        </div>
        @endif
    </div>
    <div class="card-body">
        {{-- Filter Form --}}
        <form id="daywise-trip-form" action="{{ route('daywise-trip-report') }}" method="GET">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="from_date" class="form-label">From Date <span class="text-danger">*</span></label>
                    <input type="text" id="from_date" name="from_date" 
                        class="form-control @error('from_date') is-invalid @enderror"
                        value="{{ $fromDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}" 
                        required>
                    @error('from_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="to_date" class="form-label">To Date <span class="text-danger">*</span></label>
                    <input type="text" id="to_date" name="to_date" 
                        class="form-control @error('to_date') is-invalid @enderror"
                        value="{{ $toDate ?? date('Y-m-d') }}" 
                        required>
                    @error('to_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="bus_sub_type_id" class="form-label">Bus Sub Type <span class="text-danger">*</span></label>
                    <select id="bus_sub_type_id" name="bus_sub_type_id" class="form-select @error('bus_sub_type_id') is-invalid @enderror" required>
                        <option value="">Select Bus Sub Type</option>
                        @foreach ($busSubTypes as $subType)
                        <option value="{{ $subType->id }}" {{ request('bus_sub_type_id') == $subType->id ? 'selected' : '' }}>
                            {{ $subType->sub_type_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('bus_sub_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="bus_id" class="form-label">Bus Number <span class="text-danger">*</span></label>
                    <select id="bus_id" name="bus_id" class="form-select @error('bus_id') is-invalid @enderror" required>
                        <option value="">Select Bus</option>
                        @foreach ($buses as $bus)
                        <option value="{{ $bus->id }}" {{ request('bus_id') == $bus->id ? 'selected' : '' }}>
                            {{ $bus->bus_number }} - {{ $bus->model_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('bus_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i> Generate Report
                    </button>
                </div>
            </div>
        </form>

        @if($busId && $trips->count() > 0)
        @php
            $bus = $buses->firstWhere('id', $busId) ?? $trips->first()->bus;
            $totalDistance = 0;
            $totalPassengers = 0;
            foreach ($trips as $trip) {
                if ($trip->trip_type === 'in') {
                    $totalDistance += $trip->startStoppage->distance ?? 0;
                } else {
                    $totalDistance += $trip->endStoppage->distance ?? 0;
                }
                $totalPassengers += $trip->passengers ?? 0;
            }
        @endphp

        {{-- Bus Information --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Bus Number:</strong> {{ $bus->bus_number }}
                            </div>
                            <div class="col-md-3">
                                <strong>Model:</strong> {{ $bus->model_name }}
                            </div>
                            <div class="col-md-3">
                                <strong>Bus Type:</strong> {{ $bus->busSubType->sub_type_name ?? 'N/A' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Period:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <strong>Total Trips:</strong> {{ $trips->count() }}
                            </div>
                            <div class="col-md-4">
                                <strong>Total Distance:</strong> {{ number_format($totalDistance, 2) }} KM
                            </div>
                            <div class="col-md-4">
                                <strong>Total Passengers:</strong> {{ $totalPassengers }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Trip Details Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Serial</th>
                        <th>Date</th>
                        <th>Trip Number</th>
                        <th>Trip Type</th>
                        <th>From Stoppage</th>
                        <th>To Stoppage</th>
                        <th>Distance (KM)</th>
                        <th>In Time</th>
                        <th>Out Time</th>
                        <th>Passengers</th>
                        <th>Driver</th>
                        <th>Bus Helper</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trips as $trip)
                    @php
                        // Calculate distance from stoppages
                        $distance = 0;
                        if ($trip->trip_type === 'in') {
                            $distance = $trip->startStoppage->distance ?? 0;
                        } else {
                            $distance = $trip->endStoppage->distance ?? 0;
                        }
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $trip->trip_date->format('d-m-Y') }}</td>
                        <td class="text-center">{{ $trip->trip_number ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $trip->trip_type === 'in' ? 'success' : 'warning' }}">
                                {{ strtoupper($trip->trip_type) }}
                            </span>
                        </td>
                        <td>{{ $trip->startStoppage->stoppage_name ?? 'N/A' }}</td>
                        <td>{{ $trip->endStoppage->stoppage_name ?? 'N/A' }}</td>
                        <td class="text-end">{{ number_format($distance, 2) }}</td>
                        <td class="text-center">
                            @if($trip->in_time)
                                {{ \Carbon\Carbon::parse($trip->in_time)->format('h:i A') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            @if($trip->out_time)
                                {{ \Carbon\Carbon::parse($trip->out_time)->format('h:i A') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">{{ $trip->passengers ?? 0 }}</td>
                        <td>{{ $trip->driver->full_name ?? 'N/A' }}</td>
                        <td>{{ $trip->busHelper->bus_helper_name ?? 'N/A' }}</td>
                        <td>{{ $trip->remarks ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td colspan="6" class="text-end"><strong>Total:</strong></td>
                        <td class="text-end"><strong>{{ number_format($totalDistance, 2) }}</strong></td>
                        <td colspan="2"></td>
                        <td class="text-center"><strong>{{ $totalPassengers }}</strong></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @elseif($busId)
        <div class="alert alert-info text-center">
            <i class="ti ti-info-circle me-2"></i>
            No trips found for the selected date range and bus.
        </div>
        @else
        <div class="alert alert-warning text-center">
            <i class="ti ti-alert-circle me-2"></i>
            Please select date range, bus sub type, and bus to view the trip report.
        </div>
        @endif
    </div>
</div>
@endsection

