<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentMark extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (StudentMark $mark) {
            StudentQuestionMark::query()->where('student_mark_id', $mark->getKey())->delete();
        });
    }

    protected $fillable = [
        'academic_session_id',
        'program_id',
        'course_id',
        'batch_id',
        'section_id',
        'assessment_component_id',
        'student_id',
        'attendance_marks',
        'total_marks',
        'status_id',
    ];

    protected $casts = [
        'total_marks' => 'decimal:2',
        'attendance_marks' => 'decimal:2',
        'section_id' => 'integer',
    ];

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function assessmentComponent(): BelongsTo
    {
        return $this->belongsTo(AssessmentComponent::class, 'assessment_component_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function studentQuestionMarks(): HasMany
    {
        return $this->hasMany(StudentQuestionMark::class);
    }
}
