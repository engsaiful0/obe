<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherModuleSeeder extends Seeder
{
    public function run(): void
    {
        $departmentId = Department::query()->value('id');
        if (! $departmentId) {
            return;
        }

        Teacher::updateOrCreate(
            ['employee_id' => 'T-0001'],
            [
                'department_id' => $departmentId,
                'name' => 'Dr. Demo Teacher',
                'teacher_name' => 'Dr. Demo Teacher',
                'designation' => 'Assistant Professor',
                'email' => 'demo.teacher@example.com',
                'phone' => '01700000000',
                'login_email' => 'demo.teacher.login@example.com',
                'password' => Hash::make('password'),
                'status' => 'Active',
                'employment_type' => 'Full-time',
                'experience_years' => 6,
            ]
        );
    }
}
