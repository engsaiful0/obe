<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expenses Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .amount {
            text-align: right;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Expenses Report</h1>
        <p>Generated on: {{ date('F d, Y H:i:s') }}</p>
        <p>Total Records: {{ $expenses->count() }}</p>
    </div>

    @if($expenses->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Memo No</th>
                    <th>Bill No</th>
                    <th>Expense Head</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Bus Sub Type</th>
                    <th>Bus Number</th>
                    <th>Concerned Employee</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                <tr>
                    <td>{{ $loop->iteration + ($expenses->currentPage() - 1) * $expenses->perPage() }}</td>
                    <td>{{ $expense->memo_no ?? 'N/A' }}</td>
                    <td>{{ $expense->bill_no ?? 'N/A' }}</td>
                    <td>{{ $expense->expenseHead->name ?? 'N/A' }}</td>
                    <td>{{ $expense->supplier->supplier_name ?? 'N/A' }}</td>
                    <td>{{ $expense->expense_date }}</td>
                    <td class="amount">৳{{ number_format($expense->amount, 2) }}</td>
                    <td>{{ $expense->busSubType->sub_type_name ?? 'N/A' }}</td>
                    <td>{{ $expense->bus->bus_number ?? 'N/A' }}</td>
                    <td>{{ $expense->employee->employee_name ?? 'N/A' }}</td>
                    <td>{{ $expense->remarks }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">
            <h3>Summary</h3>
            <p><strong>Total Amount:</strong> ৳{{ number_format($expenses->sum('amount'), 2) }}</p>
            <p><strong>Total Records:</strong> {{ $expenses->count() }}</p>
            <p><strong>Average Amount:</strong> ৳{{ number_format($expenses->avg('amount'), 2) }}</p>
        </div>
    @else
        <p>No expenses found for the selected criteria.</p>
    @endif

    <div class="footer">
        <p>This report was generated automatically by the TMS System</p>
    </div>
</body>
</html>