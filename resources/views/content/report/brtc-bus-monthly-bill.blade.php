@extends('layouts/layoutMaster')

@section('title', 'BRTC Bus Monthly Bill')

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
    $('.datepicker').flatpickr({
      dateFormat: 'Y-m',
      defaultDate: '{{ $currentYear }}-{{ str_pad($currentMonth, 2, '0', STR_PAD_LEFT) }}'
    });

    // Initialize Select2
    $('.form-select').select2({
      placeholder: "Select an option",
      allowClear: true
    });

    // Form submission
    $('#brtc-bill-form').on('submit', function(e) {
      e.preventDefault();
      window.location.href = '{{ route("brtc-bus-monthly-bill") }}?' + $(this).serialize();
    });

    // Auto-submit on change
    $('#bus_id, #year, #month').on('change', function() {
      $('#brtc-bill-form').submit();
    });
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">BRTC Bus Monthly Bill</h5>
        @if($busId && count($dailyBills) > 0)
        <div class="d-flex gap-2">
            <a href="{{ route('brtc-bus-monthly-bill.print-list', request()->query()) }}" 
               target="_blank" 
               class="btn btn-outline-primary">
                <i class="ti ti-printer"></i> Print
            </a>
            <a href="{{ route('brtc-bus-monthly-bill.pdf', request()->query()) }}" class="btn btn-danger" target="_blank">
                <i class="ti ti-file-pdf"></i> Export to PDF
            </a>
            <a href="{{ route('brtc-bus-monthly-bill.excel', request()->query()) }}" class="btn btn-success">
                <i class="ti ti-file-excel"></i> Export to Excel
            </a>
        </div>
        @endif
    </div>
    <div class="card-body">
        {{-- Filter Form --}}
        <form id="brtc-bill-form" action="{{ route('brtc-bus-monthly-bill') }}" method="GET">
            <div class="row mb-4">
                <div class="col-md-4">
                    <label for="bus_id" class="form-label">Select Bus <span class="text-danger">*</span></label>
                    <select id="bus_id" name="bus_id" class="form-select" required>
                        <option value="">Select BRTC Bus</option>
                        @foreach ($buses as $bus)
                        <option value="{{ $bus->id }}" {{ request('bus_id') == $bus->id ? 'selected' : '' }}>
                            {{ $bus->bus_number }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="year" class="form-label">Year</label>
                    <select id="year" name="year" class="form-select">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="month" class="form-label">Month</label>
                    <select id="month" name="month" class="form-select">
                        @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                        </option>
                        @endfor
                    </select>
                </div>
            </div>
        </form>

        @if($busId && count($dailyBills) > 0)
        @php
            $bus = $buses->firstWhere('id', $busId);
            $totals = [
                'total_trips' => collect($dailyBills)->sum('number_of_trips'),
                'total_distance' => collect($dailyBills)->sum('achieved_distance'),
                'total_daily_rent' => collect($dailyBills)->sum('daily_rent'),
                'total_vat' => collect($dailyBills)->sum('vat'),
                'total_reward' => collect($dailyBills)->sum('reward'),
                'total_punishment' => collect($dailyBills)->sum('punishment'),
                'total_daily_total_rent' => collect($dailyBills)->sum('daily_total_rent'),
            ];
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
                                <strong>Seating Capacity:</strong> {{ $bus->seating_capacity ?? 'N/A' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Rate per KM:</strong> ৳{{ number_format($bus->rate_per_km ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <strong>Period:</strong> {{ $fromDate->format('F d, Y') }} - {{ $toDate->format('F d, Y') }}
                            </div>
                            <div class="col-md-6">
                                <strong>Month:</strong> {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Daily Bill Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Serial</th>
                        <th>Date</th>
                        <th>Number of Trips</th>
                        <th>Achieved Distance (KM)</th>
                        <th>Daily Rent</th>
                        <th>15% VAT (Exclude)</th>
                        <th>Reward (Include)</th>
                        <th>Punishment (Exclude)</th>
                        <th>Daily Total Rent</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyBills as $bill)
                    <tr>
                        <td>{{ $bill['serial'] }}</td>
                        <td>{{ $bill['date']->format('d-m-Y') }}</td>
                        <td class="text-center">{{ $bill['number_of_trips'] }}</td>
                        <td class="text-end">{{ number_format($bill['achieved_distance'], 2) }}</td>
                        <td class="text-end">৳{{ number_format($bill['daily_rent'], 2) }}</td>
                        <td class="text-end">৳{{ number_format($bill['vat'], 2) }}</td>
                        <td class="text-end text-success">৳{{ number_format($bill['reward'], 2) }}</td>
                        <td class="text-end text-danger">৳{{ number_format($bill['punishment'], 2) }}</td>
                        <td class="text-end fw-bold">৳{{ number_format($bill['daily_total_rent'], 2) }}</td>
                        <td>{{ $bill['comment'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td colspan="2" class="text-end"><strong>Total:</strong></td>
                        <td class="text-center"><strong>{{ $totals['total_trips'] }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($totals['total_distance'], 2) }}</strong></td>
                        <td class="text-end"><strong>৳{{ number_format($totals['total_daily_rent'], 2) }}</strong></td>
                        <td class="text-end"><strong>৳{{ number_format($totals['total_vat'], 2) }}</strong></td>
                        <td class="text-end text-success"><strong>৳{{ number_format($totals['total_reward'], 2) }}</strong></td>
                        <td class="text-end text-danger"><strong>৳{{ number_format($totals['total_punishment'], 2) }}</strong></td>
                        <td class="text-end"><strong>৳{{ number_format($totals['total_daily_total_rent'], 2) }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @elseif($busId)
        <div class="alert alert-info text-center">
            <i class="ti ti-info-circle me-2"></i>
            No data found for the selected bus and month.
        </div>
        @else
        <div class="alert alert-warning text-center">
            <i class="ti ti-alert-circle me-2"></i>
            Please select a bus to view the monthly bill.
        </div>
        @endif
    </div>
</div>
@endsection

