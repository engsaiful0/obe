<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Purpose</th>
                <th>Required Bus Date</th>
                <th>Required Time</th>
                <th>No. of Buses</th>
                <th>Total Passengers</th>
                <th>Department</th>
                <th>Sender Name</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($busRequisitions as $requisition)
            <tr>
                <td>{{ $requisition->id }}</td>
                <td>{{ $requisition->date ? \Carbon\Carbon::parse($requisition->date)->format('M d, Y') : 'N/A' }}</td>
                <td>
                    @if($requisition->purpose)
                        <span title="{{ $requisition->purpose }}">
                            {{ \Illuminate\Support\Str::limit($requisition->purpose, 30) }}
                        </span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ $requisition->required_bus_date ? \Carbon\Carbon::parse($requisition->required_bus_date)->format('M d, Y') : 'N/A' }}</td>
                <td>
                    @if($requisition->required_time)
                        @php
                            try {
                                $time = \Carbon\Carbon::createFromFormat('H:i:s', $requisition->required_time)->format('h:i A');
                            } catch (\Exception $e) {
                                try {
                                    $time = \Carbon\Carbon::createFromFormat('H:i', $requisition->required_time)->format('h:i A');
                                } catch (\Exception $e2) {
                                    $time = $requisition->required_time;
                                }
                            }
                        @endphp
                        {{ $time }}
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $requisition->number_of_buses }}</td>
                <td>{{ $requisition->total_passengers }}</td>
                <td>{{ $requisition->department->name ?? 'N/A' }}</td>
                <td>{{ $requisition->requisition_sender_name }}</td>
                <td>{{ $requisition->mobile_number }}</td>
                <td>{{ $requisition->email_address }}</td>
                <td>
                    <select class="form-select form-select-sm status-update" data-id="{{ $requisition->id }}" style="min-width: 120px;">
                        <option value="pending" {{ $requisition->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $requisition->status == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $requisition->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </td>
                <td>
                    <div class="d-inline-block">
                        <a href="{{ route('app-bus-requisitions.show', $requisition->id) }}" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" title="View">
                            <i class="ti ti-eye ti-md"></i>
                        </a>
                        <a href="{{ route('app-bus-requisitions.edit', $requisition->id) }}" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" title="Edit">
                            <i class="ti ti-pencil ti-md"></i>
                        </a>
                        <a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon delete-record" data-id="{{ $requisition->id }}" title="Delete">
                            <i class="ti ti-trash ti-md"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center">
                    <p class="text-muted py-4">No bus requisitions found.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($busRequisitions->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div>
            <p class="text-muted mb-0">
                Showing {{ $busRequisitions->firstItem() ?? 0 }} to {{ $busRequisitions->lastItem() ?? 0 }} of {{ $busRequisitions->total() }} results
            </p>
        </div>
        <div>
            {{ $busRequisitions->links() }}
        </div>
    </div>
@endif

