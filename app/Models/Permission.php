<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];

    public function rules()
    {
        return $this->belongsToMany(
            Rule::class,
            'permission_rules',
            'permission_id',
            'rule_id'
        );
    }
}
