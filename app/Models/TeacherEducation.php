<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherEducation extends Model
{
    use HasFactory;

    protected $table = 'teacher_educations';

    protected $fillable = [
        'teacher_id',
        'degree',
        'subject',
        'university',
        'passing_year',
        'result',
    ];

    protected $casts = [
        'passing_year' => 'integer',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
