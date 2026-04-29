<!DOCTYPE html>
<html>
<head>
    <title>Damages Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Damages Report</h2>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Warehouse</th>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Reason</th>
                <th>Approximate Value</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($damages as $damage)
                @foreach($damage->damageItems as $damageItem)
                <tr>
                    <td>{{ $damage->date->format('Y-m-d') }}</td>
                    <td>{{ $damage->warehouse->warehouse_name ?? 'N/A' }}</td>
                    <td>{{ $damageItem->item->item_name ?? 'N/A' }}</td>
                    <td>{{ number_format($damageItem->quantity, 2) }}</td>
                    <td>{{ $damageItem->reason ?? 'N/A' }}</td>
                    <td>{{ $damageItem->approximate ? number_format($damageItem->approximate, 2) : 'N/A' }}</td>
                    <td>{{ $damage->remarks ?? 'N/A' }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>

