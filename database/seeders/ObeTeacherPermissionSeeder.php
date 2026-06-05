<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Rule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObeTeacherPermissionSeeder extends Seeder
{
    /**
     * OBE permissions used by the teacher panel menu and course module.
     *
     * @var array<int, string>
     */
    protected array $teacherPermissions = [
        'my-courses',
        'my-course-list',
    ];

    /**
     * Full OBE permission set (admin / coordinator menus).
     *
     * @var array<int, string>
     */
    protected array $obePermissions = [
        'my-courses',
        'my-course-list',
        'add-teacher',
        'view-teacher',
        'student-add',
        'student-view',
        'add-course-assignment',
        'view-course-assignment',
        'student-marks-view',
        'student-marks-add',
        'obe-settings-view',
    ];

    public function run(): void
    {
        $maxId = (int) (Permission::max('id') ?? 0);

        foreach ($this->obePermissions as $name) {
            if (Permission::query()->where('name', $name)->exists()) {
                continue;
            }
            $maxId++;
            Permission::create([
                'id' => $maxId,
                'name' => $name,
                'user_id' => 1,
            ]);
        }

        $teacherRule = Rule::query()
            ->whereRaw('LOWER(name) = ?', ['teacher'])
            ->first();

        if (! $teacherRule) {
            return;
        }

        $permissionIds = Permission::query()
            ->whereIn('name', $this->teacherPermissions)
            ->pluck('id')
            ->all();

        foreach ($permissionIds as $permissionId) {
            $exists = DB::table('permission_rules')
                ->where('rule_id', (int) $teacherRule->id)
                ->where('permission_id', (int) $permissionId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('permission_rules')->insert([
                'permission_id' => (int) $permissionId,
                'rule_id' => (int) $teacherRule->id,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
