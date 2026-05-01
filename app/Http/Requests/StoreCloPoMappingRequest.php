<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCloPoMappingRequest extends FormRequest
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
            'program_id' => ['required', 'exists:programs,id'],
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where(function ($q) {
                    $q->where('program_id', (int) $this->input('program_id'));
                }),
            ],
            'clo_id' => [
                'required',
                Rule::exists('clos', 'id')->where(function ($q) {
                    $q->where('course_id', (int) $this->input('course_id'));
                }),
                Rule::unique('clo_po_mappings', 'clo_id')->where(function ($q) {
                    return $q->where('program_outcome_id', (int) $this->input('program_outcome_id'))
                        ->whereNull('deleted_at');
                }),
            ],
            'program_outcome_id' => [
                'required',
                Rule::exists('program_outcomes', 'id')->where(function ($q) {
                    $q->where('program_id', (int) $this->input('program_id'));
                }),
            ],
            'mapping_level' => ['required', 'integer', 'in:1,2,3'],
            'status_id' => ['required', 'exists:statuses,id'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'clo_id.unique' => __('This CLO is already mapped to the selected PO/PLO.'),
        ];
    }
}
