@if($buses->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Photo</th>
                    <th>Bus Details</th>
                    <th>Bus Number</th>
                    <th>Oil Required Per KM</th>
                    <th>Status</th>
                    <th>Documents</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($buses as $index => $bus)
                    <tr>
                        <td>{{ $buses->firstItem() + $index }}</td>
                        <td>
                            @if($bus->bus_photo)
                                <img src="{{ asset('storage/app/public/' . $bus->bus_photo) }}" 
                                     alt="{{ $bus->display_name }}" 
                                     class="rounded" 
                                     width="50" 
                                     height="40"
                                     style="object-fit: cover;">
                            @else
                                <div class="avatar avatar-sm bg-secondary rounded d-flex align-items-center justify-content-center">
                                    <i class="ti ti-car text-white"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div>
                             
                                <br>
                                <small class="text-muted">
                                    Model: {{ $bus->model_name ?? 'N/A' }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    Brand: {{ $bus->brand->brand_name ?? 'N/A' }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    Year: {{ $bus->yearOfManufacture->year_name ?? 'N/A' }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    Color: {{ $bus->color->color_name ?? 'N/A' }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    Engine Number: {{ $bus->engine_number }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    Transmission Type: {{ $bus->transmission_type }}
                                </small>
                                <br>
                                <small class="text-muted">
                                    Seating Capacity: {{ $bus->seating_capacity }}
                                </small>
                            
                              
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $bus->bus_number }}</strong>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $bus->required_oil_per_km }}</strong>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $bus->registration_number }}</strong>
                                @if($bus->registration_expiry)
                                    <br>
                                    <small class="text-muted">
                                        Expires: {{ $bus->registration_expiry->format('M d, Y') }}
                                    </small>
                                @endif
                            </div>
                        </td>
                        <td>
                            @php
                                $statusClass = match($bus->status->status_name) {
                                    'active' => 'success',
                                    'inactive' => 'secondary',
                                    'under_maintenance' => 'warning',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $bus->status->status_name)) }}
                            </span>
                        </td>
                      
                        <td>
                            @php
                                $expiredDocs = $bus->getExpiredDocuments();
                            @endphp
                            @if(!empty($expiredDocs))
                                <span class="badge bg-danger">
                                    {{ count($expiredDocs) }} Expired
                                </span>
                            @else
                                <span class="badge bg-success">Valid</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('buses.show', $bus) }}">
                                        <i class="ti ti-eye me-2"></i>View
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('buses.edit', $bus) }}">
                                        <i class="ti ti-edit me-2"></i>Edit
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('buses.destroy', $bus) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this vehicle?')">
                                                <i class="ti ti-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-5">
        <i class="ti ti-car text-muted" style="font-size: 4rem;"></i>
        <h5 class="mt-3 text-muted">No buses found</h5>
        <p class="text-muted">Start by adding your first bus or adjust your filters.</p>
        <a href="{{ route('buses.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Add Bus
        </a>
    </div>
@endif
