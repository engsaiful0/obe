<?php

namespace Database\Seeders;

use App\Models\BacSetting;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class BacFoundationSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    protected array $permissions = [
        'bac-view',
        'bac-manage',
        'bac-review',
        'bac-report',
        'bac-evidence-submit',
    ];

    /**
     * @var array<string, array{value: string, description: string}>
     */
    protected array $settings = [
        'clo_attainment_threshold' => [
            'value' => '60',
            'description' => 'Default CLO attainment threshold percentage for BAC compliance.',
        ],
        'direct_assessment_weight' => [
            'value' => '80',
            'description' => 'Default direct assessment weight percentage for BAC reporting.',
        ],
        'indirect_assessment_weight' => [
            'value' => '20',
            'description' => 'Default indirect assessment weight percentage for BAC reporting.',
        ],
    ];

    public function run(): void
    {
        foreach ($this->permissions as $permission) {
            Permission::query()->firstOrCreate(
                ['name' => $permission],
                ['user_id' => 1]
            );
        }

        foreach ($this->settings as $key => $setting) {
            BacSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $setting['value'],
                    'description' => $setting['description'],
                ]
            );
        }
    }
}
