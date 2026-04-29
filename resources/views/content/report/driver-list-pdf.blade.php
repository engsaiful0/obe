<!DOCTYPE html>
<html>
<head>
    <title>Driver List</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 11px;
            line-height: 1.4;
        }
        .table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
        }
        .table th, .table td { 
            border: 1px solid #ddd; 
            padding: 6px; 
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
        .header img { width: 80px; }
        .header h1 { margin: 5px 0; font-size: 22px; }
        .header p { margin: 3px 0; color: #666; }
        .total { font-weight: bold; }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
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
        @if($appSetting && $appSetting->logo)
            <img src="{{ public_path('profile_pictures/' . $appSetting->logo) }}" alt="logo">
        @endif
        <h1>{{ $appSetting->name ?? 'Driver List' }}</h1>
        <p>{{ $appSetting->address ?? '' }}</p>
        <p>Generated on: {{ date('F d, Y H:i:s') }}</p>
    </div>

    <h4>Driver List</h4>
    <div class="summary">
        <p><strong>Total Drivers:</strong> {{ $totalCount }}</p>
        <p><strong>Total Salary:</strong> ৳{{ number_format($totalSalary, 2) }}</p>
        <p><strong>Average Salary:</strong> ৳{{ number_format($avgSalary, 2) }}</p>
    </div>

    @if(request()->hasAny(['search', 'driver_type_id', 'status_id', 'license_type_id', 'experience_year_id', 'min_salary', 'max_salary', 'from_date', 'to_date']))
    <div style="margin: 15px 0; padding: 10px; background-color: #f0f0f0; border-left: 4px solid #007bff;">
        <strong>Applied Filters:</strong>
        <ul style="margin: 5px 0; padding-left: 20px;">
            @if(request('search'))
                <li>Search: {{ request('search') }}</li>
            @endif
            @if(request('driver_type_id'))
                <li>Driver Type: {{ \App\Models\DriverType::find(request('driver_type_id'))->driver_type_name ?? 'N/A' }}</li>
            @endif
            @if(request('status_id'))
                <li>Status: {{ \App\Models\Status::find(request('status_id'))->status_name ?? 'N/A' }}</li>
            @endif
            @if(request('license_type_id'))
                <li>License Type: {{ \App\Models\LicenseType::find(request('license_type_id'))->items_name ?? 'N/A' }}</li>
            @endif
             @if(request('experience_year_id'))
                <li>Experience: {{ \App\Models\ExperienceYear::find(request('experience_year_id'))->items_name ?? 'N/A' }}</li>
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
                <th>Driver ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Driver Type</th>
                <th>License Type</th>
                <th>Experience</th>
                <th>Joining Date</th>
                <th>Salary</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($drivers as $driver)
            <tr>
                <td>{{ $driver->driver_unique_id }}</td>
                <td>{{ $driver->full_name }}</td>
                <td>{{ $driver->contact_number }}</td>
                <td>{{ $driver->driverType->driver_type_name ?? 'N/A' }}</td>
                <td>{{ $driver->licenseType->items_name ?? 'N/A' }}</td>
                <td>{{ $driver->experienceYear->items_name ?? 'N/A' }}</td>
                <td>{{ $driver->joining_date ? $driver->joining_date->format('d M, Y') : 'N/A' }}</td>
                <td class="text-end">৳{{ number_format($driver->gross_salary, 2) }}</td>
                <td>{{ $driver->status->status_name ?? ($driver->status ?? 'N/A') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No drivers found.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="total text-end">Total:</td>
                <td class="total text-end">৳{{ number_format($totalSalary, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the TMS System</p>
    </div>
</body>
</html>
