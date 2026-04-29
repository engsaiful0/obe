<?php

namespace App\Exports;

use App\Models\DailyBusList;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AllBusesListExport implements FromCollection, WithHeadings, WithMapping
{
    protected $dailyBusLists;

    public function __construct($dailyBusLists)
    {
        $this->dailyBusLists = $dailyBusLists;
    }

    public function collection()
    {
        return $this->dailyBusLists;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Bus',
            'Registration Number',
            'Sub Type',
            'Start Stoppage',
            'End Stoppage',
            'Start Time',
            'Driver',
            'Assistant',
            'Bus Type',
            'Remarks'
        ];
    }

    public function map($dailyBusList): array
    {
        return [
            $dailyBusList->list_date,
            $dailyBusList->bus->model_name ?? 'N/A',
            $dailyBusList->bus->registration_number ?? 'N/A',
            $dailyBusList->busSubType->sub_type_name ?? 'N/A',
            $dailyBusList->startStoppage->stoppage_name ?? 'N/A',
            $dailyBusList->endStoppage->stoppage_name ?? 'N/A',
            $dailyBusList->start_time,
            $dailyBusList->driver->full_name ?? 'N/A',
            $dailyBusList->assistant->assistant_name ?? 'N/A',
            ucfirst($dailyBusList->bus_type),
            $dailyBusList->remarks ?? ''
        ];
    }
}
