<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherCourseMarksUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'students' => ['required', 'array', 'min:1'],
            'students.*.student_id' => ['required', 'integer', 'exists:students,id'],
            'students.*.marks' => ['required', 'array'],
        ];
    }
}
