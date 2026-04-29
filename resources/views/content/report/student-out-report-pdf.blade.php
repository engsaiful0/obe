<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student OUT Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
        }
        .header p {
            margin: 3px 0;
            font-size: 11px;
        }
        .report-info {
            margin-bottom: 15px;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            font-size: 9px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($appSettings && $appSettings->institute_name)
            <h2>{{ $appSettings->institute_name }}</h2>
        @endif
        <h3>Student OUT Report</h3>
        <p>Date Range: {{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}</p>
        <p>Generated on: {{ \Carbon\Carbon::now()->format('d M, Y h:i A') }}</p>
    </div>
    
    <table>
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
    
    <div class="footer">
        <p>Total Stoppages: {{ count($reportData) }} | Total Students: {{ number_format($grandTotal, 0) }}</p>
    </div>
</body>
</html>


