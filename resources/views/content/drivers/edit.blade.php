@extends('layouts/layoutMaster')

@section('title', 'Edit Driver')

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
    
@endsection

@section('page-script')
    <script src="{{ asset('assets/js/driver-form-ajax.js') }}"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-primary">
        <h5 class="card-title text-white">Edit Driver - {{ $driver->full_name }}</h5>
    </div>
    <div class="card-body">
        <form id="driverEditForm" action="{{ route('drivers.update', $driver) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Personal Information Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-user me-2"></i>1. Personal Information
                    </h6>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" id="full_name" 
                           class="form-control @error('full_name') is-invalid @enderror" 
                           value="{{ old('full_name', $driver->full_name) }}" required>
                    @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="father_name" class="form-label">Father's Name</label>
                    <input type="text" name="father_name" id="father_name" 
                           class="form-control @error('father_name') is-invalid @enderror" 
                           value="{{ old('father_name', $driver->father_name) }}">
                    @error('father_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" 
                           class="form-control @error('date_of_birth') is-invalid @enderror" 
                           value="{{ old('date_of_birth', $driver->date_of_birth?->format('Y-m-d')) }}">
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="national_id_passport" class="form-label">National ID / Passport No. <span class="text-danger">*</span></label>
                    <input type="text" name="national_id_passport" id="national_id_passport" 
                           class="form-control @error('national_id_passport') is-invalid @enderror" 
                           value="{{ old('national_id_passport', $driver->national_id_passport) }}" required>
                    @error('national_id_passport')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="photo" class="form-label">Photo (Upload)</label>
                    <input type="file" name="photo" id="photo" 
                           class="form-control @error('photo') is-invalid @enderror" 
                           accept="image/*">
                    @if($driver->photo)
                        <div class="mt-2">
                            <small class="text-muted">Current: </small>
                            <img src="{{ asset('storage/app/public/' . $driver->photo) }}" 
                                 alt="Current photo" class="img-thumbnail" width="50" height="50">
                        </div>
                    @endif
                    @error('photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                    <input type="text" name="contact_number" id="contact_number" 
                           class="form-control @error('contact_number') is-invalid @enderror" 
                           value="{{ old('contact_number', $driver->contact_number) }}" required>
                    @error('contact_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="alternative_contact_number" class="form-label">Alternative Contact Number</label>
                    <input type="text" name="alternative_contact_number" id="alternative_contact_number" 
                           class="form-control @error('alternative_contact_number') is-invalid @enderror" 
                           value="{{ old('alternative_contact_number', $driver->alternative_contact_number) }}">
                    @error('alternative_contact_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="email" class="form-label">Email (Optional)</label>
                    <input type="email" name="email" id="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email', $driver->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
          
              
       
                <div class="col-md-3">
                    <label for="religion_id" class="form-label">Religion</label>
                    <select name="religion_id" id="religion_id" class="form-select @error('religion_id') is-invalid @enderror">
                        <option value="">Select Religion</option>
                        @foreach($religions as $religion)
                            <option value="{{ $religion->id }}" 
                                    {{ old('religion_id', $driver->religion_id) == $religion->id ? 'selected' : '' }}>
                                {{ $religion->religion_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('religion_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="educational_qualification_id" class="form-label">Educational Qualification</label>
                    <select name="educational_qualification_id" id="educational_qualification_id" 
                            class="select2 form-select @error('educational_qualification_id') is-invalid @enderror">
                        <option value="">Select Educational Qualification</option>
                        @foreach($educationalQualifications as $value)
                            <option value="{{ $value->id }}" 
                                    {{ old('educational_qualification_id', $driver->educational_qualification_id) == $value->id ? 'selected' : '' }}>
                                {{ $value->qualification_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('educational_qualification_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="marital_status_id" class="form-label">Marital Status</label>
                    <select name="marital_status_id" id="marital_status_id" 
                            class="select2 form-select @error('marital_status_id') is-invalid @enderror">
                        <option value="">Select Marital Status</option>
                        @foreach($maritalStatuses as $value)
                            <option value="{{ $value->id }}" 
                                    {{ old('marital_status_id', $driver->marital_status_id) == $value->id ? 'selected' : '' }}>
                                {{ $value->marital_status_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('marital_status_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="experience_year_id" class="form-label">Driving Experience (Years)</label>
                    <select name="experience_year_id" id="experience_year_id" 
                            class="select2 form-select @error('experience_year_id') is-invalid @enderror">
                        <option value="">Select Experience</option>
                   
                        @foreach($experienceOptions as $value)
                            <option {{ old('experience_year_id', $driver->experience_year_id) == $value->id ? 'selected' : '' }} value="{{ $value->id }}">
                                {{ $value->experience_year }}
                            </option>
                        @endforeach
                    </select>
                    @error('experience_year_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="status_id" class="form-label">Status</label>
                    <select name="status_id" id="status_id"   
                            class="select2 form-select @error('status_id') is-invalid @enderror">
                        
                        @foreach($driverStatusOptions as $value)
                            <option value="{{ $value->id }}" 
                                    {{ old('status_id', $driver->status_id) == $value->id ? 'selected' : '' }}>
                                {{ $value->status_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('status_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="permanent_address" class="form-label">Permanent Address</label>
                    <textarea name="permanent_address" id="permanent_address" 
                              class="form-control @error('permanent_address') is-invalid @enderror" 
                              rows="3">{{ old('permanent_address', $driver->permanent_address) }}</textarea>
                    @error('permanent_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="present_address" class="form-label">Present Address</label>
                    <textarea name="present_address" id="present_address" 
                              class="form-control @error('present_address') is-invalid @enderror" 
                              rows="3">{{ old('present_address', $driver->present_address) }}</textarea>
                    @error('present_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="emergency_contact_person" class="form-label">Emergency Contact Person</label>
                    <input type="text" name="emergency_contact_person" id="emergency_contact_person" 
                           class="form-control @error('emergency_contact_person') is-invalid @enderror" 
                           value="{{ old('emergency_contact_person', $driver->emergency_contact_person) }}">
                    @error('emergency_contact_person')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
          
                <div class="col-md-3">
                    <label for="emergency_contact_number" class="form-label">Emergency Contact Number</label>
                    <input type="text" name="emergency_contact_number" id="emergency_contact_number" 
                           class="form-control @error('emergency_contact_number') is-invalid @enderror" 
                           value="{{ old('emergency_contact_number', $driver->emergency_contact_number) }}">
                    @error('emergency_contact_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="reference_name" class="form-label">Reference Name</label>
                    <input type="text" name="reference_name" id="reference_name" 
                           class="form-control @error('reference_name') is-invalid @enderror" 
                           value="{{ old('reference_name', $driver->reference_name) }}">
                    @error('reference_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label for="reference_contact_number" class="form-label">Reference Contact Number</label>
                    <input type="text" name="reference_contact_number" id="reference_contact_number" 
                           class="form-control @error('reference_contact_number') is-invalid @enderror" 
                           value="{{ old('reference_contact_number', $driver->reference_contact_number) }}">
                    @error('reference_contact_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- License Information Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-license me-2"></i>2. License Information
                    </h6>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="license_number" class="form-label">License Number <span class="text-danger">*</span></label>
                    <input type="text" name="license_number" id="license_number" 
                           class="form-control @error('license_number') is-invalid @enderror" 
                           value="{{ old('license_number', $driver->license_number) }}" required>
                    @error('license_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="license_type_id" class="form-label">License Type <span class="text-danger">*</span></label>
                    <select name="license_type_id" id="license_type_id" 
                            class="form-select @error('license_type_id') is-invalid @enderror" required>
                        <option value="">Select License Type</option>
                        @foreach($licenseTypes as $licenseType)
                            <option value="{{ $licenseType->id }}" 
                                    {{ old('license_type_id', $driver->license_type_id) == $licenseType->id ? 'selected' : '' }}>
                                {{ $licenseType->license_type_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('license_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="issuing_authority" class="form-label">Issuing Authority</label>
                    <select name="issuing_authority_id" id="issuing_authority_id" 
                            class="select2 form-select @error('issuing_authority_id') is-invalid @enderror">
                        <option value="">Select Issuing Authority</option>
                        @foreach($issuingAuthorities as $value)
                            <option value="{{ $value->id }}" 
                                    {{ old('issuing_authority_id', $driver->issuing_authority_id) == $value->id ? 'selected' : '' }}>
                                {{ $value->authority_name }}
                            </option>
                        @endforeach
                    </select>
                </div>      
                
                <div class="col-md-3">
                    <label for="license_issue_date" class="form-label">License Issue Date</label>
                    <input type="date" name="license_issue_date" id="license_issue_date" 
                           class="form-control @error('license_issue_date') is-invalid @enderror" 
                           value="{{ old('license_issue_date', $driver->license_issue_date?->format('Y-m-d')) }}">
                    @error('license_issue_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-5">
                <div class="col-md-3">
                    <label for="license_expiry_date" class="form-label">License Expiry Date</label>
                    <input type="date" name="license_expiry_date" id="license_expiry_date" 
                           class="form-control @error('license_expiry_date') is-invalid @enderror" 
                           value="{{ old('license_expiry_date', $driver->license_expiry_date?->format('Y-m-d')) }}">
                    @error('license_expiry_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="license_copy" class="form-label">License Copy (Upload)</label>
                    <input type="file" name="license_copy" id="license_copy" 
                           class="form-control @error('license_copy') is-invalid @enderror" 
                           accept=".pdf,.jpg,.png">
                    @if($driver->license_copy)
                        <div class="mt-2">
                            <small class="text-muted">Current: </small>
                            <a href="{{ asset('storage/' . $driver->license_copy) }}" target="_blank" class="text-primary">View File</a>
                        </div>
                    @endif
                    @error('license_copy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
            </div>

            <!-- Employment Details Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-briefcase me-2"></i>3. Employment Details
                    </h6>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="driver_unique_id" class="form-label">Unique ID</label>
                    <input type="text" name="driver_unique_id" id="driver_unique_id" 
                           class="form-control" value="{{ $driver->driver_unique_id }}" readonly>
                </div>
                
                <div class="col-md-3">
                    <label for="joining_date" class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" id="joining_date" 
                           class="form-control @error('joining_date') is-invalid @enderror" 
                           value="{{ old('joining_date', $driver->joining_date?->format('Y-m-d')) }}">
                    @error('joining_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="bank_account_number" class="form-label">Bank Account Number (Optional)</label>
                    <input type="text" name="bank_account_number" id="bank_account_number" 
                           class="form-control @error('bank_account_number') is-invalid @enderror" 
                           value="{{ old('bank_account_number', $driver->bank_account_number) }}">
                    @error('bank_account_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="driver_type_id" class="form-label">Driver Type <span class="text-danger">*</span></label>
                    <select name="driver_type_id" id="driver_type_id" 
                            class="form-select @error('driver_type_id') is-invalid @enderror" required>
                        <option value="">Select Driver Type</option>
                        @foreach($driverTypes as $driverType)
                            <option value="{{ $driverType->id }}" 
                                    {{ old('driver_type_id', $driver->driver_type_id) == $driverType->id ? 'selected' : '' }}>
                                {{ $driverType->driver_type_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('driver_type_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-5">
                <div class="col-md-3 mb-3" id="daily_salary_control">
                    <label for="daily_salary" class="form-label">Daily Salary</label>
                    <input type="number" class="form-control @error('daily_salary') is-invalid @enderror"
                           id="daily_salary" name="daily_salary"
                           value="{{ old('daily_salary', $driver->daily_salary ?? '') }}" min="0" step="0.01">
                    @error('daily_salary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" id="basic_salary_control">
                    <label for="basic_salary" class="form-label">Basic Salary</label>
                    <input type="number" class="form-control @error('basic_salary') is-invalid @enderror"
                           id="basic_salary" name="basic_salary"
                           value="{{ old('basic_salary', $driver->basic_salary) }}" min="0" step="0.01">
                    @error('basic_salary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" id="food_allowance_control">
                    <label for="food_allowance" class="form-label">Food Allowance</label>
                    <input type="number" class="form-control @error('food_allowance') is-invalid @enderror"
                           id="food_allowance" name="food_allowance"
                           value="{{ old('food_allowance', $driver->food_allowance ?? '') }}" min="0" step="0.01">
                    @error('food_allowance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" id="house_rent_control">
                    <label for="house_rent" class="form-label">House Rent</label>
                    <input type="number" class="form-control @error('house_rent') is-invalid @enderror"
                           id="house_rent" name="house_rent"
                           value="{{ old('house_rent', $driver->house_rent) }}" min="0" step="0.01">
                    @error('house_rent')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" id="medical_allowance_control">
                    <label for="medical_allowance" class="form-label">Medical Allowance</label>
                    <input type="number" class="form-control @error('medical_allowance') is-invalid @enderror"
                           id="medical_allowance" name="medical_allowance"
                           value="{{ old('medical_allowance', $driver->medical_allowance) }}" min="0" step="0.01">
                    @error('medical_allowance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" id="other_allowance_control">
                    <label for="other_allowance" class="form-label">Other Allowance</label>
                    <input type="number" class="form-control @error('other_allowance') is-invalid @enderror"
                           id="other_allowance" name="other_allowance"
                           value="{{ old('other_allowance', $driver->other_allowance) }}" min="0" step="0.01">
                    @error('other_allowance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" id="gross_salary_control">
                    <label for="gross_salary" class="form-label">Gross Salary (Auto-calculated)</label>
                    <input type="number" class="form-control @error('gross_salary') is-invalid @enderror"
                           id="gross_salary" name="gross_salary" readonly
                           value="{{ old('gross_salary', $driver->gross_salary) }}">
                    @error('gross_salary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Documents & Verification Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="ti ti-file-certificate me-2"></i>4. Documents & Verification
                    </h6>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="nid_copy" class="form-label">NID Copy (Upload)</label>
                    <input type="file" name="nid_copy" id="nid_copy" 
                           class="form-control @error('nid_copy') is-invalid @enderror" 
                           accept=".pdf,.jpg,.png">
                    @if($driver->nid_copy)
                        <div class="mt-2">
                            <small class="text-muted">Current: </small>
                            <a href="{{ asset('storage/' . $driver->nid_copy) }}" target="_blank" class="text-primary">View File</a>
                        </div>
                    @endif
                    @error('nid_copy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="police_verification_copy" class="form-label">Police Verification Copy (Upload)</label>
                    <input type="file" name="police_verification_copy" id="police_verification_copy" 
                           class="form-control @error('police_verification_copy') is-invalid @enderror" 
                           accept=".pdf,.jpg,.png">
                    @if($driver->police_verification_copy)
                        <div class="mt-2">
                            <small class="text-muted">Current: </small>
                            <a href="{{ asset('storage/' . $driver->police_verification_copy) }}" target="_blank" class="text-primary">View File</a>
                        </div>
                    @endif
                    @error('police_verification_copy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="medical_certificate" class="form-label">Medical Certificate (Upload)</label>
                    <input type="file" name="medical_certificate" id="medical_certificate" 
                           class="form-control @error('medical_certificate') is-invalid @enderror" 
                           accept=".pdf,.jpg,.png">
                    @if($driver->medical_certificate)
                        <div class="mt-2">
                            <small class="text-muted">Current: </small>
                            <a href="{{ asset('storage/' . $driver->medical_certificate) }}" target="_blank" class="text-primary">View File</a>
                        </div>
                    @endif
                    @error('medical_certificate')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Back to List
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"></span>
                            <span id="submitText">
                                <i class="ti ti-device-floppy me-1"></i>Update Driver
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
