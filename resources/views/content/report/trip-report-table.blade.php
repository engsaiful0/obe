@php
    use Carbon\Carbon;
@endphp

@if(isset($drivers) && isset($dates) && isset($pivotData))
<div class="table-responsive text-nowrap" style="overflow-x: auto;">
    <table class="table table-bordered table-hover table-striped">
        <thead>
            <tr>
                <th rowspan="2" class="align-middle text-center" style="min-width: 200px; position: sticky; left: 0; background-color: #f8f9fa; z-index: 10;">
                    <strong>Driver Name</strong>
                </th>
                @foreach($dates as $date)
                <th class="text-center" style="min-width: 100px;">
                    {{ Carbon::parse($date)->format('d/m/Y') }}
                </th>
                @endforeach
                <th class="text-center bg-primary text-white" style="min-width: 100px;">
                    <strong>Total</strong>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($drivers as $driver)
            <tr>
                <td style="position: sticky; left: 0; background-color: white; z-index: 5;">
                    <strong>{{ $driver->full_name }}</strong>
                    @if($driver->driver_unique_id)
                        <br><small class="text-muted">ID: {{ $driver->driver_unique_id }}</small>
                    @endif
                </td>
                @php
                    $driverTotal = 0;
                @endphp
                @foreach($dates as $date)
                    @php
                        $tripCount = $pivotData[$driver->id][$date] ?? 0;
                        $driverTotal += $tripCount;
                    @endphp
                    <td class="text-center">
                        @if($tripCount > 0)
                            <span class="badge bg-success">{{ $tripCount }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                @endforeach
                <td class="text-center bg-light">
                    <strong>{{ $driverTotal }}</strong>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($dates) + 2 }}" class="text-center py-4">
                    <div class="text-muted">
                        <i class="ti ti-inbox me-2"></i>No trips found for the selected date range.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-light">
                <th style="position: sticky; left: 0; background-color: #f8f9fa; z-index: 10;">
                    <strong>Date Total</strong>
                </th>
                @foreach($dates as $date)
                    @php
                        $dateTotal = 0;
                        foreach($drivers as $driver) {
                            $dateTotal += $pivotData[$driver->id][$date] ?? 0;
                        }
                    @endphp
                    <th class="text-center">
                        <strong>{{ $dateTotal }}</strong>
                    </th>
                @endforeach
                <th class="text-center bg-primary text-white">
                    @php
                        $grandTotal = 0;
                        foreach($drivers as $driver) {
                            foreach($dates as $date) {
                                $grandTotal += $pivotData[$driver->id][$date] ?? 0;
                            }
                        }
                    @endphp
                    <strong>{{ $grandTotal }}</strong>
                </th>
            </tr>
        </tfoot>
    </table>
</div>

@if(isset($fromDate) && isset($toDate))
<div class="card-footer">
    <div class="row">
        <div class="col-md-12">
            <small class="text-muted">
                <i class="ti ti-calendar me-1"></i>
                Report Period: {{ Carbon::parse($fromDate)->format('d M Y') }} to {{ Carbon::parse($toDate)->format('d M Y') }}
                | Total Drivers: {{ $drivers->count() }} | Total Days: {{ count($dates) }}
            </small>
        </div>
    </div>
</div>
@endif

@else
<div class="alert alert-info">
    <i class="ti ti-info-circle me-2"></i>
    Please select a date range to generate the trip report.
</div>
@endif

<style>
    .table-responsive {
        max-height: 600px;
        overflow-y: auto;
    }
    .table thead th {
        position: sticky;
        top: 0;
        background-color: #f8f9fa;
        z-index: 20;
    }
    .table tbody td:first-child {
        position: sticky;
        left: 0;
        background-color: white;
        z-index: 5;
    }
    .table tfoot th:first-child {
        position: sticky;
        left: 0;
        background-color: #f8f9fa;
        z-index: 10;
    }
</style>

