<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesStudentMarkContext;
use Illuminate\Foundation\Http\FormRequest;

class ImportStudentMarksRequest extends FormRequest
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
                'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:8192'],
            ]
        );
    }
}
