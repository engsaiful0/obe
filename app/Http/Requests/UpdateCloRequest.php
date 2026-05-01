<?php

namespace App\Http\Requests;

use App\Models\Clo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCloRequest extends FormRequest
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
        /** @var Clo $clo */
        $clo = $this->route('clo');

        return [
            'program_id' => ['required', 'exists:programs,id'],
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where(function ($q) {
                    $q->where('program_id', (int) $this->input('program_id'));
                }),
            ],
            'bloom_id' => ['required', 'exists:blooms,id'],
            'clo_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('clos', 'clo_code')
                    ->where(function ($q) {
                        $q->where('course_id', (int) $this->input('course_id'))
                            ->whereNull('deleted_at');
                    })
                    ->ignore($clo->id),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status_id' => ['required', 'exists:statuses,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'clo_code.unique' => __('This CLO code is already used for the selected course.'),
        ];
    }
}
