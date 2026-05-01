<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesStudentMarkContext;
use Illuminate\Foundation\Http\FormRequest;

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
                'rows' => ['required', 'array', 'min:1'],
                'rows.*.student_id' => ['required', 'integer', 'exists:students,id'],
                'rows.*.total_marks' => ['required', 'numeric', 'min:0'],
                'rows.*.questions' => ['required', 'array', 'min:1'],
                'rows.*.questions.*.question_clo_mapping_id' => ['required', 'integer', 'exists:question_clo_mappings,id'],
                'rows.*.questions.*.obtained_marks' => ['required', 'numeric', 'min:0'],
            ]
        );
    }
}
