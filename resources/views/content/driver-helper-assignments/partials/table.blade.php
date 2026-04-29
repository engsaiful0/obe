<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Bus Information</th>
                <th>Driver</th>
                <th>Bus Helper</th>
                <th>Assignment Date</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $assignment)
                <tr>
                    <td>{{ $assignments->firstItem() + $loop->index }}</td>
                    <td>
                        <div>
                            <strong>{{ $assignment->bus->bus_number ?? 'N/A' }}</strong><br>
                            <small class="text-muted">Reg: {{ $assignment->bus->registration_number ?? 'N/A' }}</small><br>
                            <small class="text-muted">Bus #: {{ $assignment->bus->bus_number ?? 'N/A' }}</small>
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong>{{ $assignment->driver->full_name ?? 'N/A' }}</strong><br>
                            <small class="text-muted">ID: {{ $assignment->driver->driver_unique_id ?? 'N/A' }}</small>
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong>{{ $assignment->busHelper->bus_helper_name ?? 'N/A' }}</strong><br>
                            <small class="text-muted">ID: {{ $assignment->busHelper->bus_helper_id ?? 'N/A' }}</small>
                        </div>
                    </td>
                    <td>
                        {{ $assignment->assignment_date ? $assignment->assignment_date->format('Y-m-d') : 'N/A' }}
                    </td>
                    <td>
                        @if($assignment->status)
                            <span class="badge bg-label-primary">{{ $assignment->status->status_name }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($assignment->notes)
                            <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $assignment->notes }}">
                                {{ \Illuminate\Support\Str::limit($assignment->notes, 50) }}
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('driver-helper-assignments.show', $assignment) }}">
                                    <i class="ti ti-eye me-1"></i> View
                                </a>
                                <a class="dropdown-item" href="{{ route('driver-helper-assignments.edit', $assignment) }}">
                                    <i class="ti ti-pencil me-1"></i> Edit
                                </a>
                                <div class="dropdown-divider"></div>
                                <button class="dropdown-item text-danger btn-delete-assignment" type="button" data-assignment-id="{{ $assignment->id }}">
                                    <i class="ti ti-trash me-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <i class="ti ti-users-off text-muted mb-3" style="font-size: 3rem;"></i>
                            <h6 class="text-muted">No assignments found</h6>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'bus_id', 'driver_id', 'bus_helper_id', 'status_id']))
                                    Try adjusting your search criteria or 
                                    <a href="{{ route('driver-helper-assignments.index') }}">clear all filters</a>.
                                @else
                                    Start by <a href="{{ route('driver-helper-assignments.create') }}">adding your first assignment</a>.
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
function deleteAssignment(assignmentId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to delete this assignment. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="ti ti-trash me-1"></i>Yes, delete it!',
        cancelButtonText: '<i class="ti ti-x me-1"></i>Cancel',
        customClass: {
            confirmButton: 'btn btn-danger me-3',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const deleteUrl = '{{ url("/app/driver-helper-assignments") }}/' + assignmentId;
            
            $.ajax({
                url: deleteUrl,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(data) {
                    if (data.success) {
                        toastr.success(data.message || 'Assignment deleted successfully');
                        location.reload();
                    } else {
                        toastr.error(data.message || 'Error deleting assignment');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error deleting assignment';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-danger'
                        },
                        buttonsStyling: false
                    });
                    toastr.error(errorMessage);
                }
            });
        }
    });
}

// Initialize delete button handlers
$(document).ready(function() {
    $(document).off('click', '.btn-delete-assignment').on('click', '.btn-delete-assignment', function() {
        const assignmentId = $(this).data('assignment-id');
        deleteAssignment(assignmentId);
    });
});
</script>

