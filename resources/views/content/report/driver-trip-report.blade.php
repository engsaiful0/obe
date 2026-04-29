@extends('layouts/layoutMaster')

@section('title', 'Driver Trip Report')

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
    $('#driver_id').select2({
      placeholder: "Select a Driver",
      allowClear: true
    });

    // Auto-submit on change
    $('#driver_id, #from_date, #to_date').on('change', function() {
      if ($('#driver_id').val() && $('#from_date').val() && $('#to_date').val()) {
        $('#filter-form').submit();
      }
    });
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="ti ti-user me-2"></i>Driver Trip Report
        </h5>
        @if(count($reportData) > 0)
        <div class="no-print">
            <a href="{{ route('driver-trip-report.print', request()->query()) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                <i class="ti ti-printer me-1"></i>Print
            </a>
            <a href="{{ route('driver-trip-report.pdf', request()->query()) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                <i class="ti ti-file-pdf me-1"></i>Export PDF
            </a>
            <a href="{{ route('driver-trip-report.excel', request()->query()) }}" class="btn btn-sm btn-outline-success">
                <i class="ti ti-file-excel me-1"></i>Export Excel
            </a>
        </div>
        @endif
    </div>
    
    <div class="card-body">
        <!-- Filter Form -->
        <form id="filter-form" method="GET" action="{{ route('driver-trip-report') }}" class="mb-4 no-print">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="driver_id" class="form-label">Driver <span class="text-danger">*</span></label>
                    <select id="driver_id" name="driver_id" class="form-select" required>
                        <option value="">Select Driver</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}" {{ $driverId == $d->id ? 'selected' : '' }}>
                                {{ $d->full_name }} ({{ $d->driver_unique_id ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="from_date" class="form-label">From Date <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="from_date" 
                           id="from_date" 
                           class="form-control" 
                           value="{{ $fromDate }}" 
                           required>
                </div>
                
                <div class="col-md-3">
                    <label for="to_date" class="form-label">To Date <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="to_date" 
                           id="to_date" 
                           class="form-control" 
                           value="{{ $toDate }}" 
                           required>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i>Generate
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Report Content -->
        @if(count($reportData) > 0 && $driver)
            <div class="mb-3">
                <h6><strong>Driver:</strong> {{ $driver->full_name }} ({{ $driver->driver_unique_id ?? 'N/A' }})</h6>
                <p class="mb-0"><strong>Date Range:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}</p>
            </div>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 8%;">Date</th>
                            <th style="width: 8%;">Day</th>
                            <th style="width: 6%;">Total Trip</th>
                            <th style="width: 10%;">Bus Number</th>
                            <th style="width: 12%;">Helper</th>
                            @for($i = 1; $i <= $maxTripsPerDay; $i++)
                                <th style="width: {{ 66 / $maxTripsPerDay }}%;">{{ $i }}{{ $i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')) }} Trip</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportData as $dayData)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($dayData['date'])->format('d M, Y') }}</td>
                                <td>{{ $dayData['day'] }}</td>
                                <td class="text-center"><strong>{{ $dayData['total_trips'] }}</strong></td>
                                <td><strong>{{ $dayData['bus_number'] }}</strong></td>
                                <td>{{ $dayData['helper'] }}</td>
                                @for($i = 0; $i < $maxTripsPerDay; $i++)
                                    <td>
                                        @if(isset($dayData['trips'][$i]))
                                            @php
                                                $trip = $dayData['trips'][$i];
                                            @endphp
                                            <small>
                                                <strong>{{ $trip['route'] }}</strong><br>
                                                <span class="badge bg-label-{{ $trip['trip_type'] == 'IN' ? 'info' : 'warning' }}">
                                                    {{ $trip['trip_type'] }}
                                                </span>
                                                <span class="ms-1">{{ $trip['time'] }}</span>
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Report Summary -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Report Summary</h6>
                        <hr>
                        <p class="mb-1"><strong>Driver:</strong> {{ $driver->full_name }}</p>
                        <p class="mb-1"><strong>Date Range:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}</p>
                        <p class="mb-1"><strong>Total Days:</strong> {{ count($reportData) }}</p>
                        <p class="mb-0"><strong>Total Trips:</strong> {{ collect($reportData)->sum('total_trips') }}</p>
                    </div>
                </div>
            </div>
        @elseif($driverId && $fromDate && $toDate)
            <div class="alert alert-warning">
                <i class="ti ti-alert-circle me-2"></i>
                No trips found for the selected driver and date range.
            </div>
        @else
            <div class="alert alert-info">
                <i class="ti ti-info-circle me-2"></i>
                Please select a driver and date range to generate the report.
            </div>
        @endif
    </div>
</div>
@endsection


