<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'department_id',
        'program_name',
        'program_code',
        'degree_level',
        'duration',
        'total_semester',
        'total_credit',
        'status',
        'user_id',
    ];

    protected $casts = [
        'total_semester' => 'integer',
        'total_credit' => 'integer',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function courseAssignments()
    {
        return $this->hasMany(CourseAssignment::class);
    }

    public function peos()
    {
        return $this->hasMany(Peo::class);
    }

    public function programOutcomes()
    {
        return $this->hasMany(ProgramOutcome::class);
    }

    public function clos()
    {
        return $this->hasMany(Clo::class);
    }
}
