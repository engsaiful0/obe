<?php

namespace App\Http\Requests;

use App\Models\Mission;
use App\Support\ObeStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('type') === 'University') {
            $this->merge(['department_id' => null]);
        }
        if ($this->input('type') === 'Department') {
            $this->merge(['university_id' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['University', 'Department'])],
            'university_id' => [
                Rule::requiredIf(fn () => $this->input('type') === 'University'),
                'nullable',
                'exists:universities,id',
            ],
            'department_id' => [
                Rule::requiredIf(fn () => $this->input('type') === 'Department'),
                'nullable',
                'exists:departments,id',
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'status_id' => ['required', 'exists:statuses,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $statusId = (int) $this->input('status_id');
            if (! ObeStatus::isActiveStatusId($statusId)) {
                return;
            }
            $q = Mission::query()->where('type', $this->input('type'))->where('status_id', $statusId);
            if ($this->input('type') === 'University') {
                $q->where('university_id', (int) $this->input('university_id'));
            } else {
                $q->where('department_id', (int) $this->input('department_id'));
            }
            if ($q->exists()) {
                $v->errors()->add(
                    'status_id',
                    __('Only one active mission is allowed for this university or department.')
                );
            }
        });
    }
}
