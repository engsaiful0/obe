<?php

namespace App\Http\Requests;

use App\Models\AssessmentComponent;
use App\Models\Clo;
use App\Models\QuestionCloMapping;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBulkQuestionCloMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $mains = $this->input('mains');

        if (! is_array($mains)) {
            return;
        }

        foreach ($mains as $i => $main) {
            if (! is_array($main)) {
                continue;
            }

            $mains[$i]['main_question_no'] = isset($main['main_question_no'])
                ? trim((string) $main['main_question_no'])
                : '';
            $mains[$i]['has_multiple_questions'] = filter_var(
                $main['has_multiple_questions'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            $parts = is_array($main['parts'] ?? null) ? $main['parts'] : [];
            foreach ($parts as $j => $part) {
                if (! is_array($part)) {
                    continue;
                }
                $qp = isset($part['question_part']) ? strtolower(trim((string) $part['question_part'])) : '';
                $mains[$i]['parts'][$j]['question_part'] = $qp === '' ? null : $qp;
                $mains[$i]['parts'][$j]['question_label'] = isset($part['question_label'])
                    ? trim((string) $part['question_label'])
                    : '';
                if (($part['bloom_id'] ?? '') === '' || $part['bloom_id'] === null) {
                    $mains[$i]['parts'][$j]['bloom_id'] = null;
                }
            }
        }

        $this->merge(['mains' => $mains]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $courseId = (int) $this->input('course_id');

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
            'mains' => ['required', 'array', 'min:1'],
            'mains.*.main_question_no' => ['required', 'string', 'max:20'],
            'mains.*.main_question_marks' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'mains.*.has_multiple_questions' => ['required', 'boolean'],
            'mains.*.parts' => ['required', 'array', 'min:1'],
            'mains.*.parts.*.question_part' => ['nullable', 'string', 'max:1'],
            'mains.*.parts.*.question_label' => ['required', 'string', 'max:50'],
            'mains.*.parts.*.marks' => ['required', 'numeric', 'min:0.01', 'max:100'],
            'mains.*.parts.*.clo_id' => [
                'required',
                'integer',
                Rule::exists('clos', 'id')->where(function ($q) use ($courseId) {
                    $q->where('course_id', $courseId);
                }),
            ],
            'mains.*.parts.*.bloom_id' => ['nullable', 'exists:blooms,id'],
            'mains.*.parts.*.status_id' => ['required', 'exists:statuses,id'],
            'mains.*.parts.*.remarks' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $componentId = (int) $this->input('assessment_component_id');
            $component = AssessmentComponent::query()->find($componentId);
            if (! $component) {
                return;
            }

            $seenMains = [];

            foreach ($this->input('mains', []) as $mainIndex => $main) {
                if (! is_array($main)) {
                    continue;
                }

                $mainNoRaw = isset($main['main_question_no']) ? trim((string) $main['main_question_no']) : '';
                if ($mainNoRaw === '') {
                    continue;
                }

                $keyMain = strtolower($mainNoRaw);
                if (isset($seenMains[$keyMain])) {
                    $validator->errors()->add(
                        'mains.'.$mainIndex.'.main_question_no',
                        __('Duplicate main question number in this submission.')
                    );

                    continue;
                }
                $seenMains[$keyMain] = true;

                $multi = filter_var($main['has_multiple_questions'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $mainCap = round((float) ($main['main_question_marks'] ?? 0), 2);
                $parts = is_array($main['parts'] ?? null) ? $main['parts'] : [];
                $n = count($parts);

                if ($multi) {
                    if ($n < 1) {
                        $validator->errors()->add(
                            'mains.'.$mainIndex.'.parts',
                            __('Add at least one part when this main question is split into parts.')
                        );
                    }
                    if ($n > 4) {
                        $validator->errors()->add(
                            'mains.'.$mainIndex.'.parts',
                            __('A maximum of four parts per main question is allowed.')
                        );
                    }
                } elseif ($n !== 1) {
                    $validator->errors()->add(
                        'mains.'.$mainIndex.'.parts',
                        __('Exactly one row is allowed when multiple questions are disabled.')
                    );
                }

                $sumParts = 0.0;
                $usedParts = [];

                foreach ($parts as $pIndex => $part) {
                    if (! is_array($part)) {
                        continue;
                    }

                    $pq = isset($part['question_part']) ? strtolower(trim((string) $part['question_part'])) : '';
                    $rowMarks = round((float) ($part['marks'] ?? 0), 2);
                    if ($rowMarks > $mainCap + 0.0001) {
                        $validator->errors()->add(
                            'mains.'.$mainIndex.'.parts.'.$pIndex.'.marks',
                            __('Part marks must not exceed the main question marks (:cap).', [
                                'cap' => rtrim(rtrim(number_format($mainCap, 2, '.', ''), '0'), '.'),
                            ])
                        );
                    }

                    $cloId = isset($part['clo_id']) ? (int) $part['clo_id'] : 0;
                    $bloomInput = array_key_exists('bloom_id', $part) ? $part['bloom_id'] : null;
                    if ($multi) {
                        if ($pq === null || $pq === '' || ! in_array($pq, QuestionCloMapping::ALLOWED_QUESTION_PARTS, true)) {
                            $validator->errors()->add(
                                'mains.'.$mainIndex.'.parts.'.$pIndex.'.question_part',
                                __('Question part must be one of :parts.', ['parts' => 'a, b, c, d'])
                            );
                        }
                        if ($pq !== '' && isset($usedParts[$pq])) {
                            $validator->errors()->add(
                                'mains.'.$mainIndex.'.parts.'.$pIndex.'.question_part',
                                __('Duplicate part letter within the same main question.')
                            );
                        }
                        $usedParts[$pq] = true;
                    } else {
                        if ($pq !== null && $pq !== '') {
                            $validator->errors()->add(
                                'mains.'.$mainIndex.'.parts.'.$pIndex.'.question_part',
                                __('Question part must be empty when this main question is not split into parts.')
                            );
                        }
                    }

                    $clo = Clo::query()->find($cloId);
                    if ($clo && ($bloomInput !== null && $bloomInput !== '')
                        && (int) $bloomInput !== (int) $clo->bloom_id) {
                        $validator->errors()->add(
                            'mains.'.$mainIndex.'.parts.'.$pIndex.'.bloom_id',
                            __('Bloom level must match the selected CLO taxonomy level.')
                        );
                    }

                    $sumParts += $rowMarks;
                }

                if (round($sumParts, 2) > $mainCap + 0.0001) {
                    $validator->errors()->add(
                        'mains.'.$mainIndex.'.main_question_marks',
                        __('Sum of marks for main :main (:total) exceeds the declared main marks (:cap).', [
                            'main' => $mainNoRaw,
                            'total' => rtrim(rtrim(number_format($sumParts, 2, '.', ''), '0'), '.'),
                            'cap' => rtrim(rtrim(number_format($mainCap, 2, '.', ''), '0'), '.'),
                        ])
                    );
                }
            }

            $allLabels = [];
            foreach ($this->input('mains', []) as $mainIndex => $main) {
                foreach ($main['parts'] ?? [] as $pi => $part) {
                    $label = isset($part['question_label']) ? trim((string) $part['question_label']) : '';
                    if ($label === '') {
                        continue;
                    }
                    $lk = strtolower($label);
                    if (isset($allLabels[$lk])) {
                        $validator->errors()->add(
                            'mains.'.$mainIndex.'.parts.'.$pi.'.question_label',
                            __('Duplicate question label in this submission.')
                        );
                    }
                    $allLabels[$lk] = true;

                    $exists = QuestionCloMapping::query()
                        ->where('assessment_component_id', $componentId)
                        ->whereRaw('LOWER(question_label) = ?', [$lk])
                        ->whereNull('deleted_at')
                        ->exists();
                    if ($exists) {
                        $validator->errors()->add(
                            'mains.'.$mainIndex.'.parts.'.$pi.'.question_label',
                            __('The label ":label" is already used for this assessment component.', ['label' => $label])
                        );
                    }
                }
            }

            $payloadTotal = 0.0;
            foreach ($this->input('mains', []) as $main) {
                foreach ($main['parts'] ?? [] as $part) {
                    $payloadTotal += round((float) ($part['marks'] ?? 0), 2);
                }
            }

            $existing = QuestionCloMapping::sumMarksForComponent($componentId, null);
            $cap = (float) $component->marks;

            if (round($existing + $payloadTotal, 2) > round($cap, 2) + 0.0001) {
                $validator->errors()->add(
                    'assessment_component_id',
                    __('Total mapped marks (:sum plus :new) would exceed this component\'s marks cap (:cap).', [
                        'sum' => rtrim(rtrim(number_format($existing, 2, '.', ''), '0'), '.'),
                        'new' => rtrim(rtrim(number_format($payloadTotal, 2, '.', ''), '0'), '.'),
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
            'mains.required' => __('Define at least one main question.'),
        ];
    }
}
