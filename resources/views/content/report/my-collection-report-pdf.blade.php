<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Collection Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .header h2 {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 16px;
            font-weight: normal;
        }
        
        .summary {
            background: #f8f9fa;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        
        .summary h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 18px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .summary-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 14px;
        }
        
        .section-title {
            background: #007bff;
            color: white;
            padding: 10px;
            margin: 25px 0 15px 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 11px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>My Collection Report</h1>
        <h2>{{ $appSetting->institute_name ?? 'Educational Institute' }}</h2>
        <p>Report Generated: {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}</p>
        @if($selectedUser)
        <p>User: {{ $selectedUser->name }} ({{ $selectedUser->rule->name ?? 'No Role' }})</p>
        @endif
    </div>

    <div class="summary">
        <h3>Summary</h3>
        <div class="summary-row">
            <span>Total Fee Collection:</span>
            <span>৳{{ number_format($totalFeeCollection, 2) }}</span>
        </div>
        <div class="summary-row">
            <span>Total Expenses:</span>
            <span>৳{{ number_format($totalExpenses, 2) }}</span>
        </div>
        <div class="summary-row">
            <span>Net Amount:</span>
            <span>৳{{ number_format($netAmount, 2) }}</span>
        </div>
    </div>

    <div class="section-title">Fee Collections</div>
    @if($feeCollections->count() > 0)
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Academic Year</th>
                <th>Semester</th>
                <th>Payment Method</th>
                <th class="text-right">Amount</th>
                <th>Date</th>
                @if($userRole === 'Super Admin')
                <th>Collected By</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($feeCollections as $index => $collection)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $collection->student->full_name_in_english_block_letter ?? '' }}</td>
                <td>{{ $collection->academic_year->academic_year_name ?? '' }}</td>
                <td>{{ $collection->semester->semester_name ?? '' }}</td>
                <td>{{ $collection->payment_method->payment_method_name ?? '' }}</td>
                <td class="text-right">৳{{ number_format($collection->total_amount, 2) }}</td>
                <td>{{ \Carbon\Carbon::parse($collection->date)->format('d-m-Y') }}</td>
                @if($userRole === 'Super Admin')
                <td>{{ $collection->user->name ?? '' }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="{{ $userRole === 'Super Admin' ? '6' : '5' }}" class="text-right">Total:</th>
                <th class="text-right">৳{{ number_format($totalFeeCollection, 2) }}</th>
                <th></th>
                @if($userRole === 'Super Admin')
                <th></th>
                @endif
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">No fee collections found for the selected criteria.</div>
    @endif

    <div class="section-title">Expenses</div>
    @if($expenses->count() > 0)
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Expense Head</th>
                <th>Date</th>
                <th>Remarks</th>
                <th class="text-right">Amount</th>
                @if($userRole === 'Super Admin')
                <th>Created By</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($expenses as $index => $expense)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $expense->expenseHead->name ?? '' }}</td>
                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d-m-Y') }}</td>
                <td>{{ $expense->remarks }}</td>
                <td class="text-right">৳{{ number_format($expense->amount, 2) }}</td>
                @if($userRole === 'Super Admin')
                <td>{{ $expense->user->name ?? '' }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="{{ $userRole === 'Super Admin' ? '4' : '3' }}" class="text-right">Total:</th>
                <th class="text-right">৳{{ number_format($totalExpenses, 2) }}</th>
                @if($userRole === 'Super Admin')
                <th></th>
                @endif
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">No expenses found for the selected criteria.</div>
    @endif

    <div class="footer">
        <p>This report was generated on {{ \Carbon\Carbon::now()->format('d-m-Y H:i:s') }}</p>
        <p>{{ $appSetting->institute_name ?? 'Educational Institute' }} - My Collection Report</p>
    </div>
</body>
</html>
