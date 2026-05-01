<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use Illuminate\Database\Seeder;

class AcademicSessionSeeder extends Seeder
{
    public function run(): void
    {
        $year = (int) date('Y');

        AcademicSession::query()->firstOrCreate(
            ['session_name' => 'Spring', 'academic_year' => $year],
            [
                'start_date' => now()->startOfYear()->toDateString(),
                'end_date' => now()->endOfYear()->toDateString(),
                'status' => 'Active',
                'user_id' => 1,
            ]
        );
    }
}
