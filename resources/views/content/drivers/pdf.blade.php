<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Details - {{ $driver->full_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #333;
            padding: 10px;
        }
        
        .driver-wrapper {
            background: white;
            border: 1px solid #ddd;
        }
        
        .driver-header {
            background: #667eea;
            color: white;
            padding: 10px;
            text-align: center;
        }
        
        .driver-header h2 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .driver-header p {
            font-size: 9px;
            margin-bottom: 6px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }
        
        .status-active {
            background: #28a745;
            color: white;
        }
        
        .status-inactive {
            background: #6c757d;
            color: white;
        }
        
        .status-suspended {
            background: #dc3545;
            color: white;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        .info-table tr {
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-table tr:last-child {
            border-bottom: none;
        }
        
        .info-table td {
            padding: 0.4rem 0.5rem;
            vertical-align: top;
        }
        
        .info-table td:first-child {
            width: 30%;
            font-weight: bold;
            color: #495057;
            background: #f8f9fa;
            border-right: 1px solid #e9ecef;
            font-size: 8px;
        }
        
        .info-table td:last-child {
            width: 70%;
            color: #212529;
            font-size: 8px;
        }
        
        .info-table .highlight-value {
            font-size: 11px;
            font-weight: bold;
            color: #667eea;
        }
        
        .info-table .address-cell {
            padding: 0.6rem;
            line-height: 1.5;
            color: #495057;
            background: #f8f9fa;
            font-size: 8px;
        }
        
        .info-table .status-row {
            background: rgba(102, 126, 234, 0.15) !important;
            border-top: 2px solid #667eea;
            border-bottom: 2px solid #667eea;
        }
        
        .info-table .status-row td:first-child {
            background: rgba(102, 126, 234, 0.2) !important;
            font-weight: bold;
            color: #667eea;
            font-size: 9px;
        }
        
        .info-table .status-row td:last-child {
            font-weight: bold;
        }
        
        .info-table .status-cell {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-table .status-cell.status-active {
            background: #28a745;
            color: white;
        }
        
        .info-table .status-cell.status-inactive {
            background: #6c757d;
            color: white;
        }
        
        .info-table .status-cell.status-suspended {
            background: #dc3545;
            color: white;
        }
        
        .photo-container {
            display: inline-block;
            border: 1px solid #ddd;
            padding: 2px;
        }
        
        .photo-container img {
            max-width: 60px;
            max-height: 60px;
            display: block;
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 7px;
        }
    </style>
    <style>
    @font-face {
        font-family: 'Kalpurush';
        src: url("{{ storage_path('fonts/kalpurush.ttf') }}") format('truetype');
    }

    @font-face {
        font-family: 'Nikosh';
        src: url("{{ storage_path('fonts/nikoshban.ttf') }}") format('truetype');
    }

    @font-face {
        font-family: 'SolaimanLipi';
        src: url("{{ storage_path('fonts/solaimanlipi.ttf') }}") format('truetype');
    }
    @font-face {
        font-family: 'Kalpurush';
        src: url("{{ storage_path('fonts/kalpurush.ttf') }}") format('truetype');
        font-weight: normal;
        font-style: normal;
    }
    body {
        font-family: 'Kalpurush', DejaVu Sans, sans-serif;
    }
</style>
</head>
<body>
    <div class="driver-wrapper">
        <!-- Header -->
        <div class="driver-header">
            <h2>{{ $driver->full_name }}</h2>
            <p>Driver ID: {{ $driver->driver_unique_id ?? 'N/A' }}</p>
            @php
                $statusClass = 'status-inactive';
                $statusText = 'Inactive';
                if ($driver->status == 'active' || ($driver->driverStatus && str_contains(strtolower($driver->driverStatus->status_name), 'active'))) {
                    $statusClass = 'status-active';
                    $statusText = 'Active';
                } elseif ($driver->status == 'suspended') {
                    $statusClass = 'status-suspended';
                    $statusText = 'Suspended';
                }
            @endphp
            <div class="status-badge {{ $statusClass }}">{{ $statusText }}</div>
        </div>

        <!-- Information Table -->
        <table class="table table-bordered table-striped table-hover">
            <!-- Personal Information -->
            <tr class="status-row">

                <td>Full Name</td>
                <td><strong>{{ $driver->full_name }}</strong></td>

                <td>Driver Unique ID</td>
                <td><span class="highlight-value">{{ $driver->driver_unique_id ?? 'N/A' }}</span></td>

                <td>Status</td>
                <td>
                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                </td>
            </tr>

            <tr>
                <td>Father's Name</td>
                <td>{{ $driver->father_name ?? 'N/A' }}</td>
           
                <td>Date of Birth</td>
                <td>{{ $driver->date_of_birth ? $driver->date_of_birth->format('F d, Y') : 'N/A' }}</td>
          
                <td>National ID / Passport</td>
                <td>{{ $driver->national_id_passport ?? 'N/A' }}</td>
            </tr>

            <tr>
                <td>Photo</td>
                <td>
                    @if($driver->photo)
                    <div class="photo-container">
                        <img src="{{ asset('storage/' . $driver->photo) }}"
                            alt="{{ $driver->full_name }}"
                            width="120"
                            height="120"
                            style="object-fit: cover;">
                    </div>
                    @else
                    <span class="text-muted">No photo uploaded</span>
                    @endif
                </td>
           
                <td>Contact Number</td>
                <td>
                    <a href="tel:{{ $driver->contact_number }}">{{ $driver->contact_number }}</a>
                </td>
          
                <td>Alternative Contact</td>
                <td>
                    @if($driver->alternative_contact_number)
                    <a href="tel:{{ $driver->alternative_contact_number }}">{{ $driver->alternative_contact_number }}</a>
                    @else
                    N/A
                    @endif
                </td>
            </tr>

            <tr>
                <td>Email Address</td>
                <td>
                    @if($driver->email)
                    <a href="mailto:{{ $driver->email }}">{{ $driver->email }}</a>
                    @else
                    N/A
                    @endif
                </td>
           
                <td>Permanent Address</td>
                <td class="address-cell">{{ $driver->permanent_address ?? 'N/A' }}</td>
          
                <td>Present Address</td>
                <td class="address-cell">{{ $driver->present_address ?? 'N/A' }}</td>
            </tr>

            <tr>
                <td>Religion</td>
                <td>{{ $driver->religion->religion_name ?? 'N/A' }}</td>
           
                <td>Educational Qualification</td>
                <td>{{ $driver->educationalQualification->qualification_name ?? 'N/A' }}</td>
          
                <td>Marital Status</td>
                <td>{{ $driver->maritalStatus->marital_status_name ?? 'N/A' }}</td>
            </tr>

            <!-- License Information -->
            <tr>
                <td>License Number</td>
                <td><strong>{{ $driver->license_number ?? 'N/A' }}</strong></td>
           
                <td>License Type</td>
                <td>{{ $driver->licenseType->license_type_name ?? 'N/A' }}</td>
          
                <td>Issuing Authority</td>
                <td>{{ $driver->issuingAuthority->authority_name ?? ($driver->issuing_authority ?? 'N/A') }}</td>
            </tr>

            <tr>
                <td>License Issue Date</td>
                <td>{{ $driver->license_issue_date ? $driver->license_issue_date->format('F d, Y') : 'N/A' }}</td>
           
                <td>License Expiry Date</td>
                <td>
                    @if($driver->license_expiry_date)
                    {{ $driver->license_expiry_date->format('F d, Y') }}
                    @if($driver->isLicenseExpired())
                    <span class="badge bg-danger ms-2">Expired</span>
                    @elseif($driver->license_expiry_date->diffInDays(now()) <= 30)
                        <span class="badge bg-warning ms-2">Expires Soon</span>
                        @endif
                        @else
                        N/A
                        @endif
                </td>
           
                <td>Driving Experience</td>
                <td>
                    @if($driver->experienceYear)
                    <span class="highlight-value">{{ $driver->experienceYear->experience_year }}</span>
                    @elseif($driver->driving_experience)
                    <span class="highlight-value">{{ $driver->driving_experience }} {{ $driver->driving_experience == 1 ? 'Year' : 'Years' }}</span>
                    @else
                    N/A
                    @endif
                </td>
            </tr>

            <!-- Employment Details -->
            <tr>
                <td>Joining Date</td>
                <td>{{ $driver->joining_date ? $driver->joining_date->format('F d, Y') : 'N/A' }}</td>
         
                <td>Driver Type</td>
                <td>{{ $driver->driverType->driver_type_name ?? 'N/A' }}</td>
            </tr>
            @if($driver->driverType->driver_type_name == 'Daily')
            <tr>
                <td>Daily Salary</td>
                <td>{{ $driver->daily_salary ? '৳ ' . number_format($driver->daily_salary, 2) : 'N/A' }}</td>
            </tr>
            @elseif($driver->driverType->driver_type_name == 'Contractual')
            <tr>
                <td>Basic Salary</td>
                <td>{{ $driver->basic_salary ? '৳ ' . number_format($driver->basic_salary, 2) : 'N/A' }}</td>
           
                <td>Food Allowance</td>
                <td>{{ $driver->food_allowance ? '৳ ' . number_format($driver->food_allowance, 2) : 'N/A' }}</td>
            </tr>
            @else
            <tr>
                <td>Basic Salary</td>
                <td>{{ $driver->basic_salary ? '৳ ' . number_format($driver->basic_salary, 2) : 'N/A' }}</td>
           
                <td>House Rent</td>
                <td>{{ $driver->house_rent ? '৳ ' . number_format($driver->house_rent, 2) : 'N/A' }}</td>
            
                <td>Medical Allowance</td>
                <td>{{ $driver->medical_allowance ? '৳ ' . number_format($driver->medical_allowance, 2) : 'N/A' }}</td>
            </tr>
            <tr>
                <td>Other Allowance</td>
                <td>{{ $driver->other_allowance ? '৳ ' . number_format($driver->other_allowance, 2) : 'N/A' }}</td>
          
                <td>Gross Salary</td>
                <td><span class="highlight-value">{{ $driver->gross_salary ? '৳ ' . number_format($driver->gross_salary, 2) : 'N/A' }}</span></td>
            </tr>
            @endif
            <tr>
                <td>Bank Account Number</td>
                <td>{{ $driver->bank_account_number ?? 'N/A' }}</td>
           
                <td>Emergency Contact Person</td>
                <td>{{ $driver->emergency_contact_person ?? 'N/A' }}</td>
           
                <td>Emergency Contact Number</td>
                <td>
                    @if($driver->emergency_contact_number)
                    <a href="tel:{{ $driver->emergency_contact_number }}">{{ $driver->emergency_contact_number }}</a>
                    @else
                    N/A
                    @endif
                </td>
            </tr>

            <tr>
                <td>Reference Name</td>
                <td>{{ $driver->reference_name ?? 'N/A' }}</td>
          
                <td>Reference Contact Number</td>
                <td>
                    @if($driver->reference_contact_number)
                    <a href="tel:{{ $driver->reference_contact_number }}">{{ $driver->reference_contact_number }}</a>
                    @else
                    N/A
                    @endif
                </td>
           
                <td>Created At</td>
                <td>{{ $driver->created_at ? $driver->created_at->format('F d, Y h:i A') : 'N/A' }}</td>
            </tr>

          
        </table>
        <div class="footer">
            <p>Generated on {{ now()->format('F d, Y h:i A') }} | Transportation Management System</p>
        </div>
    </div>
</body>
</html>




