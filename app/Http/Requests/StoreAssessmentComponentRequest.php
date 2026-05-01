<?php

namespace App\Http\Requests;

use App\Models\AssessmentComponent;
use App\Models\Status;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssessmentComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('has_multiple_questions')) {
            $this->merge(['has_multiple_questions' => $this->boolean('has_multiple_questions')]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'program_id' => ['required', 'exists:programs,id'],
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where(function ($q) {
                    return $q->where('program_id', (int) $this->input('program_id'));
                }),
            ],
            'component_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('assessment_components', 'component_name')
                    ->where('course_id', (int) $this->input('course_id'))
                    ->whereNull('deleted_at'),
            ],
            'component_type' => ['required', Rule::in(AssessmentComponent::COMPONENT_TYPES)],
            'marks' => ['required', 'numeric', 'min:0', 'max:100'],
            'has_multiple_questions' => ['required', 'boolean'],
            'weight_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status_id' => ['required', 'exists:statuses,id'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $status = Status::query()->find((int) $this->input('status_id'));
            if ($status && $status->status_name === 'Active') {
                $courseId = (int) $this->input('course_id');
                $current = (float) AssessmentComponent::sumActiveMarksForCourse($courseId, null);
                $incomingMarks = (float) $this->input('marks');

                if (round($current + $incomingMarks, 2) > 100) {
                    $validator->errors()->add(
                        'marks',
                        __('Total marks of active components for this course cannot exceed 100 (current active total: :sum).', [
                            'sum' => rtrim(rtrim(number_format($current, 2, '.', ''), '0'), '.'),
                        ])
                    );
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'component_name.unique' => __('This component name is already used for the selected course.'),
        ];
    }
}
