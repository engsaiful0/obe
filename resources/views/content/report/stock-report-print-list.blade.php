<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Report - Print Preview</title>
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
        
        .text-muted {
            color: #6c757d;
        }
        
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            display: inline-block;
        }
        
        .badge-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
        
        .badge-primary {
            background-color: #007bff;
            color: white;
        }
        
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .badge-danger {
            background-color: #dc3545;
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
                padding: 5px !important;
                border: 1px solid #333 !important;
            }
            
            .filter-info {
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
            <h1>TMS IIUC - Stock Report</h1>
            <p>Generated on: {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</p>
            <p>Total Records: {{ $items->count() }}</p>
        </div>

        @if(!empty($filterInfo))
        <div class="filter-info">
            <strong>Applied Filters:</strong>
            @if(isset($filterInfo['item']))
                Item: {{ $filterInfo['item'] }} |
            @endif
            @if(isset($filterInfo['date_range']))
                Date Range: {{ $filterInfo['date_range'] }} |
            @endif
            @if(isset($filterInfo['from_date']) || isset($filterInfo['to_date']))
                @if(isset($filterInfo['from_date']) && isset($filterInfo['to_date']))
                    Custom Date: {{ $filterInfo['from_date'] }} to {{ $filterInfo['to_date'] }}
                @elseif(isset($filterInfo['from_date']))
                    From Date: {{ $filterInfo['from_date'] }}
                @elseif(isset($filterInfo['to_date']))
                    To Date: {{ $filterInfo['to_date'] }}
                @endif
            @endif
        </div>
        @endif

        @if($items->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Name</th>
                        <th class="text-right">Opening Stock</th>
                        <th class="text-right">Total Purchased</th>
                        <th class="text-right">Total Issued</th>
                        <th class="text-right">Current Stock</th>
                        <th class="text-right">Total Purchased Amount</th>
                        <th class="text-center">Last Purchase Date</th>
                        <th class="text-center">Last Issue Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $stockItem)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $stockItem['item']->item_name }}</strong></td>
                        <td class="text-right">
                            <span class="badge badge-info">{{ number_format($stockItem['opening_stock'], 2) }}</span>
                        </td>
                        <td class="text-right">
                            <span class="badge badge-success">{{ number_format($stockItem['total_purchased'], 2) }}</span>
                        </td>
                        <td class="text-right">
                            <span class="badge badge-warning">{{ number_format($stockItem['total_issued'], 2) }}</span>
                        </td>
                        <td class="text-right">
                            @if($stockItem['current_stock'] > 0)
                                <span class="badge badge-primary">{{ number_format($stockItem['current_stock'], 2) }}</span>
                            @elseif($stockItem['current_stock'] == 0)
                                <span class="badge badge-secondary">{{ number_format($stockItem['current_stock'], 2) }}</span>
                            @else
                                <span class="badge badge-danger">{{ number_format($stockItem['current_stock'], 2) }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <strong>৳{{ number_format($stockItem['total_purchased_amount'], 2) }}</strong>
                        </td>
                        <td class="text-center">
                            @if($stockItem['last_purchase_date'])
                                <small class="text-muted">{{ \Carbon\Carbon::parse($stockItem['last_purchase_date'])->format('d M Y') }}</small>
                            @else
                                <small class="text-muted">Never</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($stockItem['last_issue_date'])
                                <small class="text-muted">{{ \Carbon\Carbon::parse($stockItem['last_issue_date'])->format('d M Y') }}</small>
                            @else
                                <small class="text-muted">Never</small>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="2" class="text-right"><strong>Total Items:</strong></td>
                        <td class="text-right"><strong>{{ $items->count() }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($items->sum('total_purchased'), 2) }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($items->sum('total_issued'), 2) }}</strong></td>
                        <td class="text-right"><strong>{{ number_format($items->sum('current_stock'), 2) }}</strong></td>
                        <td class="text-right"><strong>৳{{ number_format($items->sum('total_purchased_amount'), 2) }}</strong></td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">No stock data found to print.</p>
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


