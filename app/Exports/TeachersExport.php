<?php

namespace App\Exports;

use App\Models\Teacher;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeachersExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $teachers;

    public function __construct($teachers)
    {
        $this->teachers = $teachers;
    }

    public function collection()
    {
        return $this->teachers;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Teacher Name',
            'Teacher ID',
            'Designation',
            'Email',
            'Mobile',
            'Gender',
            'Joining Date',
            'Basic Salary',
            'Gross Salary',
            'Present Address',
            'Permanent Address'
        ];
    }

    public function map($teacher): array
    {
        return [
            $teacher->id,
            $teacher->teacher_name,
            $teacher->teacher_unique_id ?? 'N/A',
            $teacher->designation->designation_name ?? 'N/A',
            $teacher->email ?? 'N/A',
            $teacher->mobile ?? 'N/A',
            $teacher->gender ?? 'N/A',
            $teacher->joining_date ? \Carbon\Carbon::parse($teacher->joining_date)->format('Y-m-d') : 'N/A',
            $teacher->basic_salary ?? 'N/A',
            $teacher->gross_salary ?? 'N/A',
            $teacher->present_address ?? 'N/A',
            $teacher->permanent_address ?? 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
