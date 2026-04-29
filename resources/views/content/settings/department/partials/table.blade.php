<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Created By</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departments as $department)
            <tr>
                <td>{{ $department->id }}</td>
                <td>{{ $department->name }}</td>
                <td>
                    @if($department->description)
                        <span title="{{ $department->description }}">
                            {{ \Illuminate\Support\Str::limit($department->description, 50) }}
                        </span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>
                    @if($department->user)
                        {{ $department->user->name ?? 'N/A' }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ $department->created_at ? \Carbon\Carbon::parse($department->created_at)->format('M d, Y') : 'N/A' }}</td>
                <td>
                    <div class="d-inline-block">
                        <a href="{{ route('app-settings-department.show', $department->id) }}" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" title="View">
                            <i class="ti ti-eye ti-md"></i>
                        </a>
                        <a href="{{ route('app-settings-department.edit', $department->id) }}" class="btn btn-sm btn-text-secondary rounded-pill btn-icon" title="Edit">
                            <i class="ti ti-pencil ti-md"></i>
                        </a>
                        <a href="javascript:;" class="btn btn-sm btn-text-secondary rounded-pill btn-icon delete-record" data-id="{{ $department->id }}" title="Delete">
                            <i class="ti ti-trash ti-md"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">
                    <p class="text-muted py-4">No departments found.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($departments->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div>
            <p class="text-muted mb-0">
                Showing {{ $departments->firstItem() ?? 0 }} to {{ $departments->lastItem() ?? 0 }} of {{ $departments->total() }} results
            </p>
        </div>
        <div>
            {{ $departments->links() }}
        </div>
    </div>
@endif

