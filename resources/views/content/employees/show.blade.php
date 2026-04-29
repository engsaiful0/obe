@extends('layouts/layoutMaster')

@section('title', 'Employee Details')

@section('page-style')
<style>
    .employee-wrapper {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }
    
    .employee-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .employee-header-left h2 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    
    .employee-header-left p {
        margin: 0.5rem 0 0 0;
        opacity: 0.95;
        font-size: 0.95rem;
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
        
        .employee-wrapper {
            box-shadow: none;
            border: 1px solid #333;
        }
        
        .employee-header {
            background: #667eea !important;
            color: white !important;
            padding: 0.6rem;
        }
        
        .employee-header-left h2 {
            font-size: 14px;
        }
        
        .employee-header-left p {
            font-size: 9px;
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
        <h4 class="mb-0">Employee Details</h4>
        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="ti ti-printer me-1"></i> Print
            </button>
            <a href="{{ route('employees.pdf', $employee->id) }}" class="btn btn-danger" target="_blank">
                <i class="ti ti-file-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-warning">
                <i class="ti ti-pencil me-1"></i> Edit
            </a>
            <a href="{{ route('employees.view-employee') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="employee-wrapper">
        <!-- Header -->
        <div class="employee-header">
            <div class="employee-header-left">
                <h2>{{ $employee->employee_name }}</h2>
                <p>Employee ID: {{ $employee->employee_unique_id ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Information Table -->
        <table class="info-table">
            <tr>
                <td>Employee Name</td>
                <td><strong>{{ $employee->employee_name }}</strong></td>
            </tr>
            
            <tr>
                <td>Employee Unique ID</td>
                <td><span class="highlight-value">{{ $employee->employee_unique_id ?? 'N/A' }}</span></td>
            </tr>
            
            <tr>
                <td>Gender</td>
                <td>{{ ucfirst($employee->gender ?? 'N/A') }}</td>
            </tr>
            
            <tr>
                <td>Father's Name</td>
                <td>{{ $employee->father_name ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Mother's Name</td>
                <td>{{ $employee->mother_name ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Mobile</td>
                <td>
                    <a href="tel:{{ $employee->mobile }}">{{ $employee->mobile }}</a>
                </td>
            </tr>
            
            <tr>
                <td>Email Address</td>
                <td>
                    @if($employee->email)
                        <a href="mailto:{{ $employee->email }}">{{ $employee->email }}</a>
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            
            <tr>
                <td>NID</td>
                <td>{{ $employee->nid ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Religion</td>
                <td>{{ $employee->religion->religion_name ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Designation</td>
                <td>{{ $employee->designation->designation_name ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Employee Type</td>
                <td>{{ $employee->employeeType->employee_type_name ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Photo</td>
                <td>
                    @if($employee->picture)
                        <div class="photo-container">
                            <img src="{{ asset('storage/app/public/' . $employee->picture) }}" 
                                 alt="{{ $employee->employee_name }}" 
                                 width="120" 
                                 height="120"
                                 style="object-fit: cover;">
                        </div>
                    @else
                        <span class="text-muted">No photo uploaded</span>
                    @endif
                </td>
            </tr>
            
            <tr>
                <td>Present Address</td>
                <td class="address-cell">{{ $employee->present_address ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Permanent Address</td>
                <td class="address-cell">{{ $employee->permanent_address ?? 'N/A' }}</td>
            </tr>
            
            <!-- Education Section -->
            <tr>
                <td colspan="2" style="background: #667eea; color: white; font-weight: 700; text-align: center; padding: 0.75rem;">
                    EDUCATION DETAILS
                </td>
            </tr>
            
            <tr>
                <td>SSC or Equivalent Group</td>
                <td>{{ $employee->ssc_or_equivalent_group ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>SSC Result</td>
                <td>{{ $employee->ssc_result ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>HSC or Equivalent Group</td>
                <td>{{ $employee->hsc_or_equivalent_group ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>HSC Result</td>
                <td>{{ $employee->hsc_result ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Bachelor or Equivalent Group</td>
                <td>{{ $employee->bachelor_or_equivalent_group ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Bachelor Result</td>
                <td>{{ $employee->result ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Master or Equivalent Group</td>
                <td>{{ $employee->master_or_equivalent_group ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Master Result</td>
                <td>{{ $employee->masters_result ?? 'N/A' }}</td>
            </tr>
            
            <!-- Employment Details -->
            <tr>
                <td colspan="2" style="background: #667eea; color: white; font-weight: 700; text-align: center; padding: 0.75rem;">
                    EMPLOYMENT DETAILS
                </td>
            </tr>
            
            <tr>
                <td>Years of Experience</td>
                <td>{{ $employee->years_of_experience ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Date of Join</td>
                <td>{{ $employee->date_of_join ? \Carbon\Carbon::parse($employee->date_of_join)->format('F d, Y') : 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Basic Salary</td>
                <td>{{ $employee->basic_salary ? '৳ ' . number_format($employee->basic_salary, 2) : 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>House Rent</td>
                <td>{{ $employee->house_rent ? '৳ ' . number_format($employee->house_rent, 2) : 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Medical Allowance</td>
                <td>{{ $employee->medical_allowance ? '৳ ' . number_format($employee->medical_allowance, 2) : 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Other Allowance</td>
                <td>{{ $employee->other_allowance ? '৳ ' . number_format($employee->other_allowance, 2) : 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Gross Salary</td>
                <td><span class="highlight-value">{{ $employee->gross_salary ? '৳ ' . number_format($employee->gross_salary, 2) : 'N/A' }}</span></td>
            </tr>
            
            <tr>
                <td>Created At</td>
                <td>{{ $employee->created_at ? $employee->created_at->format('F d, Y h:i A') : 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Last Updated</td>
                <td>{{ $employee->updated_at ? $employee->updated_at->format('F d, Y h:i A') : 'N/A' }}</td>
            </tr>
        </table>
    </div>
</div>
@endsection

