<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'department_id',
        'teacher_name',
        'email',
        'phone',
        'employee_id',
        'designation_id',
        'login_email',
        'password',
        'status_id',
        'religion_id',
        'marital_status_id',
        'blood_group_id',
        'gender_id',
        'profile_photo',
        'joining_date',
        'employment_type',
        'experience_years',
        'office_room',
        'is_program_coordinator',
        'is_course_coordinator',
        'can_submit_clo',
        'can_submit_cqi',
        'user_id',
        // Backward-compat columns used by old settings module
        'designation_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'is_program_coordinator' => 'boolean',
        'is_course_coordinator' => 'boolean',
        'can_submit_clo' => 'boolean',
        'can_submit_cqi' => 'boolean',
        'experience_years' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detail()
    {
        return $this->hasOne(TeacherDetail::class);
    }

    public function educations()
    {
        return $this->hasMany(TeacherEducation::class);
    }

    public function courseAssignments()
    {
        return $this->hasMany(\App\Models\CourseAssignment::class);
    }
}
