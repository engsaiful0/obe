<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Bills Report - Print Preview</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }
        
        .print-controls button {
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .print-controls button:hover {
            background: #0056b3;
        }
        
        .print-controls button.close-btn {
            background: #6c757d;
        }
        
        .print-controls button.close-btn:hover {
            background: #5a6268;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }
        
        .print-header h1 {
            font-size: 28px;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .print-header p {
            font-size: 14px;
            color: #666;
        }
        
        .filter-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .filter-info strong {
            color: #333;
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .summary-card {
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            color: white;
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
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .summary-card h4 {
            font-size: 24px;
            margin: 0;
        }
        
        .summary-card small {
            font-size: 11px;
            display: block;
            margin-top: 5px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
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
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
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
        
        .print-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .total-row {
            background-color: #e9ecef !important;
            font-weight: bold;
        }
        
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            
            .print-controls {
                display: none !important;
            }
            
            .container {
                box-shadow: none !important;
                padding: 20px !important;
                max-width: 100% !important;
            }
            
            .table {
                font-size: 9px !important;
            }
            
            .table th,
            .table td {
                padding: 4px !important;
                border: 1px solid #333 !important;
            }
            
            .filter-info {
                background-color: #f0f0f0 !important;
            }
            
            .summary-cards {
                page-break-inside: avoid;
            }
            
            @page {
                margin: 1cm;
                size: A4 landscape;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button onclick="window.print()">
            🖨️ Print
        </button>
        <button class="close-btn" onclick="window.close()">
            ✕ Close
        </button>
    </div>

    <div class="container">
        <div class="print-header">
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

        @php
            $hiredBuses = $bills->where('bus_type', 'hired');
            $brtcBuses = $bills->where('bus_type', 'brtc');
            $totalHiredAmount = $hiredBuses->sum('final_amount');
            $totalBrtcAmount = $brtcBuses->sum('final_amount');
            $grandTotal = $bills->sum('final_amount');
        @endphp

        <div class="summary-cards">
            <div class="summary-card primary">
                <h6>Total Buses</h6>
                <h4>{{ $bills->count() }}</h4>
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
        <div class="table-responsive">
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
                        <td class="text-right text-success">+৳{{ number_format($bill['total_rewards'], 2) }}</td>
                        <td class="text-right text-danger">-৳{{ number_format($bill['total_punishments'], 2) }}</td>
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
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">No monthly bills found to print.</p>
        </div>
        @endif

        <div class="print-footer">
            <p>This is a computer-generated document.</p>
            <p>Thank you for using TMS IIUC</p>
        </div>
    </div>

    <script>
        // Optional: Auto-print after page loads (commented out)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };
    </script>
</body>
</html>


