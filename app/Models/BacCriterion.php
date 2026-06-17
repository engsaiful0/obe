<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BacCriterion extends Model
{
    use HasFactory;

    protected $table = 'bac_criteria';

    protected $fillable = [
        'bac_standard_id',
        'criterion_no',
        'title',
        'description',
        'required_evidence',
        'responsible_role',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'bac_standard_id' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function standard(): BelongsTo
    {
        return $this->belongsTo(BacStandard::class, 'bac_standard_id');
    }

    public function evidenceRequirements(): HasMany
    {
        return $this->hasMany(BacEvidenceRequirement::class, 'bac_criterion_id');
    }

    public function evidenceLinks(): HasMany
    {
        return $this->hasMany(BacEvidenceLink::class, 'bac_criterion_id');
    }

    public function complianceStatuses(): HasMany
    {
        return $this->hasMany(BacComplianceStatus::class, 'bac_criterion_id');
    }
}
