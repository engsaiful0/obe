<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BacStandard extends Model
{
    use HasFactory;

    protected $table = 'bac_standards';

    protected $fillable = [
        'standard_no',
        'title',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function criteria(): HasMany
    {
        return $this->hasMany(BacCriterion::class, 'bac_standard_id');
    }

    public function activeCriteria(): HasMany
    {
        return $this->criteria()->where('is_active', true);
    }
}
