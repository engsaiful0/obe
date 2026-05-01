<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentQuestionMark extends Model
{
    protected $fillable = [
        'student_mark_id',
        'question_clo_mapping_id',
        'obtained_marks',
    ];

    protected $casts = [
        'obtained_marks' => 'decimal:2',
    ];

    public function studentMark(): BelongsTo
    {
        return $this->belongsTo(StudentMark::class);
    }

    public function questionCloMapping(): BelongsTo
    {
        return $this->belongsTo(QuestionCloMapping::class);
    }
}
