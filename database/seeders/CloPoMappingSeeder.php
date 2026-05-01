<?php

namespace Database\Seeders;

use App\Models\Clo;
use App\Models\CloPoMapping;
use App\Models\Course;
use App\Models\ProgramOutcome;
use App\Models\RelatedTo;
use App\Models\Status;
use Illuminate\Database\Seeder;

class CloPoMappingSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::query()->where('course_code', 'CSE-101')->first();
        if (! $course) {
            $this->command?->warn('CloPoMappingSeeder skipped: no course CSE-101.');

            return;
        }

        $programId = (int) $course->program_id;
        $obeRelatedId = RelatedTo::query()->where('name', 'OBE')->value('id');
        $statusId = Status::query()
            ->when($obeRelatedId, fn ($q) => $q->where('related_to_id', $obeRelatedId))
            ->where('status_name', 'Active')
            ->value('id');

        if (! $statusId) {
            $statusId = Status::query()
                ->when($obeRelatedId, fn ($q) => $q->where('related_to_id', $obeRelatedId))
                ->orderBy('status_name')
                ->value('id');
        }
        if (! $statusId) {
            $statusId = Status::query()->orderBy('id')->value('id');
        }

        if (! $statusId) {
            $this->command?->warn('CloPoMappingSeeder skipped: no status row.');

            return;
        }

        $cloByCode = Clo::query()->where('course_id', $course->id)->get()->keyBy('clo_code');
        $poByCode = ProgramOutcome::query()->where('program_id', $programId)->get()->keyBy('outcome_code');

        $rows = [
            ['CLO1', 'PO1', 3],
            ['CLO1', 'PO10', 1],
            ['CLO2', 'PO1', 2],
            ['CLO2', 'PO2', 3],
            ['CLO3', 'PO2', 3],
            ['CLO3', 'PO5', 2],
        ];

        foreach ($rows as [$cc, $poc, $level]) {
            $clo = $cloByCode->get($cc);
            $po = $poByCode->get($poc);
            if (! $clo || ! $po) {
                $this->command?->warn("CloPoMappingSeeder skipped row {$cc}/{$poc}: missing CLO or outcome.");

                continue;
            }

            CloPoMapping::query()->updateOrCreate(
                [
                    'clo_id' => $clo->id,
                    'program_outcome_id' => $po->id,
                ],
                [
                    'program_id' => $programId,
                    'course_id' => $course->id,
                    'mapping_level' => $level,
                    'status_id' => $statusId,
                    'remarks' => null,
                ]
            );
        }
    }
}
