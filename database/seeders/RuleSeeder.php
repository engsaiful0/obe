<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rule;

class RuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'Super Admin',
            'Admin',
            'Accountant',
            'Employee',
            'Teacher',
            'Student'
        ];
        foreach ($permissions as $permission) {
            Rule::create(['name' => $permission]);
        }
    }
}
