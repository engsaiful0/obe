<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student IN Report - Print</title>
    <style>
        @media print {
            .no-print { 
                display: none !important; 
            }
            .page-break { 
                page-break-after: always; 
            }
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px;
            line-height: 1.4;
            margin: 20px;
        }
        .print-actions {
            margin-bottom: 20px;
            text-align: right;
        }
        .print-actions button {
            padding: 8px 16px;
            margin-left: 10px;
            cursor: pointer;
            border: 1px solid #007bff;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            font-size: 14px;
        }
        .print-actions button:hover {
            background-color: #0056b3;
        }
        .print-actions .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .print-actions .btn-secondary:hover {
            background-color: #545b62;
        }
        .header { 
            margin-bottom: 20px; 
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 20px;
            font-weight: bold;
        }
        .header h3 {
            margin: 10px 0 5px 0;
            font-size: 18px;
        }
        .header p {
            margin: 3px 0;
            color: #666;
            font-size: 12px;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
        }
        .table th, .table td { 
            border: 1px solid #000; 
            padding: 8px; 
            text-align: left;
        }
        .table th { 
            background-color: #343a40; 
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .table td {
            text-align: left;
        }
        .text-center { 
            text-align: center; 
        }
        .text-end { 
            text-align: right; 
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .summary h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 14px;
        }
        .summary p {
            margin: 5px 0;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()" class="btn-secondary">Close</button>
    </div>

    <div class="header">
        @if($appSettings && $appSettings->institute_name)
            <h2>{{ $appSettings->institute_name }}</h2>
        @endif
        <h3>Student IN Report</h3>
        <p>Date Range: {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}</p>
        <p>Generated on: {{ \Carbon\Carbon::now()->format('d M, Y h:i A') }}</p>
    </div>

    @if(count($reportData) > 0)
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 25%;">Stoppage</th>
                    <th style="width: 15%;">Bus Number</th>
                    <th style="width: 20%;">Bus Sub Type</th>
                    <th style="width: 15%;" class="text-end">No Of Students</th>
                </tr>
            </thead>
            <tbody>
                @php
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
                <tr class="total-row">
                    <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                    <td class="text-end"><strong>{{ number_format($grandTotal, 0) }}</strong></td>
                </tr>
            </tbody>
        </table>
        
        <div class="summary">
            <h4>Report Summary</h4>
            <p><strong>Date Range:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}</p>
            <p><strong>Total Stoppages:</strong> {{ count($reportData) }}</p>
            <p><strong>Total Students:</strong> {{ number_format($grandTotal, 0) }}</p>
        </div>
    @else
        <div style="padding: 20px; text-align: center; border: 1px solid #ddd; background-color: #fff3cd; color: #856404;">
            <strong>No data found for the selected date range.</strong>
        </div>
    @endif
</body>
</html>

