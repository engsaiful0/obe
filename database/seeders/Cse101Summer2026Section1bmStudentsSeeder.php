<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Program;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Seeder;

/**
 * Seeds 20 students for CSE-101 / Summer 2026 / B.Sc. in CSE / Section 1BM (One BM).
 */
class Cse101Summer2026Section1bmStudentsSeeder extends Seeder
{
    public function run(): void
    {
        $session = AcademicSession::query()
            ->where('session_name', 'Summer 2026')
            ->first();

        $program = Program::query()
            ->where('program_name', 'B.Sc. in CSE')
            ->first();

        $course = Course::query()->where('course_code', 'CSE-101')->first();

        $section = Section::query()
            ->where('program_id', $program?->id)
            ->where(function ($q) {
                $q->where('section_code', '1BM')
                    ->orWhere('section_name', 'One BM');
            })
            ->first();

        if (! $session || ! $program || ! $course || ! $section) {
            $this->command?->warn('Cse101Summer2026Section1bmStudentsSeeder skipped: missing session, program, course, or section.');

            return;
        }

        $batch = Batch::query()->updateOrCreate(
            [
                'program_id' => $program->id,
                'batch_name' => 'CSE Summer 2026 (1BM)',
            ],
            [
                'batch_code' => 'CSE-S26-1BM',
                'academic_session_id' => $session->id,
                'start_date' => '2026-06-01',
                'expected_passing_year' => 2030,
                'status_id' => 1,
                'user_id' => 1,
            ]
        );

        $students = [
            ['Ayesha Rahman', 'Abdul Rahman', 'Fatima Rahman'],
            ['Tanvir Hasan', 'Hasan Ali', 'Nasima Begum'],
            ['Nusrat Jahan', 'Jahan Uddin', 'Rina Jahan'],
            ['Rafiul Islam', 'Islam Uddin', 'Shahana Islam'],
            ['Sadia Afrin', 'Afrin Hossain', 'Momena Afrin'],
            ['Imran Hossain', 'Hossain Mia', 'Salma Khatun'],
            ['Priya Das', 'Subhash Das', 'Anjali Das'],
            ['Mahmudul Karim', 'Karim Uddin', 'Rokeya Karim'],
            ['Farhana Akter', 'Akter Hossain', 'Jahanara Akter'],
            ['Sabbir Ahmed', 'Ahmed Ali', 'Nasreen Ahmed'],
            ['Tasnim Sultana', 'Sultana Begum', 'Rashida Sultana'],
            ['Kamal Uddin', 'Uddin Mia', 'Morjina Begum'],
            ['Laboni Chakraborty', 'Chandra Chakraborty', 'Mita Chakraborty'],
            ['Arifur Rahman', 'Rahman Mia', 'Shirin Rahman'],
            ['Mim Akhter', 'Akhter Hossain', 'Rehana Akhter'],
            ['Shuvo Das', 'Das Gupta', 'Purnima Das'],
            ['Nadia Islam', 'Islam Uddin', 'Shamima Islam'],
            ['Rakib Hasan', 'Hasan Uddin', 'Roksana Hasan'],
            ['Ishrat Jahan', 'Jahan Uddin', 'Shireen Jahan'],
            ['Fahim Chowdhury', 'Chowdhury Mia', 'Nasima Chowdhury'],
        ];

        foreach ($students as $index => [$name, $father, $mother]) {
            $num = str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $studentCode = 'CSE101-S26-'.$num;
            $registrationNo = 'CSE101-REG-2026-'.$num;

            Student::query()->updateOrCreate(
                ['student_code' => $studentCode],
                [
                    'program_id' => $program->id,
                    'batch_id' => $batch->id,
                    'academic_session_id' => $session->id,
                    'section_id' => $section->id,
                    'registration_no' => $registrationNo,
                    'roll_no' => (string) ($index + 1),
                    'student_name' => $name,
                    'father_name' => $father,
                    'mother_name' => $mother,
                    'email' => strtolower(str_replace(' ', '.', $name)).'@student.cse.local',
                    'phone' => '017'.str_pad((string) (10000000 + $index), 8, '0', STR_PAD_LEFT),
                    'gender_id' => ($index % 2) + 1,
                    'religion_id' => ($index % 3) + 1,
                    'status_id' => 1,
                    'admission_date' => '2026-06-01',
                    'current_semester' => 1,
                    'shift' => 'Morning',
                    'student_type' => 'Regular',
                    'present_address' => 'Dhaka, Bangladesh',
                ]
            );
        }

        $this->command?->info(sprintf(
            'Seeded 20 students for %s / %s / Section %s (%s) / Batch %s.',
            $course->course_code,
            $session->session_name,
            $section->section_code,
            $section->section_name,
            $batch->batch_name
        ));
    }
}
