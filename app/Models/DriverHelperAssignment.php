<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverHelperAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bus_id',
        'driver_id',
        'bus_helper_id',
        'status_id',
        'assignment_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function busHelper()
    {
        return $this->belongsTo(BusHelper::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
