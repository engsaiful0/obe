<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class GradeSheetExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(
        protected array $rows,
        protected string $sheetTitle = 'Grade Sheet',
    ) {}

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Student ID',
            'Student Code',
            'Student Name',
            'Total Marks',
            'Percentage',
            'Letter Grade',
            'Grade Point',
        ];
    }

    public function collection(): Collection
    {
        return collect($this->rows)->map(fn (array $row) => [
            $row['student_id'] ?? '',
            $row['student_code'] ?? '',
            $row['student_name'] ?? '',
            $row['total_marks'] ?? 0,
            $row['total_marks_percentage'] ?? 0,
            $row['total_marks_grade_name'] ?? '',
            $row['total_marks_grade_points'] ?? '',
        ]);
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
