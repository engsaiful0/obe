<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentMarksTemplateExport implements FromCollection, WithHeadings
{
    /**
     * @param  array<int, string>  $headings
     * @param  Collection<int, array<int, string|float|int>>  $rows
     */
    public function __construct(
        protected array $headings,
        protected Collection $rows,
    ) {}

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return $this->headings;
    }

    public function collection(): Collection
    {
        return $this->rows;
    }
}
