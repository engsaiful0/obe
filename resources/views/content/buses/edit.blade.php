@extends('layouts/layoutMaster')

@section('title', 'Edit Bus')

@section('page-style')
<link href="{{ asset('assets/css/bus-form-ajax.css') }}" rel="stylesheet">
@endsection


@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Edit Bus - {{ $bus->display_name }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('buses.update', $bus) }}" method="POST" enctype="multipart/form-data" data-ajax-bus="true">
            @csrf
            @method('PUT')

            <!-- Basic Bus Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-bus me-2"></i>Basic Bus Information
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="bus_type_id" class="form-label">Bus Type <span class="text-danger">*</span></label>
                    <select name="bus_type_id" id="bus_type_id" class="select2 form-select @error('bus_type_id') is-invalid @enderror" required>
                        <option value="">Select Bus Type</option>
                        @foreach($busTypes as $type)
                        <option value="{{ $type->id }}" {{ (old('bus_type_id', $bus->bus_type_id) == $type->id) ? 'selected' : '' }}>
                            {{ $type->bus_type_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('bus_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="bus_sub_type_id" class="form-label">Bus Sub-Type <span class="text-danger">*</span></label>
                    <select name="bus_sub_type_id" id="bus_sub_type_id" class="select2 form-select @error('bus_sub_type_id') is-invalid @enderror" required>
                        <option value="">Select Bus Sub-Type</option>
                        @foreach($busSubTypes as $subType)
                        <option value="{{ $subType->id }}" data-name="{{ $subType->sub_type_name }}" {{ (old('bus_sub_type_id', $bus->bus_sub_type_id) == $subType->id) ? 'selected' : '' }}>
                            {{ $subType->sub_type_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('bus_sub_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3" id="fixed_price_field" style="display: none;">
                    <label for="fixed_price" class="form-label">Fixed Price (Two Trips)</label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" placeholder="Enter Fixed Price" step="0.01" min="0" name="fixed_price" id="fixed_price" class="form-control @error('fixed_price') is-invalid @enderror"
                            value="{{ old('fixed_price', $bus->fixed_price) }}" placeholder="0.00">
                    </div>
                    <div class="form-text">Price for both come and go trips</div>
                    @error('fixed_price')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3" id="rate_per_km_field" style="display: none;">
                    <label for="rate_per_km" class="form-label">Rate Per Kilometer</label>
                    <div class="input-group">
                        <span class="input-group-text">৳</span>
                        <input type="number" placeholder="Enter Rate Per Kilometer" step="0.01" min="0" name="rate_per_km" id="rate_per_km" class="form-control @error('rate_per_km') is-invalid @enderror"
                            value="{{ old('rate_per_km', $bus->rate_per_km) }}" placeholder="0.00">
                    </div>
                    <div class="form-text">Rate charged per kilometer traveled</div>
                    @error('rate_per_km')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="bus_number" class="form-label">Bus Number <span class="text-danger">*</span></label>
                    <input type="text" placeholder="Enter Bus Number" name="bus_number" id="bus_number" class="form-control @error('bus_number') is-invalid @enderror"
                        value="{{ old('bus_number', $bus->bus_number) }}" required>
                    @error('bus_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                  <div class="col-md-3">
                    <label for="seating_capacity" class="form-label">Seating Capacity</label>
                    <input type="number" placeholder="Enter Seating Capacity" name="seating_capacity" id="seating_capacity" class="form-control @error('seating_capacity') is-invalid @enderror"
                        value="{{ old('seating_capacity', $bus->seating_capacity) }}">
                    @error('seating_capacity')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="model_name" class="form-label">Model Name <span class="text-danger">*</span></label>
                    <input type="text" placeholder="Enter Model Name" name="model_name" id="model_name" class="form-control @error('model_name') is-invalid @enderror"
                        value="{{ old('model_name', $bus->model_name) }}" required>
                    @error('model_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="brand_id" class="form-label">Brand <span class="text-danger">*</span></label>
                    <select name="brand_id" id="brand_id" class="select2 form-select @error('brand_id') is-invalid @enderror" required>
                        <option value="">Select Brand</option>
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ (old('brand_id', $bus->brand_id) == $brand->id) ? 'selected' : '' }}>
                            {{ $brand->brand_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('brand_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="year_of_manufacture_id" class="form-label">Year of Manufacture <span class="text-danger">*</span></label>
                    <select name="year_of_manufacture_id" id="year_of_manufacture_id" class="select2 form-select @error('year_of_manufacture_id') is-invalid @enderror" required>
                        <option value="">Select Year</option>
                        @foreach($years as $year)
                        <option value="{{ $year->id }}" {{ (old('year_of_manufacture_id', $bus->year_of_manufacture_id) == $year->id) ? 'selected' : '' }}>
                            {{ $year->year_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('year_of_manufacture_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="color_id" class="form-label">Color <span class="text-danger">*</span></label>
                    <select name="color_id" id="color_id" class="select2 form-select @error('color_id') is-invalid @enderror" required>
                        <option value="">Select Color</option>
                        @foreach($colors as $color)
                        <option value="{{ $color->id }}" {{ (old('color_id', $bus->color_id) == $color->id) ? 'selected' : '' }}>
                            {{ $color->color_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('color_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="fuel_type_id" class="form-label">Fuel Type <span class="text-danger">*</span></label>
                    <select name="fuel_type_id" id="fuel_type_id" class="select2 form-select @error('fuel_type_id') is-invalid @enderror" required>
                        <option value="">Select Fuel Type</option>
                        @foreach($fuelTypes as $fuelType)
                        <option value="{{ $fuelType->id }}" {{ (old('fuel_type_id', $bus->fuel_type_id) == $fuelType->id) ? 'selected' : '' }}>
                            {{ $fuelType->fuel_type_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('fuel_type_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="chassis_number" class="form-label">Chassis Number <span class="text-danger">*</span></label>
                    <input type="text" placeholder="Enter Chassis Number" name="chassis_number" id="chassis_number" class="form-control @error('chassis_number') is-invalid @enderror"
                        value="{{ old('chassis_number', $bus->chassis_number) }}" required>
                    @error('chassis_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="engine_number" class="form-label">Engine Number <span class="text-danger">*</span></label>
                    <input type="text" placeholder="Enter Engine Number" name="engine_number" id="engine_number" class="form-control @error('engine_number') is-invalid @enderror"
                        value="{{ old('engine_number', $bus->engine_number) }}" required>
                    @error('engine_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="required_oil_per_km" class="form-label">Required Oil Per Kilometer<span class="text-danger">*</span></label>
                    <input type="text" placeholder="Enter Required Oil Per Kilometer" name="required_oil_per_km" id="required_oil_per_km" class="form-control @error('required_oil_per_km') is-invalid @enderror"
                        value="{{ old('required_oil_per_km', $bus->required_oil_per_km) }}" required>
                    @error('required_oil_per_km')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Registration & Legal Details -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-file-text me-2"></i>Registration & Legal Details
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="registration_number" class="form-label">Registration Number <span class="text-danger">*</span></label>
                    <input type="text" placeholder="Enter Registration Number" name="registration_number" id="registration_number" class="form-control @error('registration_number') is-invalid @enderror"
                        value="{{ old('registration_number', $bus->registration_number) }}" required>
                    @error('registration_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="registration_date" class="form-label">Registration Date</label>
                    <input type="date" placeholder="Enter Registration Date" name="registration_date" id="registration_date" class="form-control @error('registration_date') is-invalid @enderror"
                        value="{{ old('registration_date', $bus->registration_date?->format('Y-m-d')) }}">
                    @error('registration_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="registration_expiry" class="form-label">Registration Expiry</label>
                    <input type="date" placeholder="Enter Registration Expiry" name="registration_expiry" id="registration_expiry" class="form-control @error('registration_expiry') is-invalid @enderror"
                        value="{{ old('registration_expiry', $bus->registration_expiry?->format('Y-m-d')) }}">
                    @error('registration_expiry')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="insurance_number" class="form-label">Insurance Number</label>
                    <input type="text" placeholder="Enter Insurance Number" name="insurance_number" id="insurance_number" class="form-control @error('insurance_number') is-invalid @enderror"
                        value="{{ old('insurance_number', $bus->insurance_number) }}">
                    @error('insurance_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="insurance_company" class="form-label">Insurance Company</label>
                    <input type="text" placeholder="Enter Insurance Company" name="insurance_company" id="insurance_company" class="form-control @error('insurance_company') is-invalid @enderror"
                        value="{{ old('insurance_company', $bus->insurance_company) }}">
                    @error('insurance_company')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="insurance_expiry" class="form-label">Insurance Expiry</label>
                    <input type="date" placeholder="Enter Insurance Expiry" name="insurance_expiry" id="insurance_expiry" class="form-control @error('insurance_expiry') is-invalid @enderror"
                        value="{{ old('insurance_expiry', $bus->insurance_expiry?->format('Y-m-d')) }}">
                    @error('insurance_expiry')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="fitness_certificate_number" class="form-label">Fitness Certificate Number</label>
                    <input type="text" placeholder="Enter Fitness Certificate Number" name="fitness_certificate_number" id="fitness_certificate_number" class="form-control @error('fitness_certificate_number') is-invalid @enderror"
                        value="{{ old('fitness_certificate_number', $bus->fitness_certificate_number) }}">
                    @error('fitness_certificate_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="fitness_expiry" class="form-label">Fitness Expiry</label>
                    <input type="date" placeholder="Enter Fitness Expiry" name="fitness_expiry" id="fitness_expiry" class="form-control @error('fitness_expiry') is-invalid @enderror"
                        value="{{ old('fitness_expiry', $bus->fitness_expiry?->format('Y-m-d')) }}">
                    @error('fitness_expiry')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="permit_number" class="form-label">Permit Number</label>
                    <input type="text" placeholder="Enter Permit Number" name="permit_number" id="permit_number" class="form-control @error('permit_number') is-invalid @enderror"
                        value="{{ old('permit_number', $bus->permit_number) }}">
                    @error('permit_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="permit_expiry" class="form-label">Permit Expiry</label>
                    <input type="date" placeholder="Enter Permit Expiry" name="permit_expiry" id="permit_expiry" class="form-control @error('permit_expiry') is-invalid @enderror"
                        value="{{ old('permit_expiry', $bus->permit_expiry?->format('Y-m-d')) }}">
                    @error('permit_expiry')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Owner & Driver Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-user me-2"></i>Owner & Driver Information
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="supplier_id" class="form-label">Owner/Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                        <option value="">Select Owner/Supplier</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ (old('supplier_id', $bus->supplier_id) == $supplier->id) ? 'selected' : '' }}>
                            {{ $supplier->supplier_name }} - {{ $supplier->mobile }}
                        </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4">
                    <label for="driver_id" class="form-label">Driver *</label>
                    <select name="driver_id" id="driver_id" class="form-select @error('driver_id') is-invalid @enderror">
                        <option value="">Select Driver (Optional)</option>
                        @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ (old('driver_id', $bus->driver_id) == $driver->id) ? 'selected' : '' }}>
                            {{ $driver->full_name }} ({{ $driver->driver_unique_id ?? 'N/A' }})
                        </option>
                        @endforeach
                    </select>
                    @error('driver_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4">
                    <label for="bus_helper_id" class="form-label">Bus Helper *</label>
                    <select name="bus_helper_id" id="bus_helper_id" class="form-select @error('bus_helper_id') is-invalid @enderror">
                        <option value="">Select Bus Helper (Optional)</option>
                        @foreach($busHelpers as $busHelper)
                        <option value="{{ $busHelper->id }}" {{ (old('bus_helper_id', $bus->bus_helper_id) == $busHelper->id) ? 'selected' : '' }}>
                            {{ $busHelper->bus_helper_name }} ({{ $busHelper->bus_helper_id ?? 'N/A' }})
                        </option>
                        @endforeach
                    </select>
                    @error('bus_helper_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Technical Specifications -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-settings me-2"></i>Technical Specifications
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="engine_capacity" class="form-label">Engine Capacity (CC)</label>
                    <input type="number" placeholder="Enter Engine Capacity" step="0.01" name="engine_capacity" id="engine_capacity" class="form-control @error('engine_capacity') is-invalid @enderror"
                        value="{{ old('engine_capacity', $bus->engine_capacity) }}">
                    @error('engine_capacity')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="transmission_type" class="form-label">Transmission Type <span class="text-danger">*</span></label>
                    <select name="transmission_type" id="transmission_type" class="select2 form-select @error('transmission_type') is-invalid @enderror" required>
                        <option value="">Select Transmission Type</option>
                        @foreach($transmissionOptions as $value => $label)
                        <option value="{{ $value }}" {{ (old('transmission_type', $bus->transmission_type) == $value) ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                    @error('transmission_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

              

                <div class="col-md-3">
                    <label for="gross_weight" class="form-label">Gross Weight (kg)</label>
                    <input type="number" placeholder="Enter Gross Weight" step="0.01" name="gross_weight" id="gross_weight" class="form-control @error('gross_weight') is-invalid @enderror"
                        value="{{ old('gross_weight', $bus->gross_weight) }}">
                    @error('gross_weight')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="bus_length" class="form-label">Length (meters)</label>
                    <input type="number" placeholder="Enter Length" step="0.01" name="bus_length" id="bus_length" class="form-control @error('bus_length') is-invalid @enderror"
                        value="{{ old('bus_length', $bus->bus_length) }}">
                    @error('bus_length')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="bus_height" class="form-label">Height (meters)</label>
                    <input type="number" placeholder="Enter Height" step="0.01" name="bus_height" id="bus_height" class="form-control @error('bus_height') is-invalid @enderror"
                        value="{{ old('bus_height', $bus->bus_height) }}">
                    @error('bus_height')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="bus_width" class="form-label">Width (meters)</label>
                    <input type="number" placeholder="Enter Width" step="0.01" name="bus_width" id="bus_width" class="form-control @error('bus_width') is-invalid @enderror"
                        value="{{ old('bus_width', $bus->bus_width) }}">
                    @error('bus_width')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Operational Details -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-calendar me-2"></i>Operational Details
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="purchase_date" class="form-label">Purchase Date</label>
                    <input type="date" placeholder="Enter Purchase Date" name="purchase_date" id="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror"
                        value="{{ old('purchase_date', $bus->purchase_date?->format('Y-m-d')) }}">
                    @error('purchase_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>



                <div class="col-md-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status_id" id="status_id" class="select2 form-select @error('status_id') is-invalid @enderror" required>
                        <option value="">Select Status</option>
                        @foreach($statusOptions as $status)
                        <option value="{{ $status->id }}" {{ (old('status_id', $bus->status_id) == $status->id) ? 'selected' : '' }}>
                            {{ $status->status_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('status_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="current_mileage" class="form-label">Current Mileage</label>
                    <input type="number" placeholder="Enter Current Mileage" step="0.01" name="current_mileage" id="current_mileage" class="form-control @error('current_mileage') is-invalid @enderror"
                        value="{{ old('current_mileage', $bus->current_mileage) }}">
                    @error('current_mileage')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="last_service_date" class="form-label">Last Service Date</label>
                    <input type="date" placeholder="Enter Last Service Date" name="last_service_date" id="last_service_date" class="form-control @error('last_service_date') is-invalid @enderror"
                        value="{{ old('last_service_date', $bus->last_service_date?->format('Y-m-d')) }}">
                    @error('last_service_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="next_service_due" class="form-label">Next Service Due</label>
                    <input type="date" placeholder="Enter Next Service Due" name="next_service_due" id="next_service_due" class="form-control @error('next_service_due') is-invalid @enderror"
                        value="{{ old('next_service_due', $bus->next_service_due?->format('Y-m-d')) }}">
                    @error('next_service_due')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Attachments -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-paperclip me-2"></i>Attachments
                    </h6>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="bus_photo" class="form-label">Bus Photo</label>
                    @if($bus->bus_photo)
                    <div class="mb-2">
                        <img src="{{ asset('storage/app/public/' . $bus->bus_photo) }}" alt="Current Photo" class="img-thumbnail" style="max-width: 100px;">
                        <p class="text-muted small">Current photo</p>
                    </div>
                    @endif
                    <input type="file" name="bus_photo" id="bus_photo" class="form-control @error('bus_photo') is-invalid @enderror"
                        accept="image/*">
                    <div class="form-text">Max size: 2MB, Formats: JPEG, PNG, JPG, GIF</div>
                    @error('bus_photo')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="registration_document" class="form-label">Registration Document</label>
                    @if($bus->registration_document)
                    <div class="mb-2">
                        <a href="{{ asset('storage/app/public/' . $bus->registration_document) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-file-text me-1"></i>View Current Document
                        </a>
                    </div>
                    @endif
                    <input type="file" name="registration_document" id="registration_document" class="form-control @error('registration_document') is-invalid @enderror"
                        accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Max size: 5MB, Formats: PDF, JPEG, PNG, JPG</div>
                    @error('registration_document')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="insurance_document" class="form-label">Insurance Document</label>
                    @if($bus->insurance_document)
                    <div class="mb-2">
                        <a href="{{ asset('storage/app/public/' . $bus->insurance_document) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-file-text me-1"></i>View Current Document
                        </a>
                    </div>
                    @endif
                    <input type="file" name="insurance_document" id="insurance_document" class="form-control @error('insurance_document') is-invalid @enderror"
                        accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Max size: 5MB, Formats: PDF, JPEG, PNG, JPG</div>
                    @error('insurance_document')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="fitness_certificate" class="form-label">Fitness Certificate</label>
                    @if($bus->fitness_certificate)
                    <div class="mb-2">
                        <a href="{{ asset('storage/app/public/' . $bus->fitness_certificate) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-file-text me-1"></i>View Current Document
                        </a>
                    </div>
                    @endif
                    <input type="file" name="fitness_certificate" id="fitness_certificate" class="form-control @error('fitness_certificate') is-invalid @enderror"
                        accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">Max size: 5MB, Formats: PDF, JPEG, PNG, JPG</div>
                    @error('fitness_certificate')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('buses.index') }}" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary" id="submit-bus">
                            <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                            <i class="ti ti-check me-1"></i>Update Bus
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')

<script>
    $(document).ready(function() {

        // AJAX Bus Form Submission
        $('form[data-ajax-bus="true"]').on('submit', function(e) {
            e.preventDefault();

            let form = $(this);
            let submitBtn = $('#submit-bus');
            let spinner = submitBtn.find('.spinner-border');

            // Prepare FormData for file uploads
            let formData = new FormData(this);

            // Disable submit button & show spinner
            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');

            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    spinner.addClass('d-none');
                    submitBtn.prop('disabled', false);

                    // Show success message (customize as needed)
                  toastr.success(response.message || 'The bus record was updated successfully.');

                    // Redirect after success (optional)
                    setTimeout(() => {
                        window.location.href = "{{ route('buses.index') }}";
                    }, 2000);
                },
                error: function(xhr) {
                    spinner.addClass('d-none');
                    submitBtn.prop('disabled', false);

                    if (xhr.status === 422) {
                        // Validation error
                        let errors = xhr.responseJSON.errors;
                        $('.invalid-feedback').remove();
                        $('.is-invalid').removeClass('is-invalid');

                        $.each(errors, function(key, value) {
                            let input = $('[name="' + key + '"]');
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + value[0] + '</div>');
                        });
                      toastr.error('Please check the highlighted fields.');
                    } else {
                      toastr.error(xhr.responseJSON?.message || 'Something went wrong while saving data.');
                    }
                }
            });
        });
    });
</script>

@endsection