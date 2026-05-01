<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'clos';

    protected $fillable = [
        'program_id',
        'course_id',
        'bloom_id',
        'clo_code',
        'title',
        'description',
        'status_id',
    ];

    protected $casts = [
        'program_id' => 'integer',
        'course_id' => 'integer',
        'bloom_id' => 'integer',
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

    public function bloom(): BelongsTo
    {
        return $this->belongsTo(Bloom::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function cloPoMappings()
    {
        return $this->hasMany(CloPoMapping::class);
    }

    public function questionCloMappings()
    {
        return $this->hasMany(QuestionCloMapping::class);
    }
}
