<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesStudentMarkContext;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentMarkRequest extends FormRequest
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
        return array_merge($this->obeMarkStatusRules(), [
            'total_marks' => ['required', 'numeric', 'min:0'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_clo_mapping_id' => ['required', 'integer', 'exists:question_clo_mappings,id'],
            'questions.*.obtained_marks' => ['required', 'numeric', 'min:0'],
        ]);
    }

    /** Context is inferred from the existing record — do not allow changing linkage via this request. */
    protected function studentMarkContextRules(): array
    {
        return [];
    }
}
