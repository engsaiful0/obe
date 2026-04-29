<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Designation;

class DesignationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $designations = [
            // Teachers
            ['designation_name' => 'Instructor', 'designation_type' => 'Teacher'],
            ['designation_name' => 'Sub-Instructor', 'designation_type' => 'Teacher'],
            ['designation_name' => 'Chief Instructor', 'designation_type' => 'Teacher'],
            ['designation_name' => 'Principal', 'designation_type' => 'Teacher'],
            ['designation_name' => 'Vice Principal', 'designation_type' => 'Teacher'],
            ['designation_name' => 'Lecturer', 'designation_type' => 'Teacher'],
            ['designation_name' => 'Senior Lecturer', 'designation_type' => 'Teacher'],
            ['designation_name' => 'Assistant Professor', 'designation_type' => 'Teacher'],
            ['designation_name' => 'Associate Professor', 'designation_type' => 'Teacher'],
            ['designation_name' => 'Professor', 'designation_type' => 'Teacher'],

            // Employees
            ['designation_name' => 'Manager', 'designation_type' => 'Employee'],
            ['designation_name' => 'Assistant Manager', 'designation_type' => 'Employee'],
            ['designation_name' => 'Senior Manager', 'designation_type' => 'Employee'],
            ['designation_name' => 'Director', 'designation_type' => 'Employee'],
            ['designation_name' => 'Assistant Director', 'designation_type' => 'Employee'],
            ['designation_name' => 'Senior Director', 'designation_type' => 'Employee'],
            ['designation_name' => 'Vice President', 'designation_type' => 'Employee'],
            ['designation_name' => 'President', 'designation_type' => 'Employee'],
            ['designation_name' => 'Chief Executive Officer', 'designation_type' => 'Employee'],
            ['designation_name' => 'Chief Operating Officer', 'designation_type' => 'Employee'],
        ];

        foreach ($designations as $designation) {
            Designation::create([
                'designation_name' => $designation['designation_name'],
                'designation_type' => $designation['designation_type'],
                'user_id' => 1
            ]);
        }
    }
}
