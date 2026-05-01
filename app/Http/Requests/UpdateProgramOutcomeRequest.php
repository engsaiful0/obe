<?php

namespace App\Http\Requests;

use App\Models\ProgramOutcome;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgramOutcomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var ProgramOutcome $outcome */
        $outcome = $this->route('program_outcome');
        $programId = (int) $this->input('program_id');

        return [
            'program_id' => ['required', 'exists:programs,id'],
            'outcome_type' => ['required', Rule::in(['PO', 'PLO'])],
            'outcome_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('program_outcomes', 'outcome_code')
                    ->where(fn ($query) => $query->where('program_id', $programId))
                    ->ignore($outcome),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['nullable', Rule::in(StoreProgramOutcomeRequest::categoryValues())],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
