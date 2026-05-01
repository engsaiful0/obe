<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramOutcome extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'program_id',
        'outcome_type',
        'outcome_code',
        'title',
        'description',
        'category',
        'status',
    ];

    protected $casts = [
        'program_id' => 'integer',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
