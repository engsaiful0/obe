<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BacComplianceStatus extends Model
{
    use HasFactory;

    public const STATUS_MISSING = 'missing';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_COMPLETE = 'complete';

    public const STATUS_NEEDS_REVIEW = 'needs_review';

    protected $table = 'bac_compliance_statuses';

    protected $fillable = [
        'bac_criterion_id',
        'program_id',
        'course_id',
        'course_assignment_id',
        'academic_session_id',
        'semester_id',
        'status',
        'responsible_user_id',
        'remarks',
        'updated_by',
    ];

    protected $casts = [
        'bac_criterion_id' => 'integer',
        'program_id' => 'integer',
        'course_id' => 'integer',
        'course_assignment_id' => 'integer',
        'academic_session_id' => 'integer',
        'semester_id' => 'integer',
        'responsible_user_id' => 'integer',
        'updated_by' => 'integer',
    ];

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(BacCriterion::class, 'bac_criterion_id');
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

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
