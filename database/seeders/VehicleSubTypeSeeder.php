<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VehicleSubType;
use App\Models\User;

class VehicleSubTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user (admin) or create a default one
        $user = User::first();
        
        if (!$user) {
            echo "No users found. Please create a user first.\n";
            return;
        }

        $vehicleSubTypes = [
            'Own',
            'Hired (Fixed Price)',
            'BRTC Rate/Per Kilometer'
        ];

        foreach ($vehicleSubTypes as $subTypeName) {
            VehicleSubType::firstOrCreate(
                [
                    'sub_type_name' => $subTypeName,
                    'user_id' => $user->id
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        echo "Vehicle Sub-Types seeded successfully!\n";
    }
}
