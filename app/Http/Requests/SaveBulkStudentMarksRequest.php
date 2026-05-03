<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesStudentMarkContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveBulkStudentMarksRequest extends FormRequest
{
    use ValidatesStudentMarkContext;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->coerceSectionId();
        $this->mergeDefaultObeStatusIfMissing();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $courseId = (int) $this->input('course_id');

        return array_merge(
            $this->studentMarkBulkMinimalRules(),
            $this->obeMarkStatusRules(),
            [
                'rows' => ['required', 'array', 'min:1'],
                'rows.*.student_id' => ['required', 'integer', 'exists:students,id'],
                'rows.*.attendance_marks' => ['sometimes', 'nullable', 'numeric', 'min:0'],
                'rows.*.component_marks' => ['required', 'array', 'min:1'],
                'rows.*.component_marks.*.assessment_component_id' => [
                    'required',
                    'integer',
                    Rule::exists('assessment_components', 'id')->where(fn ($q) => $q->where('course_id', $courseId)),
                ],
                'rows.*.component_marks.*.total_marks' => ['required', 'numeric', 'min:0'],
                'rows.*.component_marks.*.questions' => ['required', 'array', 'min:1'],
                'rows.*.component_marks.*.questions.*.question_clo_mapping_id' => ['required', 'integer', 'exists:question_clo_mappings,id'],
                'rows.*.component_marks.*.questions.*.obtained_marks' => ['required', 'numeric', 'min:0'],
            ]
        );
    }

    public function withValidator($validator): void
    {
        $validator->after(function (\Illuminate\Contracts\Validation\Validator $v): void {
            $rows = $this->input('rows');
            if (! is_array($rows)) {
                return;
            }
            foreach ($rows as $i => $row) {
                if (! is_array($row) || ! isset($row['component_marks']) || ! is_array($row['component_marks'])) {
                    continue;
                }
                $ids = collect($row['component_marks'])->pluck('assessment_component_id')->filter();
                if ($ids->count() !== $ids->unique()->count()) {
                    $v->errors()->add('rows.'.$i.'.component_marks', __('Duplicate assessment component in row.'));
                }
            }
        });
    }
}
