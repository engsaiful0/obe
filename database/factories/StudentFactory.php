<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Requires seeded programs, batches, genders, religions, and academic_sessions when generating students.
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'program_id' => 1,
            'batch_id' => 1,
            'student_code' => 'ST-'.$this->faker->unique()->numerify('######'),
            'student_name' => $this->faker->name,
            'picture' => null,
            'father_name' => $this->faker->name('male'),
            'mother_name' => null,
            'present_address' => null,
            'permanent_address' => null,
            'email' => null,
            'phone' => null,
            'gender_id' => 1,
            'religion_id' => 1,
            'academic_session_id' => 1,
            'user_id' => null,
            'status' => 'Active',
            'date_of_birth' => null,
            'nationality_id' => null,
            'nid_or_birth_cert_no' => null,
            'blood_group_id' => null,
            'marital_status_id' => null,
            'admission_date' => null,
            'guardian_name' => null,
            'guardian_relation' => null,
            'guardian_phone' => null,
            'guardian_email' => null,
            'guardian_address' => null,
            'shift' => 'Morning',
            'student_type' => 'Regular',
            'signature' => null,
            'nid_document' => null,
        ];
    }
}
