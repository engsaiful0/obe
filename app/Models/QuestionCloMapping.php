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

    public const ALLOWED_QUESTION_PARTS = ['a', 'b', 'c', 'd'];

    protected $fillable = [
        'program_id',
        'course_id',
        'assessment_component_id',
        'clo_id',
        'bloom_id',
        'main_question_no',
        'main_question_marks',
        'has_multiple_questions',
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
        'has_multiple_questions' => 'boolean',
        'marks' => 'decimal:2',
        'main_question_marks' => 'decimal:2',
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

    /** Sum part marks under the same main question (same component). */
    public static function sumPartMarksUnderMain(int $assessmentComponentId, string $mainQuestionNo, ?int $excludeId = null): float
    {
        $query = static::query()
            ->where('assessment_component_id', $assessmentComponentId)
            ->where('main_question_no', $mainQuestionNo);

        if ($excludeId) {
            $query->whereKeyNot($excludeId);
        }

        return (float) $query->sum('marks');
    }

    /** Count rows under same main question (excluding soft deletes). */
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
