<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesStudentMarkContext;
use Illuminate\Foundation\Http\FormRequest;

class ResetStudentMarksRequest extends FormRequest
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
        $bulk = $this->boolean('bulk_all');

        return array_merge(
            $bulk ? $this->studentMarkContextWithoutComponentRules() : $this->studentMarkContextRules(),
            ['bulk_all' => ['sometimes', 'boolean']]
        );
    }
}
