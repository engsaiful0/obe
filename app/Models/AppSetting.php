<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name',
        'address',
        'phone',
        'email',
        'website',
        'currency',
        'logo',
        'fevicon',
        'start_date',
        'date_format',
        'time_format',
        'maintainence_mode',
        'maintainence_mode_message',
        'sms_url',
        'api_key',
        'sender_id',
        'sms_status',
    ];
}
