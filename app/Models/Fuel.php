<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fuel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bus_id',
        'fuel_date',
        'fuel_time',
        'concern_employee_id',
        'fuel_amount',
        'unit_id',
        'comment',
        'user_id',
    ];

    protected $casts = [
        'fuel_date' => 'date',
        'fuel_time' => 'datetime:H:i',
        'fuel_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the bus that owns the fuel record
     */
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * Get the employee who handled the fuel
     */
    public function concernEmployee()
    {
        return $this->belongsTo(Employee::class, 'concern_employee_id');
    }

    /**
     * Get the unit for this fuel record
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the user who created the record
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
