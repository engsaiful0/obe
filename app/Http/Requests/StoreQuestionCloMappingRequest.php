<?php

namespace App\Http\Requests;

use App\Models\AssessmentComponent;
use App\Models\Clo;
use App\Models\QuestionCloMapping;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionCloMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('bloom_id') === '' || $this->input('bloom_id') === null) {
            $this->merge(['bloom_id' => null]);
        }

        foreach (['main_question_no', 'question_part'] as $key) {
            if ($this->has($key) && $this->input($key) !== null && is_string($this->input($key))) {
                $t = trim((string) $this->input($key));
                $this->merge([$key => $t === '' ? null : $t]);
            }
        }

        if ($this->has('question_label') && $this->input('question_label') !== null && is_string($this->input('question_label'))) {
            $this->merge(['question_label' => trim((string) $this->input('question_label'))]);
        }

        foreach (['question_title', 'question_description', 'remarks'] as $key) {
            if (! $this->has($key)) {
                continue;
            }
            $v = $this->input($key);
            if ($v !== null && is_string($v)) {
                $t = trim($v);
                $this->merge([$key => $t === '' ? null : $t]);
            }
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
            'assessment_component_id' => [
                'required',
                Rule::exists('assessment_components', 'id')->where(function ($q) {
                    return $q->where('course_id', (int) $this->input('course_id'));
                }),
            ],
            'clo_id' => [
                'required',
                Rule::exists('clos', 'id')->where(function ($q) {
                    return $q->where('course_id', (int) $this->input('course_id'));
                }),
            ],
            'bloom_id' => ['nullable', 'exists:blooms,id'],
            'main_question_no' => ['nullable', 'string', 'max:20'],
            'question_part' => ['nullable', 'string', 'max:20'],
            'question_label' => [
                'required',
                'string',
                'max:50',
                Rule::unique('question_clo_mappings', 'question_label')
                    ->where('assessment_component_id', (int) $this->input('assessment_component_id'))
                    ->whereNull('deleted_at'),
            ],
            'question_title' => ['nullable', 'string', 'max:255'],
            'question_description' => ['nullable', 'string'],
            'marks' => ['required', 'numeric', 'min:0.01', 'max:100'],
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

            $clo = Clo::query()->find((int) $this->input('clo_id'));
            $bloomId = $this->input('bloom_id');
            if ($bloomId !== null && $bloomId !== '' && $clo && (int) $bloomId !== (int) $clo->bloom_id) {
                $validator->errors()->add(
                    'bloom_id',
                    __('Bloom level must match the selected CLO taxonomy level.')
                );

                return;
            }

            $componentId = (int) $this->input('assessment_component_id');
            $component = AssessmentComponent::query()->find($componentId);
            if (! $component) {
                return;
            }

            $main = $this->input('main_question_no');
            if ($main !== null && $main !== '' && QuestionCloMapping::countForMainQuestion($componentId, (string) $main, null) >= 4) {
                $validator->errors()->add(
                    'main_question_no',
                    __('A maximum of four question parts is allowed per main question for this assessment component.')
                );

                return;
            }

            $existing = QuestionCloMapping::sumMarksForComponent($componentId, null);
            $incoming = (float) $this->input('marks');
            $cap = (float) $component->marks;

            if (round($existing + $incoming, 2) > round($cap, 2)) {
                $validator->errors()->add(
                    'marks',
                    __('Total mapped question marks for this component (:sum) plus this row (:incoming) exceed the component cap (:cap).', [
                        'sum' => rtrim(rtrim(number_format($existing, 2, '.', ''), '0'), '.'),
                        'incoming' => rtrim(rtrim(number_format($incoming, 2, '.', ''), '0'), '.'),
                        'cap' => rtrim(rtrim(number_format($cap, 2, '.', ''), '0'), '.'),
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
            'question_label.unique' => __('This question label is already used for the selected assessment component.'),
        ];
    }
}
