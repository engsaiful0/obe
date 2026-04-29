<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name', 'user_id'];

    public function rules()
    {
        return $this->belongsToMany(
            PermissionRule::class, // correct model
            'permission_rules',    // pivot table name
            'permission_id',       // foreign key in pivot table
            'rule_id'              // related key in pivot table
        );
    }
}
