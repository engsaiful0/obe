<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CourseFileCqiRequest extends FormRequest
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
        return [
            'strengths' => ['nullable', 'string', 'max:10000'],
            'weaknesses' => ['nullable', 'string', 'max:10000'],
            'problems' => ['nullable', 'string', 'max:10000'],
            'improvements' => ['nullable', 'string', 'max:10000'],
            'recommendations' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
