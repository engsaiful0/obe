@if($damages->count() > 0)
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Warehouse</th>
                    <th>Items</th>
                    <th>Total Quantity</th>
                    <th>Total Approximate</th>
                    <th>Remarks</th>
                    @if(auth()->user()->hasPermissionTo('damage-view') || auth()->user()->hasPermissionTo('damage-edit') || auth()->user()->hasPermissionTo('damage-delete'))
                    <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($damages as $damage)
                    <tr>
                        <td>{{ $damage->date->format('d M Y') }}</td>
                        <td>{{ $damage->warehouse->warehouse_name ?? 'N/A' }}</td>
                        <td>
                            @if($damage->damageItems->count() > 0)
                                <ul class="list-unstyled mb-0">
                                    @foreach($damage->damageItems as $item)
                                        <li>
                                            <span class="badge bg-primary">{{ $item->item->item_name ?? 'N/A' }}</span>
                                            <small class="text-muted">(Qty: {{ number_format($item->quantity, 2) }})</small>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="badge bg-secondary">No Items</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ number_format($damage->damageItems->sum('quantity'), 2) }}</span>
                        </td>
                        <td>
                            <span class="badge bg-warning">{{ number_format($damage->damageItems->sum('approximate'), 2) }}</span>
                        </td>
                        <td>{{ $damage->remarks ?? 'N/A' }}</td>
                        @if(auth()->user()->hasPermissionTo('damage-view') || auth()->user()->hasPermissionTo('damage-edit') || auth()->user()->hasPermissionTo('damage-delete'))
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    @if(auth()->user()->hasPermissionTo('damage-edit'))
                                    <a class="dropdown-item" href="{{ route('app-damage-edit', $damage->id) }}">
                                        <i class="ti ti-edit me-1"></i> Edit
                                    </a>
                                    @endif
                                    @if(auth()->user()->hasPermissionTo('damage-delete'))
                                    <a class="dropdown-item delete-damage" href="#" data-id="{{ $damage->id }}">
                                        <i class="ti ti-trash me-1"></i> Delete
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
 
@endif

