<?php

namespace Database\Seeders;

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

        $programId = (int) $course->program_id;

        $midterm = AssessmentComponent::query()->where('course_id', $course->id)->where('component_name', 'Midterm')->first();
        $final = AssessmentComponent::query()->where('course_id', $course->id)->where('component_name', 'Final')->first();

        $cloByCode = Clo::query()->where('course_id', $course->id)->get()->keyBy('clo_code');

        $defs = [];

        if ($midterm && $cloByCode->has('CLO1') && $cloByCode->has('CLO2') && $cloByCode->has('CLO3')) {
            $defs = array_merge($defs, [
                [$midterm, 'Q1', 'a', 'Q1a', 5, 'CLO1'],
                [$midterm, 'Q1', 'b', 'Q1b', 2, 'CLO1'],
                [$midterm, 'Q1', 'c', 'Q1c', 3, 'CLO2'],
                [$midterm, 'Q2', 'a', 'Q2a', 5, 'CLO2'],
                [$midterm, 'Q2', 'b', 'Q2b', 5, 'CLO3'],
            ]);
        }

        if ($final && $cloByCode->has('CLO1') && $cloByCode->has('CLO2') && $cloByCode->has('CLO3')) {
            $defs = array_merge($defs, [
                [$final, null, null, 'Q1', 15, 'CLO1'],
                [$final, null, null, 'Q2', 15, 'CLO2'],
                [$final, null, null, 'Q3', 10, 'CLO3'],
            ]);
        }

        foreach ($defs as [$component, $mainNo, $part, $label, $marks, $cloCode]) {
            $clo = $cloByCode[$cloCode];
            QuestionCloMapping::query()->updateOrCreate(
                [
                    'assessment_component_id' => $component->id,
                    'question_label' => $label,
                ],
                [
                    'program_id' => $programId,
                    'course_id' => $course->id,
                    'clo_id' => $clo->id,
                    'bloom_id' => $clo->bloom_id,
                    'main_question_no' => $mainNo,
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
