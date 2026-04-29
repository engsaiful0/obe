<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Report - Print Preview</title>
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
        
        .table tfoot {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        .table tfoot th {
            background-color: #e9ecef;
        }
        
        .print-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            
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
            <p style="font-size: 20px;font-weight:bold">International Islamic University</p>
            <p style="font-size: 16px;font-weight:bold">Transport Management Division</p>
            <p>Summary of Bus Maintenance Bills</p>
            <p>Generated on: {{ date('F d, Y H:i:s') }}</p>
        </div>

        @if(!empty($filterInfo))
        <div class="filter-info">
            <strong>Applied Filters:</strong>
            @if(isset($filterInfo['expense_head']))
                Expense Head: {{ $filterInfo['expense_head'] }} |
            @endif
            @if(isset($filterInfo['bus_sub_type']))
                Bus Sub Type: {{ $filterInfo['bus_sub_type'] }} |
            @endif
            @if(isset($filterInfo['bus']))
                Bus: {{ $filterInfo['bus'] }} |
            @endif
            @if(isset($filterInfo['employee']))
                Employee: {{ $filterInfo['employee'] }} |
            @endif
            @if(isset($filterInfo['date_range']))
                Date Range: {{ $filterInfo['date_range'] }}
                @if(isset($filterInfo['from_date']) && isset($filterInfo['to_date']))
                    — {{ \Carbon\Carbon::parse($filterInfo['from_date'])->format('F Y') }}, date {{ \Carbon\Carbon::parse($filterInfo['from_date'])->format('d') }} to {{ \Carbon\Carbon::parse($filterInfo['to_date'])->format('d') }}
                @endif
                |
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

        @if($expenses->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Memo No</th>
                        <th>Bill No</th>
                        <th>Supplier</th>
                        <th>Expense Head</th>
                        
                        <th>Bus Number</th>
                        
                        <th>Date</th>
                        
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $index => $expense)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $expense->memo_no ?? 'N/A' }}</td>
                        <td>{{ $expense->bill_no ?? 'N/A' }}</td>
                        <td>{{ $expense->supplier->supplier_name ?? 'N/A' }}</td>
                        <td>{{ $expense->expenseHead->name ?? 'N/A' }}</td>
                        
                        <td>{{ $expense->bus ? $expense->bus->bus_number : 'N/A' }}</td>
                        
                        <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                        
                        <td>{{ number_format($expense->amount, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="7" style="text-align: right;">Total Amount:</th>
                        <th>{{ number_format($totalAmount, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-5">
            <p class="text-muted">No expenses found to print.</p>
        </div>
        @endif

        <div class="print-footer" style="margin-top: 50px;display: flex;justify-content: space-between;">
            <div class="" style="width: 33.33%;float: left;">
                <p>___________________<br></p>
                <p>Prepared by:</p>
                <p>TMD, IIUC</p>
            </div>
            <div class="" style="width: 33.33%;float: left;">
            <p>___________________<br></p>
                <p>Director(In-Charge)</p>
                <p>TMD, IIUC</p>
            </div>
            <div class="" style="width: 33.33%;float: left;">
            <p>___________________<br></p>
                <p>Chairman:</p>
                <p>TMC, IIUC</p>
            </div>
            
        </div>
        <div class="print-footer" style="margin-top: 50px;display: flex;justify-content: space-between;">
        <div class="" style="width: 33.33%;float: left;">
        <p>___________________<br></p>
                <p>Authorized by:</p>
                <p>ACFD, IIUC</p>
            </div>
            <div class="" style="width: 33.33%;float: left;">
            <p>___________________<br></p>
                <p>Director(In-Charge):</p>
                <p>ACFD, IIUC</p>
            </div>
            <div class="" style="width: 33.33%;float: left;">
            <p>___________________<br></p>
                <p>Treasurer,IIUC:</p>
                
            </div>
        </div>
    </div>

   
</body>
</html>

