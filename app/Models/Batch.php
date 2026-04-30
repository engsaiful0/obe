<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'batch_name',
        'batch_code',
        'academic_session_id',
        'start_date',
        'expected_passing_year',
        'status',
        'status_id',
        'user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_passing_year' => 'integer',
        'status_id' => 'integer',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function batchStatus()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function courseAssignments()
    {
        return $this->hasMany(CourseAssignment::class);
    }
}
