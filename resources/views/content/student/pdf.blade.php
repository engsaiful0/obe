<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Details - {{ $employee->employee_name }}</title>
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
        
        .employee-wrapper {
            background: white;
            border: 1px solid #ddd;
        }
        
        .employee-header {
            background: #667eea;
            color: white;
            padding: 10px;
            text-align: center;
        }
        
        .employee-header h2 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .employee-header p {
            font-size: 9px;
            margin-bottom: 6px;
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
            padding: 6px 8px;
            vertical-align: top;
        }
        
        .info-table td:first-child {
            width: 30%;
            font-weight: 600;
            color: #495057;
            background: #f8f9fa;
            border-right: 1px solid #e9ecef;
            font-size: 8px;
        }
        
        .info-table td:last-child {
            width: 70%;
            color: #212529;
            font-size: 9px;
        }
        
        .info-table .highlight-value {
            font-size: 11px;
            font-weight: 700;
            color: #667eea;
        }
        
        .info-table .address-cell {
            padding: 6px;
            line-height: 1.5;
            color: #495057;
            background: #f8f9fa;
            font-size: 8px;
        }
        
        .section-header {
            background: #667eea;
            color: white;
            font-weight: 700;
            text-align: center;
            padding: 6px;
            font-size: 10px;
        }
        
        .photo-container {
            display: inline-block;
            border: 1px solid #ddd;
            padding: 3px;
            background: white;
        }
        
        .photo-container img {
            max-width: 60px;
            max-height: 60px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="employee-wrapper">
        <!-- Header -->
        <div class="employee-header">
            <h2>{{ $employee->employee_name }}</h2>
            <p>Employee ID: {{ $employee->employee_unique_id ?? 'N/A' }}</p>
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
                <td>{{ $employee->mobile ?? 'N/A' }}</td>
            </tr>
            
            <tr>
                <td>Email Address</td>
                <td>{{ $employee->email ?? 'N/A' }}</td>
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
                    @if($employee->picture && file_exists(storage_path('app/public/' . $employee->picture)))
                        <div class="photo-container">
                            <img src="{{ storage_path('app/public/' . $employee->picture) }}" alt="{{ $employee->employee_name }}">
                        </div>
                    @else
                        <span>No photo</span>
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
                <td colspan="2" class="section-header">EDUCATION DETAILS</td>
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
                <td colspan="2" class="section-header">EMPLOYMENT DETAILS</td>
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
</body>
</html>

