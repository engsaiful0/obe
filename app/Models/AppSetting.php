<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'university_name',
        'short_name',
        'logo',
        'address',
        'phone',
        'email',
        'website',
        'established_year',
        'vc_name',
        'pro_vc_name',
        'registrar_name',
        'controller_name',
        'time_zone',
        'academic_system',
        'status',
        'user_id',
    ];
}
