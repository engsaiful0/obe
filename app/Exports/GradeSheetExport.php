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
     * @param  array<int, array{key: string, label: string}>  $groupedColumns
     */
    public function __construct(
        protected array $rows,
        protected array $groupedColumns = [],
        protected string $sheetTitle = 'Grade Sheet',
    ) {}

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        $headings = ['Serial No.', 'Student Name', 'Student Code'];
        foreach ($this->groupedColumns as $group) {
            $headings[] = $group['label'];
        }

        return array_merge($headings, ['Total', '%', 'Grade', 'GPA']);
    }

    public function collection(): Collection
    {
        return collect($this->rows)->map(function (array $row) {
            $line = [
                $row['serial'] ?? '',
                $row['student_name'] ?? '',
                $row['student_code'] ?? '',
            ];
            foreach ($this->groupedColumns as $group) {
                $line[] = $row[$group['key']] ?? 0;
            }

            return array_merge($line, [
                $row['total_marks'] ?? 0,
                $row['total_marks_percentage'] ?? 0,
                $row['total_marks_grade_name'] ?? '',
                $row['total_marks_grade_points'] ?? '',
            ]);
        });
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
