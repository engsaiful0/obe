<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentComponent extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const COMPONENT_TYPES = [
        'Attendance',
        'Quiz',
        'Assignment',
        'Midterm',
        'Final',
        'Lab',
        'Project',
        'Viva',
        'Presentation',
        'Other',
    ];

    protected $fillable = [
        'program_id',
        'course_id',
        'component_name',
        'component_type',
        'marks',
        'weight_percentage',
        'status_id',
        'remarks',
    ];

    protected $casts = [
        'program_id' => 'integer',
        'course_id' => 'integer',
        'status_id' => 'integer',
        'marks' => 'decimal:2',
        'weight_percentage' => 'decimal:2',
    ];

    public static function componentTypeOptions(): array
    {
        return array_combine(self::COMPONENT_TYPES, self::COMPONENT_TYPES);
    }

    /**
     * Sum marks for rows linked to statuses with status_name = Active (excluding soft-deleted rows).
     */
    public static function sumActiveMarksForCourse(int $courseId, ?int $excludeId = null): string
    {
        $query = static::query()
            ->where('course_id', $courseId)
            ->whereHas('status', fn ($q) => $q->where('status_name', 'Active'));

        if ($excludeId) {
            $query->whereKeyNot($excludeId);
        }

        return (string) $query->sum('marks');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function questionCloMappings()
    {
        return $this->hasMany(QuestionCloMapping::class, 'assessment_component_id');
    }
}
