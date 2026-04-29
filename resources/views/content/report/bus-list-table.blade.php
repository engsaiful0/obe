<div class="table-responsive text-nowrap">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Bus Number</th>
                <th>Model Name</th>
                <th>Bus Type</th>
                <th>Bus Sub Type</th>
                <th>Brand</th>
                <th>Registration Number</th>
                <th>Chassis Number</th>
                <th>Engine Number</th>
                <th>Year</th>
                <th>Color</th>
                <th>Fuel Type</th>
                <th>Seating Capacity</th>
                <th>Driver</th>
                <th>Bus Helper</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Fixed Price</th>
                <th>Rate Per KM</th>
                <th>Current Mileage</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse ($buses as $bus)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                    <strong>{{ $bus->bus_number ?? 'N/A' }}</strong>
                </td>
                <td>{{ $bus->model_name ?? 'N/A' }}</td>
                <td>
                    <span class="badge bg-info">{{ $bus->busType->bus_type_name ?? 'N/A' }}</span>
                </td>
                <td>
                    <span class="badge bg-primary">{{ $bus->busSubType->sub_type_name ?? 'N/A' }}</span>
                </td>
                <td>{{ $bus->brand->brand_name ?? 'N/A' }}</td>
                <td>
                    <strong>{{ $bus->registration_number ?? 'N/A' }}</strong>
                </td>
                <td>{{ $bus->chassis_number ?? 'N/A' }}</td>
                <td>{{ $bus->engine_number ?? 'N/A' }}</td>
                <td>{{ $bus->yearOfManufacture->year_name ?? 'N/A' }}</td>
                <td>{{ $bus->color->color_name ?? 'N/A' }}</td>
                <td>{{ $bus->fuelType->fuel_type_name ?? 'N/A' }}</td>
                <td>{{ $bus->seating_capacity ?? 'N/A' }}</td>
                <td>{{ $bus->driver->full_name ?? 'N/A' }}</td>
                <td>{{ $bus->busHelper->bus_helper_name ?? 'N/A' }}</td>
                <td>{{ $bus->supplier->supplier_name ?? 'N/A' }}</td>
                <td>
                    @if($bus->status->status_name == 'active')
                        <span class="badge bg-success">Active</span>
                    @elseif($bus->status->status_name == 'inactive')
                        <span class="badge bg-secondary">Inactive</span>
                    @elseif($bus->status->status_name == 'under_maintenance')
                        <span class="badge bg-warning">Under Maintenance</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $bus->status->status_name ?? 'N/A')) }}</span>
                    @endif
                </td>
                <td>{{ $bus->fixed_price ? number_format($bus->fixed_price, 2) : 'N/A' }}</td>
                <td>{{ $bus->rate_per_km ? number_format($bus->rate_per_km, 2) : 'N/A' }}</td>
                <td>{{ $bus->current_mileage ? number_format($bus->current_mileage, 2) : 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="20" class="text-center py-4">
                    <div class="text-muted">
                        <i class="ti ti-inbox me-2"></i>No buses found.
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="19" class="text-end">Total Buses:</th>
                <th>{{ $buses->count() }}</th>
            </tr>
        </tfoot>
    </table>
</div>

