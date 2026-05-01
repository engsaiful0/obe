<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'semester_id',
        'course_code',
        'course_title',
        'credit',
        'course_type',
        'contact_hour',
        'marks',
        'status',
        'user_id',
    ];

    protected $casts = [
        'credit' => 'decimal:2',
        'contact_hour' => 'integer',
        'marks' => 'integer',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courseAssignments()
    {
        return $this->hasMany(CourseAssignment::class);
    }

    public function clos()
    {
        return $this->hasMany(Clo::class);
    }

    public function cloPoMappings()
    {
        return $this->hasMany(CloPoMapping::class);
    }
}
