<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Religion;

class ReligionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $religions = [
            'Islam',
            'Christianity',
            'Hinduism',
            'Buddhism',
            'Sikhism',
            'Judaism',
        ];

        foreach ($religions as $religion) {
            Religion::create(['religion_name' => $religion,'user_id' => 1]);
        }
    }
}
