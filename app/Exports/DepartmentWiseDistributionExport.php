<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DepartmentWiseDistributionExport implements FromArray, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $statistics;
    protected $departmentDistribution;

    public function __construct($statistics, $departmentDistribution)
    {
        $this->statistics = $statistics;
        $this->departmentDistribution = $departmentDistribution;
    }

    public function array(): array
    {
        $data = [];
        
        // Department-wise distribution
        foreach ($this->departmentDistribution as $dept) {
            $data[] = [
                $dept['department_name'],
                $dept['total_students'],
                $dept['percentage'] . '%'
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Department/Technology',
            'Total Students',
            'Percentage'
        ];
    }

    public function title(): string
    {
        return 'Department-wise Distribution';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 15,
            'C' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add summary information
                $rowCount = $sheet->getHighestRow();
                $sheet->insertNewRowBefore($rowCount + 2, 3);
                
                $sheet->setCellValue('A' . ($rowCount + 2), 'SUMMARY');
                $sheet->setCellValue('A' . ($rowCount + 3), 'Total Students:');
                $sheet->setCellValue('B' . ($rowCount + 3), $this->statistics['total_students']);
                $sheet->setCellValue('A' . ($rowCount + 4), 'Total Departments:');
                $sheet->setCellValue('B' . ($rowCount + 4), $this->statistics['total_departments']);
                
                // Style the summary
                $sheet->getStyle('A' . ($rowCount + 2))->getFont()->setBold(true);
                $sheet->getStyle('A' . ($rowCount + 3) . ':B' . ($rowCount + 4))->getFont()->setBold(true);
            },
        ];
    }
}
