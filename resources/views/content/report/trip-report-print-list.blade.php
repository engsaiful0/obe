<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trip Report - Print Preview</title>
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
            max-width: 100%;
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
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 10px;
        }
        
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
        }
        
        .table th {
            background-color: #343a40;
            color: white;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table tbody td:first-child {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: left;
            position: sticky;
            left: 0;
            z-index: 5;
        }
        
        .table tfoot th {
            background-color: #e9ecef;
            font-weight: bold;
            position: sticky;
            bottom: 0;
        }
        
        .table tfoot th:first-child {
            position: sticky;
            left: 0;
            z-index: 10;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
            background-color: #28a745;
            color: white;
        }
        
        .text-muted {
            color: #6c757d;
        }
        
        .print-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .table-responsive {
            overflow-x: auto;
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
                font-size: 8px !important;
            }
            
            .table th,
            .table td {
                padding: 4px !important;
                border: 1px solid #333 !important;
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
            <h1>TMS IIUC - Trip Report</h1>
            <p>Generated on: {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</p>
            @if(isset($fromDate) && isset($toDate))
            <p>Report Period: {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}</p>
            <p>Total Drivers: {{ isset($drivers) ? $drivers->count() : 0 }} | Total Days: {{ isset($dates) ? count($dates) : 0 }}</p>
            @endif
        </div>

        @if(isset($drivers) && isset($dates) && isset($pivotData))
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle" style="min-width: 200px;">
                            <strong>Driver Name</strong>
                        </th>
                        @foreach($dates as $date)
                        <th style="min-width: 80px;">
                            {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                        </th>
                        @endforeach
                        <th class="bg-primary text-white" style="min-width: 80px;">
                            <strong>Total</strong>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drivers as $driver)
                    <tr>
                        <td>
                            <strong>{{ $driver->full_name }}</strong>
                            @if($driver->driver_unique_id)
                                <br><small class="text-muted">ID: {{ $driver->driver_unique_id }}</small>
                            @endif
                        </td>
                        @php
                            $driverTotal = 0;
                        @endphp
                        @foreach($dates as $date)
                            @php
                                $tripCount = $pivotData[$driver->id][$date] ?? 0;
                                $driverTotal += $tripCount;
                            @endphp
                            <td>
                                @if($tripCount > 0)
                                    <span class="badge">{{ $tripCount }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="bg-light">
                            <strong>{{ $driverTotal }}</strong>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($dates) + 2 }}" class="text-center py-4">
                            <div class="text-muted">
                                No trips found for the selected date range.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-light">
                        <th>
                            <strong>Date Total</strong>
                        </th>
                        @foreach($dates as $date)
                            @php
                                $dateTotal = 0;
                                foreach($drivers as $driver) {
                                    $dateTotal += $pivotData[$driver->id][$date] ?? 0;
                                }
                            @endphp
                            <th>
                                <strong>{{ $dateTotal }}</strong>
                            </th>
                        @endforeach
                        <th class="bg-primary text-white">
                            @php
                                $grandTotal = 0;
                                foreach($drivers as $driver) {
                                    foreach($dates as $date) {
                                        $grandTotal += $pivotData[$driver->id][$date] ?? 0;
                                    }
                                }
                            @endphp
                            <strong>{{ $grandTotal }}</strong>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">No data available to print.</p>
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


