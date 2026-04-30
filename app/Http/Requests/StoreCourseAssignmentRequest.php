<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\EnsuresUniqueCourseAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseAssignmentRequest extends FormRequest
{
    use EnsuresUniqueCourseAssignment;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $programId = (int) $this->input('program_id');
        $semesterId = (int) $this->input('semester_id');

        return [
            'academic_session_id' => ['required', 'exists:academic_sessions,id'],
            'program_id' => ['required', 'exists:programs,id'],
            'batch_id' => [
                'required',
                Rule::exists('batches', 'id')->where(fn ($q) => $q->where('program_id', $programId)),
            ],
            'semester_id' => [
                'required',
                Rule::exists('semesters', 'id')->where(fn ($q) => $q->where('program_id', $programId)),
            ],
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where(function ($q) use ($programId, $semesterId) {
                    $q->where('program_id', $programId)->where('semester_id', $semesterId);
                }),
            ],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'section_id' => [
                'required',
                Rule::exists('sections', 'id')->where(function ($q) use ($programId) {
                    $q->where('program_id', $programId)
                        ->where('batch_id', (int) $this->input('batch_id'))
                        ->where('semester_id', (int) $this->input('semester_id'));
                }),
            ],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
