<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $expenses;

    public function __construct($expenses)
    {
        $this->expenses = $expenses;
    }

    public function collection()
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Expense Head',
            'Expense Date',
            'Amount',
            'Remarks',
            'Created At',
            'Updated At'
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->id,
            $expense->expenseHead->name ?? 'N/A',
            $expense->expense_date ? \Carbon\Carbon::parse($expense->expense_date)->format('Y-m-d') : 'N/A',
            number_format($expense->amount, 2),
            $expense->remarks ?? 'N/A',
            $expense->created_at ? \Carbon\Carbon::parse($expense->created_at)->format('Y-m-d H:i:s') : 'N/A',
            $expense->updated_at ? \Carbon\Carbon::parse($expense->updated_at)->format('Y-m-d H:i:s') : 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
