<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'batch_id',
        'student_code',
        'student_name',
        'picture',
        'father_name',
        'mother_name',
        'present_address',
        'permanent_address',
        'email',
        'phone',
        'gender_id',
        'religion_id',
        'academic_session_id',
        'user_id',
        'status_id',
        'date_of_birth',
        'nationality_id',
        'nid_or_birth_cert_no',
        'blood_group_id',
        'marital_status_id',
        'admission_date',
        'guardian_name',
        'guardian_relation',
        'guardian_phone',
        'guardian_email',
        'guardian_address',
        'shift',
        'student_type',
        'signature',
        'nid_document',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function religion()
    {
        return $this->belongsTo(Religion::class);
    }

    public function academicSession()
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }

    public function bloodGroup()
    {
        return $this->belongsTo(BloodGroup::class);
    }

    public function maritalStatus()
    {
        return $this->belongsTo(MaritalStatus::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function studentMarks()
    {
        return $this->hasMany(StudentMark::class);
    }
}
