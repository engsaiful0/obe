<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daywise Trip Report - Print Preview</title>
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
        
        .summary-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #e7f3ff;
            border-radius: 5px;
            font-size: 12px;
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
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #000;
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
            
            .bus-info,
            .summary-info {
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
            <h1>TMS IIUC - Daywise Trip Report</h1>
            <p>Generated on: {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</p>
            <p>Period: {{ \Carbon\Carbon::parse($fromDate)->format('F d, Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('F d, Y') }}</p>
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
                    <strong>Bus Type:</strong> {{ $bus->busSubType->sub_type_name ?? 'N/A' }}
                </div>
                <div>
                    <strong>Period:</strong> {{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}
                </div>
            </div>
        </div>

        <div class="summary-info">
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                <div>
                    <strong>Total Trips:</strong> {{ $trips->count() }}
                </div>
                <div>
                    <strong>Total Distance:</strong> {{ number_format($totalDistance, 2) }} KM
                </div>
                <div>
                    <strong>Total Passengers:</strong> {{ $totalPassengers }}
                </div>
            </div>
        </div>

        @if($trips->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Serial</th>
                        <th>Date</th>
                        <th>Trip Number</th>
                        <th>Trip Type</th>
                        <th>From Stoppage</th>
                        <th>To Stoppage</th>
                        <th class="text-right">Distance (KM)</th>
                        <th class="text-center">In Time</th>
                        <th class="text-center">Out Time</th>
                        <th class="text-center">Passengers</th>
                        <th>Driver</th>
                        <th>Bus Helper</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trips as $trip)
                    @php
                        // Calculate distance from stoppages
                        $distance = 0;
                        if ($trip->trip_type === 'in') {
                            $distance = $trip->startStoppage->distance ?? 0;
                        } else {
                            $distance = $trip->endStoppage->distance ?? 0;
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $trip->trip_date->format('d M Y') }}</td>
                        <td class="text-center">{{ $trip->trip_number ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $trip->trip_type === 'in' ? 'success' : 'warning' }}">
                                {{ strtoupper($trip->trip_type) }}
                            </span>
                        </td>
                        <td>{{ $trip->startStoppage->stoppage_name ?? 'N/A' }}</td>
                        <td>{{ $trip->endStoppage->stoppage_name ?? 'N/A' }}</td>
                        <td class="text-right">{{ number_format($distance, 2) }}</td>
                        <td class="text-center">
                            @if($trip->in_time)
                                {{ \Carbon\Carbon::parse($trip->in_time)->format('h:i A') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">
                            @if($trip->out_time)
                                {{ \Carbon\Carbon::parse($trip->out_time)->format('h:i A') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">{{ $trip->passengers ?? 0 }}</td>
                        <td>{{ $trip->driver->full_name ?? 'N/A' }}</td>
                        <td>{{ $trip->busHelper->bus_helper_name ?? 'N/A' }}</td>
                        <td>{{ $trip->remarks ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="6" class="text-right"><strong>Total:</strong></td>
                        <td class="text-right"><strong>{{ number_format($totalDistance, 2) }}</strong></td>
                        <td colspan="2"></td>
                        <td class="text-center"><strong>{{ $totalPassengers }}</strong></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">No trips found to print.</p>
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


