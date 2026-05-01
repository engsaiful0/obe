<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Placeholder — student marks inherit live data from Question–CLO mappings,
 * Assessment components, Students, and OBE statuses. No synthetic rows seeded by default.
 */
class StudentMarksModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Intentionally empty: use Bulk entry after mappings exist for a session/component.
    }
}
