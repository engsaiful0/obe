<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentFeeReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $report;

    public function __construct($report)
    {
        $this->report = $report;
    }

    public function array(): array
    {
        $data = [];
        
        // Student Information
        $student = $this->report['student'];
        $feeSummary = $this->report['fee_summary'];
        
        $data[] = [
            'Student ID' => $student->student_unique_id,
            'Student Name' => $student->full_name_in_english_block_letter,
            'Father Name' => $student->father_name_in_english_block_letter,
            'Academic Year' => $student->academicYear->academic_year_name ?? 'N/A',
            'Technology' => $student->technology->technology_name ?? 'N/A',
        ];
        
        $data[] = []; // Empty row
        
        // Fee Summary
        $data[] = [
            'Fee Type',
            'Total Amount',
            'Paid Amount',
            'Due Amount',
            'Status'
        ];
        
        $data[] = [
            'Semester Fees',
            number_format($feeSummary->total_semester_fees, 2),
            number_format($feeSummary->paid_semester_fees, 2),
            number_format($feeSummary->total_semester_fees - $feeSummary->paid_semester_fees, 2),
            $feeSummary->all_semester_fees_paid ? 'Complete' : 'Incomplete'
        ];
        
        $data[] = [
            'Monthly Fees',
            number_format($feeSummary->total_monthly_fees, 2),
            number_format($feeSummary->paid_monthly_fees, 2),
            number_format($feeSummary->total_monthly_fees - $feeSummary->paid_monthly_fees, 2),
            $feeSummary->all_monthly_fees_paid ? 'Complete' : 'Incomplete'
        ];
        
        $data[] = [
            'Total Fees',
            number_format($feeSummary->total_fees, 2),
            number_format($feeSummary->total_paid, 2),
            number_format($feeSummary->total_due, 2),
            $feeSummary->all_fees_paid ? 'Complete' : 'Incomplete'
        ];
        
        $data[] = []; // Empty row
        
        // Semester Fees Details
        $data[] = ['SEMESTER FEES DETAILS'];
        $data[] = ['Semester', 'Amount', 'Payment Date', 'Status'];
        
        foreach ($this->report['semester_fees'] as $semesterFee) {
            $data[] = [
                $semesterFee->semester->semester_name ?? 'N/A',
                number_format($semesterFee->amount, 2),
                $semesterFee->payment_date->format('Y-m-d'),
                $semesterFee->is_paid ? 'Paid' : 'Unpaid'
            ];
        }
        
        $data[] = []; // Empty row
        
        // Monthly Fees Details
        $data[] = ['MONTHLY FEES DETAILS'];
        $data[] = ['Month', 'Amount', 'Payment Date', 'Status'];
        
        foreach ($this->report['monthly_fees'] as $monthlyFee) {
            $data[] = [
                $monthlyFee->month->month_name ?? 'N/A',
                number_format($monthlyFee->amount, 2),
                $monthlyFee->payment_date->format('Y-m-d'),
                $monthlyFee->is_paid ? 'Paid' : 'Unpaid'
            ];
        }
        
        $data[] = []; // Empty row
        
        // Fee Collections
        $data[] = ['FEE COLLECTIONS'];
        $data[] = ['Date', 'Amount', 'Payment Method', 'Collected By'];
        
        foreach ($this->report['fee_collections'] as $collection) {
            $data[] = [
                $collection->date,
                number_format($collection->total_amount, 2),
                $collection->paymentMethod->payment_method_name ?? 'N/A',
                $collection->user->name ?? 'N/A'
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
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 20,
            'D' => 15,
            'E' => 15,
        ];
    }

    public function title(): string
    {
        return 'Student Fee Report';
    }
}
