@php
$teacher = $teacher ?? null;
$isEdit = $teacher !== null;
$detail = $isEdit ? ($teacher->detail ?? null) : null;
$oldEducations = old('educations');
$educations = is_array($oldEducations) ? $oldEducations : ($isEdit ? $teacher->educations->toArray() : []);
@endphp

<div class="row g-3">
    <div class="col-12">
        <h6 class="text-primary mb-0">Section 1: Basic Info</h6>
    </div>
    <div class="col-md-4">
        <label class="form-label">Department</label>
        <select name="department_id" class="form-select" required>
            <option value="">Select Department</option>
            @foreach($departments as $department)
            <option value="{{ $department->id }}" @selected(old('department_id', $teacher->department_id ?? '') == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4"><label class="form-label">Name</label><input class="form-control" name="teacher_name" value="{{ old('teacher_name', $teacher->teacher_name ?? '') }}" required></div>

    <div class="col-md-4"><label class="form-label">Employee ID</label><input class="form-control" name="employee_id" value="{{ old('employee_id', $teacher->employee_id ?? '') }}" required></div>

    <div class="col-md-4">
        <label class="form-label">Designation</label>
        <select name="designation_id" class="form-select" required>
            <option value="">Select Designation</option>
            @foreach($designations as $des)
            <option value="{{ $des->id }}" @selected(old('designation_id', $teacher->designation_id ?? '') == $des->id)>{{ $des->designation_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email', $teacher->email ?? '') }}" required></div>

    <div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="phone" value="{{ old('phone', $teacher->phone ?? '') }}"></div>

    <div class="col-12 mt-3">
        <h6 class="text-primary mb-0">Section 2: Login Info</h6>
    </div>
    <div class="col-md-6"><label class="form-label">Login Email</label><input type="email" class="form-control" name="login_email" value="{{ old('login_email', $teacher->login_email ?? '') }}" required></div>

    <div class="col-md-6"><label class="form-label">Password {{ $isEdit ? '(optional)' : '' }}</label><input type="password" class="form-control" name="password" {{ $isEdit ? '' : 'required' }}></div>

    <div class="col-12 mt-3">
        <h6 class="text-primary mb-0">Section 3: Personal Info</h6>
    </div>
    <div class="col-md-4"><label class="form-label">DOB</label><input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth', $isEdit ? optional($teacher->date_of_birth ?? $detail?->date_of_birth)->format('Y-m-d') : '') }}"></div>

    <div class="col-md-4">
        <label class="form-label">Gender</label><select name="gender_id" class="form-select">
            <option value="">Select</option>
            @foreach($genders as $gender)
            <option value="{{ $gender->id }}" @selected(old('gender_id', $teacher->gender_id ?? '') == $gender->id)>{{ $gender->gender_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Blood Group</label>
        <select class="form-select" name="blood_group_id">
            <option value="">Select Blood Group</option>
            @foreach($blood_groups as $blood_group)
            <option value="{{ $blood_group->id }}" @selected(old('blood_group_id', $teacher->blood_group_id ?? '') == $blood_group->id)>{{ $blood_group->blood_group_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4"><label class="form-label">NID</label><input class="form-control" name="nid" value="{{ old('nid', $isEdit ? ($teacher->nid ?? $detail?->nid ?? '') : '') }}"></div>

    <div class="col-md-4">
        <label class="form-label">Religion</label>
        <select class="form-select" name="religion_id">
            <option value="">Select Religion</option>
            @foreach($religions as $religion)
            <option value="{{ $religion->id }}" @selected(old('religion_id', $teacher->religion_id ?? '') == $religion->id)>{{ $religion->religion_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Marital Status</label>
        <select class="form-select" name="marital_status_id">
            <option value="">Select Marital Status</option>
            @foreach($marital_statuses as $marital_status)
            <option value="{{ $marital_status->id }}" @selected(old('marital_status_id', $teacher->marital_status_id ?? '') == $marital_status->id)>{{ $marital_status->marital_status_name }}</option>
            @endforeach
        </select>

    </div>

    <div class="col-md-4">
        <label class="form-label">Profile Photo</label>
        <input type="file" class="form-control" name="profile_photo">
    </div>

    <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2">{{ old('address', $isEdit ? ($teacher->address ?? $detail?->address ?? '') : '') }}</textarea></div>

    <div class="col-12 mt-3">
        <h6 class="text-primary mb-0">Section 4: Professional Info</h6>
    </div>

    <div class="col-md-3"><label class="form-label">Joining Date</label><input type="date" class="form-control" name="joining_date" value="{{ old('joining_date', $isEdit ? optional($teacher->joining_date)->format('Y-m-d') : '') }}"></div>

    <div class="col-md-3">
        <label class="form-label">Employment Type</label>
        <select name="employee_type_id" class="form-select" required>
            <option value="">Select Employment Type</option>
            @foreach($employee_types as $employee_type)
            <option value="{{ $employee_type->id }}" @selected(old('employee_type_id', $teacher->employee_type_id ?? '') == $employee_type->id)>{{ $employee_type->employee_type_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Experience Years</label>
        <select name="experience_year_id" class="form-select">
            <option value="">Select Experience Year</option>
            @foreach($experience_years as $experience_year)
            <option value="{{ $experience_year->id }}" @selected(old('experience_year_id', $teacher->experience_year_id ?? '') == $experience_year->id)>{{ $experience_year->experience_year }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3"><label class="form-label">Office Room</label><input class="form-control" name="office_room" value="{{ old('office_room', $teacher->office_room ?? '') }}"></div>

    <div class="col-12 mt-3">
        <h6 class="text-primary mb-0">Section 5: OBE Permissions</h6>
    </div>
    <div class="col-md-3 form-check ms-2"><input type="checkbox" class="form-check-input" name="is_program_coordinator" value="1" @checked(old('is_program_coordinator', $teacher->is_program_coordinator ?? false))><label class="form-check-label">Program Coordinator</label></div>

    <div class="col-md-3 form-check ms-2"><input type="checkbox" class="form-check-input" name="is_course_coordinator" value="1" @checked(old('is_course_coordinator', $teacher->is_course_coordinator ?? false))><label class="form-check-label">Course Coordinator</label></div>

    <div class="col-md-3 form-check ms-2"><input type="checkbox" class="form-check-input" name="can_submit_clo" value="1" @checked(old('can_submit_clo', $teacher->can_submit_clo ?? false))><label class="form-check-label">Can Submit CLO</label></div>

    <div class="col-md-3 form-check ms-2"><input type="checkbox" class="form-check-input" name="can_submit_cqi" value="1" @checked(old('can_submit_cqi', $teacher->can_submit_cqi ?? false))><label class="form-check-label">Can Submit CQI</label></div>

    <div class="col-12 mt-3">
        <h6 class="text-primary mb-0">Section 6: Research Info</h6>
    </div>
    <div class="col-md-4"><label class="form-label">Research Area</label><input class="form-control" name="research_area" value="{{ old('research_area', $isEdit ? ($teacher->research_area ?? $detail?->research_area ?? '') : '') }}"></div>

    <div class="col-md-4"><label class="form-label">Google Scholar Link</label><input class="form-control" name="google_scholar_link" value="{{ old('google_scholar_link', $isEdit ? ($teacher->google_scholar_link ?? $detail?->google_scholar_link ?? '') : '') }}"></div>

    <div class="col-md-2"><label class="form-label">ORCID</label><input class="form-control" name="orcid_id" value="{{ old('orcid_id', $isEdit ? ($teacher->orcid_id ?? $detail?->orcid_id ?? '') : '') }}"></div>

    <div class="col-md-2"><label class="form-label">Total Publications</label><input type="number" min="0" class="form-control" name="total_publications" value="{{ old('total_publications', $isEdit ? ($teacher->total_publications ?? $detail?->total_publications ?? 0) : 0) }}"></div>

    <div class="col-12 mt-3">
        <h6 class="text-primary mb-0">Section 7: Education <span class="text-muted fw-normal">(optional)</span></h6>
    </div>
    <div class="col-12">
        <div id="education-wrapper">
            @forelse($educations as $idx => $row)
            <div class="row g-2 mb-2 education-row">
                <div class="col-md-2"><select class="form-select" name="educations[{{ $idx }}][degree]">
                        <option value="">Degree</option>@foreach(['BSc','MSc','PhD'] as $deg)<option value="{{ $deg }}" @selected(($row['degree'] ?? '' )===$deg)>{{ $deg }}</option>@endforeach
                    </select></div>

                <div class="col-md-3"><input class="form-control" placeholder="Subject" name="educations[{{ $idx }}][subject]" value="{{ $row['subject'] ?? '' }}"></div>

                <div class="col-md-3"><input class="form-control" placeholder="University" name="educations[{{ $idx }}][university]" value="{{ $row['university'] ?? '' }}"></div>

                <div class="col-md-2"><input class="form-control" placeholder="Year" name="educations[{{ $idx }}][passing_year]" value="{{ $row['passing_year'] ?? '' }}"></div>

                <div class="col-md-1"><input class="form-control" placeholder="Result" name="educations[{{ $idx }}][result]" value="{{ $row['result'] ?? '' }}"></div>

                <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm remove-edu">X</button></div>

            </div>
            @empty
            <div class="row g-2 mb-2 education-row">
                <div class="col-md-2"><select class="form-select" name="educations[0][degree]">
                        <option value="">Degree</option>@foreach(['BSc','MSc','PhD'] as $deg)<option value="{{ $deg }}">{{ $deg }}</option>@endforeach
                    </select></div>
                <div class="col-md-3"><input class="form-control" placeholder="Subject" name="educations[0][subject]"></div>

                <div class="col-md-3"><input class="form-control" placeholder="University" name="educations[0][university]"></div>

                <div class="col-md-2"><input class="form-control" placeholder="Year" name="educations[0][passing_year]"></div>

                <div class="col-md-1"><input class="form-control" placeholder="Result" name="educations[0][result]"></div>

                <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm remove-edu">X</button></div>
            </div>
            @endforelse
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm" id="add-education">+ Add Education</button>
    </div>

    <div class="col-12 mt-3">
        <h6 class="text-primary mb-0">Section 8: Emergency Contact</h6>
    </div>
    <div class="col-md-4"><label class="form-label">Name</label><input class="form-control" name="emergency_contact_name" value="{{ old('emergency_contact_name', $isEdit ? ($teacher->emergency_contact_name ?? $detail?->emergency_contact_name ?? '') : '') }}"></div>

    <div class="col-md-4"><label class="form-label">Phone</label><input class="form-control" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $isEdit ? ($teacher->emergency_contact_phone ?? $detail?->emergency_contact_phone ?? '') : '') }}"></div>

    <div class="col-md-4"><label class="form-label">Relation</label><input class="form-control" name="emergency_contact_relation" value="{{ old('emergency_contact_relation', $isEdit ? ($teacher->emergency_contact_relation ?? $detail?->emergency_contact_relation ?? '') : '') }}"></div>

    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status_id" required>
            @foreach($teacherStatuses as $teacher_status)
            <option value="{{ $teacher_status->id }}" @selected(old('status_id', $teacher->status_id ?? '') == $teacher_status->id)>{{ $teacher_status->status_name }}</option>
            @endforeach
        </select>
    </div>
</div>

<script>
    (function() {
        const wrapper = document.getElementById('education-wrapper');
        const addBtn = document.getElementById('add-education');
        if (!wrapper || !addBtn) return;
        addBtn.addEventListener('click', function() {
            const idx = wrapper.querySelectorAll('.education-row').length;
            const row = document.createElement('div');
            row.className = 'row g-2 mb-2 education-row';
            row.innerHTML = `
                <div class="col-md-2"><select class="form-select" name="educations[${idx}][degree]"><option value="">Degree</option><option value="BSc">BSc</option><option value="MSc">MSc</option><option value="PhD">PhD</option></select></div>
                <div class="col-md-3"><input class="form-control" placeholder="Subject" name="educations[${idx}][subject]"></div>
                <div class="col-md-3"><input class="form-control" placeholder="University" name="educations[${idx}][university]"></div>
                <div class="col-md-2"><input class="form-control" placeholder="Year" name="educations[${idx}][passing_year]"></div>
                <div class="col-md-1"><input class="form-control" placeholder="Result" name="educations[${idx}][result]"></div>
                <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm remove-edu">X</button></div>
            `;
            wrapper.appendChild(row);
        });
        wrapper.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-edu')) {
                const rows = wrapper.querySelectorAll('.education-row');
                if (rows.length > 1) {
                    e.target.closest('.education-row').remove();
                }
            }
        });
    })();

</script>
