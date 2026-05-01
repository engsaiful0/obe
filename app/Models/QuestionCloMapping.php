<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionCloMapping extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'program_id',
        'course_id',
        'assessment_component_id',
        'clo_id',
        'bloom_id',
        'main_question_no',
        'question_part',
        'question_label',
        'question_title',
        'question_description',
        'marks',
        'status_id',
        'remarks',
    ];

    protected $casts = [
        'program_id' => 'integer',
        'course_id' => 'integer',
        'assessment_component_id' => 'integer',
        'clo_id' => 'integer',
        'bloom_id' => 'integer',
        'status_id' => 'integer',
        'marks' => 'decimal:2',
    ];

    /** Sum mapped question marks for a component excluding soft-deleted rows. */
    public static function sumMarksForComponent(int $assessmentComponentId, ?int $excludeId = null): float
    {
        $query = static::query()->where('assessment_component_id', $assessmentComponentId);
        if ($excludeId) {
            $query->whereKeyNot($excludeId);
        }

        return (float) $query->sum('marks');
    }

    /** Count rows sharing the same main question number under a component (for max-parts rule). */
    public static function countForMainQuestion(int $assessmentComponentId, string $mainQuestionNo, ?int $excludeId = null): int
    {
        return static::query()
            ->where('assessment_component_id', $assessmentComponentId)
            ->where('main_question_no', $mainQuestionNo)
            ->when($excludeId, fn ($q) => $q->whereKeyNot($excludeId))
            ->count();
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function assessmentComponent(): BelongsTo
    {
        return $this->belongsTo(AssessmentComponent::class);
    }

    public function clo(): BelongsTo
    {
        return $this->belongsTo(Clo::class);
    }

    public function bloom(): BelongsTo
    {
        return $this->belongsTo(Bloom::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
