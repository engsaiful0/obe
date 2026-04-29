<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $students;

    public function __construct($students)
    {
        $this->students = $students;
    }

    public function collection()
    {
        return $this->students;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Student Name (English)',
            'Student Name (Bangla)',
            'Student ID',
            'Father Name (English)',
            'Father Name (Bangla)',
            'Mother Name (English)',
            'Mother Name (Bangla)',
            'Personal Number',
            'Email',
            'Guardian Phone',
            'Date of Birth',
            'Academic Year',
            'Semester',
            'Technology',
            'Shift',
            'Present Address',
            'Permanent Address',
            'SSC Institute',
            'SSC Roll Number',
            'SSC Registration Number',
            'SSC GPA',
            'Status',
            'Status Change Reason',
            'Status Changed At'
        ];
    }

    public function map($student): array
    {
        return [
            $student->id,
            $student->full_name_in_english_block_letter ?? 'N/A',
            $student->full_name_in_banglai ?? 'N/A',
            $student->student_unique_id ?? 'N/A',
            $student->father_name_in_english_block_letter ?? 'N/A',
            $student->father_name_in_banglai ?? 'N/A',
            $student->mother_name_in_english_block_letter ?? 'N/A',
            $student->mother_name_in_banglai ?? 'N/A',
            $student->personal_number ?? 'N/A',
            $student->email ?? 'N/A',
            $student->guardian_phone ?? 'N/A',
            $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') : 'N/A',
            $student->academicYear->academic_year_name ?? 'N/A',
            $student->semester->semester_name ?? 'N/A',
            $student->technology->technology_name ?? 'N/A',
            $student->shift->shift_name ?? 'N/A',
            $student->present_address ?? 'N/A',
            $student->permanent_address ?? 'N/A',
            $student->ssc_or_equivalent_institute_name ?? 'N/A',
            $student->ssc_or_equivalent_roll_number ?? 'N/A',
            $student->ssc_or_equivalent_registration_number ?? 'N/A',
            $student->ssc_or_equivalent_gpa ?? 'N/A',
            $student->status_label ?? 'N/A',
            $student->status_change_reason ?? 'N/A',
            $student->status_changed_at ? \Carbon\Carbon::parse($student->status_changed_at)->format('Y-m-d H:i:s') : 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
