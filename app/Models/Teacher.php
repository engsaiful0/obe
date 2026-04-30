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
        'employee_type_id',
        'experience_year_id',
        'office_room',
        'is_program_coordinator',
        'is_course_coordinator',
        'can_submit_clo',
        'can_submit_cqi',
        'user_id',
        'date_of_birth',
        'nid',
        'address',
        'research_area',
        'google_scholar_link',
        'orcid_id',
        'total_publications',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'date_of_birth' => 'date',
        'is_program_coordinator' => 'boolean',
        'is_course_coordinator' => 'boolean',
        'can_submit_clo' => 'boolean',
        'can_submit_cqi' => 'boolean',
        'total_publications' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function teacherStatus()
    {
        return $this->belongsTo(Status::class, 'status_id');
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
