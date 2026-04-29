@extends('layouts/layoutMaster')

@section('title', 'Edit Bus Requisition')

@section('page-script')
<script>
    window.busRequisitionUrls = {
        update: '{{ route('app-bus-requisitions.update', $busRequisition->id) }}',
        index: '{{ route('app-bus-requisitions') }}'
    };
</script>
<script src="{{ asset('assets/js/bus-requisition-edit.js') }}?v={{ time() }}"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Edit Bus Requisition - ID: {{ $busRequisition->id }}</h5>
            <a href="{{ route('app-bus-requisitions') }}" class="btn btn-label-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            <form id="bus-requisition-form" method="POST" action="{{ route('app-bus-requisitions.update', $busRequisition->id) }}">
                @csrf
                @method('PUT')
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="date">Date <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="date2" class="input-group-text"><i class="ti ti-calendar"></i></span>
                            <input type="date" id="date" class="form-control @error('date') is-invalid @enderror" 
                                   name="date" value="{{ old('date', $busRequisition->date ? $busRequisition->date->format('Y-m-d') : '') }}" 
                                   aria-label="Select Date" aria-describedby="date2" required />
                        </div>
                        @error('date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="department_id">Department <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="department_id2" class="input-group-text"><i class="ti ti-building"></i></span>
                            <select id="department_id" class="form-select @error('department_id') is-invalid @enderror" 
                                    name="department_id" aria-label="Select Department" aria-describedby="department_id2" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id', $busRequisition->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @error('department_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="purpose">Purpose <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="purpose2" class="input-group-text"><i class="ti ti-file-text"></i></span>
                            <textarea id="purpose" class="form-control @error('purpose') is-invalid @enderror" 
                                      name="purpose" rows="3" placeholder="Enter purpose of requisition" 
                                      aria-label="Enter Purpose" aria-describedby="purpose2" required>{{ old('purpose', $busRequisition->purpose) }}</textarea>
                        </div>
                        @error('purpose')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="required_bus_date">Required Bus Date <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="required_bus_date2" class="input-group-text"><i class="ti ti-calendar-event"></i></span>
                            <input type="date" id="required_bus_date" class="form-control @error('required_bus_date') is-invalid @enderror" 
                                   name="required_bus_date" value="{{ old('required_bus_date', $busRequisition->required_bus_date ? $busRequisition->required_bus_date->format('Y-m-d') : '') }}" 
                                   aria-label="Select Required Bus Date" aria-describedby="required_bus_date2" required />
                        </div>
                        @error('required_bus_date')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="required_time">Required Time <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="required_time2" class="input-group-text"><i class="ti ti-clock"></i></span>
                            <input type="time" id="required_time" class="form-control @error('required_time') is-invalid @enderror" 
                                   name="required_time" value="{{ old('required_time', $busRequisition->required_time) }}" 
                                   aria-label="Select Required Time" aria-describedby="required_time2" required />
                        </div>
                        @error('required_time')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="number_of_buses">Number of Buses <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="number_of_buses2" class="input-group-text"><i class="ti ti-bus"></i></span>
                            <input type="number" id="number_of_buses" class="form-control @error('number_of_buses') is-invalid @enderror" 
                                   name="number_of_buses" placeholder="Enter number of buses" min="1" 
                                   value="{{ old('number_of_buses', $busRequisition->number_of_buses) }}" 
                                   aria-label="Enter Number of Buses" aria-describedby="number_of_buses2" required />
                        </div>
                        @error('number_of_buses')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="total_passengers">Total Passengers <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="total_passengers2" class="input-group-text"><i class="ti ti-users"></i></span>
                            <input type="number" id="total_passengers" class="form-control @error('total_passengers') is-invalid @enderror" 
                                   name="total_passengers" placeholder="Enter total passengers" min="1" 
                                   value="{{ old('total_passengers', $busRequisition->total_passengers) }}" 
                                   aria-label="Enter Total Passengers" aria-describedby="total_passengers2" required />
                        </div>
                        @error('total_passengers')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="requisition_sender_name">Requisition Sender Name <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="requisition_sender_name2" class="input-group-text"><i class="ti ti-user"></i></span>
                            <input type="text" id="requisition_sender_name" class="form-control @error('requisition_sender_name') is-invalid @enderror" 
                                   name="requisition_sender_name" placeholder="Enter sender name" 
                                   value="{{ old('requisition_sender_name', $busRequisition->requisition_sender_name) }}" 
                                   aria-label="Enter Sender Name" aria-describedby="requisition_sender_name2" required />
                        </div>
                        @error('requisition_sender_name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="mobile_number">Mobile Number <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="mobile_number2" class="input-group-text"><i class="ti ti-phone"></i></span>
                            <input type="text" id="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror" 
                                   name="mobile_number" placeholder="Enter mobile number" 
                                   value="{{ old('mobile_number', $busRequisition->mobile_number) }}" 
                                   aria-label="Enter Mobile Number" aria-describedby="mobile_number2" required />
                        </div>
                        @error('mobile_number')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" for="email_address">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="email_address2" class="input-group-text"><i class="ti ti-mail"></i></span>
                            <input type="email" id="email_address" class="form-control @error('email_address') is-invalid @enderror" 
                                   name="email_address" placeholder="Enter email address" 
                                   value="{{ old('email_address', $busRequisition->email_address) }}" 
                                   aria-label="Enter Email Address" aria-describedby="email_address2" required />
                        </div>
                        @error('email_address')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="remarks">Remarks</label>
                        <div class="input-group input-group-merge">
                            <span id="remarks2" class="input-group-text"><i class="ti ti-note"></i></span>
                            <textarea id="remarks" class="form-control @error('remarks') is-invalid @enderror" 
                                      name="remarks" rows="3" placeholder="Enter remarks (optional)" 
                                      aria-label="Enter Remarks" aria-describedby="remarks2">{{ old('remarks', $busRequisition->remarks) }}</textarea>
                        </div>
                        @error('remarks')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('app-bus-requisitions') }}" class="btn btn-label-secondary">
                                <i class="ti ti-x me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                                <i class="ti ti-check me-1"></i> Update
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

