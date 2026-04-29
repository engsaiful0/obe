<div class="table-responsive text-nowrap">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Helper ID</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>NID</th>
                <th>Gender</th>
                <th>Employee Type</th>
                <th>Experience</th>
                <th>Assigned Bus</th>
                <th>Basic Salary</th>
                <th>Gross Salary</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody class="table-border-bottom-0">
            @forelse ($busHelpers as $helper)
            <tr>
                <td>{{ $helper->bus_helper_id }}</td>
                <td>{{ $helper->bus_helper_name }}</td>
                <td>{{ $helper->mobile }}</td>
                <td>{{ $helper->nid_number }}</td>
                <td>{{ $helper->gender->gender_name ?? 'N/A' }}</td>
                <td>{{ $helper->employeeType->employee_type_name ?? 'N/A' }}</td>
                <td>{{ $helper->years_of_experience }} years</td>
                <td>
                    @if($helper->assignedBus)
                        {{ $helper->assignedBus->bus_number }} ({{ $helper->assignedBus->model_name }})
                    @else
                        <span class="text-muted">Not Assigned</span>
                    @endif
                </td>
                <td>৳{{ number_format($helper->basic_salary, 2) }}</td>
                <td>৳{{ number_format($helper->gross_salary, 2) }}</td>
                <td>
                    @if($helper->status)
                        <span class="badge bg-label-{{ $helper->status->status_name == 'Active' ? 'success' : 'secondary' }}">
                            {{ $helper->status->status_name }}
                        </span>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('bus-helpers.show', $helper) }}" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" title="View Details">
                        <i class="ti ti-eye ti-md"></i>
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center py-4">
                    <p class="text-muted">No helpers found.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8" class="text-end">Total:</th>
                <th colspan="2">৳{{ number_format($totalSalary, 2) }}</th>
                <th colspan="2">
                    <small class="text-muted">Avg: ৳{{ number_format($avgSalary, 2) }}</small>
                </th>
            </tr>
        </tfoot>
    </table>
</div>
@if($busHelpers->hasPages())
<div class="card-footer">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <p class="text-muted mb-0">
                Showing {{ $busHelpers->firstItem() ?? 0 }} to {{ $busHelpers->lastItem() ?? 0 }} of {{ $busHelpers->total() }} results
            </p>
        </div>
        <div>
            {{ $busHelpers->links() }}
        </div>
    </div>
</div>
@endif
