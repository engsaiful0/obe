<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Print - {{ $issue->issue_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        .print-header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .print-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .issue-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-section {
            flex: 1;
            margin: 0 10px;
        }
        .info-section h4 {
            margin: 0 0 10px 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        .info-item {
            margin: 5px 0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-section {
            margin-top: 30px;
            text-align: right;
        }
        .total-item {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }
        .print-footer {
            margin-top: 50px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="print-header">
        <div class="print-title">ISSUE REPORT</div>
        <div>Issue Number: {{ $issue->issue_number }}</div>
    </div>

    <div class="issue-info">
        <div class="info-section">
            <h4>Issue Information</h4>
            <div class="info-item">
                <span class="info-label">Issue Number:</span>
                {{ $issue->issue_number }}
            </div>
            <div class="info-item">
                <span class="info-label">Date:</span>
                {{ $issue->date->format('d M Y') }}
            </div>
            <div class="info-item">
                <span class="info-label">Remarks:</span>
                {{ $issue->remarks ?? 'N/A' }}
            </div>
        </div>

        <div class="info-section">
            <h4>Employee Information</h4>
            <div class="info-item">
                <span class="info-label">Employee:</span>
                {{ $issue->employee->employee_name }}
            </div>
            <div class="info-item">
                <span class="info-label">Employee ID:</span>
                {{ $issue->employee->employee_unique_id }}
            </div>
        </div>

        <div class="info-section">
            <h4>System Information</h4>
            <div class="info-item">
                <span class="info-label">Created By:</span>
                {{ $issue->user->name }}
            </div>
            <div class="info-item">
                <span class="info-label">Created At:</span>
                {{ $issue->created_at->format('d M Y H:i:s') }}
            </div>
        </div>
    </div>

    <h4>Issued Items</h4>
    <table class="items-table">
        <thead>
            <tr>
                <th>SL</th>
                <th>Item Name</th>
                <th>Unit</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($issue->issueItems as $index => $issueItem)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $issueItem->item->item_name }}</td>
                <td>{{ $issueItem->unit->unit_name ?? 'N/A' }}</td>
                <td>{{ $issueItem->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-item">Total Items: {{ $issue->issueItems->count() }}</div>
        <div class="total-item">
            Total Quantity: 
            @php
                $quantityByUnit = [];
                foreach($issue->issueItems as $item) {
                    $unitName = $item->unit ? $item->unit->unit_name : 'No Unit';
                    if (!isset($quantityByUnit[$unitName])) {
                        $quantityByUnit[$unitName] = 0;
                    }
                    $quantityByUnit[$unitName] += $item->quantity;
                }
                $parts = [];
                foreach($quantityByUnit as $unit => $total) {
                    $parts[] = number_format($total, 2) . ' ' . $unit;
                }
                echo implode(', ', $parts);
            @endphp
        </div>
    </div>

    <div class="print-footer">
        <p>Generated on {{ now()->format('d M Y H:i:s') }}</p>
        <p>This is a computer generated report.</p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" class="btn btn-primary">Print</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
