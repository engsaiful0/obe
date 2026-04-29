@extends('layouts/layoutMaster')

@section('title', 'Driver Details')

@section('page-style')
<style>
    .driver-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    .driver-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }

    .driver-header-left h2 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .driver-header-left p {
        margin: 0.5rem 0 0 0;
        opacity: 0.95;
        font-size: 0.95rem;
    }

    .status-badge {
        padding: 0.6rem 1.2rem;
        border-radius: 25px;
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
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
        transition: background-color 0.2s;
    }

    .info-table tr:hover {
        background-color: #f8f9fa;
    }

    .info-table tr:last-child {
        border-bottom: none;
    }

    .info-table td {
        padding: 1rem 1.25rem;
        vertical-align: top;
    }

    .info-table td:first-child {
        width: 30%;
        font-weight: 600;
        color: #495057;
        background: #f8f9fa;
        border-right: 2px solid #e9ecef;
        font-size: 0.9rem;
    }

    .info-table td:last-child {
        width: 70%;
        color: #212529;
        font-size: 0.95rem;
    }

    .info-table .highlight-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #667eea;
    }

    .info-table .address-cell {
        padding: 1.25rem;
        line-height: 1.8;
        color: #495057;
        background: #f8f9fa;
    }

    .info-table a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
    }

    .info-table a:hover {
        text-decoration: underline;
    }

    .photo-container {
        display: inline-block;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 0.5rem;
        background: white;
    }

    .photo-container img {
        border-radius: 4px;
        display: block;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    /* Print Styles */
    @media print {
        @page {
            size: A4;
            margin: 0.6cm;
        }

        * {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .no-print {
            display: none !important;
        }

        body {
            background: white;
            font-size: 9px;
        }

        .driver-wrapper {
            box-shadow: none;
            border: 1px solid #333;
        }

        .driver-header {
            background: #667eea !important;
            color: white !important;
            padding: 0.6rem;
        }

        .driver-header-left h2 {
            font-size: 14px;
        }

        .driver-header-left p {
            font-size: 9px;
        }

        .status-badge {
            padding: 0.3rem 0.8rem;
            font-size: 7px;
        }

        .info-table {
            border: 1px solid #333;
        }

        .info-table tr {
            border-bottom: 1px solid #ddd;
        }

        .info-table tr:hover {
            background-color: transparent;
        }

        .info-table td {
            padding: 0.35rem 0.5rem;
            font-size: 8px;
        }

        .info-table td:first-child {
            width: 30%;
            font-size: 7px;
            background: #f8f9fa !important;
        }

        .info-table td:last-child {
            font-size: 8px;
        }

        .info-table .highlight-value {
            font-size: 10px;
        }

        .info-table .address-cell {
            padding: 0.5rem;
            font-size: 7px;
            line-height: 1.4;
        }

        .info-table a {
            color: #333 !important;
            text-decoration: none !important;
        }

        .photo-container {
            max-width: 80px;
        }

        .photo-container img {
            max-width: 100%;
            height: auto;
        }
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h4 class="mb-0">Driver Details</h4>
        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="ti ti-printer me-1"></i> Print
            </button>
            <a href="{{ route('drivers.pdf', $driver->id) }}" class="btn btn-danger" target="_blank">
                <i class="ti ti-file-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('drivers.edit', $driver->id) }}" class="btn btn-warning">
                <i class="ti ti-pencil me-1"></i> Edit
            </a>
            <a href="{{ route('drivers.index') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="driver-wrapper">
        <!-- Header -->
        <div class="driver-header">
            <div class="driver-header-left">
                <h2>{{ $driver->full_name }}</h2>
                <p>Driver ID: {{ $driver->driver_unique_id ?? 'N/A' }}</p>
            </div>
            <div>
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
                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
            </div>
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
</div>
@endsection