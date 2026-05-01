<?php

namespace App\Http\Requests;

use App\Models\Bloom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBloomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Bloom $bloom */
        $bloom = $this->route('bloom');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('blooms', 'name')->ignore($bloom)],
            'level_order' => [
                'required',
                'integer',
                'min:1',
                'max:10',
                Rule::unique('blooms', 'level_order')->ignore($bloom),
            ],
            'description' => ['nullable', 'string'],
            'status_id' => ['required', 'exists:statuses,id'],
        ];
    }
}
