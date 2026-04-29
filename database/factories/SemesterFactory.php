<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

class SemesterFactory extends Factory
{
    protected $model = Semester::class;

    /**
     * Ensures a Program exists for semester FK (FeeCollect / student tests).
     */
    public static function factoryProgramId(): int
    {
        $faculty = Faculty::firstOrCreate(
            ['faculty_code' => 'TSTFAC'],
            [
                'faculty_name' => 'Test Faculty',
                'dean_name' => 'Dean',
                'email' => 'dean@test.edu',
                'phone' => '000',
                'status' => 'Active',
            ]
        );

        $dept = Department::firstOrCreate(
            ['department_code' => 'TSTDEP', 'faculty_id' => $faculty->id],
            [
                'name' => 'Test Department',
                'head_chairman_name' => 'Chair',
                'email' => 'chair@test.edu',
                'phone' => '000',
                'status' => 'Active',
            ]
        );

        $prog = Program::firstOrCreate(
            ['program_code' => 'TSTPRG'],
            [
                'faculty_id' => $faculty->id,
                'department_id' => $dept->id,
                'program_name' => 'Test Program',
                'degree_level' => 'Bachelor',
                'status' => 'Active',
            ]
        );

        return $prog->id;
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'program_id' => fn () => self::factoryProgramId(),
            'semester_name' => $this->faker->unique()->numerify('Semester ###'),
            'semester_order' => $this->faker->unique()->numberBetween(1, 50),
            'status' => 'Active',
            'user_id' => null,
        ];
    }
}
