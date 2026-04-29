<div class="table-responsive">
    <table class="table table-striped table-hover table-bordered">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Bus Helper ID</th>
                <th>Name & Details</th>
                <th>Contact</th>
                <th>Gender</th>
                <th>Employee Type</th>
                <th>Status</th>
                <th>Experience</th>
                <th>Gross Salary</th>
                @if(auth()->user()->hasPermissionTo('bus-helper-view') || auth()->user()->hasPermissionTo('bus-helper-edit') || auth()->user()->hasPermissionTo('bus-helper-delete'))
                <th>Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($busHelpers as $busHelper)
                <tr>
                    <td>{{ $busHelpers->firstItem() + $loop->index }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                        <span class="fw-semibold">{{ $busHelper->bus_helper_id }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if($busHelper->picture)
                                <img src="{{ asset('storage/' . $busHelper->picture) }}" alt="{{ $busHelper->bus_helper_name }}" class="rounded-circle me-2" width="32" height="32">
                            @else
                                <div class="avatar-initial rounded-circle bg-label-primary me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                    {{ strtoupper(substr($busHelper->bus_helper_name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $busHelper->bus_helper_name }}</h6>
                                <small class="text-muted">{{ $busHelper->father_name }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>
                            <span class="fw-semibold">{{ $busHelper->mobile }}</span><br>
                            <small class="text-muted">NID: {{ $busHelper->nid_number }}</small>
                        </div>
                    </td>
                    <td>
                        @if($busHelper->gender)
                            <span class="badge bg-label-info">{{ $busHelper->gender->gender_name }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if($busHelper->employeeType)
                            <span class="badge bg-label-secondary">{{ $busHelper->employeeType->employee_type_name }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if(isset($busHelper->status))
                            @if($busHelper->status == 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                        @else
                            <span class="badge bg-label-success">Active</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-label-primary">{{ $busHelper->years_of_experience }} years</span>
                    </td>
                    <td>
                        <span class="fw-semibold text-success">৳{{ number_format($busHelper->gross_salary, 2) }}</span>
                    </td>
                    @if(auth()->user()->hasPermissionTo('bus-helper-view') || auth()->user()->hasPermissionTo('bus-helper-edit') || auth()->user()->hasPermissionTo('bus-helper-delete'))
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                @if(auth()->user()->hasPermissionTo('bus-helper-view'))
                                <a class="dropdown-item" href="{{ route('bus-helpers.show', $busHelper) }}">
                                    <i class="ti ti-eye me-1"></i> View
                                </a>
                                @endif
                                @if(auth()->user()->hasPermissionTo('bus-helper-edit'))
                                <a class="dropdown-item" href="{{ route('bus-helpers.edit', $busHelper) }}">
                                    <i class="ti ti-pencil me-1"></i> Edit
                                </a>
                                @endif
                                @if(auth()->user()->hasPermissionTo('bus-helper-delete'))
                          
                                <div class="dropdown-divider"></div>
                                <button class="dropdown-item text-danger btn-delete-bus-helper" type="button" data-bus-helper-id="{{ $busHelper->id }}">
                                    <i class="ti ti-trash me-1"></i> Delete
                                </button>
                                @endif
                            </div>
                        </div>
                    </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <i class="ti ti-users-off text-muted mb-3" style="font-size: 3rem;"></i>
                            <h6 class="text-muted">No bus helpers found</h6>
                            <p class="text-muted">
                                @if(request()->hasAny(['search', 'gender_filter', 'employee_type_filter', 'status_filter', 'experience_filter', 'min_salary', 'max_salary']))
                                    Try adjusting your search criteria or 
                                    <a href="{{ route('bus-helpers.index') }}">clear all filters</a>.
                                @else
                                    Start by <a href="{{ route('bus-helpers.create') }}">adding your first bus helper</a>.
                                @endif
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

