<?php

namespace App\Http\Requests;

use App\Models\Peo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $programId = (int) $this->input('program_id');
        $routePeo = $this->route('peo');
        $peoId = $routePeo instanceof Peo ? $routePeo->getKey() : null;

        return [
            'program_id' => ['required', 'exists:programs,id'],
            'peo_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('peos', 'peo_code')
                    ->where(fn ($q) => $q->where('program_id', $programId)->whereNull('deleted_at'))
                    ->ignore($peoId),
            ],
            'peo_title' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status_id' => ['required', 'exists:statuses,id'],
        ];
    }
}
