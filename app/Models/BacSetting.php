<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BacSetting extends Model
{
    use HasFactory;

    protected $table = 'bac_settings';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    public function scopeKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }
}
