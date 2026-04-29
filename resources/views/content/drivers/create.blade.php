@extends('layouts/layoutMaster')

@section('title', 'Add Driver')

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>
    <script src="{{ asset('assets/js/driver-form-ajax.js') }}"></script>
@endsection

@section('content')
<div class="card">
    <div class="card-header bg-success">
        <h5 class="card-title text-white">Add New Driver</h5>
    </div>
    <div class="card-body">
        <form id="driverForm" action="{{ route('drivers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
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
                    <input type="text" autofocus name="full_name" id="full_name" 
                           class="form-control @error('full_name') is-invalid @enderror" 
                           value="{{ old('full_name') }}" required minlength="2" maxlength="100"
                           placeholder="Enter full name">
                    @error('full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="father_name" class="form-label">Father's Name</label>
                    <input placeholder="Enter Father's Name" type="text" name="father_name" id="father_name" 
                           class="form-control @error('father_name') is-invalid @enderror" 
                           value="{{ old('father_name') }}">
                    @error('father_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" 
                           class="form-control @error('date_of_birth') is-invalid @enderror" 
                           value="{{ old('date_of_birth', '2000-01-01') }}">
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="driver_unique_id" class="form-label">Unique ID</label>
                    <input placeholder="Enter ID " type="text" name="driver_unique_id" id="driver_unique_id" 
                           class="form-control">
                </div>
                <div class="col-md-3">
                    <label for="national_id_passport" class="form-label">National ID / Passport No. <span class="text-danger">*</span></label>
                    <input placeholder="Enter National ID / Passport No." type="text" name="national_id_passport" id="national_id_passport" 
                           class="form-control @error('national_id_passport') is-invalid @enderror" 
                           value="{{ old('national_id_passport') }}" required>
                    @error('national_id_passport')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
          
                <div class="col-md-3">
                    <label for="photo" class="form-label">Photo (Upload)</label>
                    <input type="file" name="photo" id="photo" 
                           class="form-control @error('photo') is-invalid @enderror">
                    @error('photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="contact_number" class="form-label">Contact Number <span class="text-danger">*</span></label>
                    <input type="tel" name="contact_number" id="contact_number" 
                           class="form-control @error('contact_number') is-invalid @enderror" 
                           value="{{ old('contact_number') }}" required pattern="[0-9+\-\s()]{10,15}"
                           placeholder="01XXXXXXXXX" maxlength="15">
                    @error('contact_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="alternative_contact_number" class="form-label">Alternative Contact Number</label>
                    <input placeholder="Enter Alternative Contact Number" type="text" name="alternative_contact_number" id="alternative_contact_number" 
                           class="form-control @error('alternative_contact_number') is-invalid @enderror" 
                           value="{{ old('alternative_contact_number') }}">
                    @error('alternative_contact_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="email" class="form-label">Email (Optional)</label>
                    <input type="email" name="email" id="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}" placeholder="example@email.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
         
                <div class="col-md-3">
                    <label for="emergency_contact_number" class="form-label">Religion</label>
                    <select name="religion_id" id="religion_id" class="form-select @error('religion_id') is-invalid @enderror">
                        <option value="">Select Religion</option>
                        @foreach($religions as $religion)
                            <option value="{{ $religion->id }}" {{ old('religion_id') == $religion->id ? 'selected' : '' }}>
                                {{ $religion->religion_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="educational_qualification_id" class="form-label">Educational Qualification</label>
                    <select name="educational_qualification_id" id="educational_qualification_id" 
                            class="select2 form-select @error('educational_qualification_id') is-invalid @enderror">
                        <option value="">Select Experience</option>
                        @foreach($educationalQualifications as $value)
                            <option value="{{ $value->id }}" 
                                    {{ old('educational_qualification_id') == $value->id ? 'selected' : '' }}>
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
                                    {{ old('marital_status_id') == $value->id ? 'selected' : '' }}>
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
                            <option value="{{ $value->id }}">
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
                                    {{ old('status_id') == $value->id ? 'selected' : '' }}>
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
                              rows="3">{{ old('permanent_address') }}</textarea>
                    @error('permanent_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="present_address" class="form-label">Present Address</label>
                    <textarea name="present_address" id="present_address" 
                              class="form-control @error('present_address') is-invalid @enderror" 
                              rows="3">{{ old('present_address') }}</textarea>
                    @error('present_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="emergency_contact_person" class="form-label">Emergency Contact Person</label>
                    <input placeholder="Enter Emergency Contact Person" type="text" name="emergency_contact_person" id="emergency_contact_person" 
                           class="form-control @error('emergency_contact_person') is-invalid @enderror" 
                           value="{{ old('emergency_contact_person') }}">
                </div>
          
           
                <div class="col-md-3">
                    <label for="emergency_contact_number" class="form-label">Emergency Contact Number</label>
                    <input placeholder="Enter Emergency Contact Number" type="text" name="emergency_contact_number" id="emergency_contact_number" 
                           class="form-control @error('emergency_contact_number') is-invalid @enderror" 
                           value="{{ old('emergency_contact_number') }}">
                </div>
                <div class="col-md-3">
                    <label for="reference_name" class="form-label">Reference Name</label>
                    <input placeholder="Enter Reference Name" type="text" name="reference_name" id="reference_name" 
                           class="form-control @error('reference_name') is-invalid @enderror" 
                           value="{{ old('reference_name') }}">
                    @error('reference_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3">
                    <label for="reference_contact_number" class="form-label">Reference Contact Number</label>
                    <input placeholder="Enter Reference Contact Number" type="text" name="reference_contact_number" id="reference_contact_number" 
                           class="form-control @error('reference_contact_number') is-invalid @enderror" 
                           value="{{ old('reference_contact_number') }}">
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
                    <label for="license_number" class="form-label">License Number</label>
                    <input type="text" name="license_number" id="license_number" 
                           class="form-control @error('license_number') is-invalid @enderror" 
                           value="{{ old('license_number') }}" minlength="3" maxlength="50"
                           placeholder="Enter license number">
                    @error('license_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="license_type_id" class="form-label">License Type</label>
                    <select name="license_type_id" id="license_type_id" 
                            class="form-select @error('license_type_id') is-invalid @enderror">
                        <option value="">Select License Type</option>
                        @foreach($licenseTypes as $licenseType)
                            <option value="{{ $licenseType->id }}" 
                                    {{ old('license_type_id') == $licenseType->id ? 'selected' : '' }}>
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
                                    {{ old('issuing_authority_id') == $value->id ? 'selected' : '' }}>
                                {{ $value->authority_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="license_issue_date" class="form-label">License Issue Date</label>
                    <input type="date" name="license_issue_date" id="license_issue_date" 
                           class="form-control @error('license_issue_date') is-invalid @enderror" 
                           value="{{ old('license_issue_date') }}" max="{{ date('Y-m-d') }}">
                    @error('license_issue_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-5">
                <div class="col-md-3">
                    <label for="license_expiry_date" class="form-label">License Expiry Date</label>
                    <input type="date" name="license_expiry_date" id="license_expiry_date" 
                           class=" form-control @error('license_expiry_date') is-invalid @enderror" 
                           value="{{ old('license_expiry_date') }}">
                    @error('license_expiry_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="license_copy" class="form-label">License Copy (Upload)</label>
                    <input type="file" name="license_copy" id="license_copy" 
                           class="form-control @error('license_copy') is-invalid @enderror" >
                    @error('license_copy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="experience_year_id" class="form-label">Driving Experience (Years)</label>
                    <select name="experience_year_id" id="experience_year_id" 
                            class="select2 form-select @error('experience_year_id') is-invalid @enderror">
                        <option value="">Select Experience</option>
                        @foreach($experienceOptions as $value)
                            <option value="{{ $value->id }}" 
                                    {{ old('experience_year_id') == $value->id ? 'selected' : '' }}>
                                {{ $value->experience_year }}
                            </option>
                        @endforeach
                    </select>
                    @error('experience_year_id')
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
                    <label for="joining_date" class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" id="joining_date" 
                           class="form-control @error('joining_date') is-invalid @enderror" 
                           value="{{ old('joining_date') }}">
                    @error('joining_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="bank_account_number" class="form-label">Bank Account Number</label>
                    <input placeholder="Enter Bank Account Number" type="text" name="bank_account_number" id="bank_account_number" 
                           class="form-control @error('bank_account_number') is-invalid @enderror" 
                           value="{{ old('bank_account_number') }}">
                </div>
                
                <div class="col-md-3">
                    <label for="driver_type_id" class="form-label">Driver Type <span class="text-danger">*</span></label>
                    <select name="driver_type_id" id="driver_type_id" 
                            class="select2 form-select @error('driver_type_id') is-invalid @enderror" required>
                        <option value="">Select Driver Type</option>
                        @foreach($driverTypes as $driverType)
                            <option value="{{ $driverType->id }}" 
                                    {{ old('driver_type_id') == $driverType->id ? 'selected' : '' }}>
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
                    <input placeholder="Enter Daily Salary" type="number" name="daily_salary" id="daily_salary" 
                           class="form-control @error('daily_salary') is-invalid @enderror" 
                           value="{{ old('daily_salary') }}" step="0.01" min="0">
                    @error('daily_salary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3" id="basic_salary_control">
                    <label for="basic_salary" class="form-label">Basic Salary</label>
                    <input placeholder="Enter Basic Salary" type="number" name="basic_salary" id="basic_salary" 
                           class="form-control @error('basic_salary') is-invalid @enderror" 
                           value="{{ old('basic_salary') }}" step="0.01" min="0">
                    @error('basic_salary')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3" id="food_allowance_control">
                    <label for="food_allowance" class="form-label">Food Allowance</label>
                    <input placeholder="Enter Food Allowance" type="number" name="food_allowance" id="food_allowance" 
                           class="form-control @error('food_allowance') is-invalid @enderror" 
                           value="{{ old('food_allowance') }}" step="0.01" min="0">
                    @error('food_allowance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-3" id="house_rent_control">
                    <label for="house_rent" class="form-label">House Rent</label>
                    <input placeholder="Enter House Rent" type="number" name="house_rent" id="house_rent" 
                           class="form-control @error('house_rent') is-invalid @enderror" 
                           value="{{ old('house_rent') }}" step="0.01" min="0">
                    @error('house_rent')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3" id="medical_allowance_control">
                    <label for="medical_allowance" class="form-label">Medical Allowance</label>
                    <input placeholder="Enter Medical Allowance" type="number" name="medical_allowance" id="medical_allowance" 
                           class="form-control @error('medical_allowance') is-invalid @enderror" 
                           value="{{ old('medical_allowance') }}" step="0.01" min="0">
                    @error('medical_allowance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3" id="other_allowance_control">
                    <label for="other_allowance" class="form-label">Other Allowance</label>
                    <input placeholder="Enter Other Allowance" type="number" name="other_allowance" id="other_allowance" 
                           class="form-control @error('other_allowance') is-invalid @enderror" 
                           value="{{ old('other_allowance') }}" step="0.01" min="0">
                    @error('other_allowance')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3" id="gross_salary_control">
                    <label for="gross_salary" class="form-label">Gross Salary (Auto-calculated)</label>
                    <input placeholder="Enter Gross Salary" type="number" name="gross_salary" id="gross_salary" 
                           class="form-control @error('gross_salary') is-invalid @enderror" 
                           value="{{ old('gross_salary') }}" step="0.01" min="0" readonly>
                    <small class="text-muted">Calculated automatically from salary components</small>
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
                           class="form-control @error('nid_copy') is-invalid @enderror">
                    @error('nid_copy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="police_verification_copy" class="form-label">Police Verification Copy (Upload)</label>
                    <input type="file" name="police_verification_copy" id="police_verification_copy" 
                           class="form-control @error('police_verification_copy') is-invalid @enderror">
                    @error('police_verification_copy')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-3">
                    <label for="medical_certificate" class="form-label">Medical Certificate (Upload)</label>
                    <input type="file" name="medical_certificate" id="medical_certificate" 
                           class="form-control @error('medical_certificate') is-invalid @enderror">
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
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"></span>
                            <span id="submitText">
                                <i class="ti ti-device-floppy me-1"></i>Save Driver
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
