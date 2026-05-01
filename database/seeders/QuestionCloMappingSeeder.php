<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\AssessmentComponent;
use App\Models\Clo;
use App\Models\Course;
use App\Models\QuestionCloMapping;
use App\Models\RelatedTo;
use App\Models\Status;
use Illuminate\Database\Seeder;

class QuestionCloMappingSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::query()->where('course_code', 'CSE-101')->first();
        if (! $course) {
            $this->command?->warn('QuestionCloMappingSeeder skipped: CSE-101 not found.');

            return;
        }

        $obeRelatedId = RelatedTo::query()->where('name', 'OBE')->value('id');
        $statusId = Status::query()
            ->where('related_to_id', $obeRelatedId)
            ->where('status_name', 'Active')
            ->value('id');

        if (! $statusId && $obeRelatedId) {
            $statusId = Status::query()
                ->where('related_to_id', $obeRelatedId)
                ->orderBy('status_name')
                ->value('id');
        }
        if (! $statusId) {
            $statusId = Status::query()->orderBy('id')->value('id');
        }
        if (! $statusId) {
            $this->command?->warn('QuestionCloMappingSeeder skipped: no status.');

            return;
        }

        $session = AcademicSession::query()->orderBy('id')->first();
        if (! $session) {
            $this->command?->warn('QuestionCloMappingSeeder skipped: add at least one academic_sessions row first.');

            return;
        }

        $programId = (int) $course->program_id;

        $midterm = AssessmentComponent::query()->where('course_id', $course->id)->where('component_name', 'Midterm')->first();
        $final = AssessmentComponent::query()->where('course_id', $course->id)->where('component_name', 'Final')->first();

        $cloByCode = Clo::query()->where('course_id', $course->id)->get()->keyBy('clo_code');

        $defs = [];

        if ($midterm && $cloByCode->has('CLO1') && $cloByCode->has('CLO2') && $cloByCode->has('CLO3')) {
            $defs = array_merge($defs, [
                [$midterm, 'Q1', 10.0, true, 'a', 'Q1a', 2.5, 'CLO1'],
                [$midterm, 'Q1', 10.0, true, 'b', 'Q1b', 2.5, 'CLO2'],
                [$midterm, 'Q1', 10.0, true, 'c', 'Q1c', 2.5, 'CLO2'],
                [$midterm, 'Q1', 10.0, true, 'd', 'Q1d', 2.5, 'CLO3'],
                [$midterm, 'Q2', 10.0, true, 'a', 'Q2a', 5.0, 'CLO1'],
                [$midterm, 'Q2', 10.0, true, 'b', 'Q2b', 5.0, 'CLO2'],
                [$midterm, 'Q3', 10.0, true, 'a', 'Q3a', 10.0, 'CLO3'],
            ]);
        }

        if ($final && $cloByCode->has('CLO1') && $cloByCode->has('CLO2') && $cloByCode->has('CLO3')) {
            $defs = array_merge($defs, [
                [$final, 'Q1', 15.0, false, null, 'Q1', 15.0, 'CLO1'],
                [$final, 'Q2', 15.0, false, null, 'Q2', 15.0, 'CLO2'],
                [$final, 'Q3', 10.0, false, null, 'Q3', 10.0, 'CLO3'],
            ]);
        }

        foreach ($defs as [$component, $mainNo, $mainMarks, $hasMulti, $part, $label, $marks, $cloCode]) {
            $clo = $cloByCode[$cloCode];
            QuestionCloMapping::query()->updateOrCreate(
                [
                    'assessment_component_id' => $component->id,
                    'academic_session_id' => $session->id,
                    'question_label' => $label,
                ],
                [
                    'program_id' => $programId,
                    'course_id' => $course->id,
                    'academic_session_id' => $session->id,
                    'clo_id' => $clo->id,
                    'bloom_id' => $clo->bloom_id,
                    'main_question_no' => $mainNo,
                    'main_question_marks' => $mainMarks,
                    'has_multiple_questions' => $hasMulti,
                    'question_part' => $part,
                    'question_title' => null,
                    'question_description' => null,
                    'marks' => $marks,
                    'status_id' => $statusId,
                    'remarks' => null,
                ]
            );
        }
    }
}
