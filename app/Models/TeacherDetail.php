<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'date_of_birth',
        'gender',
        'blood_group',
        'nid',
        'marital_status',
        'address',
        'research_area',
        'google_scholar_link',
        'orcid_id',
        'total_publications',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'total_publications' => 'integer',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
