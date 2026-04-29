<?php

namespace App\Exports;

use App\Models\Income;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $incomes;

    public function __construct($incomes)
    {
        $this->incomes = $incomes;
    }

    public function collection()
    {
        return $this->incomes;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Income Head',
            'Income Date',
            'Amount',
            'Concerned Employee',
            'Employee ID',
            'Remarks',
            'Created At',
            'Updated At'
        ];
    }

    public function map($income): array
    {
        return [
            $income->id,
            $income->incomeHead->name ?? 'N/A',
            $income->income_date ? \Carbon\Carbon::parse($income->income_date)->format('Y-m-d') : 'N/A',
            number_format($income->amount, 2),
            $income->employee ? ($income->employee->employee_name . ' (' . $income->employee->employee_unique_id . ')') : 'N/A',
            $income->employee ? $income->employee->employee_unique_id : 'N/A',
            $income->remarks ?? 'N/A',
            $income->created_at ? \Carbon\Carbon::parse($income->created_at)->format('Y-m-d H:i:s') : 'N/A',
            $income->updated_at ? \Carbon\Carbon::parse($income->updated_at)->format('Y-m-d H:i:s') : 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

