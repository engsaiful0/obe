<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesStudentMarkContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentMarkRequest extends FormRequest
{
    use ValidatesStudentMarkContext;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->coerceSectionId();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $multi = $this->hasStructuredComponentMarks();

        $context = $multi
            ? $this->studentMarkBulkMinimalRules()
            : $this->studentMarkContextRules();

        $courseId = (int) $this->input('course_id');

        $rules = array_merge($context, $this->obeMarkStatusRules(), [
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ]);

        if ($multi) {
            $rules['component_marks'] = ['required', 'array', 'min:1'];
            $rules['component_marks.*.assessment_component_id'] = [
                'required',
                'integer',
                Rule::exists('assessment_components', 'id')->where(fn ($q) => $q->where('course_id', $courseId)),
            ];
            $rules['component_marks.*.total_marks'] = ['required', 'numeric', 'min:0'];
            $rules['component_marks.*.questions'] = ['required', 'array', 'min:1'];
            $rules['component_marks.*.questions.*.question_clo_mapping_id'] = ['required', 'integer', 'exists:question_clo_mappings,id'];
            $rules['component_marks.*.questions.*.obtained_marks'] = ['required', 'numeric', 'min:0'];
        } else {
            $rules['total_marks'] = ['required', 'numeric', 'min:0'];
            $rules['questions'] = ['required', 'array', 'min:1'];
            $rules['questions.*.question_clo_mapping_id'] = ['required', 'integer', 'exists:question_clo_mappings,id'];
            $rules['questions.*.obtained_marks'] = ['required', 'numeric', 'min:0'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function (\Illuminate\Contracts\Validation\Validator $v): void {
            if (! $this->hasStructuredComponentMarks()) {
                return;
            }
            $rows = $this->input('component_marks');
            if (! is_array($rows)) {
                return;
            }
            $ids = collect($rows)->pluck('assessment_component_id')->filter();
            if ($ids->count() !== $ids->unique()->count()) {
                $v->errors()->add('component_marks', __('Duplicate assessment component in submission.'));
            }
        });
    }

    /** @phpstan-return bool */
    protected function hasStructuredComponentMarks(): bool
    {
        $raw = $this->input('component_marks');

        return is_array($raw) && $raw !== [] && array_key_exists(0, $raw);
    }
}
