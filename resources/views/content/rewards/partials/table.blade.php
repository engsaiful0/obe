@if($rewards->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Vehicle</th>
                    <th>Driver</th>
                    <th>Assistant</th>
                    <th>Reward Type</th>
                    <th>Reward Amount</th>
                    <th>Reason</th>
                    <th>User</th>

                    @if(auth()->user()->hasPermissionTo('reward-view') || auth()->user()->hasPermissionTo('reward-edit') || auth()->user()->hasPermissionTo('reward-delete'))
                    <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($rewards as $index => $reward)
                    <tr>
                        <td>{{ $rewards->firstItem() + $index }}</td>
                        <td>{{ $reward->reward_date->format('M d, Y') }}</td>
                        <td>
                            <strong>{{ $reward->vehicle->registration_number ?? 'N/A' }}</strong>
                            <br>
                            <small class="text-muted">{{ $reward->vehicle->model_name ?? '' }}</small>
                        </td>
                        <td>{{ $reward->driver->full_name ?? 'N/A' }}</td>
                        <td>{{ $reward->assistant->assistant_name ?? 'N/A' }}</td>
                        <td>
                            @if($reward->rewardType)
                                <span class="badge bg-info">
                                    {{ $reward->rewardType->name }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <strong class="text-success">৳{{ number_format($reward->reward_amount, 2) }}</strong>
                        </td>
                        <td>
                            <small>{{ str($reward->reason)->limit(40) }}</small>
                        </td>
                        <td>{{ $reward->user->name ?? 'N/A' }}</td>
                       
                        <td>
                            <div class="d-flex gap-1">
                                @if(auth()->user()->hasPermissionTo('reward-view'))
                                <a href="{{ route('rewards.show', $reward) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="ti ti-eye"></i>
                                </a>
                                @endif
                                @if(auth()->user()->hasPermissionTo('reward-edit'))
                                <a href="{{ route('rewards.edit', $reward) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                @endif
                                @if(auth()->user()->hasPermissionTo('reward-delete'))
                                <form action="{{ route('rewards.destroy', $reward) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-active">
                    <td colspan="6" class="text-end"><strong>Total Rewards (This Page):</strong></td>
                    <td colspan="4"><strong class="text-success">৳{{ number_format($pageTotal, 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
@else
    <div class="text-center py-5">
        <i class="ti ti-gift" style="font-size: 48px; color: #ccc;"></i>
        <h5 class="mt-3">No Reward Records Found</h5>
        <p class="text-muted">Try adjusting your filters or create a new reward record.</p>
        <a href="{{ route('rewards.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Add Reward
        </a>
    </div>
@endif

