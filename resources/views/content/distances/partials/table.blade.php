@if($distances->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Route Name</th>
                    <th>Start Stoppage</th>
                    <th>End Stoppage</th>
                    <th>Distance</th>
                    <th>Description</th>
                    <th>Created By</th>
                    @if(auth()->user()->hasPermissionTo('distance-view') || auth()->user()->hasPermissionTo('distance-edit') || auth()->user()->hasPermissionTo('distance-delete'))
                    <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($distances as $index => $distance)
                    <tr>
                        <td>{{ $distances->firstItem() + $index }}</td>
                        <td>
                            <div>
                                <strong>{{ $distance->route_name }}</strong>
                                @if($distance->distance_name)
                                    <br>
                                    <small class="text-muted">{{ $distance->distance_name }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $distance->startStoppage->stoppage_name }}</strong>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $distance->endStoppage->stoppage_name }}</strong>
                            </div>
                        </td>
                        <td>
                            <span class="fw-medium text-primary">{{ $distance->formatted_distance }}</span>
                        </td>
                        <td>
                            @if($distance->description)
                                <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                      title="{{ $distance->description }}">
                                    {{ $distance->description }}
                                </span>
                            @else
                                <span class="text-muted">No description</span>
                            @endif
                        </td>
                        <td>
                            <div>
                                <strong>{{ $distance->user->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $distance->created_at->format('M d, Y') }}</small>
                            </div>
                        </td>
                        @if(auth()->user()->hasPermissionTo('distance-view') || auth()->user()->hasPermissionTo('distance-edit') || auth()->user()->hasPermissionTo('distance-delete'))
                        <td>
                            <div class="dropdown">
                                @if(auth()->user()->hasPermissionTo('distance-view'))
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                @endif
                                <ul class="dropdown-menu">
                                    @if(auth()->user()->hasPermissionTo('distance-view'))
                                    <li><a class="dropdown-item" href="{{ route('distances.show', $distance) }}">
                                        <i class="ti ti-eye me-2"></i>View
                                    </a></li>
                                    @endif
                                    @if(auth()->user()->hasPermissionTo('distance-edit'))
                                    <li><a class="dropdown-item" href="{{ route('distances.edit', $distance) }}">
                                        <i class="ti ti-edit me-2"></i>Edit
                                    </a></li>
                                    @endif
                                    
                                    <li><hr class="dropdown-divider"></li>
                               
                                    @if(auth()->user()->hasPermissionTo('distance-delete'))
                                    <li>
                                        <button type="button" class="dropdown-item text-danger delete-distance-btn" 
                                                data-distance-id="{{ $distance->id }}"
                                                data-distance-name="{{ $distance->route_name }}"
                                                data-delete-url="{{ route('distances.destroy', $distance) }}">
                                            <i class="ti ti-trash me-2"></i>Delete
                                        </button>
                                        <!-- Hidden form for fallback delete -->
                                        <form id="delete-form-{{ $distance->id }}" action="{{ route('distances.destroy', $distance) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </li>
                                    @endif
                                </ul>
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
        <i class="ti ti-route text-muted" style="font-size: 4rem;"></i>
        <h5 class="mt-3 text-muted">No distances found</h5>
        <p class="text-muted">Start by adding your first distance or adjust your filters.</p>
        <a href="{{ route('distances.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Add Distance
        </a>
    </div>
@endif
