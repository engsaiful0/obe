<?php

namespace App\Http\Requests;

use App\Models\AssessmentComponent;
use App\Models\Clo;
use App\Models\QuestionCloMapping;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuestionCloMappingRequest extends FormRequest
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

        if ($this->has('question_part') && is_string($this->input('question_part'))) {
            $t = strtolower(trim($this->input('question_part')));
            $this->merge(['question_part' => $t === '' ? null : $t]);
        }

        if ($this->input('bloom_id') === '' || $this->input('bloom_id') === null) {
            $this->merge(['bloom_id' => null]);
        }

        if ($this->has('question_label') && is_string($this->input('question_label'))) {
            $this->merge(['question_label' => trim($this->input('question_label'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var QuestionCloMapping $row */
        $row = $this->route('question_clo_mapping');

        return [
            'main_question_marks' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'has_multiple_questions' => ['required', 'boolean'],
            'question_part' => ['nullable', Rule::in(QuestionCloMapping::ALLOWED_QUESTION_PARTS)],
            'question_label' => [
                'required',
                'string',
                'max:50',
                Rule::unique('question_clo_mappings', 'question_label')
                    ->where('assessment_component_id', (int) $row->assessment_component_id)
                    ->where('academic_session_id', (int) $row->academic_session_id)
                    ->whereNull('deleted_at')
                    ->ignore($row->id),
            ],
            'question_title' => ['nullable', 'string', 'max:255'],
            'question_description' => ['nullable', 'string'],
            'marks' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'clo_id' => [
                'required',
                Rule::exists('clos', 'id')->where(function ($q) use ($row) {
                    $q->where('course_id', (int) $row->course_id);
                }),
            ],
            'bloom_id' => ['nullable', 'exists:blooms,id'],
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

            /** @var QuestionCloMapping $row */
            $row = $this->route('question_clo_mapping');

            $acid = (int) $row->assessment_component_id;
            $asid = (int) $row->academic_session_id;
            $main = (string) $row->main_question_no;
            $multi = $this->boolean('has_multiple_questions');
            $part = $this->input('question_part');
            $mainCap = round((float) $this->input('main_question_marks'), 2);
            $incomingMarks = round((float) $this->input('marks'), 2);

            $othersCount = QuestionCloMapping::countForMainQuestion($acid, $asid, $main, $row->id);

            if (! $multi && $othersCount > 0) {
                $validator->errors()->add(
                    'has_multiple_questions',
                    __('Turn off “multiple questions” only when this is the only row for this main question (remove other parts first).')
                );

                return;
            }

            if ($multi && ($othersCount + 1) > 4) {
                $validator->errors()->add(
                    'has_multiple_questions',
                    __('A maximum of four parts per main question is allowed.')
                );

                return;
            }

            if ($multi) {
                if ($part === null || $part === '' || ! in_array($part, QuestionCloMapping::ALLOWED_QUESTION_PARTS, true)) {
                    $validator->errors()->add(
                        'question_part',
                        __('Question part must be one of :parts.', ['parts' => 'a, b, c, d'])
                    );

                    return;
                }

                $dupPart = QuestionCloMapping::query()
                    ->where('assessment_component_id', $acid)
                    ->where('academic_session_id', $asid)
                    ->where('main_question_no', $main)
                    ->where('question_part', $part)
                    ->whereKeyNot($row->id)
                    ->exists();
                if ($dupPart) {
                    $validator->errors()->add('question_part', __('This part letter is already used for the same main question.'));
                }
            } elseif ($part !== null && $part !== '') {
                $validator->errors()->add(
                    'question_part',
                    __('Question part must be empty when this main question is not split into parts.')
                );
            }

            $sumOthers = QuestionCloMapping::sumPartMarksUnderMain($acid, $asid, $main, $row->id);
            if (round($sumOthers + $incomingMarks, 2) > $mainCap + 0.0001) {
                $validator->errors()->add(
                    'marks',
                    __('Parts under this main question total :sum; with this row (:incoming) they exceed the main cap (:cap).', [
                        'sum' => rtrim(rtrim(number_format($sumOthers, 2, '.', ''), '0'), '.'),
                        'incoming' => rtrim(rtrim(number_format($incomingMarks, 2, '.', ''), '0'), '.'),
                        'cap' => rtrim(rtrim(number_format($mainCap, 2, '.', ''), '0'), '.'),
                    ])
                );
            }

            if ($incomingMarks > $mainCap + 0.0001) {
                $validator->errors()->add(
                    'marks',
                    __('This row\'s marks cannot exceed the main question cap (:cap).', [
                        'cap' => rtrim(rtrim(number_format($mainCap, 2, '.', ''), '0'), '.'),
                    ])
                );
            }

            $component = AssessmentComponent::query()->find($acid);
            if ($component) {
                $existing = QuestionCloMapping::sumMarksForComponent($acid, $asid, $row->id);
                $cap = (float) $component->marks;
                $componentName = (string) ($component->component_name ?? __('this component'));

                $sessionRow = $row->relationLoaded('academicSession')
                    ? $row->academicSession
                    : $row->academicSession()->first(['id', 'session_name', 'academic_year']);
                $sessionLabel = $sessionRow
                    ? $sessionRow->session_name.' ('.$sessionRow->academic_year.')'
                    : '';

                $combined = round($existing + $incomingMarks, 2);

                $fmt = static function ($n): string {
                    return rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
                };

                if ($combined > round($cap, 2) + 0.0001) {
                    $validator->errors()->add(
                        'marks',
                        __('The assessment component ":component" allows at most :cap marks for academic session ":session". Other mapped rows for this component and session (excluding this edit) sum to :existing. This row assigns :incoming. Combined that is :total, which exceeds the cap. Reduce this row\'s marks, adjust other mappings, or raise the component marks if appropriate.', [
                            'component' => $componentName,
                            'session' => $sessionLabel ?: (string) $asid,
                            'cap' => $fmt($cap),
                            'existing' => $fmt($existing),
                            'incoming' => $fmt($incomingMarks),
                            'total' => $fmt($combined),
                        ])
                    );
                }
            }

            $clo = Clo::query()->find((int) $this->input('clo_id'));
            $bloomId = $this->input('bloom_id');
            if ($bloomId !== null && $bloomId !== '' && $clo && (int) $bloomId !== (int) $clo->bloom_id) {
                $validator->errors()->add(
                    'bloom_id',
                    __('Bloom level must match the selected CLO taxonomy level.')
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
            'question_label.unique' => __('This question label is already used for this assessment component and academic session.'),
        ];
    }
}
