<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\NormalizesTeacherEducations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\Teacher;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeType;
use App\Models\ExperienceYear;
use App\Models\Gender;
use App\Models\MaritalStatus;
use App\Models\BloodGroup;
use App\Models\Religion;
use App\Models\Status;
use App\Models\User;
use App\Models\TeacherDetail;
use App\Models\Education;
use App\Models\RelatedTo;

class UpdateTeacherRequest extends FormRequest
{
    use NormalizesTeacherEducations;

    protected function prepareForValidation(): void
    {
        $this->normalizeTeacherEducationsInput();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeTeacher = $this->route('teacher');
        $teacherId = $routeTeacher instanceof Teacher ? $routeTeacher->getKey() : (int) $routeTeacher;

        return [
            'department_id' => ['required', 'exists:departments,id'],
            'teacher_name' => ['required', 'string', 'max:255'],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('teachers', 'employee_id')->ignore($teacherId)],
            'designation_id' => [
                'required',
                Rule::exists('designations', 'id')->where(fn ($q) => $q->where('designation_type', 'Teacher')),
            ],
            'email' => ['required', 'email', 'max:255', Rule::unique('teachers', 'email')->ignore($teacherId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'login_email' => ['required', 'email', 'max:255', Rule::unique('teachers', 'login_email')->ignore($teacherId)],
            'password' => ['nullable', 'string', 'min:6', 'max:255'],
            'gender_id' => ['nullable', 'exists:genders,id'],
            'status_id' => ['required', 'exists:statuses,id'],
            'religion_id' => ['nullable', 'exists:religions,id'],
            'profile_photo' => ['nullable', 'image', 'max:4096'],
            'joining_date' => ['nullable', 'date'],
            'employee_type_id' => ['required', 'exists:employee_types,id'],
            'experience_year_id' => ['nullable', 'exists:experience_years,id'],
            'office_room' => ['nullable', 'string', 'max:120'],

            'is_program_coordinator' => ['nullable', 'boolean'],
            'is_course_coordinator' => ['nullable', 'boolean'],
            'can_submit_clo' => ['nullable', 'boolean'],
            'can_submit_cqi' => ['nullable', 'boolean'],

            'date_of_birth' => ['nullable', 'date'],
            'blood_group_id' => ['nullable', 'exists:blood_groups,id'],
            'nid' => ['nullable', 'string', 'max:50'],
            'marital_status_id' => ['nullable', 'exists:marital_statuses,id'],
            'address' => ['nullable', 'string'],

            'research_area' => ['nullable', 'string'],
            'google_scholar_link' => ['nullable', 'string', 'max:500'],
            'orcid_id' => ['nullable', 'string', 'max:100'],
            'total_publications' => ['nullable', 'integer', 'min:0'],

            'educations' => ['nullable', 'array'],
            'educations.*.degree' => ['required', Rule::in(['BSc', 'MSc', 'PhD'])],
            'educations.*.subject' => ['nullable', 'string', 'max:150'],
            'educations.*.university' => ['nullable', 'string', 'max:200'],
            'educations.*.passing_year' => ['nullable', 'integer', 'min:1950', 'max:2100'],
            'educations.*.result' => ['nullable', 'string', 'max:50'],

            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:100'],
        ];
    }
}
