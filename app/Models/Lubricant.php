<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lubricant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'bus_id',
        'lubricant_date',
        'lubricant_time',
        'concern_employee_id',
        'lubricant_amount',
        'unit_id',
        'comment',
        'user_id',
    ];

    protected $casts = [
        'lubricant_date' => 'date',
        'lubricant_time' => 'datetime:H:i',
        'lubricant_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the bus that owns the lubricant record
     */
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * Get the employee who handled the lubricant
     */
    public function concernEmployee()
    {
        return $this->belongsTo(Employee::class, 'concern_employee_id');
    }

    /**
     * Get the unit for this lubricant record
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
