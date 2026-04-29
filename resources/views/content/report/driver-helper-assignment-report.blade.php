@extends('layouts/layoutMaster')

@section('title', 'Driver Helper Assignment Report')

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
    $('.form-select').select2({
      placeholder: "Select an option",
      allowClear: true
    });

    // Auto-submit on change
    $('#bus_id, #driver_id, #bus_helper_id, #status_id, #from_date, #to_date').on('change', function() {
      $('#filter-form').submit();
    });
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="ti ti-users-group me-2"></i>Driver Helper Assignment Report
        </h5>
        @if(count($assignments) > 0)
        <div class="no-print">
            <a href="{{ route('driver-helper-assignment-report.print', request()->query()) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                <i class="ti ti-printer me-1"></i>Print
            </a>
            <a href="{{ route('driver-helper-assignment-report.pdf', request()->query()) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                <i class="ti ti-file-pdf me-1"></i>Export PDF
            </a>
            <a href="{{ route('driver-helper-assignment-report.excel', request()->query()) }}" class="btn btn-sm btn-outline-success">
                <i class="ti ti-file-excel me-1"></i>Export Excel
            </a>
        </div>
        @endif
    </div>
    
    <div class="card-body">
        <!-- Filter Form -->
        <form id="filter-form" method="GET" action="{{ route('driver-helper-assignment-report') }}" class="mb-4 no-print">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="bus_id" class="form-label">Bus</label>
                    <select id="bus_id" name="bus_id" class="form-select">
                        <option value="">All Buses</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}" {{ request('bus_id') == $bus->id ? 'selected' : '' }}>
                                {{ $bus->bus_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="driver_id" class="form-label">Driver</label>
                    <select id="driver_id" name="driver_id" class="form-select">
                        <option value="">All Drivers</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ request('driver_id') == $driver->id ? 'selected' : '' }}>
                                {{ $driver->full_name }} ({{ $driver->driver_unique_id ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="bus_helper_id" class="form-label">Helper</label>
                    <select id="bus_helper_id" name="bus_helper_id" class="form-select">
                        <option value="">All Helpers</option>
                        @foreach($busHelpers as $helper)
                            <option value="{{ $helper->id }}" {{ request('bus_helper_id') == $helper->id ? 'selected' : '' }}>
                                {{ $helper->bus_helper_name }} ({{ $helper->bus_helper_id ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="status_id" class="form-label">Status</label>
                    <select id="status_id" name="status_id" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                {{ $status->status_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="text" 
                           name="from_date" 
                           id="from_date" 
                           class="form-control" 
                           value="{{ request('from_date') }}">
                </div>
                
                <div class="col-md-3">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="text" 
                           name="to_date" 
                           id="to_date" 
                           class="form-control" 
                           value="{{ request('to_date') }}">
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('driver-helper-assignment-report') }}" class="btn btn-secondary ms-2">
                        <i class="ti ti-refresh me-1"></i>Reset
                    </a>
                </div>
            </div>
        </form>
        
        <!-- Report Content -->
        @if(count($assignments) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 5%;">Serial</th>
                            <th style="width: 10%;">Bus Number</th>
                            <th style="width: 10%;">Bus Type</th>
                            <th style="width: 10%;">Bus Sub Type</th>
                            <th style="width: 12%;">Driver Name</th>
                            <th style="width: 10%;">Driver Mobile</th>
                            <th style="width: 12%;">Helper Name</th>
                            <th style="width: 10%;">Helper Mobile</th>
                            <th style="width: 10%;">Assignment Date</th>
                            <th style="width: 8%;">Status</th>
                            <th style="width: 13%;">Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $serial = 1;
                        @endphp
                        @foreach($assignments as $assignment)
                            <tr>
                                <td>{{ $serial++ }}</td>
                                <td><strong>{{ $assignment->bus->bus_number ?? 'N/A' }}</strong></td>
                                <td>{{ $assignment->bus->busType->bus_type_name ?? 'N/A' }}</td>
                                <td>{{ $assignment->bus->busSubType->sub_type_name ?? 'N/A' }}</td>
                                <td>{{ $assignment->driver->full_name ?? 'N/A' }}</td>
                                <td>{{ $assignment->driver->contact_number ?? 'N/A' }}</td>
                                <td>{{ $assignment->busHelper->bus_helper_name ?? 'N/A' }}</td>
                                <td>{{ $assignment->busHelper->mobile ?? 'N/A' }}</td>
                                <td>{{ $assignment->assignment_date ? $assignment->assignment_date->format('d M, Y') : 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $assignment->status->status_name == 'Active' ? 'success' : 'secondary' }}">
                                        {{ $assignment->status->status_name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $assignment->notes ?? '-' }}</td>
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
                        <p class="mb-1"><strong>Total Assignments:</strong> {{ count($assignments) }}</p>
                        <p class="mb-0"><strong>Generated on:</strong> {{ \Carbon\Carbon::now()->format('d M, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                <i class="ti ti-alert-circle me-2"></i>
                No assignments found for the selected filters.
            </div>
        @endif
    </div>
</div>
@endsection

