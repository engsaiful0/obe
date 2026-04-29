<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Bills Report - PDF</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 10px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 24px;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .header p {
            font-size: 10px;
            color: #666;
        }
        
        .filter-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 9px;
        }
        
        .filter-info strong {
            color: #333;
        }
        
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .summary-card {
            display: table-cell;
            padding: 10px;
            text-align: center;
            color: white;
            width: 33.33%;
        }
        
        .summary-card.primary {
            background-color: #007bff;
        }
        
        .summary-card.info {
            background-color: #17a2b8;
        }
        
        .summary-card.warning {
            background-color: #ffc107;
            color: #333;
        }
        
        .summary-card h6 {
            font-size: 9px;
            margin-bottom: 3px;
        }
        
        .summary-card h4 {
            font-size: 16px;
            margin: 0;
        }
        
        .summary-card small {
            font-size: 8px;
            display: block;
            margin-top: 3px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
        }
        
        .table th,
        .table td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
        }
        
        .table th {
            background-color: #343a40;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .badge-primary {
            background-color: #007bff;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
        
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .total-row {
            background-color: #e9ecef !important;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TMS IIUC - Monthly Bills Report</h1>
        <p>Generated on: {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</p>
        <p>Month: {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F Y') }}</p>
        <p>Total Records: {{ $bills->count() }}</p>
    </div>

    @if(!empty($filterInfo))
    <div class="filter-info">
        <strong>Applied Filters:</strong>
        @if(isset($filterInfo['bus']))
            Bus: {{ $filterInfo['bus'] }} |
        @endif
        @if(isset($filterInfo['bus_type']))
            Bus Type: {{ $filterInfo['bus_type'] }} |
        @endif
        @if(isset($filterInfo['from_date']))
            From Date: {{ $filterInfo['from_date'] }} |
        @endif
        @if(isset($filterInfo['to_date']))
            To Date: {{ $filterInfo['to_date'] }}
        @endif
    </div>
    @endif

    <div class="summary-cards">
        <div class="summary-card primary">
            <h6>Total Buses</h6>
            <h4>{{ $totalBuses }}</h4>
        </div>
        <div class="summary-card info">
            <h6>Hired Buses</h6>
            <h4>{{ $hiredBuses->count() }}</h4>
            <small>৳{{ number_format($totalHiredAmount, 2) }}</small>
        </div>
        <div class="summary-card warning">
            <h6>BRTC Buses</h6>
            <h4>{{ $brtcBuses->count() }}</h4>
            <small>৳{{ number_format($totalBrtcAmount, 2) }}</small>
        </div>
    </div>

    @if($bills->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Vehicle Details</th>
                <th>Month</th>
                <th>Bus Type</th>
                <th>Rate</th>
                <th>Trips</th>
                <th class="text-right">Base Amount</th>
                <th class="text-right">Rewards</th>
                <th class="text-right">Punishments</th>
                <th class="text-right">Final Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bills as $index => $bill)
            <tr>
                <td class="text-center">
                    <span class="badge badge-secondary">{{ $index + 1 }}</span>
                </td>
                <td>
                    <strong>{{ $bill['bus']->bus_number }}</strong><br>
                    <small>{{ $bill['bus']->model_name }}</small>
                </td>
                <td class="text-center">
                    <span class="badge badge-secondary">{{ $bill['formatted_bill_month'] }}</span>
                </td>
                <td class="text-center">
                    <span class="badge {{ $bill['bus_type'] == 'hired' ? 'badge-primary' : 'badge-warning' }}">
                        {{ strtoupper($bill['bus_type']) }}
                    </span>
                </td>
                <td class="text-center">
                    @if($bill['bus_type'] == 'hired')
                        <strong>৳{{ number_format($bill['daily_rate'], 2) }}</strong><br>
                        <small>Per Day</small>
                    @else
                        <strong>৳{{ number_format($bill['rate_per_km'], 2) }}</strong><br>
                        <small>Per KM</small>
                    @endif
                </td>
                <td class="text-center">
                    @if($bill['bus_type'] == 'hired')
                        <strong>{{ $bill['total_trips'] }}</strong><br>
                        <small>{{ $bill['full_days'] }} full + {{ $bill['half_days'] }} half</small>
                    @else
                        <strong>{{ $bill['total_trips'] }}</strong><br>
                        <small>{{ number_format($bill['total_distance'], 1) }} KM</small>
                    @endif
                </td>
                <td class="text-right">৳{{ number_format($bill['base_amount'], 2) }}</td>
                <td class="text-right">+৳{{ number_format($bill['total_rewards'], 2) }}</td>
                <td class="text-right">-৳{{ number_format($bill['total_punishments'], 2) }}</td>
                <td class="text-right"><strong>৳{{ number_format($bill['final_amount'], 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>Grand Total:</strong></td>
                <td class="text-right"><strong>৳{{ number_format($bills->sum('base_amount'), 2) }}</strong></td>
                <td class="text-right"><strong>+৳{{ number_format($bills->sum('total_rewards'), 2) }}</strong></td>
                <td class="text-right"><strong>-৳{{ number_format($bills->sum('total_punishments'), 2) }}</strong></td>
                <td class="text-right"><strong>৳{{ number_format($grandTotal, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="text-center py-5">
        <p class="text-muted">No monthly bills found.</p>
    </div>
    @endif

    <div class="footer">
        <p>This is a computer-generated document.</p>
        <p>Thank you for using TMS IIUC</p>
    </div>
</body>
</html>


