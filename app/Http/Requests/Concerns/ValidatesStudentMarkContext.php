<?php

namespace App\Http\Requests\Concerns;

use App\Models\RelatedTo;
use Illuminate\Validation\Rule;

trait ValidatesStudentMarkContext
{
    protected function coerceSectionId(): void
    {
        if ($this->input('section_id') === '' || $this->input('section_id') === null) {
            $this->merge(['section_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function studentMarkContextRules(): array
    {
        $programId = (int) $this->input('program_id');
        $courseId = (int) $this->input('course_id');
        $batchId = (int) $this->input('batch_id');

        return [
            'academic_session_id' => ['required', 'integer', 'exists:academic_sessions,id'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
            'course_id' => [
                'required',
                'integer',
                Rule::exists('courses', 'id')->where(fn ($q) => $q->where('program_id', $programId)),
            ],
            'batch_id' => [
                'required',
                'integer',
                Rule::exists('batches', 'id')->where(fn ($q) => $q->where('program_id', $programId)),
            ],
            'section_id' => [
                'nullable',
                'integer',
                Rule::exists('sections', 'id')->where(function ($q) use ($programId, $batchId) {
                    return $q->where('program_id', $programId)->where('batch_id', $batchId);
                }),
            ],
            'assessment_component_id' => [
                'required',
                'integer',
                Rule::exists('assessment_components', 'id')->where(fn ($q) => $q->where('course_id', $courseId)),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function obeMarkStatusRules(): array
    {
        $relatedId = RelatedTo::where('name', 'OBE')->value('id');

        return [
            'status_id' => [
                'required',
                'integer',
                Rule::exists('statuses', 'id')->where(fn ($q) => $q->where('related_to_id', $relatedId)),
            ],
        ];
    }
}
