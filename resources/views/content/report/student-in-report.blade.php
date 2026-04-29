@extends('layouts/layoutMaster')

@section('title', 'Student IN Report')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/flatpickr/flatpickr.css')}}" />
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}"></script>
@endsection

@section('page-style')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .card-header {
            border-bottom: 2px solid #000 !important;
            padding-bottom: 10px !important;
            margin-bottom: 15px !important;
        }
        .card-title {
            font-size: 18px !important;
            font-weight: bold !important;
        }
        .table {
            font-size: 11px !important;
        }
        .table th, .table td {
            padding: 6px !important;
        }
        .alert {
            border: 1px solid #000 !important;
            padding: 10px !important;
        }
        @page {
            margin: 1cm;
            size: A4 landscape;
        }
        body {
            font-size: 11px !important;
        }
    }
</style>
@endsection

@section('page-script')
<script>
  $(function () {
    // Initialize datepickers
    $('#from_date, #to_date').flatpickr({
      dateFormat: 'Y-m-d',
      maxDate: 'today'
    });

    // Auto-submit on change
    $('#from_date, #to_date').on('change', function() {
      if ($('#from_date').val() && $('#to_date').val()) {
        $('#student-in-report-form').submit();
      }
    });
  });
</script>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="ti ti-users me-2"></i>Student IN Report
        </h5>
        <div class="no-print">
            <a href="{{ route('student-in-report.print', request()->query()) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                <i class="ti ti-printer me-1"></i>Print
            </a>
            <a href="{{ route('student-in-report.pdf', request()->query()) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                <i class="ti ti-file-pdf me-1"></i>Export PDF
            </a>
            <a href="{{ route('student-in-report.excel', request()->query()) }}" class="btn btn-sm btn-outline-success">
                <i class="ti ti-file-excel me-1"></i>Export Excel
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <!-- Filter Form -->
        <form id="student-in-report-form" method="GET" action="{{ route('student-in-report') }}" class="mb-4 no-print">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="from_date" class="form-label">From Date <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="from_date" 
                           id="from_date" 
                           class="form-control" 
                           value="{{ $fromDate }}" 
                           required>
                </div>
                
                <div class="col-md-4">
                    <label for="to_date" class="form-label">To Date <span class="text-danger">*</span></label>
                    <input type="text" 
                           name="to_date" 
                           id="to_date" 
                           class="form-control" 
                           value="{{ $toDate }}" 
                           required>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i>Generate Report
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Report Content -->
        @if(count($reportData) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 25%;">Stoppage</th>
                            <th style="width: 15%;">Bus Number</th>
                            <th style="width: 20%;">Bus Sub Type</th>
                            <th style="width: 15%;">No Of Students</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $serial = 1;
                            $grandTotal = 0;
                        @endphp
                        @foreach($reportData as $stoppageData)
                            @php
                                $rowCount = count($stoppageData['buses']);
                                $isFirstRow = true;
                            @endphp
                            @foreach($stoppageData['buses'] as $busData)
                                <tr>
                                    @if($isFirstRow)
                                        <td rowspan="{{ $rowCount }}" style="vertical-align: middle;">
                                            <strong>{{ $stoppageData['stoppage_name'] }}</strong>
                                        </td>
                                        @php $isFirstRow = false; @endphp
                                    @endif
                                    <td>{{ $busData['bus_number'] }}</td>
                                    <td>{{ $busData['bus_sub_type'] }}</td>
                                    <td class="text-end">{{ number_format($busData['total_students'], 0) }}</td>
                                </tr>
                                @php
                                    $grandTotal += $busData['total_students'];
                                @endphp
                            @endforeach
                        @endforeach
                        <tr class="table-info fw-bold">
                            <td colspan="3" class="text-end">Grand Total:</td>
                            <td class="text-end">{{ number_format($grandTotal, 0) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Report Summary -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Report Summary</h6>
                        <hr>
                        <p class="mb-1"><strong>Date Range:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}</p>
                        <p class="mb-1"><strong>Total Stoppages:</strong> {{ count($reportData) }}</p>
                        <p class="mb-0"><strong>Total Students:</strong> {{ number_format($grandTotal, 0) }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                <i class="ti ti-alert-circle me-2"></i>
                No data found for the selected date range.
            </div>
        @endif
    </div>
</div>
@endsection

