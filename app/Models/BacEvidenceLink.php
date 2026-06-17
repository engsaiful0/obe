<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BacEvidenceLink extends Model
{
    use HasFactory;

    protected $table = 'bac_evidence_links';

    protected $fillable = [
        'bac_criterion_id',
        'bac_evidence_requirement_id',
        'program_id',
        'course_id',
        'course_assignment_id',
        'course_file_id',
        'course_file_document_id',
        'clo_id',
        'program_outcome_id',
        'evidence_title',
        'evidence_type',
        'evidence_source',
        'external_url',
        'notes',
        'submitted_by',
    ];

    protected $casts = [
        'bac_criterion_id' => 'integer',
        'bac_evidence_requirement_id' => 'integer',
        'program_id' => 'integer',
        'course_id' => 'integer',
        'course_assignment_id' => 'integer',
        'course_file_id' => 'integer',
        'course_file_document_id' => 'integer',
        'clo_id' => 'integer',
        'program_outcome_id' => 'integer',
        'submitted_by' => 'integer',
    ];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(BacCriterion::class, 'bac_criterion_id');
    }

    public function evidenceRequirement(): BelongsTo
    {
        return $this->belongsTo(BacEvidenceRequirement::class, 'bac_evidence_requirement_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function courseAssignment(): BelongsTo
    {
        return $this->belongsTo(CourseAssignment::class);
    }

    public function courseFile(): BelongsTo
    {
        return $this->belongsTo(CourseFile::class);
    }

    public function courseFileDocument(): BelongsTo
    {
        return $this->belongsTo(CourseFileDocument::class);
    }

    public function clo(): BelongsTo
    {
        return $this->belongsTo(Clo::class);
    }

    public function programOutcome(): BelongsTo
    {
        return $this->belongsTo(ProgramOutcome::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BacEvidenceReview::class, 'bac_evidence_link_id');
    }

    public function latestReview(): HasOne
    {
        return $this->hasOne(BacEvidenceReview::class, 'bac_evidence_link_id')->latestOfMany();
    }
}
