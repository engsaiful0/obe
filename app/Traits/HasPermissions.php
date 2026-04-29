<?php

namespace App\Traits;

use App\Models\Permission;

trait HasPermissions
{
    public function hasPermissionTo($permission)
    {
        return $this->rule->permissions()->where('name', $permission)->exists();
    }
}
