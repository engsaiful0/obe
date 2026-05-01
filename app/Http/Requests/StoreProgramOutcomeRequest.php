<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgramOutcomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return list<string> */
    public static function categoryValues(): array
    {
        return [
            'Knowledge',
            'Skill',
            'Attitude',
            'Ethics',
            'Communication',
            'Leadership',
            'Lifelong Learning',
        ];
    }

    public function rules(): array
    {
        $programId = (int) $this->input('program_id');

        return [
            'program_id' => ['required', 'exists:programs,id'],
            'outcome_type' => ['required', Rule::in(['PO', 'PLO'])],
            'outcome_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('program_outcomes', 'outcome_code')->where(
                    fn ($query) => $query->where('program_id', $programId)
                ),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['nullable', Rule::in(self::categoryValues())],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];
    }
}
