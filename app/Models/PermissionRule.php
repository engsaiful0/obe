<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionRule extends Model
{
    use HasFactory;

    protected $table = 'permission_rules'; // explicitly set
    protected $fillable = ['permission_id', 'rule_id'];

    public function rule()
    {
        return $this->belongsTo(Rule::class, 'rule_id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }
}
