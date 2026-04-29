<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'roll_no' => $this->faker->unique()->randomNumber(),
            'reg_no' => $this->faker->unique()->randomNumber(),
            'academic_year_id' => \App\Models\AcademicYear::factory(),
            'semester_id' => \App\Models\Semester::factory(),
        ];
    }
}
