<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseFileCqi extends Model
{
    protected $table = 'course_file_cqi';

    protected $fillable = [
        'course_file_id',
        'strengths',
        'weaknesses',
        'problems',
        'improvements',
        'recommendations',
    ];

    public function courseFile(): BelongsTo
    {
        return $this->belongsTo(CourseFile::class);
    }
}
