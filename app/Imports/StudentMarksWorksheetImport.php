<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Holds the raw first worksheet as a laravel Collection of rows for manual parsing.
 */
final class StudentMarksWorksheetImport implements ToCollection
{
    public Collection $rows;

    public function __construct()
    {
        $this->rows = new Collection([]);
    }

    public function collection(Collection $collection): void
    {
        $this->rows = $collection;
    }
}
