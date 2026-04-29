<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue List - Print Preview</title>
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
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 12px;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
            text-align: center;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .table tr:hover {
            background-color: #f0f0f0;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .fw-medium {
            font-weight: 500;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            display: inline-block;
        }
        
        .badge.bg-primary {
            background-color: #007bff;
            color: white;
        }
        
        .badge.bg-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .badge.bg-secondary {
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
                font-size: 10px !important;
            }
            
            .table th,
            .table td {
                padding: 6px !important;
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
            <h1>TMS IIUC - Issue List</h1>
            <p>Generated on: {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</p>
            <p>Total Records: {{ $issues->count() }}</p>
        </div>

        @if($issues->count() > 0)
        <div class="table-responsive">
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
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <span class="fw-medium">{{ $issue->issue_number }}</span>
                        </td>
                        <td>{{ $issue->employee->employee_name ?? 'N/A' }}</td>
                        <td>{{ $issue->date->format('d M Y') }}</td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $issue->issueItems->count() }}</span>
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
                                    <span class="badge bg-info me-1">{{ number_format($total, 2) }} {{ $unit }}</span>
                                @endforeach
                            @else
                                <span class="badge bg-secondary">N/A</span>
                            @endif
                        </td>
                        <td>{{ $issue->remarks ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #e9ecef; font-weight: bold;">
                        <td colspan="4" class="text-right">Total:</td>
                        <td class="text-center">{{ $issues->sum(function($issue) { return $issue->issueItems->count(); }) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">No issues found to print.</p>
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

