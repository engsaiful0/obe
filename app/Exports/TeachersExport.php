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
            'Employee ID',
            'Department',
            'Designation',
            'Email',
            'Phone',
        ];
    }

    public function map($teacher): array
    {
        return [
            $teacher->id,
            $teacher->teacher_name,
            $teacher->employee_id ?? 'N/A',
            $teacher->department->name ?? 'N/A',
            $teacher->designation->designation_name ?? 'N/A',
            $teacher->email ?? 'N/A',
            $teacher->phone ?? 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
