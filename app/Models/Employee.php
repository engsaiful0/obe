<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_name',
        'employee_unique_id',
        'gender',
        'father_name',
        'mother_name',
        'mobile',
        'email',
        'nid',
        'religion_id',
        'designation_id',
        'present_address',
        'permanent_address',
        'picture',
        'cv_upload',
        'employee_type_id',
        'ssc_or_equivalent_group',
        'ssc_result',
        'ssc_documents_upload',

        'hsc_or_equivalent_group',
        'hsc_result',
        'hsc_documents_upload',

        'bachelor_or_equivalent_group',
        'result',
        'honors_documents_upload',

        'master_or_equivalent_group',
        'masters_result',
        'masters_document_upload',

        'years_of_experience',
        'date_of_join',
        'basic_salary',
        'house_rent',
        'medical_allowance',
        'other_allowance',
        'gross_salary',

        'user_id',
    ];

    // (optional) quick relationships
    public function religion()
    {
        return $this->belongsTo(\App\Models\Religion::class);
    }

    public function designation()
    {
        return $this->belongsTo(\App\Models\Designation::class);
    }

    public function employeeType()
    {
        return $this->belongsTo(\App\Models\EmployeeType::class);
    }
}
