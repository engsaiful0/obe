<?php

namespace Database\Seeders;

use App\Models\Mission;
use App\Models\Peo;
use App\Models\Program;
use App\Models\RelatedTo;
use App\Models\Status;
use App\Models\University;
use App\Models\Vision;
use Illuminate\Database\Seeder;

class ObeVisionMissionPeoSeeder extends Seeder
{
    /**
     * Seed OBE-related statuses, sample university vision/mission, and sample PEOs per program.
     */
    public function run(): void
    {
        $userId = 1;

        $obe = RelatedTo::firstOrCreate(
            ['name' => 'OBE'],
            ['user_id' => $userId]
        );

        $active = Status::firstOrCreate(
            ['status_name' => 'Active', 'related_to_id' => $obe->id],
            ['user_id' => $userId]
        );

        Status::firstOrCreate(
            ['status_name' => 'Inactive', 'related_to_id' => $obe->id],
            ['user_id' => $userId]
        );

        $uni = University::query()->first();
        if (! $uni) {
            $uni = University::create([
                'name' => 'Sample University',
                'short_name' => 'SU',
                'user_id' => $userId,
            ]);
        }

        if (! Vision::query()->where('type', 'University')->where('university_id', $uni->id)->exists()) {
            Vision::create([
                'type' => 'University',
                'university_id' => $uni->id,
                'department_id' => null,
                'title' => 'Institutional vision',
                'description' => 'To be a leading institution in education, research, and innovation that serves society and advances sustainable development.',
                'status_id' => $active->id,
            ]);
        }

        if (! Mission::query()->where('type', 'University')->where('university_id', $uni->id)->exists()) {
            Mission::create([
                'type' => 'University',
                'university_id' => $uni->id,
                'department_id' => null,
                'title' => 'Institutional mission',
                'description' => 'To deliver quality programs, foster critical thinking and ethical leadership, and engage communities through scholarship and service.',
                'status_id' => $active->id,
            ]);
        }

        $deptId = Program::query()->value('department_id');
        if ($deptId && ! Vision::query()->where('type', 'Department')->where('department_id', $deptId)->exists()) {
            Vision::create([
                'type' => 'Department',
                'university_id' => null,
                'department_id' => $deptId,
                'title' => 'Department vision',
                'description' => 'To excel in teaching and research within the discipline while preparing graduates for professional practice and lifelong learning.',
                'status_id' => $active->id,
            ]);
        }

        if ($deptId && ! Mission::query()->where('type', 'Department')->where('department_id', $deptId)->exists()) {
            Mission::create([
                'type' => 'Department',
                'university_id' => null,
                'department_id' => $deptId,
                'title' => 'Department mission',
                'description' => 'To offer rigorous curricula, support faculty development, and maintain strong industry and academic partnerships.',
                'status_id' => $active->id,
            ]);
        }

        $samples = [
            ['PEO1', 'Technical foundation', 'Graduates will apply engineering fundamentals and modern tools to solve complex problems in their field.'],
            ['PEO2', 'Professional practice', 'Graduates will communicate effectively, work in teams, and adhere to ethical and societal responsibilities.'],
            ['PEO3', 'Lifelong learning', 'Graduates will pursue continuous professional development and adapt to emerging technologies and global trends.'],
        ];

        Program::query()->orderBy('id')->each(function (Program $program) use ($samples, $active) {
            foreach ($samples as [$code, $title, $description]) {
                if (Peo::query()->where('program_id', $program->id)->where('peo_code', $code)->exists()) {
                    continue;
                }
                Peo::create([
                    'program_id' => $program->id,
                    'peo_code' => $code,
                    'peo_title' => $title,
                    'description' => $description,
                    'status_id' => $active->id,
                ]);
            }
        });
    }
}
