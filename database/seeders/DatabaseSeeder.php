<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Month;
use App\Models\Religion;
use App\Models\Rule;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $this->call(RuleSeeder::class);

    User::factory()->create([
      'name' => 'Test User',
      'email' => 'test@example.com',
      'password' => '123456',
      'rule_id' => '1',
    ]);

    $this->call(PermissionSeeder::class);
    $this->call(ReligionSeeder::class);
    $this->call(DesignationSeeder::class);
    $this->call(PermissionRuleSeeder::class);
    $this->call(ShiftSeeder::class);
    $this->call(SscPassingYearSeeder::class);
    $this->call(SscPassingSessionSeeder::class);
    $this->call(TechnologySeeder::class);
    $this->call(BoardSeeder::class);
    $this->call(AcademicYearSeeder::class);
    $this->call(SemesterSeeder::class);


    $months = [
      'January',
      'February',
      'March',
      'April',
      'May',
      'June',
      'July',
      'August',
      'September',
      'October',
      'November',
      'December'
    ];

    foreach ($months as $index => $month) {
      Month::factory()->create([
        'month_name' => $month,
        'user_id' => 1
      ]);
    }
  }
}
