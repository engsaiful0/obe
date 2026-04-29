<?php

namespace App\Exports;

use App\Models\BusHelper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BusHelperExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $busHelpers;

    public function __construct($busHelpers)
    {
        $this->busHelpers = $busHelpers;
    }

    public function collection()
    {
        return $this->busHelpers;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Helper ID',
            'Name',
            'Father Name',
            'Mother Name',
            'Mobile',
            'NID Number',
            'Gender',
            'Marital Status',
            'Religion',
            'Present Address',
            'Permanent Address',
            'Academic Qualification',
            'Years of Experience',
            'Employee Type',
            'Assigned Bus',
            'Basic Salary',
            'House Rent',
            'Medical Allowance',
            'Other Allowance',
            'Gross Salary',
            'Status',
            'Created At',
            'Updated At'
        ];
    }

    public function map($busHelper): array
    {
        return [
            $busHelper->id,
            $busHelper->bus_helper_id,
            $busHelper->bus_helper_name,
            $busHelper->father_name,
            $busHelper->mother_name,
            $busHelper->mobile,
            $busHelper->nid_number,
            $busHelper->gender->gender_name ?? 'N/A',
            $busHelper->maritalStatus->marital_status_name ?? 'N/A',
            $busHelper->religion->religion_name ?? 'N/A',
            $busHelper->present_address,
            $busHelper->permanent_address,
            $busHelper->academic_qualification,
            $busHelper->years_of_experience,
            $busHelper->employeeType->employee_type_name ?? 'N/A',
            $busHelper->assignedBus ? ($busHelper->assignedBus->bus_number . ' - ' . $busHelper->assignedBus->model_name) : 'N/A',
            number_format($busHelper->basic_salary, 2),
            number_format($busHelper->house_rent, 2),
            number_format($busHelper->medical_allowance, 2),
            number_format($busHelper->other_allowance, 2),
            number_format($busHelper->gross_salary, 2),
            $busHelper->status->status_name ?? 'N/A',
            $busHelper->created_at ? \Carbon\Carbon::parse($busHelper->created_at)->format('Y-m-d H:i:s') : 'N/A',
            $busHelper->updated_at ? \Carbon\Carbon::parse($busHelper->updated_at)->format('Y-m-d H:i:s') : 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

