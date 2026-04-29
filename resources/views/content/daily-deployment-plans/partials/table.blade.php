@forelse($plans as $plan)
<tr>
    <td>{{ $loop->iteration + (($plans->currentPage() - 1) * $plans->perPage()) }}</td>
    <td>{{ $plan->deployment_date->format('Y-m-d') }}</td>
    <td>
        <!-- <span class="badge bg-primary">{{ $plan->tripTime->time_name ?? 'N/A' }}</span><br> -->
        @if($plan->tripTime)
        <span class="badge bg-primary">{{ \Carbon\Carbon::parse($plan->tripTime->time_value)->format('h:i') }} {{ $plan->tripTime->time_period }}</span>
        @else
        <span class="badge bg-secondary">N/A</span>
        @endif
    </td>
    <td>{{ $plan->deploymentType->deployment_type_name ?? 'N/A' }}</td>
    <td>{{ ucfirst($plan->trip_type) ?? 'N/A' }}</td>
    <td>{{ $plan->busUser->bus_user_name ?? 'N/A' }}</td>
    <td>
        @php
        $stoppages = $plan->items->pluck('stoppage.stoppage_name')->unique()->filter();
        @endphp
        {{ $stoppages->count() }} stoppage(s)
    </td>
    <td>{{ $plan->user->name ?? 'N/A' }}</td>
    
    @permission('daily-deployment-plan-view')
    <td>
        <a href="{{ route('deployment-plans.show', $plan->id) }}" class="btn btn-sm btn-info">
            <i class="ti ti-eye me-1"></i> View
        </a>
    </td>
    @endpermission
    @permission('daily-deployment-plan-edit')
    <td>
        <a href="{{ route('deployment-plans.edit', $plan->id) }}" class="btn btn-sm btn-primary">
            <i class="ti ti-pencil me-1"></i> Edit
        </a>
    </td>
    @endpermission
    @permission('daily-deployment-plan-delete')
    <td>
        <form action="{{ route('deployment-plans.destroy', $plan->id) }}" method="POST" class="d-inline delete-form">
            @csrf
            @method('DELETE')
            <input type="hidden" name="id" value="{{ $plan->id }}">
            <button type="button" class="btn btn-sm btn-danger delete-btn">
                <i class="ti ti-trash me-1"></i> Delete
            </button>
        </form>
    </td>
    @endpermission
</tr>
@empty
<tr>
    <td colspan="7" class="text-center">No deployment plans found.</td>
</tr>
@endforelse