@if($busTrips->count() > 0)
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Bus</th>
                    <th>Driver</th>
                    <th>Helper</th>
                    <th>Sub Type</th>
                    <th>Trip Type</th>
                    <th>Route</th>
                    <th>Time</th>
                    <th>No. of Pass</th>
                    <th>Trip No.</th>
                    <th>Distance (KM)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($busTrips as $index => $busTrip)
                    <tr>
                        <td>{{ $busTrips->firstItem() + $index }}</td>
                        <td>{{ $busTrip->trip_date->format('M d, Y') }}</td>
                        <td>
                            <strong>{{ $busTrip->bus->registration_number ?? 'N/A' }}</strong>
                          
                        </td>
                        <td>
                            @if($busTrip->driver)
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-user text-primary me-1"></i>
                                    <div>
                                        <strong>{{ $busTrip->driver->full_name }}</strong>
    
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">
                                    <i class="ti ti-user-x me-1"></i>Not assigned
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($busTrip->busHelper)
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-user-plus text-warning me-1"></i>
                                    <div>
                                        <strong>{{ $busTrip->busHelper->bus_helper_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $busTrip->busHelper->bus_helper_id ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">
                                    <i class="ti ti-user-x me-1"></i>Not assigned
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($busTrip->bus && $busTrip->bus->busSubType)
                                <span class="badge {{ $busTrip->bus->busSubType->sub_type_name === 'BRTC Hired Bus' ? 'bg-info' : 'bg-success' }}">
                                    {{ $busTrip->bus->busSubType->sub_type_name }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $busTrip->trip_type === 'in' ? 'bg-primary' : 'bg-warning' }}">
                                {{ strtoupper($busTrip->trip_type) }}
                            </span>
                        </td>
                        <td>
                            <small>
                                <i class="ti ti-map-pin text-success"></i> {{ $busTrip->startStoppage->stoppage_name ?? 'N/A' }}
                                <br>
                                <i class="ti ti-flag text-danger"></i> {{ $busTrip->endStoppage->stoppage_name ?? 'N/A' }}
                            </small>
                        </td>
                        <td>
                            @if($busTrip->trip_type === 'in' && $busTrip->in_time)
                                <strong>{{ $busTrip->in_time->format('h:i A') }}</strong>
                            @elseif($busTrip->trip_type === 'out' && $busTrip->out_time)
                                <strong>{{ $busTrip->out_time->format('h:i A') }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($busTrip->passengers)
                                <strong>{{ $busTrip->passengers }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($busTrip->trip_number)
                                <strong>{{ $busTrip->trip_number }}</strong>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($busTrip->total_distance)
                                <strong>{{ number_format($busTrip->total_distance, 2) }}</strong> KM
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $isDayComplete = \App\Models\BusTrip::isDayComplete($busTrip->bus_id, $busTrip->trip_date);
                            @endphp
                            @if($isDayComplete)
                                <span class="badge bg-success">Day Complete</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('bus-trips.show', $busTrip) }}" class="btn btn-sm btn-info" title="View Details">
                                    <i class="ti ti-eye"></i>
                                </a>
                                <a href="{{ route('bus-trips.edit', $busTrip) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <button type="button" 
                                        class="btn btn-sm btn-danger delete-trip-btn" 
                                        data-trip-id="{{ $busTrip->id }}"
                                        data-trip-url="{{ route('bus-trips.destroy', $busTrip) }}"
                                        title="Delete">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-5">
        <i class="ti ti-calendar-check" style="font-size: 48px; color: #ccc;"></i>
        <h5 class="mt-3">No Attendance Records Found</h5>
        <p class="text-muted">Try adjusting your filters or record a new attendance.</p>
        <a href="{{ route('bus-trips.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i>Record Attendance
        </a>
    </div>
@endif

