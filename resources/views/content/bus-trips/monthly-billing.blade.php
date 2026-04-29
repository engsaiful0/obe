@extends('layouts/layoutMaster')

@section('title', 'Monthly Vehicle Billing')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Monthly Vehicle Billing Report</h5>
        <a href="{{ route('vehicle-attendances.index') }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-1"></i>Back to Attendance
        </a>
    </div>
    <div class="card-body">
        <!-- Month/Year Filter -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="month" class="form-label">Month</label>
                    <select name="month" id="month" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year" class="form-label">Year</label>
                    <select name="year" id="year" class="form-select">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-filter me-1"></i>Generate Report
                    </button>
                </div>
            </div>
        </form>

        <h6 class="mb-3">Billing Period: <strong>{{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</strong></h6>

        @if(count($billingData) > 0)
            @php
                $grandTotal = 0;
            @endphp

            @foreach($billingData as $data)
                @php
                    $grandTotal += $data['total_bill'];
                @endphp

                <div class="card mb-3 border">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="ti ti-bus me-2"></i>
                                {{ $data['vehicle']->registration_number }} - {{ $data['vehicle']->model_name }}
                            </h6>
                            <span class="badge {{ $data['sub_type'] === 'BRTC Hired Bus' ? 'bg-info' : 'bg-success' }}">
                                {{ $data['sub_type'] }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row mb-3">
                                    @if($data['sub_type'] === 'BRTC Hired Bus')
                                        <div class="col-md-4">
                                            <small class="text-muted">Total Trips</small>
                                            <h6>{{ $data['total_trips'] }}</h6>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Total Distance</small>
                                            <h6>{{ number_format($data['total_distance'], 2) }} KM</h6>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Price per KM</small>
                                            <h6>৳{{ number_format($data['price_per_km'], 2) }}</h6>
                                        </div>
                                    @else
                                        <div class="col-md-4">
                                            <small class="text-muted">Total Trips</small>
                                            <h6>{{ $data['total_trips'] }}</h6>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Completed Days</small>
                                            <h6>{{ $data['completed_days'] }}</h6>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Daily Rate</small>
                                            <h6>৳{{ number_format($data['daily_rate'], 2) }}</h6>
                                        </div>
                                    @endif
                                </div>

                                <!-- Trip Details -->
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Trip</th>
                                                <th>Driver</th>
                                                <th>Assistant</th>
                                                <th>Route</th>
                                                <th>Time</th>
                                                @if($data['sub_type'] === 'BRTC Hired Bus')
                                                    <th>Distance (KM)</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data['attendances'] as $att)
                                                <tr>
                                                    <td>{{ $att->attendance_date->format('M d') }}</td>
                                                    <td>
                                                        <span class="badge {{ $att->trip_type === 'in' ? 'bg-primary' : 'bg-warning' }}">
                                                            {{ strtoupper($att->trip_type) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($att->driver)
                                                            <small class="text-primary">
                                                                <i class="ti ti-user me-1"></i>{{ $att->driver->full_name }}
                                                            </small>
                                                        @else
                                                            <small class="text-muted">Not assigned</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($att->assistant)
                                                            <small class="text-warning">
                                                                <i class="ti ti-user-plus me-1"></i>{{ $att->assistant->assistant_name }}
                                                            </small>
                                                        @else
                                                            <small class="text-muted">Not assigned</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <small>{{ $att->startStoppage->stoppage_name ?? 'N/A' }} → {{ $att->endStoppage->stoppage_name ?? 'N/A' }}</small>
                                                    </td>
                                                    <td>
                                                        @if($att->trip_type === 'in')
                                                            {{ $att->in_time ? $att->in_time->format('h:i A') : '-' }}
                                                        @else
                                                            {{ $att->out_time ? $att->out_time->format('h:i A') : '-' }}
                                                        @endif
                                                    </td>
                                                    @if($data['sub_type'] === 'BRTC Hired Bus')
                                                        <td>{{ $att->total_distance ? number_format($att->total_distance, 2) : '-' }}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h6 class="text-white mb-2">Total Bill</h6>
                                        <h3 class="text-white mb-0">৳{{ number_format($data['total_bill'], 2) }}</h3>
                                        @if($data['sub_type'] === 'BRTC Hired Bus')
                                            <small class="text-white-50">
                                                {{ number_format($data['total_distance'], 2) }} KM × ৳{{ number_format($data['price_per_km'], 2) }}
                                            </small>
                                        @else
                                            <small class="text-white-50">
                                                {{ $data['completed_days'] }} Days × ৳{{ number_format($data['daily_rate'], 2) }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Grand Total -->
            <div class="card border-primary">
                <div class="card-body bg-light">
                    <div class="row">
                        <div class="col-md-8 text-end">
                            <h5 class="mb-0">Grand Total (All Vehicles):</h5>
                        </div>
                        <div class="col-md-4">
                            <h4 class="text-primary mb-0">৳{{ number_format($grandTotal, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="mt-3 d-flex justify-content-end gap-2">
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="ti ti-printer me-1"></i>Print Report
                </button>
            </div>
        @else
            <div class="text-center py-5">
                <i class="ti ti-report-money" style="font-size: 48px; color: #ccc;"></i>
                <h5 class="mt-3">No Attendance Records Found</h5>
                <p class="text-muted">No billing data available for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</p>
                <a href="{{ route('vehicle-attendances.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i>Record Attendance
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-style')
<style>
    @media print {
        .btn, .card-header a, .navbar, .sidebar, .footer {
            display: none !important;
        }
        .card {
            border: 1px solid #000 !important;
        }
    }
</style>
@endsection

