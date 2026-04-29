<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentFinalReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $completedStudents;

    public function __construct($completedStudents)
    {
        $this->completedStudents = $completedStudents;
    }

    public function array(): array
    {
        $data = [];
        
        // Header
        $data[] = ['STUDENT FINAL COMPLETION REPORT'];
        $data[] = ['Students who have completed all 8 semester fees and 48 monthly fees'];
        $data[] = [];
        
        // Summary
        $data[] = [
            'Total Students with Complete Fees: ' . $this->completedStudents->count(),
            'Report Generated: ' . date('Y-m-d H:i:s')
        ];
        $data[] = [];
        
        // Headers for student data
        $data[] = [
            'Student ID',
            'Student Name',
            'Father Name',
            'Technology',
            'Academic Year',
            'Total Fees',
            'Total Paid',
            'Semesters Completed',
            'Monthly Fees Completed',
            'Completion Date',
            'Status'
        ];
        
        foreach ($this->completedStudents as $summary) {
            $student = $summary->student;
            $data[] = [
                $student->student_unique_id ?? 'N/A',
                $student->full_name_in_english_block_letter,
                $student->father_name_in_english_block_letter ?? 'N/A',
                $student->technology->technology_name ?? 'N/A',
                $student->academicYear->academic_year_name ?? 'N/A',
                number_format($summary->total_fees, 2),
                number_format($summary->total_paid, 2),
                $summary->semesters_completed . '/8',
                $summary->months_completed . '/48',
                $summary->updated_at->format('Y-m-d'),
                'COMPLETED'
            ];
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '000000']]],
            2 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => '666666']]],
            4 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'color' => ['rgb' => 'E8F5E8']]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 30,
            'C' => 25,
            'D' => 20,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 20,
            'I' => 20,
            'J' => 15,
            'K' => 12,
        ];
    }

    public function title(): string
    {
        return 'Final Completion Report';
    }
}
