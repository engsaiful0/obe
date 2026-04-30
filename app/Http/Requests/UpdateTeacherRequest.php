<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacherId = (int) $this->route('teacher');

        return [
            'department_id' => ['required', 'exists:departments,id'],
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('teachers', 'employee_id')->ignore($teacherId)],
            'designation' => ['required', Rule::in(['Lecturer', 'Assistant Professor', 'Associate Professor', 'Professor', 'Adjunct'])],
            'email' => ['required', 'email', 'max:255', Rule::unique('teachers', 'email')->ignore($teacherId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'login_email' => ['required', 'email', 'max:255', Rule::unique('teachers', 'login_email')->ignore($teacherId)],
            'password' => ['nullable', 'string', 'min:6', 'max:255'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
            'profile_photo' => ['nullable', 'image', 'max:4096'],
            'joining_date' => ['nullable', 'date'],
            'employment_type' => ['required', Rule::in(['Full-time', 'Part-time', 'Adjunct'])],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'office_room' => ['nullable', 'string', 'max:120'],

            'is_program_coordinator' => ['nullable', 'boolean'],
            'is_course_coordinator' => ['nullable', 'boolean'],
            'can_submit_clo' => ['nullable', 'boolean'],
            'can_submit_cqi' => ['nullable', 'boolean'],

            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'blood_group' => ['nullable', 'string', 'max:20'],
            'nid' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string'],

            'research_area' => ['nullable', 'string', 'max:255'],
            'google_scholar_link' => ['nullable', 'url', 'max:255'],
            'orcid_id' => ['nullable', 'string', 'max:50'],
            'total_publications' => ['nullable', 'integer', 'min:0'],

            'educations' => ['nullable', 'array'],
            'educations.*.degree' => ['required_with:educations.*.subject,educations.*.university,educations.*.passing_year', Rule::in(['BSc', 'MSc', 'PhD'])],
            'educations.*.subject' => ['nullable', 'string', 'max:150'],
            'educations.*.university' => ['nullable', 'string', 'max:200'],
            'educations.*.passing_year' => ['nullable', 'integer', 'digits:4', 'min:1950', 'max:2100'],
            'educations.*.result' => ['nullable', 'string', 'max:50'],

            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:60'],
        ];
    }
}
