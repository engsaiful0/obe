@extends('layouts/layoutMaster')

@section('title', 'Bus Helper Details')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title">Bus Helper Details - {{ $busHelper->bus_helper_name }}</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('bus-helpers.edit', $busHelper) }}" class="btn btn-primary">
                <i class="ti ti-pencil me-1"></i>Edit
            </a>
            <a href="{{ route('bus-helpers.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-1"></i>Back to List
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Profile Section -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        @if($busHelper->picture)
                            <img src="{{ asset('storage/' . $busHelper->picture) }}" alt="{{ $busHelper->bus_helper_name }}" 
                                 class="rounded-circle mb-3" width="120" height="120" style="object-fit: cover;">
                        @else
                            <div class="avatar-initial rounded-circle bg-label-primary mx-auto mb-3" 
                                 style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                {{ strtoupper(substr($busHelper->bus_helper_name, 0, 1)) }}
                            </div>
                        @endif
                        <h5 class="mb-1">{{ $busHelper->bus_helper_name }}</h5>
                        <p class="text-muted mb-2">{{ $busHelper->bus_helper_id }}</p>
                        @if(isset($busHelper->status))
                            @if($busHelper->status == 'active')
                                <span class="badge bg-label-success mb-2">Active</span>
                            @else
                                <span class="badge bg-label-danger mb-2">Inactive</span>
                            @endif
                        @else
                            <span class="badge bg-label-success mb-2">Active</span>
                        @endif
                        <br>
                        <span class="badge bg-label-{{ $busHelper->assignedBus ? 'success' : 'warning' }}">
                            {{ $busHelper->assignedBus ? 'Assigned' : 'Unassigned' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Details Section -->
            <div class="col-md-8">
                <!-- Personal Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Personal Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Father's Name</label>
                                <p class="mb-0">{{ $busHelper->father_name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Mother's Name</label>
                                <p class="mb-0">{{ $busHelper->mother_name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Mobile Number</label>
                                <p class="mb-0">{{ $busHelper->mobile }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">NID Number</label>
                                <p class="mb-0">{{ $busHelper->nid_number }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Gender</label>
                                <p class="mb-0">
                                    <span class="badge bg-label-info">{{ $busHelper->gender->gender_name ?? 'N/A' }}</span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Marital Status</label>
                                <p class="mb-0">
                                    <span class="badge bg-label-secondary">{{ $busHelper->maritalStatus->marital_status_name ?? 'N/A' }}</span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Religion</label>
                                <p class="mb-0">
                                    <span class="badge bg-label-primary">{{ $busHelper->religion->religion_name ?? 'N/A' }}</span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <p class="mb-0">
                                    @if(isset($busHelper->status))
                                        @if($busHelper->status == 'active')
                                            <span class="badge bg-label-success">Active</span>
                                        @else
                                            <span class="badge bg-label-danger">Inactive</span>
                                        @endif
                                    @else
                                        <span class="badge bg-label-success">Active</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Address Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">Present Address</label>
                                <p class="mb-0">{{ $busHelper->present_address }}</p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-semibold">Permanent Address</label>
                                <p class="mb-0">{{ $busHelper->permanent_address }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Academic & Experience Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Academic & Experience Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Academic Qualification</label>
                                <p class="mb-0">{{ $busHelper->academic_qualification }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Years of Experience</label>
                                <p class="mb-0">
                                    <span class="badge bg-label-primary">{{ $busHelper->years_of_experience }} years</span>
                                    <small class="text-muted ms-2">({{ $busHelper->experience_level }})</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employment Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Employment Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Employee Type</label>
                                <p class="mb-0">
                                    <span class="badge bg-label-secondary">{{ $busHelper->employeeType->employee_type_name ?? 'N/A' }}</span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Assigned Bus</label>
                                <p class="mb-0">
                                    @if($busHelper->assignedBus)
                                        <span class="badge bg-label-success">{{ $busHelper->assignedBus->model_name }} ({{ $busHelper->assignedBus->registration_number }})</span>
                                    @else
                                        <span class="badge bg-label-warning">No Bus Assigned</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Salary Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Basic Salary</label>
                                <p class="mb-0 fw-semibold">৳{{ number_format($busHelper->basic_salary, 2) }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">House Rent</label>
                                <p class="mb-0 fw-semibold">৳{{ number_format($busHelper->house_rent, 2) }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Medical Allowance</label>
                                <p class="mb-0 fw-semibold">৳{{ number_format($busHelper->medical_allowance, 2) }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Other Allowance</label>
                                <p class="mb-0 fw-semibold">৳{{ number_format($busHelper->other_allowance, 2) }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Gross Salary</label>
                                <p class="mb-0 fw-bold text-primary fs-5">৳{{ number_format($busHelper->gross_salary, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                @if($busHelper->nid_copy)
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Documents</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">NID Copy</label>
                                <div>
                                    <a href="{{ asset('storage/' . $busHelper->nid_copy) }}" target="_blank" class="btn btn-outline-primary">
                                        <i class="ti ti-file-text me-1"></i>View NID Copy
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Bus Assignment Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Bus Assignment</h6>
                    </div>
                    <div class="card-body">
                        @if($busHelper->assignedBus)
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="mb-1">{{ $busHelper->assignedBus->model_name }}</h6>
                                    <p class="text-muted mb-0">Registration: {{ $busHelper->assignedBus->registration_number }}</p>
                                </div>
                                <button type="button" class="btn btn-warning" onclick="unassignBus({{ $busHelper->id }})">
                                    <i class="ti ti-user-x me-1"></i>Unassign Bus
                                </button>
                            </div>
                        @else
                            <div class="text-center">
                                <i class="ti ti-car text-muted mb-3" style="font-size: 3rem;"></i>
                                <h6 class="text-muted">No Bus Assigned</h6>
                                <button type="button" class="btn btn-primary" onclick="assignBus({{ $busHelper->id }})">
                                    <i class="ti ti-car me-1"></i>Assign Bus
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bus Assignment Modal -->
<div class="modal fade" id="assignBusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Bus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignBusForm">
                    <input type="hidden" id="assistantId" name="assistant_id">
                    <div class="mb-3">
                        <label for="busSelect" class="form-label">Select Bus</label>
                        <select class="form-select" id="busSelect" name="assigned_bus_id" required>
                            <option value="">Choose a bus...</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmAssignBus()">Assign Bus</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
// Bus assignment functions
function assignBus(busHelperId) {
    document.getElementById('busHelperId').value = busHelperId;
    
    // Load available buses
    fetch('/app/buses?status=active')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('busSelect');
            select.innerHTML = '<option value="">Choose a bus...</option>';
            data.buses.forEach(bus => {
                const option = document.createElement('option');
                option.value = bus.id;
                option.textContent = `${bus.model_name} (${bus.registration_number})`;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading buses:', error));
    
    const modal = new bootstrap.Modal(document.getElementById('assignBusModal'));
    modal.show();
}

function confirmAssignBus() {
    const form = document.getElementById('assignBusForm');
    const formData = new FormData(form);
    const busHelperId = document.getElementById('busHelperId').value;
    
    fetch(`/app/bus-helpers/${busHelperId}/assign-bus`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error assigning bus: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error assigning bus');
    });
}

function unassignBus(busHelperId) {
    if (confirm('Are you sure you want to unassign the bus from this bus helper?')) {
        fetch(`/app/bus-helpers/${busHelperId}/unassign-bus`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error unassigning bus: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error unassigning bus');
        });
    }
}
</script>
@endsection
