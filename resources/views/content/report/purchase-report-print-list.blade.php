<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Report - Print Preview</title>
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
        
        .text-danger {
            color: #dc3545;
        }
        
        .text-success {
            color: #28a745;
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
            <h1>TMS IIUC - Purchase Report</h1>
            <p>Generated on: {{ \Carbon\Carbon::now()->format('F d, Y \a\t h:i A') }}</p>
            <p>Total Records: {{ $purchases->count() }}</p>
        </div>

        @if(!empty($filterInfo))
        <div class="filter-info">
            <strong>Applied Filters:</strong>
            @if(isset($filterInfo['supplier']))
                Supplier: {{ $filterInfo['supplier'] }} |
            @endif
            @if(isset($filterInfo['item']))
                Item: {{ $filterInfo['item'] }} |
            @endif
            @if(isset($filterInfo['payment_method']))
                Payment Method: {{ $filterInfo['payment_method'] }} |
            @endif
            @if(isset($filterInfo['purchase_number']))
                Purchase Number: {{ $filterInfo['purchase_number'] }} |
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

        @if($purchases->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Purchase Number</th>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Payment Method</th>
                        <th>Items</th>
                        <th class="text-right">Net Total</th>
                        <th class="text-right">Paid Amount</th>
                        <th class="text-right">Due Amount</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $index => $purchase)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $purchase->purchase_number }}</strong></td>
                        <td>{{ $purchase->date ? \Carbon\Carbon::parse($purchase->date)->format('d M Y') : 'N/A' }}</td>
                        <td>{{ $purchase->supplier ? $purchase->supplier->supplier_name : 'N/A' }}</td>
                        <td>{{ $purchase->paymentMethod ? $purchase->paymentMethod->payment_method_name : 'N/A' }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                @foreach($purchase->purchaseItems as $item)
                                <small class="text-muted">
                                    {{ $item->item->item_name ?? 'N/A' }} 
                                    ({{ $item->quantity }} {{ $item->unit ? $item->unit->unit_name : 'pcs' }})
                                </small>
                                @endforeach
                            </div>
                        </td>
                        <td class="text-right">৳{{ number_format($purchase->net_total, 2) }}</td>
                        <td class="text-right">৳{{ number_format($purchase->paid, 2) }}</td>
                        <td class="text-right">
                            @if($purchase->due > 0)
                                <span class="text-danger">৳{{ number_format($purchase->due, 2) }}</span>
                            @else
                                <span class="text-success">৳0.00</span>
                            @endif
                        </td>
                        <td>{{ $purchase->remarks ? str($purchase->remarks)->limit(30) : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td colspan="5" class="text-right"><strong>Total Amounts:</strong></td>
                        <td class="text-right"><strong>৳{{ number_format($totalNetAmount, 2) }}</strong></td>
                        <td class="text-right"><strong>৳{{ number_format($totalPaidAmount, 2) }}</strong></td>
                        <td class="text-right"><strong>৳{{ number_format($totalDueAmount, 2) }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">No purchases found to print.</p>
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


