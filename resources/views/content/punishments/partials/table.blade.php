@if($punishments->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Bus</th>
                    <th>Driver</th>
                    <th>Helper</th>
                    <th>Punishment Type</th>
                    <th>Violation Type</th>
                    <th>Fine Amount</th>
                    <th>Suspension Days</th>

                    @if(auth()->user()->hasPermissionTo('punishment-view') || auth()->user()->hasPermissionTo('punishment-edit') || auth()->user()->hasPermissionTo('punishment-delete'))
                    <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($punishments as $index => $punishment)
                    <tr>
                        <td>{{ $punishments->firstItem() + $index }}</td>
                        <td>{{ $punishment->punishment_date->format('M d, Y') }}</td>
                        <td>
                            <strong>{{ $punishment->bus->bus_number ?? 'N/A' }}</strong>
                            <br>
                            <small class="text-muted">{{ $punishment->bus->model_name ?? '' }}</small>
                        </td>
                        <td>{{ $punishment->driver->full_name ?? 'N/A' }}</td>
                        <td>{{ $punishment->busHelper->bus_helper_name ?? 'N/A' }}</td>
                        <td>
                            @php
                                $typeClass = match($punishment->punishmentType->name) {
                                    'warning' => 'warning',
                                    'fine' => 'info',
                                    'suspension' => 'danger',
                                    'termination' => 'dark',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $typeClass }}">
                                {{ ucfirst($punishment->punishmentType->name) }}
                            </span>
                        </td>
                        <td>{{ ucwords(str_replace('_', ' ', $punishment->violationType->name)) }}</td>
                        <td>
                            @if($punishment->fine_amount)
                                ৳{{ number_format($punishment->fine_amount, 2) }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($punishment->suspension_days)
                                {{ $punishment->suspension_days }} days
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        @if(auth()->user()->hasPermissionTo('punishment-view') || auth()->user()->hasPermissionTo('punishment-edit') || auth()->user()->hasPermissionTo('punishment-delete'))
                        <td>
                            <div class="d-flex gap-1">
                                @if(auth()->user()->hasPermissionTo('punishment-view'))
                                <a href="{{ route('punishments.show', $punishment) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="ti ti-eye"></i>
                                </a>
                                @endif
                                @if(auth()->user()->hasPermissionTo('punishment-edit'))
                                <a href="{{ route('punishments.edit', $punishment) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                @endif
                                @if(auth()->user()->hasPermissionTo('punishment-delete'))
                                <form action="{{ route('punishments.destroy', $punishment) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this punishment record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-5">
        <i class="ti ti-alert-circle" style="font-size: 48px; color: #ccc;"></i>
        <h5 class="mt-3">No Punishment Records Found</h5>
        <p class="text-muted">No records match your current filter criteria.</p>
    </div>
@endif
