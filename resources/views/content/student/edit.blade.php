@extends('layouts/layoutMaster')

@section('title', 'Edit Employee')

<!-- Vendor Scripts -->
@section('page-script')
<script src="{{asset('assets/js/employee-management.js')}}"></script>

<script>
    function calculateGrossSalary() {
        let basic = parseFloat(document.getElementById('basic_salary').value) || 0;
        let house = parseFloat(document.getElementById('house_rent').value) || 0;
        let medical = parseFloat(document.getElementById('medical_allowance').value) || 0;
        let other = parseFloat(document.getElementById('other_allowance').value) || 0;

        let gross = basic + house + medical + other;
        document.getElementById('gross_salary').value = gross;
    }

    document.querySelectorAll('#basic_salary, #house_rent, #medical_allowance, #other_allowance')
        .forEach(input => input.addEventListener('input', calculateGrossSalary));
</script>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">Edit Employee</h5>
    </div>
    <div class="card-body">
        <form id="editEmployeeForm" action="{{ route('employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-3">

                {{-- ============ Personal Info ============ --}}
                <div class="col-12 col-md-3">
                    <label class="form-label" for="employee_name">Employee Name</label>
                    <input type="text" id="employee_name" name="employee_name" class="form-control"
                        placeholder="John Doe" value="{{ old('employee_name', $employee->employee_name) }}" required />
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="employee_id">Employee ID</label>
                    <input readonly type="text" id="employee_unique_id" name="employee_unique_id" class="form-control" placeholder="E-0001"
                        value="{{ $employee->employee_unique_id }}" />
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="gender">Gender</label>
                    <select name="gender" id="gender" class="form-select" required>
                        <option value="">-- Select Gender --</option>
                        <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $employee->gender) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="father_name">Father's Name</label>
                    <input type="text" id="father_name" name="father_name" class="form-control"
                        placeholder="Robert Doe" value="{{ old('father_name', $employee->father_name) }}" />
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="mother_name">Mother's Name</label>
                    <input type="text" id="mother_name" name="mother_name" class="form-control"
                        placeholder="Mary Doe" value="{{ old('mother_name', $employee->mother_name) }}" />
                </div>

                {{-- ============ Contact Info ============ --}}
                <div class="col-12 col-md-3">
                    <label class="form-label" for="mobile">Mobile</label>
                    <input type="text" id="mobile" name="mobile" class="form-control"
                        placeholder="+8801XXXXXXXXX" value="{{ old('mobile', $employee->mobile) }}" required />
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control"
                        placeholder="john.doe@example.com" value="{{ old('email', $employee->email) }}" required />
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="nid">NID</label>
                    <input type="text" id="nid" name="nid" class="form-control"
                        placeholder="1234567890" value="{{ old('nid', $employee->nid) }}" required />
                </div>
                <div class="col-12 col-md-3">
                    <label for="religion_id" class="form-label">Religion</label>
                    <select class="form-control" id="religion_id" name="religion_id">
                        <option value="">Select Religion</option>
                        @foreach ($religions as $religion)
                        <option value="{{ $religion->id }}"
                            {{ old('religion_id', $employee->religion_id) == $religion->id ? 'selected' : '' }}>
                            {{ $religion->religion_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="designation_id">Designation</label>
                    <select id="designation_id" name="designation_id" class="form-select">
                        <option value="">Select Designation</option>
                        @foreach ($designations as $designation)
                        <option value="{{ $designation->id }}" {{ old('designation_id', $employee->designation_id) == $designation->id ? 'selected' : '' }}>
                            {{ $designation->designation_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                {{-- ============ Addresses ============ --}}
                <div class="col-12 col-md-3">
                    <label class="form-label" for="present_address">Present Address</label>
                    <textarea name="present_address" id="present_address" class="form-control" rows="2">{{ old('present_address', $employee->present_address) }}</textarea>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="permanent_address">Permanent Address</label>
                    <textarea name="permanent_address" id="permanent_address" class="form-control" rows="2">{{ old('permanent_address', $employee->permanent_address) }}</textarea>
                </div>

                {{-- ============ Documents ============ --}}
                <div class="col-12 col-md-3">
                    <label class="form-label" for="picture">Picture</label>
                    <input type="file" id="picture" name="picture" class="form-control" />
                    @if($employee->picture)
                    <img src="{{ asset('storage/app/public/' . $employee->picture) }}" alt="Picture" width="100" class="mt-2">
                    @endif
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="cv_upload">CV Upload</label>
                    <input type="file" id="cv_upload" name="cv_upload" class="form-control" />
                    @if(!empty($employee->cv_upload))
                    <a href="{{ asset('storage/' . $employee->cv_upload) }}" target="_blank">View CV</a>
                    @endif
                </div>
                <div class="col-12 col-md-3">
                        <label class="form-label" for="cv_upload">Employee Type</label>
                        <select id="employee_type_id" name="employee_type_id" class="form-select select2">
                            <option value="">Select Employee Type</option>
                            @foreach ($employee_type as $employee_type)
                                <option value="{{ $employee_type->id }}" @if (old('employee_type_id', $employee->employee_type_id) == $employee_type->id) selected @endif>
                                    {{ $employee_type->employee_type_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                {{-- ============ Education ============ --}}
                <h6 class="mt-4">Education Details</h6>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="ssc_or_equivalent_group">SSC or Equivalent Group</label>
                    <input type="text" id="ssc_or_equivalent_group" name="ssc_or_equivalent_group" class="form-control"
                        placeholder="Science" value="{{ old('ssc_or_equivalent_group', $employee->ssc_or_equivalent_group) }}" />
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="ssc_result">SSC Result</label>
                    <input type="number" id="ssc_result" name="ssc_result" class="form-control"
                        value="{{ old('ssc_result', $employee->ssc_result) }}" />
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="ssc_documents_upload">SSC Documents</label>
                    <input type="file" id="ssc_documents_upload" name="ssc_documents_upload" class="form-control" />
                    @if($employee->ssc_documents_upload)
                    <a href="{{ asset('storage/' . $employee->ssc_documents_upload) }}" target="_blank">View Document</a>
                    @endif
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="hsc_or_equivalent_group">HSC or Equivalent Group</label>
                    <input type="text" id="hsc_or_equivalent_group" name="hsc_or_equivalent_group" class="form-control"
                        placeholder="Science" value="{{ old('hsc_or_equivalent_group', $employee->hsc_or_equivalent_group) }}" />
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="hsc_result">HSC Result</label>
                    <input type="text" id="hsc_result" name="hsc_result" class="form-control"
                        value="{{ old('hsc_result', $employee->hsc_result) }}" />
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="hsc_documents_upload">HSC Documents</label>
                    <input type="file" id="hsc_documents_upload" name="hsc_documents_upload" class="form-control" />
                    @if($employee->hsc_documents_upload)
                    <a href="{{ asset('storage/' . $employee->hsc_documents_upload) }}" target="_blank">View Document</a>
                    @endif
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="bachelor_or_equivalent_group">Bachelor or Equivalent Group</label>
                    <input type="text" id="bachelor_or_equivalent_group" name="bachelor_or_equivalent_group" class="form-control"
                        value="{{ old('bachelor_or_equivalent_group', $employee->bachelor_or_equivalent_group) }}" />
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="result">Honors Result</label>
                    <input type="text" id="result" name="result" class="form-control"
                        value="{{ old('result', $employee->result) }}" />
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="honors_documents_upload">Honors Documents</label>
                    <input type="file" id="honors_documents_upload" name="honors_documents_upload" class="form-control" />
                    @if($employee->honors_documents_upload)
                    <a href="{{ asset('storage/' . $employee->honors_documents_upload) }}" target="_blank">View Document</a>
                    @endif
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="master_or_equivalent_group">Master or Equivalent Group</label>
                    <input type="text" id="master_or_equivalent_group" name="master_or_equivalent_group" class="form-control"
                        value="{{ old('master_or_equivalent_group', $employee->master_or_equivalent_group) }}" />
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="masters_result">Masters Result</label>
                    <input type="text" id="masters_result" name="masters_result" class="form-control"
                        value="{{ old('masters_result', $employee->masters_result) }}" />
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label" for="masters_document_upload">Masters Documents</label>
                    <input type="file" id="masters_document_upload" name="masters_document_upload" class="form-control" />
                    @if($employee->masters_document_upload)
                    <a href="{{ asset('storage/' . $employee->masters_document_upload) }}" target="_blank">View Document</a>
                    @endif
                </div>

                {{-- ============ Experience & Salary ============ --}}
                <h6 class="mt-4">Experience & Salary</h6>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="years_of_experience">Years of Experience</label>
                    <input type="number" id="years_of_experience" name="years_of_experience" class="form-control"
                        value="{{ old('years_of_experience', $employee->years_of_experience) }}" />
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="date_of_join">Date of Join</label>
                    <input type="date" id="date_of_join" name="date_of_join" class="form-control"
                        value="{{ old('date_of_join', $employee->date_of_join) }}" />
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="basic_salary">Basic Salary</label>
                    <input type="number" step="0.01" id="basic_salary" name="basic_salary" class="form-control"
                        value="{{ old('basic_salary', $employee->basic_salary) }}" />
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="house_rent">House Rent</label>
                    <input type="number" step="0.01" id="house_rent" name="house_rent" class="form-control"
                        value="{{ old('house_rent', $employee->house_rent) }}" />
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="medical_allowance">Medical Allowance</label>
                    <input type="number" step="0.01" id="medical_allowance" name="medical_allowance" class="form-control"
                        value="{{ old('medical_allowance', $employee->medical_allowance) }}" />
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="other_allowance">Other Allowance</label>
                    <input type="number" step="0.01" id="other_allowance" name="other_allowance" class="form-control"
                        value="{{ old('other_allowance', $employee->other_allowance) }}" />
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="gross_salary">Gross Salary</label>
                    <input type="number" step="0.01" id="gross_salary" name="gross_salary" class="form-control"
                        value="{{ old('gross_salary', $employee->gross_salary) }}" readonly />
                </div>

                {{-- Submit --}}
                <div class="col-12 text-center mt-3">
                    <button type="submit" class="btn btn-primary me-sm-3 me-1">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Update
                    </button>
                    <a href="{{ route('employees.view-employee') }}" class="btn btn-label-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection