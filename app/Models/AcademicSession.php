<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_name',
        'academic_year',
        'start_date',
        'end_date',
        'status',
        'user_id',
    ];

    protected $casts = [
        'academic_year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function courseAssignments()
    {
        return $this->hasMany(CourseAssignment::class);
    }

    public function questionCloMappings()
    {
        return $this->hasMany(QuestionCloMapping::class);
    }
}
