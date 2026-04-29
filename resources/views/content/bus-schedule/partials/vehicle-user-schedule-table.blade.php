<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Start Stoppage</th>
                <th>End Stoppage</th>
                <th>Route</th>
                <th>Start Time</th>
                <th>Vehicle</th>
                <th>Vehicle Type</th>
                <th>Vehicle Sub Type</th>
                <th>Driver</th>
                <th>Assistant</th>
                <th>Trip Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $schedule)
                <tr>
                    <td>{{ $schedule->startStoppage->stoppage_name ?? 'N/A' }}</td>
                    <td>{{ $schedule->endStoppage->stoppage_name ?? 'N/A' }}</td>
                    <td>{{ $schedule->vehicleRoute->route_name ?? 'N/A' }}</td>
          
                    <td>
                        @if($schedule->vehicle)
                            {{ $schedule->vehicle->model_name }}<br>
                            <small class="text-muted">{{ $schedule->vehicle->registration_number }}</small>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $schedule->vehicleType->vehicle_type_name ?? 'N/A' }}</td>
                    <td>{{ $schedule->vehicleSubType->sub_type_name ?? 'N/A' }}</td>
                    <td>{{ $schedule->driver->full_name ?? 'N/A' }}</td>
                    <td>{{ $schedule->assistant->assistant_name ?? 'N/A' }}</td>
                    <td>
                        @if($schedule->tripTime)
                            <span class="badge bg-primary">{{ $schedule->tripTime->time_name }}</span><br>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($schedule->tripTime->time_value)->format('H:i') }} {{ $schedule->tripTime->time_period }}</small>
                        @else
                            <span class="text-muted">No Trip Time</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $schedule->status == 'active' ? 'success' : ($schedule->status == 'inactive' ? 'warning' : 'danger') }}">
                            {{ ucfirst($schedule->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" onclick="viewSchedule({{ $schedule->id }})">
                                    <i class="ti ti-eye me-1"></i> View
                                </a>
                                <a class="dropdown-item" href="#" onclick="editSchedule({{ $schedule->id }})">
                                    <i class="ti ti-edit me-1"></i> Edit
                                </a>
                                <a class="dropdown-item text-danger" href="#" onclick="deleteBusSchedule({{ $schedule->id }})">
                                    <i class="ti ti-trash me-1"></i> Delete
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No schedules found for this vehicle user.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-between align-items-center mt-3">
    <div>
        Showing {{ $schedules->firstItem() ?? 0 }} to {{ $schedules->lastItem() ?? 0 }} of {{ $schedules->total() }} entries
    </div>
    <div>
        {{ $schedules->links() }}
    </div>
</div>
