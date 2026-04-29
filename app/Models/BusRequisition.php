<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusRequisition extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'purpose',
        'required_bus_date',
        'required_time',
        'number_of_buses',
        'total_passengers',
        'department_id',
        'requisition_sender_name',
        'mobile_number',
        'email_address',
        'user_id',
        'remarks',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'required_bus_date' => 'date',
        'required_time' => 'string',
        'number_of_buses' => 'integer',
        'total_passengers' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

