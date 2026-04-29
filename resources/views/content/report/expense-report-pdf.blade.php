<!DOCTYPE html>
<html>
<head>
    <title>Expense Report</title>
    <style>
        body { font-family: sans-serif; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background-color: #f2f2f2; text-align: left; }
        .text-center { text-align: center; }
        .header { margin-bottom: 20px; }
        .header img { width: 100px; }
        .header h1 { margin: 0; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header text-center">
        @if($appSetting && $appSetting->logo)
            <img src="{{ public_path('profile_pictures/' . $appSetting->logo) }}" alt="logo">
        @endif
        <h1>{{ $appSetting->name ?? 'Expense Report' }}</h1>
        <p>{{ $appSetting->address ?? '' }}</p>
    </div>

    <h4>Expense Report</h4>
    <p>
        <strong>Date Range:</strong>
        @if(request('from_date') && request('to_date'))
            {{ request('from_date') }} to {{ request('to_date') }}
        @else
            All
        @endif
    </p>
    @if(request('expense_head_id'))
        <p>
            <strong>Expense Head:</strong>
            {{ \App\Models\ExpenseHead::find(request('expense_head_id'))->name ?? 'N/A' }}
        </p>
    @endif
    @if(request('vehicle_sub_type_id'))
        <p>
            <strong>Vehicle Sub Type:</strong>
            {{ \App\Models\VehicleSubType::find(request('vehicle_sub_type_id'))->sub_type_name ?? 'N/A' }}
        </p>
    @endif
    @if(request('vehicle_id'))
        <p>
            <strong>Vehicle:</strong>
            {{ \App\Models\Vehicle::find(request('vehicle_id'))->display_name ?? 'N/A' }}
        </p>
    @endif
    @if(request('employee_id'))
        <p>
            <strong>Employee:</strong>
            {{ \App\Models\Employee::find(request('employee_id'))->employee_name ?? 'N/A' }}
        </p>
    @endif


    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Memo No</th>
                <th>Bill No</th>
                <th>Supplier</th>
                <th>Expense Head</th>
                
                <th>Bus Number</th>
                
                <th>Date</th>
                
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($expenses as $expense)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $expense->memo_no ?? 'N/A' }}</td>
                <td>{{ $expense->bill_no ?? 'N/A' }}</td>
                <td>{{ $expense->supplier->supplier_name ?? 'N/A' }}</td>
                <td>{{ $expense->expenseHead->name ?? '' }}</td>
                <td>{{ $expense->bus ? $expense->bus->bus_number : '' }}</td>
                <td>{{ $expense->expense_date }}</td>
                <td>{{ number_format($expense->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No expenses found.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="total text-end">Total Amount:</td>
                <td class="total">{{ number_format($totalAmount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
