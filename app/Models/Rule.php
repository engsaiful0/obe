<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_rules',
            'rule_id',
            'permission_id'
        );
    }


    public function users()
    {
        return $this->hasMany(User::class);
    }
}
