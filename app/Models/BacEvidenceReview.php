<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacEvidenceReview extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_NEEDS_REVIEW = 'needs_review';

    protected $table = 'bac_evidence_reviews';

    protected $fillable = [
        'bac_evidence_link_id',
        'status',
        'reviewer_remarks',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'bac_evidence_link_id' => 'integer',
        'reviewed_by' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public function evidenceLink(): BelongsTo
    {
        return $this->belongsTo(BacEvidenceLink::class, 'bac_evidence_link_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
