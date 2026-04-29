<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PermissionRule;

class PermissionRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            PermissionRule::create(['permission_id' => 1, 'rule_id' => 1,'user_id' => 1]);
    }
}
