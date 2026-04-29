<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>BRTC Bus Monthly Bill - {{ $bus->bus_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 16px;
        }
        .bus-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f5f5f5;
        }
        .bus-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .bus-info td {
            padding: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        tfoot {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $appSetting->institute_name ?? 'Transport Management System' }}</h2>
        <h3>BRTC Bus Monthly Bill</h3>
    </div>

    <div class="bus-info">
        <table>
            <tr>
                <td><strong>Bus Number:</strong> {{ $bus->bus_number }}</td>
                <td><strong>Model:</strong> {{ $bus->model_name }}</td>
                <td><strong>Seating Capacity:</strong> {{ $bus->seating_capacity ?? 'N/A' }}</td>
                <td><strong>Rate per KM:</strong> ৳{{ number_format($bus->rate_per_km ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Period:</strong> {{ $fromDate->format('F d, Y') }} - {{ $toDate->format('F d, Y') }}</td>
                <td colspan="2"><strong>Month:</strong> {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F Y') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
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
                <td class="text-center">{{ $bill['serial'] }}</td>
                <td>{{ $bill['date']->format('d-m-Y') }}</td>
                <td class="text-center">{{ $bill['number_of_trips'] }}</td>
                <td class="text-right">{{ number_format($bill['achieved_distance'], 2) }}</td>
                <td class="text-right">৳{{ number_format($bill['daily_rent'], 2) }}</td>
                <td class="text-right">৳{{ number_format($bill['vat'], 2) }}</td>
                <td class="text-right text-success">৳{{ number_format($bill['reward'], 2) }}</td>
                <td class="text-right text-danger">৳{{ number_format($bill['punishment'], 2) }}</td>
                <td class="text-right">৳{{ number_format($bill['daily_total_rent'], 2) }}</td>
                <td>{{ $bill['comment'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right"><strong>Total:</strong></td>
                <td class="text-center"><strong>{{ $totals['total_trips'] }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['total_distance'], 2) }}</strong></td>
                <td class="text-right"><strong>৳{{ number_format($totals['total_daily_rent'], 2) }}</strong></td>
                <td class="text-right"><strong>৳{{ number_format($totals['total_vat'], 2) }}</strong></td>
                <td class="text-right text-success"><strong>৳{{ number_format($totals['total_reward'], 2) }}</strong></td>
                <td class="text-right text-danger"><strong>৳{{ number_format($totals['total_punishment'], 2) }}</strong></td>
                <td class="text-right"><strong>৳{{ number_format($totals['total_daily_total_rent'], 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Generated on: {{ now()->format('F d, Y h:i A') }}</p>
        <p>{{ $appSetting->institute_name ?? 'Transport Management System' }}</p>
    </div>
</body>
</html>

