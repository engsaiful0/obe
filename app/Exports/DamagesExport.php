<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DamagesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $damages;

    public function __construct($damages)
    {
        $this->damages = $damages;
    }

    public function collection()
    {
        // Flatten the collection to include one row per damage item
        $flattened = collect();
        
        foreach ($this->damages as $damage) {
            foreach ($damage->damageItems as $damageItem) {
                $flattened->push((object)[
                    'date' => $damage->date,
                    'warehouse' => $damage->warehouse,
                    'item' => $damageItem->item,
                    'quantity' => $damageItem->quantity,
                    'reason' => $damageItem->reason,
                    'approximate' => $damageItem->approximate,
                    'remarks' => $damage->remarks,
                    'created_at' => $damage->created_at,
                ]);
            }
        }
        
        return $flattened;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Warehouse',
            'Item Name',
            'Quantity',
            'Reason',
            'Approximate Value',
            'Remarks',
            'Created At'
        ];
    }

    public function map($row): array
    {
        return [
            $row->date ? \Carbon\Carbon::parse($row->date)->format('Y-m-d') : 'N/A',
            $row->warehouse->warehouse_name ?? 'N/A',
            $row->item->item_name ?? 'N/A',
            number_format($row->quantity, 2),
            $row->reason ?? 'N/A',
            $row->approximate ? number_format($row->approximate, 2) : 'N/A',
            $row->remarks ?? 'N/A',
            $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i:s') : 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
