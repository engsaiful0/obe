<?php

namespace App\Http\Requests\Concerns;

use App\Models\CourseAssignment;
use Illuminate\Contracts\Validation\Validator;

trait EnsuresUniqueCourseAssignment
{
    protected function ensureCourseAssignmentCombinationIsUnique(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        $query = CourseAssignment::query()
            ->where('academic_session_id', (int) $this->input('academic_session_id'))
            ->where('program_id', (int) $this->input('program_id'))
            ->where('batch_id', (int) $this->input('batch_id'))
            ->where('semester_id', (int) $this->input('semester_id'))
            ->where('course_id', (int) $this->input('course_id'))
            ->where('section_id', (int) $this->input('section_id'));

        $routeAssignment = $this->route('course_assignment');
        if ($routeAssignment instanceof CourseAssignment) {
            $query->whereKeyNot($routeAssignment->getKey());
        }

        if ($query->exists()) {
            $validator->errors()->add(
                'course_id',
                __('A teacher is already assigned to this course for the selected section and academic context.')
            );
        }
    }

    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->ensureCourseAssignmentCombinationIsUnique($validator);
        });
    }
}
