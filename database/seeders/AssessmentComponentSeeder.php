<?php

namespace Database\Seeders;

use App\Models\AssessmentComponent;
use App\Models\Course;
use App\Models\RelatedTo;
use App\Models\Status;
use Illuminate\Database\Seeder;

class AssessmentComponentSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::query()->where('course_code', 'CSE-101')->first();
        if (! $course) {
            $this->command?->warn('AssessmentComponentSeeder skipped: course CSE-101 not found.');

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
            $this->command?->warn('AssessmentComponentSeeder skipped: no status.');

            return;
        }

        $programId = (int) $course->program_id;

        $rows = [
            ['Attendance', 'Attendance', 10, false],
            ['Quiz', 'Quiz', 10, false],
            ['Assignment', 'Assignment', 10, false],
            ['Midterm', 'Midterm', 30, true],
            ['Final', 'Final', 40, true],
        ];

        foreach ($rows as [$name, $type, $marks, $multiple]) {
            AssessmentComponent::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'component_name' => $name,
                ],
                [
                    'program_id' => $programId,
                    'component_type' => $type,
                    'marks' => $marks,
                    'has_multiple_questions' => $multiple,
                    'weight_percentage' => null,
                    'status_id' => $statusId,
                    'remarks' => null,
                ]
            );
        }
    }
}
