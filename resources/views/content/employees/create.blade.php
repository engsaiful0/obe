@extends('layouts/layoutMaster')

@section('title', 'Add Employee')

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
            <h5 class="card-title">Add Employee</h5>
        </div>
        <div class="card-body">
            <form id="createEmployeeForm" action="{{ route('employees.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">

                    {{-- ============ Personal Info ============ --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="employee_name">Employee Name</label>
                        <input type="text" id="employee_name" name="employee_name" class="form-control"
                            placeholder="John Doe" value="{{ old('employee_name') }}" required />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="employee_id">Employee ID</label>


                        <input readonly type="text" id="employee_unique_id" name="employee_unique_id"
                            class="form-control" placeholder="Enter Employee ID" />
                       
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="gender">Gender</label>
                        <select name="gender" id="gender" class="form-select" required>
                            <option value="">-- Select Gender --</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="father_name">Father's Name</label>
                        <input type="text" id="father_name" name="father_name" class="form-control"
                            placeholder="Robert Doe" value="{{ old('father_name') }}" />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="mother_name">Mother's Name</label>
                        <input type="text" id="mother_name" name="mother_name" class="form-control"
                            placeholder="Mary Doe" value="{{ old('mother_name') }}" />
                    </div>

                    {{-- ============ Contact Info ============ --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="mobile">Mobile</label>
                        <input type="text" id="mobile" name="mobile" class="form-control"
                            placeholder="+8801XXXXXXXXX" value="{{ old('mobile') }}" required />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                            placeholder="john.doe@example.com" value="{{ old('email') }}" required />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="nid">NID</label>
                        <input type="text" id="nid" name="nid" class="form-control" placeholder="1234567890"
                            value="{{ old('nid') }}" required />
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="religion_id" class="form-label">Religion</label>
                        <select class="form-control" id="religion_id" name="religion_id">
                            <option value="">Select Religion</option>
                            @foreach ($religions as $religion)
                                <option value="{{ $religion->id }}"
                                    {{ isset($student) && $student->religion_id == $religion->id ? 'selected' : '' }}>
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
                                <option value="{{ $designation->id }}" @if (old('designation_id') == $designation->id) selected @endif>
                                    {{ $designation->designation_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- ============ Addresses ============ --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="present_address">Present Address</label>
                        <textarea name="present_address" id="present_address" class="form-control" rows="2">{{ old('present_address') }}</textarea>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="permanent_address">Permanent Address</label>
                        <textarea name="permanent_address" id="permanent_address" class="form-control" rows="2">{{ old('permanent_address') }}</textarea>
                    </div>

                    {{-- ============ Documents ============ --}}
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="picture">Picture</label>
                        <input type="file" id="picture" name="picture" class="form-control" />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="cv_upload">CV Upload</label>
                        <input type="file" id="cv_upload" name="cv_upload" class="form-control" />
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="cv_upload">Employee Type</label>
                        <select id="employee_type_id" name="employee_type_id" class="form-select select2">
                            <option value="">Select Employee Type</option>
                            @foreach ($employee_type as $employee_type)
                                <option value="{{ $employee_type->id }}" @if (old('employee_type_id') == $employee_type->id) selected @endif>
                                    {{ $employee_type->employee_type_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ============ Education ============ --}}
                    <h6 class="mt-4">Education Details</h6>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="ssc_or_equivalent_group">SSC or Equivalent Group</label>
                        <select id="ssc_or_equivalent_group" name="ssc_or_equivalent_group" class="form-control"
                            placeholder="Science">
                            <option value="Science" {{ old('ssc_or_equivalent_group') == 'Science' ? 'selected' : '' }}>
                                Science</option>
                            <option value="Arts" {{ old('ssc_or_equivalent_group') == 'Arts' ? 'selected' : '' }}>Arts
                            </option>
                            <option value="Commerce" {{ old('ssc_or_equivalent_group') == 'Commerce' ? 'selected' : '' }}>
                                Commerce</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="ssc_result">SSC Result</label>
                        <input type="number" id="ssc_result" name="ssc_result" class="form-control"
                            value="{{ old('ssc_result') }}" />
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="ssc_documents_upload">SSC Documents</label>
                        <input type="file" id="ssc_documents_upload" name="ssc_documents_upload"
                            class="form-control" />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="hsc_or_equivalent_group">HSC or Equivalent Group</label>
                        <select id="hsc_or_equivalent_group" name="hsc_or_equivalent_group" class="form-control"
                            placeholder="Science">
                            <option value="Science" {{ old('hsc_or_equivalent_group') == 'Science' ? 'selected' : '' }}>
                                Science</option>
                            <option value="Arts" {{ old('hsc_or_equivalent_group') == 'Arts' ? 'selected' : '' }}>Arts
                            </option>
                            <option value="Commerce" {{ old('hsc_or_equivalent_group') == 'Commerce' ? 'selected' : '' }}>
                                Commerce</option>
                            <option value="ET" {{ old('hsc_or_equivalent_group') == 'ET' ? 'selected' : '' }}>
                                ET</option>
                            <option value="CMT" {{ old('hsc_or_equivalent_group') == 'CMT' ? 'selected' : '' }}>
                                CMT</option>
                            <option value="MT" {{ old('hsc_or_equivalent_group') == 'MT' ? 'selected' : '' }}>
                                MT</option>
                            <option value="CT" {{ old('hsc_or_equivalent_group') == 'CT' ? 'selected' : '' }}>
                                CT</option>
                            <option value="PT" {{ old('hsc_or_equivalent_group') == 'PT' ? 'selected' : '' }}>
                                PT</option>
                            <option value="Other" {{ old('hsc_or_equivalent_group') == 'Other' ? 'selected' : '' }}>
                                Other</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="hsc_result">HSC Result</label>
                        <input type="text" id="hsc_result" name="hsc_result" class="form-control"
                            value="{{ old('hsc_result') }}" />
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="hsc_documents_upload">HSC Documents</label>
                        <input type="file" id="hsc_documents_upload" name="hsc_documents_upload"
                            class="form-control" />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="bachelor_or_equivalent_group">Bachelor or Equivalent Group</label>
                        <select id="bachelor_or_equivalent_group" name="bachelor_or_equivalent_group"
                            class="form-control">
                            <option value="">-- Select Group/Subject --</option>

                            <optgroup label="General">
                                <option value="Science"
                                    {{ old('bachelor_or_equivalent_group') == 'Science' ? 'selected' : '' }}>Science
                                </option>
                                <option value="Arts"
                                    {{ old('bachelor_or_equivalent_group') == 'Arts' ? 'selected' : '' }}>Arts</option>
                                <option value="Commerce"
                                    {{ old('bachelor_or_equivalent_group') == 'Commerce' ? 'selected' : '' }}>Commerce
                                </option>
                            </optgroup>

                            <optgroup label="Engineering & Technology">
                                <option value="EEE"
                                    {{ old('bachelor_or_equivalent_group') == 'EEE' ? 'selected' : '' }}>EEE</option>
                                <option value="ETE"
                                    {{ old('bachelor_or_equivalent_group') == 'ETE' ? 'selected' : '' }}>ETE</option>
                                <option value="CSE"
                                    {{ old('bachelor_or_equivalent_group') == 'CSE' ? 'selected' : '' }}>CSE</option>
                                <option value="CCE"
                                    {{ old('bachelor_or_equivalent_group') == 'CCE' ? 'selected' : '' }}>CCE</option>
                                <option value="ME"
                                    {{ old('bachelor_or_equivalent_group') == 'ME' ? 'selected' : '' }}>ME</option>
                                <option value="CE"
                                    {{ old('bachelor_or_equivalent_group') == 'CE' ? 'selected' : '' }}>CE</option>
                                <option value="IPE"
                                    {{ old('bachelor_or_equivalent_group') == 'IPE' ? 'selected' : '' }}>IPE</option>
                                <option value="TE"
                                    {{ old('bachelor_or_equivalent_group') == 'TE' ? 'selected' : '' }}>TE
                                </option>
                                <option value="ARC"
                                    {{ old('bachelor_or_equivalent_group') == 'ARC' ? 'selected' : '' }}>
                                    Architecture</option>
                                <option value="ICT"
                                    {{ old('bachelor_or_equivalent_group') == 'ICT' ? 'selected' : '' }}>ICT</option>
                            </optgroup>

                            <optgroup label="Business & Management">
                                <option value="BBA"
                                    {{ old('bachelor_or_equivalent_group') == 'BBA' ? 'selected' : '' }}>BBA</option>
                                <option value="Accounting"
                                    {{ old('bachelor_or_equivalent_group') == 'Accounting' ? 'selected' : '' }}>Accounting
                                </option>
                                <option value="Finance"
                                    {{ old('bachelor_or_equivalent_group') == 'Finance' ? 'selected' : '' }}>Finance
                                </option>
                                <option value="Marketing"
                                    {{ old('bachelor_or_equivalent_group') == 'Marketing' ? 'selected' : '' }}>Marketing
                                </option>
                                <option value="Management"
                                    {{ old('bachelor_or_equivalent_group') == 'Management' ? 'selected' : '' }}>Management
                                </option>
                                <option value="HRM"
                                    {{ old('bachelor_or_equivalent_group') == 'HRM' ? 'selected' : '' }}>HRM</option>
                            </optgroup>

                            <optgroup label="Health Sciences">
                                <option value="MBBS"
                                    {{ old('bachelor_or_equivalent_group') == 'MBBS' ? 'selected' : '' }}>MBBS</option>
                                <option value="Dental"
                                    {{ old('bachelor_or_equivalent_group') == 'Dental' ? 'selected' : '' }}>Dental</option>
                                <option value="Nursing"
                                    {{ old('bachelor_or_equivalent_group') == 'Nursing' ? 'selected' : '' }}>Nursing
                                </option>
                                <option value="Pharmacy"
                                    {{ old('bachelor_or_equivalent_group') == 'Pharmacy' ? 'selected' : '' }}>Pharmacy
                                </option>
                                <option value="Public Health"
                                    {{ old('bachelor_or_equivalent_group') == 'Public Health' ? 'selected' : '' }}>Public
                                    Health</option>
                                <option value="Physiotherapy"
                                    {{ old('bachelor_or_equivalent_group') == 'Physiotherapy' ? 'selected' : '' }}>
                                    Physiotherapy</option>
                            </optgroup>

                            <optgroup label="Social Sciences & Humanities">
                                <option value="Economics"
                                    {{ old('bachelor_or_equivalent_group') == 'Economics' ? 'selected' : '' }}>Economics
                                </option>
                                <option value="Political Science"
                                    {{ old('bachelor_or_equivalent_group') == 'Political Science' ? 'selected' : '' }}>
                                    Political Science</option>
                                <option value="Sociology"
                                    {{ old('bachelor_or_equivalent_group') == 'Sociology' ? 'selected' : '' }}>Sociology
                                </option>
                                <option value="Psychology"
                                    {{ old('bachelor_or_equivalent_group') == 'Psychology' ? 'selected' : '' }}>Psychology
                                </option>
                                <option value="History"
                                    {{ old('bachelor_or_equivalent_group') == 'History' ? 'selected' : '' }}>History
                                </option>
                                <option value="English"
                                    {{ old('bachelor_or_equivalent_group') == 'English' ? 'selected' : '' }}>English
                                </option>
                                <option value="Bangla"
                                    {{ old('bachelor_or_equivalent_group') == 'Bangla' ? 'selected' : '' }}>Bangla</option>
                                <option value="Philosophy"
                                    {{ old('bachelor_or_equivalent_group') == 'Philosophy' ? 'selected' : '' }}>Philosophy
                                </option>
                                <option value="International Relations"
                                    {{ old('bachelor_or_equivalent_group') == 'International Relations' ? 'selected' : '' }}>
                                    International Relations</option>
                            </optgroup>

                            <optgroup label="Agriculture & Others">
                                <option value="Agriculture"
                                    {{ old('bachelor_or_equivalent_group') == 'Agriculture' ? 'selected' : '' }}>
                                    Agriculture</option>
                                <option value="Fisheries"
                                    {{ old('bachelor_or_equivalent_group') == 'Fisheries' ? 'selected' : '' }}>Fisheries
                                </option>
                                <option value="Veterinary Science"
                                    {{ old('bachelor_or_equivalent_group') == 'Veterinary Science' ? 'selected' : '' }}>
                                    Veterinary Science</option>
                                <option value="Law"
                                    {{ old('bachelor_or_equivalent_group') == 'Law' ? 'selected' : '' }}>Law</option>
                                <option value="Fine Arts"
                                    {{ old('bachelor_or_equivalent_group') == 'Fine Arts' ? 'selected' : '' }}>Fine Arts
                                </option>
                                <option value="Education"
                                    {{ old('bachelor_or_equivalent_group') == 'Education' ? 'selected' : '' }}>Education
                                </option>
                                <option value="Other"
                                    {{ old('bachelor_or_equivalent_group') == 'Other' ? 'selected' : '' }}>Other</option>
                            </optgroup>
                        </select>

                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="result">Honors Result</label>
                        <input type="text" id="result" name="result" class="form-control"
                            value="{{ old('result') }}" />
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="honors_documents_upload">Honors Documents</label>
                        <input type="file" id="honors_documents_upload" name="honors_documents_upload"
                            class="form-control" />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="master_or_equivalent_group">Master or Equivalent Group</label>
                        <select id="master_or_equivalent_group" name="master_or_equivalent_group" class="form-control">
                            <option value="">-- Select Group/Subject --</option>

                            <optgroup label="General">
                                <option value="Science"
                                    {{ old('master_or_equivalent_group') == 'Science' ? 'selected' : '' }}>Science</option>
                                <option value="Arts"
                                    {{ old('master_or_equivalent_group') == 'Arts' ? 'selected' : '' }}>Arts</option>
                                <option value="Commerce"
                                    {{ old('master_or_equivalent_group') == 'Commerce' ? 'selected' : '' }}>Commerce
                                </option>
                            </optgroup>

                            <optgroup label="Engineering & Technology">
                                <option value="EEE"
                                    {{ old('bachelor_or_equivalent_group') == 'EEE' ? 'selected' : '' }}>EEE</option>
                                <option value="ETE"
                                    {{ old('bachelor_or_equivalent_group') == 'ETE' ? 'selected' : '' }}>ETE</option>
                                <option value="CSE"
                                    {{ old('bachelor_or_equivalent_group') == 'CSE' ? 'selected' : '' }}>CSE</option>
                                <option value="CCE"
                                    {{ old('bachelor_or_equivalent_group') == 'CCE' ? 'selected' : '' }}>CCE</option>
                                <option value="ME"
                                    {{ old('bachelor_or_equivalent_group') == 'ME' ? 'selected' : '' }}>ME</option>
                                <option value="CE"
                                    {{ old('bachelor_or_equivalent_group') == 'CE' ? 'selected' : '' }}>CE</option>
                                <option value="IPE"
                                    {{ old('bachelor_or_equivalent_group') == 'IPE' ? 'selected' : '' }}>IPE</option>
                                <option value="TE"
                                    {{ old('bachelor_or_equivalent_group') == 'TE' ? 'selected' : '' }}>TE
                                </option>
                                <option value="ARC"
                                    {{ old('bachelor_or_equivalent_group') == 'ARC' ? 'selected' : '' }}>
                                    Architecture</option>
                                <option value="ICT"
                                    {{ old('bachelor_or_equivalent_group') == 'ICT' ? 'selected' : '' }}>ICT</option>
                            </optgroup>

                            <optgroup label="Business & Management">
                                <option value="MBA" {{ old('master_or_equivalent_group') == 'MBA' ? 'selected' : '' }}>
                                    MBA</option>
                                <option value="Accounting"
                                    {{ old('master_or_equivalent_group') == 'Accounting' ? 'selected' : '' }}>Accounting
                                </option>
                                <option value="Finance"
                                    {{ old('master_or_equivalent_group') == 'Finance' ? 'selected' : '' }}>Finance</option>
                                <option value="Marketing"
                                    {{ old('master_or_equivalent_group') == 'Marketing' ? 'selected' : '' }}>Marketing
                                </option>
                                <option value="Management"
                                    {{ old('master_or_equivalent_group') == 'Management' ? 'selected' : '' }}>Management
                                </option>
                                <option value="HRM" {{ old('master_or_equivalent_group') == 'HRM' ? 'selected' : '' }}>
                                    HRM</option>
                            </optgroup>

                            <optgroup label="Health Sciences">
                                <option value="MS/MD"
                                    {{ old('master_or_equivalent_group') == 'MS/MD' ? 'selected' : '' }}>MS/MD</option>
                                <option value="MDS" {{ old('master_or_equivalent_group') == 'MDS' ? 'selected' : '' }}>
                                    MDS</option>
                                <option value="Nursing"
                                    {{ old('master_or_equivalent_group') == 'Nursing' ? 'selected' : '' }}>Nursing
                                </option>
                                <option value="Pharmacy"
                                    {{ old('master_or_equivalent_group') == 'Pharmacy' ? 'selected' : '' }}>Pharmacy
                                </option>
                                <option value="Public Health (MPH)"
                                    {{ old('master_or_equivalent_group') == 'Public Health (MPH)' ? 'selected' : '' }}>
                                    Public Health (MPH)</option>
                                <option value="Physiotherapy"
                                    {{ old('master_or_equivalent_group') == 'Physiotherapy' ? 'selected' : '' }}>
                                    Physiotherapy</option>
                            </optgroup>

                            <optgroup label="Social Sciences & Humanities">
                                <option value="Economics"
                                    {{ old('master_or_equivalent_group') == 'Economics' ? 'selected' : '' }}>Economics
                                </option>
                                <option value="Political Science"
                                    {{ old('master_or_equivalent_group') == 'Political Science' ? 'selected' : '' }}>
                                    Political Science</option>
                                <option value="Sociology"
                                    {{ old('master_or_equivalent_group') == 'Sociology' ? 'selected' : '' }}>Sociology
                                </option>
                                <option value="Psychology"
                                    {{ old('master_or_equivalent_group') == 'Psychology' ? 'selected' : '' }}>Psychology
                                </option>
                                <option value="History"
                                    {{ old('master_or_equivalent_group') == 'History' ? 'selected' : '' }}>History
                                </option>
                                <option value="English"
                                    {{ old('master_or_equivalent_group') == 'English' ? 'selected' : '' }}>English
                                </option>
                                <option value="Bangla"
                                    {{ old('master_or_equivalent_group') == 'Bangla' ? 'selected' : '' }}>Bangla</option>
                                <option value="Philosophy"
                                    {{ old('master_or_equivalent_group') == 'Philosophy' ? 'selected' : '' }}>Philosophy
                                </option>
                                <option value="International Relations"
                                    {{ old('master_or_equivalent_group') == 'International Relations' ? 'selected' : '' }}>
                                    International Relations</option>
                            </optgroup>

                            <optgroup label="Agriculture & Others">
                                <option value="Agriculture"
                                    {{ old('master_or_equivalent_group') == 'Agriculture' ? 'selected' : '' }}>Agriculture
                                </option>
                                <option value="Fisheries"
                                    {{ old('master_or_equivalent_group') == 'Fisheries' ? 'selected' : '' }}>Fisheries
                                </option>
                                <option value="Veterinary Science"
                                    {{ old('master_or_equivalent_group') == 'Veterinary Science' ? 'selected' : '' }}>
                                    Veterinary Science</option>
                                <option value="Law (LLM)"
                                    {{ old('master_or_equivalent_group') == 'Law (LLM)' ? 'selected' : '' }}>Law (LLM)
                                </option>
                                <option value="Fine Arts"
                                    {{ old('master_or_equivalent_group') == 'Fine Arts' ? 'selected' : '' }}>Fine Arts
                                </option>
                                <option value="Education (M.Ed)"
                                    {{ old('master_or_equivalent_group') == 'Education (M.Ed)' ? 'selected' : '' }}>
                                    Education (M.Ed)</option>
                                <option value="Other"
                                    {{ old('master_or_equivalent_group') == 'Other' ? 'selected' : '' }}>Other</option>
                            </optgroup>
                        </select>

                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="masters_result">Masters Result</label>
                        <input type="text" id="masters_result" name="masters_result" class="form-control"
                            value="{{ old('masters_result') }}" />
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="masters_document_upload">Masters Documents</label>
                        <input type="file" id="masters_document_upload" name="masters_document_upload"
                            class="form-control" />
                    </div>

                    {{-- ============ Experience & Salary ============ --}}
                    <h6 class="mt-4">Experience & Salary</h6>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="years_of_experience">Years of Experience</label>
                        <input type="number" id="years_of_experience" name="years_of_experience" class="form-control"
                            value="{{ old('years_of_experience') }}" />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="date_of_join">Date of Join</label>
                        <input type="date" id="date_of_join" name="date_of_join" class="form-control"
                            value="{{ old('date_of_join') }}" />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="basic_salary">Basic Salary</label>
                        <input type="number" step="0.01" id="basic_salary" name="basic_salary" class="form-control"
                            value="{{ old('basic_salary') }}" />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="house_rent">House Rent</label>
                        <input type="number" step="0.01" id="house_rent" name="house_rent" class="form-control"
                            value="{{ old('house_rent') }}" />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="medical_allowance">Medical Allowance</label>
                        <input type="number" step="0.01" id="medical_allowance" name="medical_allowance"
                            class="form-control" value="{{ old('medical_allowance') }}" />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="other_allowance">Other Allowance</label>
                        <input type="number" step="0.01" id="other_allowance" name="other_allowance"
                            class="form-control" value="{{ old('other_allowance') }}" />
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label" for="gross_salary">Gross Salary</label>
                        <input type="number" step="0.01" id="gross_salary" name="gross_salary" class="form-control"
                            value="{{ old('gross_salary') }}" />
                    </div>

                    {{-- Submit --}}
                    <div class="col-12 text-center mt-3">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">
                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                            Submit
                        </button>
                        <a href="{{ route('employees.index') }}" class="btn btn-label-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
