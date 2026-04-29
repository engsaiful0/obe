<!DOCTYPE html>
<html>
<head>
    <title>Helper List - Print</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            .page-break { page-break-after: always; }
        }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
        }
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left;
        }
        .table th { 
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .header { 
            margin-bottom: 20px; 
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header img { width: 100px; }
        .header h1 { margin: 5px 0; font-size: 24px; }
        .header p { margin: 3px 0; color: #666; }
        .total { font-weight: bold; }
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
        .print-actions {
            margin-bottom: 20px;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="print-actions no-print">
        <button onclick="window.print()" class="btn btn-primary">Print</button>
        <button onclick="window.close()" class="btn btn-secondary">Close</button>
    </div>

    <div class="header">
        @if($appSetting && $appSetting->logo)
            <img src="{{ asset('profile_pictures/' . $appSetting->logo) }}" alt="logo">
        @endif
        <h1>{{ $appSetting->name ?? 'Helper List' }}</h1>
        <p>{{ $appSetting->address ?? '' }}</p>
        <p>Generated on: {{ date('F d, Y H:i:s') }}</p>
    </div>

    <h4>Helper List</h4>
    <div class="summary">
        <p><strong>Total Helpers:</strong> {{ $totalCount }}</p>
        <p><strong>Total Salary:</strong> ৳{{ number_format($totalSalary, 2) }}</p>
        <p><strong>Average Salary:</strong> ৳{{ number_format($avgSalary, 2) }}</p>
    </div>

    @if(request()->hasAny(['search', 'gender_id', 'status_id', 'employee_type_id', 'assigned_bus_id', 'experience_filter', 'min_salary', 'max_salary', 'from_date', 'to_date']))
    <div style="margin: 15px 0; padding: 10px; background-color: #f0f0f0; border-left: 4px solid #007bff;">
        <strong>Applied Filters:</strong>
        <ul style="margin: 5px 0; padding-left: 20px;">
            @if(request('search'))
                <li>Search: {{ request('search') }}</li>
            @endif
            @if(request('gender_id'))
                <li>Gender: {{ \App\Models\Gender::find(request('gender_id'))->gender_name ?? 'N/A' }}</li>
            @endif
            @if(request('status_id'))
                <li>Status: {{ \App\Models\Status::find(request('status_id'))->status_name ?? 'N/A' }}</li>
            @endif
            @if(request('employee_type_id'))
                <li>Employee Type: {{ \App\Models\EmployeeType::find(request('employee_type_id'))->employee_type_name ?? 'N/A' }}</li>
            @endif
            @if(request('assigned_bus_id'))
                <li>Assigned Bus: {{ \App\Models\Bus::find(request('assigned_bus_id'))->bus_number ?? 'N/A' }}</li>
            @endif
            @if(request('experience_filter'))
                <li>Experience: {{ ucfirst(request('experience_filter')) }}</li>
            @endif
            @if(request('min_salary') || request('max_salary'))
                <li>Salary Range: 
                    @if(request('min_salary')) ৳{{ number_format(request('min_salary'), 2) }} @endif
                    @if(request('min_salary') && request('max_salary')) - @endif
                    @if(request('max_salary')) ৳{{ number_format(request('max_salary'), 2) }} @endif
                </li>
            @endif
            @if(request('from_date') || request('to_date'))
                <li>Date Range: 
                    {{ request('from_date') ?? 'Start' }} to {{ request('to_date') ?? 'End' }}
                </li>
            @endif
        </ul>
    </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Helper ID</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>NID</th>
                <th>Gender</th>
                <th>Employee Type</th>
                <th>Experience</th>
                <th>Assigned Bus</th>
                <th>Basic Salary</th>
                <th>Gross Salary</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($busHelpers as $helper)
            <tr>
                <td>{{ $helper->bus_helper_id }}</td>
                <td>{{ $helper->bus_helper_name }}</td>
                <td>{{ $helper->mobile }}</td>
                <td>{{ $helper->nid_number }}</td>
                <td>{{ $helper->gender->gender_name ?? 'N/A' }}</td>
                <td>{{ $helper->employeeType->employee_type_name ?? 'N/A' }}</td>
                <td>{{ $helper->years_of_experience }} years</td>
                <td>
                    @if($helper->assignedBus)
                        {{ $helper->assignedBus->bus_number }}
                    @else
                        Not Assigned
                    @endif
                </td>
                <td class="text-end">৳{{ number_format($helper->basic_salary, 2) }}</td>
                <td class="text-end">৳{{ number_format($helper->gross_salary, 2) }}</td>
                <td>{{ $helper->status->status_name ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">No helpers found.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" class="total text-end">Total:</td>
                <td class="total text-end">৳{{ number_format($busHelpers->sum('basic_salary'), 2) }}</td>
                <td class="total text-end">৳{{ number_format($totalSalary, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
