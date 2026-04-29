<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRTC Bus Monthly Bill - Print Preview</title>
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
        
        .bus-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 12px;
        }
        
        .bus-info strong {
            color: #333;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
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
        
        .text-success {
            color: #28a745;
        }
        
        .text-danger {
            color: #dc3545;
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
                padding: 5px !important;
                border: 1px solid #333 !important;
            }
            
            .bus-info {
                background-color: #f0f0f0 !important;
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
            <h1>TMS IIUC - BRTC Bus Monthly Bill</h1>
            <p>Generated on: {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</p>
            <p>Month: {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F Y') }}</p>
            <p>Total Days: {{ count($dailyBills) }}</p>
        </div>

        <div class="bus-info">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                <div>
                    <strong>Bus Number:</strong> {{ $bus->bus_number }}
                </div>
                <div>
                    <strong>Model:</strong> {{ $bus->model_name }}
                </div>
                <div>
                    <strong>Seating Capacity:</strong> {{ $bus->seating_capacity ?? 'N/A' }}
                </div>
                <div>
                    <strong>Rate per KM:</strong> ৳{{ number_format($bus->rate_per_km ?? 0, 2) }}
                </div>
            </div>
            <div style="margin-top: 10px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                <div>
                    <strong>Period:</strong> {{ $fromDate->format('F d, Y') }} - {{ $toDate->format('F d, Y') }}
                </div>
                <div>
                    <strong>Month:</strong> {{ \Carbon\Carbon::create($currentYear, $currentMonth, 1)->format('F Y') }}
                </div>
            </div>
        </div>

        @if(count($dailyBills) > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial</th>
                        <th>Date</th>
                        <th class="text-center">Number of Trips</th>
                        <th class="text-right">Achieved Distance (KM)</th>
                        <th class="text-right">Daily Rent</th>
                        <th class="text-right">15% VAT (Exclude)</th>
                        <th class="text-right">Reward (Include)</th>
                        <th class="text-right">Punishment (Exclude)</th>
                        <th class="text-right">Daily Total Rent</th>
                        <th>Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyBills as $bill)
                    <tr>
                        <td class="text-center">{{ $bill['serial'] }}</td>
                        <td>{{ $bill['date']->format('d M Y') }}</td>
                        <td class="text-center">{{ $bill['number_of_trips'] }}</td>
                        <td class="text-right">{{ number_format($bill['achieved_distance'], 2) }}</td>
                        <td class="text-right">৳{{ number_format($bill['daily_rent'], 2) }}</td>
                        <td class="text-right">৳{{ number_format($bill['vat'], 2) }}</td>
                        <td class="text-right text-success">৳{{ number_format($bill['reward'], 2) }}</td>
                        <td class="text-right text-danger">৳{{ number_format($bill['punishment'], 2) }}</td>
                        <td class="text-right"><strong>৳{{ number_format($bill['daily_total_rent'], 2) }}</strong></td>
                        <td>{{ $bill['comment'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
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
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">No data found to print.</p>
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


