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
        return $this->studentMarkContextRules();
    }
}
