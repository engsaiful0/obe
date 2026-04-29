<div class="table-responsive text-nowrap">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Bus Sub Type</th>
                <th>Bus</th>
                <th>Driver</th>
                <th>Bus Helper</th>
                <th>Reward Type</th>
                <th>Reason</th>
                <th>Amount</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse ($rewards as $reward)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $reward->reward_date ? $reward->reward_date->format('Y-m-d') : '' }}</td>
                <td>{{ $reward->bus && $reward->bus->busSubType ? $reward->bus->busSubType->sub_type_name : '' }}</td>
                <td>{{ $reward->bus ? $reward->bus->bus_number : '' }}</td>
                <td>{{ $reward->driver ? $reward->driver->full_name : '' }}</td>
                <td>{{ $reward->bus_helper ? $reward->bus_helper->bus_helper_name : '' }}</td>
                <td>{{ $reward->rewardType ? $reward->rewardType->name : '' }}</td>
                <td>{{ $reward->reason }}</td>
                <td class="text-end">{{ number_format($reward->reward_amount, 2) }}</td>
                <td>{{ $reward->remarks }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No rewards found.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" class="text-end">Total Amount:</th>
                <th class="text-end">{{ number_format($totalAmount, 2) }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>
<div class="card-footer">
    {{ $rewards->links() }}
</div>
