<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherCourseMarksImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->filled('confirmed_rows')) {
            return [
                'confirmed_rows' => ['required', 'string'],
            ];
        }

        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value instanceof \Illuminate\Http\UploadedFile) {
                        $fail(__('Invalid upload.'));

                        return;
                    }
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (! in_array($ext, ['xlsx', 'xls', 'csv', 'txt'], true)) {
                        $fail(__('File must be .xlsx, .xls, or .csv.'));
                    }
                },
            ],
        ];
    }
}
