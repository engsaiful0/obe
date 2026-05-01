<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\ProgramOutcome;
use Illuminate\Database\Seeder;

class ProgramOutcomeSeeder extends Seeder
{
    /**
     * Seed standard engineering PO outcomes (example: B.Sc. Engineering / CSE program).
     */
    public function run(): void
    {
        $program = Program::query()
            ->where(function ($q) {
                $q->where('program_code', 'like', '%CSE%')
                    ->orWhere('program_name', 'like', '%Computer%')
                    ->orWhere('program_name', 'like', '%CSE%');
            })
            ->orderBy('id')
            ->first();

        if (! $program) {
            $program = Program::query()->orderBy('id')->first();
        }

        if (! $program) {
            $this->command?->warn('ProgramOutcomeSeeder skipped: no program found.');

            return;
        }

        $records = [
            ['PO1', 'Engineering Knowledge', 'Apply knowledge of mathematics, natural science, computing, and discipline-specific fundamentals to complex engineering problems.', 'Knowledge'],
            ['PO2', 'Problem Analysis', 'Identify, formulate, review research literature, and analyze complex engineering problems to reach substantiated conclusions.', 'Knowledge'],
            ['PO3', 'Design/Development of Solutions', 'Design solutions for complex engineering problems with appropriate consideration for health, safety, cultural, societal, and environmental considerations.', 'Knowledge'],
            ['PO4', 'Investigation', 'Investigate complex engineering problems using research-based knowledge and research methods including experiment design, analysis, and interpretation of information.', 'Knowledge'],
            ['PO5', 'Modern Tool Usage', 'Create, select, and apply appropriate techniques, resources, and modern engineering and IT tools.', 'Knowledge'],
            ['PO6', 'The Engineer and Society', 'Apply reasoning informed by contextual knowledge to assess societal, health, safety, legal, and cultural issues relevant to professional engineering practice.', 'Attitude'],
            ['PO7', 'Environment and Sustainability', 'Understand and evaluate the sustainability and impact of professional engineering solutions in societal and environmental contexts.', 'Leadership'],
            ['PO8', 'Ethics', 'Apply ethical principles and commit to responsibilities and norms of the engineering practice.', 'Ethics'],
            ['PO9', 'Individual and Team Work', 'Function effectively as an individual, and as a member or leader in diverse teams and in multidisciplinary settings.', 'Communication'],
            ['PO10', 'Communication', 'Communicate effectively on complex engineering activities with the engineering community and society at large.', 'Communication'],
            ['PO11', 'Project Management and Finance', 'Demonstrate knowledge and understanding of engineering and management principles and economic decision-making.', 'Leadership'],
            ['PO12', 'Lifelong Learning', 'Recognize the need for, and have the preparation and ability to engage in independent and life-long learning.', 'Lifelong Learning'],
        ];

        foreach ($records as [$code, $title, $desc, $category]) {
            ProgramOutcome::query()->firstOrCreate(
                [
                    'program_id' => $program->id,
                    'outcome_code' => $code,
                ],
                [
                    'outcome_type' => 'PO',
                    'title' => $title,
                    'description' => $desc,
                    'category' => $category,
                    'status' => 'Active',
                ]
            );
        }
    }
}
