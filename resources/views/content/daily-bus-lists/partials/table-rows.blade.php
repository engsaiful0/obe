@forelse ($dailyBusLists as $busList)
<tr>
    <td>{{ $loop->iteration + (($dailyBusLists->currentPage() - 1) * $dailyBusLists->perPage()) }}</td>
    <td>{{ $busList->list_date }}</td>
    <td>
        {{ $busList->vehicle->model_name ?? 'N/A' }}
        <br><small class="text-muted">{{ $busList->vehicle->registration_number ?? 'N/A' }}</small>
    </td>
    <td>{{ $busList->vehicleSubType->sub_type_name ?? 'N/A' }}</td>
    <td>{{ $busList->startStoppage->stoppage_name ?? 'N/A' }}</td>
    <td>{{ $busList->endStoppage->stoppage_name ?? 'N/A' }}</td>
    <td>
        @if($busList->tripTime)
            <span class="badge bg-primary">{{ $busList->tripTime->time_name }}</span><br>
            <small class="text-muted">{{ \Carbon\Carbon::parse($busList->tripTime->time_value)->format('H:i') }} {{ $busList->tripTime->time_period }}</small>
        @else
            <span class="text-muted">No Trip Time</span>
        @endif
    </td>
    <td>{{ $busList->driver->full_name ?? 'N/A' }}</td>
    <td>{{ $busList->assistant->assistant_name ?? 'N/A' }}</td>
   
    <td>
        <div class="btn-group" role="group">
            <a href="{{ route('daily-bus-lists.show', $busList->id) }}" class="btn btn-sm btn-outline-primary">
                <i title="View" class="ti ti-eye"></i>
            </a>
            <a href="{{ route('daily-bus-lists.edit', $busList->id) }}" class="btn btn-sm btn-outline-warning">
                <i title="Edit" class="ti ti-edit"></i>
            </a>
            <form action="{{ route('daily-bus-lists.destroy', $busList->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id" value="{{ $busList->id }}">
                <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                    <i title="Delete" class="ti ti-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="11" class="text-center py-4">
        <div class="text-muted">
            <i data-feather="inbox" class="mb-2" style="width: 48px; height: 48px;"></i>
            <br>No bus list entries found.
        </div>
    </td>
</tr>
@endforelse
