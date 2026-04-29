<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'daily-deployment-plan-add',
            'daily-deployment-plan-view',
            'daily-deployment-plan-edit',
            'daily-deployment-plan-delete',
            'friday-deployment-plan-add',
            'friday-deployment-plan-view',
            'friday-deployment-plan-edit',
            'friday-deployment-plan-delete',
        ];

        $existing = Permission::pluck('name')->all();
        $maxId = (int) (Permission::max('id') ?? 0);

        foreach ($permissions as $permissionName) {
            if (in_array($permissionName, $existing, true)) {
                continue;
            }
            $maxId++;
            Permission::create([
                'id' => $maxId,
                'name' => $permissionName,
                'user_id' => 1
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'daily-deployment-plan-add',
            'daily-deployment-plan-view',
            'daily-deployment-plan-edit',
            'daily-deployment-plan-delete',
            'friday-deployment-plan-add',
            'friday-deployment-plan-view',
            'friday-deployment-plan-edit',
            'friday-deployment-plan-delete',
        ];

        Permission::whereIn('name', $permissions)->delete();
    }
};

