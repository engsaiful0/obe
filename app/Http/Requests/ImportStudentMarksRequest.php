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
        $this->mergeDefaultObeStatusIfMissing();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $bulk = $this->boolean('bulk_all');

        return array_merge(
            $bulk ? $this->studentMarkBulkImportRules() : $this->studentMarkContextRules(),
            ['bulk_all' => ['sometimes', 'boolean']],
            $this->obeMarkStatusRules(),
            [
                'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:8192'],
            ]
        );
    }
}
