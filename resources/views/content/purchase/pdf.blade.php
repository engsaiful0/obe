<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Report</title>
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
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-primary {
            background-color: #cce5ff;
            color: #004085;
        }
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
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
        <h1>Purchase Report</h1>
        <p>Generated on: {{ date('F d, Y \a\t h:i A') }}</p>
        <p>Total Records: {{ $purchases->count() }}</p>
    </div>

    @if($purchases->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Purchase Number</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th class="text-right">Net Total</th>
                    <th class="text-right">Paid</th>
                    <th class="text-right">Due</th>
                    <th>Payment Method</th>
                    <th class="text-center">Items</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchases as $index => $purchase)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $purchase->purchase_number }}</td>
                        <td>{{ $purchase->supplier->supplier_name ?? 'N/A' }}</td>
                        <td>{{ \Carbon\Carbon::parse($purchase->date)->format('M d, Y') }}</td>
                        <td class="text-right">৳{{ number_format($purchase->net_total, 2) }}</td>
                        <td class="text-right">৳{{ number_format($purchase->paid, 2) }}</td>
                        <td class="text-right">৳{{ number_format($purchase->due, 2) }}</td>
                        <td>
                            {{ $purchase->paymentMethod->payment_method_name ?? 'N/A' }}
                         
                        </td>
                        <td class="text-center">{{ $purchase->purchaseItems->count() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <h3>Summary</h3>
            <div class="summary-row">
                <span>Total Purchases:</span>
                <span>{{ $purchases->count() }}</span>
            </div>
            <div class="summary-row">
                <span>Total Net Amount:</span>
                <span>৳{{ number_format($purchases->sum('net_total'), 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Total Paid Amount:</span>
                <span>৳{{ number_format($purchases->sum('paid'), 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Total Due Amount:</span>
                <span>৳{{ number_format($purchases->sum('due'), 2) }}</span>
            </div>
            <div class="summary-row summary-total">
                <span>Outstanding Balance:</span>
                <span>৳{{ number_format($purchases->sum('due'), 2) }}</span>
            </div>
        </div>
    @else
        <div class="text-center">
            <h3>No purchases found</h3>
            <p>No purchase records match the current filter criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Transport Management System</p>
        <p>© {{ date('Y') }} All rights reserved</p>
    </div>
</body>
</html>
