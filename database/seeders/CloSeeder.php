<?php

namespace Database\Seeders;

use App\Models\Bloom;
use App\Models\Course;
use App\Models\Clo;
use App\Models\Status;
use Illuminate\Database\Seeder;

class CloSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::query()->where('course_code', 'CSE-101')->first();
        if (! $course) {
            $this->command?->warn('CloSeeder skipped: no course with course_code CSE-101.');

            return;
        }

        $status = Status::query()->where('status_name', 'Active')->first()
            ?? Status::query()->orderBy('status_name')->first();

        if (! $status) {
            $this->command?->warn('CloSeeder skipped: no status record.');

            return;
        }

        $levels = [
            'CLO1' => [
                'bloom_name' => 'Understand',
                'title' => 'Explain fundamental computing concepts',
                'description' => 'Explain the basic concepts of computer systems, programming, and problem-solving.',
            ],
            'CLO2' => [
                'bloom_name' => 'Apply',
                'title' => 'Apply programming logic',
                'description' => 'Apply basic programming logic to solve simple computational problems.',
            ],
            'CLO3' => [
                'bloom_name' => 'Analyze',
                'title' => 'Analyze simple computing problems',
                'description' => 'Analyze basic computing problems and identify suitable algorithmic solutions.',
            ],
        ];

        foreach ($levels as $code => $payload) {
            $bloom = Bloom::query()->where('name', $payload['bloom_name'])->first();
            if (! $bloom) {
                $this->command?->warn("CloSeeder: Bloom {$payload['bloom_name']} missing, skipped {$code}.");

                continue;
            }

            Clo::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                    'clo_code' => $code,
                ],
                [
                    'program_id' => $course->program_id,
                    'bloom_id' => $bloom->id,
                    'title' => $payload['title'],
                    'description' => $payload['description'],
                    'status_id' => $status->id,
                ]
            );
        }
    }
}
