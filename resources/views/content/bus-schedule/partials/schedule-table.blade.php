<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Keyword</th>
                <th>Status</th>
                <th>Bus User</th>
                <th>Effective From</th>
                <th>Entries</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schedules as $schedule)
            <tr>
                <td>{{ $loop->iteration + ($schedules->currentPage() - 1) * $schedules->perPage() }}</td>
                <td>{{ $schedule->keyword->keyword_name ?? 'N/A' }}</td>
                <td>
                    <span class="badge bg-label-primary">{{ $schedule->status->status_name ?? 'N/A' }}</span>
                </td>
                <td>{{ $schedule->busUser->bus_user_name ?? 'N/A' }}</td>
                <td>{{ $schedule->effective_from ? \Carbon\Carbon::parse($schedule->effective_from)->format('Y-m-d') : 'N/A' }}</td>
                <td>
                    <span class="badge bg-label-info">{{ $schedule->entries->count() }} entries</span>
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-info view-schedule" data-id="{{ $schedule->id }}" title="View">
                            <i class="ti ti-eye"></i>
                        </button>
                        <a href="{{ route('bus-schedules.schedule-edit', $schedule->id) }}" class="btn btn-sm btn-warning" title="Edit">
                            <i class="ti ti-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger delete-schedule" data-id="{{ $schedule->id }}" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No bus schedules found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($schedules->hasPages())
<div class="d-flex justify-content-center mt-3 pagination-wrapper">
    {{ $schedules->links() }}
</div>
@endif

