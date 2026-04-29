<div class="table-responsive text-nowrap">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Vehicle Sub Type</th>
                <th>Vehicle</th>
                <th>Driver</th>
                <th>Bus Helper</th>
                <th>Punishment Type</th>
                <th>Violation Type</th>
                <th>Description</th>
                <th>Fine Amount</th>
                <th>Suspension Days</th>
                <th>Status</th>
                <th>Witness</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse ($punishments as $punishment)
            <tr>
            <td>{{ $loop->iteration }}</td>
                <td>{{ $punishment->punishment_date ? $punishment->punishment_date->format('Y-m-d') : '' }}</td>
                <td>{{ $punishment->bus && $punishment->bus->busSubType ? $punishment->bus->busSubType->sub_type_name : '' }}</td>
                <td>{{ $punishment->bus ? $punishment->bus->bus_number : '' }}</td>
                <td>{{ $punishment->driver ? $punishment->driver->full_name : '' }}</td>
                <td>{{ $punishment->bus_helper ? $punishment->bus_helper->bus_helper_name : '' }}</td>
                <td>{{ $punishment->punishmentType ? $punishment->punishmentType->name : '' }}</td>
                <td>{{ $punishment->violationType ? $punishment->violationType->name : '' }}</td>
                <td>{{ str($punishment->description)->limit(50) }}</td>
                <td class="text-end">{{ $punishment->fine_amount ? number_format($punishment->fine_amount, 2) : '-' }}</td>
                <td class="text-center">{{ $punishment->suspension_days ?: '-' }}</td>
                <td>
                    <span class="badge 
                        @if($punishment->status == 'active') bg-warning
                        @elseif($punishment->status == 'completed') bg-success
                        @elseif($punishment->status == 'cancelled') bg-danger
                        @else bg-secondary
                        @endif">
                        {{ ucfirst($punishment->status) }}
                    </span>
                </td>
                <td>{{ $punishment->witnessEmployee ? $punishment->witnessEmployee->employee_name : '' }}</td>
                <td>{{ str($punishment->remarks)->limit(30) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center">No punishments found.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="9" class="text-end">Total Fine Amount:</th>
                <th class="text-end">{{ number_format($totalFineAmount, 2) }}</th>
                <th class="text-center">{{ $totalSuspensionDays ?: '-' }}</th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>
</div>
<div class="card-footer">
    {{ $punishments->links() }}
</div>
