<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesStudentMarkContext;
use Illuminate\Foundation\Http\FormRequest;

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
        return array_merge(
            $this->studentMarkContextRules(),
            $this->obeMarkStatusRules(),
            [
                'student_id' => ['required', 'integer', 'exists:students,id'],
                'total_marks' => ['required', 'numeric', 'min:0'],
                'questions' => ['required', 'array', 'min:1'],
                'questions.*.question_clo_mapping_id' => ['required', 'integer', 'exists:question_clo_mappings,id'],
                'questions.*.obtained_marks' => ['required', 'numeric', 'min:0'],
            ]
        );
    }
}
