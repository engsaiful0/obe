<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBloomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:blooms,name'],
            'level_order' => [
                'required',
                'integer',
                'min:1',
                'max:10',
                'unique:blooms,level_order',
            ],
            'description' => ['nullable', 'string'],
            'status_id' => ['required', 'exists:statuses,id'],
        ];
    }
}
