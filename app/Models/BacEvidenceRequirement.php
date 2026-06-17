<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BacEvidenceRequirement extends Model
{
    use HasFactory;

    protected $table = 'bac_evidence_requirements';

    protected $fillable = [
        'bac_criterion_id',
        'title',
        'description',
        'evidence_type',
        'is_required',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'bac_criterion_id' => 'integer',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(BacCriterion::class, 'bac_criterion_id');
    }

    public function evidenceLinks(): HasMany
    {
        return $this->hasMany(BacEvidenceLink::class, 'bac_evidence_requirement_id');
    }
}
