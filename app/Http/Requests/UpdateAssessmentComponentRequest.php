<?php

namespace App\Http\Requests;

use App\Models\AssessmentComponent;
use App\Models\Status;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssessmentComponentRequest extends FormRequest
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
        /** @var AssessmentComponent $component */
        $component = $this->route('assessment_component');

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
                    ->whereNull('deleted_at')
                    ->ignore($component->id),
            ],
            'component_type' => ['required', Rule::in(AssessmentComponent::COMPONENT_TYPES)],
            'marks' => ['required', 'numeric', 'min:0', 'max:100'],
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

            /** @var AssessmentComponent $component */
            $component = $this->route('assessment_component');

            $newStatus = Status::query()->find((int) $this->input('status_id'));

            // Active total excluding this row — only other rows with Active status count.
            $courseId = (int) $this->input('course_id');
            $sumOthersActive = AssessmentComponent::sumActiveMarksForCourse($courseId, $component->id);

            $newMarks = (float) $this->input('marks');
            $sumOthers = (float) $sumOthersActive;
            $add = ($newStatus && $newStatus->status_name === 'Active') ? $newMarks : 0.0;

            if (round($sumOthers + $add, 2) > 100) {
                $validator->errors()->add(
                    'marks',
                    __('Total marks of active components for this course cannot exceed 100 (other active total: :sum).', [
                        'sum' => rtrim(rtrim(number_format($sumOthers, 2, '.', ''), '0'), '.'),
                    ])
                );
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
