<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Rules & basic settings
            'rule-add', 'rule-edit', 'rule-delete', 'settings-view',

            // Expense
            'expense-add', 'expense-view', 'expense-edit', 'expense-delete',

            // Issue
            'issue-add', 'issue-view', 'issue-edit', 'issue-delete',
            'damage-add', 'damage-view', 'damage-edit', 'damage-delete',

            // Purchase
            'purchase-add', 'purchase-view', 'purchase-edit', 'purchase-delete',

            // Employee
            'employee-add', 'employee-view', 'employee-edit', 'employee-delete',

            // Driver
            'driver-add', 'driver-view', 'driver-edit', 'driver-delete',

            // Assistant
            'assistant-add', 'assistant-view', 'assistant-edit', 'assistant-delete',

            // Bus
            'bus-add', 'bus-view', 'bus-edit', 'bus-delete',

            // Distance
            'distance-add', 'distance-view', 'distance-edit', 'distance-delete',

            // Employee Attendance
            'employee-attendance-add', 'employee-attendance-view', 'employee-attendance-edit', 'employee-attendance-delete',

            // Bus Schedules / Lists
            'bus-schedule-add', 'bus-schedule-view', 'bus-schedule-edit', 'bus-schedule-delete',
            'daily-bus-list-add', 'daily-bus-list-view', 'daily-bus-list-edit', 'daily-bus-list-delete',

            // Deployment Plans
            'daily-deployment-plan-add', 'daily-deployment-plan-view', 'daily-deployment-plan-edit', 'daily-deployment-plan-delete',
            'friday-deployment-plan-add', 'friday-deployment-plan-view', 'friday-deployment-plan-edit', 'friday-deployment-plan-delete',

            // Reports (generic view)
            'report-view',
            'expense-report-view', 'employee-list-report-view', 'monthly-bill-view', 'punishment-report-view', 'purchase-report-view', 'issue-report-view', 'stock-report-view', 'reward-report-view',

            // Salary settings/configuration
            'salary-settings-view', 'salary-settings-create', 'salary-settings-edit', 'salary-settings-delete', 'salary-settings-manage',

            // Salary sheet
            'salary-sheet-view', 'salary-sheet-export',

            // Trip (Bus Trip)
            'bus-trip-add', 'bus-trip-view', 'bus-trip-edit', 'bus-trip-delete',

            // Income
            'income-add', 'income-view', 'income-edit', 'income-delete',

            // Punishment
            'punishment-add', 'punishment-view', 'punishment-edit', 'punishment-delete',

            // Reward
            'reward-add', 'reward-view', 'reward-edit', 'reward-delete',

            // Settings entities
            'brand-manage', 'blood-group-manage', 'bus-schedule-keyword-manage', 'cache-clear', 'color-manage', 'designation-manage',
            'driver-type-manage', 'educational-qualification-manage', 'expense-head-manage', 'experience-year-manage', 'fuel-type-manage', 'gender-manage',
            'income-head-manage', 'issuing-authority-manage', 'item-manage', 'license-type-manage', 'marital-status-manage', 'nationality-manage',
            'payment-method-manage', 'punishment-type-manage', 'violation-type-manage', 'religion-manage', 'reward-type-manage', 'status-manage',
            'stoppage-manage', 'supplier-manage', 'supplier-type-manage', 'trip-time-manage', 'employee-type-manage',
            'vehicle-type-manage', 'vehicle-sub-type-manage', 'vehicle-user-manage', 'unit-manage', 'vehicle-route-manage', 'users-manage', 'year-manage',
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
}
