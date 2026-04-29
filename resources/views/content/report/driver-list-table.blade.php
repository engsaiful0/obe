<div class="table-responsive text-nowrap">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Driver ID</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Driver Type</th>
                <th>License Type</th>
                <th>Experience</th>
                <th>Salary</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse ($drivers as $driver)
            <tr>
                <td>{{ $driver->driver_unique_id }}</td>
                <td>{{ $driver->full_name }}</td>
                <td>{{ $driver->contact_number }}</td>
                <td>{{ $driver->driverType->driver_type_name ?? 'N/A' }}</td>
                <td>{{ $driver->licenseType->items_name ?? 'N/A' }}</td>
                <td>{{ $driver->experienceYear->items_name ?? 'N/A' }}</td>
                <td>৳{{ number_format($driver->gross_salary, 2) }}</td>
                <td>
                    @if($driver->status)
                        @php
                            $statusName = $driver->status->status_name ?? $driver->status;
                            $badgeClass = 'secondary';
                            if (stripos($statusName, 'active') !== false) {
                                $badgeClass = 'success';
                            } elseif (stripos($statusName, 'inactive') !== false) {
                                $badgeClass = 'danger';
                            } elseif (stripos($statusName, 'suspended') !== false) {
                                $badgeClass = 'warning';
                            }
                        @endphp
                         <span class="badge bg-label-{{ $badgeClass }}">
                            {{ $statusName }}
                        </span>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('drivers.show', $driver) }}" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" title="View Details">
                        <i class="ti ti-eye ti-md"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-4">
                    <p class="text-muted">No drivers found.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-end">Total:</th>
                <th colspan="3">৳{{ number_format($totalSalary, 2) }} <small class="text-muted ms-2">(Avg: ৳{{ number_format($avgSalary, 2) }})</small></th>
            </tr>
        </tfoot>
    </table>
</div>
@if($drivers->hasPages())
<div class="card-footer">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <p class="text-muted mb-0">
                Showing {{ $drivers->firstItem() ?? 0 }} to {{ $drivers->lastItem() ?? 0 }} of {{ $drivers->total() }} results
            </p>
        </div>
        <div>
            {{ $drivers->links() }}
        </div>
    </div>
</div>
@endif
