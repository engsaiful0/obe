<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $programId = (int) $this->input('program_id');

        return [
            'program_id' => ['required', 'exists:programs,id'],
            'peo_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('peos', 'peo_code')->where(
                    fn ($q) => $q->where('program_id', $programId)->whereNull('deleted_at')
                ),
            ],
            'peo_title' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status_id' => ['required', 'exists:statuses,id'],
        ];
    }
}
