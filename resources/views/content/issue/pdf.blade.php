<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Issue Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-primary {
            background-color: #cce5ff;
            color: #004085;
        }
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .summary-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Issue Report</h1>
        <p>Generated on: {{ date('F d, Y \a\t h:i A') }}</p>
        <p>Total Records: {{ $issues->count() }}</p>
    </div>

    @if($issues->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Issue Number</th>
                    <th>Employee</th>
                    <th>Date</th>
                    <th class="text-center">Items Count</th>
                    <th>Total Quantity</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($issues as $index => $issue)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $issue->issue_number }}</strong></td>
                        <td>{{ $issue->employee->employee_name ?? 'N/A' }}</td>
                        <td>{{ $issue->date->format('d M Y') }}</td>
                        <td class="text-center">
                            <span class="badge badge-primary">{{ $issue->issueItems->count() }}</span>
                        </td>
                        <td>
                            @php
                                $quantityByUnit = [];
                                foreach($issue->issueItems as $item) {
                                    $unitName = $item->unit ? $item->unit->unit_name : 'No Unit';
                                    if (!isset($quantityByUnit[$unitName])) {
                                        $quantityByUnit[$unitName] = 0;
                                    }
                                    $quantityByUnit[$unitName] += $item->quantity;
                                }
                            @endphp
                            @if(count($quantityByUnit) > 0)
                                @foreach($quantityByUnit as $unit => $total)
                                    <span class="badge badge-info">{{ number_format($total, 2) }} {{ $unit }}</span>
                                @endforeach
                            @else
                                <span class="badge badge-secondary">N/A</span>
                            @endif
                        </td>
                        <td>{{ $issue->remarks ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <h3>Summary</h3>
            <div class="summary-row">
                <span>Total Issues:</span>
                <span>{{ $issues->count() }}</span>
            </div>
            <div class="summary-row">
                <span>Total Items Issued:</span>
                <span>{{ $issues->sum(function($issue) { return $issue->issueItems->count(); }) }}</span>
            </div>
            <div class="summary-row summary-total">
                <span>Total Issues Generated:</span>
                <span>{{ $issues->count() }}</span>
            </div>
        </div>
    @else
        <div class="text-center">
            <h3>No issues found</h3>
            <p>No issue records match the current filter criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Transport Management System</p>
        <p>© {{ date('Y') }} All rights reserved</p>
    </div>
</body>
</html>

