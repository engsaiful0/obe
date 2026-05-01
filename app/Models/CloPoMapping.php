<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CloPoMapping extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'program_id',
        'course_id',
        'clo_id',
        'program_outcome_id',
        'mapping_level',
        'status_id',
        'remarks',
    ];

    protected $casts = [
        'program_id' => 'integer',
        'course_id' => 'integer',
        'clo_id' => 'integer',
        'program_outcome_id' => 'integer',
        'mapping_level' => 'integer',
        'status_id' => 'integer',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function clo(): BelongsTo
    {
        return $this->belongsTo(Clo::class);
    }

    public function programOutcome(): BelongsTo
    {
        return $this->belongsTo(ProgramOutcome::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
