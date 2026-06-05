<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AssignedCoursesExport implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @param  Collection<int, \App\Models\CourseAssignment>  $assignments
     */
    public function __construct(
        protected Collection $assignments,
        protected string $sheetTitle = 'Assigned Courses',
    ) {}

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Serial No.',
            'Academic Session',
            'Program',
            'Course Code',
            'Course Title',
            'Batch',
            'Section',
            'Credit Hours',
            'Total Students',
            'Course Status',
        ];
    }

    public function collection(): Collection
    {
        return $this->assignments->values()->map(function ($assignment, int $index) {
            $sectionLabel = trim(
                ($assignment->section?->section_code ?? '').' '.($assignment->section?->section_name ?? '')
            );

            return [
                $index + 1,
                trim(($assignment->academicSession?->session_name ?? '').' '.($assignment->academicSession?->academic_year ?? '')),
                $assignment->program?->program_name ?? '',
                $assignment->course?->course_code ?? '',
                $assignment->course?->course_title ?? '',
                implode(', ', $assignment->batch_labels ?? []),
                $sectionLabel,
                $assignment->credit_hours ?? $assignment->course?->credit ?? '',
                (int) ($assignment->total_students ?? 0),
                $assignment->course_status ?? '',
            ];
        });
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
